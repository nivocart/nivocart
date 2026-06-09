<?php
/**
 * Class ModelPaymentStripePayments
 *
 * @package NivoCart
 */
class ModelPaymentStripePayments extends Model {
	/** Error array Placeholder */

	public function getMethod($address, $total): array {
		$this->language->load('payment/stripe_payments');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE geo_zone_id = '" . (int)$this->config->get('stripe_payments_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('stripe_payments_total') > 0 && $this->config->get('stripe_payments_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('stripe_payments_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'stripe_payments',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('stripe_payments_sort_order')
			];
		}

		return $method_data;
	}

	// Check if order already completed (idempotency guard)
	public function isOrderComplete(int $order_id, int $paid_status_id): bool {
		$result = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "' LIMIT 1");

		return $result->num_rows && (int)$result->row['order_status_id'] === $paid_status_id;
	}

	// Complete the order via webhook (async path)
	public function completeOrderByWebhook(int $order_id, int $status_id, string $payment_intent_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $status_id . "', date_modified = NOW() WHERE order_id = '" . $order_id . "'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET order_id = '" . $order_id . "', order_status_id = '" . $status_id . "', notify = '0', `comment` = 'Payment confirmed via Stripe webhook (intent: " . $this->db->escape($payment_intent_id) . ")', date_added = NOW()");
	}

	// Mark order as failed
	public function failOrderByWebhook(int $order_id, int $status_id, string $message, string $code): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $status_id . "', date_modified = NOW() WHERE order_id = '" . $order_id . "'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET order_id = '" . $order_id . "', order_status_id = '" . $status_id . "', notify = '0', `comment` = 'Payment failed: " . $this->db->escape($message) . " [" . $this->db->escape($code) . "]', date_added = NOW()");
	}
}
