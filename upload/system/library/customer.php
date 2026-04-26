<?php
class Customer {
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
	private int $customer_id = 0;

	/**
	 * @var string
	 */
	private string $firstname = '';

	/**
	 * @var string
	 */
	private string $lastname = '';

	/**
	 * @var int
	 */
	private int $customer_group_id = 0;

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
	private string $gender = '';

	/**
	 * @var date
	 */
	private $date_of_birth;

	/**
	 * @var int
	 */
	private int $newsletter = 0;

	/**
	 * @var int
	 */
	private int $address_id = 0;

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

		if (isset($this->session->data['customer_id'])) {
			$customer_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$this->session->data['customer_id'] . "' AND status = '1'");

			if ($customer_query->num_rows) {
				$this->customer_id = $customer_query->row['customer_id'];
				$this->firstname = $customer_query->row['firstname'];
				$this->lastname = $customer_query->row['lastname'];
				$this->customer_group_id = $customer_query->row['customer_group_id'];
				$this->email = $customer_query->row['email'];
				$this->telephone = $customer_query->row['telephone'];
				$this->gender = $customer_query->row['gender'];
				$this->date_of_birth = $customer_query->row['date_of_birth'];
				$this->newsletter = $customer_query->row['newsletter'];
				$this->address_id = $customer_query->row['address_id'];

				$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET cart = '" . $this->db->escape(isset($this->session->data['cart']) ? serialize($this->session->data['cart']) : '') . "', wishlist = '" . $this->db->escape(isset($this->session->data['wishlist']) ? serialize($this->session->data['wishlist']) : '') . "', `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ip` WHERE customer_id = '" . (int)$this->session->data['customer_id'] . "' AND `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_ip` SET customer_id = '" . (int)$this->session->data['customer_id'] . "', `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', date_added = NOW()");
				}

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
	 * @param bool   $override
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $login = $this->customer->login($email, $password, $override);
	 */
	public function login(string $email, string $password, bool $override = false): bool {
		if ($override) {
			$customer_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE LOWER(email) = '" . $this->db->escape(mb_strtolower((string)$email), 'UTF-8') . "' AND status = '1'");
		} else {
			$customer_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE LOWER(email) = '" . $this->db->escape(mb_strtolower((string)$email), 'UTF-8') . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape((string)$password) . "'))))) OR password = '" . $this->db->escape(md5((string)$password)) . "') AND status = '1' AND approved = '1'");
		}

		if ($customer_query->num_rows) {
			// Create customer login cookie if HTTPS
			if ($this->config->get('config_secure')) {
				// Create a cookie and restrict it to HTTPS pages
				if ($this->request->isSecure()) {
					$this->session->data['customer_cookie'] = bin2hex(random_bytes(32));

					setcookie('customer', $this->session->data['customer_cookie'], 0, '/', '', true, true);
				} else {
					return false;
				}
			}

			// Token used to protect account functions against CSRF
			$this->setToken();

			$this->session->data['customer_id'] = $customer_query->row['customer_id'];
			$this->session->data['customer_login_time'] = time();

			// check string before unserialize
			if ($customer_query->row['cart'] && is_string($customer_query->row['cart'])) {
				$cart = unserialize($customer_query->row['cart'], ['allowed_classes' => false]);

				// Enforce expected type
				if (!is_array($cart)) {
					throw new \Exception('Error: Cart must be an array.');
				}

				foreach ($cart as $key => $value) {
					if (!array_key_exists($key, $this->session->data['cart'])) {
						$this->session->data['cart'][$key] = $value;
					} else {
						$this->session->data['cart'][$key] += $value;
					}
				}
			}

			// check string before unserialize
			if ($customer_query->row['wishlist'] && is_string($customer_query->row['wishlist'])) {
				if (!isset($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = [];
				}

				$wishlist = unserialize($customer_query->row['wishlist'], ['allowed_classes' => false]);

				// Enforce expected type
				if (!is_array($wishlist)) {
					throw new \Exception('Error: Wishlist must be an array.');
				}

				foreach ($wishlist as $product_id) {
					if (!in_array($product_id, $this->session->data['wishlist'])) {
						$this->session->data['wishlist'][] = $product_id;
					}
				}
			}

			$this->customer_id = $customer_query->row['customer_id'];
			$this->firstname = $customer_query->row['firstname'];
			$this->lastname = $customer_query->row['lastname'];
			$this->customer_group_id = $customer_query->row['customer_group_id'];
			$this->email = $customer_query->row['email'];
			$this->telephone = $customer_query->row['telephone'];
			$this->gender = $customer_query->row['gender'];
			$this->date_of_birth = $customer_query->row['date_of_birth'];
			$this->newsletter = $customer_query->row['newsletter'];
			$this->address_id = $customer_query->row['address_id'];

			$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

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
	 * $this->customer->logout();
	 */
	public function logout(): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET cart = '" . $this->db->escape(isset($this->session->data['cart']) ? serialize($this->session->data['cart']) : '') . "', wishlist = '" . $this->db->escape(isset($this->session->data['wishlist']) ? serialize($this->session->data['wishlist']) : '') . "' WHERE customer_id = '" . (int)$this->customer_id . "'");

		$this->session->data['cart'] = [];

		unset($this->session->data['customer_id']);
		unset($this->session->data['customer_cookie']);
		unset($this->session->data['customer_token']);
		unset($this->session->data['customer_login_time']);
		unset($this->session->data['wishlist']);

		$this->customer_id = 0;
		$this->firstname = '';
		$this->lastname = '';
		$this->customer_group_id = 0;
		$this->email = '';
		$this->telephone = '';
		$this->gender = '';
		$this->date_of_birth = '';
		$this->newsletter = 0;
		$this->address_id = 0;
	}

	/**
	 * Is Logged
	 *
	 * @return bool
	 *
	 * @example
	 *
	 * $logged = $this->customer->isLogged();
	 */
	public function isLogged(): bool {
		return $this->customer_id ? true : false;
	}

	/**
	 * Get Id
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $customer_id = $this->customer->getId();
	 */
	public function getId(): int {
		return $this->customer_id;
	}

	/**
	 * Get First Name
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $firstname = $this->customer->getFirstName();
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
	 * $lastname = $this->customer->getLastName();
	 */
	public function getLastName(): string {
		return $this->lastname;
	}

	/**
	 * Get Customer Group Id
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $group_id = $this->customer->getGroupId();
	 */
	public function getCustomerGroupId(): int {
		return $this->customer_group_id;
	}

	/**
	 * Get Email
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $customer = $this->customer->getEmail();
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
	 * $telephone = $this->customer->getTelephone();
	 */
	public function getTelephone(): string {
		return $this->telephone;
	}

	/**
	 * Get Gender
	 *
	 * @return string
	 *
	 * @example
	 *
	 * $gender = $this->customer->getGender();
	 */
	public function getGender(): string {
		return $this->gender;
	}

	/**
	 * Get DOB
	 *
	 * @return date
	 *
	 * @example
	 *
	 * $dob = $this->customer->getDateOfBirth();
	 */
	public function getDateOfBirth() {
		return $this->date_of_birth;
	}

	/**
	 * Get Newsletter
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $newsletter = $this->customer->getNewsletter();
	 */
	public function getNewsletter(): int {
		return $this->newsletter;
	}

	/**
	 * Get Newsletter
	 *
	 * @return int
	 *
	 * @example
	 *
	 * $address_id = $this->customer->getAddressId();
	 */
	public function getAddressId(): int {
		return $this->address_id;
	}

	/**
	 * Get Balance
	 *
	 * @return float total number of balance records
	 *
	 * @example
	 *
	 * $balance = $this->customer->getBalance();
	 */
	public function getBalance(): float {
		$query = $this->db->query("SELECT SUM(amount) AS `total` FROM `" . DB_PREFIX . "customer_transaction` WHERE customer_id = '" . (int)$this->customer_id . "'");

		return (float)$query->row['total'];
	}

	/**
	 * Get Reward Points
	 *
	 * @return float total number of reward point records
	 *
	 * @example
	 *
	 * $reward_total = $this->customer->getRewardPoints();
	 */
	public function getRewardPoints(): float {
		$query = $this->db->query("SELECT SUM(points) AS `total` FROM `" . DB_PREFIX . "customer_reward` WHERE customer_id = '" . (int)$this->customer_id . "'");

		return (float)$query->row['total'];
	}

	/**
	 * Security functions
	 *
	 * @return bool
	 */
	public function isSecure(): bool {
		if (!$this->config->get('config_secure') || ($this->request->isSecure() && isset($this->request->cookie['customer']) && isset($this->session->data['customer_cookie']) && $this->request->cookie['customer'] === $this->session->data['customer_cookie'])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Set Token
	 */
	public function setToken() {
		$this->session->data['customer_token'] = bin2hex(random_bytes(32));
	}

	/**
	 * Login Expired
	 *
	 * @return bool
	 */
	public function loginExpired($age = 1800): bool {
		if (isset($this->session->data['customer_login_time']) && (time() - $this->session->data['customer_login_time'] < $age)) {
			return false;
		} else {
			return true;
		}
	}
}
