<?php
/**
 * Class ModelSaleRecurring
 *
 * @package NivoCart
 */
class ModelSaleRecurring extends Model {
	/**
	 * Functions Add, Edit, Delete, Get
	 */
	public function getProfiles(array $data = []): array {
		$sql = "SELECT order_recurring_id, order_id, status, created, profile_reference, CONCAT(firstname, ' ', lastname) AS `customer` FROM `" . DB_PREFIX . "order_recurring` LEFT JOIN `" . DB_PREFIX . "order` USING(`order_id`) WHERE order_recurring_id IS NOT NULL";

		if (!empty($data['filter_order_recurring_id'])) {
			$sql .= " AND order_recurring_id = " . (int)$data['filter_order_recurring_id'];
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = " . (int)$data['filter_order_id'];
		}

		if (!empty($data['filter_payment_reference'])) {
			$sql .= " AND profile_reference LIKE '" . $this->db->escape($data['filter_payment_reference']) . "%'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_created'])) {
			$sql .= " AND DATE(created) = DATE('" . $this->db->escape($data['filter_created']) . "')";
		}

		if (!empty($data['filter_status'])) {
			$sql .= " AND status = " . (int)$data['filter_status'];
		}

		$sort_data = [
			'order_recurring_id',
			'order_id',
			'profile_reference',
			'customer',
			'created',
			'status'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY order_recurring_id";
		}

		if (isset($data['order']) && ($data['order'] === 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$profiles = [];

		$results = $this->db->query($sql)->rows;

		foreach ($results as $result) {
			$profiles[] = [
				'order_recurring_id' => $result['order_recurring_id'],
				'order_id'           => $result['order_id'],
				'status'             => $this->getStatus($result['status']),
				'created'            => $result['created'],
				'profile_reference'  => $result['profile_reference'],
				'customer'           => $result['customer']
			];
		}

		return $profiles;
	}

	public function getProfile(int $order_recurring_id): array {
		$profile = [];

		$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE order_recurring_id = " . (int)$order_recurring_id)->row;

		if ($result) {
			$profile = [
				'order_recurring_id'  => $result['order_recurring_id'],
				'order_id'            => $result['order_id'],
				'status'              => $this->getStatus($result['status']),
				'status_id'           => $result['status'],
				'profile_id'          => $result['profile_id'],
				'profile_name'        => $result['profile_name'],
				'profile_description' => $result['profile_description'],
				'profile_reference'   => $result['profile_reference'],
				'product_name'        => $result['product_name'],
				'product_quantity'    => $result['product_quantity']
			];
		}

		return $profile;
	}

	public function getProfileTransactions(int $order_recurring_id): array {
		$results = $this->db->query("SELECT amount, `type`, created FROM `" . DB_PREFIX . "order_recurring_transaction` WHERE order_recurring_id = " . (int)$order_recurring_id . " ORDER BY created DESC")->rows;
		$transactions = [];

		foreach ($results as $result) {
			$type = match((int)$result['type']) {
				0       => $this->language->get('text_transaction_created'),
				1       => $this->language->get('text_transaction_payment'),
				2       => $this->language->get('text_transaction_outstanding_payment'),
				3       => $this->language->get('text_transaction_skipped'),
				4       => $this->language->get('text_transaction_failed'),
				5       => $this->language->get('text_transaction_cancelled'),
				6       => $this->language->get('text_transaction_suspended'),
				7       => $this->language->get('text_transaction_suspended_failed'),
				8       => $this->language->get('text_transaction_outstanding_failed'),
				9       => $this->language->get('text_transaction_expired'),
				default => ''
			};

			$transactions[] = [
				'created' => $result['created'],
				'amount'  => $result['amount'],
				'type'    => $type
			];
		}

		return $transactions;
	}

	private function getStatus($status) {
		$status = $status ?? '';

		return match((int)$status) {
			1       => $this->language->get('text_status_inactive'),
			2       => $this->language->get('text_status_active'),
			3       => $this->language->get('text_status_suspended'),
			4       => $this->language->get('text_status_cancelled'),
			5       => $this->language->get('text_status_expired'),
			6       => $this->language->get('text_status_pending'),
			default => ''
		};
	}

	public function getTotalProfiles(array $data = []) {
		$sql = "SELECT COUNT(*) AS `profile_count` FROM `" . DB_PREFIX . "order_recurring` LEFT JOIN `" . DB_PREFIX . "order` USING(`order_id`) WHERE order_recurring_id IS NOT NULL";

		if (!empty($data['filter_order_recurring_id'])) {
			$sql .= " AND order_recurring_id = " . (int)$data['filter_order_recurring_id'];
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = " . (int)$data['filter_order_id'];
		}

		if (!empty($data['filter_payment_reference'])) {
			$sql .= " AND profile_reference LIKE '" . $this->db->escape($data['filter_payment_reference']) . "%'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_created'])) {
			$sql .= " AND DATE(created) = DATE('" . $this->db->escape($data['filter_created']) . "')";
		}

		if (!empty($data['filter_status'])) {
			$sql .= " AND status = " . (int)$data['filter_status'];
		}

		$result = $this->db->query($sql);

		return $result->row['profile_count'];
	}

	public function addOrderRecurringTransaction(int $order_recurring_id, int $type, $amount = 0): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id` = '" . (int)$order_recurring_id . "', `type` = '" . (int)$type . "', `amount` = " . (float)$amount) . ", `created` = NOW()";
	}

	public function updateOrderRecurringStatus(int $order_recurring_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = '" . (int)$status . "' WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "' LIMIT 0,1");
	}
}
