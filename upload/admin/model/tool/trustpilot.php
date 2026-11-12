<?php
/**
 * Class ModelToolTrustpilot
 *
 * Read-only helpers for the Trustpilot admin page.
 *
 * @package NivoCart
 */
class ModelToolTrustpilot extends Model {
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
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "trustpilot_invitation` WHERE `status` = 'sent' AND `date_added` >= DATE_FORMAT(NOW(), '%Y-%m-01')");

		return (int)$query->row['total'];
	}
}
