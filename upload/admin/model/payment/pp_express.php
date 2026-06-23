<?php
/**
 * Class ModelPaymentPPExpress
 *
 * Admin model for PayPal Express — Orders v2 REST API.
 * API calls are delegated to the shared PayPalClient library.
 *
 * @package NivoCart
 */
class ModelPaymentPPExpress extends Model {
	private ?PayPalClient $client = null;

	// -------------------------------------------------------------------------
	// PayPalClient accessor
	// -------------------------------------------------------------------------

	private function client(): PayPalClient {
		if ($this->client === null) {
			require_once(DIR_SYSTEM . 'vendor/paypal/paypal.php');
			$this->client = new PayPalClient($this->config);
		}

		return $this->client;
	}

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
	// PayPal REST API — delegated to PayPalClient
	// -------------------------------------------------------------------------

	public function createPayPalOrder(array $payload): array|false {
		$response = $this->client()->createOrder($payload);
		$this->log($response, 'createPayPalOrder');
		return $response;
	}

	public function getPayPalOrderDetails(string $pp_order_id): array|false {
		$response = $this->client()->getOrderDetails($pp_order_id);
		$this->log($response, 'getPayPalOrderDetails');
		return $response;
	}

	public function capturePayPalOrder(string $pp_order_id): array|false {
		$response = $this->client()->captureOrder($pp_order_id);
		$this->log($response, 'capturePayPalOrder');
		return $response;
	}

	public function authorizePayPalOrder(string $pp_order_id): array|false {
		$response = $this->client()->authorizeOrder($pp_order_id);
		$this->log($response, 'authorizePayPalOrder');
		return $response;
	}

	public function captureAuthorization(string $authorization_id, array $data = []): array|false {
		$response = $this->client()->captureAuthorization($authorization_id, $data);
		$this->log($response, 'captureAuthorization');
		return $response;
	}

	public function voidAuthorization(string $authorization_id): array|false {
		$response = $this->client()->voidAuthorization($authorization_id);
		$this->log($response, 'voidAuthorization');
		return $response;
	}

	public function refundCapture(string $capture_id, array $data = []): array|false {
		$response = $this->client()->refundCapture($capture_id, $data);
		$this->log($response, 'refundCapture');
		return $response;
	}

	public function verifyWebhookSignature(array $headers, string $raw_body, string $webhook_id): bool {
		return $this->client()->verifyWebhookSignature($headers, $raw_body, $webhook_id);
	}

	// -------------------------------------------------------------------------
	// Utility
	// -------------------------------------------------------------------------

	public function getCurrencies(): array {
		return [
			'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS',
			'JPY', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'SGD',
			'SEK', 'CHF', 'TWD', 'THB', 'TRY', 'USD', 'INR',
		];
	}

	public function log(mixed $data, string $title = '', bool $force = false): void {
		if ($this->config->get('pp_express_debug') || $force) {
			$log = new Log('pp_express.log');
			$log->write('PayPal Express (' . $title . '): ' . json_encode($data));
		}
	}
}
