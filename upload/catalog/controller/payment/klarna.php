<?php
/**
 * Class ControllerPaymentKlarna
 *
 * Catalog-side controller for Klarna Payments. Exposes three AJAX
 * endpoints called by klarna.js during the checkout flow:
 *
 *   sessionCreate       — creates a Klarna Payments session from the
 *                         current cart and returns the client_token
 *                         needed to initialise the widget.
 *
 *   sessionUpdate       — re-syncs an existing session after a
 *                         cart-affecting change (coupon, shipping).
 *                         Klarna requires the session amount to match
 *                         what will actually be authorized.
 *
 *   storeAuthorization  — stashes the authorization_token returned by
 *                         Klarna.Payments.authorize() in the PHP session
 *                         so checkout_confirm.php can retrieve it and
 *                         call ModelPaymentKlarna::createOrder().
 *
 * No index() / standalone page exists — Klarna Payments is an
 * interactive gateway rendered inline on the checkout page, not a
 * redirect flow. The old klarna.tpl / klarna_payment.tpl standalone
 * pages are retired.
 *
 * @package NivoCart
 */
class ControllerPaymentKlarna extends Controller {
	// =========================================================================
	// AJAX: session create
	// =========================================================================

	/**
	 * Creates a Klarna Payments session for the current cart and returns
	 * the client_token + available payment_method_categories as JSON.
	 * Called by klarna.js GatewayModules['klarna'].init().
	 *
	 * POST params: (none required — all data is derived server-side from
	 * cart + session + config)
	 *
	 * Response JSON (success):
	 *   client_token              string
	 *   session_id                string
	 *   payment_method_categories array
	 *
	 * Response JSON (failure):
	 *   error                     string
	 */
	public function sessionCreate(): void {
		$json = [];

		if (!$this->_guardCheckout()) {
			$json['error'] = 'Session expired. Please refresh and try again.';
			$this->_sendJson($json);
			return;
		}

		$this->load->model('payment/klarna');
		$this->load->model('checkout/order');

		$country_code = $this->_resolvePaymentCountry();

		if ($country_code === '') {
			$json['error'] = 'Payment country could not be determined. Please select a billing address.';
			$this->_sendJson($json);
			return;
		}

		$total = $this->_resolveOrderTotal();

		$currency = $this->config->get('config_currency');

		$result = $this->model_payment_klarna->createSession($country_code, $currency, $total);

		if (isset($result['error'])) {
			$log = new Log('klarna.log');
			$log->write('sessionCreate failed for country ' . $country_code . ': ' . $result['error']);

			$json['error'] = $result['error'];
		} else {
			$json['client_token'] = $result['client_token'];
			$json['session_id'] = $result['session_id'];
			$json['payment_method_categories'] = $result['payment_method_categories'];
		}

		$this->_sendJson($json);
	}

	// =========================================================================
	// AJAX: session update
	// =========================================================================

	/**
	 * Re-syncs an existing Klarna session after a cart-affecting change.
	 * Called by klarna.js when the shipping method or a coupon changes.
	 *
	 * POST params: (none — server derives all data from cart + session)
	 *
	 * Response JSON (success):
	 *   session_id  string
	 *
	 * Response JSON (failure):
	 *   error       string
	 */
	public function sessionUpdate(): void {
		$json = [];

		if (!$this->_guardCheckout()) {
			$json['error'] = 'Session expired. Please refresh and try again.';
			$this->_sendJson($json);
			return;
		}

		$this->load->model('payment/klarna');

		$country_code = $this->_resolvePaymentCountry();

		if ($country_code === '') {
			$json['error'] = 'Payment country could not be determined.';
			$this->_sendJson($json);
			return;
		}

		$total = $this->_resolveOrderTotal();

		$currency = $this->config->get('config_currency');

		$result = $this->model_payment_klarna->updateSession($country_code, $currency, $total);

		if (isset($result['error'])) {
			$log = new Log('klarna.log');
			$log->write('sessionUpdate failed for country ' . $country_code . ': ' . $result['error']);

			$json['error'] = $result['error'];
		} else {
			$json['session_id'] = $result['session_id'];
		}

		$this->_sendJson($json);
	}

	// =========================================================================
	// AJAX: store authorization token
	// =========================================================================

