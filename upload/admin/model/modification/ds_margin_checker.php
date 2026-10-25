<?php
/**
 * Class ModelModificationDsMarginChecker
 *
 * Admin model for the DS Margin Checker tool.
 *
 * Cost of a product, all in the store default currency (GBP in the examples):
 *   original_cost = nc_dropship_variant.supplier_cost (fallback: nc_product_dropship.supplier_cost)
 *   product_cost  = original_cost / exchange_rate                    (foreign currency)
 *                   original_cost                                      (default currency)
 *   margin_pct    = ((sale_price - product_cost) / sale_price) * 100   (sale price is ex-tax)
 *
 * Tax is shown for information only: cost_inc_vat = product_cost * (1 + store tax rate), where the rate is
 * the product's tax class at the store address, or the Default Tax Class setting when the product has none.
 * It never changes the margin. Overhead costs (DS Cost Calculator) are deliberately not part of this tool.
 *
 * exchange_rate is nc_currency.value of the cost currency (units of that currency for one unit
 * of the default currency). When no rate exists for the cost currency the cost and the margin are NULL
 * ("no rate"): they are never guessed. When sale_price is zero the margin is -100 ("no price set").
 *
 * Reads from: nc_product, nc_product_description, nc_product_dropship, nc_dropship_variant,
 *             nc_dropship_channel, nc_currency and the tax tables.
 * Writes to:  nc_product (price only), nc_setting (threshold, default_tax_class_id).
 *
 * Intended location: admin/model/modification/ds_margin_checker.php
 *
 * @package NivoCart
 */
class ModelModificationDsMarginChecker extends Model {
	protected array $tax_rates = [];

	// =================================================================
	// SHARED COST / MARGIN QUERY
	// =================================================================

