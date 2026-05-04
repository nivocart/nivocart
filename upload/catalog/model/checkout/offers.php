<?php
/**
 * Class ModelCheckoutOffers
 *
 * @package NivoCart
 */
class ModelCheckoutOffers extends Model {
	/**
	 * Functions Get
	 */

	// Product Product
	public function getOfferProductProducts(): array {
		$status = true;

		$product_product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "offer_product_product` WHERE ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) AND status = '1'");

		$product_product_data = [];

		foreach ($product_product_query->rows as $result) {
			if ($result['logged'] && !$this->customer->getId()) {
				$status = false;
			}

			$product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "offer_product_product` WHERE offer_product_product_id = '" . (int)$result['offer_product_product_id'] . "'");

			if ($product_query->num_rows) {
				// Format discount values
				$discount_type = $product_query->row['type'];
				$discount_value = $product_query->row['discount'];

				if (($discount_type === 'P') && ($discount_value > 0)) {
					$discount_value = round($discount_value, 0, PHP_ROUND_HALF_UP);
				}

				if (($discount_type === 'F') && ($discount_value > 0)) {
					$discount_value = round($discount_value, 2, PHP_ROUND_HALF_UP);
				}

				$product_product_data[] = [
					'one'  => $product_query->row['product_one'],
					'two'  => $product_query->row['product_two'],
					'type' => $discount_type,
					'disc' => $discount_value
				];

			} else {
				$status = false;
			}
		}

		if ($status) {
			return $product_product_data;
		} else {
			return [];
		}
	}

	// Product Category
	public function getOfferProductCategories(): array {
		$status = true;

		$product_category_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "offer_product_category` WHERE ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) AND status = '1'");

		$product_category_data = [];

		foreach ($product_category_query->rows as $result) {
			if ($result['logged'] && !$this->customer->getId()) {
				$status = false;
			}

			$product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "offer_product_category` WHERE offer_product_category_id = '" . (int)$result['offer_product_category_id'] . "'");

			if ($product_query->num_rows) {
				// Format discount values
				$discount_type = $product_query->row['type'];
				$discount_value = $product_query->row['discount'];

				if (($discount_type === 'P') && ($discount_value > 0)) {
					$discount_value = round($discount_value, 0, PHP_ROUND_HALF_UP);
				}

				if (($discount_type === 'F') && ($discount_value > 0)) {
					$discount_value = round($discount_value, 2, PHP_ROUND_HALF_UP);
				}

				$product_category_data[] = [
					'one'  => $product_query->row['product_one'],
					'two'  => $product_query->row['category_two'],
					'type' => $discount_type,
					'disc' => $discount_value
				];

			} else {
				$status = false;
			}
		}

		if ($status) {
			return $product_category_data;
		} else {
			return [];
		}
	}

	// Category Product
	public function getOfferCategoryProducts(): array {
		$status = true;

		$category_product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "offer_category_product` WHERE ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) AND status = '1'");

		$category_product_data = [];

		foreach ($category_product_query->rows as $result) {
			if ($result['logged'] && !$this->customer->getId()) {
				$status = false;
			}

			$category_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "offer_category_product` WHERE offer_category_product_id = '" . (int)$result['offer_category_product_id'] . "'");

			if ($product_query->num_rows) {
				// Format discount values
				$discount_type = $product_query->row['type'];
				$discount_value = $product_query->row['discount'];

				if (($discount_type === 'P') && ($discount_value > 0)) {
					$discount_value = round($discount_value, 0, PHP_ROUND_HALF_UP);
				}

				if (($discount_type === 'F') && ($discount_value > 0)) {
					$discount_value = round($discount_value, 2, PHP_ROUND_HALF_UP);
				}

				$category_product_data[] = [
					'one'  => $category_query->row['category_one'],
					'two'  => $category_query->row['product_two'],
					'type' => $discount_type,
					'disc' => $discount_value
				];

			} else {
				$status = false;
			}
		}

		if ($status) {
			return $category_product_data;
		} else {
			return [];
		}
	}

	// Category Category
	public function getOfferCategoryCategories(): array {
		$status = true;

		$category_category_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "offer_category_category` WHERE ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) AND status = '1'");

		$category_category_data = [];

		foreach ($category_category_query->rows as $result) {
			if ($result['logged'] && !$this->customer->getId()) {
				$status = false;
			}

			$category_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "offer_category_category` WHERE offer_category_category_id = '" . (int)$result['offer_category_category_id'] . "'");

			if ($product_query->num_rows) {
				// Format discount values
				$discount_type = $product_query->row['type'];
				$discount_value = $product_query->row['discount'];

				if (($discount_type === 'P') && ($discount_value > 0)) {
					$discount_value = round($discount_value, 0, PHP_ROUND_HALF_UP);
				}

				if (($discount_type === 'F') && ($discount_value > 0)) {
					$discount_value = round($discount_value, 2, PHP_ROUND_HALF_UP);
				}

				$category_category_data[] = [
					'one'  => $category_query->row['category_one'],
					'two'  => $category_query->row['category_two'],
					'type' => $discount_type,
					'disc' => $discount_value
				];

			} else {
				$status = false;
			}
		}

		if ($status) {
			return $category_category_data;
		} else {
			return [];
		}
	}

	// Category List
	public function getCategoryList(int $product_id): array {
		$category_list = [];

		$query = $this->db->query("SELECT category_id FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = '" . (int)$product_id . "'");

		if ($query->num_rows) {
			foreach ($query->rows as $result) {
				$category_list[] = $result['category_id'];
			}
		}

		return $category_list;
	}
}
