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

		// trim url to the root folder (result: /nivocart)
		$base_path = $path_info['dirname'];

		return $base_path;
	}
}
