<?php
/**
 * Class ControllerPaymentPpStandard
 *
 * Two responsibilities:
 *
 *   index()    — Standalone redirect page. Called by checkout_confirm after
 *                the order has been created. Renders a full page (using
 *                header_payment / footer_payment) with the PayPal button.
 *                The customer clicks the button; JS builds the PayPal form
 *                and submits it, redirecting the browser to PayPal.
 *
 *   callback() — IPN handler. Called asynchronously by PayPal after payment.
 *                Validates the IPN, maps payment_status to an order status,
 *                and calls confirm() or update() on the order model.
 *
 * @package NivoCart
 */
class ControllerPaymentPpStandard extends Controller {
	/** Error array Placeholder */

	public function index() {
		// Order must exist in session — checkout_confirm sets this before redirect
		if (empty($this->session->data['order_id'])) {
			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$this->language->load('payment/pp_standard');

		$this->document->setTitle($this->language->get('text_title'));
		$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/stylesheet-paypal.css');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (!$order_info) {
			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$action = $this->config->get('pp_standard_test') ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';

		// Build product line items
		$this->load->model('checkout/payment_widget');

		$currency_code = $order_info['currency_code'];
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
				'name'     => htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'),
				'model'    => htmlspecialchars($product['model'], ENT_QUOTES, 'UTF-8'),
				'price'    => $price,
				'quantity' => (int)$product['quantity'],
				'weight'   => (float)$product['weight'],
				'option'   => $option_data
			];
		}

		$remainder = (float)$this->currency->format(
			$order_info['total'] - $pp_subtotal, $currency_code, false, false
		);

		$discount = 0.0;

		if ($remainder > 0) {
			$products[] = [
				'name' => 'Shipping & charges', 'model' => '',
				'price' => $remainder, 'quantity' => 1, 'weight' => 0, 'option' => []
			];
		} elseif ($remainder < 0) {
			$discount = abs($remainder);
		}

		// Pass all data as data-* attributes — JS reads them and builds the form
		$this->data['pp_data'] = [
			'action'        => $action,
			'business'      => $this->config->get('pp_standard_email'),
			'currency'      => $currency_code,
			'paymentaction' => $this->config->get('pp_standard_transaction') ? 'sale' : 'authorization',
			'lc'            => $this->session->data['language'] ?? 'en',
			'invoice'       => $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8'),
			'custom'        => (int)$this->session->data['order_id'],
			'first_name'    => html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8'),
			'last_name'     => html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8'),
			'address1'      => html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8'),
			'address2'      => html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8'),
			'city'          => html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8'),
			'zip'           => html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8'),
			'country'       => $order_info['payment_iso_code_2'],
			'email'         => $order_info['email'],
			'return_url'    => $this->url->link('checkout/success', '', 'SSL'),
			'notify_url'    => $this->url->link('payment/pp_standard/callback', '', 'SSL'),
			'cancel_url'    => $this->url->link('checkout/checkout', '', 'SSL'),
			'products'      => $products,
			'discount'      => $discount,
		];

		$this->data['testmode'] = (bool)$this->config->get('pp_standard_test');

		$this->data['text_title'] = $this->language->get('text_title');
		$this->data['text_testmode'] = $this->language->get('text_testmode');

		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->data['header'] = $this->getChild('common/header_payment');
		$this->data['footer'] = $this->getChild('common/footer_payment');

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/pp_standard.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/pp_standard.tpl';
		} else {
			$this->template = 'default/template/payment/pp_standard.tpl';
		}

		$this->render();
	}

	// -------------------------------------------------------------------------

	public function callback() {
		if (isset($this->request->post['custom'])) {
			$order_id = (int)$this->request->post['custom'];
		} else {
			$order_id = 0;
		}

		if (!$order_id) {
			$this->log->write('PP_STANDARD :: callback() called with no order ID');
			return;
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if (!$order_info) {
			$this->log->write('PP_STANDARD :: callback() — order not found: ' . $order_id);
			return;
		}

		// Build IPN validation request
		$request = 'cmd=_notify-validate';

		foreach ($this->request->post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}

		$endpoint = $this->config->get('pp_standard_test') ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';

		$curl = curl_init($endpoint);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		if ($response === false) {
			$this->log->write('PP_STANDARD :: cURL failed: ' . curl_error($curl) . ' (' . curl_errno($curl) . ')');
			unset($curl);
			return;
		}

		unset($curl);

		if ($this->config->get('pp_standard_debug')) {
			$this->log->write('PP_STANDARD :: IPN REQUEST: ' . $request);
			$this->log->write('PP_STANDARD :: IPN RESPONSE: ' . $response);
		}

		// Only proceed on VERIFIED
		if (strcmp($response, 'VERIFIED') !== 0) {
			$this->log->write('PP_STANDARD :: IPN not VERIFIED for order ' . $order_id . ' — response: ' . $response);
			return;
		}

		if (!isset($this->request->post['payment_status'])) {
			$this->log->write('PP_STANDARD :: IPN VERIFIED but no payment_status for order ' . $order_id);
			return;
		}

		$order_status_id = $this->config->get('config_order_status_id');

		switch ($this->request->post['payment_status']) {

			case 'Completed':
				$receiver_email = strtolower($this->request->post['receiver_email'] ?? '');
				$config_email = strtolower($this->config->get('pp_standard_email'));
				$receiver_match = ($receiver_email === $config_email);

				$mc_gross = (float)($this->request->post['mc_gross'] ?? 0);
				$order_total = (float)$this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
				$total_match = ($mc_gross === $order_total);

				if ($receiver_match && $total_match) {
					$order_status_id = $this->config->get('pp_standard_completed_status_id');
				} else {
					if (!$receiver_match) {
						$this->log->write('PP_STANDARD :: Receiver mismatch — got: ' . $receiver_email . ', expected: ' . $config_email);
					}
					if (!$total_match) {
						$this->log->write('PP_STANDARD :: Total mismatch — got: ' . $mc_gross . ', expected: ' . $order_total);
					}
					return;
				}
				break;

			case 'Canceled_Reversal':
				$order_status_id = $this->config->get('pp_standard_canceled_reversal_status_id');
				break;
			case 'Denied':
				$order_status_id = $this->config->get('pp_standard_denied_status_id');
				break;
			case 'Expired':
				$order_status_id = $this->config->get('pp_standard_expired_status_id');
				break;
			case 'Failed':
				$order_status_id = $this->config->get('pp_standard_failed_status_id');
				break;
			case 'Pending':
				$order_status_id = $this->config->get('pp_standard_pending_status_id');
				break;
			case 'Processed':
				$order_status_id = $this->config->get('pp_standard_processed_status_id');
				break;
			case 'Refunded':
				$order_status_id = $this->config->get('pp_standard_refunded_status_id');
				break;
			case 'Reversed':
				$order_status_id = $this->config->get('pp_standard_reversed_status_id');
				break;
			case 'Voided':
				$order_status_id = $this->config->get('pp_standard_voided_status_id');
				break;

			default:
				$this->log->write('PP_STANDARD :: Unknown payment_status: ' . $this->request->post['payment_status'] . ' for order ' . $order_id);
				return;
		}

		if (!$order_info['order_status_id']) {
			$this->model_checkout_order->confirm($order_id, $order_status_id);
		} else {
			$this->model_checkout_order->update($order_id, $order_status_id);
		}
	}
}
