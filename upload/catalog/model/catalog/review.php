<?php
/**
 * Class ModelCatalogReview
 *
 * @package NivoCart
 */
class ModelCatalogReview extends Model {
	/** @var array<string, bool> */
	private array $tables = [];

	/** @var array<int, array<int, int>> */
	private array $related = [];

	/**
	 * Functions Get, Add
	 */
	public function addReview(int $product_id, array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "review` SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$product_id . "', `text` = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW(), date_modified = NOW()");
	}

	public function getReviews(array $data = []): array {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$sql = "SELECT r.review_id, r.author, r.text, r.rating, r.date_added, pd.name, p.*,";
		$sql .= " (SELECT price FROM `" . DB_PREFIX . "product_discount` pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 0,1) AS discount,";
		$sql .= " (SELECT price FROM `" . DB_PREFIX . "product_special` ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 0,1) AS special,";
		$sql .= " (SELECT ss.name FROM `" . DB_PREFIX . "stock_status` ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, r.date_added";
		$sql .= " FROM `" . DB_PREFIX . "review` r LEFT JOIN `" . DB_PREFIX . "product` p ON (r.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) LEFT JOIN `" . DB_PREFIX . "product_to_store` p2s ON (p.product_id = p2s.product_id)";
		$sql .= " WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY r.review_id";

		$sort_data = [
			'pd.name',
			'r.review_id',
			'p.price',
			'r.rating',
			'r.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] === 'pd.name') {
				$sql .= " ORDER BY LOWER(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY r.date_added";
		}

		if (isset($data['order']) && ($data['order'] === 'DESC')) {
			$sql .= " DESC, LOWER(pd.name) DESC";
		} else {
			$sql .= " ASC, LOWER(pd.name) ASC";
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

	/**
	 * Product ids whose reviews belong to this product: the product itself plus its sibling
	 * variants (same pid in nc_product_variant). Without the variant table it is the product only.
	 *
	 * @param int $product_id
	 *
	 * @return array<int, int>
	 */
	public function getRelatedProductIds(int $product_id): array {
		if (isset($this->related[$product_id])) {
			return $this->related[$product_id];
		}

		$ids = [$product_id];

		if ($this->hasTable('product_variant')) {
			$query = $this->db->query("SELECT pv2.`product_id` FROM `" . DB_PREFIX . "product_variant` pv2 WHERE pv2.`pid` = (SELECT pv.`pid` FROM `" . DB_PREFIX . "product_variant` pv WHERE pv.`product_id` = '" . (int)$product_id . "' LIMIT 1)");

			if (is_object($query)) {
				foreach ($query->rows as $row) {
					$ids[] = (int)$row['product_id'];
				}
			}
		}

		$this->related[$product_id] = array_values(array_unique($ids));

		return $this->related[$product_id];
	}

	/**
	 * Approved reviews of a product and of its sibling variants, newest first.
	 * Each row has a "variant" label (empty when the table is missing) and the reviewed "product_id".
	 *
	 * @param int $product_id
	 * @param int $start
	 * @param int $limit
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getReviewsByProductId(int $product_id, $start = 0, $limit = 20): array {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$ids = implode(',', array_map('intval', $this->getRelatedProductIds($product_id)));

		// The columns are listed one by one: p.* would overwrite the review date with the product date
		$variant = "''";

		if ($this->hasTable('product_variant') && $this->hasTable('dropship_variant')) {
			$variant = "(SELECT dv.`variant_key` FROM `" . DB_PREFIX . "product_variant` pv INNER JOIN `" . DB_PREFIX . "dropship_variant` dv ON (dv.`variant_id` = pv.`variant_id`) WHERE pv.`product_id` = r.`product_id` LIMIT 1)";
		}

		$query = $this->db->query("SELECT r.`review_id`, r.`product_id`, r.`author`, r.`text`, r.`rating`, r.`date_added`, " . $variant . " AS `variant` FROM `" . DB_PREFIX . "review` r INNER JOIN `" . DB_PREFIX . "product` p ON (r.`product_id` = p.`product_id`) WHERE r.`product_id` IN (" . $ids . ") AND r.`status` = '1' ORDER BY r.`date_added` DESC, r.`review_id` DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getRandomReviews(int $limit): array {
		$query = $this->db->query("SELECT r.author, r.text, r.rating, r.date_added, p.* FROM `" . DB_PREFIX . "review` r LEFT JOIN `" . DB_PREFIX . "product` p ON (r.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) WHERE p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY RAND() LIMIT 0," . (int)$limit);

		return $query->rows;
	}

	public function getLatestReviews(int $limit): array {
		$query = $this->db->query("SELECT r.author, r.text, r.rating, r.date_added, p.* FROM `" . DB_PREFIX . "review` r LEFT JOIN `" . DB_PREFIX . "product` p ON (r.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) WHERE p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC LIMIT 0," . (int)$limit);

		return $query->rows;
	}

	// Totals
	public function getAverageRating(int $product_id) {
		$query = $this->db->query("SELECT AVG(rating) AS `total` FROM `" . DB_PREFIX . "review` WHERE status = '1' AND product_id = '" . (int)$product_id . "' GROUP BY product_id");

		if (isset($query->row['total'])) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	public function getTotalReviews(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "review` r LEFT JOIN `" . DB_PREFIX . "product` p ON (r.product_id = p.product_id) WHERE p.date_available <= NOW() AND p.status = '1' AND r.status = '1'");

		if (isset($query->row['total'])) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function getTotalReviewsByProductId(int $product_id): int {
		$ids = implode(',', array_map('intval', $this->getRelatedProductIds($product_id)));

		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "review` r INNER JOIN `" . DB_PREFIX . "product` p ON (r.`product_id` = p.`product_id`) WHERE r.`product_id` IN (" . $ids . ") AND r.`status` = '1'");

		return (int)$query->row['total'];
	}

	/**
	 * Cached check that a table exists (the variant tables belong to the dropship modification).
	 *
	 * @param string $table Table name without prefix
	 *
	 * @return bool
	 */
	private function hasTable(string $table): bool {
		if (!isset($this->tables[$table])) {
			$check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");

			$this->tables[$table] = ($check->num_rows > 0);
		}

		return $this->tables[$table];
	}
}
