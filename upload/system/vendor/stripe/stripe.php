<?php
/**
 * Stripe Gateway
 * --------------
 * Stripe integration for NivoCart — raw cURL, no SDK required.
 *
 * Requires: PHP 7.1+, cURL extension, OpenSSL extension (for webhook sig)
 *
 * @package NivoCart
 */

if (class_exists('stripe')) {
    return; // already loaded — skip silently
}

class stripe {
    private string $secret_key;
    private string $publishable_key;
    private string $webhook_secret;
    private string $api_version = '2026-05-27.dahlia'; // pin this — update deliberately
    private string $api_base = 'https://api.stripe.com/v1';

    /**
     * Constructor.
     *
     * Pull keys from NivoCart's config table, env vars, or however the
     * platform stores sensitive settings. Never hardcode them here.
     *
     * Example (CodeIgniter-style):
     *   $config = $this->db->get_where('payment_gateways', ['name' => 'stripe'])->row();
     *   new StripeGateway($config->secret_key, $config->publishable_key, $config->webhook_secret);
     */
    public function __construct(string $secret_key, string $publishable_key, string $webhook_secret = '') {
        $this->secret_key = $secret_key;
        $this->publishable_key = $publishable_key;
        $this->webhook_secret = $webhook_secret;
    }

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Step 1 — Create a PaymentIntent when the customer reaches the confirm step.
     *
     * @param  int    $amount_pence  Amount in smallest currency unit (pence, cents, etc.)
     * @param  string $currency      ISO 4217 lowercase: 'gbp', 'usd', 'eur'
     * @param  string $order_ref     Your internal order reference — stored in Stripe metadata
     * @param  string $customer_email
     * @return array  ['client_secret' => '...', 'payment_intent_id' => '...']
     * @throws RuntimeException on API error
     */
    public function createPaymentIntent(int $amount_pence, string $currency, string $order_ref, string $customer_email = ''): array {
        $params = [
            'amount'               => $amount_pence,
            'currency'             => strtolower($currency),
            'payment_method_types' => ['card'],
            'metadata'             => ['order_ref' => $order_ref],
            // Capture immediately (default). Use 'manual' if you want to
            // authorise now and capture later (e.g. after stock check).
            'capture_method'       => 'automatic',
        ];

        if ($customer_email !== '') {
            $params['receipt_email'] = $customer_email;
        }

        $response = $this->post('/payment_intents', $params);

        return [
            'client_secret'     => $response['client_secret'],
            'payment_intent_id' => $response['id'],
        ];
    }

    /**
     * Step 2 — Verify the payment after Stripe.js redirects back.
     *
     * Always re-fetch from Stripe rather than trusting what the browser sends.
     *
     * @param  string $payment_intent_id  The pi_... ID posted by the browser
     * @return array  ['status' => 'succeeded'|'requires_action'|..., 'amount' => int, 'currency' => string, 'metadata' => array]
     * @throws RuntimeException if the intent is not in 'succeeded' state
     */
    public function verifyPayment(string $payment_intent_id): array {
        $response = $this->get('/payment_intents/' . $payment_intent_id);

        if ($response['status'] !== 'succeeded') {
            throw new RuntimeException(
                'Payment not completed. Status: ' . $response['status']
            );
        }

        return [
            'status'   => $response['status'],
            'amount'   => $response['amount'],
            'currency' => $response['currency'],
            'metadata' => $response['metadata'] ?? [],
        ];
    }

    /**
     * Step 3 (optional) — Refund a payment, fully or partially.
     *
     * @param  string   $payment_intent_id
     * @param  int|null $amount_pence  Omit to refund in full
     * @return array    Stripe Refund object
     */
    public function refund(string $payment_intent_id, ?int $amount_pence = null): array {
        $params = ['payment_intent' => $payment_intent_id];

        if ($amount_pence !== null) {
            $params['amount'] = $amount_pence;
        }

        return $this->post('/refunds', $params);
    }

