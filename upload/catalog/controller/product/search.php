<?php
/**
 * Class ControllerProductSearch
 *
 * @package NivoCart
 */
class ControllerProductSearch extends Controller {
	private $image_product_width;
	private $image_product_height;
	private $label_size_ratio;

	/**
	 * Extract and normalise all GET params once, up front.
	 */
	private function resolveParams(): array {
		$get = $this->request->get;

		$config_limit = (int)$this->config->get('config_catalog_limit');

		$limit = isset($get['limit']) ? (int)$get['limit'] : $config_limit;
		if ($limit < 1) {
			$limit = $config_limit;
		}
		if ($limit > 100) {
			$limit = 100;
		}

		return [
			'search'       => $get['search'] ?? '',
			'tag'          => $get['tag'] ?? $get['search'] ?? '',
			'color'        => $get['color'] ?? $get['search'] ?? '',
			'description'  => $get['description'] ?? '',
			'category_id'  => $get['category_id'] ?? 0,
			'sub_category' => $get['sub_category'] ?? '',
			'sort'         => $get['sort'] ?? 'parameter.sort_order',
			'order'        => $get['order'] ?? 'ASC',
			'limit'        => $limit,
			'page'         => isset($get['page']) ? max(1, (int)$get['page']) : 1
		];
	}

	/**
	 * Build a URL query string from parameters, with optional overrides.
	 */
	private function buildUrl(array $parameters, array $include, array $overrides = []): string {
		$merged = array_merge($parameters, $overrides);

		$url = '';

		$encoded_keys = ['search', 'tag', 'color'];

		foreach ($include as $key) {
			if (!isset($merged[$key]) || $merged[$key] === '' || $merged[$key] === 0) {
				continue;
			}

			$value = in_array($key, $encoded_keys) ? urlencode(html_entity_decode($merged[$key], ENT_QUOTES, 'UTF-8')) : $merged[$key];

			$url .= '&' . $key . '=' . $value;
		}

		return $url;
	}

