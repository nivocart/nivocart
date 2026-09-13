<?php
/**
 * Class ModelToolSystem
 *
 * @package NivoCart
 */
class ModelToolSystem extends Model {
	/**
	 * getRewriteBase (Catalog)
	 *
	 * Returns .htaccess RewriteBase string
	 *
	 * Required by ControllerCommonSeoUrl
	 */
	public function getRewriteBase(): string {
		// get path
		$path_info = pathinfo($_SERVER['PHP_SELF']);

		// trim url to the root folder (result: /nivocart, or '' for root installs)
		$base_path = $path_info['dirname'];

		// pathinfo() returns '/' for root-installed sites (e.g. PHP_SELF = /index.php).
		// Using '/' here causes the canonical redirect to produce '//slug', which the
		// browser treats as a protocol-relative URL (https://slug) and fails.
		// Return '' for root so the redirect becomes '/slug' — correct behaviour.
		return ($base_path === '/') ? '' : rtrim($base_path, '/');
	}
}
