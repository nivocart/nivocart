<?php
/**
 * Class ModelPaymentPpExpress
 *
 * Catalog model for PayPal Express — Orders v2 REST API.
 * API calls are delegated to the shared PayPalClient library.
 *
 * @package NivoCart
 */
class ModelPaymentPpExpress extends Model {
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
	// Payment method availability
	// -------------------------------------------------------------------------

	public function getMethod(array $address, float $total): array {
		$this->language->load('payment/pp_express');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = " . (int)$this->config->get('pp_express_geo_zone_id') . " AND `country_id` = " . (int)$address['country_id'] . " AND (`zone_id` = " . (int)$address['zone_id'] . " OR `zone_id` = 0)");

		$min = (float)$this->config->get('pp_express_total');
		$max = (float)$this->config->get('pp_express_total_max');

		if ($min > 0 && $total < $min) {
			$status = false;
		} elseif ($max > 0 && $total > $max) {
			$status = false;
		} elseif (!$this->config->get('pp_express_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if (!$status) {
			return [];
		}

		return [
			'code'       => 'pp_express',
			'title'      => $this->language->get('text_title'),
			'terms'      => '',
			'sort_order' => $this->config->get('pp_express_sort_order'),
		];
	}

	// -------------------------------------------------------------------------
	// PayPal REST API — delegated to System/vendor/paypal/paypal_client
	// -------------------------------------------------------------------------

	public function createPayPalOrder(array $payload): array|false {
		$response = $this->client()->createOrder($payload);
		$this->log($response, 'createPayPalOrder');
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

	public function getPayPalOrderDetails(string $pp_order_id): array|false {
		$response = $this->client()->getOrderDetails($pp_order_id);
		$this->log($response, 'getPayPalOrderDetails');
		return $response;
	}

	public function verifyWebhookSignature(array $headers, string $raw_body, string $webhook_id): bool {
		return $this->client()->verifyWebhookSignature($headers, $raw_body, $webhook_id);
	}

	// -------------------------------------------------------------------------
	// DB reads
	// -------------------------------------------------------------------------

	public function getPaypalOrderByOrderId(int $order_id): array|false {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_order` WHERE `order_id` = " . (int)$order_id . " LIMIT 1");

		return $query->num_rows ? $query->row : false;
	}

	public function getPaypalOrderByPPOrderId(string $pp_order_id): array|false {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_order` WHERE `pp_order_id` = '" . $this->db->escape($pp_order_id) . "' LIMIT 1");

		return $query->num_rows ? $query->row : false;
	}

	public function getTotalCaptured(int $paypal_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "paypal_order_transaction` WHERE `paypal_order_id` = " . (int)$paypal_order_id . " AND `transaction_type` = 'CAPTURE' AND `status` IN ('COMPLETED', 'PENDING')");

		return (float)($query->row['total'] ?? 0.00);
	}

	public function getTotalRefunded(int $paypal_order_id): float {
		$query = $this->db->query("SELECT SUM(`amount`) AS `total` FROM `" . DB_PREFIX . "paypal_order_transaction` WHERE `paypal_order_id` = " . (int)$paypal_order_id . " AND `transaction_type` = 'REFUND' AND `status` = 'COMPLETED'");

		return (float)($query->row['total'] ?? 0.00);
	}

	// -------------------------------------------------------------------------
	// DB writes
	// -------------------------------------------------------------------------

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

	public function updatePaypalOrderStatus(int $order_id, string $status, string $capture_id = ''): void {
		$set = "`status` = '" . $this->db->escape($status) . "', `modified` = NOW()";

		if ($capture_id !== '') {
			$set .= ", `capture_id` = '" . $this->db->escape($capture_id) . "'";
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "paypal_order` SET " . $set . " WHERE `order_id` = " . (int)$order_id);
	}

	// -------------------------------------------------------------------------
	// Webhook event processing
	// -------------------------------------------------------------------------

	/**
	 * Process a verified webhook event payload.
	 * Updates the paypal_order and paypal_order_transaction tables,
	 * then updates the NivoCart order status accordingly.
	 *
	 * Returns the NivoCart order_id on success, false if unhandled or unknown.
	 */
	public function processWebhookEvent(array $event): int|false {
		$event_type = $event['event_type'] ?? '';
		$resource = $event['resource'] ?? [];

		$this->log($event, 'webhook: ' . $event_type);

		// Resolve pp_order_id and order from resource
		$pp_order_id = $resource['supplementary_data']['related_ids']['order_id'] ?? $resource['id'] ?? '';

		if (!$pp_order_id) {
			return false;
		}

		$paypal_order = $this->getPaypalOrderByPPOrderId($pp_order_id);

		// Some events (e.g. PAYMENT.CAPTURE.*) use the capture/auth id, not the order id
		if (!$paypal_order && !empty($resource['id'])) {
			// Try resolving via capture_id stored in paypal_order
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "paypal_order` WHERE `capture_id` = '" . $this->db->escape($resource['id']) . "' LIMIT 1");

			$paypal_order = $query->num_rows ? $query->row : false;
		}

		if (!$paypal_order) {
			return false;
		}

		$order_id = (int)$paypal_order['order_id'];
		$paypal_order_id = (int)$paypal_order['paypal_order_id'];
		$currency = $resource['amount']['currency_code'] ?? $paypal_order['currency_code'];
		$amount = (float)($resource['amount']['value'] ?? 0);
		$capture_id = $resource['id'] ?? '';
		$status = $resource['status'] ?? '';

		switch ($event_type) {

			case 'PAYMENT.CAPTURE.COMPLETED':
				$this->saveTransaction([
					'paypal_order_id'  => $paypal_order_id,
					'pp_order_id'      => $pp_order_id,
					'capture_id'       => $capture_id,
					'transaction_type' => 'CAPTURE',
					'status'           => 'COMPLETED',
					'amount'           => $amount,
					'currency_code'    => $currency,
					'note'             => 'Webhook: capture completed',
					'raw_response'     => json_encode($resource),
				]);
				$this->updatePaypalOrderStatus($order_id, 'COMPLETED', $capture_id);
				$this->updateNivoCartOrderStatus($order_id, 'pp_express_completed_status_id', $resource);
				break;

			case 'PAYMENT.CAPTURE.PENDING':
				$this->saveTransaction([
					'paypal_order_id'  => $paypal_order_id,
					'pp_order_id'      => $pp_order_id,
					'capture_id'       => $capture_id,
					'transaction_type' => 'CAPTURE',
					'status'           => 'PENDING',
					'amount'           => $amount,
					'currency_code'    => $currency,
					'note'             => 'Webhook: capture pending',
					'raw_response'     => json_encode($resource),
				]);
				$this->updatePaypalOrderStatus($order_id, 'PENDING', $capture_id);
				$this->updateNivoCartOrderStatus($order_id, 'pp_express_pending_status_id', $resource);
				break;

			case 'PAYMENT.CAPTURE.DENIED':
				$this->saveTransaction([
					'paypal_order_id'  => $paypal_order_id,
					'pp_order_id'      => $pp_order_id,
					'capture_id'       => $capture_id,
					'transaction_type' => 'CAPTURE',
					'status'           => 'DENIED',
					'amount'           => $amount,
					'currency_code'    => $currency,
					'note'             => 'Webhook: capture denied',
					'raw_response'     => json_encode($resource),
				]);
				$this->updatePaypalOrderStatus($order_id, 'DENIED');
				$this->updateNivoCartOrderStatus($order_id, 'pp_express_denied_status_id', $resource);
				break;

			case 'PAYMENT.CAPTURE.REFUNDED':
				$this->saveTransaction([
					'paypal_order_id'  => $paypal_order_id,
					'pp_order_id'      => $pp_order_id,
					'capture_id'       => $capture_id,
					'transaction_type' => 'REFUND',
					'status'           => 'COMPLETED',
					'amount'           => $amount,
					'currency_code'    => $currency,
					'note'             => 'Webhook: refund',
					'raw_response'     => json_encode($resource),
				]);
				$this->updateNivoCartOrderStatus($order_id, 'pp_express_refunded_status_id', $resource);
				break;

			case 'PAYMENT.AUTHORIZATION.VOIDED':
				$this->saveTransaction([
					'paypal_order_id'  => $paypal_order_id,
					'pp_order_id'      => $pp_order_id,
					'capture_id'       => $capture_id,
					'transaction_type' => 'VOID',
					'status'           => 'VOIDED',
					'amount'           => 0,
					'currency_code'    => $currency,
					'note'             => 'Webhook: authorization voided',
					'raw_response'     => json_encode($resource),
				]);
				$this->updatePaypalOrderStatus($order_id, 'VOIDED');
				$this->updateNivoCartOrderStatus($order_id, 'pp_express_voided_status_id', $resource);
				break;

			default:
				// Unhandled event type — logged above, no DB action needed
				return false;
		}

		return $order_id;
	}

	// -------------------------------------------------------------------------
	// Utility
	// -------------------------------------------------------------------------

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
	 * Update the NivoCart oc_order status using a config-mapped status ID.
	 * Adds an order history entry with the PayPal event type as the comment.
	 */
	private function updateNivoCartOrderStatus(int $order_id, string $status_config_key, array $resource): void {
		$status_id = (int)$this->config->get($status_config_key);

		if (!$status_id) {
			return;
		}

		$this->load->model('checkout/order');

		$comment = 'PayPal: ' . ($resource['status'] ?? '') . ($resource['id'] ? ' (' . $resource['id'] . ')' : '');

		$this->model_checkout_order->confirm($order_id, $status_id, $comment, false);
	}
}
