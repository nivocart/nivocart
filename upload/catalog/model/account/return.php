<?php
/**
 * Class ModelAccountReturn
 *
 * @package NivoCart
 */
class ModelAccountReturn extends Model {
	/**
	 * Functions Get, Add
	 */
	public function addReturn(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "return` SET
			order_id = '" . (int)$data['order_id'] . "',
			customer_id = '" . (int)($data['customer_id'] ?? $this->customer->getId()) . "',
			firstname = '" . $this->db->escape((string)$data['firstname']) . "',
			lastname = '" . $this->db->escape((string)$data['lastname']) . "',
			email = '" . $this->db->escape((string)$data['email']) . "',
			telephone = '" . $this->db->escape($data['telephone']) . "',
			product = '" . $this->db->escape($data['product']) . "',
			model = '" . $this->db->escape($data['model']) . "',
			quantity = '" . (int)$data['quantity'] . "',
			opened = '" . (int)$data['opened'] . "',
			return_reason_id = '" . (int)$data['return_reason_id'] . "',
			return_action_id = '" . (int)($data['return_action_id'] ?? 0) . "',
			return_status_id = '" . (int)$this->config->get('config_return_status_id') . "',
			`comment` = '" . $this->db->escape($data['comment']) . "',
			date_ordered = '" . $this->db->escape($data['date_ordered']) . "',
			date_added = NOW(),
			date_modified = NOW()");

		$return_id = $this->db->getLastId();

		// Include a self-service track URL for guest returns (no account to log in to)
		$track_url = '';

		if (isset($data['customer_id']) && (int)$data['customer_id'] === 0) {
			$store_url = $this->config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER;
			$track_url = $store_url . 'index.php?route=account/return/guestTrack';
		}

		// Send acknowledgment email — required by CCR 2013 Regulation 27(2)
		$mail = new Mail();
		$mail->setTo($data['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_return_subject'), $this->config->get('config_name'), $return_id), ENT_QUOTES, 'UTF-8'));
		$mail->setText($this->buildReturnAcknowledgmentText($return_id, $data, $track_url));
		$mail->send();
	}

	private function buildReturnAcknowledgmentText(int $return_id, array $data, string $track_url = ''): string {
		$track_section = $track_url ? "To check the status of your return at any time, visit:\r\n" . $track_url . "\r\n\r\n" : '';

		return sprintf(
			"Dear %s %s,\r\n\r\n" .
			"We have received your return request and can confirm it has been logged.\r\n\r\n" .
			"Return Reference: #%d\r\n" .
			"Order ID:         #%d\r\n" .
			"Product:          %s\r\n" .
			"Submitted:        %s\r\n\r\n" .
			"Please keep this email as your durable record of notification. We will be in touch shortly.\r\n\r\n" .
			"If you are exercising your right to cancel under the Consumer Contracts Regulations 2013, " .
			"your 14-day cancellation window is counted from the date above.\r\n\r\n" .
			"%s" .
			"Regards,\r\n%s",
			html_entity_decode($data['firstname'], ENT_QUOTES, 'UTF-8'),
			html_entity_decode($data['lastname'], ENT_QUOTES, 'UTF-8'),
			$return_id,
			(int)$data['order_id'],
			html_entity_decode($data['product'], ENT_QUOTES, 'UTF-8'),
			date('d M Y H:i'),
			$track_section,
			html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')
		);
	}

	public function getReturn(int $return_id) {
		$query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, r.email, r.telephone, r.product, r.model, r.quantity, r.opened, (SELECT rr.name FROM `" . DB_PREFIX . "return_reason` rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = '" . (int)$this->config->get('config_language_id') . "') AS `reason`, (SELECT ra.name FROM `" . DB_PREFIX . "return_action` ra WHERE ra.return_action_id = r.return_action_id AND ra.language_id = '" . (int)$this->config->get('config_language_id') . "') AS `action`, (SELECT rs.name FROM `" . DB_PREFIX . "return_status` rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status, r.`comment`, r.date_ordered, r.date_added, r.date_modified FROM `" . DB_PREFIX . "return` r WHERE r.return_id = '" . (int)$return_id . "' AND r.customer_id = '" . $this->customer->getId() . "'");

		return $query->row;
	}

	public function getReturns($start = 0, $limit = 20): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, rs.name as status, r.date_added FROM `" . DB_PREFIX . "return` r LEFT JOIN `" . DB_PREFIX . "return_status` rs ON (r.return_status_id = rs.return_status_id) WHERE r.customer_id = '" . $this->customer->getId() . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.return_id DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalReturns(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "return` WHERE customer_id = '" . $this->customer->getId() . "'");

		if ($query->num_rows) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function getReturnHistories(int $return_id): array {
		$query = $this->db->query("SELECT rh.date_added, rs.name AS `status`, rh.`comment`, rh.notify FROM `" . DB_PREFIX . "return_history` rh LEFT JOIN `" . DB_PREFIX . "return_status` rs ON (rh.return_status_id = rs.return_status_id) WHERE rh.return_id = '" . (int)$return_id . "' AND rh.notify = '1' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY rh.date_added ASC");

		return $query->rows;
	}

	/**
	 * Look up returns for a guest by order_id + email address (case-insensitive).
	 * Used by the guest return tracking page.
	 */
	public function getReturnsByGuest(int $order_id, string $email): array {
		$query = $this->db->query("SELECT r.return_id, r.product, " . "(SELECT rr.name FROM `" . DB_PREFIX . "return_reason` rr WHERE rr.return_reason_id = r.return_reason_id AND rr.language_id = '" . (int)$this->config->get('config_language_id') . "') AS reason, " . "(SELECT rs.name FROM `" . DB_PREFIX . "return_status` rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status, " . "r.date_added " . "FROM `" . DB_PREFIX . "return` r " . "WHERE r.order_id = '" . (int)$order_id . "' " . "AND LOWER(r.email) = LOWER('" . $this->db->escape($email) . "') " . "ORDER BY r.return_id DESC");

		return $query->rows;
	}
}
