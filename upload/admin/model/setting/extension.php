<?php
/**
 * Class ModelSettingExtension
 *
 * @package NivoCart
 */
class ModelSettingExtension extends Model {
	/**
	 * Functions install, uninstall, Get
	 */
	public function getInstalled(string $type): array {
		$extension_data = [];

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape((string)$type) . "'");

		foreach ($query->rows as $result) {
			$extension_data[] = $result['code'];
		}

		return $extension_data;
	}

	public function install(string $type, string $code): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = '" . $this->db->escape((string)$type) . "', `code` = '" . $this->db->escape((string)$code) . "'");
	}

	public function uninstall(string $type, string $code): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape((string)$type) . "' AND `code` = '" . $this->db->escape((string)$code) . "'");
	}

	public function getExtensions(string $type): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape((string)$type) . "' ORDER BY `code`");

		return $query->rows;
	}

	public function getTotalInstalled(string $type): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape((string)$type) . "'");

		return $query->row['total'];
	}
}
