<?php
/**
 * Class ModelFraudFraudLabsPro
 *
 * @package NivoCart
 */
class ModelFraudFraudLabsPro extends Model {
	/**
	 * Functions Install, Uninstall, Add, Get, SendFeedback
	 */
	public function install(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "fraudlabspro` (
			  `order_id` VARCHAR(11) NOT NULL,
			  `is_country_match` CHAR(2) NOT NULL DEFAULT '',
			  `is_high_risk_country` CHAR(2) NOT NULL DEFAULT '',
			  `distance_in_km` VARCHAR(10) NOT NULL DEFAULT '',
			  `distance_in_mile` VARCHAR(10) NOT NULL DEFAULT '',
			  `ip_address` VARCHAR(39) NOT NULL DEFAULT '',
			  `ip_country` VARCHAR(2) NOT NULL DEFAULT '',
			  `ip_country_name` VARCHAR(80) NOT NULL DEFAULT '',
			  `ip_continent` VARCHAR(20) NOT NULL DEFAULT '',
			  `ip_region` VARCHAR(21) NOT NULL DEFAULT '',
			  `ip_city` VARCHAR(21) NOT NULL DEFAULT '',
			  `ip_latitude` VARCHAR(21) NOT NULL DEFAULT '',
			  `ip_longitude` VARCHAR(21) NOT NULL DEFAULT '',
			  `ip_timezone` VARCHAR(10) NOT NULL DEFAULT '',
			  `ip_elevation` VARCHAR(10) NOT NULL DEFAULT '',
			  `ip_domain` VARCHAR(50) NOT NULL DEFAULT '',
			  `ip_mobile_mnc` VARCHAR(100) NOT NULL DEFAULT '',
			  `ip_mobile_mcc` VARCHAR(100) NOT NULL DEFAULT '',
			  `ip_mobile_brand` VARCHAR(100) NOT NULL DEFAULT '',
			  `ip_netspeed` VARCHAR(10) NOT NULL DEFAULT '',
			  `ip_isp_name` VARCHAR(50) NOT NULL DEFAULT '',
			  `ip_usage_type` VARCHAR(30) NOT NULL DEFAULT '',
			  `is_free_email` CHAR(2) NOT NULL DEFAULT '',
			  `is_new_domain_name` CHAR(2) NOT NULL DEFAULT '',
			  `is_proxy_ip_address` CHAR(2) NOT NULL DEFAULT '',
			  `is_ip_blacklist` CHAR(2) NOT NULL DEFAULT '',
			  `is_bin_found` CHAR(2) NOT NULL DEFAULT '',
			  `is_bin_country_match` CHAR(2) NOT NULL DEFAULT '',
			  `is_bin_name_match` CHAR(2) NOT NULL DEFAULT '',
			  `is_bin_phone_match` CHAR(2) NOT NULL DEFAULT '',
			  `is_bin_prepaid` CHAR(2) NOT NULL DEFAULT '',
			  `is_address_ship_forward` CHAR(2) NOT NULL DEFAULT '',
			  `is_bill_ship_city_match` CHAR(2) NOT NULL DEFAULT '',
			  `is_bill_ship_state_match` CHAR(2) NOT NULL DEFAULT '',
			  `is_bill_ship_country_match` CHAR(2) NOT NULL DEFAULT '',
			  `is_bill_ship_postal_match` CHAR(2) NOT NULL DEFAULT '',
			  `ship_export_controlled` CHAR(2) NOT NULL DEFAULT '',
			  `ship_in_blacklist` CHAR(2) NOT NULL DEFAULT '',
			  `is_email_blacklist` CHAR(2) NOT NULL DEFAULT '',
			  `is_credit_card_blacklist` CHAR(2) NOT NULL DEFAULT '',
			  `is_device_blacklist` CHAR(2) NOT NULL DEFAULT '',
			  `is_user_blacklist` CHAR(2) NOT NULL DEFAULT '',
			  `email_is_disposable` CHAR(2) NOT NULL DEFAULT '',
			  `email_domain_exists` CHAR(2) NOT NULL DEFAULT '',
			  `phone_is_disposable` CHAR(2) NOT NULL DEFAULT '',
			  `phone_in_blacklist` CHAR(2) NOT NULL DEFAULT '',
			  `card_brand` VARCHAR(20) NOT NULL DEFAULT '',
			  `card_type` VARCHAR(20) NOT NULL DEFAULT '',
			  `fraudlabspro_score` CHAR(3) NOT NULL DEFAULT '',
			  `fraudlabspro_distribution` CHAR(3) NOT NULL DEFAULT '',
			  `fraudlabspro_status` CHAR(10) NOT NULL DEFAULT '',
			  `fraudlabspro_id` VARCHAR(50) NOT NULL DEFAULT '',
			  `fraudlabspro_error` CHAR(3) NOT NULL DEFAULT '',
			  `fraudlabspro_message` VARCHAR(50) NOT NULL DEFAULT '',
			  `fraudlabspro_credits` VARCHAR(10) NOT NULL DEFAULT '',
			  `api_key` CHAR(32) NOT NULL DEFAULT '',
			  PRIMARY KEY (`order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
		");

		// Ensure ip_address column is wide enough for IPv6
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` CHANGE COLUMN `ip_address` `ip_address` VARCHAR(39) NOT NULL DEFAULT '';");

		// Ensure fraudlabspro_id can hold v2 ID formats (may be longer than old CHAR(15))
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` CHANGE COLUMN `fraudlabspro_id` `fraudlabspro_id` VARCHAR(50) NOT NULL DEFAULT '';");

		// Add new v2 columns to existing installs (IF NOT EXISTS is safe to re-run)
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` ADD COLUMN IF NOT EXISTS `ip_country_name` VARCHAR(80) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` ADD COLUMN IF NOT EXISTS `ship_export_controlled` CHAR(2) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` ADD COLUMN IF NOT EXISTS `ship_in_blacklist` CHAR(2) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` ADD COLUMN IF NOT EXISTS `email_is_disposable` CHAR(2) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` ADD COLUMN IF NOT EXISTS `email_domain_exists` CHAR(2) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` ADD COLUMN IF NOT EXISTS `phone_is_disposable` CHAR(2) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` ADD COLUMN IF NOT EXISTS `phone_in_blacklist` CHAR(2) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` ADD COLUMN IF NOT EXISTS `card_brand` VARCHAR(20) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "fraudlabspro` ADD COLUMN IF NOT EXISTS `card_type` VARCHAR(20) NOT NULL DEFAULT '';");

		// Order Status
		$language_query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language`");

		foreach ($language_query->rows as $language) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET language_id = '" . (int)$language['language_id'] . "', `name` = 'Fraud'");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "order_status` SET language_id = '" . (int)$language['language_id'] . "', `name` = 'Fraud Review'");
		}

		$this->cache->delete('order_status');

		$this->addSettings();
	}

