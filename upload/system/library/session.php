<?php
/**
 * Library Class Session
 *
 * @package NivoCart
 */
class Session {
	/**
	 * @var string
	 */
	protected string $session_id = '';
	/**
	 * @var string
	 */
	protected string $key = 'default';
	/**
	 * @var array<mixed>
	 */
	public array $data = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		if (!session_id()) {
			// Session IDs are only ever accepted from the cookie, never from the URL,
			// and PHP must not adopt an ID it did not issue (session fixation).
			ini_set('session.use_only_cookies', '1');
			ini_set('session.use_strict_mode', '1');
			ini_set('session.use_cookies', '1');
			ini_set('session.use_trans_sid', '0');
			ini_set('session.cookie_httponly', '1');
			ini_set('session.cookie_samesite', 'Lax');

			if (isset($_COOKIE[session_name()]) && !preg_match('/^[a-zA-Z0-9,\-]+$/', $_COOKIE[session_name()])) {
				throw new \Exception('Error: Invalid session ID!');
			}

			session_set_cookie_params([
				'lifetime' => 0,
				'path'     => '/',
				'secure'   => $this->isHttps(),
				'httponly' => true,
				'samesite' => 'Lax'
			]);

			session_start();
		}

		$this->data = &$_SESSION;
	}

	/**
	 * Is the current request served over HTTPS?
	 *
	 * Used to set the Secure cookie flag only when it can work, so a plain
	 * HTTP development site (e.g. a local WAMP server) keeps working.
	 *
	 * @return bool
	 */
	protected function isHttps(): bool {
		if (!empty($_SERVER['HTTPS']) && (strtolower((string)$_SERVER['HTTPS']) !== 'off')) {
			return true;
		}

		if (isset($_SERVER['SERVER_PORT']) && ((int)$_SERVER['SERVER_PORT'] === 443)) {
			return true;
		}

		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')) {
			return true;
		}

		return false;
	}

	/**
	 * Cookie options for the session slot cookie
	 *
	 * @param int $expires
	 *
	 * @return array<string, mixed>
	 */
	protected function cookieOptions(int $expires = 0): array {
		return [
			'expires'  => $expires,
			'path'     => (string)ini_get('session.cookie_path') ?: '/',
			'domain'   => (string)ini_get('session.cookie_domain'),
			'secure'   => $this->isHttps(),
			'httponly' => true,
			'samesite' => 'Lax'
		];
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
				setcookie($key, '', $this->cookieOptions(time() - 42000));

				throw new \Exception('Error: Invalid session ID!');
			}

			// Only resume a slot this server issued. An unknown ID (for example one
			// planted by an attacker) is ignored and a fresh ID is minted instead.
			if (isset($_SESSION[$candidate])) {
				$this->session_id = $candidate;
			} else {
				$this->session_id = $this->createId();
			}
		} else {
			// No cookie present — mint a fresh, cryptographically-secure ID.
			$this->session_id = $this->createId();
		}

		// 3. Initialise the session data slot if this is a brand-new session ID.
		if (!isset($_SESSION[$this->session_id])) {
			$_SESSION[$this->session_id] = [];
		}

		$this->data = &$_SESSION[$this->session_id];

		$this->key = $key;

		// 4. (Re-)issue the cookie so its expiry/flags stay current.
		//    Skip for PHPSESSID — PHP manages that one itself.
		if ($key !== 'PHPSESSID') {
			setcookie($key, $this->session_id, $this->cookieOptions());
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
	 * Regenerate
	 *
	 * Moves the current session data to a new random ID and rotates the PHP
	 * container ID as well. Call this whenever the privilege level of the
	 * session changes (login), so an ID known before login is worthless after.
	 *
	 * @return string  The new session ID
	 */
	public function regenerate(): string {
		if (headers_sent() || ($this->session_id === '')) {
			return $this->session_id;
		}

		$old = $this->session_id;
		$new = $this->createId();

		$_SESSION[$new] = $this->data;

		unset($_SESSION[$old]);

		$this->session_id = $new;
		$this->data = &$_SESSION[$new];

		if ($this->key !== 'PHPSESSID') {
			setcookie($this->key, $new, $this->cookieOptions());
		}

		// Rotate the PHP container ID too, keeping all session data
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_regenerate_id(true);
		}

		return $new;
	}

	/**
	 * Destroy
	 *
	 * Deletes the current session slot and its cookie
	 *
	 * @param string $key  Cookie / session-slot name
	 *
	 * @return void
	 */
	public function destroy(string $key = 'default'): void {
		if (($this->session_id !== '') && isset($_SESSION[$this->session_id])) {
			unset($_SESSION[$this->session_id]);
		}

		$this->session_id = '';
		$this->data = [];

		unset($_COOKIE[$key]);

		setcookie($key, '', $this->cookieOptions(time() - 42000));
	}
}
