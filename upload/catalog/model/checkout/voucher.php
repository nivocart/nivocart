<?php
/**
 * Class ModelCheckoutVoucher
 *
 * @package NivoCart
 */
class ModelCheckoutVoucher extends Model {
	/**
	 * Functions Add, Generate, Get, Confirm, Redeem
	 */
	public function addVoucher(int $order_id, array $data = []): int {
		$design = (!empty($data['design']) && voucherDesignIsValid($data['design'])) ? $data['design'] : 'general';

		$code = voucherCodeNormalise($data['code'] ?? '');

		if ($code === '') {
			$code = $this->generateCode();
		}

		$customer_id = 0;

		if ($order_id) {
			$order_query = $this->db->query("SELECT customer_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");

			if ($order_query->num_rows) {
				$customer_id = (int)$order_query->row['customer_id'];
			}
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "voucher` SET order_id = '" . (int)$order_id . "', customer_id = '" . $customer_id . "', `code` = '" . $this->db->escape($code) . "', from_name = '" . $this->db->escape($data['from_name']) . "', from_email = '" . $this->db->escape($data['from_email']) . "', to_name = '" . $this->db->escape($data['to_name']) . "', to_email = '" . $this->db->escape($data['to_email']) . "', design = '" . $this->db->escape($design) . "', message = '" . $this->db->escape($data['message']) . "', amount = '" . (float)$data['amount'] . "', status = '1', date_added = NOW()");

		return (int)$this->db->getLastId();
	}

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

	public function getVoucher($code) {
		$code = voucherCodeNormalise($code);

		if ($code === '') {
			return [];
		}

		$voucher_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "voucher` WHERE REPLACE(REPLACE(`code`, '-', ''), ' ', '') = '" . $this->db->escape($code) . "' AND status = '1' AND (date_expire IS NULL OR date_expire > NOW())");

		if (!$voucher_query->num_rows) {
			return [];
		}

		if ($voucher_query->row['order_id']) {
			$order_query = $this->db->query("SELECT order_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$voucher_query->row['order_id'] . "' AND order_status_id = '" . (int)$this->config->get('config_complete_status_id') . "'");

			if (!$order_query->num_rows) {
				return [];
			}

			$order_voucher_query = $this->db->query("SELECT order_voucher_id FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$voucher_query->row['order_id'] . "' AND voucher_id = '" . (int)$voucher_query->row['voucher_id'] . "'");

			if (!$order_voucher_query->num_rows) {
				return [];
			}
		}

		$voucher_history_query = $this->db->query("SELECT SUM(amount) AS `total` FROM `" . DB_PREFIX . "voucher_history` WHERE voucher_id = '" . (int)$voucher_query->row['voucher_id'] . "'");

		$amount = (float)$voucher_query->row['amount'] + (float)$voucher_history_query->row['total'];

		if ($amount <= 0) {
			return [];
		}

		return [
			'voucher_id' => $voucher_query->row['voucher_id'],
			'code'       => $voucher_query->row['code'],
			'from_name'  => $voucher_query->row['from_name'],
			'from_email' => $voucher_query->row['from_email'],
			'to_name'    => $voucher_query->row['to_name'],
			'to_email'   => $voucher_query->row['to_email'],
			'design'     => $voucher_query->row['design'],
			'message'    => $voucher_query->row['message'],
			'amount'     => round($amount, 2, PHP_ROUND_HALF_UP),
			'status'     => $voucher_query->row['status'],
			'date_added' => $voucher_query->row['date_added'],
			'date_sent'   => $voucher_query->row['date_sent'],
			'date_expire' => $voucher_query->row['date_expire']
		];
	}

	/**
	 * Email every voucher of a completed order that has not been sent yet.
	 * The validity period starts at the moment the email is sent.
	 * Calling this again for the same order never sends a voucher twice.
	 */
	public function confirm(int $order_id): void {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if (!$order_info) {
			return;
		}

		$language = new Language($order_info['language_directory']);

		$language->load($order_info['language_filename']);
		$language->load('mail/voucher');

		$months = (int)$this->config->get('config_voucher_expiry');

		$voucher_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "voucher` WHERE order_id = '" . (int)$order_id . "' AND status = '1' AND date_sent IS NULL");

		foreach ($voucher_query->rows as $voucher) {
			$original_expire = $voucher['date_expire'];

			// Claim the voucher first so that two requests can not both send it
			$this->db->query("UPDATE `" . DB_PREFIX . "voucher` SET date_sent = NOW(), date_expire = " . (($original_expire === null && $months > 0) ? "DATE_ADD(NOW(), INTERVAL " . $months . " MONTH)" : "date_expire") . " WHERE voucher_id = '" . (int)$voucher['voucher_id'] . "' AND date_sent IS NULL");

			if (!$this->db->countAffected()) {
				continue;
			}

			$sent_query = $this->db->query("SELECT date_expire FROM `" . DB_PREFIX . "voucher` WHERE voucher_id = '" . (int)$voucher['voucher_id'] . "'");

			$voucher['date_expire'] = $sent_query->row['date_expire'];

			try {
				$this->sendVoucherEmail($voucher, $order_info, $language);
			} catch (\Throwable $e) {
				// Nothing was sent, so release the voucher; it can be sent again from Admin
				$restore = ($original_expire === null) ? "NULL" : "'" . $this->db->escape($original_expire) . "'";

				$this->db->query("UPDATE `" . DB_PREFIX . "voucher` SET date_sent = NULL, date_expire = " . $restore . " WHERE voucher_id = '" . (int)$voucher['voucher_id'] . "'");

				error_log('Voucher email failed (voucher ' . (int)$voucher['voucher_id'] . '): ' . $e->getMessage());

				continue;
			}

			try {
				$this->sendPurchaserEmail($voucher, $order_info, $language);
			} catch (\Throwable $e) {
				error_log('Voucher purchaser email failed (voucher ' . (int)$voucher['voucher_id'] . '): ' . $e->getMessage());
			}
		}
	}

