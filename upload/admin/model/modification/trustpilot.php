<?php
/**
 * Class ModelModificationTrustpilot
 *
 * Helpers for the Trustpilot modification admin page (reads nc_trustpilot_invitation).
 *
 * Location: admin/model/modification/trustpilot.php
 *
 * @package NivoCart
 */
class ModelModificationTrustpilot extends Model {
	/**
	 * Latest invitation requests, newest first.
	 *
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getInvitations(int $limit = 100): array {
		$query = $this->db->query("SELECT ti.`invitation_id`, ti.`order_id`, ti.`reference`, ti.`status`, ti.`message`, ti.`date_added`, CONCAT(o.`firstname`, ' ', o.`lastname`) AS `customer` FROM `" . DB_PREFIX . "trustpilot_invitation` ti LEFT JOIN `" . DB_PREFIX . "order` o ON (ti.`order_id` = o.`order_id`) ORDER BY ti.`invitation_id` DESC LIMIT " . (int)$limit);

		return $query->rows;
	}

	/**
	 * Invitation requests sent so far this calendar month (test invitations included,
	 * because Trustpilot counts them too). This is an estimate: Trustpilot keeps the real count.
	 *
	 * @return int
	 */
	public function getMonthlyCount(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "trustpilot_invitation` WHERE `status` IN ('sent', 'exported') AND `date_added` >= DATE_FORMAT(NOW(), '%Y-%m-01')");

		return (int)$query->row['total'];
	}

	/**
	 * Completed orders for the Trustpilot CSV export, oldest first. Orders that were already
	 * invited by email are left out, and so are orders already exported unless asked for.
	 *
	 * @param string $date_from        Y-m-d (order date)
	 * @param string $date_to          Y-m-d (order date)
	 * @param bool   $include_exported Also return orders that were exported before
	 * @param int    $limit
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getExportOrders(string $date_from, string $date_to, bool $include_exported = false, int $limit = 5000): array {
		$statuses = $include_exported ? "'sent'" : "'sent', 'exported'";

		$query = $this->db->query("SELECT o.`order_id`, o.`firstname`, o.`lastname`, o.`email` FROM `" . DB_PREFIX . "order` o WHERE o.`order_status_id` = '" . (int)$this->config->get('config_complete_status_id') . "' AND o.`order_status_id` > '0' AND DATE(o.`date_added`) >= '" . $this->db->escape($date_from) . "' AND DATE(o.`date_added`) <= '" . $this->db->escape($date_to) . "' AND NOT EXISTS (SELECT 1 FROM `" . DB_PREFIX . "trustpilot_invitation` ti WHERE ti.`order_id` = o.`order_id` AND ti.`status` IN (" . $statuses . ")) ORDER BY o.`order_id` ASC LIMIT " . (int)$limit);

		return $query->rows;
	}

	/**
	 * Remember that orders were put in a CSV export, so they are not exported or invited again.
	 *
	 * @param array<int, int> $order_ids
	 *
	 * @return void
	 */
	public function addExported(array $order_ids): void {
		foreach ($order_ids as $order_id) {
			$order_id = (int)$order_id;

			$query = $this->db->query("SELECT `invitation_id` FROM `" . DB_PREFIX . "trustpilot_invitation` WHERE `order_id` = '" . $order_id . "' AND `status` != 'sent' ORDER BY `invitation_id` DESC LIMIT 1");

			if ($query->row) {
				$this->db->query("UPDATE `" . DB_PREFIX . "trustpilot_invitation` SET `status` = 'exported', `message` = 'CSV export', `date_added` = NOW() WHERE `invitation_id` = '" . (int)$query->row['invitation_id'] . "'");
			} else {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "trustpilot_invitation` SET `order_id` = '" . $order_id . "', `reference` = '" . $order_id . "', `status` = 'exported', `message` = 'CSV export', `date_added` = NOW()");
			}
		}
	}

	/**
	 * Check whether the nc_trustpilot_invitation table exists.
	 *
	 * @return bool
	 */
	public function tableExists(): bool {
		$result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "trustpilot_invitation'");

		return (bool)$result->num_rows;
	}
}
