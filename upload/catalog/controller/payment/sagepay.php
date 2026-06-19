<?php
/**
 * Class ControllerPaymentSagepay
 *
 * Redirect gateway — Opayo (formerly Sage Pay) Form integration.
 *
 *   index()   — Standalone redirect page. Called by checkout_confirm after
 *               the order has been created. Renders a full page (using
 *               header_payment / footer_payment) that auto-submits an
 *               encrypted payload to Sage Pay's hosted form.
 *
 *   success() — Return handler. Sage Pay redirects the customer back here
 *               with an encrypted 'crypt' GET parameter. Decrypted and
 *               used to confirm/update the order.
 *
 * @package NivoCart
 */
class ControllerPaymentSagepay extends Controller {
	/** Error array Placeholder */

	public function index() {
		// Order must exist in session — checkout_confirm sets this before redirect
		if (empty($this->session->data['order_id'])) {
			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$this->language->load('payment/sagepay');

		$this->document->setTitle($this->language->get('text_title'));

		if ($this->config->get('sagepay_test') === 'live') {
			$action = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
		} elseif ($this->config->get('sagepay_test') === 'test') {
			$action = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
		} elseif ($this->config->get('sagepay_test') === 'sim') {
			$action = 'https://test.sagepay.com/simulator/vspformgateway.asp';
		} else {
			// Unknown/unset mode — fail safe to checkout rather than silently
			// posting to an undefined action.
			$this->log->write('SAGEPAY :: Unknown sagepay_test mode — cannot determine endpoint.');

			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$vendor = $this->config->get('sagepay_vendor');
		$password = $this->config->get('sagepay_password');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (!$order_info) {
			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$data = [];

		$data['VendorTxCode'] = $this->session->data['order_id'];
		$data['ReferrerID'] = 'E511AF91-E4A0-42DE-80B0-09C981A3FB61';
		$data['Amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$data['Currency'] = $order_info['currency_code'];
		$data['Description'] = sprintf($this->language->get('text_description'), date($this->language->get('date_format_short')), $this->session->data['order_id']);

		$data['SuccessURL'] = str_replace('&amp;', '&', $this->url->link('payment/sagepay/success', 'order_id=' . $this->session->data['order_id'], 'SSL'));
		$data['FailureURL'] = str_replace('&amp;', '&', $this->url->link('checkout/checkout', '', 'SSL'));

		$data['CustomerName'] = html_entity_decode($order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		$data['SendEMail'] = '1';
		$data['CustomerEMail'] = $order_info['email'];
		$data['VendorEMail'] = $this->config->get('config_email');

		$data['BillingFirstnames'] = $order_info['payment_firstname'];
		$data['BillingSurname'] = $order_info['payment_lastname'];
		$data['BillingAddress1'] = $order_info['payment_address_1'];

		if ($order_info['payment_address_2']) {
			$data['BillingAddress2'] = $order_info['payment_address_2'];
		}

		$data['BillingCity'] = $order_info['payment_city'];
		$data['BillingPostCode'] = $order_info['payment_postcode'];
		$data['BillingCountry'] = $order_info['payment_iso_code_2'];

		if ($order_info['payment_iso_code_2'] === 'US') {
			$data['BillingState'] = $order_info['payment_zone_code'];
		}

		$data['BillingPhone'] = $order_info['telephone'];

		if ($this->cart->hasShipping()) {
			$data['DeliveryFirstnames'] = $order_info['shipping_firstname'];
			$data['DeliverySurname'] = $order_info['shipping_lastname'];
			$data['DeliveryAddress1'] = $order_info['shipping_address_1'];

			if ($order_info['shipping_address_2']) {
				$data['DeliveryAddress2'] = $order_info['shipping_address_2'];
			}

			$data['DeliveryCity'] = $order_info['shipping_city'];
			$data['DeliveryPostCode'] = $order_info['shipping_postcode'];
			$data['DeliveryCountry'] = $order_info['shipping_iso_code_2'];

			if ($order_info['shipping_iso_code_2'] === 'US') {
				$data['DeliveryState'] = $order_info['shipping_zone_code'];
			}

			$data['DeliveryPhone'] = $order_info['telephone'];

		} else {
			$data['DeliveryFirstnames'] = $order_info['payment_firstname'];
			$data['DeliverySurname'] = $order_info['payment_lastname'];
			$data['DeliveryAddress1'] = $order_info['payment_address_1'];

			if ($order_info['payment_address_2']) {
				$data['DeliveryAddress2'] = $order_info['payment_address_2'];
			}

			$data['DeliveryCity'] = $order_info['payment_city'];
			$data['DeliveryPostCode'] = $order_info['payment_postcode'];
			$data['DeliveryCountry'] = $order_info['payment_iso_code_2'];

			if ($order_info['payment_iso_code_2'] === 'US') {
				$data['DeliveryState'] = $order_info['payment_zone_code'];
			}

			$data['DeliveryPhone'] = $order_info['telephone'];
		}

		$data['AllowGiftAid'] = '0';

		if (!$this->config->get('sagepay_transaction')) {
			$data['ApplyAVSCV2'] = '0';
		}

		// NOTE: 3D Secure is currently disabled. Opayo (Elavon) increasingly
		// requires 3DS2 for SCA compliance — revisit when implementing the
		// modern PI integration.
		$data['Apply3DSecure'] = '0';

		$crypt_data = [];

		foreach ($data as $key => $value) {
			$crypt_data[] = $key . '=' . $value;
		}

		$plain_text = mb_convert_encoding(implode('&', $crypt_data), 'ISO-8859-1', 'UTF-8');

		$crypt = base64_encode($this->simpleXor($plain_text, $password));

		$this->data['sagepay_data'] = [
			'action'      => $action,
			'transaction' => $this->config->get('sagepay_transaction'),
			'vendor'      => $vendor,
			'crypt'       => $crypt,
		];

		$this->data['text_title'] = $this->language->get('text_title');

		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->data['header'] = $this->getChild('common/header_payment');
		$this->data['footer'] = $this->getChild('common/footer_payment');

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/sagepay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/sagepay.tpl';
		} else {
			$this->template = 'default/template/payment/sagepay.tpl';
		}

		$this->render();
	}

	// -------------------------------------------------------------------------

	public function success() {
		if (empty($this->request->get['crypt']) || empty($this->request->get['order_id'])) {
			$this->log->write('SAGEPAY :: success() called without crypt or order_id');

			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$order_id = (int)$this->request->get['order_id'];

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if (!$order_info) {
			$this->log->write('SAGEPAY :: success() — order not found: ' . $order_id);

			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$string = base64_decode(str_replace(' ', '+', $this->request->get['crypt']));
		$password = $this->config->get('sagepay_password');

		$decrypted = $this->simpleXor($string, $password);
		$output = mb_convert_encoding($decrypted, 'UTF-8', 'ISO-8859-1');

		$data = $this->getToken($output);

		if (!$data || !is_array($data) || empty($data['Status'])) {
			$this->log->write('SAGEPAY :: success() — could not parse response for order ' . $order_id);

			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
		}

		$message = '';

		$log_fields = [
			'VPSTxId', 'TxAuthNo', 'AVSCV2', 'AddressResult',
			'PostCodeResult', 'CV2Result', '3DSecureStatus', 'CAVV',
			'CardType', 'Last4Digits',
		];

		foreach ($log_fields as $field) {
			if (isset($data[$field])) {
				$message .= $field . ': ' . $data[$field] . "\n";
			}
		}

		$order_status_id = ($data['Status'] === 'OK') ? $this->config->get('sagepay_order_status_id') : $this->config->get('config_order_status_id');

		if (!$order_info['order_status_id']) {
			$this->model_checkout_order->confirm($order_id, $order_status_id, $message, false);
		} else {
			$this->model_checkout_order->update($order_id, $order_status_id, $message, false);
		}

		$this->redirect($this->url->link('checkout/success', '', 'SSL'));
	}

	// -------------------------------------------------------------------------

	protected function simpleXor($string, $password) {
		$data = [];

		for ($i = 0; $i < strlen($password); $i++) {
			$data[$i] = ord(substr($password, $i, 1));
		}

		$output = '';

		for ($i = 0; $i < strlen($string); $i++) {
			$output .= chr(ord(substr($string, $i, 1)) ^ ($data[$i % strlen($password)]));
		}

		return $output;
	}

	protected function getToken($string) {
		$tokens = [
			'Status', 'StatusDetail', 'VendorTxCode', 'VPSTxId', 'TxAuthNo',
			'Amount', 'AVSCV2', 'AddressResult', 'PostCodeResult', 'CV2Result',
			'GiftAid', '3DSecureStatus', 'CAVV', 'AddressStatus', 'CardType',
			'Last4Digits', 'PayerStatus',
		];

		$output = [];
		$data = [];

		for ($i = count($tokens) - 1; $i >= 0; $i--) {
			$start = strpos($string, $tokens[$i]);

			if ($start !== false) {
				$data[$i]['start'] = $start;
				$data[$i]['token'] = $tokens[$i];
			}
		}

		sort($data);

		for ($i = 0; $i < count($data); $i++) {
			$start = $data[$i]['start'] + strlen($data[$i]['token']) + 1;

			if ($i === (count($data) - 1)) {
				$output[$data[$i]['token']] = substr($string, $start);
			} else {
				$length = $data[$i + 1]['start'] - $data[$i]['start'] - strlen($data[$i]['token']) - 2;
				$output[$data[$i]['token']] = substr($string, $start, $length);
			}
		}

		return $output;
	}
}
