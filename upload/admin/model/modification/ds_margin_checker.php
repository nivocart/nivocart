<?php
/**
 * Class ModelModificationDsMarginChecker
 *
 * Admin model for the DS Margin Checker tool.
 * All margin calculations use:
 *   margin_pct = ((sale_price - supplier_cost * (1 + vat_rate / 100)) / sale_price) * 100
 *
 * When sale_price is zero the margin is reported as -100 (sentinel for "no price set").
 *
 * Reads from: nc_product, nc_product_description, nc_product_dropship, nc_dropship_channel.
 * Writes to:  nc_product (price only), nc_setting (threshold).
 *
 * Intended location: admin/model/modification/ds_margin_checker.php
 *
 * @package NivoCart
 */
class ModelModificationDsMarginChecker extends Model {
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
	 *     avg_margin: float,
	 *     worst_performers: array
	 * }
	 */
	public function getDashboardStats(float $threshold): array {
		// Total active dropship products
		$q = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product_dropship` pd INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id) WHERE pd.is_active = '1'");

		$total = (int)$q->row['total'];

		// Products below the margin threshold
		$q = $this->db->query("SELECT COUNT(*) AS `cnt` FROM `" . DB_PREFIX . "product_dropship` pd INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id) WHERE pd.is_active = '1' AND (CASE WHEN p.price > 0 THEN ((p.price - pd.supplier_cost * (1 + pd.vat_rate / 100)) / p.price) * 100 ELSE -100 END) < '" . (float)$threshold . "'");

		$below_threshold = (int)$q->row['cnt'];

		// Products with zero or negative margin (sale price <= cost inc VAT)
		$q = $this->db->query("SELECT COUNT(*) AS `cnt` FROM `" . DB_PREFIX . "product_dropship` pd INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id) WHERE pd.is_active = '1' AND p.price <= pd.supplier_cost * (1 + pd.vat_rate / 100)");

		$negative_margin = (int)$q->row['cnt'];

		// Portfolio average margin
		$q = $this->db->query("SELECT AVG(CASE WHEN p.price > 0 THEN ((p.price - pd.supplier_cost * (1 + pd.vat_rate / 100)) / p.price) * 100 ELSE -100 END) AS avg_margin FROM `" . DB_PREFIX . "product_dropship` pd INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id) WHERE pd.is_active = '1'");

		$avg_margin = $q->row ? round((float)$q->row['avg_margin'], 2) : 0.0;

		// Top 10 worst-margin products for the dashboard table
		$q = $this->db->query("
			SELECT
				p.product_id,
				p.model,
				p.price,
				pd.supplier_cost,
				pd.vat_rate,
				ROUND(pd.supplier_cost * (1 + pd.vat_rate / 100), 4) AS cost_inc_vat,
				CASE WHEN p.price > 0
					 THEN ROUND(((p.price - pd.supplier_cost * (1 + pd.vat_rate / 100)) / p.price) * 100, 2)
					 ELSE -100
				END AS margin_pct,
				psd.name AS product_name
			FROM `" . DB_PREFIX . "product_dropship` pd
			INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id)
			LEFT JOIN `" . DB_PREFIX . "product_description` psd
				   ON (psd.product_id = p.product_id AND psd.language_id = '" . (int)$this->config->get('config_language_id') . "')
			WHERE pd.is_active = '1'
			ORDER BY margin_pct ASC
			LIMIT 10
		");

		$worst_performers = $q->rows;

		return [
			'total'            => $total,
			'below_threshold'  => $below_threshold,
			'negative_margin'  => $negative_margin,
			'avg_margin'       => $avg_margin,
			'worst_performers' => $worst_performers
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
	 * @return array  Rows including margin_pct, cost_inc_vat, product_name, channel_name.
	 */
	public function getProducts(array $data, float $threshold): array {
		$filter = $data['filter'] ?? 'all';

		$sql = "
			SELECT
				p.product_id,
				p.model,
				p.sku,
				p.price,
				p.status,
				pd.dropship_id,
				pd.supplier_cost,
				pd.vat_rate,
				pd.rrp,
				pd.last_price_sync,
				dc.name AS channel_name,
				ROUND(pd.supplier_cost * (1 + pd.vat_rate / 100), 4) AS cost_inc_vat,
				CASE WHEN p.price > 0
					 THEN ROUND(((p.price - pd.supplier_cost * (1 + pd.vat_rate / 100)) / p.price) * 100, 2)
					 ELSE -100
				END AS margin_pct,
				psd.name AS product_name
			FROM `" . DB_PREFIX . "product_dropship` pd
			INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id)
			LEFT JOIN `" . DB_PREFIX . "product_description` psd
				   ON (psd.product_id = p.product_id AND psd.language_id = '" . (int)$this->config->get('config_language_id') . "')
			LEFT JOIN `" . DB_PREFIX . "dropship_channel` dc ON (dc.channel_id = pd.channel_id)
			WHERE pd.is_active = '1'
		";

		// HAVING filters on the computed column alias — supported by MariaDB
		if ($filter === 'below') {
			$sql .= " HAVING margin_pct < '" . (float)$threshold . "'";
		} elseif ($filter === 'negative') {
			$sql .= " HAVING margin_pct < 0";
		}

		$sql .= " ORDER BY margin_pct ASC";

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
		$filter = $data['filter'] ?? 'all';

		// Wrap in a subquery so the HAVING filter can be counted
		$inner = "
			SELECT
				CASE WHEN p.price > 0
					 THEN ROUND(((p.price - pd.supplier_cost * (1 + pd.vat_rate / 100)) / p.price) * 100, 2)
					 ELSE -100
				END AS margin_pct
			FROM `" . DB_PREFIX . "product_dropship` pd
			INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id)
			WHERE pd.is_active = '1'
		";

		if ($filter === 'below') {
			$inner .= " HAVING margin_pct < '" . (float)$threshold . "'";
		} elseif ($filter === 'negative') {
			$inner .= " HAVING margin_pct < 0";
		}

		$query = $this->db->query("SELECT COUNT(*) AS total FROM (" . $inner . ") AS sub");

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
		$query = $this->db->query("
			SELECT
				p.product_id,
				p.price,
				pd.supplier_cost,
				pd.vat_rate,
				CASE WHEN p.price > 0
					 THEN ROUND(((p.price - pd.supplier_cost * (1 + pd.vat_rate / 100)) / p.price) * 100, 2)
					 ELSE -100
				END AS margin_pct
			FROM `" . DB_PREFIX . "product_dropship` pd
			INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id)
			WHERE p.product_id = '" . (int)$product_id . "'
			AND pd.is_active = '1'
			LIMIT 1
		");

		return $query->row ?: [];
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
