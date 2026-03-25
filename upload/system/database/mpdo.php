<?php
final class DBmPDO {
	/**
	 * @var \PDO|null
	 */
	private ?\PDO $connection;

	/**
	 * @var array<string, string>
	 */
	private array $data = [];

	/**
	 * @var $statement
	 */
	private $statement;

	/**
	 * Constructor
	 *
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param int $port
	 */
	public function __construct(string $hostname, string $username, string $password, string $database, int $port = 0) {
		if (!$port) {
			$port = 3306;
		}

		try {
			$pdo = new \PDO('mysql:host=' . $hostname . ';port=' . $port . ';dbname=' . $database . ';charset=utf8mb4', $username, $password, [\PDO::ATTR_PERSISTENT => false, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci']);
		} catch (\PDOException $e) {
			throw new \Exception('Error: Could not make a database link using ' . $username . '@' . $hostname . '!');
		}

		$this->connection = $pdo;

		$this->connection->exec("SET CHARACTER SET utf8mb4");
		$this->connection->exec("SET CHARACTER_SET_CONNECTION=utf8mb4");
		$this->connection->exec("SET SQL_MODE = 'NO_ZERO_IN_DATE,NO_ENGINE_SUBSTITUTION'");
		$this->connection->exec("SET FOREIGN_KEY_CHECKS = 0");
	}

	/**
	 * Execute
	 */
	public function execute() {
		try {
			if ($this->statement && $this->statement->execute()) {

				while ($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->row = (isset($data[0])) ? $data[0] : array();
				$result->rows = $data;
				$result->num_rows = $this->statement->rowCount();
			}

		} catch (\PDOException $e) {
			throw new \Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode());
		}
	}

	/**
	 * Query
	 *
	 * @param string $sql
	 *
	 * @return \stdClass|true
	 */
	public function query(string $sql, $params = array()) {
		$this->statement = $this->connection->prepare($sql);

		$result = false;

		try {
			if ($this->statement && $this->statement->execute($params)) {

				while ($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) {
					$data[] = $row;
				}

				$result = new \stdClass();
				$result->row = (isset($data[0]) ? $data[0] : array());
				$result->rows = $data;
				$result->num_rows = $this->statement->rowCount();

				// free up resources
				$this->statement->closeCursor();
				$this->statement = null;
			}

		} catch (\PDOException $e) {
			throw new \Exception('Error: ' . $e->getMessage() . ' Error Code : ' . $e->getCode() . ' <br />' . $sql);
		}

		if ($result) {
			return $result;
		} else {
			$result = new \stdClass();
			$result->row = array();
			$result->rows = array();
			$result->num_rows = 0;

			return $result;
		}
	}

	/**
	 * prepare
	 *
	 * @param string $sql
	 */
	public function prepare(string $sql) {
		$this->statement = $this->connection->prepare($sql);
	}

	/**
	 * Bind Param
	 *
	 * Closes the DB connection when this object is destroyed.
	 */
	public function bindParam($parameter, $variable, $data_type = \PDO::PARAM_STR, $length = 0) {
		if ($length) {
			$this->statement->bindParam($parameter, $variable, $data_type, $length);
		} else {
			$this->statement->bindParam($parameter, $variable, $data_type);
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
		return str_replace(array("\\", "\0", "\n", "\r", "\x1a", "'", '"'), array("\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"'), $value);
	}

	/**
	 * Count Affected
	 *
	 * @return int
	 */
	public function countAffected(): int {
		if ($this->statement) {
			return $this->statement->rowCount();
		} else {
			return 0;
		}
	}

	/**
	 * Get Last Id
	 *
	 * @return ?int
	 */
	public function getLastId(): ?int {
		return $this->connection->lastInsertId();
	}

	/**
	 * Is Connected
	 *
	 * @return bool
	 */
	public function isConnected(): bool {
		if ($this->connection) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Destructor
	 *
	 * Closes the DB connection when this object is destroyed.
	 */
	public function __destruct() {
		$this->connection = null;
	}
}
