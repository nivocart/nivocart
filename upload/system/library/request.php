<?php
/**
 * Library Class Request
 *
 * @package NivoCart
 */
class Request {
	/**
	 * @var array<string, mixed>
	 */
	public array $get = [];
	/**
	 * @var array<string, mixed>
	 */
	public array $post = [];
	/**
	 * @var array<string, mixed>
	 */
	public array $cookie = [];
	/**
	 * @var array<string, mixed>
	 */
	public array $files = [];
	/**
	 * @var array<string, mixed>
	 */
	public array $server = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->get = $this->clean($_GET);
		$this->post = $this->clean($_POST);
		$this->cookie = $this->clean($_COOKIE);
		$this->server = $this->clean($_SERVER);
		$this->files = $_FILES;  // Do not clean files!
	}

	/**
	 * clean
	 *
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	public function clean($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				unset($data[$key]);

				$data[$this->clean($key)] = $this->clean($value);
			}
		} else {
			$data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
		}

		return $data;
	}

	/**
	 * isSecure
	 *
	 * @return bool
	 */
	public function isSecure(): bool {
		if ((isset($this->server['HTTPS']) && in_array($this->server['HTTPS'], ['on', '1'], true)) ||
			(isset($this->server['SERVER_PORT']) && $this->server['SERVER_PORT'] === '443') ||
			(isset($this->server['HTTP_X_FORWARDED_PROTO']) && $this->server['HTTP_X_FORWARDED_PROTO'] === 'https')
		) {
			return true;
		} else {
			return false;
		}
	}
}
