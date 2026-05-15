<?php
/**
 * ModelToolExportImportBase
 *
 * Shared base class for Export and Import models.
 * Contains all helpers, utilities and shared database queries
 * used by both ModelToolExportImportExp and ModelToolExportImportImp.
 *
 * @package NivoCart
 */
static $registry = null;

/**
 * Error Handler
 */
function error_handler_for_export_import($errno, $errstr, $errfile, $errline) {
	global $registry;

	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$errors = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$errors = "Warning";
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$errors = "Fatal Error";
			break;
		default:
			$errors = "Unknown";
			break;
	}

	$url = $registry->get('url');
	$config = $registry->get('config');
	$request = $registry->get('request');
	$session = $registry->get('session');
	$log = $registry->get('log');

	if ($config->get('config_error_log')) {
		$log->write('PHP ' . $errors . ': ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	}

	if (($errors === 'Warning') || ($errors === 'Unknown')) {
		return true;
	}

	if (($errors !== "Fatal Error") && isset($request->get['route']) && ($request->get['route'] !== 'tool/export_import/download')) {
		if ($config->get('config_error_display')) {
			echo '<b>' . $errors . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
		}
	} else {
		$session->data['export_import_error'] = ['errstr' => $errstr, 'errno' => $errno, 'errfile' => $errfile, 'errline' => $errline];

		$token = $request->get['token'];

		$link = $url->link('tool/export_import', 'token=' . $token, 'SSL');

		header('Status: ' . 302);
		header('Location: ' . str_replace(['&amp;', "\n", "\r"], ['&', '', ''], $link));
		exit(0);
	}

	return true;
}

function fatal_error_shutdown_handler_for_export_import() {
	$last_error = error_get_last();

	if ($last_error !== null && $last_error['type'] === E_ERROR) {
		error_handler_for_export_import(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
	}
}

/**
 * Class ModelToolExportImportBase
 *
 * @package NivoCart
 */
class ModelToolExportImportBase extends Model {
	protected $posted_categories = '';
	protected $null_array = [];

	/**
	 * String Utilities
	 */
	protected function clean(&$str, $allowBlanks = false) {
		$result = "";

		$n = strlen($str);

		for ($m = 0; $m < $n; $m++) {
			$ch = mb_substr($str, $m, 1, 'UTF-8');

			if (($ch === " ") && (!$allowBlanks) || ($ch === "\n") || ($ch === "\r") || ($ch === "\t") || ($ch === "\0") || ($ch === "\x0B")) {
				continue;
			}

			$result .= $ch;
		}

		return $result;
	}

	protected function startsWith($haystack, $needle) {
		if (strlen($haystack) < strlen($needle)) {
			return false;
		}

		return (mb_substr($haystack, 0, strlen($needle), 'UTF-8') === $needle);
	}

	protected function endsWith($haystack, $needle) {
		if (strlen($haystack) < strlen($needle)) {
			return false;
		}

		return (mb_substr($haystack, strlen($haystack) - strlen($needle), strlen($needle), 'UTF-8') === $needle);
	}

	protected function removeEntities($string_in): string {
		$string_out = null;

		$stripped_string = strip_tags(html_entity_decode($string_in, ENT_QUOTES, 'UTF-8'));

		for ($i = 0; $i < mb_strlen($stripped_string, 'UTF-8'); $i++) {
			$ord = ord($stripped_string[$i]);

			if (($ord > 0 && $ord < 32) || ($ord > 59 && $ord < 63) || ($ord > 126)) {
				$string_out .= '';
			} else {
				switch ($stripped_string[$i]) {
					case '&':
						$string_out .= '&amp;';
						break;
					case '"':
						$string_out .= '&quot;';
						break;
					default:
						$string_out .= $stripped_string[$i];
				}
			}
		}

		$clean_string_out = str_replace(
			['/', '&amp;nbsp;', '&amp;amp;', '&amp;ndash;', '&amp;mdash;', '&amp;trade;', '&amp;reg;', '&amp;deg;', '&amp;rsquo;', '&amp;quot;', '&amp;#39;', '&amp;acute;'],
			' ',
			$string_out
		);

		return $clean_string_out;
	}

	/**
	 * Multi-query Helper
	 */
	protected function multiquery($sql) {
		if (empty($sql)) {
			return;
		}

		foreach (explode(";\n", $sql) as $statement) {
			$statement = trim($statement);

			if ($statement) {
				$this->db->query($statement);
			}
		}
	}

	/**
	 * Language & Locale
	 */
	public function getDefaultLanguageId() {
		$sql = "SELECT DISTINCT language_id FROM `" . DB_PREFIX . "language`";
		$sql .= " WHERE code = '" . $this->config->get('config_admin_language') . "'";

		$query = $this->db->query($sql);

		if ($query->row['language_id']) {
			return $query->row['language_id'];
		}

		return 1;
	}

	protected function getLanguages() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE status = '1' ORDER BY `code` ASC");

		return $query->rows;
	}

	protected function getDefaultWeightUnit() {
		$language_id = $this->getDefaultLanguageId();

		$sql = "SELECT `unit` FROM `" . DB_PREFIX . "weight_class_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			return $query->row['unit'];
		}

		$en_sql = "SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = 'en'";
		$en_query = $this->db->query($en_sql);

		if ($en_query->num_rows > 0) {
			$language_id = $en_query->row['language_id'];

			$sql = "SELECT `unit` FROM `" . DB_PREFIX . "weight_class_description`";
			$sql .= " WHERE language_id = '" . (int)$language_id . "'";

			$query = $this->db->query($sql);

			if ($query->num_rows > 0) {
				return $query->row['unit'];
			}
		}

		return 'kg';
	}

	protected function getDefaultMeasurementUnit() {
		$language_id = $this->getDefaultLanguageId();

		$sql = "SELECT `unit` FROM `" . DB_PREFIX . "length_class_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			return $query->row['unit'];
		}

		$en_sql = "SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = 'en'";
		$en_query = $this->db->query($en_sql);

		if ($en_query->num_rows > 0) {
			$language_id = $en_query->row['language_id'];

			$sql = "SELECT `unit` FROM `" . DB_PREFIX . "length_class_description`";
			$sql .= " WHERE language_id = '" . (int)$language_id . "'";

			$query = $this->db->query($sql);

			if ($query->num_rows > 0) {
				return $query->row['unit'];
			}
		}

		return 'cm';
	}

	/**
	 * Shared Lookup Queries
	 */
	protected function getLayoutIds(): array {
		$layout_ids = [];

		$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "layout`");

		foreach ($result->rows as $row) {
			$layout_ids[$row['name']] = $row['layout_id'];
		}

		return $layout_ids;
	}

	protected function getCustomerGroupIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$customer_group_ids = [];

		$sql = "SELECT customer_group_id, `name` FROM `" . DB_PREFIX . "customer_group_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";
		$sql .= " ORDER BY customer_group_id ASC";

		$result = $this->db->query($sql);

		foreach ($result->rows as $row) {
			$customer_group_ids[$row['name']] = $row['customer_group_id'];
		}

		return $customer_group_ids;
	}

	protected function getAvailableStoreIds(): array {
		$store_ids = [0];

		$result = $this->db->query("SELECT store_id FROM `" . DB_PREFIX . "store`");

		foreach ($result->rows as $row) {
			if (!in_array((int)$row['store_id'], $store_ids)) {
				$store_ids[] = (int)$row['store_id'];
			}
		}

		return $store_ids;
	}

	protected function getAvailableCountryIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$country_ids = [];

		$sql = "SELECT c.country_id AS `country_id`, cd.name AS `country_name`";
		$sql .= " FROM `" . DB_PREFIX . "country` c";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "country_description` cd ON (c.country_id = cd.country_id)";
		$sql .= " WHERE cd.language_id = '" . (int)$language_id . "'";
		$sql .= " GROUP BY c.country_id";
		$sql .= " ORDER BY cd.name ASC";

		$query = $this->db->query($sql);

		foreach ($query->rows as $row) {
			$country_ids[$row['country_name']] = $row['country_id'];
		}

		return $country_ids;
	}

	protected function getAvailableZoneIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$zone_ids = [];

		$sql = "SELECT c.country_id, z.zone_id, cd.name AS `country_name`, z.name AS `zone_name`";
		$sql .= " FROM `" . DB_PREFIX . "country` c";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "country_description` cd ON (c.country_id = cd.country_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "zone` z ON (z.country_id = c.country_id)";
		$sql .= " WHERE cd.language_id = '" . (int)$language_id . "'";

		$query = $this->db->query($sql);

		foreach ($query->rows as $row) {
			$country = $row['country_name'];
			$zone_id = ($row['zone_id']) ? $row['zone_id'] : 0;
			$zone = ($row['zone_name']) ? $row['zone_name'] : '';
			$zone_ids[$country][$zone] = $zone_id;
		}

		return $zone_ids;
	}

	protected function getWeightClassIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$weight_class_ids = [];

		$sql = "SELECT weight_class_id, `unit` FROM `" . DB_PREFIX . "weight_class_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";

		$result = $this->db->query($sql);

		if ($result->rows) {
			foreach ($result->rows as $row) {
				if (!isset($weight_class_ids[$row['unit']])) {
					$weight_class_ids[$row['unit']] = $row['weight_class_id'];
				}
			}
		}

		return $weight_class_ids;
	}

	protected function getLengthClassIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$length_class_ids = [];

		$sql = "SELECT length_class_id, `unit` FROM `" . DB_PREFIX . "length_class_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";

		$result = $this->db->query($sql);

		if ($result->rows) {
			foreach ($result->rows as $row) {
				if (!isset($length_class_ids[$row['unit']])) {
					$length_class_ids[$row['unit']] = $row['length_class_id'];
				}
			}
		}

		return $length_class_ids;
	}

	protected function getManufacturers(): array {
		$default_language_id = $this->getDefaultLanguageId();
		$manufacturers = [];

		$sql = "SELECT m2s.manufacturer_id, m2s.store_id, md.name AS `name`, md.description";
		$sql .= " FROM `" . DB_PREFIX . "manufacturer` m";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "manufacturer_to_store` m2s ON (m2s.manufacturer_id = m.manufacturer_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "manufacturer_description` md ON (md.manufacturer_id = m.manufacturer_id)";
		$sql .= " WHERE md.language_id = '" . (int)$default_language_id . "'";

		$result = $this->db->query($sql);

		foreach ($result->rows as $row) {
			$manufacturer_id = $row['manufacturer_id'];
			$store_id = $row['store_id'];
			$manufacturer_name = $row['name'];
			$manufacturer_description = $row['description'];

			if (!isset($manufacturers[$manufacturer_name])) {
				$manufacturers[$manufacturer_name] = [];
			}

			if (!isset($manufacturers[$manufacturer_name]['manufacturer_id'])) {
				$manufacturers[$manufacturer_name]['manufacturer_id'] = $manufacturer_id;
			}

			if (!isset($manufacturers[$manufacturer_name]['description'])) {
				$manufacturers[$manufacturer_name]['description'] = $manufacturer_description;
			}

			if (!isset($manufacturers[$manufacturer_name]['store_ids'])) {
				$manufacturers[$manufacturer_name]['store_ids'] = [];
			}

			if (!in_array($store_id, $manufacturers[$manufacturer_name]['store_ids'])) {
				$manufacturers[$manufacturer_name]['store_ids'][] = $store_id;
			}
		}

		return $manufacturers;
	}

	protected function getFilterGroupIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$filter_group_ids = [];

		$sql = "SELECT filter_group_id, `name` FROM `" . DB_PREFIX . "filter_group_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";

		$query = $this->db->query($sql);

		foreach ($query->rows as $row) {
			$filter_group_ids[$row['name']] = $row['filter_group_id'];
		}

		return $filter_group_ids;
	}

	protected function getFilterIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$filter_ids = [];

		$sql = "SELECT f.filter_group_id, fd.filter_id, fd.`name`";
		$sql .= " FROM `" . DB_PREFIX . "filter_description` fd";
		$sql .= " INNER JOIN `" . DB_PREFIX . "filter` f ON (f.filter_id = fd.filter_id)";
		$sql .= " WHERE fd.language_id = '" . (int)$language_id . "'";

		$query = $this->db->query($sql);

		foreach ($query->rows as $row) {
			$filter_ids[$row['filter_group_id']][$row['name']] = $row['filter_id'];
		}

		return $filter_ids;
	}

	protected function getExistingVideoProductIds(): array {
		$product_ids = [0];

		$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_youtube`");

		foreach ($result->rows as $row) {
			if (!in_array((int)$row['product_id'], $product_ids)) {
				$product_ids[] = (int)$row['product_id'];
			}
		}

		return $product_ids;
	}

	protected function getExistingProductTaxLocalRateIds(): array {
		$product_ids = [0];

		$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_tax_local_rate`");

		foreach ($result->rows as $row) {
			if (!in_array((int)$row['product_id'], $product_ids)) {
				$product_ids[] = (int)$row['product_id'];
			}
		}

		return $product_ids;
	}

	protected function getPostedCategories(): string {
		$posted_categories = '';

		if (isset($this->request->post['categories'])) {
			if (count($this->request->post['categories']) > 0) {
				foreach ($this->request->post['categories'] as $category_id) {
					$posted_categories .= ($posted_categories === '') ? '(' : ',';
					$posted_categories .= $category_id;
				}

				$posted_categories .= ')';
			}
		}

		return $posted_categories;
	}

	/**
	 * PHPExcel Cell Helper
	 */
	protected function getCell($worksheet, $row, $col, $default_val = '') {
		$col -= 1; // We use 1-based, PHPExcel uses 0-based column index
		$row += 1; // We use 0-based, PHPExcel uses 1-based row index

		$val = ($worksheet->cellExistsByColumnAndRow($col, $row))
			? $worksheet->getCellByColumnAndRow($col, $row)->getValue()
			: $default_val;

		if ($val === null) {
			$val = $default_val;
		}

		return $val;
	}

	/**
	 * PHPExcel Write Helpers
	 */
	protected function setColumnStyles($worksheet, $styles, $min_row, $max_row) {
		if ($max_row < $min_row) {
			return;
		}

		foreach ($styles as $col => $style) {
			$from = PHPExcel_Cell::stringFromColumnIndex($col) . $min_row;
			$to = PHPExcel_Cell::stringFromColumnIndex($col) . $max_row;
			$range = $from . ':' . $to;
			$worksheet->getStyle($range)->applyFromArray($style, false);
		}
	}

	protected function setCellRow($worksheet, $row, $data, $default_style = null, $styles = null) {
		if (!empty($default_style)) {
			$worksheet->getStyle($row . ':' . $row)->applyFromArray($default_style, false);
		}

		if (!empty($styles)) {
			foreach ($styles as $col => $style) {
				$worksheet->getStyleByColumnAndRow($col, $row)->applyFromArray($style, false);
			}
		}

		$worksheet->fromArray($data, null, 'A' . $row, true);
	}

	protected function setCell($worksheet, $row, $col, $val, $style = null) {
		$worksheet->setCellValueByColumnAndRow($col, $row, $val);

		if (!empty($style)) {
			$worksheet->getStyleByColumnAndRow($col, $row)->applyFromArray($style, false);
		}
	}

	/**
	 * Cache
	 */
	protected function clearCache(): void {
		$this->cache->delete('*');
	}

	protected function clearSpreadsheetCache(): void {
		$files = glob(DIR_CACHE . 'Spreadsheet_Excel_Writer' . '*');

		if ($files) {
			foreach ($files as $file) {
				if (file_exists($file)) {
					@unlink($file);
					clearstatcache();
				}
			}
		}
	}

	/**
	 * Feature Detection
	 */
	public function existFilter(): bool {
		$tables = ['filter', 'filter_group', 'product_filter', 'category_filter'];

		foreach ($tables as $table) {
			$query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");

			if ($query->num_rows === 0) {
				return false;
			}
		}

		return true;
	}

	public function existField(): bool {
		$tables = ['field', 'field_description', 'product_field'];

		foreach ($tables as $table) {
			$query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $table . "'");

			if ($query->num_rows === 0) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Count / Min / Max Queries
	 */
	public function getMaxCustomerId(): int {
		$query = $this->db->query("SELECT MAX(customer_id) AS max_customer_id FROM `" . DB_PREFIX . "customer`");

		return isset($query->row['max_customer_id']) ? (int)$query->row['max_customer_id'] : 0;
	}

	public function getMinCustomerId(): int {
		$query = $this->db->query("SELECT MIN(customer_id) AS min_customer_id FROM `" . DB_PREFIX . "customer`");

		return isset($query->row['min_customer_id']) ? (int)$query->row['min_customer_id'] : 0;
	}

	public function getCountCustomer(): int {
		$query = $this->db->query("SELECT COUNT(customer_id) AS count_customer FROM `" . DB_PREFIX . "customer`");

		return isset($query->row['count_customer']) ? (int)$query->row['count_customer'] : 0;
	}

	public function getMaxCategoryId(): int {
		$query = $this->db->query("SELECT MAX(category_id) AS max_category_id FROM `" . DB_PREFIX . "category`");

		return isset($query->row['max_category_id']) ? (int)$query->row['max_category_id'] : 0;
	}

	public function getMinCategoryId(): int {
		$query = $this->db->query("SELECT MIN(category_id) AS min_category_id FROM `" . DB_PREFIX . "category`");

		return isset($query->row['min_category_id']) ? (int)$query->row['min_category_id'] : 0;
	}

	public function getCountCategory(): int {
		$query = $this->db->query("SELECT COUNT(category_id) AS count_category FROM `" . DB_PREFIX . "category`");

		return isset($query->row['count_category']) ? (int)$query->row['count_category'] : 0;
	}

	public function getMaxProductId(): int {
		$query = $this->db->query("SELECT MAX(product_id) AS max_product_id FROM `" . DB_PREFIX . "product`");

		return isset($query->row['max_product_id']) ? (int)$query->row['max_product_id'] : 0;
	}

	public function getMinProductId(): int {
		$query = $this->db->query("SELECT MIN(product_id) AS min_product_id FROM `" . DB_PREFIX . "product`");

		return isset($query->row['min_product_id']) ? (int)$query->row['min_product_id'] : 0;
	}

	public function getCountProduct(): int {
		$posted_categories = $this->getPostedCategories();

		$sql = "SELECT COUNT(DISTINCT p.product_id) AS count_product FROM `" . DB_PREFIX . "product` p";

		if ($posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = p.product_id)";
			$sql .= " WHERE pc.category_id IN " . $posted_categories;
		}

		$query = $this->db->query($sql);

		return isset($query->row['count_product']) ? (int)$query->row['count_product'] : 0;
	}

	/**
	 * Diagnostic Count Queries
	 */
	public function getOptionNameCounts(): array {
		$default_language_id = $this->getDefaultLanguageId();

		$sql = "SELECT `name`, COUNT(option_id) AS `count` FROM `" . DB_PREFIX . "option_description`";
		$sql .= " WHERE language_id = '" . (int)$default_language_id . "'";
		$sql .= " GROUP BY `name`";

		return $this->db->query($sql)->rows;
	}

	public function getOptionValueNameCounts(): array {
		$default_language_id = $this->getDefaultLanguageId();

		$sql = "SELECT option_id, `name`, COUNT(option_value_id) AS `count` FROM `" . DB_PREFIX . "option_value_description`";
		$sql .= " WHERE language_id = '" . (int)$default_language_id . "'";
		$sql .= " GROUP BY option_id, `name`";

		return $this->db->query($sql)->rows;
	}

	public function getAttributeGroupNameCounts(): array {
		$default_language_id = $this->getDefaultLanguageId();

		$sql = "SELECT `name`, COUNT(attribute_group_id) AS `count` FROM `" . DB_PREFIX . "attribute_group_description`";
		$sql .= " WHERE language_id = '" . (int)$default_language_id . "'";
		$sql .= " GROUP BY `name`";

		return $this->db->query($sql)->rows;
	}

	public function getAttributeNameCounts(): array {
		$default_language_id = $this->getDefaultLanguageId();

		$sql = "SELECT ag.attribute_group_id, ad.`name`, COUNT(ad.attribute_id) AS `count`";
		$sql .= " FROM `" . DB_PREFIX . "attribute_description` ad";
		$sql .= " INNER JOIN `" . DB_PREFIX . "attribute` a ON (a.attribute_id = ad.attribute_id)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "attribute_group` ag ON (ag.attribute_group_id = a.attribute_group_id)";
		$sql .= " WHERE ad.language_id = '" . (int)$default_language_id . "'";
		$sql .= " GROUP BY ag.attribute_group_id, ad.`name`";

		return $this->db->query($sql)->rows;
	}

	public function getFilterGroupNameCounts(): array {
		$default_language_id = $this->getDefaultLanguageId();

		$sql = "SELECT `name`, COUNT(filter_group_id) AS `count` FROM `" . DB_PREFIX . "filter_group_description`";
		$sql .= " WHERE language_id = '" . (int)$default_language_id . "'";
		$sql .= " GROUP BY `name`";

		return $this->db->query($sql)->rows;
	}

	public function getFilterNameCounts(): array {
		$default_language_id = $this->getDefaultLanguageId();

		$sql = "SELECT fg.filter_group_id, fd.`name`, COUNT(fd.filter_id) AS `count`";
		$sql .= " FROM `" . DB_PREFIX . "filter_description` fd";
		$sql .= " INNER JOIN `" . DB_PREFIX . "filter` f ON (f.filter_id = fd.filter_id)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "filter_group` fg ON (fg.filter_group_id = f.filter_group_id)";
		$sql .= " WHERE fd.language_id = '" . (int)$default_language_id . "'";
		$sql .= " GROUP BY fg.filter_group_id, fd.`name`";

		return $this->db->query($sql)->rows;
	}

	public function getFieldTitleCounts(): array {
		$default_language_id = $this->getDefaultLanguageId();

		$sql = "SELECT fd.title, COUNT(fd.field_id) AS `count`";
		$sql .= " FROM `" . DB_PREFIX . "field_description` fd";
		$sql .= " INNER JOIN `" . DB_PREFIX . "field` f ON (f.field_id = fd.field_id)";
		$sql .= " WHERE fd.language_id = '" . (int)$default_language_id . "'";
		$sql .= " GROUP BY fd.title";

		return $this->db->query($sql)->rows;
	}

	public function getPaletteTitleCounts(): array {
		$default_language_id = $this->getDefaultLanguageId();

		$sql = "SELECT pcd.title, COUNT(pcd.palette_id) AS `count`";
		$sql .= " FROM `" . DB_PREFIX . "palette_color_description` pcd";
		$sql .= " INNER JOIN `" . DB_PREFIX . "palette` p ON (p.palette_id = pcd.palette_id)";
		$sql .= " WHERE pcd.language_id = '" . (int)$default_language_id . "'";
		$sql .= " GROUP BY pcd.title";

		return $this->db->query($sql)->rows;
	}
}
