<?php
/**
 * Class ControllerCheckoutCheckoutConfirm
 *
 * Silent confirm controller for the One Page Checkout.
 * Called via AJAX after the checkout form validates.
 * Builds totals, calls addOrder(), then routes by gateway type.
 *
 * Three gateway types:
 *
 *   $interactive_gateways  — Payment confirmed browser-side BEFORE this runs.
 *                            (Stripe, Klarna, SagePay…)
 *                            → verify server-side → redirect to success
 *
 *   $redirect_gateways     — Order created first, then customer sent to
 *                            external payment page. IPN handles status async.
 *                            (PayPal Standard, PayPal Express…)
 *                            → redirect to payment/{code}
 *
 *   direct/silent          — All payment logic is server-side, no browser step.
 *                            (Free Checkout, COD, Invoice…)
 *                            → getChild confirm → redirect to success
 *
 * Adding a new gateway: add its code to the correct array. That's it.
 *
 * @package NivoCart
 */
class ControllerCheckoutCheckoutConfirm extends Controller {
	/** Error array Placeholder */

	private $interactive_gateways = [
		'stripe_payments',
		// 'klarna',
	];

	private $redirect_gateways = [
		'pp_standard',
		'sagepay',
		// 'pp_express',
	];

	public function index() {
		// Guard — must have session data from checkout
		if (empty($this->session->data['one_page_order']) || !isset($this->session->data['payment_method'])) {
			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		// Guard — cart must still be valid
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) ||
			(!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->redirect($this->url->link('checkout/cart', '', 'SSL'));
		}

		// Guard — shipping must be set if required
		if ($this->cart->hasShipping() && empty($this->session->data['shipping_method'])) {
			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$payment_code = $this->session->data['payment_method']['code'];

		// Guard — interactive gateway must have a verified intent in session
		if (in_array($payment_code, $this->interactive_gateways)) {
			if (empty($this->session->data['stripe_payment_intent_id'])) {
				$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
			}
		}

		$order_session = $this->session->data['one_page_order'];

		// ----------------------------------------------------------------
		// Totals
		// ----------------------------------------------------------------
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

		usort($total_data, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);

		// ----------------------------------------------------------------
		// Build order data
		// ----------------------------------------------------------------
		$data = [];

		$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$data['store_id'] = $this->config->get('config_store_id');
		$data['store_name'] = $this->config->get('config_name');
		$data['store_url'] = $data['store_id'] ? $this->config->get('config_url') : HTTP_SERVER;

		// Customer
		if ($this->customer->isLogged()) {
			$data['customer_id'] = $this->customer->getId();
			$data['customer_group_id'] = $this->customer->getCustomerGroupId();
			$data['firstname'] = $this->customer->getFirstName();
			$data['lastname'] = $this->customer->getLastName();
			$data['email'] = $this->customer->getEmail();
			$data['telephone'] = $this->customer->getTelephone();
			$data['gender'] = $this->customer->getGender();
			$data['date_of_birth'] = $this->customer->getDateOfBirth();
		} else {
			$data['customer_id'] = 0;
			$data['customer_group_id'] = $order_session['customer_group_id'] ?? $this->config->get('config_customer_group_id');
			$data['firstname'] = $order_session['firstname'] ?? '';
			$data['lastname'] = $order_session['lastname'] ?? '';
			$data['email'] = $order_session['email'] ?? '';
			$data['telephone'] = $order_session['telephone'] ?? '';
			$data['gender'] = $order_session['gender'] ?? 1;
			$data['date_of_birth'] = $order_session['date_of_birth'] ?? '1970-01-01';
		}

		// Payment address
		$data['payment_firstname'] = $order_session['payment_firstname'] ?? $data['firstname'];
		$data['payment_lastname'] = $order_session['payment_lastname'] ?? $data['lastname'];
		$data['payment_company'] = $order_session['payment_company'] ?? '';
		$data['payment_company_id'] = $order_session['payment_company_id'] ?? '';
		$data['payment_tax_id'] = $order_session['payment_tax_id'] ?? '';
		$data['payment_address_1'] = $order_session['payment_address_1'] ?? '';
		$data['payment_address_2'] = $order_session['payment_address_2'] ?? '';
		$data['payment_city'] = $order_session['payment_city'] ?? '';
		$data['payment_postcode'] = $order_session['payment_postcode'] ?? '';
		$data['payment_zone'] = $order_session['payment_zone'] ?? '';
		$data['payment_zone_id'] = $order_session['payment_zone_id'] ?? '';
		$data['payment_country'] = $order_session['payment_country'] ?? '';
		$data['payment_country_id'] = $order_session['payment_country_id'] ?? '';
		$data['payment_address_format'] = '';
		$data['payment_method'] = $order_session['payment_method'] ?? '';
		$data['payment_code'] = $order_session['payment_code'] ?? '';

		// Shipping address
		$data['shipping_firstname'] = $order_session['shipping_firstname'] ?? $data['firstname'];
		$data['shipping_lastname'] = $order_session['shipping_lastname'] ?? $data['lastname'];
		$data['shipping_company'] = $order_session['shipping_company'] ?? '';
		$data['shipping_address_1'] = $order_session['shipping_address_1'] ?? '';
		$data['shipping_address_2'] = $order_session['shipping_address_2'] ?? '';
		$data['shipping_city'] = $order_session['shipping_city'] ?? '';
		$data['shipping_postcode'] = $order_session['shipping_postcode'] ?? '';
		$data['shipping_zone'] = $order_session['shipping_zone'] ?? '';
		$data['shipping_zone_id'] = $order_session['shipping_zone_id'] ?? '';
		$data['shipping_country'] = $order_session['shipping_country'] ?? '';
		$data['shipping_country_id'] = $order_session['shipping_country_id'] ?? '';
		$data['shipping_address_format'] = '';
		$data['shipping_method'] = $order_session['shipping_method'] ?? '';
		$data['shipping_code'] = $order_session['shipping_code'] ?? '';

		// Products
		$product_data = [];

		foreach ($this->cart->getProducts() as $product) {
			$option_data = [];

			foreach ($product['option'] as $option) {
				$value = ($option['type'] !== 'file') ? $option['option_value'] : $this->encryption->decrypt($option['option_value']);

				$option_data[] = [
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $value,
					'type'                    => $option['type'],
				];
			}

			$product_data[] = [
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'download'   => $product['download'],
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $product['price'],
				'cost'       => $product['cost'],
				'total'      => $product['total'],
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward'],
			];
		}

		// Gift vouchers
		$voucher_data = [];

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$voucher_data[] = [
					'description'      => $voucher['description'],
					'code'             => substr(md5(mt_rand()), 0, 10),
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'],
					'message'          => $voucher['message'],
					'amount'           => $voucher['amount'],
				];
			}
		}

