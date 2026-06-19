<?php
/**
 * Class ControllerPaymentStripePayments
 *
 * @package NivoCart
 */
class ControllerPaymentStripePayments extends Controller {
	private $error = [];

	public function index() {
		$this->language->load('payment/stripe_payments');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('stripe_payments', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['apply'])) {
				$this->redirect($this->url->link('payment/stripe_payments', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_test'] = $this->language->get('text_test');
		$this->data['text_live'] = $this->language->get('text_live');
		$this->data['text_authorization'] = $this->language->get('text_authorization');
		$this->data['text_charge'] = $this->language->get('text_charge');

		$this->data['entry_publishable_key'] = $this->language->get('entry_publishable_key');
		$this->data['entry_secret_key'] = $this->language->get('entry_secret_key');
		$this->data['entry_webhook_secret'] = $this->language->get('entry_webhook_secret');
		$this->data['entry_mode'] = $this->language->get('entry_mode');
		$this->data['entry_method'] = $this->language->get('entry_method');
		$this->data['entry_total'] = $this->language->get('entry_total');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_order_failed'] = $this->language->get('entry_order_failed');
		$this->data['entry_order_disputed'] = $this->language->get('entry_order_disputed');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['token'] = $this->session->data['token'];

		// Errors
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['publishable_key'])) {
			$this->data['error_publishable_key'] = $this->error['publishable_key'];
		} else {
			$this->data['error_publishable_key'] = '';
		}

		if (isset($this->error['secret_key'])) {
			$this->data['error_secret_key'] = $this->error['secret_key'];
		} else {
			$this->data['error_secret_key'] = '';
		}

		if (isset($this->error['webhook_secret'])) {
			$this->data['error_webhook_secret'] = $this->error['webhook_secret'];
		} else {
			$this->data['error_webhook_secret'] = '';
		}

		// Breadcrumbs
		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/stripe_payments', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['action'] = $this->url->link('payment/stripe_payments', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['stripe_payments_publishable_key'])) {
			$this->data['stripe_payments_publishable_key'] = $this->request->post['stripe_payments_publishable_key'];
		} else {
			$this->data['stripe_payments_publishable_key'] = $this->config->get('stripe_payments_publishable_key');
		}

		if (isset($this->request->post['stripe_payments_secret_key'])) {
			$this->data['stripe_payments_secret_key'] = $this->request->post['stripe_payments_secret_key'];
		} else {
			$this->data['stripe_payments_secret_key'] = $this->config->get('stripe_payments_secret_key');
		}

		if (isset($this->request->post['stripe_payments_webhook_secret'])) {
			$this->data['stripe_payments_webhook_secret'] = $this->request->post['stripe_payments_webhook_secret'];
		} else {
			$this->data['stripe_payments_webhook_secret'] = $this->config->get('stripe_payments_webhook_secret');
		}

		if (isset($this->request->post['stripe_payments_mode'])) {
			$this->data['stripe_payments_mode'] = $this->request->post['stripe_payments_mode'];
		} else {
			$this->data['stripe_payments_mode'] = $this->config->get('stripe_payments_mode');
		}

		if (isset($this->request->post['stripe_payments_method'])) {
			$this->data['stripe_payments_method'] = $this->request->post['stripe_payments_method'];
		} else {
			$this->data['stripe_payments_method'] = $this->config->get('stripe_payments_method');
		}

		if (isset($this->request->post['stripe_payments_order_status_id'])) {
			$this->data['stripe_payments_order_status_id'] = $this->request->post['stripe_payments_order_status_id'];
		} else {
			$this->data['stripe_payments_order_status_id'] = $this->config->get('stripe_payments_order_status_id');
		}

		if (isset($this->request->post['stripe_payments_order_failed_id'])) {
			$this->data['stripe_payments_order_failed_id'] = $this->request->post['stripe_payments_order_failed_id'];
		} else {
			$this->data['stripe_payments_order_failed_id'] = $this->config->get('stripe_payments_order_failed_id');
		}

		if (isset($this->request->post['stripe_payments_order_disputed_id'])) {
			$this->data['stripe_payments_order_disputed_id'] = $this->request->post['stripe_payments_order_disputed_id'];
		} else {
			$this->data['stripe_payments_order_disputed_id'] = $this->config->get('stripe_payments_order_disputed_id');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses([]);

		if (isset($this->request->post['stripe_payments_geo_zone_id'])) {
			$this->data['stripe_payments_geo_zone_id'] = $this->request->post['stripe_payments_geo_zone_id'];
		} else {
			$this->data['stripe_payments_geo_zone_id'] = $this->config->get('stripe_payments_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones([]);

		if (isset($this->request->post['stripe_payments_status'])) {
			$this->data['stripe_payments_status'] = $this->request->post['stripe_payments_status'];
		} else {
			$this->data['stripe_payments_status'] = $this->config->get('stripe_payments_status');
		}

		if (isset($this->request->post['stripe_payments_total'])) {
			$this->data['stripe_payments_total'] = $this->request->post['stripe_payments_total'];
		} else {
			$this->data['stripe_payments_total'] = $this->config->get('stripe_payments_total');
		}

		if (isset($this->request->post['stripe_payments_sort_order'])) {
			$this->data['stripe_payments_sort_order'] = $this->request->post['stripe_payments_sort_order'];
		} else {
			$this->data['stripe_payments_sort_order'] = $this->config->get('stripe_payments_sort_order');
		}

		$this->template = 'payment/stripe_payments.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/stripe_payments')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['stripe_payments_publishable_key']) {
			$this->error['publishable_key'] = $this->language->get('error_publishable_key');
		}

		if (!$this->request->post['stripe_payments_secret_key']) {
			$this->error['secret_key'] = $this->language->get('error_secret_key');
		}

		if (!$this->request->post['stripe_payments_webhook_secret']) {
			$this->error['webhook_secret'] = $this->language->get('error_webhook_secret');
		}

		return empty($this->error);
	}
}
