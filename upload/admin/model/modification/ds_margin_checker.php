<?php
/**
 * Class ModelModificationDsMarginChecker
 *
 * Admin model for the DS Margin Checker tool.
 *
 * Total cost of a product, in the currency of its supplier cost and then in the store default currency:
 *   original_cost       = nc_dropship_variant.supplier_cost (fallback: nc_product_dropship.supplier_cost)
 *   shipping_cost       = nc_ds_margin_shipping.shipping_cost (typed in by hand, same currency as the cost, default 0)
 *   total_cost_original = original_cost + shipping_cost
 *   total_cost          = total_cost_original / exchange_rate           (foreign currency)
 *                         total_cost_original                            (default currency)
 *   margin_pct          = ((sale_price - total_cost) / sale_price) * 100  (sale price is ex-tax)
 *
 * No tax and no overhead costs are used here: the tool shows the cost as accurately as possible so the retail price
 * can be fine-tuned. exchange_rate is nc_currency.value of the cost currency (units of that currency for one unit
 * of the default currency). When no rate exists for the cost currency the total cost and the margin are NULL
 * ("no rate"): they are never guessed. When sale_price is zero the margin is -100 ("no price set").
 *
 * Reads from: nc_product, nc_product_description, nc_product_dropship, nc_dropship_variant,
 *             nc_dropship_channel, nc_currency and nc_ds_margin_shipping.
 * Writes to:  nc_product (price only), nc_ds_margin_shipping (own table, created on demand),
 *             nc_setting (threshold).
 *
 * Intended location: admin/model/modification/ds_margin_checker.php
 *
 * @package NivoCart
 */
class ModelModificationDsMarginChecker extends Model {
	protected bool $shipping_table_ready = false;

	// =================================================================
	// SHARED COST / MARGIN QUERY
	// =================================================================

	/**
	 * SQL of one row per active dropship product with the original cost, its currency, the
	 * exchange rate, the total cost in the default currency and the margin.
	 * Every other query in this model wraps it, so the calculation exists in exactly one place.
	 *
	 * @param  string $where  Extra condition on the product row, for example "p.product_id = '5'".
	 * @return string
	 */
	protected function getBaseSql(string $where = ''): string {
		$this->ensureShippingTable();

		$default = $this->db->escape(strtoupper(trim((string)$this->config->get('config_currency'))));

		$rate = "CASE WHEN src.original_currency = '" . $default . "' THEN 1 WHEN src.currency_value > 0 THEN src.currency_value ELSE NULL END";

		$cost = "CASE WHEN (" . $rate . ") IS NULL THEN NULL WHEN src.original_currency = '" . $default . "' THEN (src.original_cost + src.shipping_cost) ELSE (src.original_cost + src.shipping_cost) / (" . $rate . ") END";

		return "
			SELECT
				x.*,
				CASE WHEN x.total_cost IS NULL THEN NULL
					 WHEN x.price > 0 THEN ROUND(((x.price - x.total_cost) / x.price) * 100, 2)
					 ELSE -100
				END AS margin_pct
			FROM (
				SELECT
					src.*,
					(src.original_cost + src.shipping_cost) AS total_cost_original,
					(" . $rate . ") AS exchange_rate,
					ROUND(" . $cost . ", 4) AS total_cost
				FROM (
					SELECT
						p.product_id,
						p.model,
						p.sku,
						p.price,
						p.status,
						pd.dropship_id,
						pd.channel_id,
						pd.rrp,
						pd.last_price_sync,
						dc.name AS channel_name,
						psd.name AS product_name,
						COALESCE(dv.supplier_cost, pd.supplier_cost) AS original_cost,
						COALESCE(ms.shipping_cost, 0) AS shipping_cost,
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
					LEFT JOIN `" . DB_PREFIX . "ds_margin_shipping` ms ON (ms.product_id = p.product_id)
					LEFT JOIN `" . DB_PREFIX . "currency` cur ON (cur.code = UPPER(COALESCE(dv.currency_code, '" . $default . "')))
					WHERE pd.is_active = '1'" . ($where ? " AND " . $where : "") . "
				) src
			) x
		";
	}

	// =================================================================
	// SHIPPING COST  (own table, typed in by hand)
	// =================================================================

	/**
	 * Create nc_ds_margin_shipping when it does not exist yet (once per request). It holds one shipping
	 * cost per product, in the same currency as the product's supplier cost.
	 */
	public function ensureShippingTable(): void {
		if ($this->shipping_table_ready) {
			return;
		}

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ds_margin_shipping` (
			`product_id` int NOT NULL,
			`shipping_cost` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'In the currency of the supplier cost',
			`date_modified` datetime NOT NULL,
			PRIMARY KEY (`product_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='DS Margin Checker - estimated shipping cost per product'");

		$this->shipping_table_ready = true;
	}

