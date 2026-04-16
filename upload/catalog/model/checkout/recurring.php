<?php
class ModelCheckoutRecurring extends Model {

	public function create(int $order_id, string $description, array $item = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring` SET order_id = '" . (int)$order_id . "', created = NOW(), status = '6', product_id = '" . (int)$item['product_id'] . "', product_name = '" . $this->db->escape((string)$item['name']) . "', product_quantity = '" . $this->db->escape((int)$item['quantity']) . "', profile_id = '" . (int)$item['profile_id'] . "', profile_name = '" . $this->db->escape((string)$item['profile_name']) . "', profile_description = '" . $this->db->escape((string)$description) . "', recurring_frequency = '" . $this->db->escape($item['recurring_frequency']) . "', recurring_cycle = '" . (int)$item['recurring_cycle'] . "', recurring_duration = '" . (int)$item['recurring_duration'] . "', recurring_price = '" . (double)$item['recurring_price'] . "', trial = '" . (int)$item['recurring_trial'] . "', trial_frequency = '" . $this->db->escape($item['recurring_trial_cycle']) . "', trial_cycle = '" . (int)$item['recurring_trial_cycle'] . "', trial_duration = '" . (int)$item['recurring_trial_duration'] . "', trial_price = '" . (double)$item['recurring_trial_price'] . "', profile_reference = ''");
	}

	public function addReference(int $recurring_id, string $reference): bool {
		$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET profile_reference = '" . $this->db->escape((string)$reference) . "' WHERE order_recurring_id = '" . (int)$recurring_id . "'");

		return ($this->db->countAffected() > 0) ? true : false;
	}
}
