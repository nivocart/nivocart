<?php
class ModelSaleAffiliate extends Model {

	public function addAffiliate(array $data = []): void {
		$this->db->query("INSERT INTO " . DB_PREFIX . "affiliate SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', salt = '" . $this->db->escape($salt = mb_substr(md5(uniqid(rand(), true)), 0, 9, 'UTF-8')) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', company = '" . $this->db->escape($data['company']) . "', website = '" . $this->db->escape($data['website']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', code = '" . $this->db->escape($data['code']) . "', commission = '" . (float)$data['commission'] . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

		$affiliate_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_affiliate_id'] = $affiliate_id;
	}

	public function editAffiliate(int $affiliate_id, array $data = []): void {
		$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', company = '" . $this->db->escape($data['company']) . "', website = '" . $this->db->escape($data['website']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', code = '" . $this->db->escape($data['code']) . "', commission = '" . (float)$data['commission'] . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', status = '" . (int)$data['status'] . "' WHERE affiliate_id = '" . (int)$affiliate_id . "'");

		if ($data['password']) {
			$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET salt = '" . $this->db->escape($salt = mb_substr(md5(uniqid(rand(), true)), 0, 9, 'UTF-8')) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE affiliate_id = '" . (int)$affiliate_id . "'");
		}
	}

	public function deleteAffiliate(int $affiliate_id): void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate_activity WHERE affiliate_id = '" . (int)$affiliate_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "'");
	}

	public function getAffiliate(int $affiliate_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");

		return $query->row;
	}

