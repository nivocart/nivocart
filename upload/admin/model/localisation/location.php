<?php
/**
 * Class ModelLocalisationLocation
 *
 * @package NivoCart
 */
class ModelLocalisationLocation extends Model {
	/**
	 * Functions Add, Edit, Delete, Get
	 */
	public function addLocation(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "location` SET `name` = '" . $this->db->escape($data['name']) . "', address = '" . $this->db->escape($data['address']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', `image` = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "', latitude = '" . $this->db->escape($data['latitude']) . "', longitude = '" . $this->db->escape($data['longitude']) . "', `open` = '" . $this->db->escape($data['open']) . "', `comment` = '" . $this->db->escape($data['comment']) . "'");

		$location_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_location_id'] = $location_id;
	}

	public function editLocation(int $location_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "location` SET `name` = '" . $this->db->escape($data['name']) . "', address = '" . $this->db->escape($data['address']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', `image` = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "', latitude = '" . $this->db->escape($data['latitude']) . "', longitude = '" . $this->db->escape($data['longitude']) . "', `open` = '" . $this->db->escape($data['open']) . "', `comment` = '" . $this->db->escape($data['comment']) . "' WHERE location_id = '" . (int)$location_id . "'");
	}

	public function deleteLocation(int $location_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "location` WHERE location_id = " . (int)$location_id);
	}

	public function getLocation(int $location_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "location` WHERE location_id = '" . (int)$location_id . "'");

		return $query->row;
	}

	public function getLocations(array $data = []): array {
		$sql = "SELECT location_id, `image`, `name`, address, telephone, latitude, longitude FROM `" . DB_PREFIX . "location`";

		$sort_data = [
			'image',
			'name',
			'address',
			'telephone',
			'latitude',
			'longitude'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
		}

		if (isset($data['order']) && ($data['order'] === 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) && isset($data['limit'])) {
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

	public function getTotalLocations(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "location`");

		return $query->row['total'];
	}
}
