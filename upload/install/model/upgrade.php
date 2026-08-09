<?php
/**
 * Class ModelUpgrade
 *
 * NivoCart Upgrade Script — minimum supported version: v2.0.0
 *
 * @package NivoCart
 */
class ModelUpgrade extends Model {
	/* Error Array Placeholder */

	// Legacy tables removed in NivoCart v2.2.0 and later
	public function dropLegacyTables(): bool {
		$this->db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

		$legacy_tables = [
			DB_PREFIX . 'product_tax_local_rate',
			DB_PREFIX . 'tax_local_rate',
		];

		foreach ($legacy_tables as $table) {
			$this->db->query("DROP TABLE IF EXISTS `{$table}`");
		}

		return true;
	}

	// Perform table upgrades as required
	public function dataTables(): bool {
		$file = DIR_APPLICATION . 'nivocart-upgrade.sql';

		if (!file_exists($file)) {
			exit('Could not load sql file: ' . $file);
		}

		clearstatcache();

		$string = '';
		$status = false;

		foreach (file($file) as $line) {
			$line = str_replace('CREATE TABLE `nc_', 'CREATE TABLE `' . DB_PREFIX, $line);

			if (str_starts_with($line, 'CREATE TABLE')) {
				$status = true;
			}

			if ($status) {
				$string .= $line;
			}

			if (str_contains($line, ';')) {
				$status = false;
			}
		}

		$string = trim($string, " \t\n\r\0\x0B;");
		$statements = explode(';', $string);

		$table_new_data = [];

		foreach ($statements as $sql) {
			$field_data = [];

			preg_match_all(
				'#`(\w[\w\d]*)`\s+((tinyint|smallint|mediumint|bigint|int|tinytext|text|mediumtext|longtext|tinyblob|blob|mediumblob|longblob|varchar|char|datetime|date|float|double|decimal|timestamp|time|year|enum|set|binary|varbinary)(\((.*)\))?){1}\s*(collate (\w+)\s*)?(unsigned\s*)?((NOT\s*NULL\s*)|(NULL\s*))?(auto_increment\s*)?(default \'([^\']*)\'\s*)?#i',
				$sql,
				$match
			);

			foreach (array_keys($match[0]) as $key) {
				$field_data[] = [
					'name'          => trim($match[1][$key]),
					'type'          => strtoupper(trim($match[3][$key])),
					'size'          => str_replace(['(', ')'], '', trim($match[4][$key])),
					'sizeext'       => trim($match[6][$key]),
					'collation'     => trim($match[7][$key]),
					'unsigned'      => trim($match[8][$key]),
					'notnull'       => trim($match[9][$key]),
					'autoincrement' => trim($match[12][$key]),
					'default'       => trim($match[14][$key]),
				];
			}

			// Primary keys
			$primary_data = [];
			preg_match('#primary\s*key\s*\([^)]+\)#i', $sql, $match);

			if (!empty($match[0])) {
				preg_match_all('#`(\w[\w\d]*)`#', $match[0], $match);
				$primary_data = $match[1] ?? [];
			}

			// Indexes
			$index_data = [];
			preg_match_all('#key\s*`\w[\w\d]*`\s*\(.*\)#i', $sql, $match);

			foreach ($match[0] as $key) {
				preg_match_all('#`(\w[\w\d]*)`#', $key, $parts);
				$fields = $parts[1] ?? [];

				if (count($fields) >= 2) {
					$name = array_shift($fields);
					$index_data[$name] = $fields;
				}
			}

			// Table options
			$option_data = [];
			preg_match_all('#(\w+)=(\w+)#', $sql, $option);

			foreach (array_keys($option[0]) as $key) {
				$option_data[$option[1][$key]] = $option[2][$key];
			}

			// Table name
			preg_match_all('#create\s*table\s*`(\w[\w\d]*)`#i', $sql, $table);

			if (isset($table[1][0])) {
				$table_new_data[] = [
					'sql'     => $sql,
					'name'    => $table[1][0],
					'field'   => $field_data,
					'primary' => $primary_data,
					'index'   => $index_data,
					'option'  => $option_data,
				];
			}
		}

		$this->db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);

		// Snapshot existing tables
		$table_old_data = [];

		$table_query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

		foreach ($table_query->rows as $row) {
			$table_name = $row['Tables_in_' . DB_DATABASE];

			if (mb_substr($table_name, 0, mb_strlen(DB_PREFIX, 'UTF-8'), 'UTF-8') !== DB_PREFIX) {
				continue;
			}

			$field_list = [];
			$extended_field_data = [];

			foreach ($this->db->query("SHOW COLUMNS FROM `{$table_name}`")->rows as $field) {
				$field_list[] = $field['Field'];
				$extended_field_data[] = $field;
			}

			$table_old_data[$table_name] = [
				'field_list'          => $field_list,
				'extended_field_data' => $extended_field_data,
			];
		}

