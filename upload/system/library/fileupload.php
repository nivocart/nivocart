<?php
/**
 * Library Class FileUpload
 *
 * Server-side validation for files uploaded by visitors. Never trust anything the browser sends
 * (file name case, MIME type): the extension is normalised, the MIME type is detected from the
 * actual file contents and the contents are scanned for active (executable) content.
 *
 * @package NivoCart
 */
class FileUpload {
	/**
	 * Extensions that can run code or be rendered as active content by a browser.
	 * These are refused even if they have been added to the allowed list in Settings.
	 *
	 * @var array<int, string>
	 */
	private array $blocked_extensions = [
		'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'pht', 'phtml', 'phps', 'phar', 'inc',
		'html', 'htm', 'xhtml', 'shtml', 'svg', 'svgz', 'xml', 'xsl', 'xslt', 'swf',
		'js', 'mjs', 'cgi', 'pl', 'py', 'sh', 'asp', 'aspx', 'jsp', 'exe', 'dll', 'bat', 'cmd',
		'htaccess', 'htpasswd', 'ini'
	];

	/**
	 * MIME types that a browser renders as active content.
	 *
	 * @var array<int, string>
	 */
	private array $blocked_mimes = [
		'image/svg+xml', 'text/html', 'application/xhtml+xml', 'text/xml', 'application/xml',
		'text/javascript', 'application/javascript', 'application/x-javascript', 'application/x-httpd-php',
		'text/x-php', 'application/x-shockwave-flash'
	];

	/**
	 * Validate
	 *
	 * @param string $tmp_name   Path of the uploaded temporary file
	 * @param string $filename   Sanitized original file name
	 * @param string $extensions Allowed extensions, one per line
	 * @param string $mimes      Allowed MIME types, one per line
	 *
	 * @return bool True if the file is safe to store
	 */
	public function validate(string $tmp_name, string $filename, string $extensions, string $mimes): bool {
		if (!is_file($tmp_name)) {
			return false;
		}

		// Extension: case-insensitive, and every extension in the name is checked (e.g. file.php.jpg)
		$parts = explode('.', strtolower($filename));

		if (count($parts) < 2) {
			return false;
		}

		array_shift($parts);

		$extension = (string)end($parts);

		foreach ($parts as $part) {
			if (in_array($part, $this->blocked_extensions, true)) {
				return false;
			}
		}

		if (!in_array($extension, $this->getList($extensions), true)) {
			return false;
		}

		// MIME type: detected from the file itself, not taken from the request
		if (!class_exists('finfo')) {
			return false;
		}

		$finfo = new finfo(FILEINFO_MIME_TYPE);

		$mime = $finfo->file($tmp_name);

		if (!is_string($mime)) {
			return false;
		}

		$mime = strtolower($mime);

		if (in_array($mime, $this->blocked_mimes, true) || !in_array($mime, $this->getList($mimes), true)) {
			return false;
		}

		// Contents: no PHP tags and no HTML/SVG/script markup
		$content = file_get_contents($tmp_name);

		if ($content === false || preg_match('/<\?(?:php|=)|<\?\s|<script|<svg|<html|<iframe|<object|<embed|<!doctype\s+html/i', $content)) {
			return false;
		}

		return true;
	}

	/**
	 * Convert a one-per-line setting into a clean lowercase list
	 *
	 * @param string $setting
	 *
	 * @return array<int, string>
	 */
	private function getList(string $setting): array {
		$list = [];

		foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", $setting)) as $item) {
			$item = strtolower(trim($item));

			if ($item !== '') {
				$list[] = $item;
			}
		}

		return $list;
	}
}
