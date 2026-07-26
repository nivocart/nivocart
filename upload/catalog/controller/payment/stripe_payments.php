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
    // intentCreate() — AJAX: create PaymentIntent for one page checkout
    // Called by JS before the order form is submitted, so the client_secret
    // is available for Stripe.js to confirm the card in the browser.
    // Returns JSON: { client_secret, publishable_key } or { error }
    // -------------------------------------------------------------------------
    public function intentCreate() {
        $this->language->load('payment/stripe_payments');

        $json = [];

		$amount = (int)round((float)($this->request->post['cart_total'] ?? 0) * 100);
		$currency_code = $this->request->post['currency_code'] ?? $this->config->get('config_currency');

        // Use a temporary reference — real order_id set after addOrder() in confirm
        // We store the intent in session; order_id is updated in send() after addOrder()
        $temp_ref = 'pending_' . $this->customer->getId() . '_' . time();

		require_once(DIR_SYSTEM . 'vendor/stripe/stripe.php');

        $stripe = $this->_loadStripe();

        try {
            $intent = $stripe->createPaymentIntent($amount, $currency_code, $temp_ref, $this->customer->getEmail());

            // Store in session — send() will verify against this
            $this->session->data['stripe_payment_intent_id'] = $intent['payment_intent_id'];

            $json['client_secret'] = $intent['client_secret'];
            $json['publishable_key'] = $stripe->getPublishableKey();

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

	public function storeIntent() {
		$intent_id = isset($this->request->post['payment_intent_id']) ? trim($this->request->post['payment_intent_id']) : '';

		if ($intent_id === '') {
			$this->_sendJson(['error' => 'Missing intent ID']);
			return;
		}

		$this->session->data['stripe_payment_intent_id'] = $intent_id;
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

    private function _sendJson(array $data): void {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