	/**
	 * Gift vouchers bought by a customer, with balance and state.
	 */
	public function getVouchersByCustomer(int $customer_id): array {
		if (!$customer_id) {
			return [];
		}

		$query = $this->db->query("SELECT v.voucher_id, v.to_name, v.to_email, v.amount, v.date_sent, v.date_expire, (v.amount + COALESCE((SELECT SUM(vh.amount) FROM `" . DB_PREFIX . "voucher_history` vh WHERE vh.voucher_id = v.voucher_id), 0)) AS `balance`, CASE
			WHEN v.status = '0' THEN 'disabled'
			WHEN (v.order_id > 0 AND NOT EXISTS (SELECT 1 FROM `" . DB_PREFIX . "order` o WHERE o.order_id = v.order_id AND o.order_status_id = '" . (int)$this->config->get('config_complete_status_id') . "')) THEN 'pending'
			WHEN (v.amount + COALESCE((SELECT SUM(vh2.amount) FROM `" . DB_PREFIX . "voucher_history` vh2 WHERE vh2.voucher_id = v.voucher_id), 0)) <= 0 THEN 'used'
			WHEN (v.date_expire IS NOT NULL AND v.date_expire <= NOW()) THEN 'expired'
			ELSE 'active'
		END AS `state` FROM `" . DB_PREFIX . "voucher` v WHERE v.customer_id = '" . (int)$customer_id . "' ORDER BY v.date_added DESC, v.voucher_id DESC");

		return $query->rows;
	}

	public function redeem(int $voucher_id, int $order_id, float $amount): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "voucher_history` SET voucher_id = '" . (int)$voucher_id . "', order_id = '" . (int)$order_id . "', amount = '" . (float)$amount . "', date_added = NOW()");
	}

	private function getTemplateFile(string $name): string {
		$file = DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/' . $name;

		if (!is_file($file)) {
			$file = DIR_TEMPLATE . 'default/template/mail/' . $name;
		}

		return $file;
	}

	private function getDateFormat(Language $language): string {
		$date_format = $language->get('date_format_long');

		return ($date_format === 'date_format_long') ? 'j F Y' : $date_format;
	}

	private function sendVoucherEmail(array $voucher, array $order_info, Language $language): void {
		$design = voucherDesignIsValid($voucher['design']) ? $voucher['design'] : 'general';

		$date_format = $this->getDateFormat($language);

		$html = voucherEmailHtml($this->getTemplateFile('voucher.tpl'), [
			'title'         => sprintf($language->get('text_subject'), $voucher['from_name']),
			'store_name'    => $order_info['store_name'],
			'store_url'     => $order_info['store_url'],
			'design'        => $design,
			'text_design'   => $language->get('text_design_' . $design),
			'text_greeting' => sprintf($language->get('text_greeting'), $voucher['to_name']),
			'text_from'     => sprintf($language->get('text_from'), $voucher['from_name']),
			'text_message'  => $language->get('text_message'),
			'text_amount'   => $language->get('text_amount'),
			'text_code'     => $language->get('text_code'),
			'text_redeem'   => $language->get('text_redeem'),
			'text_button'   => $language->get('text_button'),
			'text_expiry'   => $voucher['date_expire'] ? sprintf($language->get('text_expiry'), date($date_format, strtotime($voucher['date_expire']))) : $language->get('text_no_expiry'),
			'text_footer'   => $language->get('text_footer'),
			'amount'        => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
			'code'          => voucherCodeFormat($voucher['code']),
			'message'       => nl2br($voucher['message']),
			'redeem_url'    => $order_info['store_url'] . 'index.php?route=checkout/cart&voucher=' . urlencode(voucherCodeNormalise($voucher['code']))
		]);

		$mail = new Mail();
		$mail->setTo($voucher['to_email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $voucher['from_name']), ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($html);
		$mail->send();
	}

	private function sendPurchaserEmail(array $voucher, array $order_info, Language $language): void {
		if (empty($order_info['email'])) {
			return;
		}

		$date_format = $this->getDateFormat($language);

		$html = voucherEmailHtml($this->getTemplateFile('voucher_sent.tpl'), [
			'title'          => sprintf($language->get('text_sent_subject'), $voucher['to_name']),
			'store_name'     => $order_info['store_name'],
			'store_url'      => $order_info['store_url'],
			'text_greeting'  => sprintf($language->get('text_sent_greeting'), $order_info['firstname']),
			'text_intro'     => sprintf($language->get('text_sent_intro'), $voucher['to_email']),
			'text_value'     => $language->get('text_sent_value'),
			'text_recipient' => $language->get('text_sent_recipient'),
			'text_expiry'    => $voucher['date_expire'] ? sprintf($language->get('text_sent_expiry'), date($date_format, strtotime($voucher['date_expire']))) : $language->get('text_no_expiry'),
			'text_footer'    => $language->get('text_sent_footer'),
			'amount'         => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
			'to_name'        => $voucher['to_name']
		]);

		$mail = new Mail();
		$mail->setTo($order_info['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($language->get('text_sent_subject'), $voucher['to_name']), ENT_QUOTES, 'UTF-8'));
		$mail->setHtml($html);
		$mail->send();
	}
}
