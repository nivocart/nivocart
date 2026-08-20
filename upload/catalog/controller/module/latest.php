<?php
/**
 * Class ControllerModuleLatest
 *
 * @package NivoCart
 */
class ControllerModuleLatest extends Controller {
	private $name = 'latest';

	protected function index($setting) {
		static $module = 0;

		$this->language->load('module/' . $this->name);

		$this->data['heading_title'] = $this->language->get('heading_title');

		// Module
		$this->data['theme'] = $this->config->get($this->name . '_theme');
		$this->data['title'] = $this->config->get($this->name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$this->data['text_from'] = $this->language->get('text_from');
		$this->data['text_offer'] = $this->language->get('text_offer');

		$this->data['lang'] = $this->language->get('code');

		$this->data['button_cart'] = $this->language->get('button_cart');
		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_quote'] = $this->language->get('button_quote');
		$this->data['button_compare'] = $this->language->get('button_compare');
		$this->data['button_wishlist'] = $this->language->get('button_wishlist');

		$this->data['viewproduct'] = $this->config->get($this->name . '_viewproduct');
		$this->data['addproduct'] = $this->config->get($this->name . '_addproduct');

		$this->data['stock_checkout'] = $this->config->get('config_stock_checkout');
		$this->data['price_hide'] = $this->config->get('config_price_hide') ? true : false;
		$this->data['show_mini_label'] = (!$this->config->get('config_hide_mini_label') && $this->config->get($this->name . '_show_mini_label')) ? true : false;

		$display_style = $setting['style'] ? $setting['style'] : 'box';

		$this->data['boxed'] = ($display_style === 'box') ? true : false;

		$this->load->model('catalog/product');
		$this->load->model('catalog/offer');
		$this->load->model('tool/image');

		$webp = (bool)$this->config->get('config_image_webp');

		$offers = $this->model_catalog_offer->getListProductOffers();

		$this->data['products'] = [];

		$results = $this->model_catalog_product->getLatestProducts($setting['limit']);

		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']);
				$label_ratio = round((($setting['image_width'] * $this->config->get('config_label_size_ratio')) / 100), 0, PHP_ROUND_HALF_UP);
			} else {
				$image = false;
				$label_ratio = 50;
			}
			$image_webp = ($image && $webp) ? substr($image, 0, strrpos($image, '.')) . '.webp' : '';

			if ($result['label']) {
				$label = $this->model_tool_image->resize($result['label'], round(($setting['image_width'] / 3), 0, PHP_ROUND_HALF_UP), round(($setting['image_height'] / 3), 0, PHP_ROUND_HALF_UP));
				$label_style = round(($setting['image_width'] / 3), 0, PHP_ROUND_HALF_UP);
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

			$rating = $this->config->get('config_review_status') ? $result['rating'] : false;

			$stock_label = ($result['quantity'] <= 0) ? $this->model_tool_image->resize($this->config->get('config_label_stock'), $label_ratio, $label_ratio) : false;

			if (in_array($result['product_id'], $offers, true)) {
				$offer_label = $this->model_tool_image->resize($this->config->get('config_label_offer'), $label_ratio, $label_ratio);
				$offer = true;
			} else {
				$offer_label = false;
				$offer = false;
			}

			// Quote redirect
			$quote = $result['quote'] ? $this->url->link('information/quote', '', 'SSL') : false;

			$this->data['products'][] = [
				'product_id'      => $result['product_id'],
				'thumb'           => $image,
				'thumb_webp'      => $image_webp,
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
				'minimum'         => ($result['minimum'] > 0) ? $result['minimum'] : 1,
				'age_minimum'     => ($result['age_minimum'] > 0) ? $result['age_minimum'] : '',
				'rating'          => (int)$rating,
				'reviews'         => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'mini_label'      => $this->data['show_mini_label'] ? $this->model_catalog_product->getMiniLabel($result['product_id']) : '',
			'href'            => $this->url->link('product/product', 'product_id=' . $result['product_id'], 'SSL')
			];
		}

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('module/' . $this->name);

		$this->render();
	}
}
