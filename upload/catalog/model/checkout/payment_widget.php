<?php
/**
 * Class ModelCheckoutPaymentWidget
 *
 * Prepares the widget data blob passed to checkout.tpl for each
 * active payment gateway. Each public method returns an array that
 * is JSON-encoded into window.NIVOCART_PAYMENT_DATA in the template.
 *
 * Adding a new gateway:
 *   1. Add a public getWidgetData_{code}() method here.
 *   2. Add the code to the appropriate array in checkout_confirm.php.
 *   3. Create catalog/view/theme/default/javascript/payment/{code}.js.
 *
 * @package NivoCart
 */
class ModelCheckoutPaymentWidget extends Model {
	/**
	 * Entry point called by checkout.php.
	 * Loops active payment methods and calls the appropriate
	 * per-gateway method if one exists.
	 *
	 * @param  array  $payment_methods  Result of payment method loop in checkout.php
	 * @param  float  $total            Calculated order total
	 * @param  string $currency_code    Active currency code
	 * @return array  Keyed by gateway code
	 */
	public function getWidgetData(array $payment_methods, float $total, string $currency_code): array {
		$data = [];

		foreach ($payment_methods as $code => $method) {
			$method_name = 'getWidgetData_' . $code;

			if (method_exists($this, $method_name)) {
				$data[$code] = $this->$method_name($total, $currency_code);
			}
		}

		return $data;
	}

	// =========================================================================
	// PP Standard
	// =========================================================================

	private function getWidgetData_pp_standard(float $total, string $currency_code): array {
		$products = [];
		$pp_subtotal = 0;

		foreach ($this->cart->getProducts() as $product) {
			$price = (float)$this->currency->format($product['price'], $currency_code, false, false);
			$pp_subtotal += $price * $product['quantity'];

			$option_data = [];

			foreach ($product['option'] as $option) {
				if ($option['type'] !== 'file') {
					$value = $option['option_value'];
				} else {
					$this->load->model('tool/upload');

					$upload_info = $this->model_tool_upload->getUploadByCode($option['option_value']);

					$value = $upload_info ? $upload_info['name'] : '';
				}

				$option_data[] = [
					'name'  => mb_strlen($option['name'], 'UTF-8') > 64 ? mb_substr($option['name'], 0, 62, 'UTF-8') . '..' : $option['name'],
					'value' => mb_strlen($value, 'UTF-8') > 20 ? mb_substr($value, 0, 18, 'UTF-8') . '..' : $value
				];
			}

			$products[] = [
				'name'     => htmlspecialchars($product['name'],  ENT_QUOTES, 'UTF-8'),
				'model'    => htmlspecialchars($product['model'], ENT_QUOTES, 'UTF-8'),
				'price'    => $price,
				'quantity' => (int)$product['quantity'],
				'weight'   => (float)$product['weight'],
				'option'   => $option_data
			];
		}

		$remainder = (float)$this->currency->format($total - $pp_subtotal, $currency_code, false, false);
		$discount = 0.0;

		if ($remainder > 0) {
			// Shipping, taxes, surcharges
			$products[] = [
				'name'     => 'Shipping & charges',
				'model'    => '',
				'price'    => $remainder,
				'quantity' => 1,
				'weight'   => 0,
				'option'   => []
			];
		} elseif ($remainder < 0) {
			$discount = abs($remainder);
		}

		return [
			'action'        => $this->config->get('pp_standard_test') ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr',
			'business'      => $this->config->get('pp_standard_email'),
			'testmode'      => (bool)$this->config->get('pp_standard_test'),
			'paymentaction' => $this->config->get('pp_standard_transaction') ? 'sale' : 'authorization',
			'currency'      => $currency_code,
			'lc'            => $this->session->data['language'] ?? 'en',
			'products'      => $products,
			'discount'      => $discount,
			'notify_url'    => $this->url->link('payment/pp_standard/callback', '', 'SSL'),
			'return_url'    => $this->url->link('checkout/success', '', 'SSL'),
			'cancel_url'    => $this->url->link('checkout/checkout', '', 'SSL'),
		];
	}

	// =========================================================================
	// Sagepay Payments
	// =========================================================================

	private function getWidgetData_sagepay(float $total, string $currency_code): array {
		return [
			'testmode' => $this->config->get('sagepay_test') !== 'live',
		];
	}

	// =========================================================================
	// Stripe Payments
	// =========================================================================

