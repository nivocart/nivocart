<?php
/**
 * Class ModelSaleVoucher
 *
 * @package NivoCart
 */
class ModelSaleVoucher extends Model {
	/**
	 * Functions Add, Edit, Delete, Get
	 */
	public function addVoucher(array $data = []): void {
		$code = !empty($data['code']) ? voucherCodeNormalise($data['code']) : $this->generateCode();

		$this->db->query("INSERT INTO `" . DB_PREFIX . "voucher` SET `code` = '" . $this->db->escape($code) . "', from_name = '" . $this->db->escape($data['from_name']) . "', from_email = '" . $this->db->escape($data['from_email']) . "', to_name = '" . $this->db->escape($data['to_name']) . "', to_email = '" . $this->db->escape($data['to_email']) . "', design = '" . $this->db->escape($data['design']) . "', message = '" . $this->db->escape($data['message']) . "', amount = '" . (float)$data['amount'] . "', status = '" . (int)$data['status'] . "', date_expire = " . $this->getExpiryValue($data) . ", date_added = NOW()");

		$voucher_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_voucher_id'] = $voucher_id;
	}

	public function editVoucher(int $voucher_id, array $data = []): void {
		$voucher_info = $this->getVoucher($voucher_id);

		// A code that has not been changed is kept exactly as it is stored (older codes may contain other characters)
		if ($voucher_info && ($voucher_info['code'] === $data['code'])) {
			$code = $voucher_info['code'];
		} else {
			$code = voucherCodeNormalise($data['code']);
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "voucher` SET `code` = '" . $this->db->escape($code) . "', from_name = '" . $this->db->escape($data['from_name']) . "', from_email = '" . $this->db->escape($data['from_email']) . "', to_name = '" . $this->db->escape($data['to_name']) . "', to_email = '" . $this->db->escape($data['to_email']) . "', design = '" . $this->db->escape($data['design']) . "', message = '" . $this->db->escape($data['message']) . "', amount = '" . (float)$data['amount'] . "', status = '" . (int)$data['status'] . "', date_expire = " . $this->getExpiryValue($data) . " WHERE voucher_id = '" . (int)$voucher_id . "'");
	}

