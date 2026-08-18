<?php
/**
 * Class ControllerProductProduct
 *
 * @package NivoCart
 */
class ControllerProductProduct extends Controller {
	private $image_thumb_width;
	private $image_thumb_height;
	private $image_popup_width;
	private $image_popup_height;
	private $image_additional_width;
	private $image_additional_height;
	private $image_related_width;
	private $image_related_height;

	public function index() {
		$this->language->load('product/product');

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		];

		$this->load->model('catalog/category');

		if (isset($this->request->get['path']) && !is_array($this->request->get['path'])) {
			// Get url
			$page_url = array_filter([
				'sort'  => $this->request->get['sort'] ?? null,
				'order' => $this->request->get['order'] ?? null,
				'page'  => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			// Get path
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$this->data['breadcrumbs'][] = [
						'text'      => $category_info['name'],
						'href'      => $this->url->link('product/category', 'path=' . $path . $url, 'SSL'),
						'separator' => $this->language->get('text_separator')
					];
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$page_url = array_filter([
					'sort'  => $this->request->get['sort'] ?? null,
					'order' => $this->request->get['order'] ?? null,
					'limit' => $this->request->get['limit'] ?? null,
					'page'  => $this->request->get['page'] ?? null
				]);

				$url = $page_url ? '&' . http_build_query($page_url) : '';

				$this->data['breadcrumbs'][] = [
					'text'      => $category_info['name'],
					'href'      => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url, 'SSL'),
					'separator' => $this->language->get('text_separator')
				];
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$this->data['breadcrumbs'][] = [
				'text'      => $this->language->get('text_brand'),
				'href'      => $this->url->link('product/manufacturer', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			];

			$page_url = array_filter([
				'sort'  => $this->request->get['sort'] ?? null,
				'order' => $this->request->get['order'] ?? null,
				'limit' => $this->request->get['limit'] ?? null,
				'page'  => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$this->data['breadcrumbs'][] = [
					'text'      => $manufacturer_info['name'],
					'href'      => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url, 'SSL'),
					'separator' => $this->language->get('text_separator')
				];
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag']) || isset($this->request->get['color'])) {
			// Text fields: decode any HTML entities then re-encode cleanly for URL safety
			$text_fields = ['search', 'tag', 'color'];

			// Numeric/boolean fields: cast to expected types to prevent injection
			$int_fields = ['category_id', 'limit', 'page'];

			// Whitelisted string values - only allow known safe values
			$whitelist_fields = [
				'description'  => ['0', '1'],
				'sub_category' => ['0', '1'],
				'order'        => ['ASC', 'DESC'],
				'sort'         => ['p.sort_order', 'pd.name', 'p.price', 'p.model', 'rating', 'p.quantity', 'p.date_added'],
			];

			$params = [];

			foreach ($text_fields as $field) {
				if (isset($this->request->get[$field])) {
					$params[$field] = urlencode(html_entity_decode((string)$this->request->get[$field], ENT_QUOTES, 'UTF-8'));
				}
			}

			foreach ($int_fields as $field) {
				if (isset($this->request->get[$field])) {
					$params[$field] = (int)$this->request->get[$field];
				}
			}

			foreach ($whitelist_fields as $field => $allowed) {
				if (isset($this->request->get[$field]) && in_array($this->request->get[$field], $allowed, true)) {
					$params[$field] = $this->request->get[$field];
				}
			}

			$url = $params ? '&' . http_build_query($params) : '';

			$this->data['breadcrumbs'][] = [
				'text'      => $this->language->get('text_search'),
				'href'      => $this->url->link('product/search', $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			];
		}

		// Get product id. Request only once
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			// Breadcrumbs
			$text_fields = ['search', 'tag', 'color'];

			$int_fields = ['manufacturer_id', 'category_id', 'limit', 'page'];

			$whitelist_fields = [
				'description'  => ['0', '1'],
				'sub_category' => ['0', '1'],
				'order'        => ['ASC', 'DESC'],
				'sort'         => ['p.sort_order', 'pd.name', 'p.price', 'p.model', 'rating', 'p.quantity', 'p.date_added'],
			];

			// Validated pattern fields
			$pattern_fields = [
				'path'   => '/^[0-9_]+$/',       // e.g. 20_27_34
				'filter' => '/^[0-9,]+$/',       // e.g. 11,24,30
			];

			$params = [];

			foreach ($text_fields as $field) {
				if (isset($this->request->get[$field])) {
					$params[$field] = urlencode(html_entity_decode((string)$this->request->get[$field], ENT_QUOTES, 'UTF-8'));
				}
			}

			foreach ($int_fields as $field) {
				if (isset($this->request->get[$field])) {
					$params[$field] = (int)$this->request->get[$field];
				}
			}

			foreach ($whitelist_fields as $field => $allowed) {
				if (isset($this->request->get[$field]) && in_array($this->request->get[$field], $allowed, true)) {
					$params[$field] = $this->request->get[$field];
				}
			}

			foreach ($pattern_fields as $field => $pattern) {
				if (isset($this->request->get[$field]) && preg_match($pattern, $this->request->get[$field])) {
					$params[$field] = $this->request->get[$field];
				}
			}

			$url = $params ? '&' . http_build_query($params) : '';

			$this->data['breadcrumbs'][] = [
				'text'      => $product_info['name'],
				'href'      => $this->url->link('product/product', $url . '&product_id=' . $product_id, 'SSL'),
				'separator' => $this->language->get('text_separator')
			];

			$this->document->setTitle($product_info['name']);
			$this->document->setDescription($product_info['meta_description']);
			$this->document->setKeywords($product_info['meta_keyword']);

			$this->document->addLink($this->url->link('product/product', 'product_id=' . $product_id, 'SSL'), 'canonical');

			$this->document->addScript('catalog/view/javascript/jquery/panels/panels.min.js');

			$this->data['heading_title'] = $product_info['name'];

			$this->data['text_select'] = $this->language->get('text_select');
			$this->data['text_offer'] = $this->language->get('text_offer');
			$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
			$this->data['text_model'] = $this->language->get('text_model');
			$this->data['text_reward'] = $this->language->get('text_reward');
			$this->data['text_points'] = $this->language->get('text_points');
			$this->data['text_discount'] = $this->language->get('text_discount');
			$this->data['text_location'] = $this->language->get('text_location');
			$this->data['text_stock'] = $this->language->get('text_stock');
			$this->data['text_from'] = $this->language->get('text_from');
			$this->data['text_price'] = $this->language->get('text_price');
			$this->data['text_tax'] = $this->language->get('text_tax');
			$this->data['text_option'] = $this->language->get('text_option');
			$this->data['text_qty'] = $this->language->get('text_qty');
			$this->data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$this->data['text_age_minimum'] = sprintf($this->language->get('text_age_minimum'), $product_info['age_minimum']);
			$this->data['text_age_restriction'] = sprintf($this->language->get('text_age_restriction'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));
			$this->data['text_age_band'] = $this->language->get('text_age_band');
			$this->data['text_or'] = $this->language->get('text_or');
			$this->data['text_write'] = $this->language->get('text_write');
			$this->data['text_note'] = $this->language->get('text_note');
			$this->data['text_share'] = $this->language->get('text_share');
			$this->data['text_wait'] = $this->language->get('text_wait');
			$this->data['text_tags'] = $this->language->get('text_tags');

			$this->data['entry_name'] = $this->language->get('entry_name');
			$this->data['entry_review'] = $this->language->get('entry_review');
			$this->data['entry_rating'] = $this->language->get('entry_rating');
			$this->data['entry_good'] = $this->language->get('entry_good');
			$this->data['entry_bad'] = $this->language->get('entry_bad');
			$this->data['entry_captcha'] = $this->language->get('entry_captcha');

			$this->data['button_cart'] = $this->language->get('button_cart');
			$this->data['button_view'] = $this->language->get('button_view');
			$this->data['button_login'] = $this->language->get('button_login');
			$this->data['button_quote'] = $this->language->get('button_quote');
			$this->data['button_wishlist'] = $this->language->get('button_wishlist');
			$this->data['button_compare'] = $this->language->get('button_compare');
			$this->data['button_upload'] = $this->language->get('button_upload');
			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['tab_description'] = $this->language->get('tab_description');
			$this->data['tab_attribute'] = $this->language->get('tab_attribute');
			$this->data['tab_offer'] = $this->language->get('tab_offer');
			$this->data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);
			$this->data['tab_related'] = $this->language->get('tab_related');

			$this->data['gdpr_resource'] = $this->language->get('gdpr_resource');
			$this->data['dialog_resource'] = $this->language->get('dialog_resource');

			$this->data['lang'] = $this->language->get('code');

			// Buy it Now
			$this->data['stock_checkout'] = $this->config->get('config_stock_checkout');
			$this->data['buy_now_button'] = $this->config->get('config_buy_now');

			$this->data['button_buy_it_now'] = $this->language->get('button_buy_it_now');

			$this->data['buy_it_now'] = $this->url->link('checkout/checkout', '', 'SSL');

			// Image Size Variables
			$this->image_thumb_width = $this->config->get('config_image_thumb_width');
			$this->image_thumb_height = $this->config->get('config_image_thumb_height');
			$this->image_popup_width = $this->config->get('config_image_popup_width');
			$this->image_popup_height = $this->config->get('config_image_popup_height');
			$this->image_additional_width = $this->config->get('config_image_additional_width');
			$this->image_additional_height = $this->config->get('config_image_additional_height');
			$this->image_related_width = $this->config->get('config_image_related_width');
			$this->image_related_height = $this->config->get('config_image_related_height');

			// Product
			$this->data['product_id'] = (int)$this->request->get['product_id'];

			$this->load->model('catalog/review');
			$this->load->model('tool/image');

			$webp = (bool)$this->config->get('config_image_webp');

			// Image viewers — resolve active viewer once, then load assets from a data map.
			// To add a new viewer, extend $viewer_assets; no logic changes needed.
			$lightbox = match($this->config->get('config_lightbox') ?? '') {
				'viewbox'  => 'viewbox',
				'magnific' => 'magnific',
				'fancybox' => 'fancybox',
				'zoomlens' => 'zoomlens',
				default    => 'colorbox',
			};

			$this->data['lightbox'] = $lightbox;

			$viewer_assets = [
				'zoomlens' => [
					'styles'  => [
						'catalog/view/javascript/jquery/drift/drift-basic.min.css',
						'catalog/view/javascript/jquery/drift/drift-gallery.css',
					],
					'scripts' => ['catalog/view/javascript/jquery/drift/Drift.min.js'],
				],
				'viewbox' => [
					'styles'  => ['catalog/view/javascript/jquery/viewbox/viewbox.min.css'],
					'scripts' => ['catalog/view/javascript/jquery/viewbox/jquery.viewbox.min.js'],
				],
				'magnific' => [
					'styles'  => ['catalog/view/javascript/jquery/magnific/magnific.min.css'],
					'scripts' => ['catalog/view/javascript/jquery/magnific/magnific.min.js'],
				],
				'fancybox' => [
					'styles'  => ['catalog/view/javascript/jquery/fancybox-plus/css/jquery.fancybox-plus.min.css'],
					'scripts' => ['catalog/view/javascript/jquery/fancybox-plus/js/jquery.fancybox-plus.min.js'],
				],
				'colorbox' => [
					'styles'  => ['catalog/view/javascript/jquery/colorbox/colorbox.min.css'],
					'scripts' => ['catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js'],
				],
			];

			foreach ($viewer_assets[$lightbox]['styles'] as $style) {
				$this->document->addStyle($style);
			}

			foreach ($viewer_assets[$lightbox]['scripts'] as $script) {
				$this->document->addScript($script);
			}

			// Main product image and label — shared logic across all viewers
			if ($product_info['image']) {
				$this->data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->image_thumb_width, $this->image_thumb_height);
				$this->data['label'] = $this->model_tool_image->resize($product_info['label'], round(($this->image_thumb_width / 4), 0, PHP_ROUND_HALF_UP), round(($this->image_thumb_height / 4), 0, PHP_ROUND_HALF_UP));
				$this->data['label_style'] = round(($this->image_thumb_width * 0.75), 0, PHP_ROUND_HALF_UP);
				$this->data['label_height'] = round(($this->image_thumb_height * 0.25), 0, PHP_ROUND_HALF_UP);
			} else {
				$this->data['thumb'] = '';
				$this->data['label'] = '';
				$this->data['label_style'] = '';
				$this->data['label_height'] = '';
			}

			// Zoomlens additionally needs a high-res zoom image and a small gallery thumbnail
			if ($lightbox === 'zoomlens') {
				$this->data['zoom'] = $product_info['image'] ? $this->model_tool_image->resize($product_info['image'], $this->image_popup_width * 2, $this->image_popup_height * 2) : '';
				$this->data['gallery_thumb'] = $product_info['image'] ? $this->model_tool_image->resize($product_info['image'], $this->image_additional_width, $this->image_additional_height) : '';
			}

			$this->data['thumb_webp'] = ($this->data['thumb'] && $webp) ? substr($this->data['thumb'], 0, strrpos($this->data['thumb'], '.')) . '.webp' : '';

			// Responsive failsafe — always generate the safe mobile thumb at the hardcoded
			// default size (230×230) so small-screen srcset entries remain correct even if
			// an admin has increased the thumb dimensions in Settings.
			$this->data['thumb_safe'] = $product_info['image'] ? $this->model_tool_image->resize($product_info['image'], 230, 230) : '';
			$this->data['thumb_safe_webp'] = ($this->data['thumb_safe'] && $webp) ? substr($this->data['thumb_safe'], 0, strrpos($this->data['thumb_safe'], '.')) . '.webp' : '';

			// Responsive large-screen thumbs keyed to the active theme display width.
			// Normal (≤1267px container) → thumb_medium at admin "Normal Desktop Thumb" size (default 320px)
			// Wide / Unlimited (≤1907px) → also thumb_large at admin "Wide Screen Thumb" size (default 560px)
			$widescreen = $this->config->get('default_widescreen');

			$thumb_medium_size = (int)$this->config->get('config_image_thumb_width') ?: 320;
			$thumb_large_size = (int)$this->config->get('config_image_wide_thumb_width') ?: 520;

			$this->data['thumb_medium'] = '';
			$this->data['thumb_medium_webp'] = '';
			$this->data['thumb_medium_width'] = $thumb_medium_size;
			$this->data['thumb_large'] = '';
			$this->data['thumb_large_webp'] = '';
			$this->data['thumb_large_width'] = $thumb_large_size;

			if ($product_info['image']) {
				$thumb_medium = $this->model_tool_image->resize($product_info['image'], $thumb_medium_size, $thumb_medium_size);

				$this->data['thumb_medium'] = $thumb_medium;
				$this->data['thumb_medium_webp'] = ($thumb_medium && $webp) ? substr($thumb_medium, 0, strrpos($thumb_medium, '.')) . '.webp' : '';

				if ($widescreen === 'wide' || $widescreen === 'unlimited') {
					$thumb_large = $this->model_tool_image->resize($product_info['image'], $thumb_large_size, $thumb_large_size);

					$this->data['thumb_large'] = $thumb_large;
					$this->data['thumb_large_webp'] = ($thumb_large && $webp) ? substr($thumb_large, 0, strrpos($thumb_large, '.')) . '.webp' : '';
				}
			}

			if ($product_info['image']) {
				$label_ratio = round((($this->image_thumb_width * $this->config->get('config_label_size_ratio')) / 100), 0, PHP_ROUND_HALF_UP);
				$this->data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->image_popup_width, $this->image_popup_height);
			} else {
				$label_ratio = 90;
				$this->data['popup'] = '';
			}

			$this->data['popup_webp'] = ($this->data['popup'] && $webp) ? substr($this->data['popup'], 0, strrpos($this->data['popup'], '.')) . '.webp' : '';

			$this->data['images'] = [];

			$results = $this->model_catalog_product->getProductImages($product_id);

			foreach ($results as $result) {
				$add_thumb = $this->model_tool_image->resize($result['image'], $this->image_additional_width, $this->image_additional_height);
				$this->data['images'][] = [
					'zoom'       => $this->model_tool_image->resize($result['image'], $this->image_popup_width * 2, $this->image_popup_height * 2),
					'popup'      => $this->model_tool_image->resize($result['image'], $this->image_popup_width, $this->image_popup_height),
					'medium'     => $this->model_tool_image->resize($result['image'], $this->image_thumb_width, $this->image_thumb_height),
					'thumb'      => $add_thumb,
					'thumb_webp' => ($add_thumb && $webp) ? substr($add_thumb, 0, strrpos($add_thumb, '.')) . '.webp' : ''
				];
			}

			// YouTube
			if (isset($this->request->get['product_id'])) {
				$this->data['video_code'] = $this->model_catalog_product->getProductVideos($product_id);
			} else {
				$this->data['video_code'] = false;
			}

			$this->data['video_width'] = $this->image_thumb_width;
			$this->data['video_height'] = $this->image_thumb_height;

			// Barcode
			$this->load->model('tool/barcode');

			$catalog_barcode = $this->config->get('config_catalog_barcode');
			$barcode_type = $this->config->get('config_barcode_type');

			$this->data['barcode'] = ($catalog_barcode) ? $this->model_tool_barcode->getBarcode($product_info['model'], strtoupper($barcode_type), 1, 20) : '';

			$this->data['manufacturer'] = $product_info['manufacturer'];
			$this->data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id'], 'SSL');
			$this->data['model'] = $product_info['model'];

			// Fields
			$this->data['product_fields'] = [];

			$product_fields = $this->model_catalog_product->getProductFields($product_id);

			if ($product_fields) {
				foreach ($product_fields as $product_field) {
					$this->data['product_fields'][] = [
						'field_id' => $product_field['field_id'],
						'title'    => $product_field['title'],
						'text'     => html_entity_decode($product_field['text'], ENT_QUOTES, 'UTF-8')
					];
				}
			}

			$this->data['reward'] = $product_info['reward'];
			$this->data['points'] = $product_info['points'];

			// Stock Status
			if ($product_info['quantity'] <= 0) {
				$this->data['stock'] = $product_info['stock_status'];
				$this->data['stock_quantity'] = 0;
				$this->data['stock_label_large'] = $this->model_tool_image->resize($this->config->get('config_label_stock'), $label_ratio, $label_ratio);
			} elseif ($this->config->get('config_stock_display')) {
				$this->data['stock'] = $product_info['quantity'];
				$this->data['stock_quantity'] = $product_info['quantity'];
				$this->data['stock_label_large'] = '';
			} else {
				$this->data['stock'] = $this->language->get('text_instock');
				$this->data['stock_quantity'] = $product_info['quantity'];
				$this->data['stock_label_large'] = '';
			}

			// Remaining
			if ($product_info['subtract'] && ($product_info['quantity'] > 0)) {
				$this->data['stock_remaining'] = sprintf($this->language->get('text_remaining'), $product_info['quantity']);
			} else {
				$this->data['stock_remaining'] = '';
			}

			// Location
			$this->load->model('localisation/location');

			$this->data['locations'] = [];

			$location_results = $this->model_catalog_product->getProductLocationId($product_id);

			arsort($location_results);

			foreach ($location_results as $location_result) {
				if ($location_result > 0) {
					$this->data['locations'][] = $this->model_localisation_location->getLocation($location_result);
				}
			}

			// Colors
			$this->load->model('design/palette');

			$this->data['product_colors'] = [];

			$product_colors = $this->model_design_palette->getProductColors($product_id);

			if ($product_colors) {
				foreach ($product_colors as $product_color_id) {
					$palette_colors = $this->model_design_palette->getPaletteColorsByColorId($product_color_id);

					if ($palette_colors) {
						foreach ($palette_colors as $palette_color) {
							$this->data['product_colors'][] = [
								'palette_color_id' => $palette_color['palette_color_id'],
								'color'            => $palette_color['color'],
								'skin'             => $palette_color['skin'],
								'title'            => $palette_color['title']
							];
						}
					}
				}
			}

			// Price
			$this->data['price_hide'] = $this->config->get('config_price_hide') ? true : false;

			$this->data['price_option'] = $this->model_catalog_product->hasOptionPriceIncrease($product_id);

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				if (($product_info['price'] === '0.0000') && $this->config->get('config_price_free')) {
					$this->data['price'] = $this->language->get('text_free');
				} else {
					$this->data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
				}
			} else {
				$this->data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$this->data['special_label_large'] = $this->model_tool_image->resize($this->config->get('config_label_special'), $label_ratio, $label_ratio);
				$this->data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
			} else {
				$this->data['special_label_large'] = false;
				$this->data['special'] = false;
			}

