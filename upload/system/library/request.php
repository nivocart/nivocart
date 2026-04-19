<?php
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
	public array $request = [];
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
		$this->request = $this->clean($_REQUEST);
		$this->cookie = $this->clean($_COOKIE);
		$this->files = $this->clean($_FILES);
		$this->server = $this->clean($_SERVER);
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
			$data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
		}

		return $data;
	}

	/**
	 * isSecure
	 *
	 * @return bool
	 */
	public function isSecure() {
		if ((isset($this->server['HTTPS']) && (($this->server['HTTPS'] === 'on') || ($this->server['HTTPS'] === '1'))) || ($this->server['SERVER_PORT'] === '443')) {
			return true;
		} elseif (!empty($this->server['HTTP_X_FORWARDED_PROTO']) && $this->server['HTTP_X_FORWARDED_PROTO'] === 'https' || !empty($this->server['HTTP_X_FORWARDED_SSL']) && $this->server['HTTP_X_FORWARDED_SSL'] === 'on') {
			return true;
		} else {
			return false;
		}
	}
}