	public function deleteVoucher(int $voucher_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "voucher` WHERE voucher_id = '" . (int)$voucher_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "voucher_history` WHERE voucher_id = '" . (int)$voucher_id . "'");
	}

	public function getVoucher(int $voucher_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "voucher` WHERE voucher_id = '" . (int)$voucher_id . "'");

		return $query->row;
	}

	/**
	 * Find a voucher by code. Case, spaces and hyphens are ignored.
	 */
	public function getVoucherByCode($code) {
		$normalised = voucherCodeNormalise($code);

		if ($normalised === '') {
			return [];
		}

		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "voucher` WHERE `code` = '" . $this->db->escape($normalised) . "' OR REPLACE(REPLACE(`code`, '-', ''), ' ', '') = '" . $this->db->escape($normalised) . "'");

		return $query->row;
	}

	/**
	 * Generate a secure code that is not used by any voucher or pending order voucher.
	 */
	public function generateCode(): string {
		for ($i = 0; $i < 20; $i++) {
			$code = voucherCodeGenerate();

			$voucher_query = $this->db->query("SELECT voucher_id FROM `" . DB_PREFIX . "voucher` WHERE `code` = '" . $this->db->escape($code) . "'");

			$order_voucher_query = $this->db->query("SELECT order_voucher_id FROM `" . DB_PREFIX . "order_voucher` WHERE `code` = '" . $this->db->escape($code) . "'");

			if (!$voucher_query->num_rows && !$order_voucher_query->num_rows) {
				return $code;
			}
		}

		throw new \RuntimeException('Error: Could not generate a unique voucher code!');
	}

	/**
	 * Voucher state: disabled, pending (order not complete), used, expired or active.
	 */
	public function getVoucherState(int $voucher_id): string {
		$query = $this->db->query("SELECT " . $this->getStateSql() . " AS `state` FROM `" . DB_PREFIX . "voucher` v WHERE v.voucher_id = '" . (int)$voucher_id . "'");

		return $query->num_rows ? $query->row['state'] : '';
	}

	public function getVouchers(array $data = []): array {
		$sql = $this->getListSql($data);

		$sort_data = [
			'v.code',
			'v.from_name',
			'v.to_name',
			'v.design',
			'v.amount',
			'balance',
			'state',
			'v.date_added',
			'v.date_sent',
			'v.date_expire'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data, true)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY v.date_added";
		}

		if (isset($data['order']) && ($data['order'] === 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalVouchers(array $data = []): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM (" . $this->getListSql($data) . ") t");

		return (int)$query->row['total'];
	}

	/**
	 * Email the voucher to the recipient.
	 *
	 * The first send starts the validity: date_sent is set and, when an expiry period is configured and no
	 * expiry date was entered, date_expire is set. Sending again never changes either date.
	 */
	public function sendVoucher(int $voucher_id): bool {
		$voucher_info = $this->getVoucher($voucher_id);

		if (!$voucher_info) {
			return false;
		}

		$first_send = empty($voucher_info['date_sent']);

		if ($first_send) {
			$months = (int)$this->config->get('config_voucher_expiry');

			$this->db->query("UPDATE `" . DB_PREFIX . "voucher` SET date_sent = NOW(), date_expire = " . (($voucher_info['date_expire'] === null && $months > 0) ? "DATE_ADD(NOW(), INTERVAL " . $months . " MONTH)" : "date_expire") . " WHERE voucher_id = '" . (int)$voucher_id . "'");

			$original = $voucher_info;

			$voucher_info = $this->getVoucher($voucher_id);
		}

		try {
			$this->sendVoucherEmail($voucher_info);

		} catch (\Throwable $e) {
			// Nothing was sent, so the validity period must not start
			if ($first_send) {
				$expire = ($original['date_expire'] === null) ? "NULL" : "'" . $this->db->escape($original['date_expire']) . "'";

				$this->db->query("UPDATE `" . DB_PREFIX . "voucher` SET date_sent = NULL, date_expire = " . $expire . " WHERE voucher_id = '" . (int)$voucher_id . "'");
			}

			error_log('Voucher email failed (voucher ' . (int)$voucher_id . '): ' . $e->getMessage());

			return false;
		}

		return true;
	}

	public function getVoucherHistories(int $voucher_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT vh.order_id, CONCAT(o.firstname, ' ', o.lastname) AS `customer`, vh.amount, vh.date_added FROM `" . DB_PREFIX . "voucher_history` vh LEFT JOIN `" . DB_PREFIX . "order` o ON (vh.order_id = o.order_id) WHERE vh.voucher_id = '" . (int)$voucher_id . "' ORDER BY vh.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalVoucherHistories(int $voucher_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "voucher_history` WHERE voucher_id = '" . (int)$voucher_id . "'");

		return $query->row['total'];
	}

	/**
	 * Remaining balance of a voucher (amount plus the negative redemption history).
	 */
	public function getVoucherBalance(int $voucher_id): float {
		$query = $this->db->query("SELECT v.amount + COALESCE((SELECT SUM(vh.amount) FROM `" . DB_PREFIX . "voucher_history` vh WHERE vh.voucher_id = v.voucher_id), 0) AS `balance` FROM `" . DB_PREFIX . "voucher` v WHERE v.voucher_id = '" . (int)$voucher_id . "'");

		return $query->num_rows ? (float)$query->row['balance'] : 0.0;
	}

	/**
	 * Private helpers
	 */
	private function getExpiryValue(array $data): string {
		if (!empty($data['date_expire']) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $data['date_expire'], $match) && checkdate((int)$match[2], (int)$match[3], (int)$match[1])) {
			return "'" . $this->db->escape($data['date_expire']) . " 23:59:59'";
		}

		return "NULL";
	}

	private function getStateSql(): string {
		return "CASE
			WHEN v.status = '0' THEN 'disabled'
			WHEN (v.order_id > 0 AND NOT EXISTS (SELECT 1 FROM `" . DB_PREFIX . "order` o WHERE o.order_id = v.order_id AND o.order_status_id = '" . (int)$this->config->get('config_complete_status_id') . "')) THEN 'pending'
			WHEN (v.amount + COALESCE((SELECT SUM(vh.amount) FROM `" . DB_PREFIX . "voucher_history` vh WHERE vh.voucher_id = v.voucher_id), 0)) <= 0 THEN 'used'
			WHEN (v.date_expire IS NOT NULL AND v.date_expire <= NOW()) THEN 'expired'
			ELSE 'active'
		END";
	}

	private function getListSql(array $data): string {
		$sql = "SELECT v.voucher_id, v.order_id, v.code, v.from_name, v.from_email, v.to_name, v.to_email, v.design, v.amount, (v.amount + COALESCE((SELECT SUM(vh.amount) FROM `" . DB_PREFIX . "voucher_history` vh WHERE vh.voucher_id = v.voucher_id), 0)) AS `balance`, (SELECT MAX(vh.date_added) FROM `" . DB_PREFIX . "voucher_history` vh WHERE vh.voucher_id = v.voucher_id) AS `date_redeemed`, v.status, v.date_added, v.date_sent, v.date_expire, " . $this->getStateSql() . " AS `state` FROM `" . DB_PREFIX . "voucher` v";

		$implode = [];

		if (!empty($data['filter_code'])) {
			$implode[] = "REPLACE(REPLACE(v.`code`, '-', ''), ' ', '') LIKE '" . $this->db->escape(voucherCodeNormalise($data['filter_code'])) . "%'";
		}

		if (!empty($data['filter_name'])) {
			$name = $this->db->escape($data['filter_name']);

			$implode[] = "(v.from_name LIKE '" . $name . "%' OR v.to_name LIKE '" . $name . "%' OR v.from_email LIKE '" . $name . "%' OR v.to_email LIKE '" . $name . "%')";
		}

		if (!empty($data['filter_date_expire'])) {
			$implode[] = "DATE(v.date_expire) = '" . $this->db->escape($data['filter_date_expire']) . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(v.date_added) = '" . $this->db->escape($data['filter_date_added']) . "'";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		if (!empty($data['filter_state'])) {
			$sql .= " HAVING `state` = '" . $this->db->escape($data['filter_state']) . "'";
		}

		return $sql;
	}

	private function sendVoucherEmail(array $voucher_info): void {
		$order_info = [];

		if ($voucher_info['order_id']) {
			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($voucher_info['order_id']);
		}

		if ($order_info) {
			$language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('mail/voucher');

			$amount = $this->currency->format($voucher_info['amount'], $order_info['currency_code'], $order_info['currency_value']);
			$store_name = $order_info['store_name'];
			$store_url = $order_info['store_url'];

		} else {
			$this->language->load('mail/voucher');

			$language = $this->language;

			$amount = $this->currency->format($voucher_info['amount'], $this->config->get('config_currency'));
			$store_name = $this->config->get('config_name');
			$store_url = HTTPS_CATALOG;
		}

		$design = voucherDesignIsValid($voucher_info['design']) ? $voucher_info['design'] : 'general';

		$date_format = $language->get('date_format_long');

		if ($date_format === 'date_format_long') {
			$date_format = 'j F Y';
		}

		$file = DIR_CATALOG . 'view/theme/' . $this->config->get('config_template') . '/template/mail/voucher.tpl';

		if (!is_file($file)) {
			$file = DIR_CATALOG . 'view/theme/default/template/mail/voucher.tpl';
		}

		$html = voucherEmailHtml($file, [
			'title'         => sprintf($language->get('text_subject'), $voucher_info['from_name']),
			'store_name'    => $store_name,
			'store_url'     => $store_url,
			'design'        => $design,
			'text_design'   => $language->get('text_design_' . $design),
			'text_greeting' => sprintf($language->get('text_greeting'), $voucher_info['to_name']),
			'text_from'     => sprintf($language->get('text_from'), $voucher_info['from_name']),
			'text_message'  => $language->get('text_message'),
			'text_amount'   => $language->get('text_amount'),
			'text_code'     => $language->get('text_code'),
			'text_redeem'   => $language->get('text_redeem'),
			'text_button'   => $language->get('text_button'),
			'text_expiry'   => $voucher_info['date_expire'] ? sprintf($language->get('text_expiry'), date($date_format, strtotime($voucher_info['date_expire']))) : $language->get('text_no_expiry'),
			'text_footer'   => $language->get('text_footer'),
			'amount'        => $amount,
			'code'          => voucherCodeFormat($voucher_info['code']),
			'message'       => nl2br($voucher_info['message']),
			'redeem_url'    => $store_url . 'index.php?route=checkout/cart&voucher=' . urlencode(voucherCodeNormalise($voucher_info['code']))
		]);

		$mail = new Mail();
		$mail->setTo($voucher_info['to_email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $voucher_info['from_name']), ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($html);
		$mail->send();
	}
}