		foreach ($table_new_data as $table) {
			if (!isset($table_old_data[$table['name']])) {
				// Table is new — create it
				$this->db->query($table['sql']);
				continue;
			}

			// Engine
			if (isset($table['option']['ENGINE'])) {
				$this->db->query("ALTER TABLE `{$table['name']}` ENGINE = `{$table['option']['ENGINE']}`");
			}

			// Charset / collation
			if (isset($table['option']['CHARSET'], $table['option']['COLLATE'])) {
				$this->db->query("ALTER TABLE `{$table['name']}` DEFAULT CHARACTER SET `{$table['option']['CHARSET']}` COLLATE `{$table['option']['COLLATE']}`");
			}

			set_time_limit(60);

			$old = $table_old_data[$table['name']];

			foreach ($table['field'] as $i => $field) {
				$after = isset($table['field'][$i - 1]) ? " AFTER `{$table['field'][$i - 1]['name']}`" : ' FIRST';

				$definition = $this->buildFieldDefinition($field);

				if (!in_array($field['name'], $old['field_list'])) {
					// Check if this is a renamed auto-increment column
					$renamed = false;

					foreach ($old['extended_field_data'] as $oldfield) {
						if ($oldfield['Extra'] === 'auto_increment' && $field['autoincrement']) {
							$this->db->query("ALTER TABLE `{$table['name']}` CHANGE `{$oldfield['Field']}` `{$field['name']}` {$definition}{$after}");
							$renamed = true;
							break;
						}
					}

					if (!$renamed) {
						$this->db->query("ALTER TABLE `{$table['name']}` ADD `{$field['name']}` {$definition}{$after}");
					}
				} else {
					// Modify existing field (strips auto_increment before re-adding below)
					$this->db->query("ALTER TABLE `{$table['name']}` CHANGE `{$field['name']}` `{$field['name']}` {$definition}{$after}");
				}
			}

			// Drop non-primary indexes, then primary key
			$has_primary = false;
			$index_query = $this->db->query("SHOW INDEXES FROM `{$table['name']}`");
			$seen_keys = [];

			foreach ($index_query->rows as $result) {
				if ($result['Key_name'] === 'PRIMARY') {
					$has_primary = true;
				} elseif (!in_array($result['Key_name'], $seen_keys)) {
					$seen_keys[] = $result['Key_name'];
					$this->db->query("ALTER TABLE `{$table['name']}` DROP INDEX `{$result['Key_name']}`");
				}
			}

			if ($has_primary) {
				$this->db->query("ALTER TABLE `{$table['name']}` DROP PRIMARY KEY");
			}

			// Re-add primary key
			if ($table['primary']) {
				$cols = implode(',', array_map(fn($p) => "`{$p}`", $table['primary']));
				$this->db->query("ALTER TABLE `{$table['name']}` ADD PRIMARY KEY({$cols})");
			}

			// Re-add indexes
			foreach ($table['index'] as $name => $cols) {
				$col_list = implode(',', array_map(fn($c) => "`{$c}`", $cols));
				$this->db->query("ALTER TABLE `{$table['name']}` ADD INDEX `{$name}` ({$col_list})");
			}

			// Re-add auto_increment
			foreach ($table['field'] as $field) {
				if ($field['autoincrement']) {
					$definition = $this->buildFieldDefinition($field, true);
					$this->db->query("ALTER TABLE `{$table['name']}` CHANGE `{$field['name']}` `{$field['name']}` {$definition}");
				}
			}
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "version` SET `version` = '" . NC_VERSION . "', date_added = NOW()");

