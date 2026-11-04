<?php
/**
 * Class ControllerSaleVoucher
 *
 * @package NivoCart
 */
class ControllerSaleVoucher extends Controller {
	private $error = [];

	public function index() {
		$this->language->load('sale/voucher');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/voucher');

		$this->getList();
	}

	public function insert() {
		$this->language->load('sale/voucher');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/voucher');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			$this->model_sale_voucher->addVoucher($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->getUrl();

			if (isset($this->request->post['apply'])) {
				$voucher_id = $this->session->data['new_voucher_id'];

				if ($voucher_id) {
					unset($this->session->data['new_voucher_id']);

					$this->redirect($this->url->link('sale/voucher/update', 'token=' . $this->session->data['token'] . '&voucher_id=' . $voucher_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function update() {
		$this->language->load('sale/voucher');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/voucher');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			$this->model_sale_voucher->editVoucher((int)$this->request->get['voucher_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->getUrl();

			if (isset($this->request->post['apply'])) {
				$voucher_id = $this->request->get['voucher_id'];

				if ($voucher_id) {
					$this->redirect($this->url->link('sale/voucher/update', 'token=' . $this->session->data['token'] . '&voucher_id=' . $voucher_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->language->load('sale/voucher');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/voucher');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $voucher_id) {
				$this->model_sale_voucher->deleteVoucher((int)$voucher_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->getUrl();

			$this->redirect($this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$filter_code = $this->request->get['filter_code'] ?? '';
		$filter_name = $this->request->get['filter_name'] ?? '';
		$filter_state = $this->request->get['filter_state'] ?? '';
		$filter_date_start = $this->request->get['filter_date_start'] ?? '';
		$filter_date_end = $this->request->get['filter_date_end'] ?? '';

		$sort = $this->request->get['sort'] ?? 'v.date_added';
		$order = $this->request->get['order'] ?? 'DESC';
		$page = (int)($this->request->get['page'] ?? 1);

		if ($page < 1) {
			$page = 1;
		}

		$url = $this->getUrl();

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		$this->data['insert'] = $this->url->link('sale/voucher/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/voucher/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		// Pagination
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$this->data['vouchers'] = [];

		$data = [
			'filter_code'       => $filter_code,
			'filter_name'       => $filter_name,
			'filter_state'      => $filter_state,
			'filter_date_start' => $filter_date_start,
			'filter_date_end'   => $filter_date_end,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'             => $this->config->get('config_admin_limit')
		];

		$voucher_total = $this->model_sale_voucher->getTotalVouchers($data);

		$results = $this->model_sale_voucher->getVouchers($data);

		foreach ($results as $result) {
			$action = [];

			$action[] = [
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/voucher/update', 'token=' . $this->session->data['token'] . '&voucher_id=' . $result['voucher_id'] . $url, 'SSL')
			];

			$this->data['vouchers'][] = [
				'voucher_id' => $result['voucher_id'],
				'code'       => voucherCodeFormat($result['code']),
				'from'       => $result['from_name'],
				'to'         => $result['to_name'],
				'design'     => $this->language->get('text_design_' . $result['design']),
				'amount'     => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'balance'    => $this->currency->format($result['balance'], $this->config->get('config_currency')),
				'state'      => $result['state'],
				'state_text' => $this->language->get('text_state_' . $result['state']),
				'sent'       => $result['date_sent'] ? date($this->language->get('date_format_short'), strtotime($result['date_sent'])) : $this->language->get('text_not_sent'),
				'expire'     => $result['date_expire'] ? date($this->language->get('date_format_short'), strtotime($result['date_expire'])) : $this->language->get('text_never'),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'is_sent'    => !empty($result['date_sent']),
				'can_send'   => ($result['state'] === 'active'),
				'selected'   => isset($this->request->post['selected']) && in_array($result['voucher_id'], $this->request->post['selected']),
				'action'     => $action
			];
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');
		$this->data['text_confirm_delete'] = $this->language->get('text_confirm_delete');
		$this->data['text_confirm_send'] = $this->language->get('text_confirm_send');
		$this->data['text_confirm_send_text'] = $this->language->get('text_confirm_send_text');
		$this->data['text_send'] = $this->language->get('text_send');
		$this->data['text_resend'] = $this->language->get('text_resend');
		$this->data['text_wait'] = $this->language->get('text_wait');

		$this->data['column_code'] = $this->language->get('column_code');
		$this->data['column_from'] = $this->language->get('column_from');
		$this->data['column_to'] = $this->language->get('column_to');
		$this->data['column_design'] = $this->language->get('column_design');
		$this->data['column_amount'] = $this->language->get('column_amount');
		$this->data['column_balance'] = $this->language->get('column_balance');
		$this->data['column_state'] = $this->language->get('column_state');
		$this->data['column_sent'] = $this->language->get('column_sent');
		$this->data['column_expire'] = $this->language->get('column_expire');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_filter'] = $this->language->get('button_filter');

		$this->data['states'] = [];

		foreach (['active', 'pending', 'used', 'expired', 'disabled'] as $state) {
			$this->data['states'][] = [
				'value' => $state,
				'name'  => $this->language->get('text_state_' . $state)
			];
		}

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

		// Html table sorting data
		$sorts = [
			'code'       => 'v.code',
			'from'       => 'v.from_name',
			'to'         => 'v.to_name',
			'design'     => 'v.design',
			'amount'     => 'v.amount',
			'balance'    => 'balance',
			'state'      => 'state',
			'sent'       => 'v.date_sent',
			'expire'     => 'v.date_expire',
			'date_added' => 'v.date_added'
		];

		foreach ($sorts as $key => $column) {
			$this->data['sort_' . $key] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $this->getUrl(['sort', 'order', 'page']) . '&sort=' . $column . '&order=' . (($sort === $column && $order === 'ASC') ? 'DESC' : 'ASC'), 'SSL');
		}

		// Pagination data
		$url = $this->getUrl(['page']);

		$pagination = new Pagination();
		$pagination->total = $voucher_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['filter_code'] = $filter_code;
		$this->data['filter_name'] = $filter_name;
		$this->data['filter_state'] = $filter_state;
		$this->data['filter_date_start'] = $filter_date_start;
		$this->data['filter_date_end'] = $filter_date_end;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'sale/voucher_list.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_confirm_send'] = $this->language->get('text_confirm_send');

		$this->data['entry_code'] = $this->language->get('entry_code');
		$this->data['entry_from_name'] = $this->language->get('entry_from_name');
		$this->data['entry_from_email'] = $this->language->get('entry_from_email');
		$this->data['entry_to_name'] = $this->language->get('entry_to_name');
		$this->data['entry_to_email'] = $this->language->get('entry_to_email');
		$this->data['entry_design'] = $this->language->get('entry_design');
		$this->data['entry_message'] = $this->language->get('entry_message');
		$this->data['entry_amount'] = $this->language->get('entry_amount');
		$this->data['entry_expire'] = $this->language->get('entry_expire');
		$this->data['entry_balance'] = $this->language->get('entry_balance');
		$this->data['entry_state'] = $this->language->get('entry_state');
		$this->data['entry_sent'] = $this->language->get('entry_sent');
		$this->data['entry_status'] = $this->language->get('entry_status');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_generate'] = $this->language->get('button_generate');

		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_voucher_history'] = $this->language->get('tab_voucher_history');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->request->get['voucher_id'])) {
			$this->data['voucher_id'] = (int)$this->request->get['voucher_id'];
		} else {
			$this->data['voucher_id'] = 0;
		}

		// Errors
		$errors = ['warning', 'code', 'from_name', 'from_email', 'to_name', 'to_email', 'design', 'message', 'amount', 'expire'];

		foreach ($errors as $error) {
			$this->data['error_' . $error] = $this->error[$error] ?? '';
		}

		// Breadcrumbs
		$url = $this->getUrl();

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		if (!isset($this->request->get['voucher_id'])) {
			$this->data['action'] = $this->url->link('sale/voucher/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/voucher/update', 'token=' . $this->session->data['token'] . '&voucher_id=' . $this->request->get['voucher_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['generate'] = $this->url->link('sale/voucher/generate', 'token=' . $this->session->data['token'], 'SSL');

		$voucher_info = [];

		if (isset($this->request->get['voucher_id']) && ($this->request->server['REQUEST_METHOD'] !== 'POST')) {
			$voucher_info = $this->model_sale_voucher->getVoucher((int)$this->request->get['voucher_id']);
		}

		if (isset($this->request->post['code'])) {
			$this->data['code'] = $this->request->post['code'];
		} elseif (!empty($voucher_info)) {
			$this->data['code'] = voucherCodeFormat($voucher_info['code']);
		} else {
			$this->data['code'] = '';
		}

		foreach (['from_name', 'from_email', 'to_name', 'to_email', 'message'] as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (!empty($voucher_info)) {
				$this->data[$field] = $voucher_info[$field];
			} else {
				$this->data[$field] = '';
			}
		}

		$this->data['designs'] = [];

		foreach (voucherDesigns() as $design) {
			$this->data['designs'][] = [
				'value' => $design,
				'name'  => $this->language->get('text_design_' . $design)
			];
		}

		if (isset($this->request->post['design'])) {
			$this->data['design'] = $this->request->post['design'];
		} elseif (!empty($voucher_info)) {
			$this->data['design'] = $voucher_info['design'];
		} else {
			$this->data['design'] = 'general';
		}

		if (isset($this->request->post['amount'])) {
			$this->data['amount'] = $this->request->post['amount'];
		} elseif (!empty($voucher_info)) {
			$this->data['amount'] = $voucher_info['amount'];
		} else {
			$this->data['amount'] = '';
		}

		if (isset($this->request->post['date_expire'])) {
			$this->data['date_expire'] = $this->request->post['date_expire'];
		} elseif (!empty($voucher_info) && $voucher_info['date_expire']) {
			$this->data['date_expire'] = substr($voucher_info['date_expire'], 0, 10);
		} else {
			$this->data['date_expire'] = '';
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($voucher_info)) {
			$this->data['status'] = $voucher_info['status'];
		} else {
			$this->data['status'] = 1;
		}

		// Read only information (existing vouchers)
		$this->data['balance'] = '';
		$this->data['state'] = '';
		$this->data['sent'] = '';

		if ($this->data['voucher_id']) {
			$info = $this->model_sale_voucher->getVoucher($this->data['voucher_id']);

			if ($info) {
				$this->data['balance'] = $this->currency->format($this->model_sale_voucher->getVoucherBalance($this->data['voucher_id']), $this->config->get('config_currency'));
				$this->data['state'] = $this->language->get('text_state_' . $this->model_sale_voucher->getVoucherState($this->data['voucher_id']));
				$this->data['sent'] = $info['date_sent'] ? date($this->language->get('date_format_short'), strtotime($info['date_sent'])) : $this->language->get('text_not_sent');
			}
		}

		$this->template = 'sale/voucher_form.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	// Validate functions
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/voucher')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$voucher_id = isset($this->request->get['voucher_id']) ? (int)$this->request->get['voucher_id'] : 0;

		$code = trim((string)($this->request->post['code'] ?? ''));

		$unchanged = false;

		if ($voucher_id) {
			$current = $this->model_sale_voucher->getVoucher($voucher_id);

			// A stored code that has not been touched is always accepted (older codes may contain other characters)
			if ($current && ($current['code'] === $code)) {
				$unchanged = true;
			}
		}

		if (!$unchanged) {
			// An empty code on a new voucher is allowed: a secure code is generated when it is saved
			if (($code !== '' || $voucher_id) && !preg_match('/^[A-Z0-9]{3,20}$/', voucherCodeNormalise($code))) {
				$this->error['code'] = $this->language->get('error_code');
			}

			if ($code !== '' && !isset($this->error['code'])) {
				$voucher_info = $this->model_sale_voucher->getVoucherByCode($code);

				if ($voucher_info && ((int)$voucher_info['voucher_id'] !== $voucher_id)) {
					$this->error['code'] = $this->language->get('error_exists');
				}
			}
		}

		if ((mb_strlen($this->request->post['to_name'], 'UTF-8') < 1) || (mb_strlen($this->request->post['to_name'], 'UTF-8') > 64)) {
			$this->error['to_name'] = $this->language->get('error_to_name');
		}

		if (!emailIsValid($this->request->post['to_email'])) {
			$this->error['to_email'] = $this->language->get('error_email');
		}

		if ((mb_strlen($this->request->post['from_name'], 'UTF-8') < 1) || (mb_strlen($this->request->post['from_name'], 'UTF-8') > 64)) {
			$this->error['from_name'] = $this->language->get('error_from_name');
		}

		if (!emailIsValid($this->request->post['from_email'])) {
			$this->error['from_email'] = $this->language->get('error_email');
		}

		if (!voucherDesignIsValid($this->request->post['design'] ?? '')) {
			$this->error['design'] = $this->language->get('error_design');
		}

		if ((mb_strlen($this->request->post['message'], 'UTF-8') < 3) || (mb_strlen($this->request->post['message'], 'UTF-8') > 128)) {
			$this->error['message'] = $this->language->get('error_message');
		}

		if (!is_numeric($this->request->post['amount']) || ((float)$this->request->post['amount'] < 1)) {
			$this->error['amount'] = $this->language->get('error_amount');
		}

		if (!empty($this->request->post['date_expire'])) {
			$expire = $this->request->post['date_expire'];

			if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $expire, $match) || !checkdate((int)$match[2], (int)$match[3], (int)$match[1])) {
				$this->error['expire'] = $this->language->get('error_expire');
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/voucher')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('sale/order');

		foreach ($this->request->post['selected'] as $voucher_id) {
			$order_voucher_info = $this->model_sale_order->getOrderVoucherByVoucherId((int)$voucher_id);

			if ($order_voucher_info) {
				$this->error['warning'] = sprintf($this->language->get('error_order'), $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $order_voucher_info['order_id'], 'SSL'));
				break;
			}
		}

		return empty($this->error);
	}

	// History
	public function history() {
		$this->language->load('sale/voucher');

		$this->load->model('sale/voucher');

		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_order_id'] = $this->language->get('column_order_id');
		$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_amount'] = $this->language->get('column_amount');
		$this->data['column_date_added'] = $this->language->get('column_date_added');

		// Pagination
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

		if ($page < 1) {
			$page = 1;
		}

		$voucher_id = (int)($this->request->get['voucher_id'] ?? 0);

		$this->data['histories'] = [];

		$history_total = $this->model_sale_voucher->getTotalVoucherHistories($voucher_id);

		$results = $this->model_sale_voucher->getVoucherHistories($voucher_id, ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['histories'][] = [
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'amount'     => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			];
		}

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/voucher/history', 'token=' . $this->session->data['token'] . '&voucher_id=' . $voucher_id . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->template = 'sale/voucher_history.tpl';

		$this->response->setOutput($this->render());
	}

	// Send (and resend) the voucher email
	public function send() {
		$json = [];

		$this->language->load('sale/voucher');

		if (!$this->user->hasPermission('modify', 'sale/voucher')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif (isset($this->request->get['voucher_id'])) {
			$this->load->model('sale/voucher');

			$voucher_id = (int)$this->request->get['voucher_id'];

			$state = $this->model_sale_voucher->getVoucherState($voucher_id);

			if ($state !== 'active') {
				$json['error'] = sprintf($this->language->get('error_send_state'), $this->language->get('text_state_' . $state));
			} elseif ($this->model_sale_voucher->sendVoucher($voucher_id)) {
				$json['success'] = $this->language->get('text_sent');
			} else {
				$json['error'] = $this->language->get('error_send');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// Generate a secure code (Generate button on the form)
	public function generate() {
		$json = [];

		$this->language->load('sale/voucher');

		if (!$this->user->hasPermission('modify', 'sale/voucher')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('sale/voucher');

			$json['code'] = voucherCodeFormat($this->model_sale_voucher->generateCode());
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Build the filter, sort and page part of a URL from the current request.
	 *
	 * @param array<int, string> $exclude Parameters to leave out
	 */
	private function getUrl(array $exclude = []): string {
		$keys = ['filter_code', 'filter_name', 'filter_state', 'filter_date_start', 'filter_date_end', 'sort', 'order', 'page'];

		$url = '';

		foreach ($keys as $key) {
			if (!in_array($key, $exclude, true) && isset($this->request->get[$key])) {
				$url .= '&' . $key . '=' . urlencode(html_entity_decode($this->request->get[$key], ENT_QUOTES, 'UTF-8'));
			}
		}

		return $url;
	}
}
