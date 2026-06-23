<?php
/**
 * Class PayPalClient
 *
 * Shared PayPal Orders v2 / Payments v2 REST API client.
 * Used by both admin and catalog PayPal Express models.
 *
 * @package NivoCart
 */
class PayPalClient {
	/**
	 * Object $config
	 */
	private object $config;

	/**
	 * String $cached_token
	 */
	private ?string $cached_token = null;

	/**
	 * Construct
	 */
	public function __construct(object $config) {
		$this->config = $config;
	}

	// -------------------------------------------------------------------------
	// Authentication
	// -------------------------------------------------------------------------

	/**
	 * Returns a Bearer access token, cached for the lifetime of this instance.
	 */
	public function getAccessToken(): string|false {
		if ($this->cached_token !== null) {
			return $this->cached_token;
		}

		$sandbox = (bool)$this->config->get('pp_express_sandbox');
		$client_id = $sandbox ? $this->config->get('pp_express_sandbox_client_id') : $this->config->get('pp_express_client_id');
		$client_secret = $sandbox ? $this->config->get('pp_express_sandbox_client_secret') : $this->config->get('pp_express_client_secret');

		$response = $this->curlPost(
			$this->endpoint('v1/oauth2/token'),
			'grant_type=client_credentials',
			[
				CURLOPT_USERPWD    => $client_id . ':' . $client_secret,
				CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
			]
		);

		if (!empty($response['access_token'])) {
			$this->cached_token = $response['access_token'];
			return $this->cached_token;
		}

		return false;
	}

	// -------------------------------------------------------------------------
	// Orders v2
	// -------------------------------------------------------------------------

	/**
	 * POST /v2/checkout/orders
	 */
	public function createOrder(array $payload): array|false {
		$token = $this->getAccessToken();

		if (!$token) return false;

		return $this->curlPost(
			$this->endpoint('v2/checkout/orders'),
			json_encode($payload),
			$this->authHeaders($token)
		);
	}

	/**
	 * GET /v2/checkout/orders/{id}
	 */
	public function getOrderDetails(string $pp_order_id): array|false {
		$token = $this->getAccessToken();

		if (!$token) return false;

		return $this->curlGet(
			$this->endpoint('v2/checkout/orders/' . $pp_order_id),
			$this->authHeaders($token)
		);
	}

	/**
	 * POST /v2/checkout/orders/{id}/capture
	 */
	public function captureOrder(string $pp_order_id): array|false {
		$token = $this->getAccessToken();

		if (!$token) return false;

		return $this->curlPost(
			$this->endpoint('v2/checkout/orders/' . $pp_order_id . '/capture'),
			'{}',
			$this->authHeaders($token)
		);
	}

	/**
	 * POST /v2/checkout/orders/{id}/authorize
	 */
	public function authorizeOrder(string $pp_order_id): array|false {
		$token = $this->getAccessToken();

		if (!$token) return false;

		return $this->curlPost(
			$this->endpoint('v2/checkout/orders/' . $pp_order_id . '/authorize'),
			'{}',
			$this->authHeaders($token)
		);
	}

	// -------------------------------------------------------------------------
	// Payments v2
	// -------------------------------------------------------------------------

	/**
	 * POST /v2/payments/authorizations/{id}/capture
	 * Pass empty array to capture full authorized amount.
	 */
	public function captureAuthorization(string $authorization_id, array $data = []): array|false {
		$token = $this->getAccessToken();

		if (!$token) return false;

		return $this->curlPost(
			$this->endpoint('v2/payments/authorizations/' . $authorization_id . '/capture'),
			empty($data) ? '{}' : json_encode($data),
			$this->authHeaders($token)
		);
	}

	/**
	 * POST /v2/payments/authorizations/{id}/void
	 * Returns empty array on HTTP 204 success.
	 */
	public function voidAuthorization(string $authorization_id): array|false {
		$token = $this->getAccessToken();

		if (!$token) return false;

		return $this->curlPost(
			$this->endpoint('v2/payments/authorizations/' . $authorization_id . '/void'),
			'{}',
			$this->authHeaders($token),
			true // allow empty 204 response
		);
	}

	/**
	 * POST /v2/payments/captures/{id}/refund
	 * Pass empty array for full refund.
	 */
	public function refundCapture(string $capture_id, array $data = []): array|false {
		$token = $this->getAccessToken();

		if (!$token) return false;

		return $this->curlPost(
			$this->endpoint('v2/payments/captures/' . $capture_id . '/refund'),
			empty($data) ? '{}' : json_encode($data),
			$this->authHeaders($token)
		);
	}

	// -------------------------------------------------------------------------
	// Webhooks v2
	// -------------------------------------------------------------------------

	/**
	 * POST /v2/notifications/verify-webhook-signature
	 * Returns true if PayPal confirms the signature is valid.
	 */
	public function verifyWebhookSignature(array $headers, string $raw_body, string $webhook_id): bool {
		$token = $this->getAccessToken();

		if (!$token) return false;

		$payload = [
			'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'] ?? '',
			'cert_url'          => $headers['PAYPAL-CERT-URL'] ?? '',
			'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
			'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
			'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
			'webhook_id'        => $webhook_id,
			'webhook_event'     => json_decode($raw_body, true),
		];

		$response = $this->curlPost(
			$this->endpoint('v2/notifications/verify-webhook-signature'),
			json_encode($payload),
			$this->authHeaders($token)
		);

		return isset($response['verification_status'])
			&& $response['verification_status'] === 'SUCCESS';
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	private function endpoint(string $path): string {
		$base = $this->config->get('pp_express_sandbox') ? 'https://api-m.sandbox.paypal.com/' : 'https://api-m.paypal.com/';

		return $base . ltrim($path, '/');
	}

	private function authHeaders(string $token): array {
		return [
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Authorization: Bearer ' . $token,
			],
		];
	}

	/**
	 * @param bool $allow_empty  Set true for endpoints that return HTTP 204 No Content on success (e.g. void).
	 */
	private function curlPost(string $url, string $body, array $extra_opts = [], bool $allow_empty = false): array|false {
		$opts = [
			CURLOPT_URL            => $url,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $body,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => false,
			CURLOPT_FRESH_CONNECT  => true,
			CURLOPT_FORBID_REUSE   => true,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
		];

		foreach ($extra_opts as $key => $value) {
			$opts[$key] = $value;
		}

		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if (curl_errno($ch) !== CURLE_OK) {
			curl_close($ch);
			return false;
		}

		curl_close($ch);

		// HTTP 204 No Content (e.g. void authorization)
		if ($allow_empty && $http_code === 204) {
			return [];
		}

		$decoded = json_decode($response, true);

		return is_array($decoded) ? $decoded : false;
	}

	private function curlGet(string $url, array $extra_opts = []): array|false {
		$opts = [
			CURLOPT_URL            => $url,
			CURLOPT_HTTPGET        => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => false,
			CURLOPT_FRESH_CONNECT  => true,
			CURLOPT_FORBID_REUSE   => true,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
		];

		foreach ($extra_opts as $key => $value) {
			$opts[$key] = $value;
		}

		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$response = curl_exec($ch);

		if (curl_errno($ch) !== CURLE_OK) {
			curl_close($ch);
			return false;
		}

		curl_close($ch);

		$decoded = json_decode($response, true);

		return is_array($decoded) ? $decoded : false;
	}
}
