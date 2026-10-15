<?php
/**
 * Class ControllerToolSeoUrlManager
 *
 * @package NivoCart
 */
class ControllerToolSeoUrlManager extends Controller {
	private $error = [];

	public function index() {
		$this->language->load('tool/seo_url_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('tool/seo_url_manager');

		$this->getList();
	}

	public function audit() {
		$this->language->load('tool/seo_url_manager');

		$this->document->setTitle($this->language->get('heading_title_audit'));

		$this->load->model('tool/seo_url_manager');

		$problems = ['missing', 'duplicate', 'mistyped', 'orphan'];
		$types = ['product', 'category', 'manufacturer', 'information', 'news', 'other'];

		$filter_problem = (isset($this->request->get['filter_problem']) && in_array($this->request->get['filter_problem'], $problems)) ? $this->request->get['filter_problem'] : '';
		$filter_type = (isset($this->request->get['filter_type']) && in_array($this->request->get['filter_type'], $types)) ? $this->request->get['filter_type'] : '';
		$page = isset($this->request->get['page']) ? max(1, (int)$this->request->get['page']) : 1;

		$url = '';

		if ($filter_problem) {
			$url .= '&filter_problem=' . $filter_problem;
		}

		if ($filter_type) {
			$url .= '&filter_type=' . $filter_type;
		}

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title_audit'),
			'href'      => $this->url->link('tool/seo_url_manager/audit', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		$audit = $this->model_tool_seo_url_manager->getAudit();

		$summary = $audit['summary'];

		// Summary matrix, one row per entry type
		$this->data['summary_rows'] = [];

		foreach ($types as $type) {
			if (!isset($summary['types'][$type]) && ($type === 'other')) {
				continue;
			}

			$counts = [];

			foreach ($problems as $problem) {
				$count = $summary['types'][$type][$problem] ?? 0;

				$counts[$problem] = [
					'count' => $count,
					'href'  => $count ? $this->url->link('tool/seo_url_manager/audit', 'token=' . $this->session->data['token'] . '&filter_problem=' . $problem . '&filter_type=' . $type, 'SSL') : ''
				];
			}

			$this->data['summary_rows'][] = [
				'label'  => $this->language->get('text_type_' . $type),
				'counts' => $counts
			];
		}

		$this->data['summary_totals'] = [];

		foreach ($problems as $problem) {
			$this->data['summary_totals'][$problem] = [
				'count' => $summary[$problem],
				'href'  => $summary[$problem] ? $this->url->link('tool/seo_url_manager/audit', 'token=' . $this->session->data['token'] . '&filter_problem=' . $problem, 'SSL') : ''
			];
		}

		$this->data['audit_total'] = $summary['total'];

		// Filtered, paged list
		$rows = [];

		foreach ($audit['rows'] as $row) {
			if ((!$filter_problem || ($row['problem'] === $filter_problem)) && (!$filter_type || ($row['type'] === $filter_type))) {
				$rows[] = $row;
			}
		}

		$total = count($rows);
		$limit = max(1, (int)$this->config->get('config_admin_limit'));
		$page_rows = array_slice($rows, ($page - 1) * $limit, $limit);

		$ids = [];

		foreach ($page_rows as $row) {
			if ($row['id']) {
				$ids[$row['type']][] = $row['id'];
			}
		}

		$names = [];

		foreach ($ids as $type => $type_ids) {
			$names[$type] = $this->model_tool_seo_url_manager->getAuditNames($type, $type_ids);
		}

		$this->data['audit_rows'] = [];

		foreach ($page_rows as $row) {
			$href = false;
			$name = '';

			if ($row['type'] !== 'other') {
				$name = $names[$row['type']][$row['id']] ?? '';

				$href = $this->url->link('catalog/' . $row['type'] . '/update', 'token=' . $this->session->data['token'] . '&' . $row['type'] . '_id=' . $row['id'], 'SSL');
			}

			$detail = [];

			foreach (explode(',', $row['detail']) as $code) {
				$detail[] = $this->language->get('text_detail_' . $row['problem'] . '_' . $code);
			}

			$this->data['audit_rows'][] = [
				'problem' => $this->language->get('text_problem_' . $row['problem']),
				'status'  => $row['problem'],
				'type'    => $this->language->get('text_type_' . $row['type']),
				'name'    => $name,
				'href'    => $href,
				'query'   => $row['query'],
				'keyword' => $row['keyword'],
				'detail'  => implode(', ', $detail),
				'alias'   => $row['url_alias_id'] ? $this->url->link('tool/seo_url_manager/update', 'token=' . $this->session->data['token'] . '&url_alias_id=' . $row['url_alias_id'], 'SSL') : ''
			];
		}

		$this->data['heading_title'] = $this->language->get('heading_title_audit');

		$this->data['text_no_results'] = $this->language->get('text_audit_clean');
		$this->data['text_summary'] = $this->language->get('text_summary');
		$this->data['text_problems_found'] = sprintf($this->language->get('text_problems_found'), $summary['total']);
		$this->data['text_filtered'] = ($filter_problem || $filter_type) ? $this->language->get('text_filtered') : '';

		$this->data['column_type'] = $this->language->get('column_type');
		$this->data['column_problem'] = $this->language->get('column_problem');
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_query'] = $this->language->get('column_query');
		$this->data['column_keyword'] = $this->language->get('column_keyword');
		$this->data['column_detail'] = $this->language->get('column_detail');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['problem_labels'] = [];

		foreach ($problems as $problem) {
			$this->data['problem_labels'][$problem] = $this->language->get('text_problem_' . $problem);
		}

		$this->data['text_edit_entry'] = $this->language->get('text_edit_entry');
		$this->data['text_edit_alias'] = $this->language->get('text_edit_alias');
		$this->data['text_total'] = $this->language->get('text_total');

		$this->data['button_run_audit'] = $this->language->get('button_run_audit');
		$this->data['button_show_all'] = $this->language->get('button_show_all');
		$this->data['button_back'] = $this->language->get('button_back');

		$this->data['run_audit'] = $this->url->link('tool/seo_url_manager/audit', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['back'] = $this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'], 'SSL');

		if (!$this->config->get('config_seo_url')) {
			$this->data['error_url_status'] = $this->language->get('error_url_status');
		} else {
			$this->data['error_url_status'] = '';
		}

		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('tool/seo_url_manager/audit', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->template = 'tool/seo_url_manager_audit.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	public function insert() {
		$this->language->load('tool/seo_url_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('tool/seo_url_manager');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			$this->model_tool_seo_url_manager->addUrl($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$page_url = array_filter([
				'sort'  => $this->request->get['sort'] ?? null,
				'order' => $this->request->get['order'] ?? null,
				'page'  => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			if (isset($this->request->post['apply'])) {
				$url_alias_id = $this->session->data['new_url_alias_id'];

				if ($url_alias_id) {
					unset($this->session->data['new_url_alias_id']);

					$this->redirect($this->url->link('tool/seo_url_manager/update', 'token=' . $this->session->data['token'] . '&url_alias_id=' . $url_alias_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function update() {
		$this->language->load('tool/seo_url_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('tool/seo_url_manager');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			$this->model_tool_seo_url_manager->editUrl($this->request->get['url_alias_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$page_url = array_filter([
				'sort'  => $this->request->get['sort'] ?? null,
				'order' => $this->request->get['order'] ?? null,
				'page'  => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			if (isset($this->request->post['apply'])) {
				$url_alias_id = $this->request->get['url_alias_id'];

				if ($url_alias_id) {
					$this->redirect($this->url->link('tool/seo_url_manager/update', 'token=' . $this->session->data['token'] . '&url_alias_id=' . $url_alias_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->language->load('tool/seo_url_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('tool/seo_url_manager');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $url_alias_id) {
				$this->model_tool_seo_url_manager->deleteUrl($url_alias_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$page_url = array_filter([
				'sort'  => $this->request->get['sort'] ?? null,
				'order' => $this->request->get['order'] ?? null,
				'page'  => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			$this->redirect($this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$page_url = array_filter([
			'sort'  => $this->request->get['sort'] ?? 'keyword',
			'order' => $this->request->get['order'] ?? 'ASC',
			'page'  => $this->request->get['page'] ?? 1
		]);

		$url = $page_url ? '&' . http_build_query($page_url) : '';

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		$this->data['insert'] = $this->url->link('tool/seo_url_manager/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('tool/seo_url_manager/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['audit'] = $this->url->link('tool/seo_url_manager/audit', 'token=' . $this->session->data['token'], 'SSL');

		// Pagination
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$this->load->model('tool/image');

		$this->data['seo_urls'] = [];

		$sort = $this->request->get['sort'] ?? 'keyword';
		$order = $this->request->get['order'] ?? 'ASC';
		$page = $this->request->get['page'] ?? 1;

		$data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		];

		$keyword_total = $this->model_tool_seo_url_manager->getTotalUniqueKeywords();
		$url_total = $this->model_tool_seo_url_manager->getTotalUrls();

		$this->data['keyword_total'] = $keyword_total;
		$this->data['seo_url_total'] = $url_total;

		$results = $this->model_tool_seo_url_manager->getUrls($data);

		foreach ($results as $result) {
			$action = [];

			$action[] = [
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('tool/seo_url_manager/update', 'token=' . $this->session->data['token'] . '&url_alias_id=' . $result['url_alias_id'] . $url, 'SSL')
			];

			$query_link = false;

			if ($result['query']) {
				$check_query = strstr($result['query'], '_', true);

				if ($check_query === 'category') {
					$query_link = $this->url->link('catalog/category/update', 'token=' . $this->session->data['token'] . '&' . $result['query'], 'SSL');
				} elseif ($check_query === 'product') {
					$query_link = $this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . '&' . $result['query'], 'SSL');
				} elseif ($check_query === 'manufacturer') {
					$query_link = $this->url->link('catalog/manufacturer/update', 'token=' . $this->session->data['token'] . '&' . $result['query'], 'SSL');
				} elseif ($check_query === 'information') {
					$query_link = $this->url->link('catalog/information/update', 'token=' . $this->session->data['token'] . '&' . $result['query'], 'SSL');
				} elseif ($check_query === 'news') {
					$query_link = $this->url->link('catalog/news/update', 'token=' . $this->session->data['token'] . '&' . $result['query'], 'SSL');
				} else {
					$query_link = false;
				}
			}

			$this->data['seo_urls'][] = [
				'url_alias_id' => $result['url_alias_id'],
				'query'        => $result['query'],
				'query_link'   => $query_link,
				'keyword'      => $result['keyword'],
				'selected'     => isset($this->request->post['selected']) && in_array($result['url_alias_id'], $this->request->post['selected']),
				'action'       => $action
			];
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');
		$this->data['text_confirm_delete'] = $this->language->get('text_confirm_delete');

		$this->data['column_url_alias_id'] = $this->language->get('column_url_alias_id');
		$this->data['column_query'] = $this->language->get('column_query');
		$this->data['column_keyword'] = $this->language->get('column_keyword');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['link_seo_category'] = $this->language->get('link_seo_category');
		$this->data['link_seo_product'] = $this->language->get('link_seo_product');
		$this->data['link_seo_manufacturer'] = $this->language->get('link_seo_manufacturer');
		$this->data['link_seo_information'] = $this->language->get('link_seo_information');
		$this->data['link_seo_news'] = $this->language->get('link_seo_news');

		$this->data['button_audit'] = $this->language->get('button_audit');
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');

		$this->data['seo_category'] = $this->url->link('catalog/category', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['seo_product'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['seo_manufacturer'] = $this->url->link('catalog/manufacturer', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['seo_information'] = $this->url->link('catalog/information', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['seo_news'] = $this->url->link('catalog/news', 'token=' . $this->session->data['token'], 'SSL');

		$seo_url_status = $this->config->get('config_seo_url');

		if (!$seo_url_status) {
			$this->data['error_url_status'] = $this->language->get('error_url_status');
		} else {
			$this->data['error_url_status'] = '';
		}

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

		// Html table sorting data
		$url = '';

		if ($order === 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_url_alias_id'] = $this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'] . '&sort=url_alias_id' . $url, 'SSL');
		$this->data['sort_keyword'] = $this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'] . '&sort=keyword' . $url, 'SSL');

		// Pagination data
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $url_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'tool/seo_url_manager_list.tpl';
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

		$this->data['entry_query'] = $this->language->get('entry_query');
		$this->data['entry_keyword'] = $this->language->get('entry_keyword');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['query'])) {
			$this->data['error_query'] = $this->error['query'];
		} else {
			$this->data['error_query'] = '';
		}

		if (isset($this->error['keyword'])) {
			$this->data['error_keyword'] = $this->error['keyword'];
		} else {
			$this->data['error_keyword'] = '';
		}

		$page_url = array_filter([
			'sort'  => $this->request->get['sort'] ?? 'keyword',
			'order' => $this->request->get['order'] ?? 'ASC',
			'page'  => $this->request->get['page'] ?? 1
		]);

		$url = $page_url ? '&' . http_build_query($page_url) : '';

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		if (!isset($this->request->get['url_alias_id'])) {
			$this->data['action'] = $this->url->link('tool/seo_url_manager/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('tool/seo_url_manager/update', 'token=' . $this->session->data['token'] . '&url_alias_id=' . $this->request->get['url_alias_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('tool/seo_url_manager', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['url_alias_id']) && ($this->request->server['REQUEST_METHOD'] !== 'POST')) {
			$url_info = $this->model_tool_seo_url_manager->getUrl($this->request->get['url_alias_id']);
		}

		if (isset($this->request->post['query'])) {
			$this->data['query'] = trim($this->request->post['query']);
		} elseif (!empty($url_info)) {
			$this->data['query'] = trim($url_info['query']);
		} else {
			$this->data['query'] = '';
		}

		if (isset($this->request->post['keyword'])) {
			$this->data['keyword'] = trim($this->request->post['keyword']);
		} elseif (!empty($url_info)) {
			$this->data['keyword'] = trim($url_info['keyword']);
		} else {
			$this->data['keyword'] = '';
		}

		$this->template = 'tool/seo_url_manager_form.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'tool/seo_url_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((mb_strlen($this->request->post['query'], 'UTF-8') < 3) || (mb_strlen($this->request->post['query'], 'UTF-8') > 255)) {
			$this->error['query'] = $this->language->get('error_query');
		}

		if ((mb_strlen($this->request->post['keyword'], 'UTF-8') < 3) || (mb_strlen($this->request->post['keyword'], 'UTF-8') > 255)) {
			$this->error['keyword'] = $this->language->get('error_keyword');
		}

		$urls_array = [];

		$results = $this->model_tool_seo_url_manager->getUrls($urls_array);

		foreach ($results as $result) {
			if (isset($this->request->get['url_alias_id'])) {
				if (($this->request->post['keyword'] === $result['keyword']) && ($this->request->get['url_alias_id'] !== $result['url_alias_id'])) {
					$this->error['keyword'] = $this->language->get('error_keyword_exist');
				}
			} else {
				if ($this->request->post['keyword'] === $result['keyword']) {
					$this->error['keyword'] = $this->language->get('error_keyword_exist');
				}
			}
		}

		return empty($this->error);
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'tool/seo_url_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}
}
