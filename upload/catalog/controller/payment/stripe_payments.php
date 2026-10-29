<?php
/**
 * Class ControllerPaymentStripePayments
 *
 * @package NivoCart
 *
 * Handles three distinct calling contexts:
 *
 *   index()        → Standard checkout: renders card widget (getChild from confirm.tpl)
 *   confirm()      → One page checkout: silent no-op (getChild from checkout_one_page_confirm)
 *                    Payment is collected browser-side before confirm controller runs.
 *   intentCreate() → AJAX: creates PaymentIntent, returns { client_secret, publishable_key }
 *                    Called by one page checkout JS before form submission.
 *   send()         → AJAX: verifies PaymentIntent server-side, confirms order.
 *                    Called by JS after Stripe.js confirmCardPayment() succeeds.
 */
class ControllerPaymentStripePayments extends Controller {
    // -------------------------------------------------------------------------
    // index() — Standard checkout card widget
    // Called via getChild('payment/stripe_payments') from checkout/confirm.tpl
    // Creates PaymentIntent server-side, injects client_secret into template.
    // -------------------------------------------------------------------------
    protected function index() {
        $this->language->load('payment/stripe_payments');

		$this->data['text_title'] = $this->language->get('text_title');
        $this->data['text_credit_card'] = $this->language->get('text_credit_card');
		$this->data['text_wait'] = $this->language->get('text_wait');

        $this->data['entry_cc_owner'] = $this->language->get('entry_cc_owner');

        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['button_back'] = $this->language->get('button_back');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $amount = (int)($this->currency->format($order_info['total'], $order_info['currency_code'], 1.00000, false) * 100);

        $stripe = $this->_loadStripe();

        try {
            $intent = $stripe->createPaymentIntent($amount, $order_info['currency_code'], (string)$this->session->data['order_id'], $order_info['email']);

            $this->session->data['stripe_payment_intent_id'] = $intent['payment_intent_id'];

            $this->data['stripe_client_secret'] = $intent['client_secret'];
            $this->data['stripe_publishable_key'] = $stripe->getPublishableKey();
            $this->data['stripe_error'] = '';

        } catch (RuntimeException $e) {
            $this->log->write('Stripe createPaymentIntent error: ' . $e->getMessage());

            $this->data['stripe_client_secret'] = '';
            $this->data['stripe_publishable_key'] = $stripe->getPublishableKey();
            $this->data['stripe_error'] = $this->language->get('error_payment_init');
        }

        $this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('payment/stripe_payments');

        $this->render();
    }

    // -------------------------------------------------------------------------
    // confirm() — One page checkout silent hook
    // Called via getChild('payment/stripe_payments/confirm') from
    // checkout_one_page_confirm. Payment is already confirmed browser-side
    // by this point — this method intentionally does nothing.
    // checkout_one_page_confirm handles verification itself via the session.
    // -------------------------------------------------------------------------
    public function confirm() {
        // Intentional no-op for one page checkout flow.
        // See: ControllerCheckoutCheckoutOnePageConfirm::index()
    }

    // -------------------------------------------------------------------------
    // intentCreate() — AJAX: create (or refresh) the PaymentIntent for one page checkout
    // The amount is ALWAYS worked out on the server from the cart and the
    // selected shipping / payment method. Nothing posted by the browser is trusted.
    // An unpaid intent already in the session is reused and its amount updated,
    // so abandoned intents do not pile up in Stripe.
    // Returns JSON: { client_secret, publishable_key, amount } or { paid: true } or { error }
    // -------------------------------------------------------------------------
    public function intentCreate() {
        $this->language->load('payment/stripe_payments');

        $json = [];

		if (!$this->cart->hasProducts()) {
			$json['error'] = $this->language->get('error_payment_init');
			$this->_sendJson($json);
			return;
		}

		$currency_code = $this->config->get('config_currency');
		$total = round($this->getOrderTotal(), 2);

		if ($total <= 0) {
			$json['error'] = $this->language->get('error_payment_init');
			$this->_sendJson($json);
			return;
		}

        $stripe = $this->_loadStripe();

		$amount = $stripe->toMinorUnits($total, $currency_code);

        try {
			$intent_id = $this->session->data['stripe_payment_intent_id'] ?? '';
			$intent = [];

			if ($intent_id !== '') {
				try {
					$existing = $stripe->getPaymentIntent($intent_id);

					if ($existing['currency'] === strtolower($currency_code)) {
						if ($existing['status'] === 'succeeded' && $existing['amount'] === $amount) {
							// Already paid for exactly this total (e.g. wallet payment before the form was submitted)
							$json['paid'] = true;
							$this->_sendJson($json);
							return;
						}

						if ($existing['status'] === 'requires_payment_method') {
							if ($existing['amount'] !== $amount) {
								$existing = $stripe->updatePaymentIntentAmount($intent_id, $amount);
							}

							$intent = ['client_secret' => $existing['client_secret'], 'payment_intent_id' => $existing['id']];
						}
					}

					if (!$intent && $existing['status'] === 'succeeded') {
						// Paid for a different total: give the money back before starting again
						$stripe->refund($intent_id);
						$this->log->write('Stripe intentCreate: refunded ' . $intent_id . ' (paid ' . $existing['amount'] . ', cart now ' . $amount . ')');
					}
				} catch (RuntimeException $e) {
					$this->log->write('Stripe intentCreate lookup error: ' . $e->getMessage());
				}
			}

			if (!$intent) {
				// Use a temporary reference — the real order_id exists only after addOrder()
				$temp_ref = 'pending_' . $this->customer->getId() . '_' . time();

				$intent = $stripe->createPaymentIntent($amount, $currency_code, $temp_ref, $this->customer->getEmail());
			}

            // Store in session — storeIntent() and checkout_confirm verify against this
            $this->session->data['stripe_payment_intent_id'] = $intent['payment_intent_id'];

            $json['client_secret'] = $intent['client_secret'];
            $json['publishable_key'] = $stripe->getPublishableKey();
			$json['amount'] = $amount;

        } catch (RuntimeException $e) {
            $this->log->write('Stripe intentCreate error: ' . $e->getMessage());

            $json['error'] = $this->language->get('error_payment_init');
        }

        $this->_sendJson($json);
    }

