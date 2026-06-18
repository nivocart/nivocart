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
	// Add future gateways below following the same pattern:
	//
	// private function getWidgetData_pp_express(float $total, string $currency_code): array {
	//     return [ ... ];
	// }
	//
	// private function getWidgetData_klarna(float $total, string $currency_code): array {
	//     return [ ... ];
	// }
	// =========================================================================
}
