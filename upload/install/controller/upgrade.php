<?php
/**
 * Class ControllerUpgrade
 *
 * @package NivoCart
 */
class ControllerUpgrade extends Controller {
	private array $error = [];

	public function index(): void {
		$this->document->setTitle($this->language->get('heading_upgrade'));

		if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
			$this->initialize();

			$this->data['heading_success'] = $this->language->get('heading_success');
			$this->data['heading_next'] = $this->language->get('heading_next');
			$this->data['heading_update'] = $this->language->get('heading_update');

			$this->data['text_success'] = $this->language->get('text_congratulation');
			$this->data['text_forget'] = $this->language->get('text_forget');
			$this->data['text_login'] = $this->language->get('text_login');

			$this->data['help_update'] = $this->language->get('help_update');
			$this->data['help_installer'] = $this->language->get('help_installer');

			$this->template = 'success.tpl';
		} else {
			$this->data['heading_upgrade'] = $this->language->get('heading_upgrade');

			$this->data['text_follow_steps'] = $this->language->get('text_follow_steps');
			$this->data['text_clear_cookie'] = $this->language->get('text_clear_cookie');
			$this->data['text_admin_page'] = $this->language->get('text_admin_page');
			$this->data['text_admin_user'] = $this->language->get('text_admin_user');
			$this->data['text_admin_setting'] = $this->language->get('text_admin_setting');
			$this->data['text_store_front'] = $this->language->get('text_store_front');
			$this->data['text_be_patient'] = $this->language->get('text_be_patient');
			$this->data['text_is_upgrading'] = $this->language->get('text_is_upgrading');

			$this->data['button_upgrade'] = $this->language->get('button_upgrade');

			$this->data['action'] = $this->url->link('upgrade', '', 'SSL');

			$this->data['error_warning'] = $this->error['warning'] ?? '';

			$this->template = 'upgrade.tpl';
		}

		$this->children = ['header', 'footer'];
		$this->response->setOutput($this->render());
	}

	protected function initialize(): void {
		$file = DIR_APPLICATION . 'nivocart-upgrade.sql';

		if (!file_exists($file)) {
			exit('Could not load sql file: ' . $file);
		}

		clearstatcache();

		$this->load->model('upgrade');

		$step1 = $this->model_upgrade->dataTables();
		$step2 = $step1 ? $this->model_upgrade->additionalTables() : false;
		$step3 = $step2 ? $this->model_upgrade->repairCategories() : false;
		$step4 = $step3 ? $this->model_upgrade->updateConfig() : false;
		$step5 = $step4 ? $this->model_upgrade->updateLayouts() : false;

		if ($step5) {
			$this->model_upgrade->updateFields();
		}
	}

	protected function validate(): bool {
		if (DB_DRIVER !== 'mysqli') {
			return true;
		}

		// PHP 8 throws mysqli_sql_exception on connection failure — must catch it.
		mysqli_report(MYSQLI_REPORT_OFF);

		$port = defined('DB_PORT') ? (int)DB_PORT : 3306;

		try {
			$connection = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, $port);

			if ($connection->connect_errno) {
				$this->error['warning'] = 'Error database connect: "' . $connection->connect_error . '"';
				return false;
			}

			if (!$connection->query('DO 1')) {
				$this->error['warning'] = 'Error database server: "' . $connection->error . '"';
				return false;
			}

			$connection->close();

		} catch (\mysqli_sql_exception $e) {
			$this->error['warning'] = 'Error database connect: "' . $e->getMessage() . '"';
			return false;
		}

		return true;
	}
}
