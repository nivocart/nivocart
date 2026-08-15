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

	/**
	 * checkForUpdate
	 *
	 * Checks GitHub Releases API for the latest NivoCart version.
	 * Result is cached in nc_setting for 24 hours to avoid excessive API calls.
	 *
	 * @return array
	 */
	public function checkForUpdate(): array {
		$cache_key = 'nivocart_update_check';
		$cache_ts_key = 'nivocart_update_check_ts';
		$cache_seconds = 86400; // 24 hours

		// Check cached result
		$cached_ts = $this->db->query("SELECT value FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $this->db->escape($cache_ts_key) . "' AND store_id = '0' LIMIT 1");

		if ($cached_ts->num_rows) {
			$age = time() - (int)$cached_ts->row['value'];

			if ($age < $cache_seconds) {
				$cached = $this->db->query("SELECT value FROM `" . DB_PREFIX . "setting` WHERE `key` = '" . $this->db->escape($cache_key) . "' AND store_id = '0' LIMIT 1");

				if ($cached->num_rows) {
					$result = json_decode($cached->row['value'], true);
					$result['cached'] = true;
					$result['cache_age'] = $age;

					return $result;
				}
			}
		}

		// Hit the GitHub API
		$api_url = 'https://api.github.com/repos/nivocart/nivocart/releases/latest';

		$check = curl_init();

		curl_setopt_array($check, [
			CURLOPT_URL            => $api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 10,
			CURLOPT_HTTPHEADER     => [
				'User-Agent: NivoCart/' . NC_VERSION,
				'Accept: application/vnd.github+json',
			],
		]);

		$response = curl_exec($check);
		$error = curl_error($check);

		unset($check);

		if ($error || !$response) {
			return ['status' => 'error', 'message' => 'Could not reach update server.', 'cached' => false];
		}

		$data = json_decode($response, true);

		if (empty($data['tag_name'])) {
			return ['status' => 'error', 'message' => 'Invalid response from update server.', 'cached' => false];
		}

		$latest = ltrim($data['tag_name'], 'v');
		$current = ltrim(NC_VERSION, 'v');

		$result = [
			'status'        => 'ok',
			'update'        => version_compare($latest, $current, '>'),
			'current'       => $current,
			'latest'        => $latest,
			'release_notes' => $data['body'] ?? '',
			'release_url'   => $data['html_url'] ?? '',
			'released_at'   => $data['published_at'] ?? '',
			'cached'        => false,
			'cache_age'     => 0,
		];

		// Cache the result
		$json = $this->db->escape(json_encode($result));
		$now = time();

		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `key` IN ('" . $this->db->escape($cache_key) . "', '" . $this->db->escape($cache_ts_key) . "')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '0', `group` = 'nivocart', `key` = '" . $this->db->escape($cache_key) . "', `value` = '" . $json . "'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '0', `group` = 'nivocart', `key` = '" . $this->db->escape($cache_ts_key) . "', `value` = '" . (int)$now . "'");

		return $result;
	}

	/**
	 * getUpdateCacheAge
	 *
	 * Returns seconds since the last update check, or null if never checked.
	 *
	 * @return int|null
	 */
	public function getUpdateCacheAge(): ?int {
		$result = $this->db->query("SELECT value FROM `" . DB_PREFIX . "setting` WHERE `key` = 'nivocart_update_check_ts' AND store_id = '0' LIMIT 1");

		if ($result->num_rows) {
			return time() - (int)$result->row['value'];
		}

		return null;
	}
}
