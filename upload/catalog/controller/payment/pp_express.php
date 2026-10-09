<?php
/**
 * Class ControllerPaymentPpExpress
 *
 * Catalog controller for PayPal Express — Orders v2 REST API.
 *
 * Flow:
 *   JS SDK pop-up → createOrder (AJAX) → customer approves in PayPal pop-up
 *   → captureOrder (AJAX) → stores pp_order_id in session
 *   → checkout_confirm → _confirmInteractivePayment('pp_express')
 *   → NivoCart order confirmed
 *
 * Endpoints:
 *   POST payment/pp_express/createOrder   → returns {id: pp_order_id}
 *   POST payment/pp_express/captureOrder  → captures, saves to DB, stores session key
 *   POST payment/pp_express/cancelOrder   → cleans up session on customer cancel
 *   POST payment/pp_express/webhook       → PayPal async event handler
 *
 * @package NivoCart
 */
class ControllerPaymentPpExpress extends Controller {
	// -------------------------------------------------------------------------
	// Create Order
	// Called by JS SDK createOrder() callback.
	// Builds the Orders v2 payload from the current session/cart and returns
	// the PayPal order ID to the JS SDK.
	// -------------------------------------------------------------------------

	public function createOrder(): void {
		$json = [];

		$this->language->load('payment/pp_express');

		$this->load->model('payment/pp_express');

		// The NivoCart order does not exist yet in the one page checkout: it is
		// created by checkout_confirm AFTER the customer has paid. Everything here
		// is therefore built from the cart and the session totals only.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['error'] = $this->language->get('error_session');
			$this->jsonOutput($json);
			return;
		}

		$currency_code = $this->config->get('pp_express_currency') ?: $this->config->get('config_currency');
		$intent = strtoupper($this->config->get('pp_express_transaction_mode') ?: 'CAPTURE');

		// ── Build purchase_units items from cart ──────────────────────────────
		$items = [];
		$item_total = 0.00;

		foreach ($this->cart->getProducts() as $product) {
			$unit_price = round((float)$this->currency->convert($product['price'], $this->config->get('config_currency'), $currency_code), 2, PHP_ROUND_HALF_UP);
			$item_total += $unit_price * (int)$product['quantity'];

			$name = $product['name'];

			if (!empty($product['option'])) {
				$opts = [];
				foreach ($product['option'] as $opt) {
					if ($opt['type'] !== 'file') {
						$opts[] = $opt['name'] . ': ' . mb_substr($opt['option_value'], 0, 20);
					}
				}
				if ($opts) {
					$name .= ' (' . implode(', ', $opts) . ')';
				}
			}

			$items[] = [
				'name'        => mb_substr($name, 0, 127),
				'sku'         => mb_substr($product['model'], 0, 127),
				'unit_amount' => [
					'currency_code' => $currency_code,
					'value'         => number_format($unit_price, 2, '.', ''),
				],
				'quantity' => (string)(int)$product['quantity'],
			];
		}

		// Gift vouchers as line items
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$unit_price = round((float)$this->currency->convert($voucher['amount'], $this->config->get('config_currency'), $currency_code), 2, PHP_ROUND_HALF_UP);
				$item_total += $unit_price;

