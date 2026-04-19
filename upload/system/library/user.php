<?php
class User {
	/**
	 * @var object
	 */
	private object $db;

	/**
	 * @var object
	 */
	private object $request;

	/**
	 * @var object
	 */
	private object $session;

	/**
	 * @var object
	 */
	private object $config;

	/**
	 * @var int
	 */
	private int $user_id = 0;

	/**
	 * @var string
	 */
	private string $username = '';

	/**
	 * @var int
	 */
	private int $user_group_id = 0;

	/**
	 * @var array<string, array<int, string>>
	 */
	private array $permission = [];

	protected $registry;

	/**
	 * Constructor
	 *
	 * @param 	$registry
	 */
    public function __construct(Registry $registry) {
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
		$this->config = $registry->get('config');

		if (isset($this->session->data['user_id'])) {
			$user_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE user_id = '" . (int)$this->session->data['user_id'] . "' AND status = '1'");

			if ($user_query->num_rows) {
				$this->user_id = $user_query->row['user_id'];
				$this->username = $user_query->row['username'];
				$this->user_group_id = $user_query->row['user_group_id'];

				$this->db->query("UPDATE `" . DB_PREFIX . "user` SET `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE user_id = '" . (int)$this->session->data['user_id'] . "'");

				$user_group_query = $this->db->query("SELECT permission FROM `" . DB_PREFIX . "user_group` WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

				// check string before unserialize
				if ($user_group_query->row['permission'] && is_string($user_group_query->row['permission'])) {
					$permissions = unserialize($user_group_query->row['permission'], ['allowed_classes' => false]);
				} else {
					$permissions = [];
				}

				// Enforce expected type
				if (!is_array($permissions)) {
					throw new \Exception('Error: Permissions must be an array.');
				}

				if (is_array($permissions)) {
					foreach ($permissions as $key => $value) {
						$this->permission[$key] = $value;
	  				}
				}

			} else {
				$this->logout();
			}
		}
	}

	/**
	 * Login
	 *
	 * @param string $username
	 * @param string $password
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $login = $this->user->login($username, $password);
	 */
	public function login(string $username, string $password): bool {
		$username = $this->sanitize($username);

		$url = $this->request->server['REQUEST_URI'];
		$ip = $this->request->server['REMOTE_ADDR'];

		$user_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE username = '" . $this->db->escape((string)$username) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape((string)$password) . "'))))) OR password = '" . $this->db->escape(md5((string)$password)) . "') AND status = '1'");

		if ($user_query->num_rows) {
			$this->session->data['user_id'] = $user_query->row['user_id'];

			$this->user_id = $user_query->row['user_id'];
			$this->username = $user_query->row['username'];
			$this->user_group_id = $user_query->row['user_group_id'];

			$user_group_query = $this->db->query("SELECT permission FROM `" . DB_PREFIX . "user_group` WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

			// check string before unserialize
			if ($user_group_query->row['permission'] && is_string($user_group_query->row['permission'])) {
				$permissions = unserialize($user_group_query->row['permission'], ['allowed_classes' => false]);
			} else {
				$permissions = [];
			}

			if (is_array($permissions)) {
				foreach ($permissions as $key => $value) {
					$this->permission[$key] = $value;
				}
			}

			// User Log
			if ($this->config->get('user_log_enable') && $this->config->get('user_log_login')) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "user_log` SET user_id = '" . (int)$this->user_id . "', username = '" . (string)$this->username . "', `action` = 'login', `allowed` = '1', `url` = '" . $this->db->escape((string)$url) . "', `ip` = '" . $this->db->escape($ip) . "', `date` = NOW()");
			}

			return true;

		} else {
			// User Log
			if ($this->config->get('user_log_enable') && $this->config->get('user_log_hacklog')) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "user_log` SET user_id = '" . (int)$this->user_id . "', username = '" . $this->db->escape((string)$username) . "', `action` = 'login', `allowed` = '0', `url` = '" . $this->db->escape((string)$url) . "', `ip` = '" . $this->db->escape($ip) . "', `date` = NOW()");
			}

			return false;
		}
	}

	/**
	 * Logout
	 *
	 * @return void
	 *
	 * @example
	 *
	 * $this->user->logout();
	 */
	public function logout(): void {
		// User Log
		if ($this->config->get('user_log_enable') && $this->config->get('user_log_logout')) {
			$url = $this->request->server['REQUEST_URI'];
			$ip = $this->request->server['REMOTE_ADDR'];

			$this->db->query("INSERT INTO `" . DB_PREFIX . "user_log` SET user_id = '" . (int)$this->user_id . "', username = '" . (string)$this->username . "', `action` = 'logout', `allowed` = '1', `url` = '" . $this->db->escape((string)$url) . "', `ip` = '" . $this->db->escape($ip) . "', `date` = NOW()");
		}

		unset($this->session->data['user_id']);

		$this->user_id = 0;
		$this->username = '';
		$this->user_group_id = 0;

		session_destroy();
	}

	/**
	 * Has Permission
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $permission = $this->user->hasPermission();
	 */
	public function hasPermission(string $key, string $value): bool {
		$url = $this->request->server['REQUEST_URI'];
		$ip = $this->request->server['REMOTE_ADDR'];

		if (isset($this->permission[$key])) {
			// User Log
			if ($this->config->get('user_log_enable')) {
				if ((($this->config->get('user_log_allowed') === 1 || $this->config->get('user_log_allowed') === 2) && (in_array($value, $this->permission[$key]))) || (($this->config->get('user_log_allowed') === 0 || $this->config->get('user_log_allowed') === 2) && !(in_array($value, $this->permission[$key])))) {
					if (($this->config->get('user_log_access') && $key === "access") || ($this->config->get('user_log_modify') && $key === "modify")) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "user_log` SET user_id = '" . (int)$this->user_id . "', username = '" . (string)$this->username . "', `action` = '" . $key . "', `allowed` = '" . in_array($value, $this->permission[$key]) . "', `url` = '" . $this->db->escape((string)$url) . "', `ip` = '" . $this->db->escape($ip) . "', `date` = NOW()");
					}
				}
			}

			return in_array($value, $this->permission[$key]);

		} else {
			// User Log
			if ($this->config->get('user_log_enable') && ($this->config->get('user_log_allowed') === 0 || $this->config->get('user_log_allowed') === 2)) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "user_log` SET user_id = '" . (int)$this->user_id . "', username = '" . (string)$this->username . "', `action` = '" . $key . "', `allowed` = '0', `url` = '" . $this->db->escape((string)$url) . "', `ip` = '" . $this->db->escape($ip) . "', `date` = NOW()");
			}

			return false;
		}
	}

	/**
	 * Is Logged
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $logged = $this->user->isLogged();
	 */
	public function isLogged(): bool {
		return $this->user_id ? true : false;
	}

	/**
	 * Get Id
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $user_id = $this->user->getId();
	 */
	public function getId(): int {
		return $this->user_id;
	}

	/**
	 * Get User Name
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $username = $this->user->getUserName();
	 */
	public function getUserName(): string {
		return $this->username;
	}

	/**
	 * Get User Group Id
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $group_id = $this->user->getGroupId();
	 */
	public function getUserGroupId(): int {
		return $this->user_group_id;
	}

	/**
	 * Security functions
	 *
	 * @return string
	 */
	public function sanitize(string $string): string {
		// Strips HTML and PHP tags
		$string = strip_tags($string);
		// Removes any # from string
		$string = str_replace('#', '', $string);
		// Trims default ASCII characters
		$string = trim($string);

		return $string;
	}

	/**
	 * Check User Name
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $username = $this->user->checkUsername();
	 */
	public function checkUsername(string $username): bool {
		$username = mb_strtolower($username, 'UTF-8');

		$check_name = $this->sanitize($username);

		if ($username === $check_name) {
			return true;
		} else {
			return false;
		}
	}
}
