<?php
// ---------------------------------
// NivoCart Upgrade Script
// ---------------------------------
// Upgrading directly from OpenCart
// Min. version supported: v1.5.5.1
// ---------------------------------

class ModelUpgrade extends Model {

	public function dataTables($step1) {
		// Load the sql file
		$file = DIR_APPLICATION . 'nivocart-upgrade.sql';

		if (!file_exists($file)) {
			exit('Could not load sql file: ' . $file);
		}

		clearstatcache();

		$string = '';

		$lines = file($file);

		$status = false;

		// Get only the Create statements
		foreach ($lines as $line) {
			// Set any prefix
			$line = str_replace("CREATE TABLE `nc_", "CREATE TABLE `" . DB_PREFIX, $line);

			// If line begins with Create Table, start recording
			if (substr($line, 0, 12) == 'CREATE TABLE') {
				$status = true;
			}

			if ($status) {
				$string .= $line;
			}

			// If line contains ';', stop recording
			if (preg_match('/;/', $line)) {
				$status = false;
			}
		}

		$table_new_data = array();

		// Trim any spaces, and ';'
		$string = trim($string);
		$string = trim($string, ';');

		// Start reading each Create statement
		$statements = explode(';', $string);

		foreach ($statements as $sql) {
			$field_data = array();

			// Get all fields
			preg_match_all('#`(\w[\w\d]*)`\s+((tinyint|smallint|mediumint|bigint|int|tinytext|text|mediumtext|longtext|tinyblob|blob|mediumblob|longblob|varchar|char|datetime|date|float|double|decimal|timestamp|time|year|enum|set|binary|varbinary)(\((.*)\))?){1}\s*(collate (\w+)\s*)?(unsigned\s*)?((NOT\s*NULL\s*)|(NULL\s*))?(auto_increment\s*)?(default \'([^\']*)\'\s*)?#i', $sql, $match);

			foreach (array_keys($match[0]) as $key) {
				$field_data[] = array(
					'name'          => trim($match[1][$key]),
					'type'          => strtoupper(trim($match[3][$key])),
					'size'          => str_replace(array('(', ')'), '', trim($match[4][$key])),
					'sizeext'       => trim($match[6][$key]),
					'collation'     => trim($match[7][$key]),
					'unsigned'      => trim($match[8][$key]),
					'notnull'       => trim($match[9][$key]),
					'autoincrement' => trim($match[12][$key]),
					'default'       => trim($match[14][$key])
				);
			}

			// Get primary keys
			$primary_data = array();

			preg_match('#primary\s*key\s*\([^)]+\)#i', $sql, $match);

			if (isset($match[0])) {
				preg_match_all('#`(\w[\w\d]*)`#', $match[0], $match);
			} else {
				$match = array();
			}

			if ($match) {
				foreach ($match[1] as $primary) {
					$primary_data[] = $primary;
				}
			}

			// Get indexes
			$index_data = array();
			$indexes = array();

			preg_match_all('#key\s*`\w[\w\d]*`\s*\(.*\)#i', $sql, $match);

			foreach ($match[0] as $key) {
				preg_match_all('#`(\w[\w\d]*)`#', $key, $match);

				$indexes[] = $match;
			}

			foreach ($indexes as $index) {
				$key = '';

				foreach ($index[1] as $field) {
					if ($key == '') {
						$key = $field;
					} else {
						$index_data[$key][] = $field;
					}
				}
			}

			// Table options
			$option_data = array();

			preg_match_all('#(\w+)=(\w+)#', $sql, $option);

			foreach (array_keys($option[0]) as $key) {
				$option_data[$option[1][$key]] = $option[2][$key];
			}

			// Get Table Name
			preg_match_all('#create\s*table\s*`(\w[\w\d]*)`#i', $sql, $table);

			if (isset($table[1][0])) {
				$table_new_data[] = array(
					'sql'     => $sql,
					'name'    => $table[1][0],
					'field'   => $field_data,
					'primary' => $primary_data,
					'index'   => $index_data,
					'option'  => $option_data
				);
			}
		}

		$this->db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

		// Get all current tables, fields, type, size, etc..
		$table_old_data = array();

		$table_query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

		foreach ($table_query->rows as $table) {
			if (utf8_substr($table['Tables_in_' . DB_DATABASE], 0, strlen(DB_PREFIX)) == DB_PREFIX) {
				$field_data = array();
				$extended_field_data = array();

				$field_query = $this->db->query("SHOW COLUMNS FROM `" . $table['Tables_in_' . DB_DATABASE] . "`");

				foreach ($field_query->rows as $field) {
					$field_data[] = $field['Field'];
					$extended_field_data[] = $field;
				}

				$table_old_data[$table['Tables_in_' . DB_DATABASE]]['field_list'] = $field_data;
				$table_old_data[$table['Tables_in_' . DB_DATABASE]]['extended_field_data'] = $extended_field_data;
			}
		}

		foreach ($table_new_data as $table) {
			// If table is not found create it
			if (!isset($table_old_data[$table['name']])) {
				$this->db->query($table['sql']);
			} else {
				// DB Engine
				if (isset($table['option']['ENGINE'])) {
					$this->db->query("ALTER TABLE `" . $table['name'] . "` ENGINE = `" . $table['option']['ENGINE'] . "`");
				}

				// Charset
				if (isset($table['option']['CHARSET']) && isset($table['option']['COLLATE'])) {
					$this->db->query("ALTER TABLE `" . $table['name'] . "` DEFAULT CHARACTER SET `" . $table['option']['CHARSET'] . "` COLLATE `" . $table['option']['COLLATE'] . "`");
				}

				set_time_limit(60);

				// Loop through all tables and adjust based on nivocart-upgrade.sql file
				$i = 0;

				foreach ($table['field'] as $field) {
					// If field is not found create it
					if (!in_array($field['name'], $table_old_data[$table['name']]['field_list'])) {
						$status = true;

						foreach ($table_old_data[$table['name']]['extended_field_data'] as $oldfield) {
							if ($oldfield['Extra'] == 'auto_increment' && $field['autoincrement']) {
								$sql = "ALTER TABLE `" . $table['name'] . "` CHANGE `" . $oldfield['Field'] . "` `" . $field['name'] . "` " . strtoupper($field['type']);
								$status = false;
								break;
							}
						}

						if ($status) {
							$sql = "ALTER TABLE `" . $table['name'] . "` ADD `" . $field['name'] . "` " . $field['type'];
						}

						if ($field['size']) {
							$sql .= "(" . $field['size'] . ")";
						}

						if ($field['collation']) {
							$sql .= " " . $field['collation'];
						}

						if ($field['unsigned']) {
							$sql .= " " . $field['unsigned'];
						}

						if ($field['notnull']) {
							$sql .= " " . $field['notnull'];
						}

						if ($field['default'] != '') {
							$sql .= " DEFAULT '" . $field['default'] . "'";
						}

						if (isset($table['field'][$i - 1])) {
							$sql .= " AFTER `" . $table['field'][$i - 1]['name'] . "`";
						} else {
							$sql .= " FIRST";
						}

						$this->db->query($sql);

					} else {
						// Remove auto-increment from all fields
						$sql = "ALTER TABLE `" . $table['name'] . "` CHANGE `" . $field['name'] . "` `" . $field['name'] . "` " . strtoupper($field['type']);

						if ($field['size']) {
							$sql .= "(" . $field['size'] . ")";
						}

						if ($field['collation']) {
							$sql .= " " . $field['collation'];
						}

						if ($field['unsigned']) {
							$sql .= " " . $field['unsigned'];
						}

						if ($field['notnull']) {
							$sql .= " " . $field['notnull'];
						}

						if ($field['default'] != '') {
							$sql .= " DEFAULT '" . $field['default'] . "'";
						}

						if (isset($table['field'][$i - 1])) {
							$sql .= " AFTER `" . $table['field'][$i - 1]['name'] . "`";
						} else {
							$sql .= " FIRST";
						}

						$this->db->query($sql);
					}

					$i++;
				}

				$status = false;

				// Drop primary keys and indexes
				$query = $this->db->query("SHOW INDEXES FROM `" . $table['name'] . "`");

				$last_key_name = '';

				if ($query->num_rows) {
					foreach ($query->rows as $result) {
						if ($result['Key_name'] != 'PRIMARY' && $result['Key_name'] != $last_key_name) {
							$last_key_name = $result['Key_name'];

							$this->db->query("ALTER TABLE `" . $table['name'] . "` DROP INDEX `" . $result['Key_name'] . "`");
						} else {
							$status = true;
						}
					}
				}

				if ($status) {
					$this->db->query("ALTER TABLE `" . $table['name'] . "` DROP PRIMARY KEY");
				}

				// Add a new primary key
				$primary_data = array();

				foreach ($table['primary'] as $primary) {
					$primary_data[] = "`" . $primary . "`";
				}

				if ($primary_data) {
					$this->db->query("ALTER TABLE `" . $table['name'] . "` ADD PRIMARY KEY(" . implode(',', $primary_data) . ")");
				}

				// Add the new indexes
				foreach ($table['index'] as $name => $index) {
					$index_data = array();

					foreach ($index as $key) {
						$index_data[] = '`' . $key . '`';
					}

					if ($index_data) {
						$this->db->query("ALTER TABLE `" . $table['name'] . "` ADD INDEX `" . $name . "` (" . implode(',', $index_data) . ")");
					}
				}

				// Add auto-increment to primary keys again
				foreach ($table['field'] as $field) {
					if ($field['autoincrement']) {
						$sql = "ALTER TABLE `" . $table['name'] . "` CHANGE `" . $field['name'] . "` `" . $field['name'] . "` " . strtoupper($field['type']);

						if ($field['size']) {
							$sql .= "(" . $field['size'] . ")";
						}

						if ($field['collation']) {
							$sql .= " " . $field['collation'];
						}

						if ($field['unsigned']) {
							$sql .= " " . $field['unsigned'];
						}

						if ($field['notnull']) {
							$sql .= " " . $field['notnull'];
						}

						if ($field['default'] != '') {
							$sql .= " DEFAULT '" . $field['default'] . "'";
						}

						if ($field['autoincrement']) {
							$sql .= " AUTO_INCREMENT";
						}

						$this->db->query($sql);
					}
				}
			}
		}

		// Add version
		$this->db->query("INSERT INTO `" . DB_PREFIX . "version` SET `version` = '" . NC_VERSION . "', date_added = NOW()");

		$step1 = true;

		return $step1;
	}

