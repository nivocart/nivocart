<?php
class Dbmemory {
	/**
	 * @var object
	 */
	private object $db;

	private static array $results = [];
	private static array $escaped = [];

	/**
	 * Constructor
	 *
	 * @param 	$registry
	 */
	public function __construct($db) {
		$this->db = $db;
	}

	/**
	 * Query
	 *
	 * @param $sql
	 */
	public function query($sql) {
		$h = md5($sql);

		if (!isset(self::$results[$h])) {
			self::$results[$h] = $this->db->query($sql);
		}

		return self::$results[$h];
	}

	/**
	 * Escape
	 *
	 * @param string $string
	 */
	public function escape($string) {
		if (!isset(self::$escaped[$string])) {
			self::$escaped[$string] = $this->db->escape($string);
		}

		return self::$escaped[$string];
	}

	/**
	 * Count Affected
	 */
	public function countAffected() {
		return $this->db->countAffected();
	}

	/**
	 * get Last Id
	 */
	public function getLastId() {
		return $this->db->getLastId();
	}
}
