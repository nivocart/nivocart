<?php
/**
 * Class ModelPaymentPPExpress
 *
 * Admin model for PayPal Express — Orders v2 REST API.
 *
 * @package NivoCart
 */
class ModelPaymentPPExpress extends Model {
	// -------------------------------------------------------------------------
	// Install / Uninstall
	// -------------------------------------------------------------------------

	public function install(): void {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paypal_order` (
			`paypal_order_id` INT NOT NULL AUTO_INCREMENT,
			`order_id` INT NOT NULL,
			`pp_order_id` VARCHAR(20) NOT NULL DEFAULT '',
			`intent` ENUM('CAPTURE','AUTHORIZE') NOT NULL DEFAULT 'CAPTURE',
			`status` VARCHAR(30) NOT NULL DEFAULT '',
			`capture_id` VARCHAR(20) NOT NULL DEFAULT '',
			`currency_code` CHAR(3) NOT NULL DEFAULT '',
			`total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			`created` DATETIME NOT NULL,
			`modified` DATETIME NOT NULL,
			PRIMARY KEY (`paypal_order_id`),
			KEY `order_id` (`order_id`),
			KEY `pp_order_id` (`pp_order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
		");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paypal_order_transaction` (
			`paypal_order_transaction_id` INT NOT NULL AUTO_INCREMENT,
			`paypal_order_id` INT NOT NULL,
			`pp_order_id` VARCHAR(20) NOT NULL DEFAULT '',
			`capture_id` VARCHAR(20) NOT NULL DEFAULT '',
			`transaction_type` ENUM('CAPTURE','AUTHORIZE','REFUND','VOID') NOT NULL,
			`status` VARCHAR(30) NOT NULL DEFAULT '',
			`amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			`currency_code` CHAR(3) NOT NULL DEFAULT '',
			`note` VARCHAR(255) NOT NULL DEFAULT '',
			`raw_response` TEXT NOT NULL,
			`created` DATETIME NOT NULL,
			PRIMARY KEY (`paypal_order_transaction_id`),
			KEY `paypal_order_id` (`paypal_order_id`),
			KEY `capture_id` (`capture_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
		");
	}

	public function uninstall(): void {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paypal_order_transaction`;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paypal_order`;");
	}

	// -------------------------------------------------------------------------
	// DB reads — paypal_order
	// -------------------------------------------------------------------------

	/**
	 * Fetch a paypal_order row by its own PK.
	 */
	public function getPaypalOrder(int $paypal_order_id): array|false {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_order` WHERE `paypal_order_id` = " . (int)$paypal_order_id . " LIMIT 1");

		return $query->num_rows ? $query->row : false;
	}

	/**
	 * Fetch a paypal_order row by the NivoCart order_id.
	 */
	public function getPaypalOrderByOrderId(int $order_id): array|false {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_order` WHERE `order_id` = " . (int)$order_id . " LIMIT 1");

		return $query->num_rows ? $query->row : false;
	}

	/**
	 * Fetch a paypal_order row by the PayPal Orders v2 order ID (pp_order_id).
	 */
	public function getPaypalOrderByPPOrderId(string $pp_order_id): array|false {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_order` WHERE `pp_order_id` = '" . $this->db->escape($pp_order_id) . "' LIMIT 1");

		return $query->num_rows ? $query->row : false;
	}

	/**
	 * Full order record including transactions and running totals.
	 * Used by the admin order-view page.
	 */
	public function getOrder(int $order_id): array|false {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_order` WHERE `order_id` = " . (int)$order_id . " LIMIT 1");

		if (!$query->num_rows) {
			return false;
		}

		$order = $query->row;

		$order['transactions'] = $this->getTransactions($order['paypal_order_id']);
		$order['captured'] = $this->getTotalCaptured($order['paypal_order_id']);
		$order['refunded'] = $this->getTotalRefunded($order['paypal_order_id']);

		return $order;
	}

	// -------------------------------------------------------------------------
	// DB writes — paypal_order
	// -------------------------------------------------------------------------

	/**
	 * Insert a new paypal_order row when an order is first created.
	 * Returns the new paypal_order_id.
	 */
	public function saveOrder(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "paypal_order` SET
			`order_id` = " . (int)$data['order_id'] . ",
			`pp_order_id` = '" . $this->db->escape($data['pp_order_id']) . "',
			`intent` = '" . $this->db->escape($data['intent']) . "',
			`status` = '" . $this->db->escape($data['status']) . "',
			`capture_id` = '" . $this->db->escape($data['capture_id'] ?? '') . "',
			`currency_code` = '" . $this->db->escape($data['currency_code']) . "',
			`total` = " . (float)$data['total'] . ",
			`created` = NOW(),
			`modified` = NOW()
		");

		return $this->db->getLastId();
	}

	/**
	 * Update status (and optionally capture_id) after a capture or authorize call.
	 */
	public function updatePaypalOrderStatus(int $order_id, string $status, string $capture_id = ''): void {
		$set = "`status` = '" . $this->db->escape($status) . "', `modified` = NOW()";

		if ($capture_id !== '') {
			$set .= ", `capture_id` = '" . $this->db->escape($capture_id) . "'";
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "paypal_order` SET " . $set . " WHERE `order_id` = " . (int)$order_id);
	}

	// -------------------------------------------------------------------------
	// DB reads — paypal_order_transaction
	// -------------------------------------------------------------------------

	/**
	 * All transactions for a given paypal_order_id, oldest first.
	 */
	public function getTransactions(int $paypal_order_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_order_transaction`
			WHERE `paypal_order_id` = " . (int)$paypal_order_id . "
			ORDER BY `paypal_order_transaction_id` ASC
		");

		return $query->num_rows ? $query->rows : [];
	}

	/**
	 * Sum of all successful capture amounts for a paypal_order_id.
	 */
	public function getTotalCaptured(int $paypal_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total`
			FROM `" . DB_PREFIX . "paypal_order_transaction`
			WHERE `paypal_order_id` = " . (int)$paypal_order_id . "
			AND `transaction_type` = 'CAPTURE'
			AND `status` IN ('COMPLETED', 'PENDING')
		");

		return (float)($query->row['total'] ?? 0.00);
	}

	/**
	 * Sum of all refund amounts for a paypal_order_id.
	 */
	public function getTotalRefunded(int $paypal_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total`
			FROM `" . DB_PREFIX . "paypal_order_transaction`
			WHERE `paypal_order_id` = " . (int)$paypal_order_id . "
			AND `transaction_type` = 'REFUND'
			AND `status` = 'COMPLETED'
		");

		return (float)($query->row['total'] ?? 0.00);
	}

	/**
	 * Sum of refunds against a specific capture_id.
	 * Used by the admin refund panel to know how much has already been refunded.
	 */
	public function getTotalRefundedByCaptureId(string $capture_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total`
			FROM `" . DB_PREFIX . "paypal_order_transaction`
			WHERE `capture_id` = '" . $this->db->escape($capture_id) . "'
			AND `transaction_type` = 'REFUND'
			AND `status` = 'COMPLETED'
		");

		return (float)($query->row['total'] ?? 0.00);
	}

	// -------------------------------------------------------------------------
	// DB writes — paypal_order_transaction
	// -------------------------------------------------------------------------

	/**
	 * Insert a transaction row.
	 * Returns the new paypal_order_transaction_id.
	 */
	public function saveTransaction(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "paypal_order_transaction` SET
			`paypal_order_id` = " . (int)$data['paypal_order_id'] . ",
			`pp_order_id` = '" . $this->db->escape($data['pp_order_id'] ?? '') . "',
			`capture_id` = '" . $this->db->escape($data['capture_id'] ?? '') . "',
			`transaction_type` = '" . $this->db->escape($data['transaction_type']) . "',
			`status` = '" . $this->db->escape($data['status'] ?? '') . "',
			`amount` = " . (float)($data['amount'] ?? 0.00) . ",
			`currency_code` = '" . $this->db->escape($data['currency_code'] ?? '') . "',
			`note` = '" . $this->db->escape($data['note'] ?? '') . "',
			`raw_response` = '" . $this->db->escape($data['raw_response'] ?? '') . "',
			`created` = NOW()
		");

		return $this->db->getLastId();
	}

	// -------------------------------------------------------------------------
	// PayPal REST API — authentication
	// -------------------------------------------------------------------------

	/**
	 * Exchange client credentials for an OAuth2 access token.
	 * Returns the token string, or false on failure.
	 */
	public function getAccessToken(): string|false {
		$sandbox = (bool)$this->config->get('pp_express_sandbox');
		$client_id = $sandbox ? $this->config->get('pp_express_sandbox_client_id') : $this->config->get('pp_express_client_id');
		$secret = $sandbox ? $this->config->get('pp_express_sandbox_client_secret') : $this->config->get('pp_express_client_secret');

		$endpoint = $sandbox ? 'https://api-m.sandbox.paypal.com/v1/oauth2/token' : 'https://api-m.paypal.com/v1/oauth2/token';

		$response = $this->curlPost($endpoint, 'grant_type=client_credentials', [
			CURLOPT_USERPWD    => $client_id . ':' . $secret,
			CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
		]);

		if (isset($response['access_token'])) {
			return $response['access_token'];
		}

		$this->log($response, 'getAccessToken failed');

		return false;
	}

	// -------------------------------------------------------------------------
	// PayPal REST API — Orders v2
	// -------------------------------------------------------------------------

	/**
	 * Create an order via POST /v2/checkout/orders.
	 * $payload is the full PHP array; it will be JSON-encoded before sending.
	 * Returns the decoded response array, or false on failure.
	 */
	public function createPayPalOrder(array $payload): array|false {
		$token = $this->getAccessToken();

		if (!$token) {
			return false;
		}

		$response = $this->curlPost($this->endpoint('v2/checkout/orders'), json_encode($payload), $this->authHeaders($token));

		$this->log($response, 'createPayPalOrder');

		return $response ?: false;
	}

	/**
	 * Retrieve an order via GET /v2/checkout/orders/{id}.
	 */
	public function getPayPalOrderDetails(string $pp_order_id): array|false {
		$token = $this->getAccessToken();

		if (!$token) {
			return false;
		}

		$response = $this->curlGet($this->endpoint('v2/checkout/orders/' . $pp_order_id), $this->authHeaders($token));

		$this->log($response, 'getPayPalOrderDetails');

		return $response ?: false;
	}

	/**
	 * Capture payment for an approved order via POST /v2/checkout/orders/{id}/capture.
	 */
	public function capturePayPalOrder(string $pp_order_id): array|false {
		$token = $this->getAccessToken();

		if (!$token) {
			return false;
		}

		$response = $this->curlPost($this->endpoint('v2/checkout/orders/' . $pp_order_id . '/capture'), '{}', $this->authHeaders($token));

		$this->log($response, 'capturePayPalOrder');

		return $response ?: false;
	}

	/**
	 * Authorize payment for an approved order via POST /v2/checkout/orders/{id}/authorize.
	 */
	public function authorizePayPalOrder(string $pp_order_id): array|false {
		$token = $this->getAccessToken();

		if (!$token) {
			return false;
		}

		$response = $this->curlPost($this->endpoint('v2/checkout/orders/' . $pp_order_id . '/authorize'), '{}', $this->authHeaders($token));

		$this->log($response, 'authorizePayPalOrder');

		return $response ?: false;
	}

	// -------------------------------------------------------------------------
	// PayPal REST API — Payments v2 (post-capture actions)
	// -------------------------------------------------------------------------

	/**
	 * Refund a captured payment via POST /v2/payments/captures/{id}/refund.
	 * Pass amount in $data to do a partial refund, or empty array for full refund.
	 *
	 * Example $data for partial refund:
	 *   ['amount' => ['value' => '10.00', 'currency_code' => 'GBP'], 'note_to_payer' => '...']
	 */
	public function refundCapture(string $capture_id, array $data = []): array|false {
		$token = $this->getAccessToken();

		if (!$token) {
			return false;
		}

		$body = empty($data) ? '{}' : json_encode($data);

		$response = $this->curlPost($this->endpoint('v2/payments/captures/' . $capture_id . '/refund'), $body, $this->authHeaders($token));

		$this->log($response, 'refundCapture');

		return $response ?: false;
	}

	/**
	 * Capture a previously authorized payment via POST /v2/payments/authorizations/{id}/capture.
	 * $data can include a partial amount; pass empty array to capture the full authorized amount.
	 */
	public function captureAuthorization(string $authorization_id, array $data = []): array|false {
		$token = $this->getAccessToken();

		if (!$token) {
			return false;
		}

		$body = empty($data) ? '{}' : json_encode($data);

		$response = $this->curlPost($this->endpoint('v2/payments/authorizations/' . $authorization_id . '/capture'), $body, $this->authHeaders($token));

		$this->log($response, 'captureAuthorization');

		return $response ?: false;
	}

	/**
	 * Void an authorization via POST /v2/payments/authorizations/{id}/void.
	 */
	public function voidAuthorization(string $authorization_id): array|false {
		$token = $this->getAccessToken();

		if (!$token) {
			return false;
		}

		$response = $this->curlPost($this->endpoint('v2/payments/authorizations/' . $authorization_id . '/void'), '{}', $this->authHeaders($token));

		$this->log($response, 'voidAuthorization');

		return $response ?: false;
	}

	// -------------------------------------------------------------------------
	// PayPal REST API — Webhooks v2
	// -------------------------------------------------------------------------

	/**
	 * Verify a webhook notification signature via POST /v2/notifications/verify-webhook-signature.
	 * Returns true if PayPal confirms the signature is valid.
	 *
	 * $headers must include: PAYPAL-AUTH-ALGO, PAYPAL-CERT-URL,
	 *                         PAYPAL-TRANSMISSION-ID, PAYPAL-TRANSMISSION-SIG,
	 *                         PAYPAL-TRANSMISSION-TIME
	 */
	public function verifyWebhookSignature(array $headers, string $raw_body, string $webhook_id): bool {
		$token = $this->getAccessToken();

		if (!$token) {
			return false;
		}

		$payload = [
			'auth_algo'         => $headers['PAYPAL-AUTH-ALGO'] ?? '',
			'cert_url'          => $headers['PAYPAL-CERT-URL'] ?? '',
			'transmission_id'   => $headers['PAYPAL-TRANSMISSION-ID'] ?? '',
			'transmission_sig'  => $headers['PAYPAL-TRANSMISSION-SIG'] ?? '',
			'transmission_time' => $headers['PAYPAL-TRANSMISSION-TIME'] ?? '',
			'webhook_id'        => $webhook_id,
			'webhook_event'     => json_decode($raw_body, true),
		];

		$response = $this->curlPost($this->endpoint('v2/notifications/verify-webhook-signature'), json_encode($payload), $this->authHeaders($token));

		$this->log($response, 'verifyWebhookSignature');

		return isset($response['verification_status']) && $response['verification_status'] === 'SUCCESS';
	}

	// -------------------------------------------------------------------------
	// Utility
	// -------------------------------------------------------------------------

	/**
	 * List of currencies supported by PayPal Orders v2.
	 */
	public function getCurrencies(): array {
		return [
			'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS',
			'JPY', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'SGD',
			'SEK', 'CHF', 'TWD', 'THB', 'TRY', 'USD', 'INR',
		];
	}

	/**
	 * Write a debug log entry (respects the pp_express_debug config flag).
	 */
	public function log(mixed $data, string $title = '', bool $force = false): void {
		if ($this->config->get('pp_express_debug') || $force) {
			$log = new Log('pp_express.log');
			$log->write('PayPal Express (' . $title . '): ' . json_encode($data));
		}
	}

	// -------------------------------------------------------------------------
	// Private helpers
	// -------------------------------------------------------------------------

	/**
	 * Build a fully-qualified PayPal REST API endpoint URL.
	 */
	private function endpoint(string $path): string {
		$base = $this->config->get('pp_express_sandbox') ? 'https://api-m.sandbox.paypal.com/' : 'https://api-m.paypal.com/';

		return $base . ltrim($path, '/');
	}

	/**
	 * Standard JSON + Bearer auth headers for REST calls.
	 */
	private function authHeaders(string $token): array {
		return [
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
				'Authorization: Bearer ' . $token,
			],
		];
	}

	/**
	 * cURL POST helper. Returns decoded JSON response as array, or false.
	 */
	private function curlPost(string $url, string $body, array $extra_opts = []): array|false {
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

		if (curl_errno($ch) !== CURLE_OK) {
			$this->log(['curl_error' => curl_error($ch), 'url' => $url], 'curlPost failed', true);
			curl_close($ch);
			return false;
		}

		curl_close($ch);

		$decoded = json_decode($response, true);

		return is_array($decoded) ? $decoded : false;
	}

	/**
	 * cURL GET helper. Returns decoded JSON response as array, or false.
	 */
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
			$this->log(['curl_error' => curl_error($ch), 'url' => $url], 'curlGet failed', true);
			curl_close($ch);
			return false;
		}

		curl_close($ch);

		$decoded = json_decode($response, true);

		return is_array($decoded) ? $decoded : false;
	}
}
