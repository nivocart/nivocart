<?php
/**
 * Library Class Response
 *
 * @package NivoCart
 */
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
	 * @var array<string, string>
	 */
	private array $image_roots = [];
	/**
	 * @var array<string, array<int, int>|null>
	 */
	private array $image_sizes = [];

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
	 * Set Image Roots
	 *
	 * Enables automatic width and height attributes on <img> tags that have none (prevents layout shifts).
	 * Each key is a public URL prefix and each value is the matching directory on disk (with a trailing slash).
	 *
	 * @param array<string, string> $roots
	 *
	 * @return void
	 */
	public function setImageRoots(array $roots): void {
		$this->image_roots = $roots;
	}

	/**
	 * Get Image Size
	 *
	 * Resolves an image URL to its pixel size. Cache images are read from the file name (name-WxH.ext),
	 * any other local image is read with getimagesize(). Returns null for anything that is not a local image.
	 *
	 * @param string $src
	 *
	 * @return array<int, int>|null
	 */
	private function getImageSize(string $src): ?array {
		$src = html_entity_decode($src, ENT_QUOTES, 'UTF-8');

		if (array_key_exists($src, $this->image_sizes)) {
			return $this->image_sizes[$src];
		}

		$this->image_sizes[$src] = null;

		$path = null;
		$root = '';

		foreach ($this->image_roots as $prefix => $directory) {
			if ($prefix !== '' && str_starts_with($src, $prefix)) {
				$path = substr($src, strlen($prefix));
				$root = $directory;
				break;
			}
		}

		// Relative URLs resolve against the store base URL
		if ($path === null && $src !== '' && !str_starts_with($src, '/') && !preg_match('/^[a-z][a-z0-9+.\-]*:/i', $src)) {
			$path = $src;
			$root = (string)reset($this->image_roots);
		}

		if ($path === null || $root === '') {
			return null;
		}

		$path = rawurldecode((string)preg_replace('/[?#].*$/', '', $path));

		if ($path === '' || str_contains($path, '..') || str_contains($path, "\0") || str_contains($path, '\\')) {
			return null;
		}

		if (!preg_match('/\.(?:avif|gif|jpe?g|png|webp)$/i', $path)) {
			return null;
		}

		// Cache images are always written at the exact size in the file name
		if (preg_match('#^image/cache/.+-(\d+)x(\d+)\.[a-z]+$#i', $path, $match)) {
			if ((int)$match[1] > 0 && (int)$match[2] > 0) {
				return $this->image_sizes[$src] = [(int)$match[1], (int)$match[2]];
			}

			return null;
		}

		$file = $root . $path;

		if (!is_file($file)) {
			return null;
		}

		$info = getimagesize($file);

		if ($info === false || $info[0] < 1 || $info[1] < 1) {
			return null;
		}

		return $this->image_sizes[$src] = [(int)$info[0], (int)$info[1]];
	}

	/**
	 * Add Image Dimensions
	 *
	 * Adds width and height to every <img> tag that has neither, so the browser can reserve the space before the image loads.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	private function addImageDimensions(string $html): string {
		if (!$this->image_roots || stripos($html, '<img') === false) {
			return $html;
		}

		$result = preg_replace_callback('/<img\b((?:[^>"\']|"[^"]*"|\'[^\']*\')*)>/i', function (array $match): string {
			$attributes = $match[1];

			// Leave tags alone that already have a width or a height
			if (preg_match('/\s(?:width|height)\s*=/i', $attributes)) {
				return $match[0];
			}

			if (!preg_match('/\ssrc\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', $attributes, $src)) {
				return $match[0];
			}

			$size = $this->getImageSize($src[1] !== '' ? $src[1] : ($src[2] ?? ''));

			if ($size === null) {
				return $match[0];
			}

			$closing = '';

			if (preg_match('/\s*\/\s*$/', $attributes, $end)) {
				$attributes = substr($attributes, 0, -strlen($end[0]));
				$closing = ' /';
			}

			return '<img' . $attributes . ' width="' . $size[0] . '" height="' . $size[1] . '"' . $closing . '>';
		}, $html);

		return $result ?? $html;
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
			$output = $this->output;

			// Only HTML pages are post-processed (JSON, XML and other types are left untouched)
			if ($this->image_roots) {
				$content_type = '';

				foreach ($this->headers as $header) {
					if (stripos($header, 'Content-Type:') === 0) {
						$content_type = $header;
					}
				}

				if (stripos($content_type, 'text/html') !== false) {
					$output = $this->addImageDimensions($output);
				}
			}

			if ($this->level) {
				$output = $this->compress($output, $this->level);
			}

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
