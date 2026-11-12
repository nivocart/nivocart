<?php
/**
 * Class ControllerToolTrustpilot
 *
 * Settings and invitation overview for the Trustpilot review invitations.
 *
 * @package NivoCart
 */
class ControllerToolTrustpilot extends Controller {
	private array $error = [];

	public function index(): void {
		$this->language->load('tool/trustpilot');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('trustpilot', [
				'trustpilot_status'        => !empty($this->request->post['trustpilot_status']) ? 1 : 0,
				'trustpilot_afs_email'     => trim((string)($this->request->post['trustpilot_afs_email'] ?? '')),
				'trustpilot_sender_email'  => trim((string)($this->request->post['trustpilot_sender_email'] ?? '')),
				'trustpilot_monthly_limit' => (int)($this->request->post['trustpilot_monthly_limit'] ?? 50)
			]);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('tool/trustpilot', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$texts = [
			'tab_settings', 'tab_invitations', 'text_enabled', 'text_disabled', 'text_yes', 'text_no', 'text_info',
			'text_no_results', 'text_test', 'text_confirm_test', 'column_date', 'column_order', 'column_customer',
			'column_status', 'column_details', 'entry_status', 'entry_afs_email', 'entry_sender_email',
			'entry_monthly_limit', 'entry_trigger_status', 'entry_test', 'help_status', 'help_afs_email',
			'help_sender_email', 'help_monthly_limit', 'help_trigger_status', 'help_test', 'button_test',
			'button_test_title', 'button_save', 'button_cancel'
		];

		foreach ($texts as $text) {
			$this->data[$text] = $this->language->get($text);
		}

		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['error_afs_email'] = $this->error['afs_email'] ?? '';
		$this->data['error_sender_email'] = $this->error['sender_email'] ?? '';
		$this->data['error_monthly_limit'] = $this->error['monthly_limit'] ?? '';

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/trustpilot', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['action'] = $this->url->link('tool/trustpilot', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] = $this->session->data['token'];

		// Settings (posted values win so a failed save keeps what was typed)
		foreach (['trustpilot_status', 'trustpilot_afs_email', 'trustpilot_sender_email', 'trustpilot_monthly_limit'] as $key) {
			$this->data[$key] = $this->request->post[$key] ?? (string)$this->config->get($key);
		}

		if ($this->data['trustpilot_monthly_limit'] === '') {
			$this->data['trustpilot_monthly_limit'] = 50;
		}

		// Trigger status (read only, it is the store's Complete Order Status)
		$this->load->model('localisation/order_status');

		$status_info = $this->model_localisation_order_status->getOrderStatus((int)$this->config->get('config_complete_status_id'));

		if ($status_info) {
			$this->data['trigger_status'] = sprintf($this->language->get('text_trigger_status'), $status_info['name']);
		} else {
			$this->data['trigger_status'] = $this->language->get('text_trigger_not_set');
		}

		// Invitations tab
		$this->load->model('tool/trustpilot');

		$limit = max(1, (int)$this->config->get('trustpilot_monthly_limit') ?: 50);
		$month_count = $this->model_tool_trustpilot->getMonthlyCount();

		$this->data['text_month_count'] = sprintf($this->language->get('text_month_count'), $month_count, $limit);
		$this->data['limit_reached'] = ($month_count >= $limit);
		$this->data['text_limit_reached'] = $this->language->get('text_limit_reached');

		$this->data['invitations'] = [];

		foreach ($this->model_tool_trustpilot->getInvitations(100) as $result) {
			$this->data['invitations'][] = [
				'date'        => date('Y-m-d H:i', strtotime($result['date_added'])),
				'order_id'    => (int)$result['order_id'],
				'order'       => $result['order_id'] ? $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . (int)$result['order_id'], 'SSL') : '',
				'reference'   => $result['reference'],
				'customer'    => $result['order_id'] ? trim((string)$result['customer']) : $this->language->get('text_test'),
				'status'      => $result['status'],
				'status_text' => $this->language->get('text_status_' . $result['status']),
				'message'     => $result['message']
			];
		}

		$this->template = 'tool/trustpilot.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	/**
	 * Ajax: send a real test invitation request.
	 */
	public function test(): void {
		$this->language->load('tool/trustpilot');

		$json = [];

		$email = trim((string)($this->request->post['test_email'] ?? ''));

		if (!$this->user->hasPermission('modify', 'tool/trustpilot')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif (!emailIsValid($email)) {
			$json['error'] = $this->language->get('error_email');
		} else {
			require_once DIR_SYSTEM . 'library/trustpilot.php';

			$trustpilot = new Trustpilot($this->registry);

			if (!$trustpilot->isEnabled()) {
				$json['error'] = $this->language->get('error_not_enabled');
			} else {
				try {
					$trustpilot->sendTest($email);

					$json['success'] = sprintf($this->language->get('text_test_success'), $email);
				} catch (\Exception $e) {
					$json['error'] = $e->getMessage();
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'tool/trustpilot')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$afs_email = trim((string)($this->request->post['trustpilot_afs_email'] ?? ''));
		$sender_email = trim((string)($this->request->post['trustpilot_sender_email'] ?? ''));

		if (!empty($this->request->post['trustpilot_status']) && !emailIsValid($afs_email)) {
			$this->error['afs_email'] = $this->language->get('error_afs_email');
		}

		if (($sender_email !== '') && !emailIsValid($sender_email)) {
			$this->error['sender_email'] = $this->language->get('error_sender_email');
		}

		$limit = $this->request->post['trustpilot_monthly_limit'] ?? '';

		if (!ctype_digit((string)$limit) || ((int)$limit < 1)) {
			$this->error['monthly_limit'] = $this->language->get('error_monthly_limit');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}
}
