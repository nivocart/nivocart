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
				throw new \Exception('Error: Invalid session ID!');
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
	 * Starts or resumes a named session slot within the active PHP session.
	 * Accepts no externally-supplied session ID — the ID is always either
	 * read from a validated cookie or freshly generated.
	 *
	 * @param string $key  Cookie / session-slot name (alphanumeric + underscore, max 32 chars)
	 *
	 * @return string  The session ID for this slot
	 *
	 * @throws \Exception on invalid cookie name or a tampered cookie value
	 */
	public function start(string $key = 'default'): string {
		// 1. Validate the cookie name — must be a safe, predictable identifier.
		//    Never allow arbitrary / user-supplied values here.
		if (!preg_match('/^[A-Za-z0-9_]{1,32}$/', $key)) {
			throw new \Exception('Error: Invalid session cookie name.');
		}

		// 2. Determine the session ID.
		//    We never accept a caller-supplied $value — doing so enables session fixation.
		//    Priority: existing valid cookie → generate a new one.
		if (isset($_COOKIE[$key])) {
			$candidate = $_COOKIE[$key];

			// Reject anything that doesn't match our own ID format.
			// This stops an attacker probing other users' session slots.
			if (!preg_match('/^[a-f0-9]{64}$/', $candidate)) {
				// Destroy the bad cookie so the browser doesn't keep sending it.
				setcookie($key, '', time() - 42000,
					'/',
					(string) ini_get('session.cookie_domain'),
					(bool) ini_get('session.cookie_secure'),
					true
				);
				throw new \Exception('Error: Invalid session ID!');
			}

			$this->session_id = $candidate;
		} else {
			// No cookie present — mint a fresh, cryptographically-secure ID.
			$this->session_id = $this->createId();
		}

		// 3. Initialise the session data slot if this is a brand-new session ID.
		if (!isset($_SESSION[$this->session_id])) {
			$_SESSION[$this->session_id] = [];
		}

		$this->data = &$_SESSION[$this->session_id];

		// 4. (Re-)issue the cookie so its expiry/flags stay current.
		//    Skip for PHPSESSID — PHP manages that one itself.
		if ($key !== 'PHPSESSID') {
			setcookie(
				$key,
				$this->session_id,
				[
					'expires'  => 0,                                      // session cookie
					'path'     => (string) ini_get('session.cookie_path'),
					'domain'   => (string) ini_get('session.cookie_domain'),
					'secure'   => (bool) ini_get('session.cookie_secure'),
					'httponly' => true,
					'samesite' => 'Lax',                                  // CSRF mitigation
				]
			);
		}

		return $this->session_id;
	}

	/**
	 * createId
	 *
	 * Generates a cryptographically-secure session ID.
	 * 32 random bytes → 64 lowercase hex characters.
	 *
	 * @return string
	 *
	 * @throws \Exception if the system CSPRNG is unavailable
	 */
	public function createId(): string {
		return bin2hex(random_bytes(32));
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
