<?php
/**
 * Class ControllerFraudFraudLabsPro
 *
 * @package NivoCart
 */
class ControllerFraudFraudLabsPro extends Controller {
	private $error = [];
	private $_name = 'fraudlabspro';

	public function index() {
		$this->language->load('fraud/fraudlabspro');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('fraudlabspro', $this->request->post);

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
		$this->data['text_rules'] = $this->language->get('text_rules');
		$this->data['text_testing'] = $this->language->get('text_testing');

		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_key'] = $this->language->get('entry_key');
		$this->data['entry_score'] = $this->language->get('entry_score');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_review_status'] = $this->language->get('entry_review_status');
		$this->data['entry_approve_status'] = $this->language->get('entry_approve_status');
		$this->data['entry_reject_status'] = $this->language->get('entry_reject_status');
		$this->data['entry_simulate_ip'] = $this->language->get('entry_simulate_ip');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		// Errors
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
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

		$this->data['action'] = $this->url->link('fraud/fraudlabspro', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/fraud', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['fraudlabspro_key'])) {
			$this->data['fraudlabspro_key'] = $this->request->post['fraudlabspro_key'];
		} else {
			$this->data['fraudlabspro_key'] = $this->config->get('fraudlabspro_key');
		}

		if (isset($this->request->post['fraudlabspro_score'])) {
			$this->data['fraudlabspro_score'] = $this->request->post['fraudlabspro_score'];
		} elseif ($this->config->get('fraudlabspro_score')) {
			$this->data['fraudlabspro_score'] = $this->config->get('fraudlabspro_score');
		} else {
			$this->data['fraudlabspro_score'] = '50';
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses([]);

		if (isset($this->request->post['fraudlabspro_order_status_id'])) {
			$this->data['fraudlabspro_order_status_id'] = $this->request->post['fraudlabspro_order_status_id'];
		} else {
			$this->data['fraudlabspro_order_status_id'] = $this->config->get('fraudlabspro_order_status_id');
		}

		if (isset($this->request->post['fraudlabspro_status'])) {
			$this->data['fraudlabspro_status'] = $this->request->post['fraudlabspro_status'];
		} else {
			$this->data['fraudlabspro_status'] = $this->config->get('fraudlabspro_status');
		}

		if (isset($this->request->post['fraudlabspro_review_status_id'])) {
			$this->data['fraudlabspro_review_status_id'] = $this->request->post['fraudlabspro_review_status_id'];
		} else {
			$this->data['fraudlabspro_review_status_id'] = $this->config->get('fraudlabspro_review_status_id');
		}

		if (isset($this->request->post['fraudlabspro_approve_status_id'])) {
			$this->data['fraudlabspro_approve_status_id'] = $this->request->post['fraudlabspro_approve_status_id'];
		} else {
			$this->data['fraudlabspro_approve_status_id'] = $this->config->get('fraudlabspro_approve_status_id');
		}

		if (isset($this->request->post['fraudlabspro_reject_status_id'])) {
			$this->data['fraudlabspro_reject_status_id'] = $this->request->post['fraudlabspro_reject_status_id'];
		} else {
			$this->data['fraudlabspro_reject_status_id'] = $this->config->get('fraudlabspro_reject_status_id');
		}

		if (isset($this->request->post['fraudlabspro_simulate_ip'])) {
			$this->data['fraudlabspro_simulate_ip'] = $this->request->post['fraudlabspro_simulate_ip'];
		} else {
			$this->data['fraudlabspro_simulate_ip'] = $this->config->get('fraudlabspro_simulate_ip');
		}

		$this->template = 'fraud/' . $this->_name . '.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	public function install(): void {
		$this->load->model('fraud/fraudlabspro');

		$this->model_fraud_fraudlabspro->install();
	}

	public function uninstall(): void {
		$this->load->model('fraud/fraudlabspro');

		$this->model_fraud_fraudlabspro->uninstall();
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'fraud/fraudlabspro')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['fraudlabspro_key']) {
			$this->error['key'] = $this->language->get('error_key');
		}

