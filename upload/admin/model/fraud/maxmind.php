<?php
/**
 * Class ModelFraudMaxMind
 *
 * @package NivoCart
 */
class ModelFraudMaxMind extends Model {
	/**
	 * Functions Install, Uninstall, GetOrder
	 */
	public function install(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "maxmind` (
			  `order_id` INT NOT NULL,
			  `customer_id` INT NOT NULL,
			  `country_match` VARCHAR(3) NOT NULL DEFAULT '',
			  `country_code` VARCHAR(2) NOT NULL DEFAULT '',
			  `high_risk_country` VARCHAR(3) NOT NULL DEFAULT '',
			  `distance` INT NOT NULL DEFAULT 0,
			  `ip_region` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_city` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_latitude` DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
			  `ip_longitude` DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
			  `ip_isp` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_org` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_asnum` INT NOT NULL DEFAULT 0,
			  `ip_user_type` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_country_confidence` VARCHAR(3) NOT NULL DEFAULT '',
			  `ip_region_confidence` VARCHAR(3) NOT NULL DEFAULT '',
			  `ip_city_confidence` VARCHAR(3) NOT NULL DEFAULT '',
			  `ip_postal_confidence` VARCHAR(3) NOT NULL DEFAULT '',
			  `ip_postal_code` VARCHAR(10) NOT NULL DEFAULT '',
			  `ip_accuracy_radius` INT NOT NULL DEFAULT 0,
			  `ip_net_speed_cell` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_metro_code` INT NOT NULL DEFAULT 0,
			  `ip_area_code` INT NOT NULL DEFAULT 0,
			  `ip_time_zone` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_region_name` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_domain` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_country_name` VARCHAR(255) NOT NULL DEFAULT '',
			  `ip_continent_code` VARCHAR(2) NOT NULL DEFAULT '',
			  `ip_corporate_proxy` VARCHAR(3) NOT NULL DEFAULT '',
			  `anonymous_proxy` VARCHAR(3) NOT NULL DEFAULT '',
			  `proxy_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
			  `is_trans_proxy` VARCHAR(3) NOT NULL DEFAULT '',
			  `free_mail` VARCHAR(3) NOT NULL DEFAULT '',
			  `carder_email` VARCHAR(3) NOT NULL DEFAULT '',
			  `high_risk_username` VARCHAR(3) NOT NULL DEFAULT '',
			  `high_risk_password` VARCHAR(3) NOT NULL DEFAULT '',
			  `bin_match` VARCHAR(10) NOT NULL DEFAULT '',
			  `bin_country` VARCHAR(2) NOT NULL DEFAULT '',
			  `bin_name_match` VARCHAR(3) NOT NULL DEFAULT '',
			  `bin_name` VARCHAR(255) NOT NULL DEFAULT '',
			  `bin_phone_match` VARCHAR(3) NOT NULL DEFAULT '',
			  `bin_phone` VARCHAR(32) NOT NULL DEFAULT '',
			  `customer_phone_in_billing_location` VARCHAR(8) NOT NULL DEFAULT '',
			  `ship_forward` VARCHAR(3) NOT NULL DEFAULT '',
			  `city_postal_match` VARCHAR(3) NOT NULL DEFAULT '',
			  `ship_city_postal_match` VARCHAR(3) NOT NULL DEFAULT '',
			  `score` DECIMAL(10,5) NOT NULL DEFAULT 0.00000,
			  `explanation` TEXT NOT NULL,
			  `risk_score` DECIMAL(10,5) NOT NULL DEFAULT 0.00000,
			  `queries_remaining` INT NOT NULL DEFAULT 0,
			  `maxmind_id` VARCHAR(36) NOT NULL DEFAULT '',
			  `error` TEXT NOT NULL,
			  `email_is_disposable` VARCHAR(3) NOT NULL DEFAULT '',
			  `email_is_high_risk` VARCHAR(3) NOT NULL DEFAULT '',
			  `credit_card_brand` VARCHAR(20) NOT NULL DEFAULT '',
			  `credit_card_type` VARCHAR(20) NOT NULL DEFAULT '',
			  `credit_card_is_prepaid` VARCHAR(3) NOT NULL DEFAULT '',
			  `ship_is_high_risk` VARCHAR(3) NOT NULL DEFAULT '',
			  `date_added` DATETIME NOT NULL,
			  PRIMARY KEY (`order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
		");

		// Widen maxmind_id for v2 UUID format (was varchar(8), now varchar(36)).
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "maxmind` CHANGE COLUMN `maxmind_id` `maxmind_id` VARCHAR(36) NOT NULL DEFAULT '';");

		// Add v2 columns to existing installs (safe to re-run on fresh installs too).
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "maxmind` ADD COLUMN IF NOT EXISTS `email_is_disposable` VARCHAR(3) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "maxmind` ADD COLUMN IF NOT EXISTS `email_is_high_risk` VARCHAR(3) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "maxmind` ADD COLUMN IF NOT EXISTS `credit_card_brand` VARCHAR(20) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "maxmind` ADD COLUMN IF NOT EXISTS `credit_card_type` VARCHAR(20) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "maxmind` ADD COLUMN IF NOT EXISTS `credit_card_is_prepaid` VARCHAR(3) NOT NULL DEFAULT '';");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "maxmind` ADD COLUMN IF NOT EXISTS `ship_is_high_risk` VARCHAR(3) NOT NULL DEFAULT '';");
	}

	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "maxmind`");
	}

	public function getOrder(int $order_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "maxmind` WHERE order_id = '" . (int)$order_id . "'");

		return $query->row;
	}
}
