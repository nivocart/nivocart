<?php
class Session {
	/**
	 * @var string
	 */
	protected string $session_id = '';
	/**
	 * @var array<mixed>
	 */
	public array $data = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		if (!session_id()) {
			ini_set('session.use_only_cookies', 'Off');
			ini_set('session.use_cookies', 'On');
			ini_set('session.use_trans_sid', 'Off');
			ini_set('session.cookie_httponly', 'On');

			if (isset($_COOKIE[session_name()]) && !preg_match('/^[a-zA-Z0-9,\-]+$/', $_COOKIE[session_name()])) {
				exit('Error: Invalid session ID!');
			}

			session_set_cookie_params(0, '/');
			session_start();
		}

		$this->data = &$_SESSION;
	}

	/**
	 * Get Session ID
	 *
	 * @return string
	 */
	public function getId(): string {
		return $this->session_id;
	}

	/**
	 * Start
	 *
	 * Starts a session
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return string returns the current session ID
	 */
	public function start(string $key = 'default', string $value = ''): string {
		if ($value) {
			$this->session_id = $value;
		} elseif (isset($_COOKIE[$key])) {
			$this->session_id = $_COOKIE[$key];
		} else {
			$this->session_id = $this->createId();
		}

		if (!isset($_SESSION[$this->session_id])) {
			$_SESSION[$this->session_id] = [];
		}

		$this->data = &$_SESSION[$this->session_id];

		if ($key != 'PHPSESSID') {
			setcookie($key, $this->session_id, ini_get('session.cookie_lifetime'), ini_get('session.cookie_path'), ini_get('session.cookie_domain'), ini_get('session.cookie_secure'), ini_get('session.cookie_httponly'));
		}

		return $this->session_id;
	}

	/**
	 * createId
	 *
	 * Creates a new session_id
	 *
	 * @return string
	 */
	public function createId(): string {
		if (function_exists('openssl_random_pseudo_bytes')) {
			return substr(bin2hex(openssl_random_pseudo_bytes(26)), 0, 26);
		} else {
			return substr(bin2hex(mcrypt_create_iv(26, MCRYPT_DEV_URANDOM)), 0, 26);
		}
	}

	/**
	 * Destroy
	 *
	 * Deletes the current session
	 *
	 * @return void
	 */
	public function destroy($key = 'default'): void {
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}

		setcookie($key, '', time() - 42000, ini_get('session.cookie_path'), ini_get('session.cookie_domain'));
	}
}
