<?php
/**
 * Class ControllerFraudMaxMind
 *
 * @package NivoCart
 */
class ControllerFraudMaxMind extends Controller {
	private $error = [];
	private $_name = 'maxmind';

	public function index() {
		$this->language->load('fraud/maxmind');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('maxmind', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['apply'])) {
				$this->redirect($this->url->link('fraud/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('extension/fraud', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_edit'] = $this->language->get('text_edit');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_signup'] = $this->language->get('text_signup');

		$this->data['entry_account_id'] = $this->language->get('entry_account_id');
		$this->data['entry_key'] = $this->language->get('entry_key');
		$this->data['entry_score'] = $this->language->get('entry_score');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_status'] = $this->language->get('entry_status');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		// Errors
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['account_id'])) {
			$this->data['error_account_id'] = $this->error['account_id'];
		} else {
			$this->data['error_account_id'] = '';
		}

		if (isset($this->error['key'])) {
			$this->data['error_key'] = $this->error['key'];
		} else {
			$this->data['error_key'] = '';
		}

		// Breadcrumbs
		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_fraud'),
			'href'      => $this->url->link('extension/fraud', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('fraud/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['action'] = $this->url->link('fraud/maxmind', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/fraud', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['maxmind_account_id'])) {
			$this->data['maxmind_account_id'] = $this->request->post['maxmind_account_id'];
		} else {
			$this->data['maxmind_account_id'] = $this->config->get('maxmind_account_id');
		}

		if (isset($this->request->post['maxmind_key'])) {
			$this->data['maxmind_key'] = $this->request->post['maxmind_key'];
		} else {
			$this->data['maxmind_key'] = $this->config->get('maxmind_key');
		}

		if (isset($this->request->post['maxmind_score'])) {
			$this->data['maxmind_score'] = $this->request->post['maxmind_score'];
		} elseif ($this->config->get('maxmind_score')) {
			$this->data['maxmind_score'] = $this->config->get('maxmind_score');
		} else {
			$this->data['maxmind_score'] = '80';
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses([]);

		if (isset($this->request->post['maxmind_order_status_id'])) {
			$this->data['maxmind_order_status_id'] = $this->request->post['maxmind_order_status_id'];
		} else {
			$this->data['maxmind_order_status_id'] = $this->config->get('maxmind_order_status_id');
		}

		if (isset($this->request->post['maxmind_status'])) {
			$this->data['maxmind_status'] = $this->request->post['maxmind_status'];
		} else {
			$this->data['maxmind_status'] = $this->config->get('maxmind_status');
		}

		$this->template = 'fraud/' . $this->_name . '.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	public function install(): void {
		$this->load->model('fraud/maxmind');

		$this->model_fraud_maxmind->install();
	}

	public function uninstall(): void {
		$this->load->model('fraud/maxmind');

		$this->model_fraud_maxmind->uninstall();
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'fraud/maxmind')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (empty($this->request->post['maxmind_account_id'])) {
			$this->error['account_id'] = $this->language->get('error_account_id');
		}

		if (empty($this->request->post['maxmind_key'])) {
			$this->error['key'] = $this->language->get('error_key');
		}

