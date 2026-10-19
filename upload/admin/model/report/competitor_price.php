<?php
/**
 * Class ModelReportCompetitorPrice
 *
 * Data layer of the Competitor Price report.
 *
 * Products are matched to competitor listings by exact EAN/GTIN only. Results of
 * each lookup are cached in nc_competitor_price, so the report itself never calls
 * the marketplace: it recalculates the comparison from the cache against the
 * current price, and a product drops out of the list as soon as it is repriced.
 *
 * Settings live in nc_setting under the group "competitor_price".
 *
 * @package NivoCart
 */
class ModelReportCompetitorPrice extends Model {
	/** @var array<int, array<int, array{type: string, rate: float}>> Tax rates already loaded, by tax class. */
	private array $tax_cache = [];

	// =================================================================
	// INSTALL
	// =================================================================

	public function tableExists(string $table): bool {
		$query = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape(DB_PREFIX . $table) . "'");

		return is_object($query) && $query->num_rows > 0;
	}

	public function isInstalled(): bool {
		return $this->tableExists('competitor_price');
	}

	public function install(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "competitor_price` (
			`competitor_price_id` int NOT NULL AUTO_INCREMENT,
			`product_id` int NOT NULL,
			`country_code` char(2) NOT NULL,
			`ean` varchar(14) NOT NULL,
			`status` varchar(16) NOT NULL DEFAULT 'none',
			`item_price` decimal(15,4) NOT NULL DEFAULT 0.0000,
			`postage` decimal(15,4) DEFAULT NULL,
			`currency_code` char(3) NOT NULL DEFAULT '',
			`seller` varchar(64) NOT NULL DEFAULT '',
			`url` text,
			`offer_count` int NOT NULL DEFAULT 0,
			`date_checked` datetime NOT NULL,
			PRIMARY KEY (`competitor_price_id`),
			UNIQUE KEY `product_country` (`product_id`, `country_code`),
			KEY `country_status` (`country_code`, `status`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
	}

	// =================================================================
	// SETTINGS
	// =================================================================

	/**
	 * @param  string $key      Key without the group prefix (e.g. "country").
	 * @param  mixed  $default  Returned when the key is not stored.
	 */
	public function getSetting(string $key, mixed $default = null): mixed {
		$query = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '0' AND `group` = 'competitor_price' AND `key` = '" . $this->db->escape('competitor_price_' . $key) . "' LIMIT 1");

		return $query->row ? $query->row['value'] : $default;
	}

	public function setSetting(string $key, string $value): void {
		$full_key = $this->db->escape('competitor_price_' . $key);

		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '0' AND `group` = 'competitor_price' AND `key` = '" . $full_key . "'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `store_id` = '0', `group` = 'competitor_price', `key` = '" . $full_key . "', `value` = '" . $this->db->escape($value) . "', `serialized` = '0'");
	}

	// =================================================================
	// EAN
	// =================================================================

	/**
	 * True for an EAN-8, UPC-A (12), EAN-13 or GTIN-14 whose GS1 check digit is correct.
	 */
	public function isValidGtin(string $ean): bool {
		if (!preg_match('/^[0-9]{8}$|^[0-9]{12,14}$/', $ean)) {
			return false;
		}

		$sum = 0;
		$weight = 1;

		for ($i = strlen($ean) - 1; $i >= 0; $i--) {
			$sum += (int)$ean[$i] * $weight;
			$weight = ($weight === 1) ? 3 : 1;
		}

		return $sum % 10 === 0;
	}

	// =================================================================
	// SCAN
	// =================================================================

	/**
	 * Next products to look up: enabled, EAN present, and never checked (or checked
	 * longer ago than $recheck_days) for this country.
	 *
	 * @return array<int, array{product_id: string, ean: string}>
	 */
	public function getScanBatch(string $country_code, int $after_id, int $limit, int $recheck_days): array {
		$query = $this->db->query("SELECT p.`product_id`, TRIM(p.`ean`) AS `ean` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "competitor_price` cp ON (cp.`product_id` = p.`product_id` AND cp.`country_code` = '" . $this->db->escape($country_code) . "') WHERE p.`status` = '1' AND TRIM(p.`ean`) REGEXP '^[0-9]{8,14}$' AND p.`product_id` > '" . (int)$after_id . "' AND (cp.`competitor_price_id` IS NULL OR cp.`date_checked` < DATE_SUB(NOW(), INTERVAL " . (int)$recheck_days . " DAY)) ORDER BY p.`product_id` ASC LIMIT " . (int)$limit);

		return $query->rows;
	}

	public function countScanCandidates(string $country_code, int $recheck_days): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product` p LEFT JOIN `" . DB_PREFIX . "competitor_price` cp ON (cp.`product_id` = p.`product_id` AND cp.`country_code` = '" . $this->db->escape($country_code) . "') WHERE p.`status` = '1' AND TRIM(p.`ean`) REGEXP '^[0-9]{8,14}$' AND (cp.`competitor_price_id` IS NULL OR cp.`date_checked` < DATE_SUB(NOW(), INTERVAL " . (int)$recheck_days . " DAY))");

		return (int)$query->row['total'];
	}

	/**
	 * Store the outcome of one lookup.
	 *
	 * @param array<string, mixed> $result status "found" | "none" | "invalid", plus the offer fields when found.
	 */
	public function saveResult(int $product_id, string $ean, string $country_code, array $result): void {
		$status = (string)($result['status'] ?? 'none');
		$postage = isset($result['postage']) ? "'" . (float)$result['postage'] . "'" : 'NULL';

		$this->db->query("INSERT INTO `" . DB_PREFIX . "competitor_price` SET `product_id` = '" . (int)$product_id . "', `country_code` = '" . $this->db->escape($country_code) . "', `ean` = '" . $this->db->escape($ean) . "', `status` = '" . $this->db->escape($status) . "', `item_price` = '" . (float)($result['item_price'] ?? 0) . "', `postage` = " . $postage . ", `currency_code` = '" . $this->db->escape((string)($result['currency'] ?? '')) . "', `seller` = '" . $this->db->escape(mb_substr((string)($result['seller'] ?? ''), 0, 64)) . "', `url` = '" . $this->db->escape((string)($result['url'] ?? '')) . "', `offer_count` = '" . (int)($result['offer_count'] ?? 0) . "', `date_checked` = NOW() ON DUPLICATE KEY UPDATE `ean` = VALUES(`ean`), `status` = VALUES(`status`), `item_price` = VALUES(`item_price`), `postage` = VALUES(`postage`), `currency_code` = VALUES(`currency_code`), `seller` = VALUES(`seller`), `url` = VALUES(`url`), `offer_count` = VALUES(`offer_count`), `date_checked` = NOW()");
	}

	public function clearCache(string $country_code): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "competitor_price` WHERE `country_code` = '" . $this->db->escape($country_code) . "'");
	}

	// =================================================================
	// STATISTICS
	// =================================================================

	/**
	 * @return array{enabled: int, no_ean: int, invalid_ean: int, checked: int}
	 */
	public function getStats(string $country_code): array {
		$enabled = (int)$this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product` WHERE `status` = '1'")->row['total'];

		$with_ean = (int)$this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "product` WHERE `status` = '1' AND TRIM(`ean`) REGEXP '^[0-9]{8,14}$'")->row['total'];

		$cache = $this->db->query("SELECT SUM(cp.`status` = 'invalid') AS `invalid`, SUM(cp.`status` IN ('found', 'none')) AS `checked` FROM `" . DB_PREFIX . "competitor_price` cp INNER JOIN `" . DB_PREFIX . "product` p ON (p.`product_id` = cp.`product_id`) WHERE p.`status` = '1' AND cp.`country_code` = '" . $this->db->escape($country_code) . "'");

		return [
			'enabled'     => $enabled,
			'no_ean'      => $enabled - $with_ean,
			'invalid_ean' => (int)($cache->row['invalid'] ?? 0),
			'checked'     => (int)($cache->row['checked'] ?? 0)
		];
	}

	// =================================================================
	// REPORT
	// =================================================================

	/**
	 * Build the report from the cache: only products where the lowest competitor
	 * price (VAT included) is below our current price (Special price when active,
	 * VAT included), biggest percentage gap first.
	 *
	 * @param  string $country_code
	 * @param  bool   $include_postage  Add the competitor's postage to their price when it is known.
	 * @param  int    $limit            Maximum rows returned (0 = no limit).
	 * @param  int    $fresh_days       When above zero, only rows checked within that many days count.
	 * @return array{rows: array<int, array<string, mixed>>, total: int}
	 */
	public function getReport(string $country_code, bool $include_postage, int $limit, int $fresh_days = 0): array {
		$base = (string)$this->config->get('config_currency');
		$group_id = (int)$this->config->get('config_customer_group_id');

		$dropship_sql = $this->tableExists('product_dropship')
			? "(SELECT MIN(pdr.`supplier_cost` * (1 + pdr.`vat_rate` / 100)) FROM `" . DB_PREFIX . "product_dropship` pdr WHERE pdr.`product_id` = p.`product_id` AND pdr.`is_active` = '1')"
			: 'NULL';

		$sql = "SELECT cp.*, p.`price`, p.`cost`, p.`tax_class_id`, pd.`name`, "
			. "(SELECT ps.`price` FROM `" . DB_PREFIX . "product_special` ps WHERE ps.`product_id` = p.`product_id` AND ps.`customer_group_id` = '" . $group_id . "' AND (ps.`date_start` = '0000-00-00' OR ps.`date_start` < NOW()) AND (ps.`date_end` = '0000-00-00' OR ps.`date_end` > NOW()) ORDER BY ps.`priority` ASC, ps.`price` ASC LIMIT 1) AS `special`, "
			. $dropship_sql . " AS `dropship_cost` "
			. "FROM `" . DB_PREFIX . "competitor_price` cp "
			. "INNER JOIN `" . DB_PREFIX . "product` p ON (p.`product_id` = cp.`product_id`) "
			. "LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (pd.`product_id` = p.`product_id` AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "') "
			. "WHERE cp.`country_code` = '" . $this->db->escape($country_code) . "' AND cp.`status` = 'found' AND p.`status` = '1'";

		if ($fresh_days > 0) {
			$sql .= " AND cp.`date_checked` >= DATE_SUB(NOW(), INTERVAL " . (int)$fresh_days . " DAY)";
		}

		$rows = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$own_ex = ((float)$row['special'] > 0) ? (float)$row['special'] : (float)$row['price'];

			// Free products cannot be compared.
			if ($own_ex <= 0 || !$this->currency->has($row['currency_code'])) {
				continue;
			}

			$own_inc = $this->withTax($own_ex, (int)$row['tax_class_id']);

			$competitor_item = $this->currency->convert((float)$row['item_price'], $row['currency_code'], $base);
			$competitor_postage = ($row['postage'] !== null) ? $this->currency->convert((float)$row['postage'], $row['currency_code'], $base) : null;
			$competitor_total = $competitor_item + (($include_postage && $competitor_postage !== null) ? $competitor_postage : 0.0);

			// Same price or higher: nothing to adjust.
			if ($competitor_total + 0.004 >= $own_inc) {
				continue;
			}

			$floor_ex = max((float)$row['cost'], (float)$row['dropship_cost']);
			$floor_inc = ($floor_ex > 0) ? $this->withTax($floor_ex, (int)$row['tax_class_id']) : null;

			$rows[] = [
				'product_id'         => (int)$row['product_id'],
				'name'               => (string)$row['name'],
				'ean'                => (string)$row['ean'],
				'has_special'        => ((float)$row['special'] > 0),
				'own_price'          => $own_inc,
				'competitor_item'    => $competitor_item,
				'competitor_postage' => $competitor_postage,
				'competitor_total'   => $competitor_total,
				'seller'             => (string)$row['seller'],
				'url'                => (string)$row['url'],
				'offer_count'        => (int)$row['offer_count'],
				'gap'                => $own_inc - $competitor_total,
				'gap_percent'        => (($own_inc - $competitor_total) / $own_inc) * 100,
				'floor'              => $floor_inc,
				'can_match'          => ($floor_inc === null) ? null : ($competitor_total >= $floor_inc),
				'date_checked'       => (string)$row['date_checked']
			];
		}

		usort($rows, static fn(array $a, array $b): int => $b['gap_percent'] <=> $a['gap_percent']);

		$total = count($rows);

		if ($limit > 0) {
			$rows = array_slice($rows, 0, $limit);
		}

		return ['rows' => $rows, 'total' => $total];
	}

	/**
	 * Price including the VAT/tax of the tax class for the store's own country.
	 * The admin has no Tax library, so the rules are read here the same way the
	 * storefront does (store country and zone, default customer group).
	 */
	private function withTax(float $value, int $tax_class_id): float {
		if ($tax_class_id < 1) {
			return $value;
		}

		if (!isset($this->tax_cache[$tax_class_id])) {
			$query = $this->db->query("SELECT DISTINCT tr2.`tax_rate_id`, tr2.`rate`, tr2.`type` FROM `" . DB_PREFIX . "tax_rule` tr1 LEFT JOIN `" . DB_PREFIX . "tax_rate` tr2 ON (tr1.`tax_rate_id` = tr2.`tax_rate_id`) INNER JOIN `" . DB_PREFIX . "tax_rate_to_customer_group` tr2cg ON (tr2.`tax_rate_id` = tr2cg.`tax_rate_id`) LEFT JOIN `" . DB_PREFIX . "zone_to_geo_zone` z2gz ON (tr2.`geo_zone_id` = z2gz.`geo_zone_id`) WHERE tr1.`tax_class_id` = '" . (int)$tax_class_id . "' AND tr2cg.`customer_group_id` = '" . (int)$this->config->get('config_customer_group_id') . "' AND z2gz.`country_id` = '" . (int)$this->config->get('config_country_id') . "' AND (z2gz.`zone_id` = '0' OR z2gz.`zone_id` = '" . (int)$this->config->get('config_zone_id') . "')");

			$this->tax_cache[$tax_class_id] = [];

			foreach ($query->rows as $rate) {
				$this->tax_cache[$tax_class_id][] = ['type' => (string)$rate['type'], 'rate' => (float)$rate['rate']];
			}
		}

		$total = $value;

		foreach ($this->tax_cache[$tax_class_id] as $rate) {
			$total += ($rate['type'] === 'P') ? ($value / 100) * $rate['rate'] : $rate['rate'];
		}

		return $total;
	}
}
