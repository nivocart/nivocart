<?php
/**
 * Class ModelReportSale
 *
 * @package NivoCart
 */
class ModelReportSale extends Model {
	/**
	 * Functions Get
	 */
	public function getOrders(array $data = []): array {
		$sql = "SELECT MIN(tmp.date_added) AS date_start, MAX(tmp.date_added) AS date_end, COUNT(tmp.order_id) AS orders, SUM(tmp.products) AS products, SUM(tmp.tax) AS tax, SUM(tmp.total) AS `total` FROM (SELECT o.order_id, (SELECT SUM(op.quantity) FROM `" . DB_PREFIX . "order_product` op WHERE op.order_id = o.order_id GROUP BY op.order_id) AS products, (SELECT SUM(ot.value) FROM `" . DB_PREFIX . "order_total` ot WHERE ot.order_id = o.order_id AND ot.code = 'tax' GROUP BY ot.order_id) AS tax, o.total, o.date_added FROM `" . DB_PREFIX . "order` o";

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

		$sql .= " GROUP BY o.order_id) tmp";

		$group = $data['filter_group'] ?? 'week';

		$sql .= match($group) {
			'day'   => " GROUP BY DAY(tmp.date_added)",
			'month' => " GROUP BY MONTH(tmp.date_added)",
			'year'  => " GROUP BY YEAR(tmp.date_added)",
			default => " GROUP BY WEEK(tmp.date_added)"
		};

		$sql .= " ORDER BY tmp.date_added DESC";

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

		return $query->rows;
	}

	public function getTotalOrders(array $data = []): int {
		$group = $data['filter_group'] ?? 'week';

		$sql = match($group) {
			'day'   => "SELECT COUNT(DISTINCT DAY(date_added)) AS `total` FROM `" . DB_PREFIX . "order`",
			'month' => "SELECT COUNT(DISTINCT MONTH(date_added)) AS `total` FROM `" . DB_PREFIX . "order`",
			'year'  => "SELECT COUNT(DISTINCT YEAR(date_added)) AS `total` FROM `" . DB_PREFIX . "order`",
			default => "SELECT COUNT(DISTINCT WEEK(date_added)) AS `total` FROM `" . DB_PREFIX . "order`"
		};

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getTotalOrdersByCountry(): array {
		$query = $this->db->query("SELECT COUNT(*) AS `total`, SUM(o.total) AS `amount`, c.iso_code_2 AS iso_code_2 FROM `" . DB_PREFIX . "order` o LEFT JOIN `" . DB_PREFIX . "country` c ON ((o.payment_country_id = c.country_id) OR (o.shipping_country_id = c.country_id)) WHERE o.order_status_id > '0' GROUP BY c.country_id");

		return $query->rows;
	}

	public function getTopOrdersByCountry(int $limit): array {
		$limit = (isset($limit) && $limit > 0) ? (int)$limit : 1;

		$query = $this->db->query("SELECT SUM(o.total) AS amount, c.iso_code_2 AS iso_code_2 FROM `" . DB_PREFIX . "order` o LEFT JOIN `" . DB_PREFIX . "country` c ON ((o.payment_country_id = c.country_id) OR (o.shipping_country_id = c.country_id)) WHERE o.order_status_id > '0' GROUP BY c.country_id ORDER BY amount DESC LIMIT 0," . (int)$limit);

		return $query->rows;
	}

	public function getTotalSales(array $data = []) {
		$sql = "SELECT SUM(total) AS `total` FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0'";

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalOrdersByCurrencyId(int $currency_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order` WHERE currency_id = '" . (int)$currency_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTaxes(array $data = []): array {
		$sql = "SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS `total`, COUNT(o.order_id) AS orders FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'tax'";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$group = $data['filter_group'] ?? 'week';

		$sql .= match($group) {
			'day'   => " GROUP BY ot.title, DAY(o.date_added)",
			'month' => " GROUP BY ot.title, MONTH(o.date_added)",
			'year'  => " GROUP BY ot.title, YEAR(o.date_added)",
			default => " GROUP BY ot.title, WEEK(o.date_added)"
		};

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

	public function getTotalTaxes(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM (SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'tax'";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$group = $data['filter_group'] ?? 'week';

		$sql .= match($group) {
			'day'   => " GROUP BY DAY(o.date_added), ot.title",
			'month' => " GROUP BY MONTH(o.date_added), ot.title",
			'year'  => " GROUP BY YEAR(o.date_added), ot.title",
			default => " GROUP BY WEEK(o.date_added), ot.title"
		};

		$sql .= ") tmp";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getShipping(array $data = []): array {
		$sql = "SELECT MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS `total`, COUNT(o.order_id) AS `orders` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'shipping'";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$group = $data['filter_group'] ?? 'week';

		$sql .= match($group) {
			'day'   => " GROUP BY ot.title, DAY(o.date_added)",
			'month' => " GROUP BY ot.title, MONTH(o.date_added)",
			'year'  => " GROUP BY ot.title, YEAR(o.date_added)",
			default => " GROUP BY ot.title, WEEK(o.date_added)"
		};

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

	public function getTotalShipping(array $data = []): int {
		$sql = "SELECT COUNT(*) AS `total` FROM (SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = 'shipping'";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND order_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$group = $data['filter_group'] ?? 'week';

		$sql .= match($group) {
			'day'   => " GROUP BY DAY(o.date_added), ot.title",
			'month' => " GROUP BY MONTH(o.date_added), ot.title",
			'year'  => " GROUP BY YEAR(o.date_added), ot.title",
			default => " GROUP BY WEEK(o.date_added), ot.title"
		};

		$sql .= ") tmp";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}