		return empty($this->error);
	}

	public function order() {
		$this->language->load('fraud/maxmind');

		$this->load->model('fraud/maxmind');

		$order_id = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;

		$fraud_info = $this->model_fraud_maxmind->getOrder($order_id);

		if ($fraud_info) {
			// Language strings — labels
			$this->data['text_country_match'] = $this->language->get('text_country_match');
			$this->data['text_country_code'] = $this->language->get('text_country_code');
			$this->data['text_high_risk_country'] = $this->language->get('text_high_risk_country');
			$this->data['text_distance'] = $this->language->get('text_distance');
			$this->data['text_ip_region'] = $this->language->get('text_ip_region');
			$this->data['text_ip_city'] = $this->language->get('text_ip_city');
			$this->data['text_ip_latitude'] = $this->language->get('text_ip_latitude');
			$this->data['text_ip_longitude'] = $this->language->get('text_ip_longitude');
			$this->data['text_ip_isp'] = $this->language->get('text_ip_isp');
			$this->data['text_ip_org'] = $this->language->get('text_ip_org');
			$this->data['text_ip_asnum'] = $this->language->get('text_ip_asnum');
			$this->data['text_ip_user_type'] = $this->language->get('text_ip_user_type');
			$this->data['text_ip_country_confidence'] = $this->language->get('text_ip_country_confidence');
			$this->data['text_ip_region_confidence'] = $this->language->get('text_ip_region_confidence');
			$this->data['text_ip_city_confidence'] = $this->language->get('text_ip_city_confidence');
			$this->data['text_ip_postal_confidence'] = $this->language->get('text_ip_postal_confidence');
			$this->data['text_ip_postal_code'] = $this->language->get('text_ip_postal_code');
			$this->data['text_ip_accuracy_radius'] = $this->language->get('text_ip_accuracy_radius');
			$this->data['text_ip_net_speed_cell'] = $this->language->get('text_ip_net_speed_cell');
			$this->data['text_ip_time_zone'] = $this->language->get('text_ip_time_zone');
			$this->data['text_ip_region_name'] = $this->language->get('text_ip_region_name');
			$this->data['text_ip_domain'] = $this->language->get('text_ip_domain');
			$this->data['text_ip_country_name'] = $this->language->get('text_ip_country_name');
			$this->data['text_ip_continent_code'] = $this->language->get('text_ip_continent_code');
			$this->data['text_ip_corporate_proxy'] = $this->language->get('text_ip_corporate_proxy');
			$this->data['text_anonymous_proxy'] = $this->language->get('text_anonymous_proxy');
			$this->data['text_proxy_score'] = $this->language->get('text_proxy_score');
			$this->data['text_free_mail'] = $this->language->get('text_free_mail');
			$this->data['text_carder_email'] = $this->language->get('text_carder_email');
			$this->data['text_bin_match'] = $this->language->get('text_bin_match');
			$this->data['text_bin_country'] = $this->language->get('text_bin_country');
			$this->data['text_bin_name_match'] = $this->language->get('text_bin_name_match');
			$this->data['text_bin_name'] = $this->language->get('text_bin_name');
			$this->data['text_bin_phone_match'] = $this->language->get('text_bin_phone_match');
			$this->data['text_bin_phone'] = $this->language->get('text_bin_phone');
			$this->data['text_ship_forward'] = $this->language->get('text_ship_forward');
			$this->data['text_city_postal_match'] = $this->language->get('text_city_postal_match');
			$this->data['text_ship_city_postal_match'] = $this->language->get('text_ship_city_postal_match');
			$this->data['text_risk_score'] = $this->language->get('text_risk_score');
			$this->data['text_queries_remaining'] = $this->language->get('text_queries_remaining');
			$this->data['text_maxmind_id'] = $this->language->get('text_maxmind_id');
			$this->data['text_error'] = $this->language->get('text_error');
			$this->data['text_email_is_disposable'] = $this->language->get('text_email_is_disposable');
			$this->data['text_email_is_high_risk'] = $this->language->get('text_email_is_high_risk');
			$this->data['text_credit_card_brand'] = $this->language->get('text_credit_card_brand');
			$this->data['text_credit_card_type'] = $this->language->get('text_credit_card_type');
			$this->data['text_credit_card_is_prepaid'] = $this->language->get('text_credit_card_is_prepaid');
			$this->data['text_ship_is_high_risk'] = $this->language->get('text_ship_is_high_risk');

			// Data values
			$this->data['country_match'] = $fraud_info['country_match'];
			$this->data['country_code'] = $fraud_info['country_code'];
			$this->data['high_risk_country'] = $fraud_info['high_risk_country'];
			$this->data['distance'] = $fraud_info['distance'];
			$this->data['ip_region'] = $fraud_info['ip_region'];
			$this->data['ip_city'] = $fraud_info['ip_city'];
			$this->data['ip_latitude'] = $fraud_info['ip_latitude'];
			$this->data['ip_longitude'] = $fraud_info['ip_longitude'];
			$this->data['ip_isp'] = $fraud_info['ip_isp'];
			$this->data['ip_org'] = $fraud_info['ip_org'];
			$this->data['ip_asnum'] = $fraud_info['ip_asnum'];
			$this->data['ip_user_type'] = $fraud_info['ip_user_type'];
			$this->data['ip_country_confidence'] = $fraud_info['ip_country_confidence'];
			$this->data['ip_region_confidence'] = $fraud_info['ip_region_confidence'];
			$this->data['ip_city_confidence'] = $fraud_info['ip_city_confidence'];
			$this->data['ip_postal_confidence'] = $fraud_info['ip_postal_confidence'];
			$this->data['ip_postal_code'] = $fraud_info['ip_postal_code'];
			$this->data['ip_accuracy_radius'] = $fraud_info['ip_accuracy_radius'];
			$this->data['ip_net_speed_cell'] = $fraud_info['ip_net_speed_cell'];
			$this->data['ip_time_zone'] = $fraud_info['ip_time_zone'];
			$this->data['ip_region_name'] = $fraud_info['ip_region_name'];
			$this->data['ip_domain'] = $fraud_info['ip_domain'];
			$this->data['ip_country_name'] = $fraud_info['ip_country_name'];
			$this->data['ip_continent_code'] = $fraud_info['ip_continent_code'];
			$this->data['ip_corporate_proxy'] = $fraud_info['ip_corporate_proxy'];
			$this->data['anonymous_proxy'] = $fraud_info['anonymous_proxy'];
			$this->data['proxy_score'] = $fraud_info['proxy_score'];
			$this->data['free_mail'] = $fraud_info['free_mail'];
			$this->data['carder_email'] = $fraud_info['carder_email'];
			$this->data['bin_match'] = $fraud_info['bin_match'];
			$this->data['bin_country'] = $fraud_info['bin_country'];
			$this->data['bin_name_match'] = $fraud_info['bin_name_match'];
			$this->data['bin_name'] = $fraud_info['bin_name'];
			$this->data['bin_phone_match'] = $fraud_info['bin_phone_match'];
			$this->data['bin_phone'] = $fraud_info['bin_phone'];
			$this->data['ship_forward'] = $fraud_info['ship_forward'];
			$this->data['city_postal_match'] = $fraud_info['city_postal_match'];
			$this->data['ship_city_postal_match'] = $fraud_info['ship_city_postal_match'];
			$this->data['risk_score'] = $fraud_info['risk_score'];
			$this->data['queries_remaining'] = $fraud_info['queries_remaining'];
			$this->data['maxmind_id'] = $fraud_info['maxmind_id'];
			$this->data['error'] = $fraud_info['error'];
			$this->data['email_is_disposable'] = $fraud_info['email_is_disposable'] ?? '';
			$this->data['email_is_high_risk'] = $fraud_info['email_is_high_risk'] ?? '';
			$this->data['credit_card_brand'] = $fraud_info['credit_card_brand'] ?? '';
			$this->data['credit_card_type'] = $fraud_info['credit_card_type'] ?? '';
			$this->data['credit_card_is_prepaid'] = $fraud_info['credit_card_is_prepaid'] ?? '';
			$this->data['ship_is_high_risk'] = $fraud_info['ship_is_high_risk'] ?? '';

			$this->template = 'fraud/maxmind_info.tpl';

			$this->response->setOutput($this->render());
		}
	}
}
