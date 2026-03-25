<?php
class ModelToolRoute extends Model {

	public function getRoutes(array $data = []): array {
		$routes_data = array();

		$parent_id = 0;

		$links_data = $this->getLinks();
		$categories_data = $this->getCategories($parent_id);

		$routes_data = array_merge($links_data, $categories_data);

		return $routes_data;
	}

	// Routes
	public function getLinks(): array {
		$links_data = array();

		$this->load->model('catalog/sitemap');

		// Common Pages
		$links_data[] = array('link'	=> 'common/home', 'name' => '');
		$links_data[] = array('link'	=> 'account/account', 'name' => '');
		$links_data[] = array('link'	=> 'account/login', 'name' => '');
		$links_data[] = array('link'	=> 'account/register', 'name' => '');
		$links_data[] = array('link'	=> 'account/edit', 'name' => '');
		$links_data[] = array('link'	=> 'account/password', 'name' => '');
		$links_data[] = array('link'	=> 'account/address', 'name' => '');
		$links_data[] = array('link'	=> 'account/wishlist', 'name' => '');
		$links_data[] = array('link'	=> 'account/order', 'name' => '');
		$links_data[] = array('link'	=> 'account/download', 'name' => '');
		$links_data[] = array('link'	=> 'account/reward', 'name' => '');
		$links_data[] = array('link'	=> 'account/return', 'name' => '');
		$links_data[] = array('link'	=> 'account/return/insert', 'name' => '');
		$links_data[] = array('link'	=> 'account/transaction', 'name' => '');
		$links_data[] = array('link'	=> 'account/newsletter', 'name' => '');
		$links_data[] = array('link'	=> 'affiliate/account', 'name' => '');
		$links_data[] = array('link'	=> 'affiliate/login', 'name' => '');
		$links_data[] = array('link'	=> 'affiliate/register', 'name' => '');
		$links_data[] = array('link'	=> 'affiliate/edit', 'name' => '');
		$links_data[] = array('link'	=> 'affiliate/password', 'name' => '');
		$links_data[] = array('link'	=> 'affiliate/payment', 'name' => '');
		$links_data[] = array('link'	=> 'affiliate/product', 'name' => '');
		$links_data[] = array('link'	=> 'affiliate/tracking', 'name' => '');
		$links_data[] = array('link'	=> 'affiliate/transaction', 'name' => '');
		$links_data[] = array('link'	=> 'product/search', 'name' => '');
		$links_data[] = array('link'	=> 'product/special', 'name' => '');
		$links_data[] = array('link'	=> 'product/compare', 'name' => '');
		$links_data[] = array('link'	=> 'product/product_list', 'name' => '');
		$links_data[] = array('link'	=> 'product/product_wall', 'name' => '');
		$links_data[] = array('link'	=> 'product/review_list', 'name' => '');
		$links_data[] = array('link'	=> 'product/category_list', 'name' => '');
		$links_data[] = array('link'	=> 'product/manufacturer', 'name' => '');
		$links_data[] = array('link'	=> 'information/contact', 'name' => '');
		$links_data[] = array('link'	=> 'information/sitemap', 'name' => '');
		$links_data[] = array('link'	=> 'information/news_list', 'name' => '');

		// Information
		$information_data = array();

		$informations = $this->model_catalog_sitemap->getAllInformations($information_data);

		foreach ($informations as $information) {
			$links_data[] = array(
				'link' => str_replace('&', '&amp;', 'information/information&information_id=' . $information['information_id']),
				'name' => ' [ ' . $information['title'] . ' ] '
			);
		}

		// Manufacturers
		$store_id = 0;

		$manufacturers = $this->model_catalog_sitemap->getAllManufacturers($store_id);

		foreach ($manufacturers as $manufacturer) {
			$links_data[] = array(
				'link' => str_replace('&', '&amp;', 'product/manufacturer/info&manufacturer_id=' . $manufacturer['manufacturer_id']),
				'name' => ' [ ' . $manufacturer['name'] . ' ] '
			);
		}

		return $links_data;
	}

	public function getCategories(int $parent_id, $current_path = ''): array {
		$categories_data = array();

		$this->load->model('catalog/sitemap');

		$store_id = 0;

		$results = $this->model_catalog_sitemap->getAllCategories($parent_id, $store_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			$categories_data[] = array(
				'link' => str_replace('&', '&amp;', 'product/category&path=' . $new_path),
				'name' => ' [ ' . $result['name'] . ' ] '
			);

			$this->getCategories($result['category_id'], $new_path);
		}

		return $categories_data;
	}
}