	public function getAffiliateByEmail($email) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "affiliate WHERE LCASE(email) = '" . $this->db->escape(mb_strtolower($email, 'UTF-8')) . "'");

		return $query->row;
	}

	public function getAffiliates(array $data = []): array {
		$sql = "SELECT *, CONCAT(a.firstname, ' ', a.lastname) AS name, (SELECT SUM(at.amount) FROM " . DB_PREFIX . "affiliate_transaction at WHERE at.affiliate_id = a.affiliate_id GROUP BY at.affiliate_id) AS balance FROM " . DB_PREFIX . "affiliate a";

		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(a.firstname, ' ', a.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "LOWER(a.email) LIKE '" . $this->db->escape(mb_strtolower($data['filter_email'], 'UTF-8')) . "%'";
		}

		if (!empty($data['filter_code'])) {
			$implode[] = "a.code = '" . $this->db->escape($data['filter_code']) . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "a.approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(a.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "a.status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($implode)) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'a.email',
			'a.code',
			'a.approved',
			'a.date_added',
			'a.status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
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

	public function approve(int $affiliate_id): void {
		$affiliate_info = $this->getAffiliate($affiliate_id);

		if ($affiliate_info) {
			$this->db->query("UPDATE " . DB_PREFIX . "affiliate SET approved = '1' WHERE affiliate_id = '" . (int)$affiliate_id . "'");

			$this->language->load('mail/affiliate');

			$message = sprintf($this->language->get('text_approve_welcome'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')) . "\n\n";
			$message .= $this->language->get('text_approve_login') . "\n";
			$message .= ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . 'index.php?route=affiliate/login' . "\n\n";
			$message .= $this->language->get('text_approve_services') . "\n\n";
			$message .= $this->language->get('text_approve_thanks') . "\n";
			$message .= html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			// HTML Mail
			$subject = html_entity_decode(sprintf($this->language->get('text_approve_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8');

			$template = new Template();

			$template->data['title'] = $subject;
			$template->data['logo'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . 'image/' . $this->config->get('config_logo');
			$template->data['store_name'] = $this->config->get('config_name');
			$template->data['store_url'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG);
			$template->data['message'] = nl2br($message);

			$html = $template->fetch('mail/default.tpl');

			$mail = new Mail();
			$mail->setTo($affiliate_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml($html);
			$mail->send();
		}
	}

	public function getAffiliatesByNewsletter(): array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate WHERE newsletter = '1' ORDER BY firstname, lastname, email");

		return $query->rows;
	}

	public function getTotalAffiliates(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM " . DB_PREFIX . "affiliate";

		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "LOWER(email) LIKE '" . $this->db->escape(mb_strtolower($data['filter_email'], 'UTF-8')) . "%'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "status = '" . (int)$data['filter_status'] . "'";
		}

		if (!empty($implode)) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalAffiliatesAwaitingApproval(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . DB_PREFIX . "affiliate WHERE status = '0' OR approved = '0'");

		return $query->row['total'];
	}

	public function getTotalAffiliatesByCountryId(int $country_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . DB_PREFIX . "affiliate WHERE country_id = '" . (int)$country_id . "'");

		return $query->row['total'];
	}

	public function getTotalAffiliatesByZoneId(int $zone_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . DB_PREFIX . "affiliate WHERE zone_id = '" . (int)$zone_id . "'");

		return $query->row['total'];
	}

	// Product
	public function getAffiliateProducts(int $affiliate_id): array {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate_product WHERE affiliate_id = '" . (int)$affiliate_id . "'");

		return $query->rows;
	}

	public function getTotalAffiliateProducts(int $affiliate_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . DB_PREFIX . "affiliate_product WHERE affiliate_id = '" . (int)$affiliate_id . "'");

		return $query->row['total'];
	}

	// Transaction
	public function addTransaction(int $affiliate_id, $description = '', $amount = '', int $order_id = 0) {
		$affiliate_info = $this->getAffiliate($affiliate_id);

		if ($affiliate_info) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "affiliate_transaction SET affiliate_id = '" . (int)$affiliate_id . "', order_id = '" . (float)$order_id . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$amount . "', date_added = NOW()");

			$affiliate_transaction_id = $this->db->getLastId();

			$this->language->load('mail/affiliate');

			$message = sprintf($this->language->get('text_transaction_received'), $this->currency->format($amount, $this->config->get('config_currency'))) . "\n\n";
			$message .= sprintf($this->language->get('text_transaction_total'), $this->currency->format($this->getTransactionTotal($affiliate_id), $this->config->get('config_currency')));

			// HTML Mail
			$subject = html_entity_decode(sprintf($this->language->get('text_transaction_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8');

			$template = new Template();

			$template->data['title'] = $subject;
			$template->data['logo'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . 'image/' . $this->config->get('config_logo');
			$template->data['store_name'] = $this->config->get('config_name');
			$template->data['store_url'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG);
			$template->data['message'] = nl2br($message);

			$html = $template->fetch('mail/default.tpl');

			$mail = new Mail();
			$mail->setTo($affiliate_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml($html);
			$mail->send();

			return $affiliate_transaction_id;
		}
	}

	public function deleteTransactionById(int $affiliate_transaction_id): void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_transaction_id = '" . (int)$affiliate_transaction_id . "'");
	}

	public function getTransactions(int $affiliate_id, int $start = 0, int $limit = 10): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalTransactions(int $affiliate_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total`  FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "'");

		return $query->row['total'];
	}

	public function getTransactionTotal(int $affiliate_id): int {
		$query = $this->db->query("SELECT SUM(amount) AS `total` FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "'");

		return $query->row['total'];
	}

	public function getTotalTransactionsByOrderId(int $order_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . DB_PREFIX . "affiliate_transaction WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	// Login
	public function getLoginAttempts($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate_login WHERE LOWER(email) = '" . $this->db->escape(mb_strtolower($email, 'UTF-8')) . "'");

		return $query->row;
	}

	public function deleteLoginAttempts($email): void {
		$this->db->query("DELETE FROM " . DB_PREFIX . "affiliate_login WHERE LOWER(email) = '" . $this->db->escape(mb_strtolower($email, 'UTF-8')) . "'");
	}
}
