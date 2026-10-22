<?php
/**
 * Class ModelAccountReview
 *
 * Customer account product reviews: purchased products list, eligibility and review storage.
 *
 * @package NivoCart
 */
class ModelAccountReview extends Model {
	/**
	 * Review status values (nc_review.status)
	 */
	public const STATUS_PENDING = 0;
	public const STATUS_APPROVED = 1;
	public const STATUS_REFUSED = 2;

	/** Cached result of the variant tables existence check. */
	private ?bool $hasVariantTables = null;

	/**
	 * Functions Add, Edit, Get
	 */
	public function addReview(int $product_id, array $data = []): int {
		$customer_id = (int)$this->customer->getId();

		// Atomic "insert if the customer has no review for this product yet"
		$this->db->query("INSERT INTO `" . DB_PREFIX . "review` (`product_id`, `customer_id`, `author`, `text`, `rating`, `status`, `date_added`, `date_modified`) SELECT '" . (int)$product_id . "', '" . $customer_id . "', '" . $this->db->escape($data['author']) . "', '" . $this->db->escape($data['text']) . "', '" . (int)$data['rating'] . "', '" . self::STATUS_PENDING . "', NOW(), NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `" . DB_PREFIX . "review` WHERE `product_id` = '" . (int)$product_id . "' AND `customer_id` = '" . $customer_id . "')");

		if ($this->db->countAffected()) {
			return $this->db->getLastId();
		}

		return 0;
	}

	public function editReview(int $review_id, array $data = []): void {
		// Only the owner can edit, and only while the review is still pending
		$this->db->query("UPDATE `" . DB_PREFIX . "review` SET `text` = '" . $this->db->escape($data['text']) . "', `rating` = '" . (int)$data['rating'] . "', `date_modified` = NOW() WHERE `review_id` = '" . (int)$review_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "' AND `status` = '" . self::STATUS_PENDING . "'");
	}

	/**
	 * Latest review written by the logged customer for a product, or an empty array.
	 */
	public function getCustomerReview(int $product_id): array {
		$query = $this->db->query("SELECT `review_id`, `author`, `text`, `rating`, `status`, `date_added` FROM `" . DB_PREFIX . "review` WHERE `product_id` = '" . (int)$product_id . "' AND `customer_id` = '" . (int)$this->customer->getId() . "' ORDER BY `review_id` DESC LIMIT 1");

		return $query->row;
	}

	/**
	 * Has the logged customer got a completed order containing this (enabled) product?
	 */
	public function hasPurchased(int $product_id): bool {
		$query = $this->db->query("SELECT op.`order_id` FROM `" . DB_PREFIX . "order_product` op INNER JOIN `" . DB_PREFIX . "order` o ON (op.`order_id` = o.`order_id`) INNER JOIN `" . DB_PREFIX . "product` p ON (op.`product_id` = p.`product_id`) WHERE p.`status` = '1' AND op.`product_id` = '" . (int)$product_id . "' AND o.`customer_id` = '" . (int)$this->customer->getId() . "' AND o.`order_status_id` = '" . (int)$this->config->get('config_complete_status_id') . "' LIMIT 1");

		return (bool)$query->num_rows;
	}

	/**
	 * Products from the customer's completed orders, with their review (if any).
	 *
	 * Optional keys in $data: product_id, start, limit
	 */
	public function getPurchasedProducts(array $data = []): array {
		$variant = $this->getVariantSql();

		$sql = "SELECT op.`product_id`, MAX(pd.`name`) AS `product_name`, " . $variant['select'] . " AS `variant`, GROUP_CONCAT(DISTINCT o.`order_id` ORDER BY o.`order_id` SEPARATOR ',') AS `order_ids`, MAX(r.`review_id`) AS `review_id`, MAX(r.`status`) AS `review_status`";
		$sql .= $this->getPurchasedFromSql($variant['join'], $data);
		$sql .= " GROUP BY op.`product_id` ORDER BY LOWER(MAX(pd.`name`)) ASC, op.`product_id` ASC";

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 10;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getPurchasedProduct(int $product_id): array {
		$results = $this->getPurchasedProducts(['product_id' => $product_id]);

		return $results ? $results[0] : [];
	}

	public function getTotalPurchasedProducts(): int {
		$sql = "SELECT COUNT(DISTINCT op.`product_id`) AS `total`" . $this->getPurchasedFromSql('', []);

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	/**
	 * Number of reviews the logged customer has written in the last $minutes minutes.
	 */
	public function getTotalReviewsSince(int $minutes): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "review` WHERE `customer_id` = '" . (int)$this->customer->getId() . "' AND `date_added` > DATE_SUB(NOW(), INTERVAL " . (int)$minutes . " MINUTE)");

		return (int)$query->row['total'];
	}

	/**
	 * Common FROM / WHERE part of the purchased products queries.
	 */
	private function getPurchasedFromSql(string $variant_join, array $data): string {
		$customer_id = (int)$this->customer->getId();

		$sql = " FROM `" . DB_PREFIX . "order_product` op";
		$sql .= " INNER JOIN `" . DB_PREFIX . "order` o ON (op.`order_id` = o.`order_id`)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "product` p ON (op.`product_id` = p.`product_id`)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "product_description` pd ON (p.`product_id` = pd.`product_id` AND pd.`language_id` = '" . (int)$this->config->get('config_language_id') . "')";
		$sql .= " LEFT JOIN (SELECT `product_id`, MAX(`review_id`) AS `review_id` FROM `" . DB_PREFIX . "review` WHERE `customer_id` = '" . $customer_id . "' GROUP BY `product_id`) rx ON (rx.`product_id` = op.`product_id`)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "review` r ON (r.`review_id` = rx.`review_id`)";
		$sql .= $variant_join;
		$sql .= " WHERE o.`customer_id` = '" . $customer_id . "' AND o.`order_status_id` = '" . (int)$this->config->get('config_complete_status_id') . "' AND p.`status` = '1'";

		if (!empty($data['product_id'])) {
			$sql .= " AND op.`product_id` = '" . (int)$data['product_id'] . "'";
		}

		return $sql;
	}

	/**
	 * Variant label SQL. Only available when the CJDropshipping modification tables exist.
	 *
	 * @return array{select: string, join: string}
	 */
	private function getVariantSql(): array {
		if ($this->hasVariantTables === null) {
			$product_variant = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "product_variant'");
			$dropship_variant = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "dropship_variant'");

			$this->hasVariantTables = ($product_variant->num_rows > 0 && $dropship_variant->num_rows > 0);
		}

		if ($this->hasVariantTables) {
			return [
				'select' => "MAX(dv.`variant_key`)",
				'join'   => " LEFT JOIN `" . DB_PREFIX . "product_variant` pv ON (pv.`product_id` = op.`product_id`) LEFT JOIN `" . DB_PREFIX . "dropship_variant` dv ON (dv.`variant_id` = pv.`variant_id`)"
			];
		}

		return [
			'select' => "''",
			'join'   => ''
		];
	}
}
