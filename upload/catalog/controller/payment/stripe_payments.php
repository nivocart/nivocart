<?php
/**
 * Class ControllerPaymentStripePayments
 *
 * @package NivoCart
 *
 * Refactored for Stripe PaymentIntents API (replaces deprecated Stripe_Charge / Stripe_Customer).
 * No raw card data ever touches this server — Stripe.js handles card collection in the browser.
 *
 * Flow:
 *   index()  → renders the payment form, creates a PaymentIntent, injects client_secret into template
 *   send()   → called by JS after Stripe.js confirms the card; verifies with Stripe, confirms order
 */
class ControllerPaymentStripePayments extends Controller {
    // -------------------------------------------------------------------------
    // index() — render payment form
    // Called by the checkout confirm step to embed the Stripe card widget.
    // -------------------------------------------------------------------------
    protected function index() {
        $this->language->load('payment/stripe_payments');

		$this->data['text_title'] = $this->language->get('text_title');
        $this->data['text_credit_card'] = $this->language->get('text_credit_card');
		$this->data['text_wait'] = $this->language->get('text_wait');

        $this->data['entry_cc_owner'] = $this->language->get('entry_cc_owner');

        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['button_back'] = $this->language->get('button_back');

        // Load order info to build the PaymentIntent
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        // Amount in smallest currency unit (pence, cents, etc.) — same logic as original
        $amount = (int)($this->currency->format($order_info['total'], $order_info['currency_code'], 1.00000, false) * 100);

        // Load our gateway library
        require_once(DIR_SYSTEM . 'vendor/stripe/stripe.php');

        $stripe = new Stripe(
            $this->config->get('stripe_payments_secret_key'),
            $this->config->get('stripe_payments_publishable_key'),
            $this->config->get('stripe_payments_webhook_secret')
        );

        // Create the PaymentIntent server-side and pass only the client_secret to the template
        try {
            $intent = $stripe->createPaymentIntent($amount, $order_info['currency_code'], (string)$this->session->data['order_id'], $order_info['email']);

            // Store intent ID in session so send() can verify it server-side
            $this->session->data['stripe_payment_intent_id'] = $intent['payment_intent_id'];

            $this->data['stripe_client_secret'] = $intent['client_secret'];
            $this->data['stripe_publishable_key'] = $stripe->getPublishableKey();
            $this->data['stripe_error'] = '';

        } catch (RuntimeException $e) {
            // Failed to create intent — surface a safe error, log the real one
            $this->log->write('Stripe createPaymentIntent error: ' . $e->getMessage());

            $this->data['stripe_client_secret'] = '';
            $this->data['stripe_publishable_key'] = $stripe->getPublishableKey();
            $this->data['stripe_error'] = $this->language->get('error_payment_init');
        }

        // Template
        $this->data['template'] = $this->config->get('config_template');

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/stripe_payments.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/stripe_payments.tpl';
        } else {
            $this->template = 'default/template/payment/stripe_payments.tpl';
        }

        $this->render();
    }

    // -------------------------------------------------------------------------
    // send() — verify payment and confirm order
    // Called via fetch() POST from Stripe.js after confirmCardPayment() succeeds.
    // Returns JSON: { success: url } or { error: message }
    // -------------------------------------------------------------------------
    public function send() {
        $json = [];

        // --- Input validation ---
        $posted_intent_id = isset($this->request->post['payment_intent_id']) ? trim($this->request->post['payment_intent_id']) : '';
        $session_intent_id = isset($this->session->data['stripe_payment_intent_id']) ? $this->session->data['stripe_payment_intent_id'] : '';

        // Reject if IDs don't match — prevents substitution attacks
        if ($posted_intent_id === '' || $posted_intent_id !== $session_intent_id) {
            $this->log->write('Stripe send(): payment_intent_id mismatch or missing. ' . 'Posted: ' . $posted_intent_id . ' Session: ' . $session_intent_id);

            $json['error'] = $this->language->get('error_payment_mismatch');

            $this->_sendJson($json);
            return;
        }

        // --- Load order ---
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if (!$order_info) {
            $json['error'] = $this->language->get('error_order_not_found');

            $this->_sendJson($json);
            return;
        }

        // --- Verify with Stripe (never trust the browser alone) ---
        require_once(DIR_SYSTEM . 'vendor/stripe/stripe.php');

        $stripe = new Stripe(
            $this->config->get('stripe_payments_secret_key'),
            $this->config->get('stripe_payments_publish_key'),
            $this->config->get('stripe_payments_webhook_secret')
        );

        try {
            $payment = $stripe->verifyPayment($posted_intent_id);
        } catch (RuntimeException $e) {
            $this->log->write('Stripe verifyPayment error: ' . $e->getMessage());

            $json['error'] = $this->language->get('error_payment_failed');

            $this->_sendJson($json);
            return;
        }

        // --- Confirm the order (same call as original) ---
        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('stripe_payments_order_status_id'));

        // Clean up session
        unset($this->session->data['stripe_payment_intent_id']);

        $json['success'] = $this->url->link('checkout/success', '', 'SSL');

        $this->_sendJson($json);
    }

    // -------------------------------------------------------------------------
    // Private helper
    // -------------------------------------------------------------------------
    private function _sendJson(array $data): void {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
}
