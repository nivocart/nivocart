<?php
/**
 * Class ModelModificationCjdropshipping
 *
 * Catalog-side model for CJDropshipping data.
 * Provides tracking number lookup for the Order History list.
 *
 * @package NivoCart
 */
class ModelModificationCjdropshipping extends Model {

	/**
	 * Return a comma-separated string of distinct, non-empty tracking numbers
	 * for the given order, or an empty string if none exist.
	 *
	 * @param int $order_id
	 * @return string
	 */
	public function getOrderTracking(int $order_id): string {
		$query = $this->db->query("
			SELECT COALESCE(
				GROUP_CONCAT(
					DISTINCT NULLIF(`tracking_number`, '')
					ORDER BY `dropship_order_id`
					SEPARATOR ', '
				), ''
			) AS tracking
			FROM `" . DB_PREFIX . "dropship_order`
			WHERE `order_id` = '" . (int)$order_id . "'
		");

		return $query->row['tracking'] ?? '';
	}
}
