<?php
class ModelAccountRecurring extends Model {

	public function getProfile(int $order_recurring_id) {
		$result = $this->db->query("SELECT orc.*, o.payment_method, o.payment_code, o.currency_code FROM `" . DB_PREFIX . "order_recurring` orc LEFT JOIN `" . DB_PREFIX . "order` o ON (orc.order_id = o.order_id) WHERE orc.order_recurring_id = '" . (int)$order_recurring_id . "' AND o.customer_id = '" . (int)$this->customer->getId() . "' LIMIT 0,1");

		if ($result->num_rows > 0) {
			$profile = $result->row;

			return $profile;
		} else {
			return false;
		}
	}

	public function getProfileByRef(string $profile_reference) {
		$profile = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE profile_reference = '" . $this->db->escape((string)$profile_reference) . "' LIMIT 0,1");

		if ($profile->num_rows > 0) {
			return $profile->row;
		} else {
			return false;
		}
	}

	public function getProfileTransactions(int $order_recurring_id) {
		$profile = $this->getProfile($order_recurring_id);

		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring_transaction` WHERE order_recurring_id = '" . (int)$order_recurring_id . "'");

		if ($results->num_rows > 0) {
			$transactions = array();

			foreach ($results->rows as $transaction) {
				$transaction['amount'] = $this->currency->format($transaction['amount'], $profile['currency_code'], 1);

				$transactions[] = $transaction;
			}

			return $transactions;
		} else {
			return false;
		}
	}

	public function getAllProfiles($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$result = $this->db->query("SELECT orc.*, o.payment_method, o.currency_id, o.currency_value FROM `" . DB_PREFIX . "order_recurring` orc LEFT JOIN `" . DB_PREFIX . "order` o ON (orc.order_id = o.order_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);

		if ($result->num_rows > 0) {
			$profiles = array();

			foreach ($result->rows as $profile) {
				$profiles[] = $profile;
			}

			return $profiles;
		} else {
			return false;
		}
	}

	public function getTotalRecurring(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "order_recurring` orc LEFT JOIN `" . DB_PREFIX . "order` o ON (orc.order_id = o.order_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "'");

		if ($query->num_rows) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function addOrderRecurringTransaction(int $order_recurring_id, int $type, $amount = 0): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET order_recurring_id = '" . (int)$order_recurring_id . "', created = NOW(), amount = '" . (double)$amount . "', `type` = '" . (int)$type . "'");
	}

	public function updateOrderRecurringStatus(int $order_recurring_id, int $status): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET status = '" . (int)$status . "' WHERE order_recurring_id = '" . (int)$order_recurring_id . "'");
	}
}