	/**
	 * SQL of one row per active dropship product with the original cost, its currency, the
	 * exchange rate, the product cost in the default currency and the margin.
	 * Every other query in this model wraps it, so the calculation exists in exactly one place.
	 *
	 * @param  string $where  Extra condition on the product row, for example "p.product_id = '5'".
	 * @return string
	 */
	protected function getBaseSql(string $where = ''): string {
		$default = $this->db->escape(strtoupper(trim((string)$this->config->get('config_currency'))));

		$rate = "CASE WHEN src.original_currency = '" . $default . "' THEN 1 WHEN src.currency_value > 0 THEN src.currency_value ELSE NULL END";

		$cost = "CASE WHEN (" . $rate . ") IS NULL THEN NULL WHEN src.original_currency = '" . $default . "' THEN src.original_cost ELSE src.original_cost / (" . $rate . ") END";

		return "
			SELECT
				x.*,
				CASE WHEN x.product_cost IS NULL THEN NULL
					 WHEN x.price > 0 THEN ROUND(((x.price - x.product_cost) / x.price) * 100, 2)
					 ELSE -100
				END AS margin_pct
			FROM (
				SELECT
					src.*,
					(" . $rate . ") AS exchange_rate,
					ROUND(" . $cost . ", 4) AS product_cost
				FROM (
					SELECT
						p.product_id,
						p.model,
						p.sku,
						p.price,
						p.status,
						p.tax_class_id,
						pd.dropship_id,
						pd.channel_id,
						pd.rrp,
						pd.last_price_sync,
						dc.name AS channel_name,
						psd.name AS product_name,
						COALESCE(dv.supplier_cost, pd.supplier_cost) AS original_cost,
						UPPER(COALESCE(dv.currency_code, '" . $default . "')) AS original_currency,
						cur.`value` AS currency_value
					FROM `" . DB_PREFIX . "product_dropship` pd
					INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id)
					LEFT JOIN `" . DB_PREFIX . "product_description` psd
						   ON (psd.product_id = p.product_id AND psd.language_id = '" . (int)$this->config->get('config_language_id') . "')
					LEFT JOIN `" . DB_PREFIX . "dropship_channel` dc ON (dc.channel_id = pd.channel_id)
					LEFT JOIN `" . DB_PREFIX . "dropship_variant` dv
						   ON (dv.variant_id = (
								SELECT MAX(dv2.variant_id)
								FROM `" . DB_PREFIX . "dropship_variant` dv2
								WHERE dv2.dropship_id = pd.dropship_id AND dv2.product_id = pd.product_id AND dv2.is_active = '1'
						   ))
					LEFT JOIN `" . DB_PREFIX . "currency` cur ON (cur.code = UPPER(COALESCE(dv.currency_code, '" . $default . "')))
					WHERE pd.is_active = '1'" . ($where ? " AND " . $where : "") . "
				) src
			) x
		";
	}

	// =================================================================
	// STORE TAX RATE  (information only)
	// =================================================================

	/**
	 * Percentage tax rate for a tax class at the store address (store country and zone, default
	 * customer group), using the same rules the storefront uses for a visitor with no address.
	 * Falls back to the Default Tax Class setting when the product has no tax class. Fixed amount
	 * rates are ignored. Returns 0 when nothing applies.
	 *
	 * @param  int $tax_class_id
	 * @return float
	 */
	public function getTaxRate(int $tax_class_id): float {
		if ($tax_class_id < 1) {
			$tax_class_id = (int)$this->getSetting('default_tax_class_id', 0);
		}

		if ($tax_class_id < 1) {
			return 0.0;
		}

		if (isset($this->tax_rates[$tax_class_id])) {
			return $this->tax_rates[$tax_class_id];
		}

		$based = ($this->config->get('config_tax_default') === 'payment') ? 'payment' : 'shipping';

		$query = $this->db->query("SELECT SUM(t.rate) AS rate FROM (SELECT DISTINCT tr.tax_rate_id, tr.rate FROM `" . DB_PREFIX . "tax_rule` tru INNER JOIN `" . DB_PREFIX . "tax_rate` tr ON (tr.tax_rate_id = tru.tax_rate_id) INNER JOIN `" . DB_PREFIX . "tax_rate_to_customer_group` trcg ON (trcg.tax_rate_id = tr.tax_rate_id AND trcg.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') INNER JOIN `" . DB_PREFIX . "zone_to_geo_zone` z2gz ON (z2gz.geo_zone_id = tr.geo_zone_id AND z2gz.country_id = '" . (int)$this->config->get('config_country_id') . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->config->get('config_zone_id') . "')) WHERE tru.tax_class_id = '" . (int)$tax_class_id . "' AND tru.based IN ('" . $based . "', 'store') AND tr.type = 'P') t");

		$this->tax_rates[$tax_class_id] = (float)($query->row['rate'] ?? 0);

		return $this->tax_rates[$tax_class_id];
	}

	/**
	 * Add the information-only tax columns to product rows.
	 *
	 * @param  array $rows  Rows from getBaseSql().
	 * @return array        Rows with vat_rate and cost_inc_vat (NULL without a cost).
	 */
	protected function addTax(array $rows): array {
		foreach ($rows as &$row) {
			$row['vat_rate'] = $this->getTaxRate((int)($row['tax_class_id'] ?? 0));
			$row['cost_inc_vat'] = ($row['product_cost'] === null) ? null : round((float)$row['product_cost'] * (1 + $row['vat_rate'] / 100), 4);
		}

		unset($row);

		return $rows;
	}

	/**
	 * Tax classes for the Default Tax Class setting.
	 *
	 * @return array
	 */
	public function getTaxClasses(): array {
		return $this->db->query("SELECT tax_class_id, title FROM `" . DB_PREFIX . "tax_class` ORDER BY title ASC")->rows;
	}

	// =================================================================
	// DASHBOARD STATS
	// =================================================================

	/**
	 * Return all KPI figures for the dashboard overview tiles plus the
	 * top-10 worst-margin products table.
	 *
	 * @param  float $threshold   Minimum acceptable margin percentage.
	 * @return array {
	 *     total: int,
	 *     below_threshold: int,
	 *     negative_margin: int,
	 *     no_rate: int,
	 *     avg_margin: float,
	 *     worst_performers: array
	 * }
	 */
	public function getDashboardStats(float $threshold): array {
		$base = $this->getBaseSql();

		$q = $this->db->query("SELECT COUNT(*) AS `total`, COALESCE(SUM(t.margin_pct < '" . (float)$threshold . "'), 0) AS `below_threshold`, COALESCE(SUM(t.margin_pct <= 0), 0) AS `negative_margin`, COALESCE(SUM(t.margin_pct IS NULL), 0) AS `no_rate`, AVG(t.margin_pct) AS `avg_margin` FROM (" . $base . ") t");

		// Top 10 worst-margin products for the dashboard table (products without a rate are counted above)
		$worst = $this->db->query("SELECT t.* FROM (" . $base . ") t WHERE t.margin_pct IS NOT NULL ORDER BY t.margin_pct ASC LIMIT 10");

		return [
			'total'            => (int)$q->row['total'],
			'below_threshold'  => (int)$q->row['below_threshold'],
			'negative_margin'  => (int)$q->row['negative_margin'],
			'no_rate'          => (int)$q->row['no_rate'],
			'avg_margin'       => $q->row['avg_margin'] !== null ? round((float)$q->row['avg_margin'], 2) : 0.0,
			'worst_performers' => $this->addTax($worst->rows)
		];
	}

	// =================================================================
	// PRODUCT LIST
	// =================================================================

	/**
	 * Return a paginated, filtered list of dropship products with margin data.
	 *
	 * @param  array $data {
	 *     filter: 'all'|'below'|'negative',
	 *     start: int,
	 *     limit: int
	 * }
	 * @param  float $threshold
	 * @return array  Rows including original_cost, original_currency, exchange_rate, product_cost,
	 *                vat_rate, cost_inc_vat (information only), margin_pct (NULL without a rate), product_name, channel_name.
	 */
	public function getProducts(array $data, float $threshold): array {
		$sql = "SELECT t.* FROM (" . $this->getBaseSql() . ") t" . $this->getFilterSql($data['filter'] ?? 'all', $threshold);

		// NULL (no rate) sorts first, so the products needing attention are on top
		$sql .= " ORDER BY t.margin_pct ASC";

		if (isset($data['start'], $data['limit'])) {
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $this->addTax($query->rows);
	}

	/**
	 * Return the total row count for the filtered product list (for pagination).
	 *
	 * @param  array $data   Same filter as getProducts().
	 * @param  float $threshold
	 * @return int
	 */
	public function getTotalProducts(array $data, float $threshold): int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM (" . $this->getBaseSql() . ") t" . $this->getFilterSql($data['filter'] ?? 'all', $threshold));

		return (int)$query->row['total'];
	}

	/**
	 * Return a single product row with margin data.
	 * Used after updateProductPrice() to return the recalculated margin_pct.
	 *
	 * @param  int $product_id
	 * @return array  Empty array if product not found or not a dropship product.
	 */
	public function getProduct(int $product_id): array {
		$query = $this->db->query("SELECT t.* FROM (" . $this->getBaseSql("p.product_id = '" . (int)$product_id . "'") . ") t LIMIT 1");

		return $query->row ? $this->addTax([$query->row])[0] : [];
	}

	protected function getFilterSql(string $filter, float $threshold): string {
		if ($filter === 'below') {
			return " WHERE t.margin_pct < '" . (float)$threshold . "'";
		}

		if ($filter === 'negative') {
			return " WHERE t.margin_pct < 0";
		}

		return '';
	}

	// =================================================================
	// PRICE UPDATE
	// =================================================================

	/**
	 * Update a product's sale price in nc_product.
	 *
	 * @param  int   $product_id
	 * @param  float $price        Must be >= 0; 0.00 means "price not set".
	 */
	public function updateProductPrice(int $product_id, float $price): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `price` = '" . (float)$price . "', `date_modified` = NOW() WHERE `product_id` = '" . (int)$product_id . "'");
	}

	// =================================================================
	// SETTINGS  (nc_setting)
	// =================================================================

	/**
	 * Read a DS Margin Checker setting from nc_setting.
	 *
	 * @param  string     $key      Setting key suffix (e.g. 'threshold').
	 * @param  mixed|null $default  Value to return when the key does not exist.
	 * @return mixed
	 */
	public function getSetting(string $key, mixed $default = null): mixed {
		$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '0' AND `group` = 'ds_margin_checker' AND `key` = '" . $this->db->escape('ds_margin_checker_' . $key) . "' LIMIT 1");

		return $query->row ? $query->row['value'] : $default;
	}

	/**
	 * Write a DS Margin Checker setting to nc_setting.
	 * Mirrors the core editSetting() convention: key must be prefixed with the
	 * group name, store_id defaults to 0, serialized = 0 for scalar values.
	 * Uses DELETE + INSERT so it is safe whether the key already exists or not.
	 *
	 * @param string $key    Setting key suffix (e.g. 'threshold' → stored as 'ds_margin_checker_threshold').
	 * @param mixed  $value  Scalar value; pass an array to have it JSON-encoded and serialized = 1.
	 */
	public function setSetting(string $key, mixed $value): void {
		$full_key = 'ds_margin_checker_' . $key;
		$serialized = is_array($value) ? 1 : 0;
		$stored = $serialized ? json_encode($value) : (string)$value;

		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '0' AND `group` = 'ds_margin_checker' AND `key` = '" . $this->db->escape($full_key) . "'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `group` = 'ds_margin_checker', `key` = '" . $this->db->escape($full_key) . "', `value` = '" . $this->db->escape($stored) . "', `serialized` = '" . (int)$serialized . "'");
	}

	/**
	 * Delete all DS Margin Checker settings for store 0 from nc_setting.
	 * Called by the controller's uninstall() action; mirrors the pattern of
	 * the core deleteSetting(group, store_id) which removes all keys in a group.
	 */
	public function deleteAllSettings(): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '0' AND `group` = 'ds_margin_checker'");
	}
}
