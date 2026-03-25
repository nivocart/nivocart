<?php
class ModelReportProduct extends Model {

	public function getProductsViewed(array $data = []): array {
		$sql = "SELECT p.product_id, pd.name, p.model, p.viewed FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.viewed > '0' ORDER BY p.viewed DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = [];

		$query = $this->db->query($sql);

		foreach ($query->rows as $key => $result) {
			$product_data[$key] = $result;
		}

		return $product_data;
	}

	public function getTotalProductsViewed(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM " . DB_PREFIX . "product WHERE viewed > '0'");

		return (int)$query->row['total'];
	}

	public function getTotalProductViews(): int {
		$query = $this->db->query("SELECT SUM(viewed) AS `total` FROM " . DB_PREFIX . "product WHERE viewed > '0'");

		if (!empty($query->num_rows) && $query->num_rows > 0) {
			$views_data = $query->num_rows;

			return $views_data;
		} else {
			return 0;
		}
	}

	public function resetViews(): void {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = '0' WHERE viewed > '0'");
	}

	public function getPurchased(array $data = []): array {
		$sql = "SELECT op.product_id, op.name, op.model, p.price, p.cost, SUM(op.quantity) AS quantity, SUM((op.price + op.tax) * op.quantity) AS `total` FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "product p ON (op.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$sql .= " GROUP BY op.model ORDER BY `total` DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		if (!empty($query)) {
			return $query->rows;
		} else {
			return [];
		}
	}

	public function getTotalPurchased($data): int {
		$sql = "SELECT COUNT(DISTINCT op.model) AS `total` FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getProfit(array $data = []): array {
		$sql = "SELECT YEAR(o.date_added) AS year, MONTHNAME(o.date_added) AS month, SUM(op.price * op.quantity) AS price, SUM(op.cost * op.quantity) AS cost, SUM(op.price * op.quantity - op.cost * op.quantity) AS profit, o.date_added FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$sql .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added) ORDER BY YEAR(o.date_added), MONTH(o.date_added) ASC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		if (!empty($query)) {
			return $query->rows;
		} else {
			return [];
		}
	}

	public function getTotalProfit($data) {
		$sql = "SELECT COUNT(*), YEAR(o.date_added), MONTHNAME(o.date_added) FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$sql .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added)";

		$query = $this->db->query($sql);

		if ($query->num_rows) {
			return $query->num_rows;
		} else {
			return 0;
		}
	}

	public function getProducts(array $data = []): array {
		$sql = "SELECT p.product_id, p.image, p.label, p.cost, p.price, pd.name AS `name` FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.price > '0'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'p.product_id',
			'pd.name',
			'p.price',
			'p.cost'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		if (!empty($query)) {
			return $query->rows;
		} else {
			return [];
		}
	}

	public function getTotalProducts(array $data = []): int {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS `total` FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.price > '0'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}
}
