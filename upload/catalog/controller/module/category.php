<?php
class ControllerModuleCategory extends Controller {
	private $_name = 'category';

	protected function index($setting) {
		$this->language->load('module/' . $this->_name);

		$this->data['heading_title'] = $this->language->get('heading_title');

		// Module
		$this->data['theme'] = $this->config->get($this->_name . '_theme');
		$this->data['title'] = $this->config->get($this->_name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		// Categories
		if (isset($this->request->get['path']) && !is_array($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		$this->data['category_id'] = (isset($parts[0])) ? $parts[0] : 0;
		$this->data['child_id'] = (isset($parts[1])) ? $parts[1] : 0;

		$empty_category = $this->config->get('config_empty_category');
		$product_count = $this->config->get('config_product_count');

		$this->data['categories'] = array();

		$parent_id = 0;

		$categories = $this->model_catalog_category->getCategories($parent_id);

		foreach ($categories as $category) {
			$data = array(
				'filter_category_id'  => $category['category_id'],
				'filter_sub_category' => true
			);

			if ($product_count) {
				$total = $this->model_catalog_product->getTotalProducts($data);
			} else {
				$total = 0;
			}

			$children_data = array();

			$children = $this->model_catalog_category->getCategories($category['category_id']);

			foreach ($children as $child) {
				$data = array(
					'filter_category_id'  => $child['category_id'],
					'filter_sub_category' => true
				);

				if (!$empty_category || $product_count) {
					$product_total = $this->model_catalog_product->getTotalProducts($data);
				} else {
					$product_total = 0;
				}

				if ($empty_category || $product_total > 0) {
					$children_data[] = array(
						'category_id' => $child['category_id'],
						'name'        => $child['name'] . ($product_count ? ' (' . $product_total . ')' : ''),
						'href'        => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'], 'SSL')
					);
				}
			}

			$this->data['categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name'] . ($product_count ? ' (' . $total . ')' : ''),
				'children'    => $children_data,
				'href'        => $this->url->link('product/category', 'path=' . $category['category_id'], 'SSL')
			);
		}

		// Template
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/' . $this->_name . '.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/' . $this->_name . '.tpl';
		} else {
			$this->template = 'default/template/module/' . $this->_name . '.tpl';
		}

		$this->render();
  	}
}
