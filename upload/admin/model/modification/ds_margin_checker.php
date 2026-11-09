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
 * Target price (the price "Set All Prices Automatically" gives, before the rounding rule), from the pricing rules
 * (Settings): a margin up to a Breakpoint, a second margin above it, and a minimum profit (floor):
 *   profit = max( markup_low * min(cost, breakpoint) + markup_high * max(cost - breakpoint, 0), floor )
 *   target = cost + profit,   markup = margin / (100 - margin)
 * Below the breakpoint this is exactly cost / (1 - margin). When no second margin is chosen the pricing is not tiered.
 * calculateTargetPrice() is the only place that formula exists in PHP, getBaseSql() has its SQL twin (target_price).
 * A product is "below target" when its price is lower than its target price.
 *
 * No tax and no overhead costs are used here: the tool shows the cost as accurately as possible so the retail price
 * can be fine-tuned. exchange_rate is nc_currency.value of the cost currency (units of that currency for one unit
 * of the default currency). When no rate exists for the cost currency the total cost and the margin are NULL
 * ("no rate"): they are never guessed. When sale_price is zero the margin is -100 ("no price set").
 *
 * Reads from: nc_product, nc_product_description, nc_product_dropship, nc_dropship_variant,
 *             nc_dropship_channel, nc_currency and nc_ds_margin_shipping.
 *             nc_product_special (read only, to warn about Specials after a price change) and nc_currency (symbol).
 * Writes to:  nc_product (price only), nc_ds_margin_shipping (own table, created on demand),
 *             nc_setting (pricing rules).
 *
 * Intended location: admin/model/modification/ds_margin_checker.php
 *
 * @package NivoCart
 */
class ModelModificationDsMarginChecker extends Model {
	protected bool $shipping_table_ready = false;
	protected ?array $pricing_rules = null;
	protected ?array $currency_format = null;

	// =================================================================
	// SHARED COST / MARGIN QUERY
	// =================================================================

