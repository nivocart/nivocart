<?php
/**
 * Class ModelReportDashboard
 *
 * Compact aggregate queries for the Reports Dashboard summaries.
 * Every method takes an inclusive Y-m-d date range already validated by the controller.
 *
 * @package NivoCart
 */
class ModelReportDashboard extends Model {
	/**
	 * Functions Get
	 */
	public function getDailySales(string $date_start, string $date_end): array {
		$data = ['orders' => [], 'revenue' => [], 'profit' => []];

		$query = $this->db->query("SELECT DATE(o.date_added) AS `day`, COUNT(*) AS `orders`, SUM(o.total) AS `revenue` FROM `" . DB_PREFIX . "order` o WHERE o.order_status_id > '0' AND DATE(o.date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(o.date_added) <= '" . $this->db->escape($date_end) . "' GROUP BY DATE(o.date_added)");

		foreach ($query->rows as $row) {
			$data['orders'][$row['day']] = (int)$row['orders'];
			$data['revenue'][$row['day']] = (float)$row['revenue'];
		}

		// The cost of a dropship line is rebuilt in the store currency (null helper = no Dropship modules)
		$this->load->model('report/dropship');

		$line = $this->model_report_dropship->getLineCostSql();

		$joins = $line ? $line['joins'] : '';
		$cost = $line ? $line['cost'] : 'op.cost';

		$query = $this->db->query("SELECT DATE(o.date_added) AS `day`, SUM((op.price - " . $cost . ") * op.quantity) AS `profit` FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)" . $joins . " WHERE o.order_status_id > '0' AND DATE(o.date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(o.date_added) <= '" . $this->db->escape($date_end) . "' GROUP BY DATE(o.date_added)");

		foreach ($query->rows as $row) {
			$data['profit'][$row['day']] = (float)$row['profit'];
		}

		return $data;
	}

	public function getOrderTotalByCode(string $code, string $date_start, string $date_end): float {
		$query = $this->db->query("SELECT SUM(ot.value) AS `total` FROM `" . DB_PREFIX . "order_total` ot LEFT JOIN `" . DB_PREFIX . "order` o ON (ot.order_id = o.order_id) WHERE ot.code = '" . $this->db->escape($code) . "' AND o.order_status_id > '0' AND DATE(o.date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(o.date_added) <= '" . $this->db->escape($date_end) . "'");

		return (float)($query->row['total'] ?? 0);
	}

	public function getReturnTotals(string $date_start, string $date_end): array {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "return` WHERE return_status_id > '0' AND DATE(date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(date_added) <= '" . $this->db->escape($date_end) . "'");

