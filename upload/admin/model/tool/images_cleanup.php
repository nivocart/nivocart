<?php
/**
 * Class ModelToolImagesCleanup
 *
 * @package NivoCart
 */
class ModelToolImagesCleanup extends Model {
	/**
	 * Check whether the Avasam/CJD dropship tables are installed.
	 */
	public function dropshipInstalled(): bool {
		$query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "dropship_variant'");

		return ($query->num_rows > 0);
	}

	/**
	 * Return every image path that is currently referenced in the database
	 * and begins with the given folder prefix (e.g. 'data/dropship/').
	 *
	 * Sources queried:
	 *   - nc_product.image               (main product image)
	 *   - nc_product_image.image         (additional product images, joined to nc_product)
	 *   - nc_dropship_variant.image      (dropship variant images, joined to nc_product)
	 *
	 * The joins ensure that images belonging to deleted products are not
	 * treated as referenced, even when orphaned rows remain in child tables.
	 *
	 * @param  string $folder_prefix  Path prefix as stored in the DB, e.g. 'data/dropship/'
	 * @return array<string, true>    Associative array keyed by path for O(1) lookups
	 */
	public function getReferencedImages(string $folder_prefix): array {
		$like = $this->db->escape($folder_prefix) . '%';

		$referenced = [];

		// Main product images
		$q = $this->db->query("SELECT DISTINCT `image` FROM `" . DB_PREFIX . "product` WHERE `image` LIKE '" . $like . "' AND `image` != ''");

		foreach ($q->rows as $row) {
			$referenced[$row['image']] = true;
		}

		// Additional product images — joined to nc_product so stale rows for deleted products are excluded
		$q = $this->db->query("SELECT DISTINCT pi.`image` FROM `" . DB_PREFIX . "product_image` pi INNER JOIN `" . DB_PREFIX . "product` p ON p.`product_id` = pi.`product_id` WHERE pi.`image` LIKE '" . $like . "' AND pi.`image` != ''");

		foreach ($q->rows as $row) {
			$referenced[$row['image']] = true;
		}

		// Dropship variant images — joined to nc_product so images from cleared products are treated as orphans
		if ($this->dropshipInstalled()) {
			$q = $this->db->query("SELECT DISTINCT dv.`image` FROM `" . DB_PREFIX . "dropship_variant` dv INNER JOIN `" . DB_PREFIX . "product` p ON p.`product_id` = dv.`product_id` WHERE dv.`image` LIKE '" . $like . "' AND dv.`image` != ''");

			foreach ($q->rows as $row) {
				$referenced[$row['image']] = true;
			}
		}

		return $referenced;
	}
}
