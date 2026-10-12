<?php
/**
 * Class ModelFeedPinterestFeed
 *
 * @package NivoCart
 */
class ModelFeedPinterestFeed extends Model {
	/**
	 * Functions Get
	 */
	/** Cached result of the product_variant table existence check. */
	private ?bool $hasVariantTable = null;

	/**
	 * True when the dropship product_variant table exists (item_group_id can be exported).
	 */
	public function hasVariants(): bool {
		if ($this->hasVariantTable === null) {
			$check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "product_variant'");

			$this->hasVariantTable = ($check->num_rows > 0);
		}

		return $this->hasVariantTable;
	}

	/**
	 * One row per product (variants are NOT grouped). Only enabled, in stock, available products of this store.
	 */
	public function getProducts(int $start, int $limit): array {
		$customer_group_id = (int)$this->config->get('config_customer_group_id');

		$sql = "SELECT p.`product_id`, pd.`name`, pd.`description`, p.`model`, p.`sku`, p.`mpn`, p.`upc`, p.`ean`, p.`jan`, p.`isbn`, p.`image`, p.`price`, p.`tax_class_id`, p.`quantity`,";
		$sql .= " (SELECT md.`name` FROM `" . DB_PREFIX . "manufacturer_description` md WHERE md.`manufacturer_id` = p.`manufacturer_id` LIMIT 1) AS `manufacturer`,";
		$sql .= " (SELECT ps.`price` FROM `" . DB_PREFIX . "product_special` ps WHERE ps.`product_id` = p.`product_id` AND ps.`customer_group_id` = '" . $customer_group_id . "' AND ((ps.`date_start` = '0000-00-00' OR ps.`date_start` < NOW()) AND (ps.`date_end` = '0000-00-00' OR ps.`date_end` > NOW())) ORDER BY ps.`priority` ASC, ps.`price` ASC LIMIT 1) AS `special`";

		if ($this->hasVariants()) {
			$sql .= ", (SELECT pv.`pid` FROM `" . DB_PREFIX . "product_variant` pv WHERE pv.`product_id` = p.`product_id` LIMIT 1) AS `pid`";
		} else {
			$sql .= ", NULL AS `pid`";
		}

		$sql .= " FROM `" . DB_PREFIX . "product` p";
		$sql .= " INNER JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id`)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.`product_id` = p2s.`product_id`)";
		$sql .= " WHERE pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND p2s.`store_id` = '" . (int)$this->config->get('config_store_id') . "'";
		$sql .= " AND p.`status` = '1' AND p.`quantity` > 0 AND p.`date_available` <= NOW()";
		$sql .= " ORDER BY p.`product_id` ASC LIMIT " . (int)$start . "," . (int)$limit;

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/**
	 * Category ids per product id, for the given products.
	 */
	public function getProductCategories(array $product_ids): array {
		$data = [];

		if (!$product_ids) {
			return $data;
		}

		$query = $this->db->query("SELECT `product_id`, `category_id` FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` IN (" . implode(',', array_map('intval', $product_ids)) . ") ORDER BY `category_id` ASC");

		foreach ($query->rows as $row) {
			$data[(int)$row['product_id']][] = (int)$row['category_id'];
		}

		return $data;
	}

	/**
	 * Additional images per product id, for the given products.
	 */
	public function getProductImages(array $product_ids): array {
		$data = [];

		if (!$product_ids) {
			return $data;
		}

		$query = $this->db->query("SELECT `product_id`, `image` FROM `" . DB_PREFIX . "product_image` WHERE `product_id` IN (" . implode(',', array_map('intval', $product_ids)) . ") ORDER BY `sort_order` ASC");

		foreach ($query->rows as $row) {
			$data[(int)$row['product_id']][] = $row['image'];
		}

		return $data;
	}

	/**
	 * All categories as category_id => [parent_id, name] for path building.
	 */
	public function getCategories(): array {
		$data = [];

		$query = $this->db->query("SELECT c.`category_id`, c.`parent_id`, cd.`name` FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`) WHERE cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		foreach ($query->rows as $row) {
			$data[(int)$row['category_id']] = [
				'parent_id' => (int)$row['parent_id'],
				'name'      => $row['name']
			];
		}

		return $data;
	}
}
