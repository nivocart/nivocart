<?php
/**
 * Class ControllerToolMailManager
 *
 * @package NivoCart
 */
class ControllerToolMailManager extends Controller {
	private array $error = [];

	public function index(): void {
		$this->language->load('tool/mail_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('tool/mail_manager');

		$this->getList();
	}

	public function insert(): void {
		$this->language->load('tool/mail_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('tool/mail_manager');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			$this->model_tool_mail_manager->addEmailTemplate($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$page_url = array_filter([
				'filter_type'   => $this->request->get['filter_type'] ?? null,
				'filter_status' => $this->request->get['filter_status'] ?? null,
				'sort'          => $this->request->get['sort'] ?? null,
				'order'         => $this->request->get['order'] ?? null,
				'page'          => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			if (isset($this->request->post['apply'])) {
				$template_id = $this->session->data['new_template_id'] ?? null;

				if ($template_id) {
					unset($this->session->data['new_template_id']);

					$this->redirect($this->url->link('tool/mail_manager/update', 'token=' . $this->session->data['token'] . '&template_id=' . $template_id . $url, 'SSL'));
				}
			} else {
				$this->redirect($this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function update(): void {
		$this->language->load('tool/mail_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('tool/mail_manager');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			$this->model_tool_mail_manager->editEmailTemplate((int)$this->request->get['template_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$page_url = array_filter([
				'filter_type'   => $this->request->get['filter_type'] ?? null,
				'filter_status' => $this->request->get['filter_status'] ?? null,
				'sort'          => $this->request->get['sort'] ?? null,
				'order'         => $this->request->get['order'] ?? null,
				'page'          => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			if (isset($this->request->post['apply'])) {
				$this->redirect($this->url->link('tool/mail_manager/update', 'token=' . $this->session->data['token'] . '&template_id=' . (int)$this->request->get['template_id'] . $url, 'SSL'));
			} else {
				$this->redirect($this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function delete(): void {
		$this->language->load('tool/mail_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('tool/mail_manager');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $template_id) {
				$this->model_tool_mail_manager->deleteEmailTemplate((int)$template_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$page_url = array_filter([
				'filter_type'   => $this->request->get['filter_type'] ?? null,
				'filter_status' => $this->request->get['filter_status'] ?? null,
				'sort'          => $this->request->get['sort'] ?? null,
				'order'         => $this->request->get['order'] ?? null,
				'page'          => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			$this->redirect($this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	// ----------------------------------------------------------------
	// AJAX endpoint — called by sale/contact template picker
	// Returns JSON: { template_id, name, subject, body } or { error }
	// URL: tool/mail_manager/getTemplate&code=newsletter&token=...
	// ----------------------------------------------------------------

	public function getTemplate(): void {
		$this->load->model('tool/mail_manager');

		$json = [];

		if (!$this->user->hasPermission('access', 'tool/mail_manager')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$code = $this->request->get['code'] ?? '';
			$store_id = (int)($this->request->get['store_id'] ?? 0);
			$language_id = (int)($this->request->get['language_id'] ?? 1);

			$template = $this->model_tool_mail_manager->getEmailTemplateByCode($code, $store_id, $language_id);

			if ($template) {
				$json['template_id'] = (int)$template['template_id'];
				$json['name'] = $template['name'];
				$json['subject'] = $template['subject'];
				$json['body'] = html_entity_decode($template['body'], ENT_QUOTES, 'UTF-8');
			} else {
				$json['error'] = $this->language->get('error_template_not_found');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// ----------------------------------------------------------------
	// AJAX endpoint — returns all active templates as a list
	// Used by the Newsletter page template picker dropdown
	// URL: tool/mail_manager/getTemplateList&token=...
	// ----------------------------------------------------------------

	public function getTemplateList(): void {
		$this->load->model('tool/mail_manager');

		$json = [];

		if (!$this->user->hasPermission('access', 'tool/mail_manager')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$templates = $this->model_tool_mail_manager->getEmailTemplates([
				'filter_status' => 1
			]);

			$json['templates'] = [];

			foreach ($templates as $template) {
				$json['templates'][] = [
					'template_id' => (int)$template['template_id'],
					'code'        => $template['code'],
					'name'        => $template['name'],
					'type'        => $template['type']
				];
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function getList(): void {
		$filter_name = $this->request->get['filter_name'] ?? '';
		$filter_code = $this->request->get['filter_code'] ?? '';
		$filter_type = $this->request->get['filter_type'] ?? '';
		$filter_status = $this->request->get['filter_status'] ?? '';
		$sort = $this->request->get['sort'] ?? 'type';
		$order = $this->request->get['order'] ?? 'ASC';
		$page = (int)($this->request->get['page'] ?? 1);

		// Build persistent URL params
		$page_url = array_filter([
			'filter_name'   => $filter_name ?: null,
			'filter_code'   => $filter_code ?: null,
			'filter_type'   => $filter_type ?: null,
			'filter_status' => $filter_status !== '' ? $filter_status : null,
			'sort'          => $sort,
			'order'         => $order,
			'page'          => $page > 1 ? $page : null
		]);

		$url = $page_url ? '&' . http_build_query($page_url) : '';

		// Breadcrumbs
		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		// Action links
		$this->data['insert'] = $this->url->link('tool/mail_manager/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('tool/mail_manager/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		// Pagination config
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		// Fetch templates
		$data = [
			'filter_name'   => $filter_name,
			'filter_code'   => $filter_code,
			'filter_type'   => $filter_type,
			'filter_status' => $filter_status,
			'sort'          => $sort,
			'order'         => $order,
			'start'         => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'         => $this->config->get('config_admin_limit')
		];

		$template_total = $this->model_tool_mail_manager->getTotalEmailTemplates($data);

		$this->data['template_total'] = $template_total;

		$results = $this->model_tool_mail_manager->getEmailTemplates($data);

		$this->data['templates'] = [];

		foreach ($results as $result) {
			$action = [];

			$action[] = [
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('tool/mail_manager/update', 'token=' . $this->session->data['token'] . '&template_id=' . $result['template_id'] . $url, 'SSL')
			];

			$this->data['templates'][] = [
				'template_id' => $result['template_id'],
				'name'        => $result['name'],
				'code'        => $result['code'],
				'type'        => $result['type'],
				'subject'     => $result['subject'],
				'status'      => $result['status'],
				'sort_order'  => $result['sort_order'],
				'selected'    => isset($this->request->post['selected']) && in_array($result['template_id'], $this->request->post['selected']),
				'action'      => $action
			];
		}

		// Language strings — headings & text
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm_delete'] = $this->language->get('text_confirm_delete');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_types'] = $this->language->get('text_all_types');
		$this->data['text_all_statuses'] = $this->language->get('text_all_statuses');

		// Entry labels needed by the filter bar in the list template
		$this->data['entry_type'] = $this->language->get('entry_type');
		$this->data['entry_status'] = $this->language->get('entry_status');

		// Language strings — columns
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_code'] = $this->language->get('column_code');
		$this->data['column_type'] = $this->language->get('column_type');
		$this->data['column_subject'] = $this->language->get('column_subject');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');

		// Language strings — buttons
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_filter'] = $this->language->get('button_filter');

		// Filter values (so the tpl can repopulate the filter row)
		$this->data['filter_name'] = $filter_name;
		$this->data['filter_code'] = $filter_code;
		$this->data['filter_type'] = $filter_type;
		$this->data['filter_status'] = $filter_status;

		// Token needed by the filter() JS function
		$this->data['token'] = $this->session->data['token'];

		// Confirm dialog text
		$this->data['text_confirm'] = $this->language->get('text_confirm');

		// Type options for filter dropdown
		$this->data['types'] = [
			'newsletter' => $this->language->get('text_type_newsletter'),
			'customer'   => $this->language->get('text_type_customer'),
			'order'      => $this->language->get('text_type_order'),
			'affiliate'  => $this->language->get('text_type_affiliate')
		];

		// Errors / success
		$this->data['error_warning'] = $this->error['warning'] ?? '';

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		// Sort links
		$sort_url = '&order=' . ($order === 'ASC' ? 'DESC' : 'ASC');

		if (isset($this->request->get['page'])) {
			$sort_url .= '&page=' . $this->request->get['page'];
		}

		if ($filter_type) { $sort_url .= '&filter_type=' . urlencode($filter_type); }
		if ($filter_status !== '') { $sort_url .= '&filter_status=' . $filter_status; }

		$this->data['sort_name'] = $this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . '&sort=name' . $sort_url, 'SSL');
		$this->data['sort_code'] = $this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . '&sort=code' . $sort_url, 'SSL');
		$this->data['sort_type'] = $this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . '&sort=type' . $sort_url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . '&sort=sort_order' . $sort_url, 'SSL');

		// Pagination
		$pagination_url = '';

		if ($filter_type) { $pagination_url .= '&filter_type=' . urlencode($filter_type); }
		if ($filter_status !== '') { $pagination_url .= '&filter_status=' . $filter_status; }

		if (isset($this->request->get['sort']))  { $pagination_url .= '&sort=' . $this->request->get['sort']; }
		if (isset($this->request->get['order'])) { $pagination_url .= '&order=' . $this->request->get['order']; }

		$pagination = new Pagination();
		$pagination->total = $template_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . $pagination_url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'tool/mail_manager_list.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function getForm(): void {
		// Heading
		$this->data['heading_title'] = $this->language->get('heading_title');

		// Text
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_default'] = $this->language->get('text_default');

		// Entries
		$this->data['entry_store'] = $this->language->get('entry_store');
		$this->data['entry_language'] = $this->language->get('entry_language');
		$this->data['entry_type'] = $this->language->get('entry_type');
		$this->data['entry_code'] = $this->language->get('entry_code');
		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_subject'] = $this->language->get('entry_subject');
		$this->data['entry_body'] = $this->language->get('entry_body');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		// Help
		$this->data['help_code'] = $this->language->get('help_code');
		$this->data['help_store'] = $this->language->get('help_store');

		// Buttons
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		// Placeholder sidebar
		$this->data['text_placeholders'] = $this->language->get('text_placeholders');
		$this->data['text_placeholder_hint'] = $this->language->get('text_placeholder_hint');

		$this->data['token'] = $this->session->data['token'];

		// Errors
		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['error_name'] = $this->error['name'] ?? '';
		$this->data['error_code'] = $this->error['code'] ?? '';
		$this->data['error_subject'] = $this->error['subject'] ?? '';
		$this->data['error_body'] = $this->error['body'] ?? '';

		// Persistent URL params
		$page_url = array_filter([
			'filter_type'   => $this->request->get['filter_type'] ?? null,
			'filter_status' => $this->request->get['filter_status'] ?? null,
			'sort'          => $this->request->get['sort'] ?? null,
			'order'         => $this->request->get['order'] ?? null,
			'page'          => $this->request->get['page'] ?? null
		]);

		$url = $page_url ? '&' . http_build_query($page_url) : '';

		// Breadcrumbs
		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		// Form action
		if (!isset($this->request->get['template_id'])) {
			$this->data['action'] = $this->url->link('tool/mail_manager/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('tool/mail_manager/update', 'token=' . $this->session->data['token'] . '&template_id=' . (int)$this->request->get['template_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('tool/mail_manager', 'token=' . $this->session->data['token'] . $url, 'SSL');

		// Load existing record if editing and not a POST re-render
		$template_info = [];

		if (isset($this->request->get['template_id']) && ($this->request->server['REQUEST_METHOD'] !== 'POST')) {
			$template_info = $this->model_tool_mail_manager->getEmailTemplate((int)$this->request->get['template_id']);
		}

		// Store checkboxes — array of store_ids
		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores([]);

		if (isset($this->request->post['mail_store'])) {
			$this->data['mail_store'] = $this->request->post['mail_store'];
		} elseif (isset($this->request->get['template_id'])) {
			$this->data['mail_store'] = $this->model_tool_mail_manager->getEmailTemplateStores((int)$this->request->get['template_id']);
		} else {
			$this->data['mail_store'] = [0];
		}

		// Field values: POST → DB → default
		$fields = [
			'language_id' => 1,
			'type'        => 'newsletter',
			'code'        => '',
			'name'        => '',
			'subject'     => '',
			'body'        => '',
			'status'      => 1,
			'sort_order'  => 0
		];

		foreach ($fields as $field => $default) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (!empty($template_info)) {
				$this->data[$field] = $template_info[$field];
			} else {
				$this->data[$field] = $default;
			}
		}

		// Language
		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages([]);

		// Type options
		$this->data['types'] = [
			'newsletter' => $this->language->get('text_type_newsletter'),
			'customer'   => $this->language->get('text_type_customer'),
			'order'      => $this->language->get('text_type_order'),
			'affiliate'  => $this->language->get('text_type_affiliate')
		];

		// Placeholder tokens grouped by type — drives the sidebar
		$this->data['placeholders'] = [
			'general' => [
				'{store_name}'    => $this->language->get('placeholder_store_name'),
				'{store_url}'     => $this->language->get('placeholder_store_url'),
				'{store_email}'   => $this->language->get('placeholder_store_email'),
				'{store_phone}'   => $this->language->get('placeholder_store_phone')
			],
			'customer' => [
				'{firstname}'     => $this->language->get('placeholder_firstname'),
				'{lastname}'      => $this->language->get('placeholder_lastname'),
				'{email}'         => $this->language->get('placeholder_email')
			],
			'order' => [
				'{order_id}'      => $this->language->get('placeholder_order_id'),
				'{order_total}'   => $this->language->get('placeholder_order_total'),
				'{order_status}'  => $this->language->get('placeholder_order_status'),
				'{order_date}'    => $this->language->get('placeholder_order_date')
			],
			'affiliate' => [
				'{affiliate_name}'  => $this->language->get('placeholder_affiliate_name'),
				'{affiliate_email}' => $this->language->get('placeholder_affiliate_email')
			]
		];

		$this->template = 'tool/mail_manager_form.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function validateForm(): bool {
		if (!$this->user->hasPermission('modify', 'tool/mail_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((mb_strlen($this->request->post['name'] ?? '', 'UTF-8') < 3) || (mb_strlen($this->request->post['name'] ?? '', 'UTF-8') > 128)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((mb_strlen($this->request->post['code'] ?? '', 'UTF-8') < 3) || (mb_strlen($this->request->post['code'] ?? '', 'UTF-8') > 64)) {
			$this->error['code'] = $this->language->get('error_code');
		}

		if (mb_strlen($this->request->post['subject'] ?? '', 'UTF-8') < 3) {
			$this->error['subject'] = $this->language->get('error_subject');
		}

		if (mb_strlen($this->request->post['body'] ?? '', 'UTF-8') < 10) {
			$this->error['body'] = $this->language->get('error_body');
		}

		return empty($this->error);
	}

	protected function validateDelete(): bool {
		if (!$this->user->hasPermission('modify', 'tool/mail_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}
}
