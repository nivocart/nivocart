<?php
/**
 * Class ControllerProductCategoryList
 *
 * @package NivoCart
 */
class ControllerProductCategoryList extends Controller {
	private $_name = 'category_list';

	public function index() {
		$this->language->load('product/' . $this->_name);

		$this->load->model('catalog/category');
		$this->load->model('tool/image');

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		  ];

		$category_array = [];

		$categories_list = $this->model_catalog_category->getCategories($category_array);

		if ($categories_list) {
			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_empty'] = $this->language->get('text_empty');

			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['breadcrumbs'][] = [
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('product/category_list', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			];

			$this->load->model('catalog/product');

			$empty_category = $this->config->get('config_empty_category');

			$cat_width = $this->config->get('config_image_category_width');
			$cat_height = $this->config->get('config_image_category_height');

			$this->data['categories'] = [];

			foreach ($categories_list as $category_1) {
				// Image
				if (!empty($category_1['image'])) {
					$thumb = $this->model_tool_image->resize($category_1['image'], $cat_width, $cat_height);
				} else {
					$thumb = '';
				}

				// Description — strip tags, truncate to 200 chars
				$description = substr(strip_tags(html_entity_decode($category_1['description'], ENT_QUOTES, 'UTF-8')), 0, 200);

				if (strlen(strip_tags(html_entity_decode($category_1['description'], ENT_QUOTES, 'UTF-8'))) > 200) {
					$description .= '..';
				}

				// Direct child categories (level 2 only) — used as pills
				$children = [];

				$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);

				foreach ($categories_2 as $category_2) {
					$show = true;

					if (!$empty_category) {
						$data = [
							'filter_category_id'  => $category_2['category_id'],
							'filter_sub_category' => true
						];

						$product_total = $this->model_catalog_product->getTotalProducts($data);

						$show = ($product_total > 0);
					}

					if ($show) {
						$children[] = [
							'name' => $category_2['name'],
							'href' => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'], 'SSL')
						];
					}
				}

				$this->data['categories'][] = [
					'name'        => $category_1['name'],
					'thumb'       => $thumb,
					'description' => $description,
					'href'        => $this->url->link('product/category', 'path=' . $category_1['category_id'], 'SSL'),
					'children'    => $children
				];
			}

			$this->data['continue'] = $this->url->link('common/home', '', 'SSL');

			// Theme
			$this->data['template'] = $this->config->get('config_template');

			$this->resolveTemplate('product/' . $this->_name);

			$this->children = [
				'common/content_higher',
				'common/content_high',
				'common/content_left',
				'common/content_right',
				'common/content_low',
				'common/content_lower',
				'common/footer',
				'common/header'
			];

			$this->response->setOutput($this->render());

		} else {
			$this->data['breadcrumbs'][] = [
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('product/category_list', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			];

			$this->data['heading_title'] = $this->language->get('text_error');

			$this->data['text_error'] = $this->language->get('text_error');

			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['continue'] = $this->url->link('common/home', '', 'SSL');

			// Theme
			$this->data['template'] = $this->config->get('config_template');

			$this->resolveTemplate('error/not_found');

			$this->children = [
				'common/content_higher',
				'common/content_high',
				'common/content_left',
				'common/content_right',
				'common/content_low',
				'common/content_lower',
				'common/footer',
				'common/header'
			];

			$this->response->addheader($this->request->server['SERVER_PROTOCOL'] . ' 404 not found');
			$this->response->setOutput($this->render());
		}
	}
}
