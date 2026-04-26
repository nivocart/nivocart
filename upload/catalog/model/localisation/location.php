<?php
/**
 * Class ModelLocalisationLocation
 *
 * @package NivoCart
 */
class ModelLocalisationLocation extends Model {
	/**
	 * Functions Get
	 */
	public function getLocation(int $location_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "location` WHERE location_id = '" . (int)$location_id . "'");

		return $query->row;
	}

	public function getLocations(int $limit): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "location` ORDER BY `name` DESC LIMIT 0," . (int)$limit);

		return $query->rows;
	}

	public function getTotalLocations(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "location`");

		return $query->row['total'];
	}
}