			if ($this->config->get('config_tax')) {
				$this->data['tax'] = $this->currency->format(((float)$product_info['special'] ? $product_info['special'] : $product_info['price']), $this->config->get('config_currency'));
			} else {
				$this->data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($product_id);

			$this->data['discounts'] = [];

			foreach ($discounts as $discount) {
				$this->data['discounts'][] = [
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'))
				];
			}

			// Minimum age
			$this->data['dob'] = $this->config->get('config_customer_dob');
			$this->data['age_minimum'] = $product_info['age_minimum'];

			$age_logged = false;
			$age_checked = false;

			if ($this->config->get('config_customer_dob') && ($product_info['age_minimum'] > 0)) {
				if ($this->customer->isLogged() && $this->customer->isSecure()) {
					$age_logged = true;

					$this->load->model('account/customer');

					$date_of_birth = $this->model_account_customer->getCustomerDateOfBirth($this->customer->getId());

					if ($date_of_birth && ($date_of_birth !== '0000-00-00')) {
						$customer_age = date_diff(date_create($date_of_birth), date_create('today'))->y;

						if ($customer_age >= $product_info['age_minimum']) {
							$age_checked = true;
						}
					}
				}
			}

			$this->data['login_register'] = $this->url->link('account/login', '', 'SSL');

			$this->data['age_logged'] = $age_logged;
			$this->data['age_checked'] = $age_checked;

			// Quote
			if ($product_info['quote']) {
				$this->data['is_quote'] = $this->url->link('information/quote', '', 'SSL');
			} else {
				$this->data['is_quote'] = false;
			}

			// Options
			$this->data['options'] = [];

			foreach ($this->model_catalog_product->getProductOptions($product_id) as $option) {
				if ($option['type'] === 'select' || $option['type'] === 'radio' || $option['type'] === 'checkbox' || $option['type'] === 'image') {
					$option_value_data = [];

					foreach ($option['option_value'] as $option_value) {
						if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
							if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
								$price = $this->currency->format(($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false)), $this->config->get('config_currency'));
							} else {
								$price = false;
							}

							$option_value_data[] = [
								'product_option_value_id' => $option_value['product_option_value_id'],
								'option_value_id'         => $option_value['option_value_id'],
								'name'                    => $option_value['name'],
								'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
								'price'                   => $price,
								'price_prefix'            => $option_value['price_prefix']
							];
						}
					}