	private function getWidgetData_stripe_payments(float $total, string $currency_code): array {
		return [
			'publishable_key' => $this->config->get('stripe_payments_publishable_key'),
			'intent_url'      => 'index.php?route=payment/stripe_payments/intentCreate',
			'store_url'       => 'index.php?route=payment/stripe_payments/storeIntent',
			'currency'        => $currency_code,
			'total'           => $total
		];
	}

	// =========================================================================
	// Klarna Payments
	// =========================================================================

	private function getWidgetData_klarna(float $total, string $currency_code): array {
		// Resolve the payment country from session — same approach as
		// ControllerPaymentKlarna::_resolvePaymentCountry(). We need the
		// country at widget-data-build time (checkout page load) to verify
		// Klarna is actually available before handing any data to the JS.
		$order_session = $this->session->data['one_page_order'] ?? [];

		// Prefer iso_code_2 if already in session, otherwise resolve from
		// country_id via DB — consistent with how the catalog controller
		// and ModelCheckoutOrder::getOrder() both derive it.
		if (!empty($order_session['payment_iso_code_2'])) {
			$country_code_2 = strtoupper($order_session['payment_iso_code_2']);
		} elseif (!empty($order_session['payment_country_id'])) {
			$result = $this->db->query("SELECT iso_code_2 FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_session['payment_country_id'] . "' LIMIT 1");
			$country_code_2 = $result->num_rows ? strtoupper($result->row['iso_code_2']) : '';
		} else {
			$country_code_2 = '';
		}

		// If we can't determine the country, or Klarna isn't configured/
		// enabled for it, return empty — gateway_loader.js will receive no
		// data for 'klarna' and won't show the widget div.
		if ($country_code_2 === '') {
			return [];
		}

		$this->load->model('payment/klarna');

		if (!$this->model_payment_klarna->isAvailable($country_code_2)) {
			return [];
		}

		return [
			'session_url' => 'index.php?route=payment/klarna/sessionCreate',
			'update_url' => 'index.php?route=payment/klarna/sessionUpdate',
			'store_url' => 'index.php?route=payment/klarna/storeAuthorization',
		];
	}

	// =========================================================================
	// PP Express
	// =========================================================================

	public function getWidgetData_pp_express(array $cart, float $total): array {
		$sandbox = (bool)$this->config->get('pp_express_sandbox');

		return [
			'client_id'         => $sandbox ? $this->config->get('pp_express_sandbox_client_id') : $this->config->get('pp_express_client_id'),
			'currency'          => $this->config->get('pp_express_currency') ?: $this->config->get('config_currency'),
			'intent'            => strtolower($this->config->get('pp_express_transaction_mode') ?: 'capture'),
			'pay_later'         => (bool)$this->config->get('pp_express_pay_later'),
			'sandbox'           => $sandbox,
			'url_create_order'  => $this->url->link('payment/pp_express/createOrder', '', 'SSL'),
			'url_capture_order' => $this->url->link('payment/pp_express/captureOrder', '', 'SSL'),
			'url_cancel_order'  => $this->url->link('payment/pp_express/cancelOrder', '', 'SSL'),
		];
	}

	// =========================================================================
	// Bank Transfer
	// =========================================================================

	private function getWidgetData_bank_transfer(float $total, string $currency_code): array {
		$this->language->load('payment/bank_transfer');

		return [
			'heading'      => $this->language->get('text_instruction'),
			'description'  => $this->language->get('text_description'),
			'bank_details' => nl2br($this->config->get('bank_transfer_bank_' . $this->config->get('config_language_id'))),
			'payment_note' => $this->language->get('text_payment'),
		];
	}

	// =========================================================================
	// Cheque / Money Order
	// =========================================================================

	private function getWidgetData_cheque(float $total, string $currency_code): array {
		$this->language->load('payment/cheque');

		return [
			'heading'       => $this->language->get('text_instruction'),
			'payable_label' => $this->language->get('text_payable'),
			'payable_to'    => (string)$this->config->get('cheque_payable'),
			'address_label' => $this->language->get('text_address'),
			'address'       => nl2br($this->config->get('config_address')),
			'payment_note'  => $this->language->get('text_payment'),
		];
	}

	// =========================================================================
	// Add future gateways below following the same pattern:
	//
	// private function getWidgetData_klarna(float $total, string $currency_code): array {
	//     return [ ... ];
	// }
	// =========================================================================
}
