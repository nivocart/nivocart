<?php
/**
 * Class ModelLocalisationTaxLocalRate
 *
 * @package NivoCart
 */
class ModelLocalisationTaxLocalRate extends Model {
	/**
	 * Functions Add, Edit, Delete, Get
	 */
	public function addTaxLocalRate(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "tax_local_rate` SET `name` = '" . $this->db->escape($data['name']) . "', rate = '" . (float)$data['rate'] . "', status = '" . (int)$data['status'] . "'");

		$tax_local_rate_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_tax_local_rate_id'] = $tax_local_rate_id;
	}

	public function editTaxLocalRate(int $tax_local_rate_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "tax_local_rate` SET `name` = '" . $this->db->escape($data['name']) . "', rate = '" . (float)$data['rate'] . "', status = '" . (int)$data['status'] . "' WHERE tax_local_rate_id = '" . (int)$tax_local_rate_id . "'");
	}

	public function deleteTaxLocalRate(int $tax_local_rate_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "tax_local_rate` WHERE tax_local_rate_id = '" . (int)$tax_local_rate_id . "'");
	}

	public function getTaxLocalRate(int $tax_local_rate_id) {
		$query = $this->db->query("SELECT rate FROM `" . DB_PREFIX . "tax_local_rate` WHERE `tax_local_rate_id` = '" . (int)$tax_local_rate_id . "' AND status = '1'");

		if ($query->num_rows) {
			return $query->row['rate'];
		}
	}

	public function getTaxLocalRates(): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "tax_local_rate`";

		$tax_data = [];

		$query = $this->db->query($sql);

		foreach ($query->rows as $key => $result) {
			$tax_data[$key] = $result;
		}

		return $tax_data;
	}

	public function getTotalTaxLocalRates(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "tax_local_rate`");

		return (int)$query->row['total'];
	}

	public function getTotalProductsByTaxLocalRateId(int $tax_local_rate_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product_tax_local_rate` WHERE tax_local_rate_id = '" . (int)$tax_local_rate_id . "'");

		return (int)$query->row['total'];
	}
}
