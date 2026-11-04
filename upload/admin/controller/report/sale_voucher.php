<?php
/**
 * Class ControllerReportSaleVoucher
 *
 * @package NivoCart
 */
class ControllerReportSaleVoucher extends Controller {
	/** Error array Placeholder */

	public function index() {
		$this->language->load('report/sale_voucher');

		$this->document->setTitle($this->language->get('heading_title'));

		$filter_date_start = $this->request->get['filter_date_start'] ?? '';
		$filter_date_end = $this->request->get['filter_date_end'] ?? '';
		$filter_state = $this->request->get['filter_state'] ?? '';

		if (isset($this->request->get['page'])) {
			$page = max(1, (int)$this->request->get['page']);
		} else {
			$page = 1;
		}

		$url = '';

		if ($filter_date_start !== '') {
			$url .= '&filter_date_start=' . urlencode(html_entity_decode($filter_date_start, ENT_QUOTES, 'UTF-8'));
		}

		if ($filter_date_end !== '') {
			$url .= '&filter_date_end=' . urlencode(html_entity_decode($filter_date_end, ENT_QUOTES, 'UTF-8'));
		}

		if ($filter_state !== '') {
			$url .= '&filter_state=' . urlencode($filter_state);
		}

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('report/sale_voucher', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		// Pagination
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$this->load->model('report/voucher');

		$this->data['vouchers'] = [];

		$data = [
			'filter_date_start' => $filter_date_start,
			'filter_date_end'   => $filter_date_end,
			'filter_state'      => $filter_state,
			'start'             => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'             => $this->config->get('config_admin_limit')
		];

		$voucher_total = $this->model_report_voucher->getTotalVouchers($data);

		$results = $this->model_report_voucher->getVouchers($data);

		$date_format = $this->language->get('date_format_short');

		if ($date_format === 'date_format_short') {
			$date_format = 'd/m/Y';
		}

		foreach ($results as $result) {
			$action = [];

			$action[] = [
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/voucher/update', 'token=' . $this->session->data['token'] . '&voucher_id=' . $result['voucher_id'], 'SSL')
			];

			$this->data['vouchers'][] = [
				'code'      => voucherCodeFormat($result['code']),
				'recipient' => $result['to_name'],
				'value'     => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'balance'   => $this->currency->format(max(0, $result['balance']), $this->config->get('config_currency')),
				'purchased' => date($date_format, strtotime($result['date_added'])),
				'sent'      => $result['date_sent'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'valid'     => $result['date_sent'] ? date($date_format, strtotime($result['date_sent'])) : $this->language->get('text_never'),
				'expiry'    => $result['date_expire'] ? date($date_format, strtotime($result['date_expire'])) : $this->language->get('text_never'),
				'redeemed'  => $result['date_redeemed'] ? date($date_format, strtotime($result['date_redeemed'])) : $this->language->get('text_never'),
				'state'     => $this->language->get('text_' . $result['state']),
				'action'    => $action
			];
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_all_states'] = $this->language->get('text_all_states');

		$this->data['column_code'] = $this->language->get('column_code');
		$this->data['column_recipient'] = $this->language->get('column_recipient');
		$this->data['column_value'] = $this->language->get('column_value');
		$this->data['column_balance'] = $this->language->get('column_balance');
		$this->data['column_purchased'] = $this->language->get('column_purchased');
		$this->data['column_sent'] = $this->language->get('column_sent');
		$this->data['column_valid'] = $this->language->get('column_valid');
		$this->data['column_expiry'] = $this->language->get('column_expiry');
		$this->data['column_redeemed'] = $this->language->get('column_redeemed');
		$this->data['column_state'] = $this->language->get('column_state');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
		$this->data['entry_state'] = $this->language->get('entry_state');

		$this->data['button_close'] = $this->language->get('button_close');
		$this->data['button_filter'] = $this->language->get('button_filter');

		$this->data['states'] = [];

		foreach (['active', 'used', 'expired', 'pending', 'disabled'] as $state) {
			$this->data['states'][] = [
				'value' => $state,
				'name'  => $this->language->get('text_' . $state)
			];
		}

		$this->data['close'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['token'] = $this->session->data['token'];

		$pagination = new Pagination();
		$pagination->total = $voucher_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('report/sale_voucher', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['filter_date_start'] = $filter_date_start;
		$this->data['filter_date_end'] = $filter_date_end;
		$this->data['filter_state'] = $filter_state;

		$this->template = 'report/sale_voucher.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}
}
