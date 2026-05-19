<?php
/**
 * Class ControllerAffiliateTracking
 *
 * @package NivoCart
 */
class ControllerAffiliateTracking extends Controller {
	/** Error array Placeholder */

	public function index() {
		if (!$this->affiliate->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('affiliate/tracking', '', 'SSL');

			$this->redirect($this->url->link('affiliate/login', '', 'SSL'));
		}

		if (!$this->affiliate->isSecure()) {
			$this->affiliate->logout();

			$this->session->data['redirect'] = $this->url->link('affiliate/account', '', 'SSL');

			$this->redirect($this->url->link('affiliate/login', '', 'SSL'));
		}

		// If this affiliate is also logged in as a customer, log out the customer session
		if ($this->customer->isLogged()) {
			$this->customer->logout();
		}

		$this->language->load('affiliate/tracking');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('affiliate/account', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('affiliate/tracking', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_description'] = sprintf($this->language->get('text_description'), $this->config->get('config_name'));

		$this->data['text_code'] = $this->language->get('text_code');
		$this->data['text_generator'] = $this->language->get('text_generator');
		$this->data['text_link'] = $this->language->get('text_link');

		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['code'] = $this->affiliate->getCode();

		$this->data['continue'] = $this->url->link('affiliate/account', '', 'SSL');

		$this->data['token'] = $this->session->data['affiliate_token'];

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/affiliate/tracking.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/affiliate/tracking.tpl';
		} else {
			$this->template = 'default/template/affiliate/tracking.tpl';
		}

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
	}

	/**
	 * Autocomplete Product
	 */
	public function autocomplete() {
		if (!$this->affiliate->isLogged() || !$this->affiliate->isSecure()) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode([]));
			return;
		}

		$json = [];

		if (isset($this->request->get['filter_name'])) {
			$filter_name = trim($this->request->get['filter_name']);

			$this->load->model('catalog/product');

			$data = [
				'filter_name'  => $filter_name,
				'autocomplete' => true,
				'start'        => 0,
				'limit'        => 20
			];

			$results = $this->model_catalog_product->getProducts($data);

			foreach ($results as $result) {
				$json[] = [
					'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'link' => $this->url->link('product/product', 'product_id=' . $result['product_id'] . '&tracking=' . $this->affiliate->getCode(), 'SSL')
				];
			}

			usort($json, fn($a, $b) => strcmp($a['name'], $b['name']));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
