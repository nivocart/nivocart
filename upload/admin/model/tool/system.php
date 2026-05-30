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

			// Derive RewriteBase by climbing up from admin/index.php
			// dirname once = admin folder, dirname twice = web root or subfolder
			$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');

			$path = $base ? $base . '/' : '/';

			$document = str_replace('RewriteBase /', 'RewriteBase ' . $path, $document);

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
	 */
	public function getRewriteBase(): string {
		$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');

		return $base ? $base . '/' : '/';
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
