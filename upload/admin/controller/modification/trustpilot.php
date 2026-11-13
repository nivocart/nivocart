<?php
/**
 * Class ControllerModificationTrustpilot
 *
 * Optional modification: Trustpilot review invitations (Automatic Feedback Service).
 * Settings, invitation overview and the install / uninstall of its own database table.
 *
 * Location: admin/controller/modification/trustpilot.php
 *
 * @package NivoCart
 */
class ControllerModificationTrustpilot extends Controller {
	private array $error = [];
	private string $name = 'trustpilot';

	public function index(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/trustpilot');

		// The table is created on install, show the install page if it is missing (e.g. restored database)
		if (!$this->model_modification_trustpilot->tableExists()) {
			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');
			$this->data['text_install_message'] = $this->language->get('text_install_message');
			$this->data['text_install_btn'] = $this->language->get('text_install_btn');

			$this->data['install_url'] = $this->url->link('modification/trustpilot/setup', 'token=' . $this->session->data['token'], 'SSL');

			$this->data['breadcrumbs'] = $this->breadcrumbs();

			$this->template = 'modification/trustpilot_install.tpl';
			$this->children = [
				'common/header',
				'common/footer'
			];

			$this->response->setOutput($this->render());

			return;
		}

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

			$this->redirect($this->url->link('modification/trustpilot', 'token=' . $this->session->data['token'], 'SSL'));
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

		$this->data['breadcrumbs'] = $this->breadcrumbs();

		$this->data['action'] = $this->url->link('modification/trustpilot', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL');
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
		
		$limit = max(1, (int)$this->config->get('trustpilot_monthly_limit') ?: 50);
		$month_count = $this->model_modification_trustpilot->getMonthlyCount();

		$this->data['text_month_count'] = sprintf($this->language->get('text_month_count'), $month_count, $limit);
		$this->data['limit_reached'] = ($month_count >= $limit);
		$this->data['text_limit_reached'] = $this->language->get('text_limit_reached');

		$this->data['invitations'] = [];

		foreach ($this->model_modification_trustpilot->getInvitations(100) as $result) {
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

		$this->template = 'modification/trustpilot.tpl';
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
		$this->language->load('modification/' . $this->name);

		$json = [];

		$email = trim((string)($this->request->post['test_email'] ?? ''));

		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
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

	/**
	 * Called by Extensions > Modifications when the modification is installed.
	 */
	public function install(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trustpilot_invitation` (
			`invitation_id` int NOT NULL AUTO_INCREMENT,
			`order_id` int NOT NULL DEFAULT '0',
			`reference` varchar(32) NOT NULL,
			`status` varchar(16) NOT NULL DEFAULT 'sent',
			`message` varchar(255) NOT NULL DEFAULT '',
			`date_added` datetime NOT NULL,
			PRIMARY KEY (`invitation_id`),
			KEY `order_id` (`order_id`),
			KEY `status_date` (`status`,`date_added`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
	}

	/**
	 * Called by Extensions > Modifications when the modification is uninstalled.
	 * The settings (group "trustpilot") are removed by the Modifications page itself.
	 */
	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "trustpilot_invitation`");
	}

	/**
	 * Install page button: create the missing table and open the settings.
	 */
	public function setup(): void {
		$this->language->load('modification/' . $this->name);

		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} else {
			$this->install();
		}

		$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
	}

	private function breadcrumbs(): array {
		return [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			],
			[
				'text'      => $this->language->get('text_modification'),
				'href'      => $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			],
			[
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			]
		];
	}

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
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
