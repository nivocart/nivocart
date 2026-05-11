<?php
/**
 * ModelToolExportImportExp
 *
 * Export side of the Export/Import tool.
 * Handles all spreadsheet generation and download functionality.
 *
 * @package NivoCart
 */
require_once DIR_APPLICATION . 'model/tool/export_import_base.php';

/**
 * Class ModelToolExportImportExp
 */
class ModelToolExportImportExp extends ModelToolExportImportBase {
	//------------------------------------------
	// Customers Export
	//------------------------------------------
	protected function getCustomers($offset = null, $rows = null, $min_id = null, $max_id = null): array {
		$language_id = $this->getDefaultLanguageId();

		$sql = "SELECT c.*, cgd.name AS `customer_group`";
		$sql .= " FROM `" . DB_PREFIX . "customer` c";
		$sql .= " INNER JOIN `" . DB_PREFIX . "customer_group_description` cgd ON (cgd.customer_group_id = c.customer_group_id)";
		$sql .= " WHERE cgd.language_id = '" . (int)$language_id . "'";

		if (isset($min_id) && isset($max_id)) {
			$sql .= " AND c.customer_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";
		}

		$sql .= " GROUP BY c.customer_id";
		$sql .= " ORDER BY c.customer_id";

		if (isset($offset) && isset($rows)) {
			$sql .= " ASC LIMIT '" . (int)$offset . "','" . (int)$rows . "'";
		} else {
			$sql .= " ASC";
		}

		return $this->db->query($sql)->rows;
	}

