<?php
/**
 * Class ModelSettingStore
 *
 * @package NivoCart
 */
class ModelSettingStore extends Model {
	/**
	 * Functions Add, Edit, Delete, Get
	 */
	public function addStore(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "store` SET `name` = '" . $this->db->escape($data['config_name']) . "', `url` = '" . $this->db->escape($data['config_url']) . "', `ssl` = '" . $this->db->escape($data['config_ssl']) . "'");

		$store_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_store_id'] = $store_id;

		$this->cache->delete('store');
	}

	public function editStore(int $store_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "store` SET `name` = '" . $this->db->escape($data['config_name']) . "', `url` = '" . $this->db->escape($data['config_url']) . "', `ssl` = '" . $this->db->escape($data['config_ssl']) . "' WHERE store_id = '" . (int)$store_id . "'");

		$this->cache->delete('store');
	}

	public function deleteStore(int $store_id): int {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "store` WHERE store_id = '" . (int)$store_id . "'");

		$this->cache->delete('store');
	}

	public function getStore(int $store_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "store` WHERE store_id = '" . (int)$store_id . "'");

		return $query->row;
	}

	public function getAllStores(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` ORDER BY url ASC");

		return $query->rows;
	}

	public function getStores(array $data = []) {
		if ($data) {
			$sql = "SELECT * FROM `" . DB_PREFIX . "store`";

			$sort_data = [
				'name',
				'url'
			];

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY `url`";
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

		} else {
			$store_data = $this->cache->get('store');

			if (!$store_data) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store` ORDER BY `url` ASC");

				$store_data = $query->rows;

				$this->cache->set('store', $store_data);
			}

			return $store_data;
		}
	}

	public function getTemplate(int $store_id) {
		$query = $this->db->query("SELECT DISTINCT `value` AS template FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `group` = 'config' AND `key` = 'config_template'");

		return $query->row['template'];
	}

	public function getTotalStores(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "store`");

		return $query->row['total'];
	}

	public function getTotalStoresByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_layout_id' AND `value` = '" . (int)$layout_id . "' AND store_id != '0'");

		return $query->row['total'];
	}

	public function getTotalStoresByLanguage($language): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language' AND `value` = '" . $this->db->escape($language) . "' AND store_id != '0'");

		return $query->row['total'];
	}

	public function getTotalStoresByCurrency($currency): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_currency' AND `value` = '" . $this->db->escape($currency) . "' AND store_id != '0'");

		return $query->row['total'];
	}

	public function getTotalStoresByCountryId(int $country_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_country_id' AND `value` = '" . (int)$country_id . "' AND store_id != '0'");

		return $query->row['total'];
	}

	public function getTotalStoresByZoneId(int $zone_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_zone_id' AND `value` = '" . (int)$zone_id . "' AND store_id != '0'");

		return $query->row['total'];
	}

	public function getTotalStoresByStylesheet(string $name): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_admin_stylesheet' AND `value` = '" . $this->db->escape((string)$name) . "' AND store_id != '0'");

		return $query->row['total'];
	}

	public function getTotalStoresByCustomerGroupId(int $customer_group_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_customer_group_id' AND `value` = '" . (int)$customer_group_id . "' AND store_id != '0'");

		return $query->row['total'];
	}

	public function getTotalStoresBySupplierGroupId(int $supplier_group_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_supplier_group_id' AND `value` = '" . (int)$supplier_group_id . "' AND store_id != '0'");

		return $query->row['total'];
	}

	public function getTotalStoresByInformationId(int $information_id): int {
		$account_query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_account_id' AND `value` = '" . (int)$information_id . "' AND store_id != '0'");
		$checkout_query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_checkout_id' AND `value` = '" . (int)$information_id . "' AND store_id != '0'");

		return ($account_query->row['total'] + $checkout_query->row['total']);
	}

	public function getTotalStoresByOrderStatusId(int $order_status_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_order_status_id' AND `value` = '" . (int)$order_status_id . "' AND store_id != '0'");

		return $query->row['total'];
	}
}
