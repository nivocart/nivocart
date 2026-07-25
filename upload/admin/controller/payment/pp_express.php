<?php
/**
 * Class ControllerPaymentPPExpress
 *
 * Admin controller for PayPal Express — Orders v2 REST API.
 *
 * @package NivoCart
 */
class ControllerPaymentPPExpress extends Controller {
	public const DEBUG_LOG_FILE = 'pp_express.log';
	private array $errors = [];

	// -------------------------------------------------------------------------
	// Settings page
	// -------------------------------------------------------------------------
	public function index(): void {
		$this->language->load('payment/pp_express');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('payment/pp_express');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pp_express', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['apply'])) {
				$this->redirect($this->url->link('payment/pp_express', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
			}
		} else {
			$this->data['errors'] = $this->errors;
		}

		// ── Heading & common text ────────────────────────────────────────────
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_capture'] = $this->language->get('text_capture');
		$this->data['text_authorize'] = $this->language->get('text_authorize');

		// ── Entry labels ─────────────────────────────────────────────────────
		$this->data['entry_client_id'] = $this->language->get('entry_client_id');
		$this->data['entry_client_secret'] = $this->language->get('entry_client_secret');
		$this->data['entry_sandbox_client_id'] = $this->language->get('entry_sandbox_client_id');
		$this->data['entry_sandbox_client_secret'] = $this->language->get('entry_sandbox_client_secret');
		$this->data['entry_webhook_id'] = $this->language->get('entry_webhook_id');
		$this->data['entry_sandbox_webhook_id'] = $this->language->get('entry_sandbox_webhook_id');
		$this->data['entry_sandbox'] = $this->language->get('entry_sandbox');
		$this->data['entry_transaction_mode'] = $this->language->get('entry_transaction_mode');
		$this->data['entry_pay_later'] = $this->language->get('entry_pay_later');
		$this->data['entry_currency'] = $this->language->get('entry_currency');
		$this->data['entry_debug'] = $this->language->get('entry_debug');
		$this->data['entry_total'] = $this->language->get('entry_total');
		$this->data['entry_total_max'] = $this->language->get('entry_total_max');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_webhook_url'] = $this->language->get('entry_webhook_url');

		// Order status mappings
		$this->data['entry_completed_status'] = $this->language->get('entry_completed_status');
		$this->data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$this->data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$this->data['entry_refunded_status'] = $this->language->get('entry_refunded_status');
		$this->data['entry_voided_status'] = $this->language->get('entry_voided_status');
		$this->data['entry_denied_status'] = $this->language->get('entry_denied_status');
		$this->data['entry_expired_status'] = $this->language->get('entry_expired_status');

		// ── Help text ────────────────────────────────────────────────────────
		$this->data['help_sandbox'] = $this->language->get('help_sandbox');
		$this->data['help_transaction_mode'] = $this->language->get('help_transaction_mode');
		$this->data['help_pay_later'] = $this->language->get('help_pay_later');
		$this->data['help_currency'] = $this->language->get('help_currency');
		$this->data['help_debug'] = $this->language->get('help_debug');
		$this->data['help_total'] = $this->language->get('help_total');
		$this->data['help_total_max'] = $this->language->get('help_total_max');
		$this->data['help_webhook_id'] = $this->language->get('help_webhook_id');

		// ── Buttons ──────────────────────────────────────────────────────────
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		// ── Tabs ─────────────────────────────────────────────────────────────
		$this->data['tab_api'] = $this->language->get('tab_api');
		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_order_status'] = $this->language->get('tab_order_status');
		$this->data['tab_debug_log'] = $this->language->get('tab_debug_log');

		// ── Session flash ────────────────────────────────────────────────────
		$this->data['token'] = $this->session->data['token'];

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		// ── Breadcrumbs ──────────────────────────────────────────────────────
		$this->data['breadcrumbs'] = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false,
			],
			[
				'text'      => $this->language->get('text_payment'),
				'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: ',
			],
			[
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('payment/pp_express', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: ',
			],
		];