    /**
     * Webhook — validate the Stripe-Signature header and return the decoded event.
     *
     * Call this at the top of stripe-webhook.php BEFORE any order processing.
     * Returns the event array on success, throws on failure.
     *
     * @param  string $payload   Raw request body — use file_get_contents('php://input')
     * @param  string $sig_header  Value of $_SERVER['HTTP_STRIPE_SIGNATURE']
     * @return array  Stripe Event object
     * @throws RuntimeException on signature mismatch or expired timestamp
     */
    public function validateWebhook(string $payload, string $sig_header): array {
        if ($this->webhook_secret === '') {
            throw new RuntimeException('Webhook secret not configured.');
        }

        // Stripe sends: t=timestamp,v1=signature
        $parts = explode(',', $sig_header);
        $timestamp = null;
        $signatures = [];

        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part, 2);
            if ($key === 't') {
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if ($timestamp === null || empty($signatures)) {
            throw new RuntimeException('Invalid Stripe-Signature header.');
        }

        // Reject webhooks older than 5 minutes (replay attack protection)
        if (abs(time() - $timestamp) > 300) {
            throw new RuntimeException('Webhook timestamp too old.');
        }

        $signed_payload = $timestamp . '.' . $payload;
        $expected_sig = hash_hmac('sha256', $signed_payload, $this->webhook_secret);
        $signature_valid = false;

        foreach ($signatures as $sig) {
            if (hash_equals($expected_sig, $sig)) {
                $signature_valid = true;
                break;
            }
        }

        if (!$signature_valid) {
            throw new RuntimeException('Stripe webhook signature mismatch.');
        }

        $event = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Webhook payload is not valid JSON.');
        }

        return $event;
    }

    /**
     * Return the publishable key for injecting into the checkout template.
     */
    public function getPublishableKey(): string {
        return $this->publishable_key;
    }

    // -------------------------------------------------------------------------
    // Private HTTP helpers
    // -------------------------------------------------------------------------

    private function post(string $endpoint, array $params): array {
        return $this->request('POST', $endpoint, $params);
    }

    private function get(string $endpoint, array $params = []): array {
        return $this->request('GET', $endpoint, $params);
    }

    private function request(string $method, string $endpoint, array $params = []): array {
        $url = $this->api_base . $endpoint;
        $ch = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->secret_key,
            'Stripe-Version: ' . $this->api_version,
            'Content-Type: application/x-www-form-urlencoded',
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            // Keep SSL verification on — never disable in production
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            // Stripe expects nested arrays as indexed fields: metadata[order_ref]=X
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->buildQuery($params));
        } elseif ($method === 'GET' && !empty($params)) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $this->buildQuery($params));
        }

        $body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error !== '') {
            throw new RuntimeException('Stripe cURL error: ' . $curl_error);
        }

        $data = json_decode($body, true);

        if ($http_code >= 400) {
            $message = $data['error']['message'] ?? 'Unknown Stripe error (HTTP ' . $http_code . ')';
            $code = $data['error']['code'] ?? '';
            throw new RuntimeException('Stripe API error: ' . $message . ($code ? ' [' . $code . ']' : ''));
        }

        return $data;
    }

    /**
     * Build a URL-encoded query string that handles nested arrays correctly.
     * e.g. ['metadata' => ['order_ref' => 'ORD-123']] → "metadata[order_ref]=ORD-123"
     * e.g. ['payment_method_types' => ['card']] → "payment_method_types[0]=card"
     */
    private function buildQuery(array $params, string $prefix = ''): string {
        $parts = [];

        foreach ($params as $key => $value) {
            $full_key = $prefix !== '' ? $prefix . '[' . $key . ']' : (string) $key;

            if (is_array($value)) {
                $parts[] = $this->buildQuery($value, $full_key);
            } else {
                $parts[] = urlencode($full_key) . '=' . urlencode((string) $value);
            }
        }

        return implode('&', $parts);
    }
}
