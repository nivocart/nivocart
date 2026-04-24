<?php
/**
 * Class ModelToolUpload
 *
 * @package NivoCart
 */
class ModelToolUpload extends Model {
	/**
	 * Functions Add, Get
	 */
	public function addUpload(string $name, string $filename): string {
		$code = sha1(uniqid(mt_rand(), true));

		$this->db->query("INSERT INTO `" . DB_PREFIX . "upload` SET `name` = '" . $this->db->escape((string)$name) . "', filename = '" . $this->db->escape((string)$filename) . "', `code` = '" . $this->db->escape((string)$code) . "', date_added = NOW()");

		return $code;
	}

	public function getUploadByCode(string $code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "upload` WHERE `code` = '" . $this->db->escape((string)$code) . "'");

		return $query->row;
	}
}