		$this->data['action'] = $this->url->link('payment/pp_express', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		// ── API credentials ──────────────────────────────────────────────────
		$fields = [
			'pp_express_client_id',
			'pp_express_client_secret',
			'pp_express_sandbox_client_id',
			'pp_express_sandbox_client_secret',
			'pp_express_webhook_id',
			'pp_express_sandbox_webhook_id',
			'pp_express_sandbox',
			'pp_express_transaction_mode',
			'pp_express_pay_later',
			'pp_express_currency',
			'pp_express_debug',
			'pp_express_total',
			'pp_express_total_max',
			'pp_express_geo_zone_id',
			'pp_express_status',
			'pp_express_sort_order',
			'pp_express_completed_status_id',
			'pp_express_pending_status_id',
			'pp_express_failed_status_id',
			'pp_express_refunded_status_id',
			'pp_express_voided_status_id',
			'pp_express_denied_status_id',
			'pp_express_expired_status_id',
		];

		foreach ($fields as $field) {
			$this->data[$field] = $this->request->post[$field] ?? $this->config->get($field);
		}

		// Webhook URL (read-only display, catalog side)
		$this->data['webhook_url'] = HTTPS_CATALOG . 'index.php?route=payment/pp_express/webhook';

		// ── Dropdowns ────────────────────────────────────────────────────────
		$this->data['currencies'] = $this->model_payment_pp_express->getCurrencies();

		$this->load->model('localisation/geo_zone');
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones([]);

		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses([]);

		// ── Debug log tab ────────────────────────────────────────────────────
		if ($this->data['pp_express_debug']) {
			$this->data['button_debug_clear'] = $this->language->get('button_clear');
			$this->data['button_debug_download'] = $this->language->get('button_download');
			$this->data['debug_clear'] = $this->url->link('payment/pp_express/debugClear', 'token=' . $this->session->data['token'], 'SSL');
			$this->data['debug_download'] = $this->url->link('payment/pp_express/debugDownload', 'token=' . $this->session->data['token'], 'SSL');

			if (!is_dir(DIR_SYSTEM . 'logs/')) {
				mkdir(DIR_SYSTEM . 'logs', 0777);
			}

			$debug_file = DIR_LOGS . self::DEBUG_LOG_FILE;

			$this->data['debug_log'] = file_exists($debug_file) ? file_get_contents($debug_file) : '';

			clearstatcache();
		}

		$this->template = 'payment/pp_express.tpl';
		$this->children = ['common/header', 'common/footer'];

		$this->response->setOutput($this->render());
	}