	/**
	 * Receives and stashes the authorization_token returned by
	 * Klarna.Payments.authorize() after the customer completes the widget
	 * flow. checkout_confirm.php retrieves it from session in
	 * _confirmInteractivePayment() to call ModelPaymentKlarna::createOrder().
	 *
	 * Mirrors stripe_payments/storeIntent exactly in structure and purpose.
	 *
	 * POST params:
	 *   authorization_token  string  (required)
	 *
	 * Response JSON (success):
	 *   ok   true
	 *
	 * Response JSON (failure):
	 *   error  string
	 */
	public function storeAuthorization(): void {
		$json = [];

		if (!$this->_guardCheckout()) {
			$json['error'] = 'Session expired. Please refresh and try again.';
			$this->_sendJson($json);
			return;
		}

		$token = trim($this->request->post['authorization_token'] ?? '');

		if ($token === '') {
			$json['error'] = 'No authorization token received.';
			$this->_sendJson($json);
			return;
		}

		// Stash in session — checkout_confirm.php guards on this key
		// before allowing the Klarna interactive path to proceed, same
		// pattern as stripe_payment_intent_id for Stripe.
		$this->session->data['klarna_authorization_token'] = $token;

		$json['ok'] = true;

		$this->_sendJson($json);
	}

	// =========================================================================
	// Private helpers
	// =========================================================================

	/**
	 * Shared guard: verifies the checkout session is in a valid state
	 * for Klarna AJAX calls. Mirrors the guards in checkout_confirm.php
	 * so both paths agree on what constitutes a valid session.
	 *
	 * Does NOT require payment_method === 'klarna' here because the
	 * customer may be switching between gateways — sessionCreate can
	 * fire immediately on radio selection before payment_method is
	 * formally committed to session.
	 */
	private function _guardCheckout(): bool {
		// Must have a valid cart
		if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
			return false;
		}

		// Stock check
		if (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')) {
			return false;
		}

		// Must have a billing country resolvable (checked separately in
		// each action rather than here, to give a cleaner error message)
		return true;
	}

	/**
	 * Resolves the payment country code (ISO-2) from the session.
	 *
	 * Checkout stores billing address info in session->data['one_page_order']
	 * once the customer has selected a country. The country_id there is
	 * looked up in the country table to get iso_code_2, consistent with
	 * how checkout_confirm.php builds the order data array.
	 *
	 * Falls back to empty string — callers must check and return an
	 * error rather than proceeding without a country.
	 */
	private function _resolvePaymentCountry(): string {
		// If iso_code_2 is already in session (some checkout flows store
		// it directly), use it immediately.
		$order_session = $this->session->data['one_page_order'] ?? [];

		if (!empty($order_session['payment_iso_code_2'])) {
			return strtoupper($order_session['payment_iso_code_2']);
		}

		// Otherwise resolve from country_id via DB lookup — same approach
		// getOrder() uses for payment_iso_code_2.
		$country_id = (int)($order_session['payment_country_id'] ?? 0);

		if ($country_id === 0) {
			return '';
		}

		$result = $this->db->query("SELECT iso_code_2 FROM `" . DB_PREFIX . "country` WHERE country_id = '" . $country_id . "' LIMIT 1");

		return $result->num_rows ? strtoupper($result->row['iso_code_2']) : '';
	}

	/**
	 * Calculates the current order total from cart + active totals,
	 * mirroring the total-aggregation logic in checkout_confirm.php.
	 *
	 * This is a fast pass using the same extensions loop — not a full
	 * order build. The result is used only to set the Klarna session
	 * amount so the widget shows the correct total.
	 */
	private function _resolveOrderTotal(): float {
		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('total');

		usort($results, fn($a, $b) =>
			$this->config->get($a['code'] . '_sort_order') <=> $this->config->get($b['code'] . '_sort_order')
		);

		$total = 0.0;
		$taxes = $this->cart->getTaxes();
		$total_data = [];

		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('total/' . $result['code']);

				$model = $this->{'model_total_' . $result['code']};
				$contribution = $model->getTotal($taxes, $total);

				$total_data = array_merge($total_data, $contribution['total_data']);
				$total += $contribution['total'];

				$taxes = array_merge($taxes, $contribution['taxes'] ?? []);
			}
		}

		return (float)$total;
	}

	/**
	 * Sets the JSON response header and outputs encoded JSON.
	 */
	private function _sendJson(array $data): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}
