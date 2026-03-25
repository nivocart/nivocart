<?php
class DB {
	/**
	 * @var object
	 */
	private object $driver;

	/**
	 * Constructor
	 *
	 * @param string $driver
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param int $port
	 */
	public function __construct(string $driver, string $hostname, string $username, string $password, string $database, int $port) {
		$file = DIR_DATABASE . $driver . '.php';

		if (file_exists($file)) {
			require_once($file);

			$class = 'DB' . $driver;

			$this->driver = new $class($hostname, $username, $password, $database, $port);
		} else {
			throw new \Exception('Error: Could not load database driver ' . $driver . '!');
		}
	}

	/**
	 * Query
	 *
	 * @param string $sql SQL statement to be executed
	 *
	 * @return mixed
	 */
	public function query(string $sql, array $params = []) {
		return $this->driver->query($sql, $params);
	}

	/**
	 * Escape
	 *
	 * @param string $value Value to be protected against SQL injections
	 *
	 * @return string Returns escaped value
	 */
	public function escape(string $value): string {
		return $this->driver->escape($value);
	}

	/**
	 * Count Affected
	 *
	 * Gets the total number of affected rows from the last query
	 *
	 * @return int returns the total number of affected rows
	 */
	public function countAffected(): int {
		return $this->driver->countAffected();
	}

	/**
	 * Get Last Id
	 *
	 * Get the last ID gets the primary key that was returned after creating a row in a table.
	 *
	 * @return int Returns last ID
	 */
	public function getLastId(): int {
		return $this->driver->getLastId();
	}

	/**
	 * Is Connected
	 *
	 * Checks if a DB connection is active.
	 *
	 * @return bool
	 */
	public function isConnected(): bool {
		return $this->driver->isConnected();
	}
}
