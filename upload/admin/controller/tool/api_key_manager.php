<?php
/**
 * Class ControllerToolApiKeyManager
 *
 * Passphrase-protected vault for API keys. Key Codes are encrypted by the Vault library
 * and only readable while the vault is unlocked (see system/library/vault.php).
 *
 * @package NivoCart
 */
class ControllerToolApiKeyManager extends Controller {
	private $error = [];

	public function index() {
		$this->initialise();

		if ($this->vaultReady() && !$this->vault->isSetUp()) {
			$this->redirect($this->url->link('tool/api_key_manager/setup', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		$this->getList();
	}

	public function setup() {
		$this->initialise();

		if (!$this->vaultReady() || $this->vault->isSetUp()) {
			$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validatePassphraseForm('setup')) {
			try {
				$this->vault->setUp($this->decode($this->request->post['passphrase'] ?? ''));

				$migrated = $this->model_tool_api_key_manager->migrateLegacyApiKeys();

				$this->model_tool_api_key_manager->logAction('api key vault setup');

				$this->session->data['success'] = sprintf($this->language->get('text_success_setup'), $migrated);

				$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));

				return;
			} catch (\Throwable $e) {
				$this->log->write('API Key Manager setup: ' . $e->getMessage());

				$this->error['warning'] = $this->language->get('error_vault');
			}
		}

		$this->getVaultForm('setup');
	}

	public function unlock() {
		$this->initialise();

		if (!$this->vaultReady()) {
			$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		if (!$this->vault->isSetUp()) {
			$this->redirect($this->url->link('tool/api_key_manager/setup', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validatePassphraseForm('unlock')) {
			try {
				if ($this->vault->unlock($this->decode($this->request->post['passphrase'] ?? ''))) {
					$this->model_tool_api_key_manager->logAction('api key unlock');

					$this->session->data['success'] = $this->language->get('text_success_unlock');

					$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));

					return;
				}

				$this->model_tool_api_key_manager->logAction('api key unlock failed', false);

				$this->error['warning'] = $this->language->get('error_passphrase_wrong');
			} catch (\Throwable $e) {
				$this->log->write('API Key Manager unlock: ' . $e->getMessage());

				$this->error['warning'] = $this->language->get('error_vault');
			}
		}

		$this->getVaultForm('unlock');
	}

	public function lock() {
		$this->initialise();

		if ($this->vaultReady() && $this->vault->isSetUp() && $this->vault->isUnlocked()) {
			$this->vault->lock();

			$this->model_tool_api_key_manager->logAction('api key lock');

			$this->session->data['success'] = $this->language->get('text_success_lock');
		}

		$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function changepassphrase() {
		$this->initialise();

		if (!$this->vaultReady() || !$this->vault->isSetUp()) {
			$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validatePassphraseForm('change')) {
			try {
				if ($this->vault->changePassphrase($this->decode($this->request->post['passphrase_current'] ?? ''), $this->decode($this->request->post['passphrase'] ?? ''))) {
					$this->model_tool_api_key_manager->logAction('api key passphrase change');

					$this->session->data['success'] = $this->language->get('text_success_passphrase');

					$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));

					return;
				}

				$this->model_tool_api_key_manager->logAction('api key passphrase failed', false);

				$this->error['warning'] = $this->language->get('error_passphrase_wrong');
			} catch (\Throwable $e) {
				$this->log->write('API Key Manager passphrase: ' . $e->getMessage());

				$this->error['warning'] = $this->language->get('error_vault');
			}
		}

		$this->getVaultForm('change');
	}

	public function migrate() {
		$this->initialise();

		if ($this->vaultReady() && $this->vault->isSetUp() && ($this->request->server['REQUEST_METHOD'] === 'POST')) {
			if (!$this->user->hasPermission('modify', 'tool/api_key_manager')) {
				$this->session->data['error'] = $this->language->get('error_permission');
			} elseif (!$this->vault->isUnlocked()) {
				$this->session->data['error'] = $this->language->get('error_locked');
			} else {
				try {
					$migrated = $this->model_tool_api_key_manager->migrateLegacyApiKeys();

					$this->model_tool_api_key_manager->logAction('api key migrate');

					$this->session->data['success'] = sprintf($this->language->get('text_success_migrate'), $migrated);
				} catch (\Throwable $e) {
					$this->log->write('API Key Manager migrate: ' . $e->getMessage());

					$this->session->data['error'] = $this->language->get('error_vault');
				}
			}
		}

		$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function reveal() {
		$this->initialise();

		$json = [];

		if (!$this->vaultReady() || !$this->vault->isSetUp() || ($this->request->server['REQUEST_METHOD'] !== 'POST')) {
			$json['error'] = $this->language->get('error_vault');
		} elseif (!$this->vault->isUnlocked()) {
			$json['error'] = $this->language->get('error_locked');
		} else {
			try {
				$code = $this->model_tool_api_key_manager->revealApiKey((int)$this->getPostString('api_key_id'));

				if ($code === null) {
					$json['error'] = $this->language->get('error_not_found');
				} else {
					$json['code'] = $code;

					$this->model_tool_api_key_manager->logAction('api key reveal');
				}
			} catch (\Throwable $e) {
				$this->log->write('API Key Manager reveal: ' . $e->getMessage());

				$json['error'] = $this->language->get('error_vault');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->addHeader('Cache-Control: no-store');
		$this->response->setOutput(json_encode($json));
	}

	public function insert() {
		$this->initialise();

		if (!$this->vaultReady()) {
			$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		if (!$this->vault->isSetUp()) {
			$this->redirect($this->url->link('tool/api_key_manager/setup', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		if (!$this->vault->isUnlocked()) {
			$this->redirect($this->url->link('tool/api_key_manager/unlock', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			try {
				$api_key_id = $this->model_tool_api_key_manager->addApiKey($this->getFormData());

				$this->model_tool_api_key_manager->logAction('api key add');

				$this->session->data['success'] = $this->language->get('text_success');

				if (isset($this->request->post['apply'])) {
					$this->redirect($this->url->link('tool/api_key_manager/update', 'token=' . $this->session->data['token'] . '&api_key_id=' . $api_key_id . $this->getUrlParams(), 'SSL'));
				} else {
					$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'] . $this->getUrlParams(), 'SSL'));
				}

				return;
			} catch (\Throwable $e) {
				$this->log->write('API Key Manager add: ' . $e->getMessage());

				$this->error['warning'] = $this->language->get('error_vault');
			}
		}

		$this->getForm();
	}

	public function update() {
		$this->initialise();

		if (!$this->vaultReady()) {
			$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		if (!$this->vault->isSetUp()) {
			$this->redirect($this->url->link('tool/api_key_manager/setup', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		if (!$this->vault->isUnlocked()) {
			$this->redirect($this->url->link('tool/api_key_manager/unlock', 'token=' . $this->session->data['token'], 'SSL'));

			return;
		}

		$api_key_id = (int)($this->request->get['api_key_id'] ?? 0);

		if (!$api_key_id || !$this->model_tool_api_key_manager->getApiKey($api_key_id)) {
			$this->session->data['error'] = $this->language->get('error_not_found');

			$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'] . $this->getUrlParams(), 'SSL'));

			return;
		}

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			try {
				$this->model_tool_api_key_manager->editApiKey($api_key_id, $this->getFormData());

				$this->model_tool_api_key_manager->logAction('api key edit');

				$this->session->data['success'] = $this->language->get('text_success');

				if (isset($this->request->post['apply'])) {
					$this->redirect($this->url->link('tool/api_key_manager/update', 'token=' . $this->session->data['token'] . '&api_key_id=' . $api_key_id . $this->getUrlParams(), 'SSL'));
				} else {
					$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'] . $this->getUrlParams(), 'SSL'));
				}

				return;
			} catch (\Throwable $e) {
				$this->log->write('API Key Manager edit: ' . $e->getMessage());

				$this->error['warning'] = $this->language->get('error_vault');
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->initialise();

		if (isset($this->request->post['selected']) && is_array($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $api_key_id) {
				if (is_scalar($api_key_id) && ((int)$api_key_id > 0)) {
					$this->model_tool_api_key_manager->deleteApiKey((int)$api_key_id);
				}
			}

			$this->model_tool_api_key_manager->logAction('api key delete');

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'] . $this->getUrlParams(), 'SSL'));

			return;
		}

		$this->getList();
	}

	protected function initialise(): void {
		$this->language->load('tool/api_key_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->library('vault');

		$this->vault = new Vault($this->registry);

		$this->load->model('tool/api_key_manager');
	}

	/**
	 * The database is ready for the vault: the vault table exists and the Key Code column is
	 * large enough for ciphertext (both come from the upgrade SQL). A column that is too small
	 * would silently cut encrypted values short, so nothing is saved until this is true.
	 */
	protected function vaultReady(): bool {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = '" . $this->db->escape(DB_PREFIX . 'api_key_vault') . "'");

		if ((int)$query->row['total'] < 1) {
			return false;
		}

		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = '" . $this->db->escape(DB_PREFIX . 'api_key') . "' AND `COLUMN_NAME` = 'code' AND `DATA_TYPE` IN ('text', 'mediumtext', 'longtext')");

		return (int)$query->row['total'] > 0;
	}

	/**
	 * Value as typed: Request::clean() HTML-encodes all input, so decode it before it is
	 * stored. Output is escaped in the templates instead.
	 *
	 * @param mixed $value
	 */
	protected function decode($value): string {
		return is_string($value) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : '';
	}

	/**
	 * A POST value that must be a plain string (arrays and missing values become '')
	 */
	protected function getPostString(string $key): string {
		return is_string($this->request->post[$key] ?? null) ? $this->request->post[$key] : '';
	}

	protected function getFormData(): array {
		return [
			'name'   => $this->decode($this->request->post['name'] ?? ''),
			'code'   => trim($this->decode($this->request->post['code'] ?? '')),
			'status' => ($this->getPostString('status') === '1') ? 1 : 0
		];
	}

	protected function getUrlParams(): string {
		$params = [];

		if (isset($this->request->get['sort']) && in_array($this->request->get['sort'], ['name', 'api_key_id'], true)) {
			$params['sort'] = $this->request->get['sort'];
		}

		if (isset($this->request->get['order']) && in_array($this->request->get['order'], ['ASC', 'DESC'], true)) {
			$params['order'] = $this->request->get['order'];
		}

		if (isset($this->request->get['page']) && ((int)$this->request->get['page'] > 1)) {
			$params['page'] = (int)$this->request->get['page'];
		}

		return $params ? '&' . http_build_query($params) : '';
	}

	protected function getList() {
		$url = $this->getUrlParams();

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		$token = 'token=' . $this->session->data['token'];

		$this->data['insert'] = $this->url->link('tool/api_key_manager/insert', $token . $url, 'SSL');
		$this->data['delete'] = $this->url->link('tool/api_key_manager/delete', $token . $url, 'SSL');
		$this->data['unlock'] = $this->url->link('tool/api_key_manager/unlock', $token, 'SSL');
		$this->data['lock'] = $this->url->link('tool/api_key_manager/lock', $token, 'SSL');
		$this->data['passphrase'] = $this->url->link('tool/api_key_manager/changepassphrase', $token, 'SSL');
		$this->data['migrate'] = $this->url->link('tool/api_key_manager/migrate', $token, 'SSL');
		$this->data['reveal'] = $this->url->link('tool/api_key_manager/reveal', $token, 'SSL');

		// Pagination
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$sort = (isset($this->request->get['sort']) && in_array($this->request->get['sort'], ['name', 'api_key_id'], true)) ? $this->request->get['sort'] : 'name';
		$order = (isset($this->request->get['order']) && ($this->request->get['order'] === 'DESC')) ? 'DESC' : 'ASC';
		$page = isset($this->request->get['page']) ? max(1, (int)$this->request->get['page']) : 1;

		$limit = max(1, (int)$this->config->get('config_admin_limit'));

		$data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		];

		$api_key_total = $this->model_tool_api_key_manager->getTotalApiKeys();

		$this->data['api_key_total'] = $api_key_total;

		$results = $this->model_tool_api_key_manager->getApiKeys($data);

		$this->data['api_keys'] = [];

		foreach ($results as $result) {
			$action = [];

			$action[] = [
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('tool/api_key_manager/update', $token . '&api_key_id=' . (int)$result['api_key_id'] . $url, 'SSL')
			];

			// The Key Code is never sent to the list page, only masked in the template
			$this->data['api_keys'][] = [
				'api_key_id' => (int)$result['api_key_id'],
				'name'       => $result['name'],
				'status'     => $result['status'],
				'selected'   => isset($this->request->post['selected']) && is_array($this->request->post['selected']) && in_array((string)$result['api_key_id'], $this->request->post['selected'], true),
				'action'     => $action
			];
		}

		// Vault state
		$vault_ready = $this->vaultReady();

		$this->data['vault_ready'] = $vault_ready;
		$this->data['vault_unlocked'] = $vault_ready && $this->vault->isUnlocked();
		$this->data['legacy_total'] = $this->model_tool_api_key_manager->getTotalLegacyApiKeys();

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_api_info'] = $this->language->get('text_api_info');
		$this->data['text_confirm_delete'] = $this->language->get('text_confirm_delete');
		$this->data['text_locked'] = $this->language->get('text_locked');
		$this->data['text_legacy'] = sprintf($this->language->get('text_legacy'), $this->data['legacy_total']);
		$this->data['text_legacy_locked'] = $this->language->get('text_legacy_locked');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_code'] = $this->language->get('column_code');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_info'] = $this->language->get('button_info');
		$this->data['button_unlock'] = $this->language->get('button_unlock');
		$this->data['button_lock'] = $this->language->get('button_lock');
		$this->data['button_passphrase'] = $this->language->get('button_passphrase');
		$this->data['button_migrate'] = $this->language->get('button_migrate');
		$this->data['button_reveal'] = $this->language->get('button_reveal');
		$this->data['button_hide'] = $this->language->get('button_hide');

		$this->data['error_reveal'] = $this->language->get('error_vault');

		if (!$vault_ready) {
			$this->data['error_warning'] = $this->language->get('error_table');
		} elseif (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
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

		// Html table sorting data
		$url = '&order=' . (($order === 'ASC') ? 'DESC' : 'ASC');

		if ($page > 1) {
			$url .= '&page=' . $page;
		}

		$this->data['sort_name'] = $this->url->link('tool/api_key_manager', $token . '&sort=name' . $url, 'SSL');

		// Pagination data
		$url = '';

		if (isset($this->request->get['sort']) && ($this->request->get['sort'] === $sort)) {
			$url .= '&sort=' . $sort;
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $order;
		}

		$pagination = new Pagination();
		$pagination->total = $api_key_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('tool/api_key_manager', $token . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'tool/api_key_manager_list.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');

		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_code'] = $this->language->get('entry_code');
		$this->data['entry_status'] = $this->language->get('entry_status');

		$this->data['help_code'] = $this->language->get('help_code');

		$this->data['link_google'] = $this->language->get('link_google');
		$this->data['link_facebook'] = $this->language->get('link_facebook');
		$this->data['link_twitter'] = $this->language->get('link_twitter');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['error_name'] = $this->error['name'] ?? '';
		$this->data['error_code'] = $this->error['code'] ?? '';
		$this->data['error_status'] = $this->error['status'] ?? '';

		$url = $this->getUrlParams();

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		$api_key_id = (int)($this->request->get['api_key_id'] ?? 0);

		if (!$api_key_id) {
			$this->data['action'] = $this->url->link('tool/api_key_manager/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('tool/api_key_manager/update', 'token=' . $this->session->data['token'] . '&api_key_id=' . $api_key_id . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$api_key_info = [];

		if ($api_key_id && ($this->request->server['REQUEST_METHOD'] !== 'POST')) {
			try {
				$api_key_info = $this->model_tool_api_key_manager->getApiKeyDecrypted($api_key_id);
			} catch (\Throwable $e) {
				$this->log->write('API Key Manager form: ' . $e->getMessage());

				$this->data['error_warning'] = $this->language->get('error_vault');
			}
		}

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$posted = $this->getFormData();

			$this->data['name'] = $posted['name'];
			$this->data['code'] = $posted['code'];
			$this->data['status'] = $posted['status'];
		} elseif (!empty($api_key_info)) {
			$this->data['name'] = $api_key_info['name'];
			$this->data['code'] = $api_key_info['code'];
			$this->data['status'] = $api_key_info['status'];
		} else {
			$this->data['name'] = '';
			$this->data['code'] = '';
			$this->data['status'] = true;
		}

		$this->template = 'tool/api_key_manager_form.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function getVaultForm(string $mode) {
		$this->data['mode'] = $mode;

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_info'] = $this->language->get('text_' . $mode . '_info');

		$this->data['entry_passphrase'] = $this->language->get('entry_passphrase');
		$this->data['entry_passphrase_confirm'] = $this->language->get('entry_passphrase_confirm');
		$this->data['entry_passphrase_current'] = $this->language->get('entry_passphrase_current');
		$this->data['entry_passphrase_new'] = $this->language->get('entry_passphrase_new');

		$this->data['help_passphrase'] = $this->language->get('help_passphrase');

		$this->data['button_submit'] = $this->language->get('button_' . $mode);
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$lockout = $this->vault->getLockoutRemaining();

		if ($lockout > 0) {
			$this->error['warning'] = sprintf($this->language->get('error_lockout'), (int)ceil($lockout / 60));
		}

		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['error_passphrase'] = $this->error['passphrase'] ?? '';
		$this->data['error_confirm'] = $this->error['confirm'] ?? '';

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$routes = [
			'setup'  => 'tool/api_key_manager/setup',
			'unlock' => 'tool/api_key_manager/unlock',
			'change' => 'tool/api_key_manager/changepassphrase'
		];

		$this->data['action'] = $this->url->link($routes[$mode], 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('tool/api_key_manager', 'token=' . $this->session->data['token'], 'SSL');

		$this->template = 'tool/api_key_manager_vault.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function validateForm(): bool {
		if (!$this->user->hasPermission('modify', 'tool/api_key_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$data = $this->getFormData();

		if ((mb_strlen($data['name'], 'UTF-8') < 3) || (mb_strlen($data['name'], 'UTF-8') > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((mb_strlen($data['code'], 'UTF-8') < 3) || (mb_strlen($data['code'], 'UTF-8') > 255)) {
			$this->error['code'] = $this->language->get('error_code');
		}

		if (!in_array($this->getPostString('status'), ['0', '1'], true)) {
			$this->error['status'] = $this->language->get('error_status');
		}

		return empty($this->error);
	}

	protected function validatePassphraseForm(string $mode): bool {
		if (($mode !== 'unlock') && !$this->user->hasPermission('modify', 'tool/api_key_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$passphrase = $this->decode($this->request->post['passphrase'] ?? '');

		if ($mode === 'unlock') {
			if ($passphrase === '') {
				$this->error['passphrase'] = $this->language->get('error_passphrase_empty');
			}
		} else {
			if (mb_strlen($passphrase, 'UTF-8') < Vault::MIN_PASSPHRASE_LENGTH) {
				$this->error['passphrase'] = sprintf($this->language->get('error_passphrase'), Vault::MIN_PASSPHRASE_LENGTH);
			}

			if ($passphrase !== $this->decode($this->request->post['passphrase_confirm'] ?? '')) {
				$this->error['confirm'] = $this->language->get('error_confirm');
			}

			if (($mode === 'change') && ($this->decode($this->request->post['passphrase_current'] ?? '') === '')) {
				$this->error['warning'] = $this->language->get('error_passphrase_current');
			}
		}

		return empty($this->error);
	}

	protected function validateDelete(): bool {
		if (!$this->user->hasPermission('modify', 'tool/api_key_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}
}
