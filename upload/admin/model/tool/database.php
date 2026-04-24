<?php
/**
 * Class ModelToolDatabase
 *
 * @package NivoCart
 */
class ModelToolDatabase extends Model {
	/**
	 * Functions Get
	 */
	public function getTables(): array {
		$table_data = [];

		$query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

		foreach ($query->rows as $result) {
			if (substr($result['Tables_in_' . DB_DATABASE], 0, strlen(DB_PREFIX)) === DB_PREFIX) {
				if (isset($result['Tables_in_' . DB_DATABASE])) {
					$table_data[] = $result['Tables_in_' . DB_DATABASE];
				}
			}
		}

		return $table_data;
	}

	public function tableOptimize(): void {
		$query = $this->db->query("SHOW TABLE STATUS FROM `" . DB_DATABASE . "` WHERE Data_free > 0");

		foreach ($query->rows as $result) {
			if (substr($result['Name'], 0, strlen(DB_PREFIX)) === DB_PREFIX) {
				if (isset($result['Name'])) {
					$this->db->query("OPTIMIZE TABLE " . $result['Name']);
				}
			}
		}
	}

	public function tableRepair(): void {
		$query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

		foreach ($query->rows as $result) {
			if (substr($result['Tables_in_' . DB_DATABASE], 0, strlen(DB_PREFIX)) === DB_PREFIX) {
				if (isset($result['Tables_in_' . DB_DATABASE])) {
					$this->db->query("REPAIR TABLE " . $result['Tables_in_' . DB_DATABASE]);
				}
			}
		}
	}

	public function getEngines(): array {
		$engines = [];

		$query = $this->db->query("SHOW TABLE STATUS FROM `" . DB_DATABASE . "`");

		foreach ($query->rows as $result) {
			$engines = ['engine' => $result['Engine']];

			return $engines;
		}
	}

	public function engineInnoDB(): void {
		$query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

		foreach ($query->rows as $result) {
			if (substr($result['Tables_in_' . DB_DATABASE], 0, strlen(DB_PREFIX)) === DB_PREFIX) {
				if (isset($result['Tables_in_' . DB_DATABASE])) {
					$this->db->query("ALTER TABLE " . $result['Tables_in_' . DB_DATABASE] . " ENGINE = InnoDB");
				}
			}
		}
	}

	public function engineMyISAM(): void {
		$query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

		foreach ($query->rows as $result) {
			if (substr($result['Tables_in_' . DB_DATABASE], 0, strlen(DB_PREFIX)) === DB_PREFIX) {
				if (isset($result['Tables_in_' . DB_DATABASE])) {
					$this->db->query("ALTER TABLE " . $result['Tables_in_' . DB_DATABASE] . " ENGINE = MyISAM");
				}
			}
		}
	}
}