				$items[] = [
					'name'        => mb_substr($voucher['description'], 0, 127),
					'sku'         => 'VOUCHER',
					'unit_amount' => [
						'currency_code' => $currency_code,
						'value'         => number_format($unit_price, 2, '.', ''),
					],
					'quantity' => '1',
				];
			}
		}

		// ── Totals: shipping + tax + discounts ────────────────────────────────
		// We pass item_total + handling (tax, fees) as breakdown.
		// PayPal rejects a tax_total that does not match per-item tax, so tax and
		// any rounding difference are carried in handling instead.
		$cart_totals = $this->getCartTotals();

		$order_total = round((float)$this->currency->convert($cart_totals['total'], $this->config->get('config_currency'), $currency_code), 2, PHP_ROUND_HALF_UP);
		$item_total = round($item_total, 2, PHP_ROUND_HALF_UP);

		$shipping_total = 0.00;
		$discount_total = 0.00;

		foreach ($cart_totals['totals'] as $total_row) {
			if ($total_row['code'] === 'shipping') {
				$shipping_total = round((float)$this->currency->convert($total_row['value'], $this->config->get('config_currency'), $currency_code), 2, PHP_ROUND_HALF_UP);
			}

			if (in_array($total_row['code'], ['coupon', 'voucher', 'reward'])) {
				$discount_total += round((float)abs($this->currency->convert($total_row['value'], $this->config->get('config_currency'), $currency_code)), 2, PHP_ROUND_HALF_UP);
			}
		}

		// handling = order_total - item_total - shipping + discount
		// A negative difference cannot be sent as handling, so it becomes discount.
		$handling = round($order_total - $item_total - $shipping_total + $discount_total, 2, PHP_ROUND_HALF_UP);

		if ($handling < 0) {
			$discount_total = round($discount_total + abs($handling), 2, PHP_ROUND_HALF_UP);
			$handling = 0.00;
		}

		// ── Assemble purchase_unit ────────────────────────────────────────────
		$breakdown = [
			'item_total' => [
				'currency_code' => $currency_code,
				'value'         => number_format($item_total, 2, '.', ''),
			],
			'shipping' => [
				'currency_code' => $currency_code,
				'value'         => number_format($shipping_total, 2, '.', ''),
			],
			'tax_total' => [
				'currency_code' => $currency_code,
				'value'         => '0.00',
			],
		];

		if ($discount_total > 0) {
			$breakdown['discount'] = [
				'currency_code' => $currency_code,
				'value'         => number_format($discount_total, 2, '.', ''),
			];
		}

		if ($handling > 0) {
			$breakdown['handling'] = [
				'currency_code' => $currency_code,
				'value'         => number_format($handling, 2, '.', ''),
			];
		}

		$purchase_unit = [
			'reference_id' => 'default',
			'amount'       => [
				'currency_code' => $currency_code,
				'value'         => number_format($order_total, 2, '.', ''),
				'breakdown'     => $breakdown,
			],
			'items' => $items,
		];

		// ── Full payload ──────────────────────────────────────────────────────
		$payload = [
			'intent'          => $intent,
			'purchase_units'  => [$purchase_unit],
			'payment_source'  => [
				'paypal' => [
					'experience_context' => [
						'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
						'brand_name'                => $this->config->get('config_name'),
						'locale'                    => 'en-GB',
						'user_action'               => 'PAY_NOW',
						'shipping_preference'       => $this->cart->hasShipping() ? 'GET_FROM_FILE' : 'NO_SHIPPING',
					],
				],
			],
		];

		$response = $this->model_payment_pp_express->createPayPalOrder($payload);

		if (!$response || empty($response['id'])) {
			$json['error'] = $this->extractApiError(is_array($response) ? $response : []);
			$this->jsonOutput($json);
			return;
		}

		// Remember the PayPal order for captureOrder and checkout_confirm
		$this->session->data['pp_express_order_id'] = $response['id'];
		unset($this->session->data['pp_express_capture']);

		$json['id'] = $response['id'];

		$this->jsonOutput($json);
	}

	// -------------------------------------------------------------------------
	// Capture Order
	// Called by JS SDK onApprove() callback after customer approves in pop-up.
	// Captures (or authorizes) the payment server-side and holds the result in
	// the session. checkout_confirm saves it to the database once the NivoCart
	// order exists, then finalizes the order.
	// -------------------------------------------------------------------------

	public function captureOrder(): void {
		$json = [];

		$this->language->load('payment/pp_express');
		$this->load->model('payment/pp_express');

		$pp_order_id = $this->session->data['pp_express_order_id'] ?? '';
		$posted_id = $this->request->post['pp_order_id'] ?? '';

		if (!$pp_order_id || ($posted_id !== '' && $posted_id !== $pp_order_id)) {
			$json['error'] = $this->language->get('error_session');
			$this->jsonOutput($json);
			return;
		}

		// Already captured for this PayPal order (e.g. double click): do not capture twice
		if (!empty($this->session->data['pp_express_capture']['pp_order_id']) && $this->session->data['pp_express_capture']['pp_order_id'] === $pp_order_id) {
			$json['success'] = true;
			$json['status'] = $this->session->data['pp_express_capture']['status'];
			$this->jsonOutput($json);
			return;
		}

		$intent = strtoupper($this->config->get('pp_express_transaction_mode') ?: 'CAPTURE');

		if ($intent === 'AUTHORIZE') {
			$response = $this->model_payment_pp_express->authorizePayPalOrder($pp_order_id);
		} else {
			$response = $this->model_payment_pp_express->capturePayPalOrder($pp_order_id);
		}

		if (!$response || empty($response['status'])) {
			$json['error'] = $this->extractApiError(is_array($response) ? $response : []);
			$this->jsonOutput($json);
			return;
		}

		// Extract capture/authorization ID and amount from response
		$capture_id = '';
		$amount = 0.00;
		$currency_code = $this->config->get('pp_express_currency') ?: $this->config->get('config_currency');

		if ($intent === 'AUTHORIZE') {
			$auth = $response['purchase_units'][0]['payments']['authorizations'][0] ?? [];
			$capture_id = $auth['id'] ?? '';
			$amount = (float)($auth['amount']['value'] ?? 0);
			$currency_code = $auth['amount']['currency_code'] ?? $currency_code;
			$status = $auth['status'] ?? $response['status'];
		} else {
			$capture = $response['purchase_units'][0]['payments']['captures'][0] ?? [];
			$capture_id = $capture['id'] ?? '';
			$amount = (float)($capture['amount']['value'] ?? 0);
			$currency_code = $capture['amount']['currency_code'] ?? $currency_code;
			$status = $capture['status'] ?? $response['status'];
		}

		if (!$capture_id || !in_array($status, ['COMPLETED', 'PENDING', 'APPROVED', 'CREATED'], true)) {
			$json['error'] = $this->language->get('error_general');
			$this->jsonOutput($json);
			return;
		}

		// Hold in session — checkout_confirm writes it to the DB against the real order_id
		$this->session->data['pp_express_capture'] = [
			'pp_order_id'   => $pp_order_id,
			'intent'        => $intent,
			'status'        => $status,
			'capture_id'    => $capture_id,
			'amount'        => $amount,
			'currency_code' => $currency_code,
			'raw_response'  => json_encode($response),
		];

		$json['success'] = true;
		$json['status'] = $status;

		$this->jsonOutput($json);
	}

	// -------------------------------------------------------------------------
	// Cancel Order
	// Called by JS SDK onCancel() callback.
	// -------------------------------------------------------------------------

	public function cancelOrder(): void {
		$this->language->load('payment/pp_express');

		unset($this->session->data['pp_express_order_id'], $this->session->data['pp_express_capture']);

		$json['success'] = false;
		$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');

		$this->jsonOutput($json);
	}

	// -------------------------------------------------------------------------
	// Webhook
	// POST from PayPal — verifies signature, processes event.
	// -------------------------------------------------------------------------

	public function webhook(): void {
		$this->load->model('payment/pp_express');

		$raw_body = file_get_contents('php://input');

		if (empty($raw_body)) {
			http_response_code(400);
			return;
		}

		// Extract PayPal signature headers from $_SERVER
		$headers = [];

		$header_map = [
			'HTTP_PAYPAL_AUTH_ALGO'         => 'PAYPAL-AUTH-ALGO',
			'HTTP_PAYPAL_CERT_URL'          => 'PAYPAL-CERT-URL',
			'HTTP_PAYPAL_TRANSMISSION_ID'   => 'PAYPAL-TRANSMISSION-ID',
			'HTTP_PAYPAL_TRANSMISSION_SIG'  => 'PAYPAL-TRANSMISSION-SIG',
			'HTTP_PAYPAL_TRANSMISSION_TIME' => 'PAYPAL-TRANSMISSION-TIME',
		];

		foreach ($header_map as $server_key => $paypal_key) {
			if (!empty($this->request->server[$server_key])) {
				$headers[$paypal_key] = $this->request->server[$server_key];
			}
		}

		$sandbox = (bool)$this->config->get('pp_express_sandbox');

		$webhook_id = $sandbox ? $this->config->get('pp_express_sandbox_webhook_id') : $this->config->get('pp_express_webhook_id');

		if ($webhook_id && !$this->model_payment_pp_express->verifyWebhookSignature($headers, $raw_body, $webhook_id)) {
			$this->model_payment_pp_express->log(['headers' => $headers], 'webhook signature failed', true);
			http_response_code(400);
			return;
		}

		$event = json_decode($raw_body, true);

		if (empty($event['event_type'])) {
			http_response_code(400);
			return;
		}

		$this->model_payment_pp_express->log($event['event_type'], 'webhook received');

		$order_id = $this->model_payment_pp_express->processWebhookEvent($event);

		// Always return 200 to PayPal — even unhandled events should not retry
		http_response_code(200);
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Cart totals through the enabled total extensions — same calculation as
	 * checkout_confirm, needed here because no order exists yet.
	 *
	 * @return array{total: float, totals: array}
	 */
	private function getCartTotals(): array {
		$total_data = [];
		$total = 0.0;
		$taxes = $this->cart->getTaxes();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('total');

		usort($results, fn($a, $b) => $this->config->get($a['code'] . '_sort_order') <=> $this->config->get($b['code'] . '_sort_order'));

		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('total/' . $result['code']);

				$model = $this->{'model_total_' . $result['code']};
				$contribution = $model->getTotal($taxes, $total);

				$total_data = array_merge($total_data, $contribution['total_data']);
				$total += $contribution['total'];
				$taxes += $contribution['taxes'];
			}
		}

		return ['total' => $total, 'totals' => $total_data];
	}

	/**
	 * Extract a readable message from a PayPal v2 error response.
	 */
	private function extractApiError(array $response): string {
		// PayPal's own wording is only shown while testing (sandbox or debug on)
		if (!empty($response['message']) && ($this->config->get('pp_express_sandbox') || $this->config->get('pp_express_debug'))) {
			$msg = $response['message'];

			if (!empty($response['details'][0]['description'])) {
				$msg .= ' ' . $response['details'][0]['description'];
			}

			return $msg;
		}

		return $this->language->get('error_connection');
	}

	private function jsonOutput(array $json): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