					$this->data['options'][] = [
						'product_option_id' => $option['product_option_id'],
						'option_id'         => $option['option_id'],
						'name'              => $option['name'],
						'type'              => $option['type'],
						'option_value'      => $option_value_data,
						'required'          => $option['required']
					];

				} elseif ($option['type'] === 'text' || $option['type'] === 'textarea' || $option['type'] === 'file' || $option['type'] === 'date' || $option['type'] === 'time') {
					$this->data['options'][] = [
						'product_option_id' => $option['product_option_id'],
						'option_id'         => $option['option_id'],
						'name'              => $option['name'],
						'type'              => $option['type'],
						'option_value'      => $option['option_value'],
						'required'          => $option['required']
					];
				}
			}

			$this->data['minimum'] = ($product_info['minimum'] > 1) ? $product_info['minimum'] : 1;

			// ShareThis
			$this->data['sharethis'] = $this->config->get('config_sharethis') ? $this->config->get('config_sharethis') : false;

			$this->data['share_sharethis'] = $this->config->get('config_share_sharethis') ? true : false;

			// Offers
			$this->load->model('catalog/offer');

			$this->data['offers'] = [];

			$product_offers = $this->model_catalog_offer->getOfferProducts($product_id);

			if ($product_offers) {
				$this->data['offer_label_large'] = $this->model_tool_image->resize($this->config->get('config_label_offer'), $label_ratio, $label_ratio);

				$label_ratio_medium = round((($this->image_related_width * $this->config->get('config_label_size_ratio')) / 100), 0, PHP_ROUND_HALF_UP);

				$this->data['offer_label_medium'] = $this->model_tool_image->resize($this->config->get('config_label_offer'), $label_ratio_medium, $label_ratio_medium);

				foreach ($product_offers as $product_offer) {
					$product_offer_one = (int)$product_offer['one'];
					$product_offer_two = (int)$product_offer['two'];

					if ($product_offer_one === $product_id) {
						$product_offer_image = $this->model_catalog_offer->getOfferProductImage($product_offer_two);

						if ($product_offer_image) {
							$offer_image = $this->model_tool_image->resize($product_offer_image, $this->image_related_width, $this->image_related_height);
						} else {
							$offer_image = false;
						}

						$offer_name = $this->model_catalog_offer->getOfferProductName($product_offer_two);
						$offer_mirror_name = $this->model_catalog_offer->getOfferProductName($product_offer_one);

						$offer_product = $product_offer_two;

					} elseif ($product_offer_two === $product_id) {
						$product_offer_image = $this->model_catalog_offer->getOfferProductImage($product_offer_one);

						if ($product_offer_image) {
							$offer_image = $this->model_tool_image->resize($product_offer_image, $this->image_related_width, $this->image_related_height);
						} else {
							$offer_image = false;
						}

						$offer_name = $this->model_catalog_offer->getOfferProductName($product_offer_one);
						$offer_mirror_name = $this->model_catalog_offer->getOfferProductName($product_offer_two);

						$offer_product = $product_offer_one;

					} else {
						$offer_image = false;
						$offer_name = '';
						$offer_mirror_name = '';
						$offer_product = '';
					}

					$product_offer_group = (string)$product_offer['group'];

					if ($product_offer_group === 'G241') {
						$offer_label = sprintf($this->language->get('text_G241'), $product_offer['type']);
					} elseif ($product_offer_group === 'G241D') {
						$offer_label = sprintf($this->language->get('text_G241D'), $offer_mirror_name, $offer_name, $product_offer['type']);
					} elseif ($product_offer_group === 'G242D') {
						$offer_label = sprintf($this->language->get('text_G242D'), $offer_mirror_name, $offer_name, $product_offer['type']);
					} elseif ($product_offer_group === 'G142D') {
						$offer_label = sprintf($this->language->get('text_G142D'), $product_offer['type'], $offer_mirror_name, $offer_name);
					} else {
						$offer_label = '';
					}

					$this->data['offers'][] = [
						'thumb'      => $offer_image,
						'thumb_webp' => ($offer_image && $webp) ? substr($offer_image, 0, strrpos($offer_image, '.')) . '.webp' : '',
						'name'       => $offer_name,
						'href'  => $this->url->link('product/product', 'product_id=' . $offer_product, 'SSL'),
						'group' => $offer_label
					];
				}

			} else {
				$this->data['offer_label_large'] = '';
				$this->data['offer_label_medium'] = '';
			}

			// Reviews - Display
			$this->data['review_block'] = $this->reviews();

			// Reviews - Write
			$review_status = $this->config->get('config_review_status');
			$review_login = $this->config->get('config_review_login');

			if ($review_status && !$review_login) {
				$this->data['review_allowed'] = true;
				$this->data['help_review_logged'] = false;
			} elseif ($review_status && $review_login && $this->customer->isLogged() && $this->customer->isSecure()) {
				$this->data['review_allowed'] = true;
				$this->data['help_review_logged'] = false;
			} else {
				$this->data['review_allowed'] = false;
				$this->data['help_review_logged'] = $this->language->get('help_review_logged');
			}

			$this->data['review_status'] = $review_status;

			$this->data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$this->data['rating'] = (int)$product_info['rating'];
			$this->data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

			// Captcha required
			$this->data['captcha'] = '';
			// Generate session captcha
			$this->load->library('captcha');

			$captcha = new Captcha();

			$this->session->data['captcha'] = $captcha->getCode();

			$this->data['captcha_image'] = $this->session->data['captcha'];

			// Attributes
			$attribute_groups = $this->model_catalog_product->getProductAttributes($product_id);

			$this->data['attribute_groups'] = array_reverse($attribute_groups, true);

			// Related
			$related_offers = $this->model_catalog_offer->getListProductOffers();

			$this->data['products'] = [];

			$results = $this->model_catalog_product->getProductRelated($product_id);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->image_related_width, $this->image_related_height);
					$label_ratio = round((($this->image_related_width * $this->config->get('config_label_size_ratio')) / 100), 0, PHP_ROUND_HALF_UP);
				} else {
					$image = false;
					$label_ratio = 50;
				}

				if ($result['label']) {
					$label = $this->model_tool_image->resize($result['label'], round(($this->image_related_width / 3), 0, PHP_ROUND_HALF_UP), round(($this->image_related_height / 3), 0, PHP_ROUND_HALF_UP));
					$label_style = round(($this->image_related_width / 3), 0, PHP_ROUND_HALF_UP);
				} else {
					$label = '';
					$label_style = '';
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					if (($result['price'] === '0.0000') && $this->config->get('config_price_free')) {
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

				$rating = $this->config->get('config_review_status') ? (int)$result['rating'] : false;
				$stock_label = ($result['quantity'] <= 0) ? $this->model_tool_image->resize($this->config->get('config_label_stock'), $label_ratio, $label_ratio) : false;

				if (in_array($result['product_id'], $related_offers, true)) {
					$offer_label = $this->model_tool_image->resize($this->config->get('config_label_offer'), $label_ratio, $label_ratio);
					$offer = true;
				} else {
					$offer_label = false;
					$offer = false;
				}

				$quote = $result['quote'] ? $this->url->link('information/quote', '', 'SSL') : false;

				$this->data['products'][] = [
					'product_id'      => $result['product_id'],
					'thumb'           => $image,
					'thumb_webp'      => ($image && $webp) ? substr($image, 0, strrpos($image, '.')) . '.webp' : '',
					'label'           => $label,
					'label_style'     => $label_style,
					'stock_label'     => $stock_label,
					'offer_label'     => $offer_label,
					'special_label'   => $special_label,
					'offer'           => $offer,
					'name'            => $result['name'],
					'stock_status'    => $result['stock_status'],
					'stock_quantity'  => $result['quantity'],
					'stock_remaining' => $result['subtract'] ? sprintf($this->language->get('text_remaining'), $result['quantity']) : '',
					'quote'           => $quote,
					'price'           => $price,
					'price_option'    => $this->model_catalog_product->hasOptionPriceIncrease($result['product_id']),
					'special'         => $special,
					'rating'          => $rating,
					'reviews'         => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
					'href'            => $this->url->link('product/product', 'product_id=' . $result['product_id'], 'SSL')
				];
			}

			// Tags
			$this->data['tags'] = [];

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$tag = trim(str_replace('&', '&amp;', (string)$tag));

					$this->data['tags'][] = [
						'tag'  => $tag,
						'href' => $this->url->link('product/search', 'search=' . $tag . '&tag=' . $tag, 'SSL')
					];
				}
			}

			// Update viewed
			$this->model_catalog_product->updateViewed($product_id);

			// Theme
			$this->data['template'] = $this->config->get('config_template');

			$this->resolveTemplate('product/product');

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
			// Breadcrumbs
			$text_fields = ['search', 'tag', 'color'];

			$int_fields = ['manufacturer_id', 'category_id', 'limit', 'page'];

			$whitelist_fields = [
				'description'  => ['0', '1'],
				'sub_category' => ['0', '1'],
				'order'        => ['ASC', 'DESC'],
				'sort'         => ['p.sort_order', 'pd.name', 'p.price', 'p.model', 'rating', 'p.quantity', 'p.date_added'],
			];

			// Validated pattern fields
			$pattern_fields = [
				'path'   => '/^[0-9_]+$/',       // e.g. 20_27_34
				'filter' => '/^[0-9,]+$/',       // e.g. 11,24,30
			];

			$params = [];

			foreach ($text_fields as $field) {
				if (isset($this->request->get[$field])) {
					$params[$field] = urlencode(html_entity_decode((string)$this->request->get[$field], ENT_QUOTES, 'UTF-8'));
				}
			}

			foreach ($int_fields as $field) {
				if (isset($this->request->get[$field])) {
					$params[$field] = (int)$this->request->get[$field];
				}
			}

			foreach ($whitelist_fields as $field => $allowed) {
				if (isset($this->request->get[$field]) && in_array($this->request->get[$field], $allowed, true)) {
					$params[$field] = $this->request->get[$field];
				}
			}

			foreach ($pattern_fields as $field => $pattern) {
				if (isset($this->request->get[$field]) && preg_match($pattern, $this->request->get[$field])) {
					$params[$field] = $this->request->get[$field];
				}
			}

			$url = $params ? '&' . http_build_query($params) : '';

			$this->data['breadcrumbs'][] = [
				'text'      => $product_info['name'],
				'href'      => $this->url->link('product/product', $url . '&product_id=' . $product_id, 'SSL'),
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

	public function reviews() {
		$this->language->load('product/product');

		$this->load->model('catalog/review');

		$this->data['text_latest'] = $this->language->get('text_latest');
		$this->data['text_on'] = $this->language->get('text_on');
		$this->data['text_no_reviews'] = $this->language->get('text_no_reviews');

		$product_id = (int)$this->request->get['product_id'];

		$this->data['latest_reviews'] = [];

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($product_id);

		$page = isset($this->request->get['page']) ? $this->request->get['page'] : 1;

		$results = $this->model_catalog_review->getReviewsByProductId($product_id, ($page - 1) * 3, 3);

		foreach ($results as $result) {
			$this->data['latest_reviews'][] = [
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'reviews'    => sprintf($this->language->get('text_reviews'), (int)$review_total),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			];
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 3;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('product/product/product_reviews', 'product_id=' . $product_id . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('product/product_reviews');

		return $this->render();
	}

	public function write() {
		$this->language->load('product/product');

		$this->load->model('catalog/review');

		$product_id = isset($this->request->post['product_id']) ? (int)$this->request->post['product_id'] : (int)$this->request->get['product_id'];

		$json = [];

		if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->config->get('config_review_status')) {
			if (empty($this->request->post['name']) || (mb_strlen($this->request->post['name'], 'UTF-8') < 3) || (mb_strlen($this->request->post['name'], 'UTF-8') > 25)) {
				$json['error'] = $this->language->get('error_name');
			} elseif (empty($this->request->post['text']) || (mb_strlen($this->request->post['text'], 'UTF-8') < 25) || (mb_strlen($this->request->post['text'], 'UTF-8') > 1000)) {
				$json['error'] = $this->language->get('error_text');
			} elseif (empty($this->request->post['rating'])) {
				$json['error'] = $this->language->get('error_rating');
			} elseif (empty($this->session->data['captcha']) || ($this->session->data['captcha'] !== $this->request->post['captcha'])) {
				$json['error'] = $this->language->get('error_captcha');
			}

			if (!isset($json['error'])) {
				unset($this->session->data['captcha']);

				$this->model_catalog_review->addReview($product_id, $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function upload() {
		$this->language->load('product/product');

		$json = [];

		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

			if ((mb_strlen($filename, 'UTF-8') < 3) || (mb_strlen($filename, 'UTF-8') > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Allowed file extension types
			$allowed = [];

			$filetypes = explode("\n", str_replace(["\r\n", "\r"], "\n", $this->config->get('config_file_extension_allowed')));

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Allowed file mime types
			$allowed = [];

			$filetypes = explode("\n", str_replace(["\r\n", "\r"], "\n", $this->config->get('config_file_mime_allowed')));

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($this->request->files['file']['tmp_name']);

			if (preg_match('/\<\?php/i', $content)) {
				$json['error'] = $this->language->get('error_filetype');
			}

			if ($this->request->files['file']['error'] !== UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}

		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json && is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
			$file = basename($filename) . '.' . substr(md5(mt_rand()), 0, 10);

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

			// Hide the uploaded file name so people can not link to it directly.
			$this->load->model('tool/upload');

			$json['code'] = $this->model_tool_upload->addUpload($filename, $file);

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
