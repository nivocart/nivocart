<?php
class Response {
	/**
	 * @var array<int, string>
	 */
	private array $headers = [];
	/**
	 * @var int
	 */
	private int $level = 0;
	/**
	 * @var string
	 */
	private string $output = '';

	/**
	 * Sanitize a header string against newline/CRLF injection.
	 *
	 * @param string $header
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	private function sanitizeHeader(string $header): string {
		// Strip null bytes, carriage returns, and newlines — the core characters used in header injection attacks.
		$sanitized = str_replace(["\0", "\r", "\n"], '', $header);

		// Reject the header entirely if sanitizing changed it, so that callers are made aware
		// of malformed input rather than silently swallowing it.
		if ($sanitized !== $header) {
			throw new \InvalidArgumentException('Invalid header value: control characters are not permitted.');
		}

		return $sanitized;
	}

	/**
	 * Add Header
	 *
	 * @param string $header
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addHeader(string $header): void {
		$this->headers[] = $this->sanitizeHeader($header);
	}

	/**
	 * Get Headers
	 *
	 * @return array<int, string>
	 */
	public function getHeaders(): array {
		return $this->headers;
	}

	/**
	 * Redirect
	 *
	 * @param string $url
	 * @param int    $status
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function redirect(string $url, int $status = 302): void {
		// Validate the HTTP status code is within the legitimate 3xx range.
		if ($status < 300 || $status > 399) {
			throw new \InvalidArgumentException('Invalid redirect status code: must be in the 3xx range.');
		}

		// Decode the URL first so encoded control characters (e.g. %0d%0a)
		// are caught by the scheme and character checks below.
		$decoded = urldecode($url);

		// Strip all ASCII control characters (0x00–0x1F and 0x7F), which covers CRLF, null bytes,
		// and other injection primitives, whether literal or decoded from percent-encoding.
		if (preg_match('/[\x00-\x1F\x7F]/', $decoded)) {
			throw new \InvalidArgumentException('Invalid redirect URL: control characters are not permitted.');
		}

		// Allow only safe URL schemes. Without this a caller could pass
		// "javascript:" or "data:" URIs as the redirect target.
		$scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

		if (!in_array($scheme, ['http', 'https'], true)) {
			throw new \InvalidArgumentException('Invalid redirect URL: only http and https schemes are permitted.');
		}

		// Re-encode the sanitized URL to ensure it is well-formed before placing it in the Location header.
		header('Location: ' . filter_var($url, FILTER_SANITIZE_URL), true, $status);
		exit();
	}

	/**
	 * Set Compression
	 *
	 * @param int $level
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setCompression(int $level): void {
		if ($level < -1 || $level > 9) {
			throw new \InvalidArgumentException('Invalid compression level: must be between -1 and 9.');
		}

		$this->level = $level;
	}

	/**
	 * Set Output
	 *
	 * @param string $output
	 *
	 * @return void
	 */
	public function setOutput(string $output): void {
		$this->output = $output;
	}

	/**
	 * Get Output
	 *
	 * @return string
	 */
	public function getOutput(): string {
		return $this->output;
	}

	/**
	 * Compress
	 *
	 * @param string $data
	 * @param int    $level
	 *
	 * @return string
	 */
	private function compress(string $data, int $level = 0): string {
		$encoding = null;

		if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			// Match against a strict allowlist rather than trusting the
			// raw client-supplied string. This prevents the user-controlled
			// Accept-Encoding value from ever reaching a header directly.
			if (str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
				$encoding = 'gzip';
			} elseif (str_contains($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {
				$encoding = 'x-gzip';
			}
		}

		if ($encoding === null || ($level < -1 || $level > 9)) {
			return $data;
		}

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
		}

		if (headers_sent()) {
			return $data;
		}

		if (connection_status()) {
			return $data;
		}

		// $encoding is now always a hardcoded safe string ('gzip' or
		// 'x-gzip'), not the raw client value, so addHeader() is safe here.
		$this->addHeader('Content-Encoding: ' . $encoding);

		return gzencode($data, $level);
	}

	/**
	 * Output
	 *
	 * Displays the set HTML output.
	 *
	 * @return void
	 */
	public function output(): void {
		if ($this->output) {
			$output = $this->level ? $this->compress($this->output, $this->level) : $this->output;

			if (!headers_sent()) {
				foreach ($this->headers as $header) {
					header($header, true);
				}
			}

			// $output is intentionally raw HTML — escaping here would corrupt it.
			// XSS prevention belongs upstream, at the point of interpolation.
			echo $output;
		}
	}
}
