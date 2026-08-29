<?php
/**
 * Class ModelToolDataRetention
 *
 * Purge queries for GDPR/UK-PECR data retention.
 * Used by both the admin UI controller and the CLI cron.php entry point.
 *
 * Retention periods:
 *   - Registration IP (nc_customer.ip)  : 90 days
 *   - Login IP log (nc_customer_ip)     : 90 days
 *   - Online session tracker            :  2 hours (housekeeping)
 *   - Soft-deleted accounts             :  2 years
 *
 * @package NivoCart
 */
class ModelToolDataRetention extends Model {
	/**
	 * Anonymise the registration IP for accounts created more than 90 days ago.
	 *
	 * @return int Rows affected
	 */
	public function purgeIpColumns(): int {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `ip` = '' WHERE `ip` != '' AND `date_added` < NOW() - INTERVAL 90 DAY");

		return $this->db->countAffected();
	}

	/**
	 * Delete login IP log rows older than 90 days.
	 *
	 * @return int Rows affected
	 */
	public function purgeIpLog(): int {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_ip` WHERE `date_added` < NOW() - INTERVAL 90 DAY");

		return $this->db->countAffected();
	}

	/**
	 * Delete stale online-session tracker rows (older than 2 hours).
	 * nc_customer_online uses ip as PRIMARY KEY — rows are overwritten on each
	 * page visit, so date_added reflects last activity time.
	 *
	 * @return int Rows affected
	 */
	public function purgeOnlineSessions(): int {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_online` WHERE `date_added` < NOW() - INTERVAL 2 HOUR");

		return $this->db->countAffected();
	}

	/**
	 * Hard-delete soft-deleted customer accounts older than 2 years.
	 *
	 * @return int Rows affected
	 */
	public function purgeDeletedAccounts(): int {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "customer_deleted` WHERE `date_added` < NOW() - INTERVAL 2 YEAR");

		return $this->db->countAffected();
	}

	/**
	 * Return the most recent log entry for each task.
	 *
	 * @return array Keyed by task identifier
	 */
	public function getLastRuns(): array {
		$query = $this->db->query("SELECT `task`, `rows_affected`, `status`, `message`, `date_added` FROM `" . DB_PREFIX . "cron_log` WHERE `log_id` IN (SELECT MAX(`log_id`) FROM `" . DB_PREFIX . "cron_log` GROUP BY `task`) ORDER BY `task` ASC");

		$results = [];

		foreach ($query->rows as $row) {
			$results[$row['task']] = $row;
		}

		return $results;
	}

	/**
	 * Return the most recent cron log entries (newest first).
	 *
	 * @param int $limit
	 * @return array
	 */
	public function getRecentLog(int $limit = 30): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cron_log` ORDER BY `log_id` DESC LIMIT " . (int)$limit);

		return $query->rows;
	}
}