	public function index() {
		$this->language->load('product/search');

		$this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');

		$parameter = $this->resolveParams(); // Single read of all GET params

		// --- Page title ---
		$title_suffix = $parameter['search'] ?: ($parameter['tag'] ?: '');

		$page_title = $this->language->get('heading_title') . ($title_suffix ? ' - ' . $title_suffix : '');

		$this->document->setTitle($page_title);

		// --- Breadcrumbs ---
		$core_keys = ['search', 'tag', 'color', 'description', 'category_id', 'sub_category', 'sort', 'order', 'limit', 'page'];

		$breadcrumb_url = $this->buildUrl($parameter, $core_keys);

		$this->data['breadcrumbs'] = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false,
			],
			[
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('product/search', $breadcrumb_url, 'SSL'),
				'separator' => $this->language->get('text_separator'),
			],
		];

		$this->data['heading_title'] = $page_title;

		// --- Language strings ---
		$this->data['text_empty'] = $this->language->get('text_empty');
		$this->data['text_search'] = $this->language->get('text_search');
		$this->data['text_keyword'] = $this->language->get('text_keyword');
		$this->data['text_category'] = $this->language->get('text_category');
		$this->data['text_quantity'] = $this->language->get('text_quantity');
		$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
		$this->data['text_model'] = $this->language->get('text_model');
		$this->data['text_from'] = $this->language->get('text_from');
		$this->data['text_price'] = $this->language->get('text_price');
		$this->data['text_tax'] = $this->language->get('text_tax');
		$this->data['text_points'] = $this->language->get('text_points');
		$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare'])) ? count($this->session->data['compare']) : 0);
		$this->data['text_display'] = $this->language->get('text_display');
		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_grid'] = $this->language->get('text_grid');
		$this->data['text_sort'] = $this->language->get('text_sort');
		$this->data['text_limit'] = $this->language->get('text_limit');
		$this->data['text_offer'] = $this->language->get('text_offer');

		$this->data['entry_search'] = $this->language->get('entry_search');
		$this->data['entry_search_in'] = $this->language->get('entry_search_in');

		$this->data['lang'] = $this->language->get('code');

		$this->data['button_search'] = $this->language->get('button_search');
		$this->data['button_cart'] = $this->language->get('button_cart');
		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_login'] = $this->language->get('button_login');
		$this->data['button_quote'] = $this->language->get('button_quote');
		$this->data['button_wishlist'] = $this->language->get('button_wishlist');
		$this->data['button_compare'] = $this->language->get('button_compare');

		$this->data['compare'] = $this->url->link('product/compare', '', 'SSL');
		$this->data['login_register'] = $this->url->link('account/login', '', 'SSL');

		$this->data['dob'] = $this->config->get('config_customer_dob');
		$this->data['stock_checkout'] = $this->config->get('config_stock_checkout');
		$this->data['price_hide'] = $this->config->get('config_price_hide') ? true : false;

		$compareCount = isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0;

		$this->data['text_compare'] = sprintf($this->language->get('text_compare'), $compareCount);
		$this->data['lang'] = $this->language->get('code');
		$this->data['compare'] = $this->url->link('product/compare', '', 'SSL');
		$this->data['login_register'] = $this->url->link('account/login', '', 'SSL');
		$this->data['dob'] = $this->config->get('config_customer_dob');
		$this->data['stock_checkout'] = $this->config->get('config_stock_checkout');
		$this->data['price_hide'] = (bool)$this->config->get('config_price_hide');

		// --- Models ---
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('catalog/offer');
		$this->load->model('tool/image');
		$this->load->model('account/customer');

		$offers = $this->model_catalog_offer->getListProductOffers();

		$empty_category = $this->config->get('config_empty_category');

		// --- Categories (unchanged logic, tidied) ---
		$this->data['categories'] = $this->buildCategories($empty_category);

		// --- Products ---
		$this->data['products'] = [];

		$has_search = ($parameter['search'] !== '' || $parameter['tag'] !== '' || $parameter['color'] !== '');

		if ($has_search) {
			$filter_data = [
				'filter_name'         => $parameter['search'],
				'filter_tag'          => $parameter['tag'],
				'filter_color'        => $parameter['color'],
				'filter_description'  => $parameter['description'],
				'filter_category_id'  => $parameter['category_id'],
				'filter_sub_category' => $parameter['sub_category'],
				'sort'                => $parameter['sort'],
				'order'               => $parameter['order'],
				'start'               => ($parameter['page'] - 1) * $parameter['limit'],
				'limit'               => $parameter['limit']
			];

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$this->image_product_width = $this->config->get('config_image_product_width');
			$this->image_product_height = $this->config->get('config_image_product_height');
			$this->label_size_ratio = $this->config->get('config_label_size_ratio');

			$results = $this->model_catalog_product->getProducts($filter_data);

			$product_url = $this->buildUrl($parameter, $core_keys);

			foreach ($results as $result) {
				$this->data['products'][] = $this->formatProduct($result, $offers, $product_url);
			}

			// Shared base for sort/limit/pagination URLs (no page)
			$base_keys = ['search', 'tag', 'color', 'description', 'category_id', 'sub_category'];
			$sort_base = $this->buildUrl($parameter, $base_keys);
			$limit_base = $this->buildUrl($parameter, array_merge($base_keys, ['sort', 'order']));
			$page_base = $this->buildUrl($parameter, array_merge($base_keys, ['sort', 'order', 'limit']));

			// --- Sorts ---
			$sort_options = [
				['text_default', 'parameter.sort_order', 'ASC'],
				['text_name_asc', 'pd.name', 'ASC'],
				['text_name_desc', 'pd.name', 'DESC'],
				['text_price_asc', 'parameter.price', 'ASC'],
				['text_price_desc', 'parameter.price', 'DESC'],
			];

			if ($this->config->get('config_review_status')) {
				$sort_options[] = ['text_rating_desc', 'rating', 'DESC'];
				$sort_options[] = ['text_rating_asc', 'rating', 'ASC'];
			}

			$sort_options[] = ['text_model_asc', 'parameter.model', 'ASC'];
			$sort_options[] = ['text_model_desc', 'parameter.model', 'DESC'];

			$this->data['sorts'] = [];

			foreach ($sort_options as [$language_key, $sort_field, $sort_order]) {
				$this->data['sorts'][] = [
					'text'  => $this->language->get($language_key),
					'value' => $sort_field . '-' . $sort_order,
					'href'  => $this->url->link('product/search', 'sort=' . $sort_field . '&order=' . $sort_order . $sort_base, 'SSL'),
				];
			}

			// --- Limits ---
			$this->data['limits'] = [];

			$limits = array_unique([$this->config->get('config_catalog_limit'), 25, 50, 75, 100]);

			sort($limits);

			foreach ($limits as $value) {
				$this->data['limits'][] = [
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/search', $limit_base . '&limit=' . $value, 'SSL'),
				];
			}

			// --- Pagination ---
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $parameter['page'];
			$pagination->limit = $parameter['limit'];
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('product/search', $page_base . '&page={page}', 'SSL');

			$this->data['pagination'] = $pagination->render();
		}

		$this->data['search'] = $parameter['search'];
		$this->data['description'] = $parameter['description'];
		$this->data['category_id'] = $parameter['category_id'];
		$this->data['sub_category'] = $parameter['sub_category'];
		$this->data['sort'] = $parameter['sort'];
		$this->data['order'] = $parameter['order'];
		$this->data['limit'] = $parameter['limit'];

		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/search.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/product/search.tpl';
		} else {
			$this->template = 'default/template/product/search.tpl';
		}

		$this->children = [
			'common/content_higher', 'common/content_high',
			'common/content_left',   'common/content_right',
			'common/content_low',    'common/content_lower',
			'common/footer',         'common/header',
		];

		$this->response->setOutput($this->render());
	}

	/**
	 * Extracted from index() to keep it readable.
	 */
	private function buildCategories(bool $empty_category): array {
		$categories = [];

		foreach ($this->model_catalog_category->getCategories(0) as $cat1) {
			$level2 = [];

			foreach ($this->model_catalog_category->getCategories($cat1['category_id']) as $cat2) {
				$level3 = [];

				foreach ($this->model_catalog_category->getCategories($cat2['category_id']) as $cat3) {
					$count = $empty_category ? 0 : $this->model_catalog_product->getTotalProducts([
						'filter_category_id'  => $cat3['category_id'],
						'filter_sub_category' => true,
					]);

					if ($empty_category || $count > 0) {
						$level3[] = ['category_id' => $cat3['category_id'], 'name' => $cat3['name']];
					}
				}

				$level2[] = ['category_id' => $cat2['category_id'], 'name' => $cat2['name'], 'children' => $level3];
			}

			$categories[] = ['category_id' => $cat1['category_id'], 'name' => $cat1['name'], 'children' => $level2];
		}

		return $categories;
	}

	/**
	 * Extracted from the product loop in index().
	 */
	private function formatProduct(array $result, array $offers, string $url): array {
		$width = $this->image_product_width;
		$height = $this->image_product_height;

		if ($result['image']) {
			$image = $this->model_tool_image->resize($result['image'], $width, $height);
			$label_ratio = round(($width * $this->label_size_ratio) / 100, 0, PHP_ROUND_HALF_UP);
		} else {
			$image = false;
			$label_ratio = 50;
		}

		if ($result['label']) {
			$label = $this->model_tool_image->resize($result['label'], round($width / 3, 0, PHP_ROUND_HALF_UP), round($height / 3, 0, PHP_ROUND_HALF_UP));
			$label_style = round($width / 3, 0, PHP_ROUND_HALF_UP);
		} else {
			$label = '';
			$label_style = '';
		}

		if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
			if (($result['price'] == '0.0000') && $this->config->get('config_price_free')) {
				$price = $this->language->get('text_free');
			} else {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
			}
		} else {
			$price = false;
		}

		if ((float)$result['special']) {
			$special_label = $this->model_tool_image->resize($this->config->get('config_label_special'), $label_ratio, $label_ratio);
			$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
		} else {
			$special_label = false;
			$special = false;
		}

		$tax = $this->config->get('config_tax') ? $this->currency->format(((float)$result['special'] ?: $result['price']), $this->config->get('config_currency')) : false;
		$rating = $this->config->get('config_review_status') ? (int)$result['rating'] : false;
		$stock_label = ($result['quantity'] <= 0) ? $this->model_tool_image->resize($this->config->get('config_label_stock'), $label_ratio, $label_ratio) : false;

		$offer = in_array($result['product_id'], $offers, true);
		$offer_label = $offer ? $this->model_tool_image->resize($this->config->get('config_label_offer'), $label_ratio, $label_ratio) : false;

		$age_logged = false;
		$age_checked = false;

		if ($this->config->get('config_customer_dob') && $result['age_minimum'] > 0 && $this->customer->isLogged() && $this->customer->isSecure()) {
			$age_logged = true;
			$date_of_birth = $this->model_account_customer->getCustomerDateOfBirth($this->customer->getId());

			if ($date_of_birth && $date_of_birth !== '0000-00-00') {
				$age_checked = date_diff(date_create($date_of_birth), date_create('today'))->y >= $result['age_minimum'];
			}
		}

		return [
			'product_id'      => $result['product_id'],
			'thumb'           => $image,
			'label'           => $label,
			'label_style'     => $label_style,
			'stock_label'     => $stock_label,
			'offer_label'     => $offer_label,
			'special_label'   => $special_label,
			'offer'           => $offer,
			'manufacturer'    => $result['manufacturer'] ?: false,
			'name'            => $result['name'],
			'description'     => substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 300) . '..',
			'age_minimum'     => $result['age_minimum'] > 0 ? (int)$result['age_minimum'] : '',
			'age_logged'      => $age_logged,
			'age_checked'     => $age_checked,
			'stock_status'    => $result['stock_status'],
			'stock_quantity'  => $result['quantity'],
			'stock_remaining' => $result['subtract'] ? sprintf($this->language->get('text_remaining'), $result['quantity']) : '',
			'palette_id'      => $result['palette_id'] ? (int)$result['palette_id'] : '',
			'quote'           => $result['quote'] ? $this->url->link('information/quote', '', 'SSL') : false,
			'price'           => $price,
			'price_option'    => $this->model_catalog_product->hasOptionPriceIncrease($result['product_id']),
			'special'         => $special,
			'tax'             => $tax,
			'rating'          => $rating,
			'reviews'         => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
			'href'            => $this->url->link('product/product', 'product_id=' . $result['product_id'], 'SSL')
		];
	}

	public function livesearch() {
		$data = [];

		$template = $this->config->get('config_template');

		if (isset($this->request->get['keyword']) && $this->config->get($template . '_livesearch')) {
			$keywords = mb_strtolower($this->request->get['keyword'], 'UTF-8');

			if (mb_strlen($keywords, 'UTF-8') >= 2) {
				$customer_group_id = $this->customer->isLogged() ? $this->customer->getCustomerGroupId() : $this->config->get('config_customer_group_id');

				$search_limit = $this->config->get($template . '_livesearch_limit');

				$this->load->model('tool/search');

				$result = $this->model_tool_search->liveSearch($customer_group_id, $keywords, $search_limit);

				if ($result) {
					$data = (isset($result->rows)) ? $result->rows : $result->row;

					$this->load->model('tool/image');

					foreach ($data as $key => $values) {
						if ($values['image']) {
							$image = $this->model_tool_image->resize($values['image'], 32, 32);
						} else {
							$image = $this->model_tool_image->resize('no_image.jpg', 32, 32);
						}

						if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
							if (($values['price'] == '0.0000') && $this->config->get('config_price_free')) {
								$price = $this->language->get('text_free');
							} else {
								$price = $this->currency->format($this->tax->calculate($values['price'], $values['tax_class_id'], $this->config->get('config_tax')));
							}
						} else {
							$price = false;
						}

						if ((float)$values['special']) {
							$special = $this->currency->format($this->tax->calculate($values['special'], $values['tax_class_id'], $this->config->get('config_tax')));
						} else {
							$special = false;
						}

						if ($this->config->get('config_price_hide')) {
							$product_price = '';
						} else {
							$product_price = ($special) ? $special : $price;
						}

						$product_id = (int)$values['product_id'];

						$data[$key] = [
							'name'  => html_entity_decode($values['name'] . ' ' . $product_price, ENT_QUOTES, 'UTF-8'),
							'image' => $image,
							'alt'   => $values['name'],
							'href'  => $this->url->link('product/product', 'product_id=' . (int)$product_id, 'SSL')
						];
					}
				}
			}
		} else {
			return;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}
