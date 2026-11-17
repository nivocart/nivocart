<?php
/**
 * Class ModelReportProduct
 *
 * @package NivoCart
 */
class ModelReportProduct extends Model {
	/**
	 * Functions Get
	 */
	public function getProductsViewed(array $data = []): array {
		$sql = "SELECT p.product_id, pd.name, p.model, p.viewed FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.viewed > '0' ORDER BY p.viewed DESC";

		if (isset($data['start']) && isset($data['limit'])) {
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
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product` WHERE viewed > '0'");

		return (int)$query->row['total'];
	}

	public function getTotalProductViews() {
		$query = $this->db->query("SELECT SUM(viewed) AS `total` FROM `" . DB_PREFIX . "product` WHERE viewed > '0'");

		if ($query->row['total']) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function resetViews(): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET viewed = '0' WHERE viewed > '0'");
	}

	public function getPurchased(array $data = []): array {
		$sql = "SELECT op.product_id, op.name, op.model, p.price, p.cost, SUM(op.quantity) AS quantity, SUM((op.price + op.tax) * op.quantity) AS `total` FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

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

	public function getTotalPurchased($data): int {
		$sql = "SELECT COUNT(DISTINCT op.model) AS `total` FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

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

	/**
	 * Cost SQL for the order lines: the stored line cost for standard lines, and the supplier cost plus shipping
	 * converted to the store currency for dropship lines (see ModelReportDropship::getLineCostSql()).
	 *
	 * @return array{joins: string, cost: string}
	 */
	protected function getLineCost(): array {
		$this->load->model('report/dropship');

		$line = $this->model_report_dropship->getLineCostSql();

		return $line ?? ['joins' => '', 'cost' => 'op.cost'];
	}

	/**
	 * WHERE part shared by the profit queries.
	 */
	protected function getProfitWhere(array $data): string {
		if (!empty($data['filter_order_status_id'])) {
			$sql = " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql = " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		return $sql;
	}

	/**
	 * Sales, cost and profit per month. A dropship line with no exchange rate has no cost, so it is left out of
	 * all three figures (never guessed); getNoRateProfitLines() counts them.
	 */
	public function getProfit(array $data = []): array {
		$line = $this->getLineCost();

		$cost = $line['cost'];

		$sql = "SELECT YEAR(o.date_added) AS year, MONTHNAME(o.date_added) AS month, SUM(CASE WHEN " . $cost . " IS NULL THEN 0 ELSE op.price * op.quantity END) AS price, SUM(" . $cost . " * op.quantity) AS cost, SUM(op.price * op.quantity - " . $cost . " * op.quantity) AS profit, o.date_added FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)" . $line['joins'];

		$sql .= $this->getProfitWhere($data);

		$sql .= " GROUP BY YEAR(o.date_added), MONTH(o.date_added) ORDER BY YEAR(o.date_added), MONTH(o.date_added) ASC";

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

	/**
	 * Dropship order lines in the period that have no exchange rate for their cost currency.
	 */
	public function getNoRateProfitLines(array $data = []): int {
		$line = $this->getLineCost();

		if (!$line['joins']) {
			return 0;
		}

		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)" . $line['joins'] . $this->getProfitWhere($data) . " AND " . $line['cost'] . " IS NULL");

		return (int)$query->row['total'];
	}

	public function getTotalProfit(array $data = []) {
		$sql = "SELECT COUNT(*), YEAR(o.date_added), MONTHNAME(o.date_added) FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)";

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

	/**
	 * Cost of the dropship products in the store currency, from the DS Margin Checker, or null when that
	 * modification is not installed.
	 */
	protected function getDropshipCostSql(): ?string {
		if (!is_file(DIR_APPLICATION . 'model/modification/ds_margin_checker.php')) {
			return null;
		}

		$this->load->model('modification/ds_margin_checker');

		if (!$this->model_modification_ds_margin_checker->isReady()) {
			return null;
		}

		return $this->model_modification_ds_margin_checker->getCostSql();
	}

	/**
	 * Products with price and cost. For a dropship product the cost is its Total Cost in the store currency
	 * (supplier cost plus shipping at the current rate); no_rate is 1 when that cannot be worked out.
	 */
	public function getProducts(array $data = []): array {
		$dropship = $this->getDropshipCostSql();

		if ($dropship) {
			$sql = "SELECT p.product_id, p.image, p.label, (CASE WHEN dsc.product_id IS NULL THEN p.cost ELSE dsc.total_cost END) AS `cost`, (dsc.product_id IS NOT NULL AND dsc.total_cost IS NULL) AS `no_rate`, p.price, pd.name AS `name` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) LEFT JOIN (" . $dropship . ") dsc ON (dsc.product_id = p.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.price > '0'";
		} else {
			$sql = "SELECT p.product_id, p.image, p.label, p.cost, 0 AS `no_rate`, p.price, pd.name AS `name` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.price > '0'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = [
			'p.product_id',
			'pd.name',
			'p.price',
			'p.cost'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . (($data['sort'] === 'p.cost') ? '`cost`' : $data['sort']);
		} else {
			$sql .= " ORDER BY pd.name";
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

	public function getTotalProducts(array $data = []): int {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS `total` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.price > '0'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}
}