		return true;
	}

	public function additionalTables(): bool {
		set_time_limit(60);

		$setting_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' ORDER BY store_id ASC");

		$settings = [];

		foreach ($setting_query->rows as $setting) {
			$settings[$setting['key']] = $setting['serialized'] ? ($setting['value'] ? json_decode($setting['value'], true) : []) : $setting['value'];
		}

		// No additional table changes at this time.

		flush();

		return true;
	}

	public function repairCategories(int $parent_id = 0): bool {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category` WHERE parent_id = '" . (int)$parent_id . "'");

		foreach ($query->rows as $category) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category['category_id'] . "'");

			$level = 0;
			$level_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY `level` ASC");

			foreach ($level_query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', path_id = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");
				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', path_id = '" . (int)$category['category_id'] . "', `level` = '" . (int)$level . "'");

			$this->repairCategories($category['category_id']);
		}

		$upload_directory = DIR_SYSTEM . 'upload/';

		if (!is_dir($upload_directory)) {
			mkdir($upload_directory, 0777, true);
		}

		return true;
	}

	public function updateConfig(): bool {
		set_time_limit(60);

		$root_config = DIR_NIVOCART . 'config.php';

		if (!is_file($root_config)) {
			return true;
		}

		$candidates = array_filter([
			DIR_NIVOCART . 'config.php',
			DIR_NIVOCART . 'admin/config.php',
		], 'is_file');

		foreach ($candidates as $file) {
			if (!is_writable($file)) {
				exit('ATTENTION: ' . $file . ' is read-only. Please adjust CHMOD and try again.');
			}
		}

		$patches = [
			'HTTP_IMAGE'  => ['search' => 'HTTP_SERVER', 'build' => fn() => "define('HTTP_IMAGE', '" . str_replace('\\', '/', str_replace('/install', '', HTTP_SERVER)) . "image/');"],
			'HTTPS_IMAGE' => ['search' => 'HTTPS_SERVER', 'build' => fn() => "define('HTTPS_IMAGE', '" . str_replace('\\', '/', str_replace('/install', '', HTTP_SERVER)) . "image/');"],
			'DIR_UPLOAD'  => ['search' => 'DIR_DOWNLOAD', 'build' => fn() => "define('DIR_UPLOAD', '" . str_replace('\\', '/', DIR_SYSTEM) . "upload/');"],
		];

		foreach ($candidates as $file) {
			$lines = file($file);

			foreach ($patches as $constant => $patch) {
				$already_defined = array_filter($lines, fn($l) => str_contains($l, $constant));

				if ($already_defined) {
					continue;
				}

				$output = '';

				foreach ($lines as $line) {
					if (str_contains($line, $patch['search'])) {
						$output .= $patch['build']() . "\n";
					}

					$output .= $line;
				}

				file_put_contents($file, $output);

				$lines = file($file); // re-read for next patch
			}

			// Remove PHP closing tag
			$lines = file($file);

			$has_close_tag = array_filter($lines, fn($l) => str_contains($l, '?>'));

			if ($has_close_tag) {
				$output = implode('', array_map(fn($l) => str_replace('?>', '', $l), $lines));
				file_put_contents($file, $output);
			}
		}

		clearstatcache();
		flush();

		return true;
	}

	public function updateLayouts(): bool {
		$store_query = $this->db->query("SELECT store_id FROM `" . DB_PREFIX . "store`");

		$stores = array_column($store_query->rows, 'store_id');

		$layout_groups = [
			'News'    => ['information/news', 'information/news_list'],
			'Special' => ['product/special'],
		];

		foreach ($layout_groups as $layout_name => $routes) {
			$exists = $this->db->query("SELECT layout_id FROM `" . DB_PREFIX . "layout` WHERE `name` = '" . $this->db->escape($layout_name) . "' LIMIT 1");

			if ($exists->num_rows === 0) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "layout` SET `name` = '" . $this->db->escape($layout_name) . "'");
			}

			foreach ($stores as $store_id) {
				foreach ($routes as $route) {
					$exists = $this->db->query("SELECT layout_id FROM `" . DB_PREFIX . "layout_route` WHERE store_id = '" . (int)$store_id . "' AND `route` = '" . $this->db->escape($route) . "' LIMIT 1");

					if ($exists->num_rows === 0) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "layout_route` SET layout_id = (SELECT DISTINCT layout_id FROM `" . DB_PREFIX . "layout` WHERE `name` = '" . $this->db->escape($layout_name) . "'), store_id = '" . (int)$store_id . "', `route` = '" . $this->db->escape($route) . "'");
					}
				}
			}
		}

		return true;
	}

	public function updateFields(): void {
		$country_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "country' AND COLUMN_NAME = 'name'");

		if ($country_query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "country` DROP `name`");
		}

		$manufacturer_query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "manufacturer' AND COLUMN_NAME = 'name'");

		if ($manufacturer_query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` DROP `name`");
		}
	}

	// -----------------------------------------------------------------------
	// Private helper: builds a column definition string from a field array.
	// Pass $with_autoincrement = true when re-adding AUTO_INCREMENT after
	// primary keys have been restored.
	// -----------------------------------------------------------------------
	private function buildFieldDefinition(array $field, bool $with_autoincrement = false): string {
		$sql = mb_strtoupper($field['type'], 'UTF-8');

		if ($field['size']) {
			$sql .= "({$field['size']})";
		}

		if ($field['collation']) {
			$sql .= " {$field['collation']}";
		}

		if ($field['unsigned']) {
			$sql .= " {$field['unsigned']}";
		}

		if ($field['notnull']) {
			$sql .= " {$field['notnull']}";
		}

		if ($field['default'] !== '') {
			$sql .= " DEFAULT '{$field['default']}'";
		}

		if ($with_autoincrement && $field['autoincrement']) {
			$sql .= ' AUTO_INCREMENT';
		}

		return $sql;
	}
}
