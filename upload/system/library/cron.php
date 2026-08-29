<?php
/**
 * Class Cron
 *
 * Lightweight logging helper for CLI data-retention tasks.
 * Writes each task result to nc_cron_log for audit and admin display.
 *
 * @package NivoCart
 */
class Cron {
	private $db;

	public function __construct($registry) {
		$this->db = $registry->get('db');
	}

	/**
	 * Log a cron task result to nc_cron_log.
	 *
	 * @param string $task          Short task identifier (e.g. 'purge_ip_log')
	 * @param int    $rows_affected Number of rows affected by the task
	 * @param string $status        'success' or 'error'
	 * @param string $message       Optional detail or error message
	 */
	public function log(string $task, int $rows_affected, string $status = 'success', string $message = ''): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "cron_log` SET `task` = '" . $this->db->escape($task) . "', `rows_affected` = '" . (int)$rows_affected . "', `status` = '" . $this->db->escape($status) . "', `message` = '" . $this->db->escape($message) . "', `date_added` = NOW()");
	}
}