	/**
	 * SQL of one row per active dropship product with the original cost, its currency, the
	 * exchange rate, the total cost in the default currency, the margin, the target price, target_margin_pct,
	 * below_target (1 when the price is under the target price) and margin_gap (margin_pct minus target_margin_pct,
	 * the sort key: the most negative products need attention first).
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

		// Target price: the SQL twin of calculateTargetPrice() (exact, before the rounding rule). NULL without a cost.
		$rules = $this->getPricingRules();

		$profit = "GREATEST(" . number_format($this->getMarkup($rules['margin_low']), 10, '.', '') . " * LEAST(x.total_cost, " . number_format($rules['breakpoint'], 4, '.', '') . ") + " . number_format($this->getMarkup($rules['margin_high']), 10, '.', '') . " * GREATEST(x.total_cost - " . number_format($rules['breakpoint'], 4, '.', '') . ", 0), " . number_format($rules['floor'], 4, '.', '') . ")";

		return "
			SELECT
				y.*,
				CASE WHEN y.target_price IS NULL THEN 0 WHEN y.price < y.target_price - 0.0001 THEN 1 ELSE 0 END AS below_target,
				CASE WHEN y.target_price IS NULL THEN NULL ELSE ROUND(((y.target_price - y.total_cost) / y.target_price) * 100, 2) END AS target_margin_pct,
				CASE WHEN y.margin_pct IS NULL THEN NULL
					 WHEN y.target_price IS NULL THEN y.margin_pct
					 ELSE y.margin_pct - ROUND(((y.target_price - y.total_cost) / y.target_price) * 100, 2)
				END AS margin_gap
			FROM (
				SELECT
					x.*,
					CASE WHEN x.total_cost IS NULL THEN NULL
						 WHEN x.price > 0 THEN ROUND(((x.price - x.total_cost) / x.price) * 100, 2)
						 ELSE -100
					END AS margin_pct,
					CASE WHEN x.total_cost IS NULL OR x.total_cost <= 0 THEN NULL
						 ELSE ROUND(x.total_cost + " . $profit . ", 4)
					END AS target_price
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
			) y
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
	 * top-10 furthest-below-target products table.
	 *
	 * @return array {
	 *     total: int,
	 *     below_threshold: int,
	 *     negative_margin: int,
	 *     no_rate: int,
	 *     avg_margin: float,
	 *     worst_performers: array
	 * }
	 */
	public function getDashboardStats(): array {
		$base = $this->getBaseSql();

		$q = $this->db->query("SELECT COUNT(*) AS `total`, COALESCE(SUM(t.below_target), 0) AS `below_threshold`, COALESCE(SUM(t.margin_pct <= 0), 0) AS `negative_margin`, COALESCE(SUM(t.margin_pct IS NULL), 0) AS `no_rate`, AVG(t.margin_pct) AS `avg_margin` FROM (" . $base . ") t");

		// Top 10 products furthest below their target margin (products without a rate are counted above)
		$worst = $this->db->query("SELECT t.* FROM (" . $base . ") t WHERE t.margin_pct IS NOT NULL ORDER BY t.margin_gap ASC, t.product_id ASC LIMIT 10");

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
	 * @return array  Rows including original_cost, original_currency, shipping_cost, total_cost_original,
	 *                exchange_rate, total_cost, margin_pct (NULL without a rate), target_price, below_target,
	 *                product_name, channel_name.
	 */
	public function getProducts(array $data): array {
		$sql = "SELECT t.* FROM (" . $this->getBaseSql() . ") t" . $this->getFilterSql($data['filter'] ?? 'all');

		// NULL (no rate) sorts first, then the products furthest below their target margin
		$sql .= " ORDER BY t.margin_gap ASC, t.product_id ASC";

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
	 * @return int
	 */
	public function getTotalProducts(array $data): int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM (" . $this->getBaseSql() . ") t" . $this->getFilterSql($data['filter'] ?? 'all'));

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

	protected function getFilterSql(string $filter): string {
		if ($filter === 'below') {
			return " WHERE t.below_target = 1";
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
	// PRICING RULES AND TARGET PRICE
	// =================================================================

	/**
	 * The pricing rules, from the settings or from an override (the unsaved Settings form, for the sample prices).
	 * Every value is cleaned: margins between 0 and 99.99, breakpoint and floor not negative.
	 * An empty second margin means "same as the first": the pricing is not tiered.
	 *
	 * @param  array $override  Optional keys: margin, margin_high, breakpoint, floor.
	 * @return array {margin_low: float, margin_high: float, breakpoint: float, floor: float, tiered: bool}
	 */
	public function getPricingRules(array $override = []): array {
		if (!$override && $this->pricing_rules !== null) {
			return $this->pricing_rules;
		}

		$low = $override['margin'] ?? $this->getSetting('threshold', 20.0);
		$high = $override['margin_high'] ?? $this->getSetting('margin_high', '');
		$breakpoint = $override['breakpoint'] ?? $this->getSetting('breakpoint', 40);
		$floor = $override['floor'] ?? $this->getSetting('min_profit', 0);

		$low = max(0.0, min(99.99, is_numeric($low) ? (float)$low : 20.0));
		$high = ($high === '' || $high === null || !is_numeric($high)) ? $low : max(0.0, min(99.99, (float)$high));

		$rules = [
			'margin_low'  => $low,
			'margin_high' => $high,
			'breakpoint'  => max(0.0, is_numeric($breakpoint) ? (float)$breakpoint : 0.0),
			'floor'       => max(0.0, is_numeric($floor) ? (float)$floor : 0.0),
			'tiered'      => abs($high - $low) > 0.00001
		];

		if (!$override) {
			$this->pricing_rules = $rules;
		}

		return $rules;
	}

	/**
	 * Profit as a share of the cost for a margin on the price: margin 25% = markup 33.33%.
	 */
	protected function getMarkup(float $margin_pct): float {
		return $margin_pct / (100 - $margin_pct);
	}

	/**
	 * The price (ex-VAT, in the store default currency) wanted for a Total Cost, always rounded UP to whole pence
	 * so the margin never ends below the wanted one. This is the only place the pricing formula exists in PHP.
	 *
	 *   profit = max( markup_low * min(cost, breakpoint) + markup_high * max(cost - breakpoint, 0), floor )
	 *   price  = cost + profit, then the rounding rule
	 *
	 * Below the breakpoint this equals cost / (1 - margin), the same price as before tiers existed.
	 *
	 * @param  float      $total_cost_converted  Total cost in the store default currency (> 0).
	 * @param  string     $rounding              none | five (next price ending in 0 or 5 pence) | nine (next price ending in 9 pence).
	 * @param  array|null $rules                 Rules from getPricingRules(), null for the saved ones.
	 * @param  int|null   $product_id            Not used yet. Reserved for per-product or per-category rules.
	 * @return float      Price with 2 decimals, 0 when there is no cost.
	 */
	public function calculateTargetPrice(float $total_cost_converted, string $rounding = 'none', ?array $rules = null, ?int $product_id = null): float {
		if ($total_cost_converted <= 0) {
			return 0.0;
		}

		$rules = $rules ?? $this->getPricingRules();

		$profit = ($this->getMarkup($rules['margin_low']) * min($total_cost_converted, $rules['breakpoint'])) + ($this->getMarkup($rules['margin_high']) * max($total_cost_converted - $rules['breakpoint'], 0.0));

		$profit = max($profit, $rules['floor']);

		$pence = (int)ceil(round(($total_cost_converted + $profit) * 100, 6));

		if ($rounding === 'five') {
			$pence = (int)(ceil($pence / 5) * 5);
		} elseif ($rounding === 'nine') {
			$pence = (int)(ceil(($pence - 9) / 10) * 10 + 9);
		}

		return max(0, $pence) / 100;
	}

	/**
	 * Price, profit and margin for some costs under a set of rules (the Sample prices table of the Settings page).
	 *
	 * @param  float[] $costs
	 * @param  string  $rounding
	 * @param  array   $rules   From getPricingRules().
	 * @return array   Rows with cost, price, profit and margin (% of the price).
	 */
	public function getSamplePrices(array $costs, string $rounding, array $rules): array {
		$rows = [];

		foreach ($costs as $cost) {
			$price = $this->calculateTargetPrice((float)$cost, $rounding, $rules);

			$rows[] = [
				'cost'   => (float)$cost,
				'price'  => $price,
				'profit' => $price - (float)$cost,
				'margin' => $price > 0 ? (($price - (float)$cost) / $price) * 100 : 0
			];
		}

		return $rows;
	}

	/**
	 * Format an amount in the store default currency (symbol and decimals from nc_currency), for display only.
	 */
	public function formatAmount(float $amount): string {
		if ($this->currency_format === null) {
			$code = strtoupper(trim((string)$this->config->get('config_currency')));

			$query = $this->db->query("SELECT `symbol_left`, `symbol_right`, `decimal_place` FROM `" . DB_PREFIX . "currency` WHERE `code` = '" . $this->db->escape($code) . "' LIMIT 1");

			$this->currency_format = $query->row ? [
				'left'     => (string)$query->row['symbol_left'],
				'right'    => (string)$query->row['symbol_right'],
				'decimals' => (int)$query->row['decimal_place']
			] : ['left' => '', 'right' => ' ' . $code, 'decimals' => 2];
		}

		return ($amount < 0 ? '-' : '') . $this->currency_format['left'] . number_format(abs($amount), $this->currency_format['decimals']) . $this->currency_format['right'];
	}

	// =================================================================
	// SET ALL PRICES
	// =================================================================

	/**
	 * The lowest and highest price of the Specials of each product that are still running or have not started yet.
	 *
	 * @return array  product_id => ['min' => float, 'max' => float]
	 */
	protected function getSpecialRanges(): array {
		if (!$this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "product_special'")->num_rows) {
			return [];
		}

		$ranges = [];

		$query = $this->db->query("SELECT `product_id`, MIN(`price`) AS `min_price`, MAX(`price`) AS `max_price` FROM `" . DB_PREFIX . "product_special` WHERE (`date_end` IS NULL OR `date_end` = '0000-00-00' OR `date_end` >= CURDATE()) GROUP BY `product_id`");

		foreach ($query->rows as $row) {
			$ranges[(int)$row['product_id']] = ['min' => (float)$row['min_price'], 'max' => (float)$row['max_price']];
		}

		return $ranges;
	}

	/**
	 * The price change of every dropship product with a cost, from the saved pricing rules. Nothing is written.
	 * A product is flagged 'special' when one of its Specials is at or above the new price, or below its cost.
	 *
	 * @param  string $rounding  none | five | nine
	 * @return array  ['changes' => array, 'skipped' => int]
	 */
	protected function getPriceChanges(string $rounding): array {
		$changes = [];
		$skipped = 0;

		$specials = $this->getSpecialRanges();
		$rules = $this->getPricingRules();

		$query = $this->db->query("SELECT t.product_id, t.product_name, t.price, t.total_cost FROM (" . $this->getBaseSql() . ") t ORDER BY t.product_id ASC");

		foreach ($query->rows as $row) {
			if ($row['total_cost'] === null || (float)$row['total_cost'] <= 0) {
				$skipped++;
				continue;
			}

			$product_id = (int)$row['product_id'];
			$cost = (float)$row['total_cost'];
			$new = $this->calculateTargetPrice($cost, $rounding, $rules, $product_id);

			$changes[] = [
				'product_id' => $product_id,
				'name'       => (string)$row['product_name'],
				'old'        => (float)$row['price'],
				'new'        => $new,
				'special'    => isset($specials[$product_id]) && ($specials[$product_id]['max'] >= $new - 0.00001 || $specials[$product_id]['min'] < $cost)
			];
		}

		return ['changes' => $changes, 'skipped' => $skipped];
	}

	/**
	 * Preview of setAllPrices(): how many prices go up, go down or stay, the biggest changes, and how many
	 * products would have a Special at or above the new price or below their cost. Nothing is written.
	 *
	 * @param  string $rounding  none | five | nine
	 * @return array  ['up' => int, 'down' => int, 'same' => int, 'skipped' => int, 'specials' => int, 'drops' => array, 'rises' => array]
	 */
	public function previewAllPrices(string $rounding): array {
		$result = $this->getPriceChanges($rounding);

		$out = ['up' => 0, 'down' => 0, 'same' => 0, 'skipped' => $result['skipped'], 'specials' => 0, 'drops' => [], 'rises' => []];

		$drops = [];
		$rises = [];

		foreach ($result['changes'] as $change) {
			$diff = (int)round($change['new'] * 100) - (int)round($change['old'] * 100);

			if ($diff > 0) {
				$out['up']++;
				$rises[] = $change + ['diff' => $diff];
			} elseif ($diff < 0) {
				$out['down']++;
				$drops[] = $change + ['diff' => -$diff];
			} else {
				$out['same']++;
			}

			if ($change['special']) {
				$out['specials']++;
			}
		}

		usort($drops, fn($a, $b) => $b['diff'] <=> $a['diff']);
		usort($rises, fn($a, $b) => $b['diff'] <=> $a['diff']);

		foreach (array_slice($drops, 0, 5) as $row) {
			$out['drops'][] = ['name' => $row['name'], 'old' => $this->formatAmount($row['old']), 'new' => $this->formatAmount($row['new'])];
		}

		foreach (array_slice($rises, 0, 3) as $row) {
			$out['rises'][] = ['name' => $row['name'], 'old' => $this->formatAmount($row['old']), 'new' => $this->formatAmount($row['new'])];
		}

		return $out;
	}

	/**
	 * Set the sale price of every dropship product from its Total Cost (default currency) and the pricing rules.
	 * Products with no cost (no exchange rate, or a zero cost) are skipped, prices already right are left alone.
	 * Specials are never changed: the result counts the products whose Special is at or above the new price or below their cost.
	 *
	 * @param  string $rounding  none | five | nine
	 * @return array  ['updated' => int, 'unchanged' => int, 'skipped' => int, 'specials' => int]
	 */
	public function setAllPrices(string $rounding): array {
		$result = $this->getPriceChanges($rounding);

		$out = ['updated' => 0, 'unchanged' => 0, 'skipped' => $result['skipped'], 'specials' => 0];

		$flagged = [];

		foreach ($result['changes'] as $change) {
			if ($change['special']) {
				$flagged[$change['product_id']] = true;
			}

			if ((int)round($change['new'] * 100) === (int)round($change['old'] * 100)) {
				$out['unchanged']++;
				continue;
			}

			$this->updateProductPrice($change['product_id'], $change['new']);

			$out['updated']++;
		}

		$out['specials'] = count($flagged);

		return $out;
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