	/**
	 * Save a product's shipping cost (0 removes the row).
	 *
	 * @param int   $product_id
	 * @param float $shipping_cost  Must be >= 0, in the currency of the supplier cost.
	 */
	public function updateProductShipping(int $product_id, float $shipping_cost): void {
		$this->ensureShippingTable();

		if ($shipping_cost > 0) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "ds_margin_shipping` SET `product_id` = '" . (int)$product_id . "', `shipping_cost` = '" . (float)$shipping_cost . "', `date_modified` = NOW() ON DUPLICATE KEY UPDATE `shipping_cost` = VALUES(`shipping_cost`), `date_modified` = NOW()");
		} else {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "ds_margin_shipping` WHERE `product_id` = '" . (int)$product_id . "'");
		}
	}

	/**
	 * Ids of the enabled CJ Dropshipping channels, whose shipping costs can be synced.
	 *
	 * @return int[]
	 */
	public function getCjChannelIds(): array {
		if (!$this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "dropship_channel'")->num_rows) {
			return [];
		}

		return array_map('intval', array_column($this->db->query("SELECT `channel_id` FROM `" . DB_PREFIX . "dropship_channel` WHERE `provider` = 'cjdropshipping' AND `status` = '1' ORDER BY `channel_id` ASC")->rows, 'channel_id'));
	}

	/**
	 * Drop the shipping table (called on uninstall).
	 */
	public function dropShippingTable(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ds_margin_shipping`");

		$this->shipping_table_ready = false;
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
			'worst_performers' => $worst->rows
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
	 * @return array  Rows including original_cost, original_currency, shipping_cost, total_cost_original,
	 *                exchange_rate, total_cost, margin_pct (NULL without a rate), product_name, channel_name.
	 */
	public function getProducts(array $data, float $threshold): array {
		$sql = "SELECT t.* FROM (" . $this->getBaseSql() . ") t" . $this->getFilterSql($data['filter'] ?? 'all', $threshold);

		// NULL (no rate) sorts first, so the products needing attention are on top
		$sql .= " ORDER BY t.margin_pct ASC";

		if (isset($data['start'], $data['limit'])) {
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
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

		return $query->row ?: [];
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

	/**
	 * Price (ex-VAT, in the store default currency) that gives exactly the wanted margin on a cost,
	 * always rounded UP so the margin never ends below the wanted one. Works in whole pence.
	 *
	 * @param  float  $cost       Total cost in the store default currency (> 0).
	 * @param  float  $margin_pct Wanted margin, 0 to below 100.
	 * @param  string $rule       none | five (next price ending in 0 or 5 pence) | nine (next price ending in 9 pence).
	 * @return int    Price in pence.
	 */
	public function calculatePricePence(float $cost, float $margin_pct, string $rule): int {
		$pence = (int)ceil(round(($cost / (1 - ($margin_pct / 100))) * 100, 6));

		if ($rule === 'five') {
			$pence = (int)(ceil($pence / 5) * 5);
		} elseif ($rule === 'nine') {
			$pence = (int)(ceil(($pence - 9) / 10) * 10 + 9);
		}

		return max(0, $pence);
	}

	/**
	 * Set the sale price of every dropship product from its Total Cost (default currency) and the margin threshold.
	 * Products with no cost (no exchange rate, or a zero cost) are skipped, prices already right are left alone.
	 *
	 * @param  float  $margin_pct
	 * @param  string $rule  none | five | nine
	 * @return array  ['updated' => int, 'unchanged' => int, 'skipped' => int]
	 */
	public function setAllPrices(float $margin_pct, string $rule): array {
		$result = ['updated' => 0, 'unchanged' => 0, 'skipped' => 0];

		$query = $this->db->query("SELECT t.product_id, t.price, t.total_cost FROM (" . $this->getBaseSql() . ") t");

		foreach ($query->rows as $row) {
			if ($row['total_cost'] === null || (float)$row['total_cost'] <= 0) {
				$result['skipped']++;
				continue;
			}

			$pence = $this->calculatePricePence((float)$row['total_cost'], $margin_pct, $rule);

			if ($pence === (int)round((float)$row['price'] * 100)) {
				$result['unchanged']++;
				continue;
			}

			$this->updateProductPrice((int)$row['product_id'], $pence / 100);

			$result['updated']++;
		}

		return $result;
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
