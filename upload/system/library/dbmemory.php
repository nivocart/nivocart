<?php
/**
 * Dbmemory class is a query result memorization layer.
 *
 * What it actually does:
 * ----------------------
 * - It wraps your real database object ($this->db).
 * - Every SQL query is hashed with md5().
 * - If that exact query has been run before in the same request, it returns the stored result instead of hitting the database again.
 * - The results live in static arrays, meaning they persist for the lifetime of the PHP request/process only.
 *
 * A few things worth noting:
 * --------------------------
 * - The static keyword on $results and $escaped means all instances of Dbmemory share the same cache pool within a request, which is intentional and correct here.
 * - It's safe for SELECT queries, but countAffected() and getLastId() pass straight through, which suggests write queries aren't being memorized — good, because caching those would be dangerous.
 * - If the same SEO URL query fires multiple times per page load (e.g. in a loop or across multiple components), this will genuinely save you repeated DB round trips.
 *
 * USAGE: Currently used in conjonction with SEO URL Rewrite to accelerate Catalog queries.
 */
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
		$hash = md5($sql);

		if (!isset(self::$results[$hash])) {
			self::$results[$hash] = $this->db->query($sql);
		}

		return self::$results[$hash];
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