		return [
			'total'   => (int)$query->row['total'],
			'pending' => $this->getTotalPendingReturns()
		];
	}

	public function getTotalPendingReturns(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "return` WHERE return_status_id = '" . (int)$this->config->get('config_return_status_id') . "'");

		return (int)$query->row['total'];
	}

	public function getCouponTotals(string $date_start, string $date_end): array {
		$query = $this->db->query("SELECT COUNT(DISTINCT order_id) AS `orders`, SUM(amount) AS `total` FROM `" . DB_PREFIX . "coupon_history` WHERE DATE(date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(date_added) <= '" . $this->db->escape($date_end) . "'");

		return [
			'orders' => (int)$query->row['orders'],
			'total'  => abs((float)$query->row['total'])
		];
	}

	public function getVoucherTotals(string $date_start, string $date_end): array {
		$sold = $this->db->query("SELECT COUNT(*) AS `total`, SUM(amount) AS `amount` FROM `" . DB_PREFIX . "voucher` WHERE DATE(date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(date_added) <= '" . $this->db->escape($date_end) . "'");

		$complete = (int)$this->config->get('config_complete_status_id');

		$outstanding = $this->db->query("SELECT SUM(tmp.balance) AS `total` FROM (SELECT (v.amount + COALESCE((SELECT SUM(vh.amount) FROM `" . DB_PREFIX . "voucher_history` vh WHERE vh.voucher_id = v.voucher_id), 0)) AS `balance` FROM `" . DB_PREFIX . "voucher` v WHERE (v.order_id = '0' OR EXISTS (SELECT 1 FROM `" . DB_PREFIX . "order` o WHERE o.order_id = v.order_id AND o.order_status_id = '" . $complete . "'))) tmp WHERE tmp.balance > 0");

		return [
			'count'       => (int)$sold->row['total'],
			'amount'      => (float)$sold->row['amount'],
			'outstanding' => (float)$outstanding->row['total']
		];
	}

	public function getUnitsSold(string $date_start, string $date_end): int {
		$query = $this->db->query("SELECT SUM(op.quantity) AS `total` FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) WHERE o.order_status_id > '0' AND DATE(o.date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(o.date_added) <= '" . $this->db->escape($date_end) . "'");

		return (int)($query->row['total'] ?? 0);
	}

	/**
	 * Margin summary for the Reports Dashboard.
	 *
	 * Standard products compare price with the product cost (both in the store currency).
	 * When the DS Margin Checker is installed, its dropship products are left out of that count and
	 * measured with the Margin Checker's own figures instead, because their product cost is in the
	 * supplier currency and has no shipping.
	 *
	 * @return array{total: int, below: int, low: int, average: float, standard_below: int, dropship: bool, dropship_below: int, dropship_low: int, dropship_no_rate: int}
	 */
	public function getMarginSummary(): array {
		$dropship = $this->getDropshipMarginSummary();

		$exclude = $dropship ? " AND p.product_id NOT IN (SELECT pd.product_id FROM `" . DB_PREFIX . "product_dropship` pd WHERE pd.is_active = '1')" : '';

		$query = $this->db->query("SELECT COUNT(*) AS `total`, SUM(p.price < p.cost) AS `below`, SUM(p.price >= p.cost AND ((p.price - p.cost) / p.price) < 0.1) AS `low`, SUM(((p.price - p.cost) / p.price) * 100) AS `margin_sum` FROM `" . DB_PREFIX . "product` p WHERE p.status = '1' AND p.cost > 0 AND p.price > 0" . $exclude);

		$total = (int)$query->row['total'];
		$below = (int)$query->row['below'];
		$low = (int)$query->row['low'];
		$sum = (float)$query->row['margin_sum'];
		$ds = $dropship ?: ['rated' => 0, 'below' => 0, 'low' => 0, 'no_rate' => 0, 'margin_sum' => 0.0];
		$rated = $total + $ds['rated'];

		return [
			'total'            => $rated,
			'below'            => $below + $ds['below'],
			'low'              => $low + $ds['low'],
			'average'          => $rated ? ($sum + $ds['margin_sum']) / $rated : 0.0,
			'standard_below'   => $below,
			'dropship'         => (bool)$dropship,
			'dropship_below'   => $ds['below'],
			'dropship_low'     => $ds['low'],
			'dropship_no_rate' => $ds['no_rate']
		];
	}

	/**
	 * Margin figures of the DS Margin Checker, or null when that modification is not installed.
	 */
	protected function getDropshipMarginSummary(): ?array {
		if (!is_file(DIR_APPLICATION . 'model/modification/ds_margin_checker.php')) {
			return null;
		}

		$tables = [DB_PREFIX . 'product_dropship', DB_PREFIX . 'dropship_variant', DB_PREFIX . 'dropship_channel'];

		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('" . implode("', '", array_map([$this->db, 'escape'], $tables)) . "')");

		if ((int)$query->row['total'] < count($tables)) {
			return null;
		}

		$this->load->model('modification/ds_margin_checker');

		return $this->model_modification_ds_margin_checker->getMarginSummary();
	}

	public function getLabelSummary(): array {
		$query = $this->db->query("SELECT COUNT(*) AS `total`, SUM(p.label IS NOT NULL AND p.label <> '') AS `labelled` FROM `" . DB_PREFIX . "product` p WHERE p.status = '1'");

		return [
			'total'    => (int)$query->row['total'],
			'labelled' => (int)$query->row['labelled']
		];
	}

	public function getRewardSummary(string $date_start, string $date_end): array {
		$total = $this->db->query("SELECT SUM(points) AS `total` FROM `" . DB_PREFIX . "customer_reward`");

		$period = $this->db->query("SELECT SUM(points) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE points > 0 AND DATE(date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(date_added) <= '" . $this->db->escape($date_end) . "'");

		return [
			'outstanding' => (int)$total->row['total'],
			'earned'      => (int)$period->row['total']
		];
	}

	public function getCreditBalance(): float {
		$query = $this->db->query("SELECT SUM(amount) AS `total` FROM `" . DB_PREFIX . "customer_transaction`");

		return (float)$query->row['total'];
	}

	public function getTopCountries(string $date_start, string $date_end, int $limit = 5): array {
		$query = $this->db->query("SELECT cd.name AS `name`, c.iso_code_2 AS `iso`, COUNT(*) AS `orders`, SUM(o.total) AS `amount` FROM `" . DB_PREFIX . "order` o LEFT JOIN `" . DB_PREFIX . "country` c ON (o.payment_country_id = c.country_id) LEFT JOIN `" . DB_PREFIX . "country_description` cd ON (c.country_id = cd.country_id AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE o.order_status_id > '0' AND DATE(o.date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(o.date_added) <= '" . $this->db->escape($date_end) . "' GROUP BY c.country_id ORDER BY `amount` DESC LIMIT " . (int)$limit);

		return (is_object($query)) ? $query->rows : [];
	}

	public function getTotalCustomersDeleted(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "customer_deleted`");

		return (int)$query->row['total'];
	}

	public function getAffiliateActivityTotal(string $date_start, string $date_end): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "affiliate_activity` WHERE DATE(date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(date_added) <= '" . $this->db->escape($date_end) . "'");

		return (int)$query->row['total'];
	}

	public function getAffiliateCommission(string $date_start, string $date_end, int $limit = 3): array {
		$query = $this->db->query("SELECT SUM(at.amount) AS `total` FROM `" . DB_PREFIX . "affiliate_transaction` at WHERE DATE(at.date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(at.date_added) <= '" . $this->db->escape($date_end) . "'");

		$top = $this->db->query("SELECT CONCAT(a.firstname, ' ', a.lastname) AS `name`, SUM(at.amount) AS `amount` FROM `" . DB_PREFIX . "affiliate_transaction` at LEFT JOIN `" . DB_PREFIX . "affiliate` a ON (at.affiliate_id = a.affiliate_id) WHERE DATE(at.date_added) >= '" . $this->db->escape($date_start) . "' AND DATE(at.date_added) <= '" . $this->db->escape($date_end) . "' GROUP BY at.affiliate_id ORDER BY `amount` DESC LIMIT " . (int)$limit);

		return [
			'total' => (float)$query->row['total'],
			'top'   => $top->rows
		];
	}

	public function getAbandonedSummary(): array {
		$days = $this->config->get('config_abandoned_cart') ? (int)$this->config->get('config_abandoned_cart') : 7;

		$query = $this->db->query("SELECT COUNT(*) AS `total`, SUM(o.total) AS `amount` FROM `" . DB_PREFIX . "order` o WHERE o.date_added >= DATE_SUB(NOW(), INTERVAL " . $days . " DAY) AND o.order_status_id = '0'");

		return [
			'total'  => (int)$query->row['total'],
			'amount' => (float)$query->row['amount']
		];
	}

	public function getTopBanners(int $limit = 3): array {
		$query = $this->db->query("SELECT bid.title AS `title`, bi.clicked AS `clicked` FROM `" . DB_PREFIX . "banner_image` bi LEFT JOIN `" . DB_PREFIX . "banner_image_description` bid ON (bi.banner_image_id = bid.banner_image_id) WHERE bid.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bi.clicked > '0' ORDER BY bi.clicked DESC LIMIT " . (int)$limit);

		return (is_object($query)) ? $query->rows : [];
	}
}
