<?php
/**
 * Class ModelToolSystem
 *
 * @package NivoCart
 */
class ModelToolSystem extends Model {
	/**
	 * deleteDirectory
	 *
	 * $var $dir directory name
	 *
	 * Required by Image Manager
	 */
	public function deleteDirectory($dir) {
		if (!file_exists($dir)) {
			return true;
		}

		if (!is_dir($dir) || is_link($dir)) {
			return unlink($dir);
		}

		clearstatcache();

		foreach (scandir($dir) as $item) {
			if ($item === '.' || $item === '..') {
				continue;
			}

			if (!$this->deleteDirectory($dir . "/" . $item)) {
				chmod($dir . "/" . $item, 0777);

				if (!$this->deleteDirectory($dir . "/" . $item)) {
					return false;
				}
			}
		}

		return rmdir($dir);
	}

	/**
	 * setupSeo
	 *
	 * Required by Settings
	 */
	public function setupSeo() {
		if (file_exists('../.htaccess')) {
			return;
		}

		if (function_exists('apache_get_modules')) {
			$mod_rewrite = in_array('mod_rewrite', apache_get_modules(), true);
		} else {
			$mod_rewrite = ((isset($_SERVER['HTTP_MOD_REWRITE']) && strtolower($_SERVER['HTTP_MOD_REWRITE']) === 'on') || strtolower(getenv('HTTP_MOD_REWRITE')) === 'on');
		}

		if ($mod_rewrite && file_exists('../.htaccess.txt')) {
			$document = file_get_contents('../.htaccess.txt');

			// Correctly extract the RewriteBase path
			$root = rtrim(HTTP_SERVER, '/');
			$folder = substr(strrchr($root, '/'), 1);
			$script_dir = dirname($_SERVER['SCRIPT_NAME']);
			$path = str_replace('/' . $folder, '', $script_dir);
			$path = rtrim($path, '/') . '/';

			if (!$path) {
				$path = '/';
			}

			$document = str_replace('RewriteBase /', 'RewriteBase ' . $path, $document);

			// Write to file first, then rename
			file_put_contents('../.htaccess.txt', $document);

			rename('../.htaccess.txt', '../.htaccess');

			chmod('../.htaccess', 0644);
		}

		clearstatcache();
	}

	/**
	 * getRewriteBase (Admin)
	 *
	 * Returns .htaccess RewriteBase string
	 *
	 * Required by ModelToolSeoUrl controller
	 */
	public function getRewriteBase(): string {
		// get path
		$path_info = pathinfo($_SERVER['PHP_SELF']);

		// trim url to the root folders
		$path = $path_info['dirname'];

		// trim folder on the right of first /
		$removed = strrchr($path, '/');

		// trim string with empty (result: /nivocart)
		$base_path = str_replace($removed, '', $path);

		return $base_path;
	}

	/**
	 * Token Generator
	 *
	 * @param int $length Number of random bytes (output will be 2× this as hex)
	 *
	 * @return string
	 */
	public function token(int $length = 32): string {
		return bin2hex(random_bytes($length));
	}
}