	// -------------------------------------------------------------------------
	// Validation
	// -------------------------------------------------------------------------
	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'payment/pp_express')) {
			$this->errors['warning'] = $this->language->get('error_permission');
		}

		if (!empty($this->request->post['pp_express_sandbox'])) {
			if (empty($this->request->post['pp_express_sandbox_client_id'])) {
				$this->errors['sandbox_client_id'] = $this->language->get('error_sandbox_client_id');
			}
			if (empty($this->request->post['pp_express_sandbox_client_secret'])) {
				$this->errors['sandbox_client_secret'] = $this->language->get('error_sandbox_client_secret');
			}
		} else {
			if (empty($this->request->post['pp_express_client_id'])) {
				$this->errors['client_id'] = $this->language->get('error_client_id');
			}
			if (empty($this->request->post['pp_express_client_secret'])) {
				$this->errors['client_secret'] = $this->language->get('error_client_secret');
			}
		}

		return empty($this->errors);
	}

	// -------------------------------------------------------------------------
	// Install / Uninstall
	// -------------------------------------------------------------------------
	public function install(): void {
		$this->load->model('payment/pp_express');
		$this->model_payment_pp_express->install();
	}

	public function uninstall(): void {
		$this->load->model('payment/pp_express');
		$this->model_payment_pp_express->uninstall();
	}

	// -------------------------------------------------------------------------
	// Order action panel (rendered inside sale/order/info)
	// -------------------------------------------------------------------------
	public function orderAction(): void {
		if (!$this->config->get('pp_express_status')) {
			return;
		}

		$this->language->load('payment/pp_express');

		$order_id = (int)($this->request->get['order_id'] ?? 0);

		$this->load->model('payment/pp_express');

		$paypal_order = $this->model_payment_pp_express->getPaypalOrderByOrderId($order_id);

		if (!$paypal_order) {
			return;
		}

		// ── Text strings ─────────────────────────────────────────────────────
		$lang_keys = [
			'text_payment_info', 'text_intent', 'text_pp_order_id', 'text_status',
			'text_capture_id', 'text_amount_authorised', 'text_amount_captured',
			'text_amount_refunded', 'text_amount_remaining', 'text_transactions',
			'text_confirm_void', 'text_success', 'text_loading', 'text_no_results',
			'column_type', 'column_capture_id', 'column_amount', 'column_currency',
			'column_status', 'column_note', 'column_created', 'column_actions',
			'entry_capture_amount', 'entry_capture_note',
			'button_capture', 'button_capture_full', 'button_void', 'button_refund',
			'error_capture_amt', 'error_timeout', 'error_missing_order',
		];

		foreach ($lang_keys as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		$this->data['token'] = $this->session->data['token'];
		$this->data['order_id'] = $order_id;

		// ── Totals ───────────────────────────────────────────────────────────
		$captured = $this->model_payment_pp_express->getTotalCaptured($paypal_order['paypal_order_id']);
		$refunded = $this->model_payment_pp_express->getTotalRefunded($paypal_order['paypal_order_id']);
		$remaining = $paypal_order['total'] - $captured + $refunded;

		$paypal_order['captured'] = number_format($captured, 2);
		$paypal_order['refunded'] = number_format($refunded, 2);
		$paypal_order['remaining'] = number_format(max($remaining, 0), 2);

		$this->data['paypal_order'] = $paypal_order;

		// ── Transactions (folded in) ──────────────────────────────────────────
		$transactions = $this->model_payment_pp_express->getTransactions($paypal_order['paypal_order_id']);

		$this->data['transactions'] = [];

		foreach ($transactions as $t) {
			$this->data['transactions'][] = [
				'paypal_order_transaction_id' => $t['paypal_order_transaction_id'],
				'transaction_type' => $t['transaction_type'],
				'capture_id'       => $t['capture_id'],
				'amount'           => number_format((float)$t['amount'], 2),
				'currency_code'    => $t['currency_code'],
				'status'           => $t['status'],
				'note'             => $t['note'],
				'created'          => date($this->language->get('date_format_time'), strtotime($t['created'])),
				'refund'           => ($t['transaction_type'] === 'CAPTURE' && $t['status'] === 'COMPLETED') ? $this->url->link('payment/pp_express/refund', 'token=' . $this->session->data['token'] . '&capture_id=' . urlencode($t['capture_id']) . '&order_id=' . $order_id, 'SSL') : '',
			];
		}

		// ── AJAX endpoint URLs ────────────────────────────────────────────────
		$this->data['url_capture'] = $this->url->link('payment/pp_express/doCapture', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['url_capture_full'] = $this->url->link('payment/pp_express/doCaptureFull', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['url_void'] = $this->url->link('payment/pp_express/doVoid', 'token=' . $this->session->data['token'], 'SSL');

		$this->template = 'payment/pp_express_order.tpl';

		$this->response->setOutput($this->render());
	}

	// -------------------------------------------------------------------------
	// Capture (partial — Authorize mode only)
	// -------------------------------------------------------------------------
	public function doCapture(): void {
		$json = [];

		$this->language->load('payment/pp_express');

		$order_id = (int)($this->request->post['order_id'] ?? 0);
		$amount = (float)($this->request->post['amount'] ?? 0);
		$note = $this->request->post['note'] ?? '';

		if (!$order_id || $amount <= 0) {
			$json['error'] = $this->language->get('error_missing_data');
			$this->jsonOutput($json);
			return;
		}

		$this->load->model('payment/pp_express');

		$paypal_order = $this->model_payment_pp_express->getPaypalOrderByOrderId($order_id);

		if (!$paypal_order) {
			$json['error'] = $this->language->get('error_missing_order');
			$this->jsonOutput($json);
			return;
		}

		// The authorization_id is stored in capture_id field during AUTHORIZE flow
		$authorization_id = $paypal_order['capture_id'];

		$payload = [];

		if (!empty($note)) {
			$payload['note_to_payer'] = $note;
		}

		// Always pass amount for partial capture
		$payload['amount'] = [
			'value'         => number_format($amount, 2),
			'currency_code' => $paypal_order['currency_code'],
		];

		$response = $this->model_payment_pp_express->captureAuthorization($authorization_id, $payload);

		if ($response === false) {
			$json['error'] = $this->language->get('error_connection');
			$this->jsonOutput($json);
			return;
		}

		if (!empty($response['id']) && isset($response['status'])) {
			$capture_id = $response['id'];
			$status = $response['status'];

			$this->model_payment_pp_express->saveTransaction([
				'paypal_order_id'  => $paypal_order['paypal_order_id'],
				'pp_order_id'      => $paypal_order['pp_order_id'],
				'capture_id'       => $capture_id,
				'transaction_type' => 'CAPTURE',
				'status'           => $status,
				'amount'           => $amount,
				'currency_code'    => $paypal_order['currency_code'],
				'note'             => $note,
				'raw_response'     => json_encode($response),
			]);

			$this->model_payment_pp_express->updatePaypalOrderStatus($order_id, $status, $capture_id);

			$captured = $this->model_payment_pp_express->getTotalCaptured($paypal_order['paypal_order_id']);
			$refunded = $this->model_payment_pp_express->getTotalRefunded($paypal_order['paypal_order_id']);
			$remaining = max($paypal_order['total'] - $captured + $refunded, 0);

			$json['captured'] = number_format($captured, 2);
			$json['refunded'] = number_format($refunded, 2);
			$json['remaining'] = number_format($remaining, 2);
			$json['status'] = $status;
			$json['success'] = $this->language->get('text_success');
		} else {
			$json['error'] = $this->extractApiError($response);
		}

		$this->jsonOutput($json);
	}

	// -------------------------------------------------------------------------
	// Capture full authorized amount in one click
	// -------------------------------------------------------------------------
	public function doCaptureFull(): void {
		$json = [];

		$this->language->load('payment/pp_express');

		$order_id = (int)($this->request->post['order_id'] ?? 0);

		if (!$order_id) {
			$json['error'] = $this->language->get('error_missing_data');
			$this->jsonOutput($json);
			return;
		}

		$this->load->model('payment/pp_express');

		$paypal_order = $this->model_payment_pp_express->getPaypalOrderByOrderId($order_id);

		if (!$paypal_order) {
			$json['error'] = $this->language->get('error_missing_order');
			$this->jsonOutput($json);
			return;
		}

		// Empty payload = capture full authorized amount
		$response = $this->model_payment_pp_express->captureAuthorization($paypal_order['capture_id']);

		if ($response === false) {
			$json['error'] = $this->language->get('error_connection');
			$this->jsonOutput($json);
			return;
		}

		if (!empty($response['id']) && isset($response['status'])) {
			$capture_id = $response['id'];
			$status = $response['status'];
			$amount = (float)($response['amount']['value'] ?? $paypal_order['total']);

			$this->model_payment_pp_express->saveTransaction([
				'paypal_order_id'  => $paypal_order['paypal_order_id'],
				'pp_order_id'      => $paypal_order['pp_order_id'],
				'capture_id'       => $capture_id,
				'transaction_type' => 'CAPTURE',
				'status'           => $status,
				'amount'           => $amount,
				'currency_code'    => $paypal_order['currency_code'],
				'note'             => '',
				'raw_response'     => json_encode($response),
			]);

			$this->model_payment_pp_express->updatePaypalOrderStatus($order_id, $status, $capture_id);

			$captured = $this->model_payment_pp_express->getTotalCaptured($paypal_order['paypal_order_id']);
			$refunded = $this->model_payment_pp_express->getTotalRefunded($paypal_order['paypal_order_id']);

			$json['captured'] = number_format($captured, 2);
			$json['refunded'] = number_format($refunded, 2);
			$json['remaining'] = '0.00';
			$json['status'] = $status;
			$json['success'] = $this->language->get('text_success');
		} else {
			$json['error'] = $this->extractApiError($response);
		}

		$this->jsonOutput($json);
	}

	// -------------------------------------------------------------------------
	// Refund page (GET — renders the form)
	// -------------------------------------------------------------------------
	public function refund(): void {
		$this->language->load('payment/pp_express');

		$this->document->setTitle($this->language->get('heading_title'));

		$order_id = (int)($this->request->get['order_id'] ?? 0);
		$capture_id = $this->request->get['capture_id'] ?? '';

		$this->data['heading_title'] = $this->language->get('heading_title');

		$lang_keys = [
			'entry_capture_id', 'entry_refund_full', 'entry_amount',
			'entry_note', 'button_cancel', 'button_refund',
			'error_partial_amt', 'error_positive_amt', 'text_already_refunded'
		];

		foreach ($lang_keys as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		$this->data['token'] = $this->session->data['token'];
		$this->data['order_id'] = $order_id;
		$this->data['capture_id'] = $capture_id;

		// ── Error from session (redirect back on failure) ─────────────────────
		if (isset($this->session->data['error'])) {
			$this->data['error'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$this->data['error'] = '';
		}

		// ── Breadcrumbs ──────────────────────────────────────────────────────
		$this->data['breadcrumbs'] = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false,
			],
			[
				'text'      => $this->language->get('heading_order') . ' N&deg;' . $order_id,
				'href'      => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $order_id, 'SSL'),
				'separator' => ' :: ',
			],
			[
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('payment/pp_express/refund', 'token=' . $this->session->data['token'] . '&capture_id=' . urlencode($capture_id) . '&order_id=' . $order_id, 'SSL'),
				'separator' => ' :: ',
			],
		];

		$this->data['action'] = $this->url->link('payment/pp_express/doRefund', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $order_id, 'SSL');

		// ── Fetch capture details from our DB ─────────────────────────────────
		$this->load->model('payment/pp_express');

		$paypal_order = $this->model_payment_pp_express->getPaypalOrderByOrderId($order_id);

		if ($paypal_order) {
			$this->data['currency_code'] = $paypal_order['currency_code'];
			$this->data['amount_original'] = number_format((float)$paypal_order['total'], 2);
			$already_refunded = $this->model_payment_pp_express->getTotalRefundedByCaptureId($capture_id);
			$this->data['already_refunded'] = number_format($already_refunded, 2);
			$this->data['refund_available'] = number_format(max((float)$paypal_order['total'] - $already_refunded, 0), 2);
		} else {
			$this->data['currency_code'] = '';
			$this->data['amount_original'] = '0.00';
			$this->data['already_refunded'] = '0.00';
			$this->data['refund_available'] = '0.00';
			$this->data['error'] = $this->language->get('error_missing_order');
		}

		$this->template = 'payment/pp_express_refund.tpl';
		$this->children = ['common/header', 'common/footer'];

		$this->response->setOutput($this->render());
	}

	// -------------------------------------------------------------------------
	// Process refund (POST — JSON response)
	// -------------------------------------------------------------------------
	public function doRefund(): void {
		$json = [];

		$this->language->load('payment/pp_express');

		$capture_id = $this->request->post['capture_id'] ?? '';
		$refund_full = (int)($this->request->post['refund_full'] ?? 0);
		$amount = (float)($this->request->post['amount'] ?? 0);
		$note = $this->request->post['note'] ?? '';
		$order_id = (int)($this->request->post['order_id'] ?? 0);

		if (!$capture_id || !$order_id) {
			$json['error'] = $this->language->get('error_missing_data');
			$this->jsonOutput($json);
			return;
		}

		if (!$refund_full && $amount <= 0) {
			$json['error'] = $this->language->get('error_partial_amt');
			$this->jsonOutput($json);
			return;
		}

		$this->load->model('payment/pp_express');

		$paypal_order = $this->model_payment_pp_express->getPaypalOrderByOrderId($order_id);

		if (!$paypal_order) {
			$json['error'] = $this->language->get('error_missing_order');
			$this->jsonOutput($json);
			return;
		}

		// Build refund payload
		$payload = [];

		if (!$refund_full) {
			$payload['amount'] = [
				'value'         => number_format($amount, 2),
				'currency_code' => $paypal_order['currency_code'],
			];
		}

		if (!empty($note)) {
			$payload['note_to_payer'] = $note;
		}

		$response = $this->model_payment_pp_express->refundCapture($capture_id, $payload);

		if ($response === false) {
			$json['error'] = $this->language->get('error_connection');
			$this->jsonOutput($json);
			return;
		}

		if (!empty($response['id']) && isset($response['status'])) {
			$refund_amount = (float)($response['amount']['value'] ?? $amount);

			$this->model_payment_pp_express->saveTransaction([
				'paypal_order_id'  => $paypal_order['paypal_order_id'],
				'pp_order_id'      => $paypal_order['pp_order_id'],
				'capture_id'       => $capture_id,
				'transaction_type' => 'REFUND',
				'status'           => $response['status'],
				'amount'           => $refund_amount,
				'currency_code'    => $paypal_order['currency_code'],
				'note'             => $note,
				'raw_response'     => json_encode($response),
			]);

			$json['success'] = $this->language->get('text_success');
		} else {
			$json['error'] = $this->extractApiError($response);
		}

		$this->jsonOutput($json);
	}

	// -------------------------------------------------------------------------
	// Void authorization (AUTHORIZE mode only)
	// -------------------------------------------------------------------------
	public function doVoid(): void {
		$json = [];

		$this->language->load('payment/pp_express');

		$order_id = (int)($this->request->post['order_id'] ?? 0);

		if (!$order_id) {
			$json['error'] = $this->language->get('error_missing_data');
			$this->jsonOutput($json);
			return;
		}

		$this->load->model('payment/pp_express');

		$paypal_order = $this->model_payment_pp_express->getPaypalOrderByOrderId($order_id);

		if (!$paypal_order) {
			$json['error'] = $this->language->get('error_missing_order');
			$this->jsonOutput($json);
			return;
		}

		$authorization_id = $paypal_order['capture_id'];

		$response = $this->model_payment_pp_express->voidAuthorization($authorization_id);

		if ($response === false) {
			$json['error'] = $this->language->get('error_connection');
			$this->jsonOutput($json);
			return;
		}

		// PayPal returns HTTP 204 No Content on success — response array will be empty
		// The model returns an empty array (not false) on 204
		$this->model_payment_pp_express->saveTransaction([
			'paypal_order_id'  => $paypal_order['paypal_order_id'],
			'pp_order_id'      => $paypal_order['pp_order_id'],
			'capture_id'       => $authorization_id,
			'transaction_type' => 'VOID',
			'status'           => 'VOIDED',
			'amount'           => 0,
			'currency_code'    => $paypal_order['currency_code'],
			'note'             => '',
			'raw_response'     => json_encode($response),
		]);

		$this->model_payment_pp_express->updatePaypalOrderStatus($order_id, 'VOIDED');

		$json['status'] = 'VOIDED';
		$json['success'] = $this->language->get('text_success');

		$this->jsonOutput($json);
	}

	// -------------------------------------------------------------------------
	// Debug log helpers
	// -------------------------------------------------------------------------
	public function debugClear(): void {
		$this->language->load('payment/pp_express');

		$file = DIR_LOGS . self::DEBUG_LOG_FILE;
		$handle = fopen($file, 'w+');
		fclose($handle);
		clearstatcache();

		$this->session->data['success'] = $this->language->get('text_debug_clear_success');

		$this->redirect($this->url->link('payment/pp_express', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function debugDownload(): void {
		$file = DIR_LOGS . self::DEBUG_LOG_FILE;

		clearstatcache();

		if (!file_exists($file) || !is_file($file) || filesize($file) === 0) {
			$this->redirect($this->url->link('payment/pp_express', 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		if (headers_sent()) {
			exit('Error: Headers already sent!');
		}

		header('Content-Type: application/octet-stream');
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . str_replace(' ', '_', $this->config->get('config_name')) . '_' . date('Y-m-d_H-i-s') . '_' . self::DEBUG_LOG_FILE);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));

		readfile($file);
		exit();
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Set JSON Content-Type header and output encoded response.
	 */
	private function jsonOutput(array $json): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Extract a human-readable error message from a PayPal v2 error response.
	 * v2 errors follow: { "name": "...", "message": "...", "details": [...] }
	 */
	private function extractApiError(array $response): string {
		$this->language->load('payment/pp_express');

		if (!empty($response['message'])) {
			$msg = $response['message'];

			if (!empty($response['details']) && is_array($response['details'])) {
				$details = array_map(
					fn($d) => ($d['field'] ?? '') . ' ' . ($d['description'] ?? ''),
					$response['details']
				);
				$msg .= '<br>' . implode('<br>', array_filter($details));
			}

			return $msg;
		}

		return $this->language->get('error_general');
	}
}