	public function uninstall(): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_status` WHERE `name` = 'Fraud'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_status` WHERE `name` = 'Fraud Review'");

		$this->cache->delete('order_status');

		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "fraudlabspro`");
	}

	public function getOrder(int $order_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "fraudlabspro` WHERE order_id = '" . (int)$order_id . "'");

		return $query->row;
	}

	public function updateFeedback(int $order_id, string $flp_status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "fraudlabspro` SET fraudlabspro_status = '" . $this->db->escape($flp_status) . "' WHERE order_id = '" . (int)$order_id . "'");
	}

	/**
	 * Send an order feedback call to the FraudLabs Pro v2 Feedback API.
	 *
	 * @param string $flp_id  The fraudlabspro_id returned at screening time.
	 * @param string $action  One of: APPROVE, REJECT, REJECT_BLACKLIST
	 * @return bool           True if the call succeeded, false on network/parse error.
	 */
	public function sendFeedback(string $flp_id, string $action): bool {
		$request = [
			'key'    => $this->config->get('fraudlabspro_key'),
			'id'     => $flp_id,
			'action' => $action,
			'format' => 'json',
		];

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, 'https://api.fraudlabspro.com/v2/order/feedback');
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($request));
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);

		$response = curl_exec($curl);

		unset($curl);

		if ($response === false || $response === '') {
			return false;
		}

		$json = json_decode($response);

		return ($json !== null);
	}

	public function addOrderHistory(int $order_id, $store_id = 0, array $data = []) {
		$json = [];

		$this->load->model('setting/store');

		$store_info = $this->model_setting_store->getStore($store_id);

		if ($store_info) {
			$url = $this->config->get('config_secure') ? $store_info['ssl'] : $store_info['url'];
		} else {
			$url = $this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG;
		}

		if (isset($this->session->data['cookie'])) {
			$curl = curl_init();

			// Set SSL if required
			if (substr($url, 0, 5) === 'https') {
				curl_setopt($curl, CURLOPT_PORT, 443);
			}

			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_USERAGENT, $this->request->server['HTTP_USER_AGENT']);
			curl_setopt($curl, CURLOPT_COOKIE, session_name() . '=' . $this->session->data['cookie'] . ';');
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL, $url . 'index.php?route=sale/order/history&order_id=' . $order_id);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);

			$json = curl_exec($curl);

			unset($curl);
		}

		return $json;
	}

	public function addSettings(): void {
		$this->load->model('localisation/order_status');

		$order_statuses = $this->model_localisation_order_status->getOrderStatuses([]);

		foreach ($order_statuses as $order_status) {
			if ($order_status['name'] === 'Fraud') {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `group` = 'fraudlabspro', `key` = 'fraudlabspro_order_status_id', `value` = '" . (int)$order_status['order_status_id'] . "'");
			}

			if ($order_status['name'] === 'Fraud Review') {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `group` = 'fraudlabspro', `key` = 'fraudlabspro_review_status_id', `value` = '" . (int)$order_status['order_status_id'] . "'");
			}
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `group` = 'fraudlabspro', `key` = 'fraudlabspro_approve_status_id', `value` = '" . $this->config->get('config_order_status_id') . "'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `group` = 'fraudlabspro', `key` = 'fraudlabspro_reject_status_id', `value` = '8'");
	}
}