		return empty($this->error);
	}

	public function order() {
		$this->language->load('fraud/fraudlabspro');

		$this->load->model('fraud/fraudlabspro');

		// Get current Order Id
		$order_id = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;

		// Action of the Approve / Reject / Reject & Blacklist button click
		if (isset($_POST['flp_id'])) {
			$flp_status = strtoupper(trim($_POST['new_flp_status'] ?? ''));

			// Valid feedback actions for the v2 Feedback API
			$allowed_actions = ['APPROVE', 'REJECT', 'REJECT_BLACKLIST'];

			if (!in_array($flp_status, $allowed_actions, true)) {
				throw new \InvalidArgumentException('Invalid FLP feedback action.');
			}

			$flp_id = trim($_POST['flp_id'] ?? '');

			// v2 IDs may be alphanumeric; reject anything that looks unsafe
			if (!is_string($flp_id) || !preg_match('/^[A-Za-z0-9\-]{1,50}$/', $flp_id)) {
				throw new \InvalidArgumentException('Invalid FLP transaction ID.');
			}

			// Send feedback to FraudLabs Pro v2 Feedback API
			$this->model_fraud_fraudlabspro->sendFeedback($flp_id, $flp_status);

			// Update local DB status
			$this->model_fraud_fraudlabspro->updateFeedback($order_id, $flp_status);

			$this->data['flp_status'] = $flp_status;

			// Update order history record
			if ($flp_status === 'APPROVE') {
				$data_temp = [
					'order_status_id' => $this->config->get('fraudlabspro_approve_status_id'),
					'notify'          => 0,
					'comment'         => $this->language->get('text_comment_approve'),
				];

				$this->model_fraud_fraudlabspro->addOrderHistory($order_id, 0, $data_temp);

			} elseif ($flp_status === 'REJECT') {
				$data_temp = [
					'order_status_id' => $this->config->get('fraudlabspro_reject_status_id'),
					'notify'          => 0,
					'comment'         => $this->language->get('text_comment_reject'),
				];

				$this->model_fraud_fraudlabspro->addOrderHistory($order_id, 0, $data_temp);

			} elseif ($flp_status === 'REJECT_BLACKLIST') {
				$data_temp = [
					'order_status_id' => $this->config->get('fraudlabspro_reject_status_id'),
					'notify'          => 0,
					'comment'         => $this->language->get('text_comment_reject_blacklist'),
				];

				$this->model_fraud_fraudlabspro->addOrderHistory($order_id, 0, $data_temp);
			}
		}

		$fraud_info = $this->model_fraud_fraudlabspro->getOrder($order_id);

		if ($fraud_info) {
			$this->data['text_loading'] = $this->language->get('text_loading');
			$this->data['text_fraudlabspro_id'] = $this->language->get('text_fraudlabspro_id');
			$this->data['text_transaction_id'] = $this->language->get('text_transaction_id');
			$this->data['text_score'] = $this->language->get('text_score');
			$this->data['text_status'] = $this->language->get('text_status');
			$this->data['text_ip_address'] = $this->language->get('text_ip_address');
			$this->data['text_ip_net_speed'] = $this->language->get('text_ip_net_speed');
			$this->data['text_ip_isp_name'] = $this->language->get('text_ip_isp_name');
			$this->data['text_ip_usage_type'] = $this->language->get('text_ip_usage_type');
			$this->data['text_ip_domain'] = $this->language->get('text_ip_domain');
			$this->data['text_ip_time_zone'] = $this->language->get('text_ip_time_zone');
			$this->data['text_ip_location'] = $this->language->get('text_ip_location');
			$this->data['text_ip_distance'] = $this->language->get('text_ip_distance');
			$this->data['text_ip_latitude'] = $this->language->get('text_ip_latitude');
			$this->data['text_ip_longitude'] = $this->language->get('text_ip_longitude');
			$this->data['text_risk_country'] = $this->language->get('text_risk_country');
			$this->data['text_free_email'] = $this->language->get('text_free_email');
			$this->data['text_email_disposable'] = $this->language->get('text_email_disposable');
			$this->data['text_phone_disposable'] = $this->language->get('text_phone_disposable');
			$this->data['text_phone_blacklist'] = $this->language->get('text_phone_blacklist');
			$this->data['text_card_brand'] = $this->language->get('text_card_brand');
			$this->data['text_card_type'] = $this->language->get('text_card_type');
			$this->data['text_ship_forward'] = $this->language->get('text_ship_forward');
			$this->data['text_ship_export_controlled'] = $this->language->get('text_ship_export_controlled');
			$this->data['text_using_proxy'] = $this->language->get('text_using_proxy');
			$this->data['text_bin_found'] = $this->language->get('text_bin_found');
			$this->data['text_email_blacklist'] = $this->language->get('text_email_blacklist');
			$this->data['text_credit_card_blacklist'] = $this->language->get('text_credit_card_blacklist');
			$this->data['text_message'] = $this->language->get('text_message');
			$this->data['text_credits'] = $this->language->get('text_credits');
			$this->data['text_flp_upgrade'] = $this->language->get('text_flp_upgrade');
			$this->data['text_flp_merchant_area'] = $this->language->get('text_flp_merchant_area');

			$this->data['button_approve'] = $this->language->get('button_approve');
			$this->data['button_reject'] = $this->language->get('button_reject');
			$this->data['button_reject_blacklist'] = $this->language->get('button_reject_blacklist');

			$this->data['flp_ip_address'] = $fraud_info['ip_address'] ?: '';

			$this->data['flp_ip_net_speed'] = $fraud_info['ip_netspeed'] ?: '';

			$this->data['flp_ip_isp_name'] = $fraud_info['ip_isp_name'] ?: '';

			$this->data['flp_ip_usage_type'] = $fraud_info['ip_usage_type'] ?: '';

			$this->data['flp_ip_domain'] = $fraud_info['ip_domain'] ?: '';

			$this->data['flp_ip_time_zone'] = $fraud_info['ip_timezone'] ?: '';

			if ($fraud_info['ip_country']) {
				$this->data['flp_ip_location'] = $this->fixCase($fraud_info['ip_continent']) . ', ' . ($fraud_info['ip_country_name'] ?: $fraud_info['ip_country']) . ', ' . $fraud_info['ip_region'] . ', ' . $fraud_info['ip_city'] . ' &nbsp;&nbsp; <a href="http://www.geolocation.com/' . $fraud_info['ip_address'] . '" target="_blank"><b>[ Map ]</b></a>';
			} else {
				$this->data['flp_ip_location'] = '-';
			}

			if ($fraud_info['distance_in_mile'] != '-') {
				$this->data['flp_ip_distance'] = $fraud_info['distance_in_mile'] . ' miles / ' . $fraud_info['distance_in_km'] . ' km';
			} else {
				$this->data['flp_ip_distance'] = '';
			}

			$this->data['flp_ip_latitude'] = $fraud_info['ip_latitude'] ?: '';
			$this->data['flp_ip_longitude'] = $fraud_info['ip_longitude'] ?: '';

			$this->data['flp_risk_country'] = $fraud_info['is_high_risk_country'] ?: '';

			$this->data['flp_free_email'] = $fraud_info['is_free_email'] ?: '';
			$this->data['flp_email_disposable'] = $fraud_info['email_is_disposable'] ?: '';

			$this->data['flp_phone_disposable'] = $fraud_info['phone_is_disposable'] ?: '';
			$this->data['flp_phone_blacklist'] = $fraud_info['phone_in_blacklist'] ?: '';

			$this->data['flp_card_brand'] = $fraud_info['card_brand'] ?: '';
			$this->data['flp_card_type'] = $fraud_info['card_type'] ?: '';

			$this->data['flp_ship_forward'] = $fraud_info['is_address_ship_forward'] ?: '';
			$this->data['flp_ship_export_controlled'] = $fraud_info['ship_export_controlled'] ?: '';

			$this->data['flp_using_proxy'] = $fraud_info['is_proxy_ip_address'] ?: '';
			$this->data['flp_bin_found'] = $fraud_info['is_bin_found'] ?: '';
			$this->data['flp_email_blacklist'] = $fraud_info['is_email_blacklist'] ?: '';
			$this->data['flp_credit_card_blacklist'] = $fraud_info['is_credit_card_blacklist'] ?: '';

			$this->data['flp_score'] = $fraud_info['fraudlabspro_score'] ?: '';
			$this->data['flp_status'] = $fraud_info['fraudlabspro_status'] ?: '';
			$this->data['flp_message'] = $fraud_info['fraudlabspro_message'] ?: '';

			$this->data['flp_id'] = $fraud_info['fraudlabspro_id'] ?: '';
			$this->data['flp_link'] = $fraud_info['fraudlabspro_id'] ?: '';

			$this->data['flp_credits'] = $fraud_info['fraudlabspro_credits'] ?: '';

			$this->template = 'fraud/fraudlabspro_info.tpl';

			$this->response->setOutput($this->render());
		}
	}

	private function fixCase(string $s): string {
		$s = ucwords(strtolower($s));
		$s = preg_replace_callback("/( [ a-zA-Z]{1}')([a-zA-Z0-9]{1})/s", function ($matches) {
			return $matches[1] . strtoupper($matches[2]);
		}, $s);
		return $s;
	}
}
