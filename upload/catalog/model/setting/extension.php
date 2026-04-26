<?php
/**
 * Class ModelSettingExtension
 *
 * @package NivoCart
 */
class ModelSettingExtension extends Model {
	/**
	 * Functions Get
	 */
	public function getExtensions(string $type) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape((string)$type) . "' ORDER BY `code`");

		return $query->rows;
	}
}