		$data['products'] = $product_data;
		$data['vouchers'] = $voucher_data;
		$data['totals'] = $total_data;
		$data['comment'] = $order_session['comment'] ?? '';
		$data['total'] = $total;

		// Affiliate
		if (isset($this->request->cookie['tracking'])) {
			$this->load->model('affiliate/affiliate');

			$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);

			$subtotal = $this->cart->getSubTotal();

			if ($affiliate_info) {
				$data['affiliate_id'] = $affiliate_info['affiliate_id'];
				$data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
			} else {
				$data['affiliate_id'] = 0;
				$data['commission'] = 0;
			}
		} else {
			$data['affiliate_id'] = 0;
			$data['commission'] = 0;
		}

		$data['language_id'] = $this->config->get('config_language_id');

		// Currency
		$this->load->model('localisation/currency');

		$currency_info = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));

		if ($currency_info) {
			$data['currency_id'] = $currency_info['currency_id'];
			$data['currency_code'] = $currency_info['code'];
			$data['currency_value'] = $currency_info['value'];
		} else {
			$data['currency_id'] = 0;
			$data['currency_code'] = $this->config->get('config_currency');
			$data['currency_value'] = 1.00000000;
		}

		// IP / user agent
		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'] ?? ($this->request->server['HTTP_CLIENT_IP'] ?? '');
		$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'] ?? '';
		$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'] ?? '';

		// Gateway-type-specific order status before addOrder()
		if (in_array($payment_code, $this->interactive_gateways)) {
			// Interactive: gateway sets its own confirmed status
			$data['order_status_id'] = (int)$this->config->get($payment_code . '_order_status_id');
		} elseif (in_array($payment_code, $this->redirect_gateways)) {
			// Redirect: order waits at Pending until IPN fires
			$data['order_status_id'] = 1; // Pending
		}
		// Direct/silent: addOrder() uses config_order_status_id by default

		// ----------------------------------------------------------------
		// Save the order — cart is cleared inside addOrder()
		// ----------------------------------------------------------------
		$this->load->model('checkout/order');

		$this->session->data['order_id'] = $this->model_checkout_order->addOrder($data);

		// Clean up checkout session data before any redirect
		unset($this->session->data['one_page_order']);

		// ----------------------------------------------------------------
		// Route by gateway type
		// ----------------------------------------------------------------
		if (in_array($payment_code, $this->interactive_gateways)) {
			$this->_confirmInteractivePayment($payment_code);
			$this->redirect($this->url->link('checkout/success', '', 'SSL'));

		} elseif (in_array($payment_code, $this->redirect_gateways)) {
			// Redirect to standalone payment page — order_id is now in session
			$this->redirect($this->url->link('payment/' . $payment_code, '', 'SSL'));

		} else {
			// Direct/silent gateway
			$this->getChild('payment/' . $payment_code . '/confirm');
			$this->redirect($this->url->link('checkout/success', '', 'SSL'));
		}
	}

	// -------------------------------------------------------------------------
	// Interactive gateway server-side verification
	// Add a case here for each new interactive gateway.
	// -------------------------------------------------------------------------
	private function _confirmInteractivePayment(string $payment_code): void {
		switch ($payment_code) {

			case 'stripe_payments':
				require_once(DIR_SYSTEM . 'vendor/stripe/stripe.php');

				$stripe = new Stripe(
					$this->config->get('stripe_payments_secret_key'),
					$this->config->get('stripe_payments_publishable_key'),
					$this->config->get('stripe_payments_webhook_secret')
				);

				$intent_id = $this->session->data['stripe_payment_intent_id'] ?? '';

				try {
					$stripe->verifyPayment($intent_id);
				} catch (RuntimeException $e) {
					$this->session->data['error'] = 'Payment could not be verified. Please try again.';
					$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
				}

				$this->model_checkout_order->confirm(
					$this->session->data['order_id'],
					$this->config->get('stripe_payments_order_status_id')
				);

				unset($this->session->data['stripe_payment_intent_id']);
				break;
		}
	}
}
