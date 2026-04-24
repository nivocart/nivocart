<?php
/**
 * Class ModelReportReturn
 *
 * @package NivoCart
 */
class ModelReportReturn extends Model {
	/**
	 * Functions Get
	 */
	public function getReturns(array $data = []): array {
		$sql = "SELECT MIN(r.date_added) AS date_start, MAX(r.date_added) AS date_end, COUNT(r.return_id) AS `returns` FROM `" . DB_PREFIX . "return` r";

		if (!empty($data['filter_return_status_id'])) {
			$sql .= " WHERE r.return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
		} else {
			$sql .= " WHERE r.return_status_id > '0'";
		}

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(r.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(r.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$group = $data['filter_group'] ?? 'week';

		$sql .= match($group) {
			'day'   => " GROUP BY DAY(r.date_added)",
			'month' => " GROUP BY MONTH(r.date_added)",
			'year'  => " GROUP BY YEAR(r.date_added)",
			default => " GROUP BY WEEK(r.date_added)"
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

	public function getTotalReturns(array $data = []): int {
		$group = $data['filter_group'] ?? 'week';

		$sql = match($group) {
			'day'   => "SELECT COUNT(DISTINCT DAY(date_added)) AS `total` FROM `" . DB_PREFIX . "return`",
			'month' => "SELECT COUNT(DISTINCT MONTH(date_added)) AS `total` FROM `" . DB_PREFIX . "return`",
			'year'  => "SELECT COUNT(DISTINCT YEAR(date_added)) AS `total` FROM `" . DB_PREFIX . "return`",
			default => "SELECT COUNT(DISTINCT WEEK(date_added)) AS `total` FROM `" . DB_PREFIX . "return`"
		};

		if (!empty($data['filter_return_status_id'])) {
			$sql .= " WHERE return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
		} else {
			$sql .= " WHERE return_status_id > '0'";
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
}
