<?php
class Url {
	/**
	 * @var string
	 */
	private string $url;

	/**
	 * @var string
	 */
	private string $ssl;

	/**
	 * @var array<int, object>
	 */
	private array $rewrite = [];

	/**
	 * Constructor
	 *
	 * @param string $url
	 * @param string $ssl
	 */
	public function __construct(string $url, string $ssl = '') {
		$this->url = $url;
		$this->ssl = $ssl;
	}

	/**
	 * Add Rewrite
	 *
	 * Add a rewrite method to the URL system
	 *
	 * @return void
	 */
	public function addRewrite(object $rewrite): void {
		if (is_callable([$rewrite, 'rewrite'])) {
			$this->rewrite[] = $rewrite;
		}
	}

	/**
	 * Link
	 *
	 * Generates a URL
	 *
	 * @param string  $route
	 * @param mixed   $args
	 * @param string  $connection
	 *
	 * @return string
	 */
	public function link(string $route, $args = '', string $connection = 'NONSSL'): string {
		if ($connection == 'NONSSL') {
			$url = $this->url . 'index.php?route=' . $route;
		} else {
			$url = $this->ssl . 'index.php?route=' . $route;
		}

		if ($args) {
			if (is_array($args)) {
				$url .= '&amp;' . http_build_query($args, '', '&amp;');
			} else {
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
		}

		foreach ($this->rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		// See https://stackoverflow.com/questions/78729429/403-forbidden-when-url-contains-get-with-encoded-question-mark-unsafeallow3f
		// https://github.com/opencart/opencart/issues/14202
		$url = str_replace('%3F', '?', $url);

		return $url;
	}

	/**
	 * getHttpResponseCode
	 *
	 * Test HTTP Response
	 *
	 * @param string  $url
	 *
	 * @return string
	 */
	public function getHttpResponseCode(string $url): string {
		$headers = get_headers($url);

		if ($headers) {
			return substr($headers[0], 9, 3);
		} else {
			return '404';
		}
	}

	/**
	 * isLocal
	 *
	 * Test if server is local
	 */
	public function isLocal() {
		$local_address = false;
		$local_name = false;
		$local_host = false;

		if (isset($_SERVER['SERVER_ADDR'])) {
			$local_address = ($_SERVER['SERVER_ADDR'] == '::1' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') ? true : false;
		}

		if (isset($_SERVER['SERVER_NAME'])) {
			$local_name = ($_SERVER['SERVER_NAME'] == 'localhost') ? true : false;
		}

		if (isset($_SERVER['HTTP_HOST'])) {
			$local_host = ($_SERVER['HTTP_HOST'] == 'localhost') ? true : false;
		}

		if ($local_address || $local_name || $local_host) {
			return true;
		} else {
			return false;
		}
	}
}