	protected function populateCustomersWorksheet($worksheet, $box_format, $text_format, $date_format, $datetime_format, $offset = null, $rows = null, $min_id = null, $max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('customer_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('customer_group') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('store_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('firstname') + 4, 20) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('lastname') + 4, 20) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('email') + 4, 25) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('telephone') + 4, 16) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('gender'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_of_birth'), 12) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('password'), 24) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('salt'), 16) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('cart'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('wishlist'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('newsletter') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('address_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('ip'), 12) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('status') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('approved') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('token'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_added'), 19) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'customer_id';
		$styles[$j] = &$text_format;
		$data[$j++] = 'customer_group';
		$data[$j++] = 'store_id';
		$styles[$j] = &$text_format;
		$data[$j++] = 'firstname';
		$styles[$j] = &$text_format;
		$data[$j++] = 'lastname';
		$styles[$j] = &$text_format;
		$data[$j++] = 'email';
		$styles[$j] = &$text_format;
		$data[$j++] = 'telephone';
		$styles[$j] = &$text_format;
		$data[$j++] = 'gender';
		$styles[$j] = &$date_format;
		$data[$j++] = 'date_of_birth';
		$styles[$j] = &$text_format;
		$data[$j++] = 'password';
		$styles[$j] = &$text_format;
		$data[$j++] = 'salt';
		$styles[$j] = &$text_format;
		$data[$j++] = 'cart';
		$styles[$j] = &$text_format;
		$data[$j++] = 'wishlist';
		$data[$j++] = 'newsletter';
		$data[$j++] = 'address_id';
		$styles[$j] = &$text_format;
		$data[$j++] = 'ip';
		$data[$j++] = 'status';
		$data[$j++] = 'approved';
		$styles[$j] = &$text_format;
		$data[$j++] = 'token';
		$styles[$j] = &$datetime_format;
		$data[$j++] = 'date_added';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		$customers = $this->getCustomers($offset, $rows, $min_id, $max_id);
		$length = count($customers);
		$min_id = ($length > 0) ? $customers[0]['customer_id'] : 0;
		$max_id = ($length > 0) ? $customers[$length - 1]['customer_id'] : 0;

		foreach ($customers as $row) {
			$data = [];
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$data[$j++] = $row['customer_id'];
			$data[$j++] = $row['customer_group'];
			$data[$j++] = $row['store_id'];
			$data[$j++] = $row['firstname'];
			$data[$j++] = $row['lastname'];
			$data[$j++] = $row['email'];
			$data[$j++] = $row['telephone'];
			$data[$j++] = $row['gender'];
			$data[$j++] = ($row['date_of_birth']) ? $row['date_of_birth'] : '0000-00-00';
			$data[$j++] = $row['password'];
			$data[$j++] = $row['salt'];
			$data[$j++] = $row['cart'];
			$data[$j++] = $row['wishlist'];
			$data[$j++] = ($row['newsletter'] === 0) ? 'false' : 'true';
			$data[$j++] = $row['address_id'];
			$data[$j++] = $row['ip'];
			$data[$j++] = ($row['status'] === 0) ? 'false' : 'true';
			$data[$j++] = ($row['approved'] === 0) ? 'false' : 'true';
			$data[$j++] = $row['token'];
			$data[$j++] = $row['date_added'];
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	//------------------------------------------
	// Addresses Export
	//------------------------------------------
	protected function getAddresses($min_id, $max_id): array {
		$language_id = $this->getDefaultLanguageId();

		$sql = "SELECT a.*, cd.name AS `country`, z.name AS `zone`, (cu.address_id = a.address_id) AS `default`";
		$sql .= " FROM `" . DB_PREFIX . "address` a";
		$sql .= " INNER JOIN `" . DB_PREFIX . "country` c ON (c.country_id = a.country_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "country_description` cd ON (c.country_id = cd.country_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "zone` z ON z.country_id = a.country_id AND z.zone_id = a.zone_id";
		$sql .= " INNER JOIN `" . DB_PREFIX . "customer` cu ON (cu.customer_id = a.customer_id)";
		$sql .= " WHERE cd.language_id = '" . (int)$language_id . "'";

		if (isset($min_id) && isset($max_id)) {
			$sql .= " AND a.customer_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";
		}

		$sql .= " ORDER BY a.customer_id ASC, a.address_id ASC";

		return $this->db->query($sql)->rows;
	}

	protected function populateAddressesWorksheet(&$worksheet, &$box_format, &$text_format, $min_id = null, $max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('customer_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('firstname'), 20) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('lastname'), 20) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('company'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('company_id'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tax_id'), 15) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('address_1'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('address_2'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('city'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('postcode'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('zone'), 20) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('country'), 20) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('default'), 5) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'customer_id';
		$styles[$j] = &$text_format; $data[$j++] = 'firstname';
		$styles[$j] = &$text_format; $data[$j++] = 'lastname';
		$styles[$j] = &$text_format; $data[$j++] = 'company';
		$styles[$j] = &$text_format; $data[$j++] = 'company_id';
		$styles[$j] = &$text_format; $data[$j++] = 'tax_id';
		$styles[$j] = &$text_format; $data[$j++] = 'address_1';
		$styles[$j] = &$text_format; $data[$j++] = 'address_2';
		$styles[$j] = &$text_format; $data[$j++] = 'city';
		$styles[$j] = &$text_format; $data[$j++] = 'postcode';
		$styles[$j] = &$text_format; $data[$j++] = 'zone';
		$styles[$j] = &$text_format; $data[$j++] = 'country';
		$data[$j++] = 'default';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getAddresses($min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['customer_id'];
			$data[$j++] = $row['firstname'];
			$data[$j++] = $row['lastname'];
			$data[$j++] = $row['company'];
			$data[$j++] = $row['company_id'];
			$data[$j++] = $row['tax_id'];
			$data[$j++] = $row['address_1'];
			$data[$j++] = $row['address_2'];
			$data[$j++] = $row['city'];
			$data[$j++] = $row['postcode'];
			$data[$j++] = html_entity_decode($row['zone'], ENT_QUOTES, 'UTF-8');
			$data[$j++] = $row['country'];
			$data[$j++] = ($row['default'] === 0) ? 'no' : 'yes';
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	//------------------------------------------
	// Categories Export
	//------------------------------------------
	protected function getStoreIdsForCategories(): array {
		$store_ids = [];

		$result = $this->db->query("SELECT category_id, store_id FROM `" . DB_PREFIX . "category_to_store`");

		foreach ($result->rows as $row) {
			$category_id = $row['category_id'];
			$store_id = $row['store_id'];

			if (!isset($store_ids[$category_id])) {
				$store_ids[$category_id] = [];
			}

			if (!in_array($store_id, $store_ids[$category_id])) {
				$store_ids[$category_id][] = $store_id;
			}
		}

		return $store_ids;
	}

	protected function getLayoutsForCategories(): array {
		$layouts = [];

		$sql = "SELECT cl.*, l.`name` FROM `" . DB_PREFIX . "category_to_layout` cl";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "layout` l ON (cl.layout_id = l.layout_id)";
		$sql .= " ORDER BY cl.category_id, cl.store_id ASC";

		foreach ($this->db->query($sql)->rows as $row) {
			$category_id = $row['category_id'];

			if (!isset($layouts[$category_id])) {
				$layouts[$category_id] = [];
			}

			$layouts[$category_id][$row['store_id']] = $row['name'];
		}

		return $layouts;
	}

	protected function getCategoryDescriptions($languages, $offset, $rows, $min_id, $max_id): array {
		$category_descriptions = [];

		foreach ($languages as $language) {
			$language_id = $language['language_id'];
			$language_code = strtolower($language['code']);

			$sql = "SELECT name, description, meta_description, meta_keyword";
			$sql .= " FROM `" . DB_PREFIX . "category_description`";
			$sql .= " WHERE language_id = '" . (int)$language_id . "'";

			if (isset($min_id) && isset($max_id)) {
				$sql .= " AND category_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";
			}

			$sql .= " ORDER BY category_id";

			if (isset($offset) && isset($rows)) {
				$sql .= " ASC LIMIT '" . (int)$offset . "','" . (int)$rows . "'";
			} else {
				$sql .= " ASC";
			}

			$category_descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		return $category_descriptions;
	}

	public function getCategories($languages, $offset = null, $rows = null, $min_id = null, $max_id = null): array {
		$sql = "SELECT c.*, ua.keyword AS `seo_keyword`";
		$sql .= " FROM `" . DB_PREFIX . "category` c";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "url_alias` ua ON (ua.query = CONCAT('category_id=',c.category_id))";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "category_to_store` cs ON (cs.category_id = c.category_id)";

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE c.category_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";
		}

		$sql .= " ORDER BY c.category_id";

		if (isset($offset) && isset($rows)) {
			$sql .= " ASC LIMIT '" . (int)$offset . "','" . (int)$rows . "'";
		} else {
			$sql .= " ASC";
		}

		$results = $this->db->query($sql);
		$category_descriptions = $this->getCategoryDescriptions($languages, $offset, $rows, $min_id, $max_id);

		foreach ($languages as $language) {
			$language_code = strtolower($language['code']);

			foreach ($results->rows as $key => $row) {
				$results->rows[$key]['name'][$language_code] = $category_descriptions[$language_code][$key]['name'] ?? '';
				$results->rows[$key]['description'][$language_code] = $category_descriptions[$language_code][$key]['description'] ?? '';
				$results->rows[$key]['meta_description'][$language_code] = $category_descriptions[$language_code][$key]['meta_description'] ?? '';
				$results->rows[$key]['meta_keyword'][$language_code] = $category_descriptions[$language_code][$key]['meta_keyword'] ?? '';
			}
		}

		return $results->rows;
	}

	protected function populateCategoriesWorksheet($worksheet, $languages, $box_format, $text_format, $datetime_format, $offset = null, $rows = null, $min_id = null, $max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('category_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('parent_id') + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name') + 4, 30) + 1); }
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('description') + 4, 48) + 1); }
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_description') + 4, 32) + 1); }
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_keywords') + 4, 24) + 1); }
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('sort_order') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image_name'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_added'), 19) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_modified'), 19) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('seo_keyword'), 16) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('store_ids'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('layout'), 16) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('status'), 5) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'category_id';
		$data[$j++] = 'parent_id';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'name(' . $language['code'] . ')'; }
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'description(' . $language['code'] . ')'; }
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'meta_description(' . $language['code'] . ')'; }
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'meta_keywords(' . $language['code'] . ')'; }
		$data[$j++] = 'sort_order';
		$styles[$j] = &$text_format; $data[$j++] = 'image_name';
		$styles[$j] = &$datetime_format; $data[$j++] = 'date_added';
		$styles[$j] = &$datetime_format; $data[$j++] = 'date_modified';
		$styles[$j] = &$text_format; $data[$j++] = 'seo_keyword';
		$data[$j++] = 'store_ids';
		$styles[$j] = &$text_format; $data[$j++] = 'layout';
		$data[$j++] = 'status';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		$store_ids_map = $this->getStoreIdsForCategories();
		$layouts_map = $this->getLayoutsForCategories();
		$keep_tags = $this->config->get('export_import_settings_use_export_tags');
		$categories = $this->getCategories($languages, $offset, $rows, $min_id, $max_id);

		$length = count($categories);
		$min_id = ($length > 0) ? $categories[0]['category_id'] : 0;
		$max_id = ($length > 0) ? $categories[$length - 1]['category_id'] : 0;

		foreach ($categories as $row) {
			$data = [];
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$category_id = $row['category_id'];

			$data[$j++] = $row['category_id'];
			$data[$j++] = $row['parent_id'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['name'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			foreach ($languages as $language) {
				$data[$j++] = (isset($keep_tags))
					? html_entity_decode($row['description'][$language['code']], ENT_QUOTES, 'UTF-8')
					: $this->removeEntities($row['description'][$language['code']]);
			}
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['meta_description'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['meta_keyword'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$data[$j++] = $row['sort_order'];
			$data[$j++] = $row['image'];
			$data[$j++] = $row['date_added'];
			$data[$j++] = $row['date_modified'];
			$data[$j++] = ($row['seo_keyword']) ? $row['seo_keyword'] : '';

			$store_id_list = '';
			if (isset($store_ids_map[$category_id])) {
				foreach ($store_ids_map[$category_id] as $store_id) {
					$store_id_list .= ($store_id_list === '') ? $store_id : ',' . $store_id;
				}
			}

			$data[$j++] = $store_id_list;

			$layout_list = '';
			if (isset($layouts_map[$category_id])) {
				foreach ($layouts_map[$category_id] as $store_id => $name) {
					$layout_list .= ($layout_list === '') ? $store_id . ':' . $name : ',' . $store_id . ':' . $name;
				}
			}

			$data[$j++] = $layout_list;
			$data[$j++] = ($row['status'] === 0) ? 'false' : 'true';

			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	//------------------------------------------
	// Category Filters Export
	//------------------------------------------
	protected function getFilterGroupNames($language_id): array {
		$filter_group_names = [];

		$sql = "SELECT filter_group_id, `name` FROM `" . DB_PREFIX . "filter_group_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";
		$sql .= " ORDER BY filter_group_id ASC";

		foreach ($this->db->query($sql)->rows as $row) {
			$filter_group_names[$row['filter_group_id']] = $row['name'];
		}

		return $filter_group_names;
	}

	protected function getFilterNames($language_id): array {
		$filter_names = [];

		$sql = "SELECT filter_id, `name` FROM `" . DB_PREFIX . "filter_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";
		$sql .= " ORDER BY filter_id ASC";

		foreach ($this->db->query($sql)->rows as $row) {
			$filter_names[$row['filter_id']] = $row['name'];
		}

		return $filter_names;
	}

	protected function getCategoryFilters($min_id, $max_id): array {
		$category_filters = [];

		$sql = "SELECT cf.category_id, fg.filter_group_id, cf.filter_id";
		$sql .= " FROM `" . DB_PREFIX . "category_filter` cf";
		$sql .= " INNER JOIN `" . DB_PREFIX . "filter` f ON (f.filter_id = cf.filter_id)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "filter_group` fg ON (fg.filter_group_id = f.filter_group_id)";

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE cf.category_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";
		}

		$sql .= " ORDER BY cf.category_id ASC, fg.filter_group_id ASC, cf.filter_id ASC";

		foreach ($this->db->query($sql)->rows as $row) {
			$category_filters[] = [
				'category_id'     => $row['category_id'],
				'filter_group_id' => $row['filter_group_id'],
				'filter_id'       => $row['filter_id'],
			];
		}

		return $category_filters;
	}

	protected function populateCategoryFiltersWorksheet($worksheet, $languages, $default_language_id, $box_format, $text_format, $min_id = null, $max_id = null) {
		$use_fg_id = $this->config->get('export_import_settings_use_filter_group_id');
		$use_f_id = $this->config->get('export_import_settings_use_filter_id');

		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('category_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth($use_fg_id ? strlen('filter_group_id') + 1 : max(strlen('filter_group'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth($use_f_id ? strlen('filter_id') + 1 : max(strlen('filter'), 30) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'category_id';
		if ($use_fg_id) {
			$data[$j++] = 'filter_group_id';
		} else {
			$styles[$j] = $text_format;
			$data[$j++] = 'filter_group';
		}

		if ($use_f_id) {
			$data[$j++] = 'filter_id';
		} else {
			$styles[$j] = $text_format;
			$data[$j++] = 'filter';
		}

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$filter_group_names = (!$use_fg_id) ? $this->getFilterGroupNames($default_language_id) : [];
		$filter_names = (!$use_f_id) ? $this->getFilterNames($default_language_id) : [];

		$i += 1;
		$j = 0;

		foreach ($this->getCategoryFilters($min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['category_id'];
			$data[$j++] = $use_fg_id ? $row['filter_group_id'] : html_entity_decode($filter_group_names[$row['filter_group_id']], ENT_QUOTES, 'UTF-8');
			$data[$j++] = $use_f_id ? $row['filter_id'] : html_entity_decode($filter_names[$row['filter_id']], ENT_QUOTES, 'UTF-8');
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	//------------------------------------------
	// Products Export
	//------------------------------------------
	protected function getStoreIdsForProducts(): array {
		$store_ids = [];

		foreach ($this->db->query("SELECT product_id, store_id FROM `" . DB_PREFIX . "product_to_store`")->rows as $row) {
			$product_id = $row['product_id'];

			if (!isset($store_ids[$product_id])) {
				$store_ids[$product_id] = [];
			}

			if (!in_array($row['store_id'], $store_ids[$product_id])) {
				$store_ids[$product_id][] = $row['store_id'];
			}
		}

		return $store_ids;
	}

	protected function getLayoutsForProducts(): array {
		$layouts = [];

		$sql = "SELECT pl.*, l.`name` FROM `" . DB_PREFIX . "product_to_layout` pl";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "layout` l ON (pl.layout_id = l.layout_id)";
		$sql .= " ORDER BY pl.product_id, pl.store_id";

		foreach ($this->db->query($sql)->rows as $row) {
			$product_id = $row['product_id'];

			if (!isset($layouts[$product_id])) {
				$layouts[$product_id] = [];
			}

			$layouts[$product_id][$row['store_id']] = $row['name'];
		}

		return $layouts;
	}

	protected function getVideoCodeForProducts($product_id) {
		$sql = "SELECT video_code FROM `" . DB_PREFIX . "product_youtube`";
		$sql .= " WHERE product_id = '" . (int)$product_id . "'";

		$query = $this->db->query($sql);

		return isset($query->row['video_code']) ? $query->row['video_code'] : 0;
	}

	protected function getProductDescriptions($languages, $offset = null, $rows = null, $min_id = null, $max_id = null): array {
		$product_descriptions = [];

		foreach ($languages as $language) {
			$language_id = $language['language_id'];
			$language_code = strtolower($language['code']);

			$sql = "SELECT p.product_id, pd.*";
			$sql .= " FROM `" . DB_PREFIX . "product_description` pd";
			$sql .= " INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pd.product_id)";

			if ($this->posted_categories) {
				$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = p.product_id)";
			}

			$sql .= " WHERE pd.language_id = '" . (int)$language_id . "'";

			if (isset($min_id) && isset($max_id)) {
				$sql .= " AND p.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

				if ($this->posted_categories) {
					$sql .= " AND pc.category_id IN " . $this->posted_categories;
				}
			} elseif ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}

			$sql .= " GROUP BY p.product_id";
			$sql .= " ORDER BY p.product_id";

			if (isset($offset) && isset($rows)) {
				$sql .= " ASC LIMIT '" . (int)$offset . "','" . (int)$rows . "'";
			} else {
				$sql .= " ASC";
			}

			$product_descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		return $product_descriptions;
	}

	public function getProducts(&$languages, $default_language_id, $offset = null, $rows = null, $min_id = null, $max_id = null): array {
		$sql = "SELECT p.product_id,";
		$sql .= " GROUP_CONCAT(DISTINCT CAST(pc.category_id AS CHAR(11)) SEPARATOR \",\") AS `categories`,";
		$sql .= " p.sku, p.upc, p.ean, p.jan, p.isbn, p.mpn, p.location, p.quantity, p.model,";
		$sql .= " md.name AS `manufacturer_name`,";
		$sql .= " p.image AS `image_name`, p.label AS `label_name`,";
		$sql .= " p.shipping, p.price, p.cost, p.quote, p.age_minimum, p.points,";
		$sql .= " p.date_added, p.date_modified, p.date_available, p.palette_id,";
		$sql .= " p.weight, wc.unit AS `weight_unit`,";
		$sql .= " p.length, p.width, p.height, mc.unit AS `length_unit`,";
		$sql .= " p.status, p.tax_class_id, ptlr.tax_local_rate_id, ua.keyword,";
		$sql .= " p.stock_status_id, p.sort_order, p.subtract, p.minimum, p.viewed,";
		$sql .= " GROUP_CONCAT(DISTINCT CAST(pr.related_id AS CHAR(11)) SEPARATOR \",\") AS `related`,";
		$sql .= " GROUP_CONCAT(DISTINCT CAST(pl.location_id AS CHAR(11)) SEPARATOR \",\") AS `location`";
		$sql .= " FROM `" . DB_PREFIX . "product` p";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = p.product_id)";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc2 ON (pc2.product_id = p.product_id)";
		}

		$sql .= " LEFT JOIN `" . DB_PREFIX . "url_alias` ua ON (ua.query = CONCAT('product_id=',p.product_id))";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "manufacturer_description` md ON (md.manufacturer_id = p.manufacturer_id) AND md.language_id = '" . (int)$default_language_id . "'";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "weight_class_description` wc ON (wc.weight_class_id = p.weight_class_id) AND wc.language_id = '" . (int)$default_language_id . "'";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "length_class_description` mc ON (mc.length_class_id = p.length_class_id) AND mc.language_id = '" . (int)$default_language_id . "'";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "product_tax_local_rate` ptlr ON (ptlr.product_id = p.product_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "product_related` pr ON (pr.product_id = p.product_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_location` pl ON (pl.product_id = p.product_id)";

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE p.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc2.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " WHERE pc2.category_id IN " . $this->posted_categories;
		}

		$sql .= " GROUP BY p.product_id";
		$sql .= " ORDER BY p.product_id";

		if (isset($offset) && isset($rows)) {
			$sql .= " ASC LIMIT '" . (int)$offset . "','" . (int)$rows . "'";
		} else {
			$sql .= " ASC";
		}

		$results = $this->db->query($sql);
		$product_descriptions = $this->getProductDescriptions($languages, $offset, $rows, $min_id, $max_id);

		foreach ($languages as $language) {
			$language_code = strtolower($language['code']);

			foreach ($results->rows as $key => $row) {
				$results->rows[$key]['name'][$language_code]             = $product_descriptions[$language_code][$key]['name'] ?? '';
				$results->rows[$key]['description'][$language_code]      = $product_descriptions[$language_code][$key]['description'] ?? '';
				$results->rows[$key]['meta_description'][$language_code] = $product_descriptions[$language_code][$key]['meta_description'] ?? '';
				$results->rows[$key]['meta_keyword'][$language_code]     = $product_descriptions[$language_code][$key]['meta_keyword'] ?? '';
				$results->rows[$key]['tag'][$language_code]              = $product_descriptions[$language_code][$key]['tag'] ?? '';
			}
		}

		return $results->rows;
	}

	protected function populateProductsWorksheet(&$worksheet, &$languages, $default_language_id, &$box_format, &$price_format, &$weight_format, &$text_format, &$date_format, &$datetime_format, $offset = null, $rows = null, &$min_id = null, &$max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('product_id'), 4) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name') + 4, 30) + 1); }
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('categories'), 12) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sku'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('upc'), 12) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('ean'), 14) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('jan'), 13) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('isbn'), 13) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('mpn'), 15) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('location'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('quantity'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('model'), 16) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('manufacturer_name'), 16) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image_name') + 4, 36) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('label_name') + 4, 36) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('video_code') + 4, 19) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('shipping'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('cost'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('quote'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('age_minimum'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('points'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_added'), 19) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_modified'), 19) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_available'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('palette_id'), 3) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight'), 6) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight_unit'), 3) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('length'), 8) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('width'), 8) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('height'), 8) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('length_unit'), 3) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('status'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tax_class_id'), 3) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tax_local_rate_id'), 3) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('seo_keyword'), 16) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('description') + 4, 48) + 1); }
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_description') + 4, 32) + 1); }
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_keywords') + 4, 24) + 1); }
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('stock_status_id'), 3) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('store_ids'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('layout'), 16) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('related_ids'), 16) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('location_ids'), 16) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tags') + 4, 24) + 1); }
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('subtract'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('minimum'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('viewed'), 5) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'name(' . $language['code'] . ')'; }
		$styles[$j] = &$text_format; $data[$j++] = 'categories';
		$styles[$j] = &$text_format; $data[$j++] = 'sku';
		$styles[$j] = &$text_format; $data[$j++] = 'upc';
		$styles[$j] = &$text_format; $data[$j++] = 'ean';
		$styles[$j] = &$text_format; $data[$j++] = 'jan';
		$styles[$j] = &$text_format; $data[$j++] = 'isbn';
		$styles[$j] = &$text_format; $data[$j++] = 'mpn';
		$styles[$j] = &$text_format; $data[$j++] = 'location';
		$data[$j++] = 'quantity';
		$styles[$j] = &$text_format; $data[$j++] = 'model';
		$styles[$j] = &$text_format; $data[$j++] = 'manufacturer_name';
		$styles[$j] = &$text_format; $data[$j++] = 'image_name';
		$styles[$j] = &$text_format; $data[$j++] = 'label_name';
		$styles[$j] = &$text_format; $data[$j++] = 'video_code';
		$data[$j++] = 'shipping';
		$styles[$j] = &$price_format; $data[$j++] = 'price';
		$styles[$j] = &$price_format; $data[$j++] = 'cost';
		$data[$j++] = 'quote';
		$data[$j++] = 'age_minimum';
		$data[$j++] = 'points';
		$styles[$j] = &$datetime_format; $data[$j++] = 'date_added';
		$styles[$j] = &$datetime_format; $data[$j++] = 'date_modified';
		$styles[$j] = &$date_format; $data[$j++] = 'date_available';
		$data[$j++] = 'palette_id';
		$styles[$j] = &$weight_format; $data[$j++] = 'weight';
		$styles[$j] = &$text_format; $data[$j++] = 'weight_unit';
		$data[$j++] = 'length';
		$data[$j++] = 'width';
		$data[$j++] = 'height';
		$styles[$j] = &$text_format; $data[$j++] = 'length_unit';
		$data[$j++] = 'status';
		$data[$j++] = 'tax_class_id';
		$data[$j++] = 'tax_local_rate_id';
		$styles[$j] = &$text_format; $data[$j++] = 'seo_keyword';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'description(' . $language['code'] . ')'; }
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'meta_description(' . $language['code'] . ')'; }
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'meta_keywords(' . $language['code'] . ')'; }
		$data[$j++] = 'stock_status_id';
		$data[$j++] = 'store_ids';
		$styles[$j] = &$text_format; $data[$j++] = 'layout';
		$data[$j++] = 'related_ids';
		$data[$j++] = 'location_ids';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'tags(' . $language['code'] . ')'; }
		$data[$j++] = 'sort_order';
		$data[$j++] = 'subtract';
		$data[$j++] = 'minimum';
		$data[$j++] = 'viewed';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		$store_ids_map = $this->getStoreIdsForProducts();
		$layouts_map = $this->getLayoutsForProducts();
		$keep_tags = $this->config->get('export_import_settings_use_export_tags');
		$products = $this->getProducts($languages, $default_language_id, $offset, $rows, $min_id, $max_id);

		$length = count($products);

		$min_id = ($length > 0) ? $products[0]['product_id'] : 0;
		$max_id = ($length > 0) ? $products[$length - 1]['product_id'] : 0;

		foreach ($products as $row) {
			$data = [];
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$product_id = $row['product_id'];
			$video_code = $this->getVideoCodeForProducts($product_id);

			$data[$j++] = $product_id;
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['name'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$data[$j++] = $row['categories'];
			$data[$j++] = $row['sku'];
			$data[$j++] = $row['upc'];
			$data[$j++] = $row['ean'];
			$data[$j++] = $row['jan'];
			$data[$j++] = $row['isbn'];
			$data[$j++] = $row['mpn'];
			$data[$j++] = $row['location'];
			$data[$j++] = $row['quantity'];
			$data[$j++] = $row['model'];
			$data[$j++] = ($row['manufacturer_name']) ? $row['manufacturer_name'] : '';
			$data[$j++] = $row['image_name'];
			$data[$j++] = $row['label_name'];
			$data[$j++] = ($video_code) ? $video_code : '';
			$data[$j++] = ($row['shipping'] === 0) ? 'no' : 'yes';
			$data[$j++] = $row['price'];
			$data[$j++] = $row['cost'];
			$data[$j++] = ($row['quote'] === 0) ? 'false' : 'true';
			$data[$j++] = $row['age_minimum'];
			$data[$j++] = $row['points'];
			$data[$j++] = $row['date_added'];
			$data[$j++] = $row['date_modified'];
			$data[$j++] = $row['date_available'];
			$data[$j++] = $row['palette_id'];
			$data[$j++] = $row['weight'];
			$data[$j++] = $row['weight_unit'];
			$data[$j++] = $row['length'];
			$data[$j++] = $row['width'];
			$data[$j++] = $row['height'];
			$data[$j++] = $row['length_unit'];
			$data[$j++] = ($row['status'] === 0) ? 'false' : 'true';
			$data[$j++] = $row['tax_class_id'];
			$data[$j++] = $row['tax_local_rate_id'];
			$data[$j++] = ($row['keyword']) ? $row['keyword'] : '';
			foreach ($languages as $language) {
				$data[$j++] = (isset($keep_tags))
					? html_entity_decode($row['description'][$language['code']], ENT_QUOTES, 'UTF-8')
					: $this->removeEntities($row['description'][$language['code']]);
			}
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['meta_description'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['meta_keyword'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$data[$j++] = $row['stock_status_id'];

			$store_id_list = '';
			if (isset($store_ids_map[$product_id])) {
				foreach ($store_ids_map[$product_id] as $store_id) {
					$store_id_list .= ($store_id_list === '') ? $store_id : ',' . $store_id;
				}
			}
			$data[$j++] = $store_id_list;

			$layout_list = '';
			if (isset($layouts_map[$product_id])) {
				foreach ($layouts_map[$product_id] as $store_id => $name) {
					$layout_list .= ($layout_list === '') ? $store_id . ':' . $name : ',' . $store_id . ':' . $name;
				}
			}
			$data[$j++] = $layout_list;
			$data[$j++] = $row['related'];
			$data[$j++] = $row['location'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['tag'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$data[$j++] = $row['sort_order'];
			$data[$j++] = ($row['subtract'] === 0) ? 'false' : 'true';
			$data[$j++] = $row['minimum'];
			$data[$j++] = $row['viewed'];

			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	//------------------------------------------
	// Product sub-sheets Export (shared pattern)
	//------------------------------------------
	protected function getAdditionalImages($min_id = null, $max_id = null): array {
		$sql = "SELECT p.product_id, pia.image, pia.palette_color_id, pia.sort_order";
		$sql .= " FROM `" . DB_PREFIX . "product_image` pia";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "product` p ON (p.product_id = pia.product_id)";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = pia.product_id)";
		}

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE p.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " WHERE pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY p.product_id, pia.image, pia.sort_order";

		return $this->db->query($sql)->rows;
	}

	protected function populateAdditionalImagesWorksheet($worksheet, $box_format, $text_format, $min_id = null, $max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('product_id'), 4) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image') + 4, 36) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('palette_color_id'), 4) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'), 5) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		$styles[$j] = &$text_format; $data[$j++] = 'image';
		$data[$j++] = 'palette_color_id';
		$data[$j++] = 'sort_order';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getAdditionalImages($min_id, $max_id) as $row) {
			$data = [];
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data[$j++] = $row['product_id'];
			$data[$j++] = $row['image'];
			$data[$j++] = $row['palette_color_id'];
			$data[$j++] = $row['sort_order'];
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getSpecials($language_id, $min_id = null, $max_id = null): array {
		$sql = "SELECT ps.*, cgd.name AS `name` FROM `" . DB_PREFIX . "product_special` ps";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "customer_group_description` cgd ON (cgd.customer_group_id = ps.customer_group_id)";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = ps.product_id)";
		}

		$sql .= " WHERE cgd.language_id = '" . (int)$language_id . "'";

		if (isset($min_id) && isset($max_id)) {
			$sql .= " AND ps.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " AND pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY ps.product_id, cgd.`name`, ps.priority";

		return $this->db->query($sql)->rows;
	}

	protected function populateSpecialsWorksheet($worksheet, $language_id, $box_format, $price_format, $text_format, $date_format, $min_id = null, $max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('customer_group') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('priority') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_start'), 19) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_end'), 19) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		$styles[$j] = &$text_format; $data[$j++] = 'customer_group';
		$data[$j++] = 'priority';
		$styles[$j] = &$price_format; $data[$j++] = 'price';
		$styles[$j] = &$date_format; $data[$j++] = 'date_start';
		$styles[$j] = &$date_format; $data[$j++] = 'date_end';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getSpecials($language_id, $min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['product_id'];
			$data[$j++] = $row['name'];
			$data[$j++] = $row['priority'];
			$data[$j++] = $row['price'];
			$data[$j++] = $row['date_start'];
			$data[$j++] = $row['date_end'];
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getDiscounts($language_id, $min_id = null, $max_id = null): array {
		$sql = "SELECT pd.*, cgd.name AS `name` FROM `" . DB_PREFIX . "product_discount` pd";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "customer_group_description` cgd ON (cgd.customer_group_id = pd.customer_group_id)";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = pd.product_id)";
		}

		$sql .= " WHERE cgd.language_id = '" . (int)$language_id . "'";

		if (isset($min_id) && isset($max_id)) {
			$sql .= " AND pd.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " AND pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY pd.product_id, cgd.`name`, pd.quantity";

		return $this->db->query($sql)->rows;
	}

	protected function populateDiscountsWorksheet($worksheet, $language_id, $box_format, $price_format, $text_format, $date_format, $min_id = null, $max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('customer_group') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('quantity') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('priority') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_start'), 19) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date_end'), 19) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		$styles[$j] = &$text_format; $data[$j++] = 'customer_group';
		$data[$j++] = 'quantity';
		$data[$j++] = 'priority';
		$styles[$j] = &$price_format; $data[$j++] = 'price';
		$styles[$j] = &$date_format; $data[$j++] = 'date_start';
		$styles[$j] = &$date_format; $data[$j++] = 'date_end';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getDiscounts($language_id, $min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['product_id'];
			$data[$j++] = $row['name'];
			$data[$j++] = $row['quantity'];
			$data[$j++] = $row['priority'];
			$data[$j++] = $row['price'];
			$data[$j++] = $row['date_start'];
			$data[$j++] = $row['date_end'];
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getRewards($language_id, $min_id = null, $max_id = null): array {
		$sql = "SELECT pr.*, cgd.`name` FROM `" . DB_PREFIX . "product_reward` pr";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "customer_group_description` cgd ON (cgd.customer_group_id = pr.customer_group_id)";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = pr.product_id)";
		}

		$sql .= " WHERE cgd.language_id = '" . $language_id . "'";

		if (isset($min_id) && isset($max_id)) {
			$sql .= " AND pr.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " AND pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY pr.product_id";

		return $this->db->query($sql)->rows;
	}

	protected function populateRewardsWorksheet($worksheet, $language_id, $box_format, $text_format, $min_id = null, $max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('customer_group') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('points') + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		$styles[$j] = &$text_format; $data[$j++] = 'customer_group';
		$data[$j++] = 'points';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getRewards($language_id, $min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['product_id'];
			$data[$j++] = $row['name'];
			$data[$j++] = $row['points'];
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getProductOptions($min_id, $max_id): array {
		$language_id = $this->getDefaultLanguageId();

		$po_query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_option` LIKE 'value'");
		$exist_po_value = ($po_query->num_rows > 0);

		$value_field = $exist_po_value ? "po.value AS `option_value`" : "po.option_value";

		$sql = "SELECT p.product_id, po.option_id, $value_field, po.required, od.`name` AS `option`";
		$sql .= " FROM (SELECT p1.product_id FROM `" . DB_PREFIX . "product` p1";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = p1.product_id)";
		}

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE p1.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " WHERE pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY p1.product_id ASC) AS p";
		$sql .= " INNER JOIN `" . DB_PREFIX . "product_option` po ON (po.product_id = p.product_id)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "option_description` od ON (od.option_id = po.option_id)";
		$sql .= " WHERE od.language_id = '" . (int)$language_id . "'";
		$sql .= " ORDER BY p.product_id, po.option_id";

		return $this->db->query($sql)->rows;
	}

	protected function populateProductOptionsWorksheet($worksheet, $box_format, $text_format, $min_id = null, $max_id = null) {
		$use_option_id = $this->config->get('export_import_settings_use_option_id');

		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth($use_option_id ? strlen('option_id') + 1 : max(strlen('option'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('default_option_value') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('required'), 5) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		if ($use_option_id) {
			$data[$j++] = 'option_id';
		} else {
			$styles[$j] = &$text_format; $data[$j++] = 'option';
		}
		$styles[$j] = &$text_format; $data[$j++] = 'default_option_value';
		$data[$j++] = 'required';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getProductOptions($min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['product_id'];
			$data[$j++] = $use_option_id ? $row['option_id'] : html_entity_decode($row['option'], ENT_QUOTES, 'UTF-8');
			$data[$j++] = html_entity_decode($row['option_value'], ENT_QUOTES, 'UTF-8');
			$data[$j++] = ($row['required'] === 0) ? 'false' : 'true';
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getProductOptionValues($min_id, $max_id): array {
		$language_id = $this->getDefaultLanguageId();

		$sql = "SELECT p.product_id, pov.option_id, pov.option_value_id, pov.quantity, pov.subtract,";
		$sql .= " od.name AS `option`, ovd.name AS `option_value`,";
		$sql .= " pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix";
		$sql .= " FROM (SELECT p1.product_id FROM `" . DB_PREFIX . "product` p1";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = p1.product_id)";
		}

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE p1.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " WHERE pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY p1.product_id ASC) AS p";
		$sql .= " INNER JOIN `" . DB_PREFIX . "product_option_value` pov ON (pov.product_id = p.product_id)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ovd.option_value_id = pov.option_value_id)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "option_description` od ON (od.option_id = ovd.option_id)";
		$sql .= " WHERE ovd.language_id = '" . (int)$language_id . "'";
		$sql .= " AND od.language_id = '" . (int)$language_id . "'";
		$sql .= " ORDER BY p.product_id, pov.option_id, pov.option_value_id";

		return $this->db->query($sql)->rows;
	}

	protected function populateProductOptionValuesWorksheet($worksheet, $box_format, $price_format, $weight_format, $text_format, $min_id = null, $max_id = null) {
		$use_option_id = $this->config->get('export_import_settings_use_option_id');
		$use_option_value_id = $this->config->get('export_import_settings_use_option_value_id');

		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth($use_option_id ? strlen('option_id') + 1 : max(strlen('option'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth($use_option_value_id ? strlen('option_value_id') + 1 : max(strlen('option_value'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('quantity'), 4) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('subtract'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price_prefix'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('points'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('points_prefix'), 5) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight_prefix'), 5) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		if ($use_option_id) { $data[$j++] = 'option_id'; } else { $styles[$j] = &$text_format; $data[$j++] = 'option'; }
		if ($use_option_value_id) { $data[$j++] = 'option_value_id'; } else { $styles[$j] = &$text_format; $data[$j++] = 'option_value'; }
		$data[$j++] = 'quantity';
		$styles[$j] = &$text_format; $data[$j++] = 'subtract';
		$styles[$j] = &$price_format; $data[$j++] = 'price';
		$styles[$j] = &$text_format; $data[$j++] = 'price_prefix';
		$data[$j++] = 'points';
		$styles[$j] = &$text_format; $data[$j++] = 'points_prefix';
		$styles[$j] = &$weight_format; $data[$j++] = 'weight';
		$styles[$j] = &$text_format; $data[$j++] = 'weight_prefix';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getProductOptionValues($min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['product_id'];
			$data[$j++] = $use_option_id ? $row['option_id'] : html_entity_decode($row['option'], ENT_QUOTES, 'UTF-8');
			$data[$j++] = $use_option_value_id ? $row['option_value_id'] : html_entity_decode($row['option_value'], ENT_QUOTES, 'UTF-8');
			$data[$j++] = $row['quantity'];
			$data[$j++] = ($row['subtract'] === 0) ? 'false' : 'true';
			$data[$j++] = $row['price'];
			$data[$j++] = $row['price_prefix'];
			$data[$j++] = $row['points'];
			$data[$j++] = $row['points_prefix'];
			$data[$j++] = $row['weight'];
			$data[$j++] = $row['weight_prefix'];
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getProductColors($min_id, $max_id): array {
		$sql = "SELECT pc1.product_id, pc1.product_color_id, pc1.palette_color_id";
		$sql .= " FROM `" . DB_PREFIX . "product_color` pc1";
		$sql .= " INNER JOIN `" . DB_PREFIX . "palette_color` pc2 ON (pc2.palette_color_id = pc1.palette_color_id)";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = pc1.product_id)";
		}

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE pc1.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " WHERE pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY pc1.product_id, pc1.palette_color_id";

		$product_colors = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$product_colors[] = [
				'product_id'       => $row['product_id'],
				'product_color_id' => $row['product_color_id'],
				'palette_color_id' => $row['palette_color_id'],
			];
		}

		return $product_colors;
	}

	protected function populateProductColorsWorksheet($worksheet, $box_format, $text_format, $min_id = null, $max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_color_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('palette_color_id') + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		$data[$j++] = 'product_color_id';
		$data[$j++] = 'palette_color_id';

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getProductColors($min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['product_id'];
			$data[$j++] = $row['product_color_id'];
			$data[$j++] = $row['palette_color_id'];
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getProductFields($languages, $min_id, $max_id): array {
		$sql = "SELECT pf.product_id, pf.field_id, pf.language_id, pf.text";
		$sql .= " FROM `" . DB_PREFIX . "product_field` pf";
		$sql .= " INNER JOIN `" . DB_PREFIX . "field` f ON (f.field_id = pf.field_id)";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = pf.product_id)";
		}

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE pf.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " WHERE pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY pf.product_id, pf.field_id";

		$texts = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$texts[$row['product_id']][$row['field_id']][$row['language_id']] = $row['text'];
		}

		$product_fields = [];

		foreach ($texts as $product_id => $level1) {
			foreach ($level1 as $field_id => $text) {
				$product_field = ['product_id' => $product_id, 'field_id' => $field_id, 'text' => []];

				foreach ($languages as $language) {
					$product_field['text'][$language['code']] = $text[$language['language_id']] ?? '';
				}

				$product_fields[] = $product_field;
			}
		}

		return $product_fields;
	}

	protected function populateProductFieldsWorksheet($worksheet, $languages, $default_language_id, $box_format, $text_format, $min_id = null, $max_id = null) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('field_id') + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('text') + 4, 30) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		$data[$j++] = 'field_id';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'text(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getProductFields($languages, $min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['product_id'];
			$data[$j++] = $row['field_id'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['text'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getAttributeGroupNames($language_id): array {
		$names = [];

		$sql = "SELECT attribute_group_id, `name` FROM `" . DB_PREFIX . "attribute_group_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";
		$sql .= " ORDER BY attribute_group_id ASC";

		foreach ($this->db->query($sql)->rows as $row) {
			$names[$row['attribute_group_id']] = $row['name'];
		}

		return $names;
	}

	protected function getAttributeNames($language_id): array {
		$names = [];

		$sql = "SELECT attribute_id, `name` FROM `" . DB_PREFIX . "attribute_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";
		$sql .= " ORDER BY attribute_id ASC";

		foreach ($this->db->query($sql)->rows as $row) {
			$names[$row['attribute_id']] = $row['name'];
		}

		return $names;
	}

	protected function getProductAttributes($languages, $min_id, $max_id): array {
		$sql = "SELECT pa.product_id, ag.attribute_group_id, pa.attribute_id, pa.language_id, pa.text";
		$sql .= " FROM `" . DB_PREFIX . "product_attribute` pa";
		$sql .= " INNER JOIN `" . DB_PREFIX . "attribute` a ON (a.attribute_id = pa.attribute_id)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "attribute_group` ag ON (ag.attribute_group_id = a.attribute_group_id)";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = pa.product_id)";
		}

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE pa.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " WHERE pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY pa.product_id, ag.attribute_group_id, pa.attribute_id";

		$texts = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$texts[$row['product_id']][$row['attribute_group_id']][$row['attribute_id']][$row['language_id']] = $row['text'];
		}

		$product_attributes = [];

		foreach ($texts as $product_id => $level1) {
			foreach ($level1 as $attribute_group_id => $level2) {
				foreach ($level2 as $attribute_id => $text) {
					$product_attribute = [
						'product_id'         => $product_id,
						'attribute_group_id' => $attribute_group_id,
						'attribute_id'       => $attribute_id,
						'text'               => [],
					];

					foreach ($languages as $language) {
						$product_attribute['text'][$language['code']] = $text[$language['language_id']] ?? '';
					}

					$product_attributes[] = $product_attribute;
				}
			}
		}

		return $product_attributes;
	}

	protected function populateProductAttributesWorksheet($worksheet, $languages, $default_language_id, $box_format, $text_format, $min_id = null, $max_id = null) {
		$use_ag_id = $this->config->get('export_import_settings_use_attribute_group_id');
		$use_a_id = $this->config->get('export_import_settings_use_attribute_id');

		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth($use_ag_id ? strlen('attribute_group_id') + 1 : max(strlen('attribute_group'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth($use_a_id ? strlen('attribute_id') + 1 : max(strlen('attribute'), 30) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('text') + 4, 30) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		if ($use_ag_id) { $data[$j++] = 'attribute_group_id'; } else { $styles[$j] = &$text_format; $data[$j++] = 'attribute_group'; }
		if ($use_a_id) { $data[$j++] = 'attribute_id'; } else { $styles[$j] = &$text_format; $data[$j++] = 'attribute'; }
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'text(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$attribute_group_names = (!$use_ag_id) ? $this->getAttributeGroupNames($default_language_id) : [];
		$attribute_names = (!$use_a_id) ? $this->getAttributeNames($default_language_id) : [];

		$i += 1;
		$j = 0;

		foreach ($this->getProductAttributes($languages, $min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['product_id'];
			$data[$j++] = $use_ag_id ? $row['attribute_group_id'] : html_entity_decode($attribute_group_names[$row['attribute_group_id']], ENT_QUOTES, 'UTF-8');
			$data[$j++] = $use_a_id ? $row['attribute_id'] : html_entity_decode($attribute_names[$row['attribute_id']], ENT_QUOTES, 'UTF-8');
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['text'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getProductFilters($min_id, $max_id): array {
		$sql = "SELECT pf.product_id, fg.filter_group_id, pf.filter_id";
		$sql .= " FROM `" . DB_PREFIX . "product_filter` pf";
		$sql .= " INNER JOIN `" . DB_PREFIX . "filter` f ON (f.filter_id = pf.filter_id)";
		$sql .= " INNER JOIN `" . DB_PREFIX . "filter_group` fg ON (fg.filter_group_id = f.filter_group_id)";

		if ($this->posted_categories) {
			$sql .= " LEFT JOIN `" . DB_PREFIX . "product_to_category` pc ON (pc.product_id = pf.product_id)";
		}

		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE pf.product_id BETWEEN '" . (int)$min_id . "' AND '" . (int)$max_id . "'";

			if ($this->posted_categories) {
				$sql .= " AND pc.category_id IN " . $this->posted_categories;
			}
		} elseif ($this->posted_categories) {
			$sql .= " WHERE pc.category_id IN " . $this->posted_categories;
		}

		$sql .= " ORDER BY pf.product_id ASC, fg.filter_group_id ASC, pf.filter_id ASC";

		$product_filters = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$product_filters[] = [
				'product_id'      => $row['product_id'],
				'filter_group_id' => $row['filter_group_id'],
				'filter_id'       => $row['filter_id'],
			];
		}

		return $product_filters;
	}

	protected function populateProductFiltersWorksheet($worksheet, $languages, $default_language_id, $box_format, $text_format, $min_id = null, $max_id = null) {
		$use_fg_id = $this->config->get('export_import_settings_use_filter_group_id');
		$use_f_id = $this->config->get('export_import_settings_use_filter_id');

		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id') + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth($use_fg_id ? strlen('filter_group_id') + 1 : max(strlen('filter_group'), 30) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth($use_f_id ? strlen('filter_id') + 1 : max(strlen('filter'), 30) + 1);

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'product_id';
		if ($use_fg_id) { $data[$j++] = 'filter_group_id'; } else { $styles[$j] = &$text_format; $data[$j++] = 'filter_group'; }
		if ($use_f_id) { $data[$j++] = 'filter_id'; } else { $styles[$j] = &$text_format; $data[$j++] = 'filter'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$filter_group_names = (!$use_fg_id) ? $this->getFilterGroupNames($default_language_id) : [];
		$filter_names = (!$use_f_id) ? $this->getFilterNames($default_language_id) : [];

		$i += 1;
		$j = 0;

		foreach ($this->getProductFilters($min_id, $max_id) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['product_id'];
			$data[$j++] = $use_fg_id ? $row['filter_group_id'] : html_entity_decode($filter_group_names[$row['filter_group_id']], ENT_QUOTES, 'UTF-8');
			$data[$j++] = $use_f_id ? $row['filter_id'] : html_entity_decode($filter_names[$row['filter_id']], ENT_QUOTES, 'UTF-8');
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	//------------------------------------------
	// Options / Attributes / Filters / Fields / Palettes Export
	// (shared description-merge pattern)
	//------------------------------------------
	private function mergeDescriptions($results, $descriptions, $languages, $keys): array {
		foreach ($languages as $language) {
			$language_code = $language['code'];

			foreach ($results->rows as $idx => $row) {
				foreach ($keys as $key) {
					$results->rows[$idx][$key][$language_code] = $descriptions[$language_code][$idx][$key] ?? '';
				}
			}
		}

		return $results->rows;
	}

	protected function getOptions($languages): array {
		$option_descriptions = [];

		foreach ($languages as $language) {
			$language_id = (int)$language['language_id'];
			$language_code = $language['code'];

			$sql = "SELECT o.option_id, od.*";
			$sql .= " FROM `" . DB_PREFIX . "option` o";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "option_description` od ON (od.option_id = o.option_id)";
			$sql .= " WHERE od.language_id = '" . $language_id . "'";
			$sql .= " GROUP BY o.option_id";
			$sql .= " ORDER BY o.option_id ASC";

			$option_descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option` ORDER BY option_id ASC");

		return $this->mergeDescriptions($results, $option_descriptions, $languages, ['name']);
	}

	protected function populateOptionsWorksheet($worksheet, $languages, $box_format, $text_format) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option_id'), 4) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('type'), 10) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'), 5) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name') + 4, 30) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'option_id';
		$data[$j++] = 'type';
		$data[$j++] = 'sort_order';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'name(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getOptions($languages) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['option_id'];
			$data[$j++] = $row['type'];
			$data[$j++] = $row['sort_order'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['name'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getOptionValues($languages): array {
		$option_value_descriptions = [];

		foreach ($languages as $language) {
			$language_id = (int)$language['language_id'];
			$language_code = $language['code'];

			$sql = "SELECT ov.option_id, ov.option_value_id, ovd.*";
			$sql .= " FROM `" . DB_PREFIX . "option_value` ov";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ovd.option_value_id = ov.option_value_id)";
			$sql .= " WHERE ovd.language_id = '" . $language_id . "'";
			$sql .= " GROUP BY ov.option_id, ov.option_value_id";
			$sql .= " ORDER BY ov.option_id ASC, ov.option_value_id ASC";

			$option_value_descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_value` ORDER BY option_id ASC, option_value_id ASC");

		return $this->mergeDescriptions($results, $option_value_descriptions, $languages, ['name']);
	}

	protected function populateOptionValuesWorksheet($worksheet, $languages, $box_format, $text_format) {
		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "option_value` LIKE 'image'");
		$exist_image = ($query->num_rows > 0);

		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option_value_id'), 2) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option_id'), 4) + 1);
		if ($exist_image) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image'), 12) + 1); }
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'), 5) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name') + 4, 30) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'option_value_id';
		$data[$j++] = 'option_id';
		if ($exist_image) { $styles[$j] = &$text_format; $data[$j++] = 'image'; }
		$data[$j++] = 'sort_order';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'name(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getOptionValues($languages) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['option_value_id'];
			$data[$j++] = $row['option_id'];
			if ($exist_image) { $data[$j++] = $row['image']; }
			$data[$j++] = $row['sort_order'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['name'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getAttributeGroups($languages): array {
		$descriptions = [];

		foreach ($languages as $language) {
			$language_id = (int)$language['language_id'];
			$language_code = $language['code'];

			$sql = "SELECT ag.attribute_group_id, agd.*";
			$sql .= " FROM `" . DB_PREFIX . "attribute_group` ag";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "attribute_group_description` agd ON (agd.attribute_group_id = ag.attribute_group_id)";
			$sql .= " WHERE agd.language_id = '" . $language_id . "'";
			$sql .= " GROUP BY ag.attribute_group_id";
			$sql .= " ORDER BY ag.attribute_group_id ASC";

			$descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attribute_group` ORDER BY attribute_group_id ASC");

		return $this->mergeDescriptions($results, $descriptions, $languages, ['name']);
	}

	protected function populateAttributeGroupsWorksheet($worksheet, $languages, $box_format, $text_format) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('attribute_group_id'), 4) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'), 5) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name') + 4, 30) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'attribute_group_id';
		$data[$j++] = 'sort_order';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'name(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getAttributeGroups($languages) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['attribute_group_id'];
			$data[$j++] = $row['sort_order'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['name'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getAttributes($languages): array {
		$descriptions = [];

		foreach ($languages as $language) {
			$language_id = (int)$language['language_id'];
			$language_code = $language['code'];

			$sql = "SELECT a.attribute_group_id, a.attribute_id, ad.*";
			$sql .= " FROM `" . DB_PREFIX . "attribute` a";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (ad.attribute_id = a.attribute_id)";
			$sql .= " WHERE ad.language_id = '" . $language_id . "'";
			$sql .= " GROUP BY a.attribute_group_id, a.attribute_id";
			$sql .= " ORDER BY a.attribute_group_id ASC, a.attribute_id ASC";

			$descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "attribute` ORDER BY attribute_group_id ASC, attribute_id ASC");

		return $this->mergeDescriptions($results, $descriptions, $languages, ['name']);
	}

	protected function populateAttributesWorksheet($worksheet, $languages, $box_format, $text_format) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('attribute_id'), 2) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('attribute_group_id'), 4) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'), 5) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name') + 4, 30) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'attribute_id';
		$data[$j++] = 'attribute_group_id';
		$data[$j++] = 'sort_order';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'name(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getAttributes($languages) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['attribute_id'];
			$data[$j++] = $row['attribute_group_id'];
			$data[$j++] = $row['sort_order'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['name'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getFilterGroups($languages): array {
		$descriptions = [];

		foreach ($languages as $language) {
			$language_id = (int)$language['language_id'];
			$language_code = $language['code'];

			$sql = "SELECT ag.filter_group_id, agd.*";
			$sql .= " FROM `" . DB_PREFIX . "filter_group` ag";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "filter_group_description` agd ON (agd.filter_group_id = ag.filter_group_id)";
			$sql .= " WHERE agd.language_id = '" . $language_id . "'";
			$sql .= " GROUP BY ag.filter_group_id";
			$sql .= " ORDER BY ag.filter_group_id ASC";

			$descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "filter_group` ORDER BY filter_group_id ASC");

		foreach ($languages as $language) {
			$language_code = $language['code'];

			foreach ($results->rows as $key => $row) {
				$results->rows[$key]['name'][$language_code] = $descriptions[$language_code][$key]['name'] ?? '';
			}
		}

		return $results->rows;
	}

	protected function populateFilterGroupsWorksheet($worksheet, $languages, $box_format, $text_format) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_group_id'), 4) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'), 5) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name') + 4, 30) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'filter_group_id';
		$data[$j++] = 'sort_order';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'name(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getFilterGroups($languages) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['filter_group_id'];
			$data[$j++] = $row['sort_order'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['name'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getFilters($languages): array {
		$descriptions = [];

		foreach ($languages as $language) {
			$language_id = (int)$language['language_id'];
			$language_code = $language['code'];

			$sql = "SELECT a.filter_group_id, a.filter_id, ad.*";
			$sql .= " FROM `" . DB_PREFIX . "filter` a";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "filter_description` ad ON (ad.filter_id = a.filter_id)";
			$sql .= " WHERE ad.language_id = '" . $language_id . "'";
			$sql .= " GROUP BY a.filter_group_id, a.filter_id";
			$sql .= " ORDER BY a.filter_group_id ASC, a.filter_id ASC";

			$descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "filter` ORDER BY filter_group_id ASC, filter_id ASC");

		return $this->mergeDescriptions($results, $descriptions, $languages, ['name']);
	}

	protected function populateFiltersWorksheet($worksheet, $languages, $box_format, $text_format) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_id'), 2) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_group_id'), 4) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'), 5) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name') + 4, 30) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'filter_id';
		$data[$j++] = 'filter_group_id';
		$data[$j++] = 'sort_order';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'name(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getFilters($languages) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['filter_id'];
			$data[$j++] = $row['filter_group_id'];
			$data[$j++] = $row['sort_order'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['name'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getFields($languages): array {
		$descriptions = [];

		foreach ($languages as $language) {
			$language_id = (int)$language['language_id'];
			$language_code = $language['code'];

			$sql = "SELECT f.field_id, fd.*";
			$sql .= " FROM `" . DB_PREFIX . "field` f";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "field_description` fd ON (fd.field_id = f.field_id)";
			$sql .= " WHERE fd.language_id = '" . $language_id . "'";
			$sql .= " GROUP BY f.field_id";
			$sql .= " ORDER BY f.field_id ASC";

			$descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "field` ORDER BY field_id ASC");

		return $this->mergeDescriptions($results, $descriptions, $languages, ['title', 'description']);
	}

	protected function populateFieldsWorksheet($worksheet, $languages, $box_format, $text_format) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('field_id'), 2) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sort_order'), 2) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('status'), 2) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('title') + 4, 10) + 1); }
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('description') + 4, 30) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'field_id';
		$data[$j++] = 'sort_order';
		$data[$j++] = 'status';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'title(' . $language['code'] . ')'; }
		foreach ($languages as $language) { $styles[$j] = $text_format; $data[$j++] = 'description(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getFields($languages) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['field_id'];
			$data[$j++] = $row['sort_order'];
			$data[$j++] = $row['status'];
			foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = html_entity_decode($row['title'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = html_entity_decode($row['description'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	protected function getPalettes($languages): array {
		$descriptions = [];

		foreach ($languages as $language) {
			$language_id = $language['language_id'];
			$language_code = $language['code'];

			$sql = "SELECT p.palette_id, pc.palette_color_id, pcd.*";
			$sql .= " FROM `" . DB_PREFIX . "palette` p";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "palette_color` pc ON (pc.palette_id = p.palette_id)";
			$sql .= " LEFT JOIN `" . DB_PREFIX . "palette_color_description` pcd ON (pcd.palette_id = p.palette_id) AND pcd.palette_color_id = pc.palette_color_id";
			$sql .= " WHERE pcd.language_id = '" . (int)$language_id . "'";
			$sql .= " GROUP BY pc.palette_color_id";
			$sql .= " ORDER BY p.palette_id, pc.palette_color_id ASC";

			$descriptions[$language_code] = $this->db->query($sql)->rows;
		}

		$results = $this->db->query(
			"SELECT * FROM `" . DB_PREFIX . "palette` p" .
			" LEFT JOIN `" . DB_PREFIX . "palette_color` pc ON (pc.palette_id = p.palette_id)" .
			" ORDER BY p.palette_id, pc.palette_color_id ASC"
		);

		return $this->mergeDescriptions($results, $descriptions, $languages, ['title']);
	}

	protected function populatePalettesWorksheet($worksheet, $languages, $box_format, $text_format) {
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('palette_color_id'), 2) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('palette_id'), 2) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name') + 4, 20) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('color') + 4, 12) + 1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('skin') + 4, 12) + 1);
		foreach ($languages as $language) { $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('title') + 4, 15) + 1); }

		$styles = [];
		$data = [];
		$i = 1;
		$j = 0;

		$data[$j++] = 'palette_color_id';
		$data[$j++] = 'palette_id';
		$styles[$j] = &$text_format; $data[$j++] = 'name';
		$styles[$j] = &$text_format; $data[$j++] = 'color';
		$styles[$j] = &$text_format; $data[$j++] = 'skin';
		foreach ($languages as $language) { $styles[$j] = &$text_format; $data[$j++] = 'title(' . $language['code'] . ')'; }

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet, $i, $data, $box_format);

		$i += 1;
		$j = 0;

		foreach ($this->getPalettes($languages) as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = [];
			$data[$j++] = $row['palette_color_id'];
			$data[$j++] = $row['palette_id'];
			$data[$j++] = $row['name'];
			$data[$j++] = $row['color'];
			$data[$j++] = $row['skin'];
			foreach ($languages as $language) { $data[$j++] = html_entity_decode($row['title'][$language['code']], ENT_QUOTES, 'UTF-8'); }
			$this->setCellRow($worksheet, $i, $data, $this->null_array, $styles);
			$i += 1;
			$j = 0;
		}
	}

	//------------------------------------------
	// Shared Style Builder
	//------------------------------------------
	private function buildWorkbookStyles(): array {
		return [
			'box_format' => [
				'fill' => [
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => ['argb' => 'FFF0F0F0'],
				],
			],
			'text_format' => [
				'numberformat' => ['code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT],
			],
			'price_format' => [
				'numberformat' => ['code' => '######0.00'],
				'alignment'    => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT],
			],
			'date_format' => [
				'numberformat' => ['code' => '0000-00-00'],
				'alignment'    => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
			],
			'datetime_format' => [
				'numberformat' => ['code' => '0000-00-00 00:00:00'],
				'alignment'    => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
			],
			'weight_format' => [
				'numberformat' => ['code' => '##0.00'],
				'alignment'    => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT],
			],
		];
	}

	//------------------------------------------
	// Download Entry Point
	//------------------------------------------
	public function download($export_type, $offset = null, $rows = null, $min_id = null, $max_id = null) {
		global $registry;
		$registry = $this->registry;

		set_error_handler('error_handler_for_export_import', E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_export_import');

		$cwd = getcwd();
		chdir(DIR_SYSTEM . 'vendor');
		require_once('phpexcel/PHPExcel.php');
		PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_ExportImportValueBinder());
		chdir($cwd);

		$all = !isset($offset) && !isset($rows) && !isset($min_id) && !isset($max_id);

		if ($this->config->get('export_import_settings_use_export_cache')) {
			PHPExcel_Settings::setCacheStorageMethod(
				PHPExcel_CachedObjectStorageFactory::CACHETOPHPTEMP,
				['memoryCacheSize' => '16MB']
			);
		}

		if ($this->config->get('export_import_settings_use_export_pclzip')) {
			PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
		}

		$this->posted_categories = $this->getPostedCategories();

		try {
			set_time_limit(1800);

			$workbook = new PHPExcel();
			$workbook->getDefaultStyle()->getFont()->setName('Arial');
			$workbook->getDefaultStyle()->getFont()->setSize(10);
			$workbook->getDefaultStyle()->getAlignment()->setWrapText(true);
			$workbook->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$workbook->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$workbook->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);

			$s = $this->buildWorkbookStyles();
			$box_format = $s['box_format'];
			$text_format = $s['text_format'];
			$price_format = $s['price_format'];
			$date_format = $s['date_format'];
			$datetime_format = $s['datetime_format'];
			$weight_format = $s['weight_format'];

			$wi = 0;
			$languages = $this->getLanguages();
			$default_language_id = $this->getDefaultLanguageId();

			switch ($export_type) {
				case 'm':
					$workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet();
					$ws->setTitle('Customers');
					$this->populateCustomersWorksheet($ws, $box_format, $text_format, $date_format, $datetime_format, $offset, $rows, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet();
					$workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet();
					$ws->setTitle('Addresses');
					$this->populateAddressesWorksheet($ws, $box_format, $text_format, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);
					break;

				case 'c':
					$workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet();
					$ws->setTitle('Categories');
					$this->populateCategoriesWorksheet($ws, $languages, $box_format, $text_format, $datetime_format, $offset, $rows, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					if ($this->existFilter()) {
						$workbook->createSheet();
						$workbook->setActiveSheetIndex($wi++);
						$ws = $workbook->getActiveSheet();
						$ws->setTitle('CategoryFilters');
						$this->populateCategoryFiltersWorksheet($ws, $languages, $default_language_id, $box_format, $text_format, $min_id, $max_id);
						$ws->freezePaneByColumnAndRow(1, 2);
					}
					break;

				case 'p':
					$workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet();
					$ws->setTitle('Products');
					$this->populateProductsWorksheet($ws, $languages, $default_language_id, $box_format, $price_format, $weight_format, $text_format, $date_format, $datetime_format, $offset, $rows, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('AdditionalImages');
					$this->populateAdditionalImagesWorksheet($ws, $box_format, $text_format, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('Specials');
					$this->populateSpecialsWorksheet($ws, $default_language_id, $box_format, $price_format, $text_format, $date_format, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('Discounts');
					$this->populateDiscountsWorksheet($ws, $default_language_id, $box_format, $price_format, $text_format, $date_format, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('Rewards');
					$this->populateRewardsWorksheet($ws, $default_language_id, $box_format, $text_format, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('ProductOptions');
					$this->populateProductOptionsWorksheet($ws, $box_format, $text_format, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('ProductOptionValues');
					$this->populateProductOptionValuesWorksheet($ws, $box_format, $price_format, $weight_format, $text_format, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('ProductColors');
					$this->populateProductColorsWorksheet($ws, $box_format, $text_format, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					if ($this->existField()) {
						$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
						$ws = $workbook->getActiveSheet(); $ws->setTitle('ProductFields');
						$this->populateProductFieldsWorksheet($ws, $languages, $default_language_id, $box_format, $text_format, $min_id, $max_id);
						$ws->freezePaneByColumnAndRow(1, 2);
					}

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('ProductAttributes');
					$this->populateProductAttributesWorksheet($ws, $languages, $default_language_id, $box_format, $text_format, $min_id, $max_id);
					$ws->freezePaneByColumnAndRow(1, 2);

					if ($this->existFilter()) {
						$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
						$ws = $workbook->getActiveSheet(); $ws->setTitle('ProductFilters');
						$this->populateProductFiltersWorksheet($ws, $languages, $default_language_id, $box_format, $text_format, $min_id, $max_id);
						$ws->freezePaneByColumnAndRow(1, 2);
					}
					break;

				case 'o':
					$workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('Options');
					$this->populateOptionsWorksheet($ws, $languages, $box_format, $text_format);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('OptionValues');
					$this->populateOptionValuesWorksheet($ws, $languages, $box_format, $text_format);
					$ws->freezePaneByColumnAndRow(1, 2);
					break;

				case 'a':
					$workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('AttributeGroups');
					$this->populateAttributeGroupsWorksheet($ws, $languages, $box_format, $text_format);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('Attributes');
					$this->populateAttributesWorksheet($ws, $languages, $box_format, $text_format);
					$ws->freezePaneByColumnAndRow(1, 2);
					break;

				case 'f':
					if (!$this->existFilter()) {
						throw new Exception($this->language->get('error_filter_not_supported'));
					}

					$workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('FilterGroups');
					$this->populateFilterGroupsWorksheet($ws, $languages, $box_format, $text_format);
					$ws->freezePaneByColumnAndRow(1, 2);

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('Filters');
					$this->populateFiltersWorksheet($ws, $languages, $box_format, $text_format);
					$ws->freezePaneByColumnAndRow(1, 2);
					break;

				case 'e':
					if (!$this->existField()) {
						throw new Exception($this->language->get('error_field_not_supported'));
					}

					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('Fields');
					$this->populateFieldsWorksheet($ws, $languages, $box_format, $text_format);
					$ws->freezePaneByColumnAndRow(1, 2);
					break;

				case 't':
					$workbook->createSheet(); $workbook->setActiveSheetIndex($wi++);
					$ws = $workbook->getActiveSheet(); $ws->setTitle('Palettes');
					$this->populatePalettesWorksheet($ws, $languages, $box_format, $text_format);
					$ws->freezePaneByColumnAndRow(1, 2);
					break;

				default:
					break;
			}

			$workbook->setActiveSheetIndex(0);

			$datetime = date('Y-m-d');

			$range_suffix = '';
			if (!$all) {
				if (isset($offset)) {
					$range_suffix .= "-offset-" . $offset;
				} elseif (isset($min_id)) {
					$range_suffix .= "-start-" . $min_id;
				}
				if (isset($rows)) {
					$range_suffix .= "-rows-" . $rows;
				} elseif (isset($max_id)) {
					$range_suffix .= "-end-" . $max_id;
				}
			}

			$filenames = [
				'm' => 'customers-' . $datetime . $range_suffix . '.xlsx',
				'c' => 'categories-' . $datetime . $range_suffix . '.xlsx',
				'p' => 'products-' . $datetime . $range_suffix . '.xlsx',
				'o' => 'options-' . $datetime . '.xlsx',
				'a' => 'attributes-' . $datetime . '.xlsx',
				'f' => 'filters-' . $datetime . '.xlsx',
				'e' => 'fields-' . $datetime . '.xlsx',
				't' => 'palettes-' . $datetime . '.xlsx',
			];

			$filename = $filenames[$export_type] ?? $datetime . '.xlsx';

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel2007');
			$objWriter->setPreCalculateFormulas(false);
			$objWriter->save('php://output');

			$this->clearSpreadsheetCache();

		} catch (Exception $e) {
			$this->session->data['export_import_error'] = [
				'errstr'  => $e->getMessage(),
				'errno'   => $e->getCode(),
				'errfile' => $e->getFile(),
				'errline' => $e->getLine(),
			];

			if ($this->config->get('config_error_log')) {
				$this->log->write('PHP ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
			}
		}
	}
}
