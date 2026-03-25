<?php
class Affiliate {
	/**
	 * @var object
	 */
	private object $db;

	/**
	 * @var object
	 */
	private object $config;

	/**
	 * @var object
	 */
	private object $request;

	/**
	 * @var object
	 */
	private object $session;

	/**
	 * @var int
	 */
	private int $affiliate_id = 0;

	/**
	 * @var string
	 */
	private string $firstname = '';

	/**
	 * @var string
	 */
	private string $lastname = '';

	/**
	 * @var string
	 */
	private string $email = '';

	/**
	 * @var string
	 */
	private string $telephone = '';

	/**
	 * @var string
	 */
	private string $website = '';

	/**
	 * @var string
	 */
	private string $code = '';

	protected $registry;

	/**
	 * Constructor
	 *
	 * @param 	$registry
	 */
    public function __construct(Registry $registry) {
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

		if (isset($this->session->data['affiliate_id'])) {
			$affiliate_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "affiliate` WHERE affiliate_id = '" . (int)$this->session->data['affiliate_id'] . "' AND status = '1'");

			if ($affiliate_query->num_rows) {
				$this->affiliate_id = $affiliate_query->row['affiliate_id'];
				$this->firstname = $affiliate_query->row['firstname'];
				$this->lastname = $affiliate_query->row['lastname'];
				$this->email = $affiliate_query->row['email'];
				$this->telephone = $affiliate_query->row['telephone'];
				$this->website = $affiliate_query->row['website'];
				$this->code = $affiliate_query->row['code'];

				$this->db->query("UPDATE `" . DB_PREFIX . "affiliate` SET `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE affiliate_id = '" . (int)$this->session->data['affiliate_id'] . "'");

			} else {
				$this->logout();
			}
		}
	}

	/**
	 * Login
	 *
	 * @param string $email
	 * @param string $password
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $login = $this->affiliate->login($username, $password);
	 */
	public function login(string $email, string $password): bool {
		$affiliate_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "affiliate` WHERE LOWER(email) = '" . $this->db->escape(mb_strtolower((string)$email, 'UTF-8')) . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape((string)$password) . "'))))) OR password = '" . $this->db->escape(md5((string)$password)) . "') AND status = '1' AND approved = '1'");

		if ($affiliate_query->num_rows) {
			// Create affiliate login cookie if HTTPS
			if ($this->config->get('config_secure')) {
				if ($this->request->isSecure()) {
					// Create a cookie and restrict it to HTTPS pages
					$this->session->data['affiliate_cookie'] = hash_rand('ripemd128');

					setcookie('affiliate', $this->session->data['affiliate_cookie'], 0, '/', '', true, true);
				} else {
					return false;
				}
			}

			// Token used to protect affiliate functions against CSRF
			$this->setToken();

			$this->session->data['affiliate_id'] = $affiliate_query->row['affiliate_id'];
			$this->session->data['affiliate_login_time'] = time();

			$this->affiliate_id = $affiliate_query->row['affiliate_id'];
			$this->firstname = $affiliate_query->row['firstname'];
			$this->lastname = $affiliate_query->row['lastname'];
			$this->email = $affiliate_query->row['email'];
			$this->telephone = $affiliate_query->row['telephone'];
			$this->website = $affiliate_query->row['website'];
			$this->code = $affiliate_query->row['code'];

			return true;
		} else {
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
	 * $this->affiliate->logout();
	 */
	public function logout(): void {
		unset($this->session->data['affiliate_id']);
		unset($this->session->data['affiliate_cookie']);
		unset($this->session->data['affiliate_token']);
		unset($this->session->data['affiliate_login_time']);

		$this->affiliate_id = 0;
		$this->firstname = '';
		$this->lastname = '';
		$this->email = '';
		$this->telephone = '';
		$this->website = '';
		$this->code = '';
	}

	/**
	 * Is Logged
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $logged = $this->affiliate->isLogged();
	 */
	public function isLogged(): bool {
		return $this->affiliate_id ? true : false;
	}

	/**
	 * Get Id
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $user_id = $this->affiliate->getId();
	 */
	public function getId(): int {
		return $this->affiliate_id;
	}

	/**
	 * Get First Name
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $username = $this->affiliate->getFirstName();
	 */
	public function getFirstName(): string {
		return $this->firstname;
	}

	/**
	 * Get Last Name
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $username = $this->affiliate->getLastName();
	 */
	public function getLastName(): string {
		return $this->lastname;
	}

	/**
	 * Get Email
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $username = $this->affiliate->getEmail();
	 */
	public function getEmail(): string {
		return $this->email;
	}

	/**
	 * Get Telephone
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $username = $this->affiliate->getTelephone();
	 */
	public function getTelephone(): string {
		return $this->telephone;
	}

	/**
	 * Get Website
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $username = $this->affiliate->getWebsite();
	 */
	public function getWebsite(): string {
		return $this->website;
	}

	/**
	 * Get Code
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $username = $this->affiliate->getCode();
	 */
	public function getCode(): string {
		return $this->code;
	}

	/**
	 * Security functions
	 *
	 * @return bool
	 */
	public function isSecure(): bool {
		if (!$this->config->get('config_secure') || ($this->request->isSecure() && isset($this->request->cookie['affiliate']) && isset($this->session->data['affiliate_cookie']) && $this->request->cookie['affiliate'] == $this->session->data['affiliate_cookie'])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set Token
	 */
	public function setToken() {
		$this->session->data['affiliate_token'] = hash_rand('ripemd128');
	}

	/**
	 * Login Expired
	 *
	 * @return bool
	 */
	public function loginExpired($age = 1800): bool {
		if (isset($this->session->data['affiliate_login_time']) && (time() - $this->session->data['affiliate_login_time'] < $age)) {
			return false;
		} else {
			return true;
		}
	}
}
