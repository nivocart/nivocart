<?php
final class DBMySQLi {
	/**
	 * @var ?\mysqli
	 */
	private ?\mysqli $connection;

	/**
	 * Constructor
	 *
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param int    $port
	 */
	public function __construct(string $hostname, string $username, string $password, string $database, int $port = 0) {
		if (!$port) {
			$port = 3306;
		}

		try {
			mysqli_report(MYSQLI_REPORT_STRICT);

			$this->connection = @new \mysqli($hostname, $username, $password, $database, $port);

		} catch (\mysqli_sql_exception $e) {
			throw new \Exception('Error: Could not make a database link using ' . (string)$username . '@' . (string)$hostname . '!');
		}

		$this->connection->set_charset("utf8mb4");

		// Set connection session parameters and sync date
		$this->query("SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
		$this->query("SET FOREIGN_KEY_CHECKS = 0");
		$this->query("SET `time_zone` = '" . $this->escape(date('P')) . "'");
	}

	/**
	 * Query
	 *
	 * @param string $sql
	 *
	 * @return mixed
	 */
	public function query(string $sql) {
		try {
			$query = $this->connection->query($sql);

			if ($query instanceof \mysqli_result) {
				$data = [];

				while ($row = $query->fetch_assoc()) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->num_rows = $query->num_rows;
				$result->row = $data[0] ?? [];
				$result->rows = $data;

				$query->close();

				unset($data);

				return $result;
			} else {
				return true;
			}
		} catch (\mysqli_sql_exception $e) {
			throw new \Exception('Error: ' . $this->connection->error . '<br/>Error No: ' . $this->connection->errno . '<br/>' . $sql);
		}
	}

	/**
	 * Escape
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function escape(string $value): string {
		return $this->connection->real_escape_string($value);
	}

	/**
	 * Count Affected
	 *
	 * @return int
	 */
	public function countAffected(): int {
		return $this->connection->affected_rows;
	}

	/**
	 * Get Last Id
	 *
	 * @return int
	 */
	public function getLastId(): int {
		return $this->connection->insert_id;
	}

	/**
	 * Is Connected
	 *
	 * @return bool
	 */
	public function isConnected(): bool {
		return $this->connection !== null;
	}

	/**
	 * Destructor
	 *
	 * Closes the DB connection when this object is destroyed.
	 */
	public function __destruct() {
		if ($this->connection) {
			$this->connection->close();

			$this->connection = null;
		}
	}
}