    // -------------------------------------------------------------------------
    // send() — AJAX: verify PaymentIntent and confirm order
    // Called by JS after Stripe.js confirmCardPayment() succeeds.
    // Works for BOTH standard and one page checkout flows.
    // Returns JSON: { success: url } or { error: message }
    // -------------------------------------------------------------------------
    public function send() {
        $this->language->load('payment/stripe_payments');

        $json = [];

        $posted_intent_id = isset($this->request->post['payment_intent_id']) ? trim($this->request->post['payment_intent_id']) : '';

        $session_intent_id = isset($this->session->data['stripe_payment_intent_id']) ? $this->session->data['stripe_payment_intent_id'] : '';

        if ($posted_intent_id === '' || $posted_intent_id !== $session_intent_id) {
            $this->log->write('Stripe send(): intent mismatch. Posted: ' . $posted_intent_id . ' Session: ' . $session_intent_id);

            $json['error'] = $this->language->get('error_payment_mismatch');
            $this->_sendJson($json);
            return;
        }

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if (!$order_info) {
            $json['error'] = $this->language->get('error_order_not_found');
            $this->_sendJson($json);
            return;
        }

        $stripe = $this->_loadStripe();

        try {
            $stripe->verifyPayment($posted_intent_id);
        } catch (RuntimeException $e) {
            $this->log->write('Stripe verifyPayment error: ' . $e->getMessage());

            $json['error'] = $this->language->get('error_payment_failed');
            $this->_sendJson($json);
            return;
        }

        $this->model_checkout_order->confirm(
            $this->session->data['order_id'],
            $this->config->get('stripe_payments_order_status_id')
        );

        unset($this->session->data['stripe_payment_intent_id']);

        $json['success'] = $this->url->link('checkout/success', '', 'SSL');

        $this->_sendJson($json);
    }

	// -------------------------------------------------------------------------
	// storeIntent() — AJAX: called after Stripe.js reports the payment succeeded.
	// The intent must be the one this session created, and Stripe itself must
	// confirm it is paid. The amount is checked again in checkout_confirm.
	// -------------------------------------------------------------------------
	public function storeIntent() {
		$this->language->load('payment/stripe_payments');

		$intent_id = isset($this->request->post['payment_intent_id']) ? trim($this->request->post['payment_intent_id']) : '';
		$session_intent_id = $this->session->data['stripe_payment_intent_id'] ?? '';

		if ($intent_id === '' || $intent_id !== $session_intent_id) {
			$this->log->write('Stripe storeIntent(): intent mismatch. Posted: ' . $intent_id . ' Session: ' . $session_intent_id);
			$this->_sendJson(['error' => $this->language->get('error_payment_mismatch')]);
			return;
		}

		try {
			$this->_loadStripe()->verifyPayment($intent_id);
		} catch (RuntimeException $e) {
			$this->log->write('Stripe storeIntent() verify error: ' . $e->getMessage());
			$this->_sendJson(['error' => $this->language->get('error_payment_failed')]);
			return;
		}

		$this->_sendJson(['success' => true]);
	}

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------
    private function _loadStripe(): Stripe {
        require_once(DIR_SYSTEM . 'vendor/stripe/stripe.php');

		$secret_key = $this->config->get('stripe_payments_secret_key');
		$publishable_key = $this->config->get('stripe_payments_publishable_key');
		$webhook_secret = $this->config->get('stripe_payments_webhook_secret');

		$stripe = new Stripe($secret_key, $publishable_key, $webhook_secret);

        return $stripe;
    }

	/**
	 * Order total in the store's default currency, built exactly as checkout_confirm builds it.
	 */
	private function getOrderTotal(): float {
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

				$total += $contribution['total'];
				$taxes = OrderTotalHelper::mergeTaxes($taxes, $contribution['taxes']);
			}
		}

		return $total;
	}

    private function _sendJson(array $data): void {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