	// -----------------------------------
	// Function to update additional tables
	// -----------------------------------
	public function additionalTables($step2) {
		set_time_limit(60);

		// Add serialized to Setting
		$setting_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' ORDER BY store_id ASC");

		foreach ($setting_query->rows as $setting) {
			if (!$setting['serialized']) {
				$settings[$setting['key']] = $setting['value'];
			} else {
				$settings[$setting['key']] = unserialize($setting['value']);
			}
		}

		// Update the country table (OCE)
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "country' AND COLUMN_NAME = 'name'");

		if ($query->num_rows) {
			// Country 'name' field moved to new country_description table. Need to loop through and move over
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country`");

			foreach ($country_query->rows as $country) {
				$language_query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language`");

				foreach ($language_query->rows as $language) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "country_description` SET country_id = '" . (int)$country['country_id'] . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($country['name']) . "'");
				}
			}
		}

		// Update the manufacturer table (OCE)
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "manufacturer' AND COLUMN_NAME = 'name'");

		if ($query->num_rows) {
			// Manufacturer 'name' field moved to new manufacturer_description table. Need to loop through and move over
			$manufacturer_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer`");

			foreach ($manufacturer_query->rows as $manufacturer) {
				$language_query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language`");

				foreach ($language_query->rows as $language) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "manufacturer_description` SET manufacturer_id = '" . (int)$manufacturer['manufacturer_id'] . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($manufacturer['name']) . "'");
				}
			}
		}

		// Move customer IP blacklist to customer ban IP table (OC)
		$ip_query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "customer_ip_blacklist'");

		if ($ip_query->num_rows) {
			$blacklist_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_ip_blacklist`");

			foreach ($blacklist_query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_ban_ip` SET ip = '" . $this->db->escape($result['ip']) . "'");
			}

			// Drop unused table
			$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_ip_blacklist`");
		}

		// Product tag table to product description tag (OCE)
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "product_tag'");

		if ($query->num_rows) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language`");

			foreach ($query->rows as $language) {
				$query = $this->db->query("SELECT p.product_id, GROUP_CONCAT(DISTINCT pt.tag order by pt.tag ASC SEPARATOR ',') AS tags FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_tag pt ON (p.product_id = pt.product_id) WHERE pt.language_id = '" . (int)$language['language_id'] . "' GROUP BY p.product_id");

				if ($query->num_rows) {
					foreach ($query->rows as $row) {
						$this->db->query("UPDATE " . DB_PREFIX . "product_description SET tag = '" . $this->db->escape(strtolower($row['tags'])) . "' WHERE product_id = '" . (int)$row['product_id'] . "' AND language_id = '" . (int)$language['language_id'] . "'");
					}
				}
			}
		}

		// Drop unused order_fraud table (OCE)
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "order_fraud`");

		flush();

		$step2 = true;

		return $step2;
	}

	// --------------------------------------------------------------------------------
	// Function to repair any erroneous categories that are not in the category path table
	// --------------------------------------------------------------------------------
	public function repairCategories($parent_id = 0, $step3) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category` WHERE parent_id = '" . (int)$parent_id . "'");

		foreach ($query->rows as $category) {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category['category_id'] . "'");

			// Fix for records with no paths
			$level = 0;

			$level_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY `level` ASC");

			foreach ($level_query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', path_id = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', path_id = '" . (int)$category['category_id'] . "', `level` = '" . (int)$level . "'");

			$this->repairCategories($category['category_id'], false);
		}

		// Create system/upload directory
		$upload_directory = DIR_SYSTEM . 'upload/';

		if (!is_dir($upload_directory)) {
			mkdir(DIR_SYSTEM . 'upload', 0777);
		}

		$step3 = true;

		return $step3;
	}

	// -----------------------------------------------
	// Function to update the existing "config.php" files
	//
	// If missing, deprecated or redundant:
	// -----------------------------------
	// Add constant: 'HTTP_IMAGE'
	// Add constant: 'HTTPS_IMAGE'
	// Add constant: 'DIR_UPLOAD'
	// Add constant: 'DIR_VQMOD'
	// Add constant: 'DB_PORT'
	// Replace DB_DRIVER: 'mysql' with 'mysqli'
	// Replace backslashes with slashes
	// Remove PHP closing tag
	// -----------------------------------------------
	public function updateConfig($step4) {
		set_time_limit(60);

		if (is_file(DIR_NIVOCART . 'config.php')) {
			$files = glob(DIR_NIVOCART . '{config.php,admin/config.php}', GLOB_BRACE);

			// Check if config files are writeable
			foreach ($files as $file) {
				if (!is_writable($file)) {
					exit('ATTENTION: ' . $file . ' file is read only. Please adjust and try again.');
				}
			}

			// Add HTTP_IMAGE if missing
			foreach ($files as $file) {
				$upgrade_http = true;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, 'HTTP_IMAGE') !== false) {
						$upgrade_http = false;
						break;
					}
				}

				if ($upgrade_http) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, 'HTTP_SERVER') !== false) {
							$new_line = "define('HTTP_IMAGE', '" . str_replace("\\", "/", HTTP_SERVER) . 'image/' . "');";
							$strip_line = str_replace("/install", "", $new_line);
							$output .= $strip_line . "\n";
							$output .= $line;
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}

			// Add HTTPS_IMAGE if missing
			foreach ($files as $file) {
				$upgrade_https = true;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, 'HTTPS_IMAGE') !== false) {
						$upgrade_https = false;
						break;
					}
				}

				if ($upgrade_https) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, 'HTTPS_SERVER') !== false) {
							$new_line = "define('HTTPS_IMAGE', '" . str_replace("\\", "/", HTTP_SERVER) . 'image/' . "');";
							$strip_line = str_replace("/install", "", $new_line);
							$output .= $strip_line . "\n";
							$output .= $line;
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}

			// Add DIR_UPLOAD if missing
			foreach ($files as $file) {
				$upgrade_upload = true;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, 'DIR_UPLOAD') !== false) {
						$upgrade_upload = false;
						break;
					}
				}

				if ($upgrade_upload) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, 'DIR_DOWNLOAD') !== false) {
							$new_line = "define('DIR_UPLOAD', '" . str_replace("\\", "/", DIR_SYSTEM) . 'upload/' . "');";
							$output .= $new_line . "\n";
							$output .= $line;
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}

			// Add DIR_VQMOD if missing
			foreach ($files as $file) {
				$upgrade_vqmod = true;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, 'DIR_VQMOD') !== false) {
						$upgrade_vqmod = false;
						break;
					}
				}

				if ($upgrade_vqmod) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, 'DIR_LOGS') !== false) {
							$new_line = "define('DIR_VQMOD', '" . str_replace("\\", "/", DIR_NIVOCART) . 'vqmod/' . "');";
							$output .= $new_line . "\n";
							$output .= $line;
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}

			// Add DB_PORT if missing
			foreach ($files as $file) {
				$upgrade_port = true;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos(strtoupper($line), 'DB_PORT') !== false) {
						$upgrade_port = false;
						break;
					}
				}

				if ($upgrade_port) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, 'DB_PREFIX') !== false) {
							$new_line = "define('DB_PORT', '" . ini_get('mysqli.default_port') . "');";
							$output .= $new_line . "\n";
							$output .= $line;
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}

			// Replace deprecated mysql with mysqli
			foreach ($files as $file) {
				$upgrade_mysql = false;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, "'mysql'") !== false) {
						$upgrade_mysql = true;
						break;
					}
				}

				if ($upgrade_mysql) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, "'mysql'") !== false) {
							$new_line = "define('DB_DRIVER', 'mysqli');";
							$output .= $new_line . "\n";
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}

			// Replace all "\" in URLs with "/"
			foreach ($files as $file) {
				$upgrade_slashes = false;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, "\\") !== false) {
						$upgrade_slashes = true;
						break;
					}
				}

				if ($upgrade_slashes) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						$output .= str_replace("\\", "/", $line);
					}

					file_put_contents($file, $output);
				}
			}

			// Remove redundant PHP closing tag
			foreach ($files as $file) {
				$upgrade_tag = false;

				$lines = file($file);

				foreach ($lines as $line) {
					if (strpos($line, "?>") !== false) {
						$upgrade_tag = true;
						break;
					}
				}

				if ($upgrade_tag) {
					$output = '';

					foreach ($lines as $line_id => $line) {
						if (strpos($line, "?>") !== false) {
							$output .= str_replace("?>", "", $line);
						} else {
							$output .= $line;
						}
					}

					file_put_contents($file, $output);
				}
			}
		}

		clearstatcache();

		flush();

		$step4 = true;

		return $step4;
	}

	// ------------------------------------
	// Function to update the layout routes
	// ------------------------------------
	public function updateLayouts($step5) {
		// Get stores
		$stores = array(0);

		$sql = "SELECT store_id FROM " . DB_PREFIX . "store";

		$query_store = $this->db->query($sql);

		foreach ($query_store->rows as $store) {
			$stores[] = $store['store_id'];
		}

		// Create News layout
		$sql = "SELECT layout_id FROM `" . DB_PREFIX . "layout` WHERE name LIKE 'News' LIMIT 0,1";

		$query_name = $this->db->query($sql);

		if ($query_name->num_rows == 0) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "layout` SET name = 'News'");
		}

		// Add News routes
		$news_routes = array('information/news', 'information/news_list');

		foreach ($stores as $store_id) {
			foreach ($news_routes as $news_route) {
				$sql = "SELECT layout_id FROM `" . DB_PREFIX . "layout_route` WHERE store_id = '" . (int)$store_id . "' AND `route` LIKE '" . $news_route . "' LIMIT 0,1";

				$query = $this->db->query($sql);

				if ($query->num_rows == 0) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "layout_route` SET layout_id = (SELECT DISTINCT layout_id FROM `" . DB_PREFIX . "layout` WHERE name = 'News'), store_id = '" . (int)$store_id . "', `route` = '" . $news_route . "'");
				}
			}
		}

		// Create Special layout
		$sql = "SELECT layout_id FROM `" . DB_PREFIX . "layout` WHERE name LIKE 'Special' LIMIT 0,1";

		$query_name = $this->db->query($sql);

		if ($query_name->num_rows == 0) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "layout` SET name = 'Special'");
		}

		// Add Special routes
		$special_routes = array('product/special');

		foreach ($stores as $store_id) {
			foreach ($special_routes as $special_route) {
				$sql = "SELECT layout_id FROM `" . DB_PREFIX . "layout_route` WHERE store_id = '" . (int)$store_id . "' AND `route` LIKE '" . $special_route . "' LIMIT 0,1";

				$query = $this->db->query($sql);

				if ($query->num_rows == 0) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "layout_route` SET layout_id = (SELECT DISTINCT layout_id FROM `" . DB_PREFIX . "layout` WHERE name = 'Special'), store_id = '" . (int)$store_id . "', `route` = '" . $special_route . "'");
				}
			}
		}

		$step5 = true;

		return $step5;
	}

	// ------------------------------------
	// Function to update fields and finalize
	// ------------------------------------
	public function updateFields() {
		$country_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "country' AND COLUMN_NAME = 'name'");

		if ($country_query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "country` DROP name");
		}

		$manufacturer_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "manufacturer' AND COLUMN_NAME = 'name'");

		if ($manufacturer_query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` DROP name");
		}
	}
}
