<?php
class ModelSettingExtension extends Model {

	public function getExtensions(string $type) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape((string)$type) . "' ORDER BY `code`");

		return $query->rows;
	}
}
