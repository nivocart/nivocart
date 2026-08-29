<?php
/**
 * Class ControllerToolDataRetention
 *
 * @package NivoCart
 */
class ControllerToolDataRetention extends Controller {
	private $error = [];

	public function index(): void {
		$this->language->load('tool/data_retention');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/data_retention', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_info'] = $this->language->get('text_info');
		$this->data['text_last_run'] = $this->language->get('text_last_run');
		$this->data['text_task'] = $this->language->get('text_task');
		$this->data['text_rows'] = $this->language->get('text_rows');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date'] = $this->language->get('text_date');
		$this->data['text_never'] = $this->language->get('text_never');
		$this->data['text_recent_log'] = $this->language->get('text_recent_log');
		$this->data['text_confirm'] = $this->language->get('text_confirm');
		$this->data['text_no_log'] = $this->language->get('text_no_log');
		$this->data['text_cron_cmd'] = $this->language->get('text_cron_cmd');
		$this->data['text_cron_example'] = sprintf($this->language->get('text_cron_example'), HTTP_SERVER);

		$this->data['text_task_ip_columns'] = $this->language->get('text_task_ip_columns');
		$this->data['text_task_ip_log'] = $this->language->get('text_task_ip_log');
		$this->data['text_task_online_sessions'] = $this->language->get('text_task_online_sessions');
		$this->data['text_task_deleted_accounts'] = $this->language->get('text_task_deleted_accounts');

		$this->data['button_run_now'] = $this->language->get('button_run_now');
		$this->data['button_close'] = $this->language->get('button_close');

		$this->data['close'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['action'] = $this->url->link('tool/data_retention/runNow', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->load->model('tool/data_retention');

		$this->data['last_runs'] = $this->model_tool_data_retention->getLastRuns();
		$this->data['recent_log'] = $this->model_tool_data_retention->getRecentLog(30);

		// Task definitions: key => display label
		$this->data['tasks'] = [
			'purge_ip_columns'       => $this->language->get('text_task_ip_columns'),
			'purge_ip_log'           => $this->language->get('text_task_ip_log'),
			'purge_online_sessions'  => $this->language->get('text_task_online_sessions'),
			'purge_deleted_accounts' => $this->language->get('text_task_deleted_accounts'),
		];

		$this->template = 'tool/data_retention.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	/**
	 * Manual trigger — runs all purge tasks immediately.
	 */
	public function runNow(): void {
		$this->language->load('tool/data_retention');

		if (!$this->validate()) {
			$this->session->data['error'] = $this->error['warning'];
			$this->response->redirect($this->url->link('tool/data_retention', 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		$this->load->model('tool/data_retention');

		require_once DIR_SYSTEM . 'library/cron.php';

		$cron = new Cron($this->registry);

		$tasks = [
			'purge_ip_columns'       => [$this->model_tool_data_retention, 'purgeIpColumns'],
			'purge_ip_log'           => [$this->model_tool_data_retention, 'purgeIpLog'],
			'purge_online_sessions'  => [$this->model_tool_data_retention, 'purgeOnlineSessions'],
			'purge_deleted_accounts' => [$this->model_tool_data_retention, 'purgeDeletedAccounts'],
		];

		$total = 0;

		foreach ($tasks as $key => $callable) {
			$rows = $callable();
			$total += $rows;
			$cron->log($key, $rows, 'success', 'Manual run');
		}

		$this->session->data['success'] = sprintf($this->language->get('text_success'), $total);

		$this->response->redirect($this->url->link('tool/data_retention', 'token=' . $this->session->data['token'], 'SSL'));
	}

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'tool/data_retention')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}
}
