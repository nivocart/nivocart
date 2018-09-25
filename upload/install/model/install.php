<?php
class ModelInstall extends Model {

	public function database($data) {
		$db = new DB($data['db_driver'], htmlspecialchars_decode($data['db_hostname']), htmlspecialchars_decode($data['db_username']), htmlspecialchars_decode($data['db_password']), htmlspecialchars_decode($data['db_database']), $data['db_port']);

		if (isset($data['demo_data'])) {
			$file = DIR_APPLICATION . 'nivocart-clean.sql';

			if (!file_exists($file)) {
				exit('Could not load SQL file: ' . $file);
			}
		} else {
			$file = DIR_APPLICATION . 'nivocart.sql';

			if (!file_exists($file)) {
				exit('Could not load SQL file: ' . $file);
			}
		}

		clearstatcache();

		set_time_limit(60);

		$lines = file($file);

		if ($lines) {
			$sql = '';

			foreach ($lines as $line) {
				if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
					$sql .= $line;

					if (preg_match('/;\s*$/', $line)) {
						$sql = str_replace("DROP TABLE IF EXISTS `nc_", "DROP TABLE IF EXISTS `" . $data['db_prefix'], $sql);
						$sql = str_replace("CREATE TABLE `nc_", "CREATE TABLE `" . $data['db_prefix'], $sql);
						$sql = str_replace("INSERT INTO `nc_", "INSERT INTO `" . $data['db_prefix'], $sql);

						$db->query($sql);

						$sql = '';
					}
				}
			}

			$db->query("SET CHARACTER SET utf8");

			$db->query("SET @@session.sql_mode = 'MYSQL40'");

			$db->query("DELETE FROM `" . $data['db_prefix'] . "user` WHERE user_id = '1'");
			$db->query("INSERT INTO `" . $data['db_prefix'] . "user` SET user_id = '1', user_group_id = '1', username = '" . $db->escape($data['username']) . "', salt = '" . $db->escape($salt = substr(md5(uniqid(rand(), true)), 0, 9)) . "', password = '" . $db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', status = '1', email = '" . $db->escape($data['email']) . "', date_added = NOW()");

			$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_email'");
			$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `group` = 'config', `key` = 'config_email', `value` = '" . $db->escape($data['email']) . "'");

			$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_url'");
			$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `group` = 'config', `key` = 'config_url', `value` = '" . $db->escape(HTTP_NIVOCART) . "'");

			$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_encryption'");
			$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `group` = 'config', `key` = 'config_encryption', `value` = '" . $db->escape(hash_rand('ripemd128')) . "'");

			$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_maintenance'");
			$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `group` = 'config', `key` = 'config_maintenance', `value` = '" . (isset($data['maintenance']) ? 1 : 0) . "'");

			$db->query("INSERT INTO `" . $data['db_prefix'] . "version` SET `version` = '" . $db->escape(NC_VERSION) . "', date_added = NOW()");
		}

		// Write Catalog config.php
		$output = '<?php' . "\n";
		$output .= '// HTTP' . "\n";
		$output .= 'define(\'HTTP_SERVER\', \'' . HTTP_NIVOCART . '\');' . "\n";
		$output .= 'define(\'HTTP_IMAGE\', \'' . HTTP_NIVOCART . 'image/\');' . "\n\n";

		$output .= '// HTTPS' . "\n";
		$output .= 'define(\'HTTPS_SERVER\', \'' . HTTP_NIVOCART . '\');' . "\n";
		$output .= 'define(\'HTTPS_IMAGE\', \'' . HTTP_NIVOCART . 'image/\');' . "\n\n";

		$output .= '// DIR' . "\n";
		$output .= 'define(\'DIR_APPLICATION\', \'' . DIR_NIVOCART . 'catalog/\');' . "\n";
		$output .= 'define(\'DIR_SYSTEM\', \'' . DIR_NIVOCART. 'system/\');' . "\n";
		$output .= 'define(\'DIR_DATABASE\', \'' . DIR_NIVOCART . 'system/database/\');' . "\n";
		$output .= 'define(\'DIR_LANGUAGE\', \'' . DIR_NIVOCART . 'catalog/language/\');' . "\n";
		$output .= 'define(\'DIR_TEMPLATE\', \'' . DIR_NIVOCART . 'catalog/view/theme/\');' . "\n";
		$output .= 'define(\'DIR_CONFIG\', \'' . DIR_NIVOCART . 'system/config/\');' . "\n";
		$output .= 'define(\'DIR_IMAGE\', \'' . DIR_NIVOCART . 'image/\');' . "\n";
		$output .= 'define(\'DIR_CACHE\', \'' . DIR_NIVOCART . 'system/cache/\');' . "\n";
		$output .= 'define(\'DIR_UPLOAD\', \'' . DIR_NIVOCART . 'system/upload/\');' . "\n";
		$output .= 'define(\'DIR_DOWNLOAD\', \'' . DIR_NIVOCART . 'download/\');' . "\n";
		$output .= 'define(\'DIR_VQMOD\', \'' . DIR_NIVOCART . 'vqmod/\');' . "\n";
		$output .= 'define(\'DIR_LOGS\', \'' . DIR_NIVOCART . 'system/logs/\');' . "\n\n";

		$output .= '// DB' . "\n";
		$output .= 'define(\'DB_DRIVER\', \'' . addslashes($data['db_driver']) . '\');' . "\n";
		$output .= 'define(\'DB_HOSTNAME\', \'' . addslashes($data['db_hostname']) . '\');' . "\n";
		$output .= 'define(\'DB_USERNAME\', \'' . addslashes($data['db_username']) . '\');' . "\n";
		$output .= 'define(\'DB_PASSWORD\', \'' . addslashes(html_entity_decode($data['db_password'], ENT_QUOTES, 'UTF-8')) . '\');' . "\n";
		$output .= 'define(\'DB_DATABASE\', \'' . addslashes($data['db_database']) . '\');' . "\n";
		$output .= 'define(\'DB_PORT\', \'' . addslashes($data['db_port']) . '\');' . "\n";
		$output .= 'define(\'DB_PREFIX\', \'' . addslashes($data['db_prefix']) . '\');' . "\n";

		$file_catalog = fopen(DIR_NIVOCART . 'config.php', 'w');

		fwrite($file_catalog, $output);

		fclose($file_catalog);

		// Write Admin config.php
		$output = '<?php' . "\n";
		$output .= '// HTTP' . "\n";
		$output .= 'define(\'HTTP_SERVER\', \'' . HTTP_NIVOCART . 'admin/\');' . "\n";
		$output .= 'define(\'HTTP_IMAGE\', \'' . HTTP_NIVOCART . 'image/\');' . "\n";
		$output .= 'define(\'HTTP_CATALOG\', \'' . HTTP_NIVOCART . '\');' . "\n\n";

		$output .= '// HTTPS' . "\n";
		$output .= 'define(\'HTTPS_SERVER\', \'' . HTTP_NIVOCART . 'admin/\');' . "\n";
		$output .= 'define(\'HTTPS_IMAGE\', \'' . HTTP_NIVOCART . 'image/\');' . "\n";
		$output .= 'define(\'HTTPS_CATALOG\', \'' . HTTP_NIVOCART . '\');' . "\n\n";

		$output .= '// DIR' . "\n";
		$output .= 'define(\'DIR_APPLICATION\', \'' . DIR_NIVOCART . 'admin/\');' . "\n";
		$output .= 'define(\'DIR_SYSTEM\', \'' . DIR_NIVOCART . 'system/\');' . "\n";
		$output .= 'define(\'DIR_DATABASE\', \'' . DIR_NIVOCART . 'system/database/\');' . "\n";
		$output .= 'define(\'DIR_LANGUAGE\', \'' . DIR_NIVOCART . 'admin/language/\');' . "\n";
		$output .= 'define(\'DIR_TEMPLATE\', \'' . DIR_NIVOCART . 'admin/view/template/\');' . "\n";
		$output .= 'define(\'DIR_CONFIG\', \'' . DIR_NIVOCART . 'system/config/\');' . "\n";
		$output .= 'define(\'DIR_IMAGE\', \'' . DIR_NIVOCART . 'image/\');' . "\n";
		$output .= 'define(\'DIR_CACHE\', \'' . DIR_NIVOCART . 'system/cache/\');' . "\n";
		$output .= 'define(\'DIR_UPLOAD\', \'' . DIR_NIVOCART . 'system/upload/\');' . "\n";
		$output .= 'define(\'DIR_DOWNLOAD\', \'' . DIR_NIVOCART . 'download/\');' . "\n";
		$output .= 'define(\'DIR_VQMOD\', \'' . DIR_NIVOCART . 'vqmod/\');' . "\n";
		$output .= 'define(\'DIR_LOGS\', \'' . DIR_NIVOCART . 'system/logs/\');' . "\n";
		$output .= 'define(\'DIR_CATALOG\', \'' . DIR_NIVOCART . 'catalog/\');' . "\n\n";

		$output .= '// DB' . "\n";
		$output .= 'define(\'DB_DRIVER\', \'' . addslashes($data['db_driver']) . '\');' . "\n";
		$output .= 'define(\'DB_HOSTNAME\', \'' . addslashes($data['db_hostname']) . '\');' . "\n";
		$output .= 'define(\'DB_USERNAME\', \'' . addslashes($data['db_username']) . '\');' . "\n";
		$output .= 'define(\'DB_PASSWORD\', \'' . addslashes(html_entity_decode($data['db_password'], ENT_QUOTES, 'UTF-8')) . '\');' . "\n";
		$output .= 'define(\'DB_DATABASE\', \'' . addslashes($data['db_database']) . '\');' . "\n";
		$output .= 'define(\'DB_PORT\', \'' . addslashes($data['db_port']) . '\');' . "\n";
		$output .= 'define(\'DB_PREFIX\', \'' . addslashes($data['db_prefix']) . '\');' . "\n";

		$file_admin = fopen(DIR_NIVOCART . 'admin/config.php', 'w');

		fwrite($file_admin, $output);

		fclose($file_admin);

		// Convert .htaccess
		if (isset($data['rewrite'])) {
			$mod_rewrite = false;

			if (function_exists('apache_get_modules')) {
				$apache_modules = apache_get_modules();

				$mod_rewrite = (in_array('mod_rewrite', $apache_modules, true)) ? true : false;
			} else {
				$mod_rewrite = ((isset($_SERVER['HTTP_MOD_REWRITE']) && strtolower($_SERVER['HTTP_MOD_REWRITE']) == 'on') || strtolower(getenv('HTTP_MOD_REWRITE')) == 'on') ? true : false;
			}

			if ($mod_rewrite && file_exists('../.htaccess.txt') && is_writable('../.htaccess.txt')) {
				$file = fopen('../.htaccess.txt', 'a');

				$document = file_get_contents('../.htaccess.txt');

				$root = rtrim(HTTP_SERVER, '/');

				$folder = substr(strrchr($root, '/'), 1);

				$path = rtrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), ''), '/' . $folder . '.\\');

				if (strlen($path) > 1) {
					$path .= '/';
				}

				if (!$path) {
					$path = '/';
				}

				$document = str_replace('RewriteBase /', 'RewriteBase ' . $path, $document);

				file_put_contents('../.htaccess.txt', $document);

				fflush($file);

				fclose($file);

				rename('../.htaccess.txt', '../.htaccess');

				$db->query("DELETE FROM `" . $data['db_prefix'] . "setting` WHERE `key` = 'config_seo_url'");
				$db->query("INSERT INTO `" . $data['db_prefix'] . "setting` SET `group` = 'config', `key` = 'config_seo_url', `value` = '" . (isset($data['rewrite']) ? 1 : 0) . "'");

				clearstatcache();
			}
		}
	}
}
