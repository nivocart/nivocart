<?php
/**
 * ModelToolExportImportImp
 *
 * Import side of the Export/Import tool.
 * Handles all spreadsheet reading, validation and database insertion.
 *
 * @package NivoCart
 */
require_once DIR_APPLICATION . 'model/tool/export_import_base.php';

/**
 * Class ModelToolExportImportImp
 */
class ModelToolExportImportImp extends ModelToolExportImportBase {
	/**
	 * Shared helpers for ID lookups
	 */
	protected function getAvailableCustomerIds(): array {
		$result = $this->db->query("SELECT `customer_id` FROM `" . DB_PREFIX . "customer`");
		$customer_ids = [];

		foreach ($result->rows as $row) {
			$customer_ids[$row['customer_id']] = (int)$row['customer_id'];
		}

		return $customer_ids;
	}

	protected function getAvailableCategoryIds(): array {
		$result = $this->db->query("SELECT category_id FROM `" . DB_PREFIX . "category`");
		$category_ids = [];

		foreach ($result->rows as $row) {
			$category_ids[$row['category_id']] = (int)$row['category_id'];
		}

		return $category_ids;
	}

	protected function getAvailableProductIds(&$data): array {
		$k = $data->getHighestRow();
		$available_product_ids = [];

		for ($i = 1; $i < $k; $i++) {
			$product_id = trim($this->getCell($data, $i, 1));

			if ($product_id === "") {
				continue;
			}

			$available_product_ids[$product_id] = $product_id;
		}

		return $available_product_ids;
	}

	protected function getAvailableAddressIds(): array {
		$result = $this->db->query("SELECT address_id FROM `" . DB_PREFIX . "address`");
		$address_ids = [];

		foreach ($result->rows as $row) {
			$address_ids[$row['address_id']] = (int)$row['address_id'];
		}

		return $address_ids;
	}

	protected function getCustomerAddressIds(): array {
		$address_ids = [];

		$result = $this->db->query("SELECT address_id, customer_id FROM `" . DB_PREFIX . "address`");

		foreach ($result->rows as $row) {
			$address_ids[$row['customer_id']] = $row['address_id'];
		}

		return $address_ids;
	}

	protected function getOptionIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$option_ids = [];

		$sql = "SELECT option_id, name FROM `" . DB_PREFIX . "option_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$option_ids[htmlspecialchars_decode($row['name'])] = $row['option_id'];
		}

		return $option_ids;
	}

	protected function getOptionValueIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$option_value_ids = [];

		$sql = "SELECT option_id, option_value_id, `name` FROM `" . DB_PREFIX . "option_value_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$option_value_ids[$row['option_id']][htmlspecialchars_decode($row['name'])] = $row['option_value_id'];
		}

		return $option_value_ids;
	}

	protected function getAttributeGroupIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$attribute_group_ids = [];

		$sql = "SELECT attribute_group_id, name FROM `" . DB_PREFIX . "attribute_group_description`";
		$sql .= " WHERE language_id = '" . (int)$language_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$attribute_group_ids[$row['name']] = $row['attribute_group_id'];
		}

		return $attribute_group_ids;
	}

	protected function getAttributeIds(): array {
		$language_id = $this->getDefaultLanguageId();
		$attribute_ids = [];

		$sql = "SELECT a.attribute_group_id, ad.attribute_id, ad.name";
		$sql .= " FROM `" . DB_PREFIX . "attribute_description` ad";
		$sql .= " INNER JOIN `" . DB_PREFIX . "attribute` a ON (a.attribute_id = ad.attribute_id)";
		$sql .= " WHERE ad.language_id = '" . (int)$language_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$attribute_ids[$row['attribute_group_id']][$row['name']] = $row['attribute_id'];
		}

		return $attribute_ids;
	}

	protected function getProductOptionIds(&$product_id): array {
		$product_option_ids = [];

		$sql = "SELECT product_option_id, option_id FROM `" . DB_PREFIX . "product_option`";
		$sql .= " WHERE product_id = '" . (int)$product_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$product_option_ids[$row['option_id']] = $row['product_option_id'];
		}

		return $product_option_ids;
	}

	protected function getCategoryUrlAliasIds(): array {
		$url_alias_ids = [];

		$sql = "SELECT url_alias_id, SUBSTRING(query, CHAR_LENGTH('category_id=')+1) AS category_id";
		$sql .= " FROM `" . DB_PREFIX . "url_alias` WHERE `query` LIKE 'category_id=%'";

		foreach ($this->db->query($sql)->rows as $row) {
			$url_alias_ids[$row['category_id']] = $row['url_alias_id'];
		}

		return $url_alias_ids;
	}

	protected function getProductUrlAliasIds(): array {
		$url_alias_ids = [];

		$sql = "SELECT url_alias_id, SUBSTRING(query, CHAR_LENGTH('product_id=')+1) AS product_id";
		$sql .= " FROM `" . DB_PREFIX . "url_alias` WHERE `query` LIKE 'product_id=%'";

		foreach ($this->db->query($sql)->rows as $row) {
			$url_alias_ids[$row['product_id']] = $row['url_alias_id'];
		}

		return $url_alias_ids;
	}

	protected function isInteger($value): bool {
		return ctype_digit((string)$value);
	}

	/**
	 * Customers Import
	 */
	protected function storeCustomerIntoDatabase(&$customer, &$available_customer_ids, &$customer_group_ids): void {
		$customer_id = $customer['customer_id'];
		$customer_group_id = isset($customer_group_ids[$customer['customer_group']])
			? $customer_group_ids[$customer['customer_group']]
			: $this->config->get('config_customer_group_id');
		$store_id = $customer['store_id'];
		$firstname = $this->db->escape($customer['firstname']);
		$lastname = $this->db->escape($customer['lastname']);
		$email = $this->db->escape($customer['email']);
		$telephone = $customer['telephone'];
		$gender = $customer['gender'];
		$date_of_birth = $customer['date_of_birth'];
		$password = $customer['password'];
		$salt = $customer['salt'];
		$cart = $customer['cart'];
		$wishlist = ((strtoupper($customer['wishlist']) === "TRUE") || (strtoupper($customer['wishlist']) === "YES") || (strtoupper($customer['wishlist']) === "ENABLED")) ? 1 : 0;
		$newsletter = ((strtoupper($customer['newsletter']) === "TRUE") || (strtoupper($customer['newsletter']) === "YES") || (strtoupper($customer['newsletter']) === "ENABLED")) ? 1 : 0;
		$address_id = ($customer['address_id']) ? $customer['address_id'] : '0';
		$ip = $customer['ip'];
		$status = ((strtoupper($customer['status']) === "TRUE") || (strtoupper($customer['status']) === "YES") || (strtoupper($customer['status']) === "ENABLED")) ? 1 : 0;
		$approved = ((strtoupper($customer['approved']) === "TRUE") || (strtoupper($customer['approved']) === "YES") || (strtoupper($customer['approved']) === "ENABLED")) ? 1 : 0;
		$token = $this->db->escape($customer['token']);
		$date_added = $customer['date_added'];

		$sql = "INSERT INTO `" . DB_PREFIX . "customer`";
		$sql .= " (`customer_id`,`customer_group_id`,`store_id`,`firstname`,`lastname`,`email`,`telephone`,`gender`,`date_of_birth`,`password`,`salt`,`cart`,`wishlist`,`newsletter`,`address_id`,`ip`,`status`,`approved`,`token`,`date_added`)";
		$sql .= " VALUES ($customer_id, $customer_group_id, $store_id,";
		$sql .= " '$firstname', '$lastname', '$email', '$telephone', '$gender', '$date_of_birth',";
		$sql .= " '$password', '$salt', '$cart', $wishlist, $newsletter, $address_id,";
		$sql .= " '$ip', $status, $approved, '$token', '$date_added')";

		$this->db->query($sql);
	}

	protected function deleteCustomer(int $customer_id): void {
		$sql = "DELETE FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$customer_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "customer_history` WHERE customer_id = '" . (int)$customer_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "customer_ip` WHERE customer_id = '" . (int)$customer_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "customer_online` WHERE customer_id = '" . (int)$customer_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "customer_reward` WHERE customer_id = '" . (int)$customer_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "customer_transaction` WHERE customer_id = '" . (int)$customer_id . "';\n";

		$this->multiquery($sql);

		$query = $this->db->query("SHOW TABLES LIKE \"" . DB_PREFIX . "address\"");

		if ($query->num_rows) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "address` WHERE customer_id = '" . (int)$customer_id . "'");
		}
	}

	protected function deleteCustomers(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "customer`");
	}

	protected function moreCustomerCells($i, &$j, &$worksheet, &$customer) {
		return;
	}

	protected function uploadCustomers(&$reader, $incremental, &$available_customer_ids = []): void {
		$data = $reader->getSheetByName('Customers');

		if ($data === null) {
			return;
		}

		$customer_group_ids = $this->getCustomerGroupIds();
		$available_customer_ids = [];

		if ($incremental) {
			$available_customer_ids = $this->getAvailableCustomerIds();
		} else {
			$this->deleteCustomers();
		}

		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$customer_id = trim($this->getCell($data, $i, $j++));

			if ($customer_id === "") {
				continue;
			}

			$customer_group = trim($this->getCell($data, $i, $j++));
			$store_id = $this->getCell($data, $i, $j++, '0');
			$firstname = $this->getCell($data, $i, $j++);
			$lastname = $this->getCell($data, $i, $j++);
			$email = $this->getCell($data, $i, $j++);
			$telephone = $this->getCell($data, $i, $j++);
			$telephone = (is_string($telephone) && mb_strlen($telephone, 'UTF-8') > 0) ? $telephone : '000';
			$gender = $this->getCell($data, $i, $j++);
			$gender = ($gender) ? '1' : '0';
			$date_of_birth = trim($this->getCell($data, $i, $j++));
			$date_of_birth = (is_string($date_of_birth) && mb_strlen($date_of_birth, 'UTF-8') > 0) ? $date_of_birth : '0000-00-00';
			$password = trim($this->getCell($data, $i, $j++));
			$salt = trim($this->getCell($data, $i, $j++));

			if ($password === '') {
				if ($salt === '') {
					$salt = substr(md5(uniqid(rand(), true)), 0, 9);
				}
				$password = sha1($salt . sha1($salt . sha1('nivocart')));
			} else {
				$password = md5('nivocart');
			}

			$cart = $this->getCell($data, $i, $j++);
			$wishlist = $this->getCell($data, $i, $j++);
			$newsletter = trim($this->getCell($data, $i, $j++));
			$address_id = $this->getCell($data, $i, $j++);
			$ip = $this->getCell($data, $i, $j++);
			$ip = (is_string($ip)) ? $ip : '127.0.0.1';
			$status = $this->getCell($data, $i, $j++);
			$approved = $this->getCell($data, $i, $j++);
			$token = $this->getCell($data, $i, $j++);
			$date_added = trim($this->getCell($data, $i, $j++));
			$date_added = (is_string($date_added) && mb_strlen($date_added, 'UTF-8') > 0) ? $date_added : date('Y-m-d');

			$customer = [
				'customer_id'    => $customer_id,
				'customer_group' => $customer_group,
				'store_id'       => $store_id,
				'firstname'      => $firstname,
				'lastname'       => $lastname,
				'email'          => $email,
				'telephone'      => $telephone,
				'gender'         => $gender,
				'date_of_birth'  => $date_of_birth,
				'password'       => $password,
				'salt'           => $salt,
				'cart'           => $cart,
				'wishlist'       => $wishlist,
				'newsletter'     => $newsletter,
				'address_id'     => $address_id,
				'ip'             => $ip,
				'status'         => $status,
				'approved'       => $approved,
				'token'          => $token,
				'date_added'     => $date_added,
			];

			if ($incremental && $available_customer_ids && in_array((int)$customer_id, $available_customer_ids)) {
				$this->deleteCustomer($customer_id);
			}

			$this->moreCustomerCells($i, $j, $data, $customer);
			$this->storeCustomerIntoDatabase($customer, $available_customer_ids, $customer_group_ids);
		}
	}

	/**
	 * Addresses Import
	 */
	protected function storeAddressIntoDatabase(&$address): void {
		$customer_id = $address['customer_id'];
		$default = ((strtoupper($address['default']) === "TRUE") || (strtoupper($address['default']) === "YES") || (strtoupper($address['default']) === "ENABLED")) ? 1 : 0;

		$sql = "INSERT INTO `" . DB_PREFIX . "address`";
		$sql .= " (`customer_id`,`firstname`,`lastname`,`company`,`company_id`,`tax_id`,`address_1`,`address_2`,`city`,`postcode`,`country_id`,`zone_id`)";
		$sql .= " VALUES ($customer_id,";
		$sql .= " '" . $this->db->escape($address['firstname']) . "',";
		$sql .= " '" . $this->db->escape($address['lastname']) . "',";
		$sql .= " '" . $this->db->escape($address['company']) . "',";
		$sql .= " '" . $this->db->escape($address['company_id']) . "',";
		$sql .= " '" . $this->db->escape($address['tax_id']) . "',";
		$sql .= " '" . $this->db->escape($address['address_1']) . "',";
		$sql .= " '" . $this->db->escape($address['address_2']) . "',";
		$sql .= " '" . $this->db->escape($address['city']) . "',";
		$sql .= " '" . $this->db->escape($address['postcode']) . "',";
		$sql .= " " . (int)$address['country_id'] . ", " . (int)$address['zone_id'];
		$sql .= ")";

		$this->db->query($sql);

		if ($default) {
			$address_id = $this->db->getLastId();
			$this->db->query("UPDATE `" . DB_PREFIX . "customer` SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
		}
	}

	protected function deleteAddresses(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "address`");
	}

	protected function deleteAddress(int $customer_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "address` WHERE customer_id = '" . (int)$customer_id . "'");
	}

	protected function deleteUnlistedAddresses(&$unlisted_customer_ids): void {
		foreach ($unlisted_customer_ids as $customer_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "address` WHERE customer_id = '" . (int)$customer_id . "'");
		}
	}

	protected function moreAddressCells($i, &$j, &$worksheet, &$option): void {
		return;
	}

	protected function uploadAddresses(&$reader, $incremental, &$available_customer_ids): void {
		$data = $reader->getSheetByName('Addresses');

		if ($data === null) {
			return;
		}

		$available_country_ids = $this->getAvailableCountryIds();
		$available_zone_ids = $this->getAvailableZoneIds();

		if ($incremental) {
			$unlisted_customer_ids = $available_customer_ids;
		} else {
			$this->deleteAddresses();
		}

		$previous_customer_id = 0;
		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$customer_id = trim($this->getCell($data, $i, $j++));

			if ($customer_id === '') {
				continue;
			}

			$firstname = $this->getCell($data, $i, $j++, '');
			$lastname = $this->getCell($data, $i, $j++, '');
			$company = $this->getCell($data, $i, $j++, '');
			$company_id = $this->getCell($data, $i, $j++, '');
			$tax_id = $this->getCell($data, $i, $j++, '');
			$address_1 = $this->getCell($data, $i, $j++, '');
			$address_2 = $this->getCell($data, $i, $j++, '');
			$city = $this->getCell($data, $i, $j++, '');
			$postcode = $this->getCell($data, $i, $j++, '');
			$zone = $this->getCell($data, $i, $j++, '');
			$country = $this->getCell($data, $i, $j++, '');

			if (!isset($available_country_ids[$country])) {
				$country = html_entity_decode($country, ENT_QUOTES, 'UTF-8');
			}

			$country_id = isset($available_country_ids[$country]) ? $available_country_ids[$country] : 0;

			// Zone matching with fallback encoding attempts
			if (!isset($available_zone_ids[$country][$zone])) {
				$zone = html_entity_decode($zone, ENT_QUOTES, 'UTF-8');
			}

			if (!isset($available_zone_ids[$country][$zone])) {
				$zone = htmlentities($zone, ENT_NOQUOTES, 'UTF-8');
			}

			if (!isset($available_zone_ids[$country][$zone])) {
				$zone = html_entity_decode($zone, ENT_QUOTES, 'UTF-8');
				$zone = htmlentities($zone, ENT_QUOTES, 'UTF-8');
			}

			if (!isset($available_zone_ids[$country][$zone])) {
				$zone = html_entity_decode($zone, ENT_QUOTES, 'UTF-8');
				$zone = htmlentities($zone, ENT_NOQUOTES, 'UTF-8');
				$zone = str_replace("'", "&#39;", $zone);
			}

			$zone_id = isset($available_zone_ids[$country][$zone]) ? $available_zone_ids[$country][$zone] : 0;
			$default = $this->getCell($data, $i, $j++, 'no');

			$address = [
				'customer_id' => $customer_id,
				'firstname'   => $firstname,
				'lastname'    => $lastname,
				'company'     => $company,
				'company_id'  => $company_id,
				'tax_id'      => $tax_id,
				'address_1'   => $address_1,
				'address_2'   => $address_2,
				'city'        => $city,
				'postcode'    => $postcode,
				'country_id'  => $country_id,
				'zone_id'     => $zone_id,
				'default'     => $default,
			];

			if ($incremental && $customer_id !== $previous_customer_id) {
				$this->deleteAddress($customer_id);

				if (isset($unlisted_customer_ids[$customer_id])) {
					unset($unlisted_customer_ids[$customer_id]);
				}
			}

			$this->moreAddressCells($i, $j, $data, $address);
			$this->storeAddressIntoDatabase($address);
			$previous_customer_id = $customer_id;
		}

		if ($incremental) {
			$this->deleteUnlistedAddresses($unlisted_customer_ids);
		}
	}

	/**
	 * Categories Import
	 */
	protected function storeCategoryIntoDatabase(&$category, $languages, &$layout_ids, &$available_store_ids, &$url_alias_ids): void {
		$category_id = $category['category_id'];
		$parent_id = $category['parent_id'];
		$sort_order = $category['sort_order'];
		$image_name = $this->db->escape($category['image']);
		$date_added = $category['date_added'];
		$date_modified = $category['date_modified'];
		$seo_keyword = $category['seo_keyword'];
		$store_ids = $category['store_ids'];
		$layout = $category['layout'];
		$status = ((strtoupper($category['status']) === "TRUE") || (strtoupper($category['status']) === "YES") || (strtoupper($category['status']) === "ENABLED")) ? 1 : 0;
		$names = $category['names'];
		$descriptions = $category['descriptions'];
		$meta_descriptions = $category['meta_descriptions'];
		$meta_keywords = $category['meta_keywords'];

		$sql = "INSERT INTO `" . DB_PREFIX . "category`";
		$sql .= " (`category_id`,`image`,`parent_id`,`top`,`column`,`sort_order`,`date_added`,`date_modified`,`status`)";
		$sql .= " VALUES ($category_id, '$image_name', $parent_id, 0, 0, $sort_order,";
		$sql .= " '$date_added', '$date_modified', $status)";

		$this->db->query($sql);

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$name = isset($names[$language_code]) ? $this->db->escape($names[$language_code]) : '';
			$description = isset($descriptions[$language_code]) ? $this->db->escape($descriptions[$language_code]) : '';
			$meta_description = isset($meta_descriptions[$language_code]) ? $this->db->escape($meta_descriptions[$language_code]) : '';
			$meta_keyword = isset($meta_keywords[$language_code]) ? $this->db->escape($meta_keywords[$language_code]) : '';

			$sql = "INSERT INTO `" . DB_PREFIX . "category_description`";
			$sql .= " (`category_id`,`language_id`,`name`,`description`,`meta_description`,`meta_keyword`)";
			$sql .= " VALUES ($category_id, $language_id, '$name', '$description', '$meta_description', '$meta_keyword')";

			$this->db->query($sql);
		}

		if ($seo_keyword) {
			if (isset($url_alias_ids[$category_id])) {
				$url_alias_id = $url_alias_ids[$category_id];

				$sql = "INSERT INTO `" . DB_PREFIX . "url_alias`";
				$sql .= " (`url_alias_id`,`query`,`keyword`)";
				$sql .= " VALUES ($url_alias_id, 'category_id=$category_id', '$seo_keyword')";

				unset($url_alias_ids[$category_id]);
			} else {
				$sql = "INSERT INTO `" . DB_PREFIX . "url_alias`";
				$sql .= " (`query`,`keyword`)";
				$sql .= " VALUES ('category_id=$category_id', '$seo_keyword')";
			}

			$this->db->query($sql);
		}

		foreach ($store_ids as $store_id) {
			if (in_array((int)$store_id, $available_store_ids)) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_store` (`category_id`,`store_id`) VALUES ($category_id, $store_id)");
			}
		}

		$layouts = [];

		foreach ($layout as $layout_part) {
			$next_layout = explode(':', $layout_part);

			if ($next_layout === false || count($next_layout) === 1) {
				$next_layout = [0, $layout_part];
			}

			if ((count($next_layout) === 2) && (in_array((int)$next_layout[0], $available_store_ids)) && (is_string($next_layout[1]))) {
				$store_id = (int)$next_layout[0];
				$layout_name = $next_layout[1];

				if (isset($layout_ids[$layout_name]) && !isset($layouts[$store_id])) {
					$layouts[$store_id] = (int)$layout_ids[$layout_name];
				}
			}
		}

		foreach ($layouts as $store_id => $layout_id) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_layout` (`category_id`,`store_id`,`layout_id`) VALUES ($category_id, $store_id, $layout_id)");
		}
	}

	protected function deleteCategory(int $category_id): void {
		$sql = "DELETE FROM `" . DB_PREFIX . "category` WHERE category_id = '" . (int)$category_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "category_description` WHERE category_id = '" . (int)$category_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE category_id = '" . (int)$category_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "category_to_layout` WHERE category_id = '" . (int)$category_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` LIKE 'category_id=" . (int)$category_id . "';\n";

		$this->multiquery($sql);

		$query = $this->db->query("SHOW TABLES LIKE \"" . DB_PREFIX . "category_path\"");

		if ($query->num_rows) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_id . "'");
		}
	}

	protected function deleteCategories(&$url_alias_ids): void {
		$sql = "TRUNCATE TABLE `" . DB_PREFIX . "category`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "category_description`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "category_to_store`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "category_to_layout`;\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` LIKE 'category_id=%';\n";

		$this->multiquery($sql);

		$query = $this->db->query("SHOW TABLES LIKE \"" . DB_PREFIX . "category_path\"");

		if ($query->num_rows) {
			$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_path`");
		}

		$alias_query = $this->db->query("SELECT (MAX(url_alias_id)+1) AS next_url_alias_id FROM `" . DB_PREFIX . "url_alias`");

		$next_url_alias_id = $alias_query->row['next_url_alias_id'];

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "url_alias` AUTO_INCREMENT = " . (int)$next_url_alias_id);

		$remove = [];

		foreach ($url_alias_ids as $category_id => $url_alias_id) {
			if ($url_alias_id >= $next_url_alias_id) {
				$remove[$category_id] = $url_alias_id;
			}
		}

		foreach ($remove as $category_id => $url_alias_id) {
			unset($url_alias_ids[$category_id]);
		}
	}

	protected function moreCategoryCells($i, $j, $worksheet, &$category): void {
		return;
	}

	protected function uploadCategories($reader, $incremental, &$available_category_ids = []): void {
		$data = $reader->getSheetByName('Categories');

		if ($data === null) {
			return;
		}

		$url_alias_ids = $this->getCategoryUrlAliasIds();
		$available_category_ids = [];

		if ($incremental) {
			$available_category_ids = $this->getAvailableCategoryIds();
		} else {
			$this->deleteCategories($url_alias_ids);
		}

		$layout_ids = $this->getLayoutIds();
		$available_store_ids = $this->getAvailableStoreIds();
		$languages = $this->getLanguages();
		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$category_id = trim($this->getCell($data, $i, $j++));

			if ($category_id === "") {
				continue;
			}

			$parent_id = $this->getCell($data, $i, $j++, '0');

			$names = [];

			while ($this->startsWith($first_row[$j - 1], "name(")) {
				$language_code = substr($first_row[$j - 1], strlen("name("), strlen($first_row[$j - 1]) - strlen("name(") - 1);
				$name = $this->getCell($data, $i, $j++);
				$names[$language_code] = htmlspecialchars((string)$name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$descriptions = [];

			while ($this->startsWith($first_row[$j - 1], "description(")) {
				$language_code = substr($first_row[$j - 1], strlen("description("), strlen($first_row[$j - 1]) - strlen("description(") - 1);
				$description = $this->getCell($data, $i, $j++);
				$descriptions[$language_code] = htmlspecialchars((string)$description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$meta_descriptions = [];

			while ($this->startsWith($first_row[$j - 1], "meta_description(")) {
				$language_code = substr($first_row[$j - 1], strlen("meta_description("), strlen($first_row[$j - 1]) - strlen("meta_description(") - 1);
				$meta_description = $this->getCell($data, $i, $j++);
				$meta_descriptions[$language_code] = htmlspecialchars((string)$meta_description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$meta_keywords = [];

			while ($this->startsWith($first_row[$j - 1], "meta_keywords(")) {
				$language_code = substr($first_row[$j - 1], strlen("meta_keywords("), strlen($first_row[$j - 1]) - strlen("meta_keywords(") - 1);
				$meta_keyword = $this->getCell($data, $i, $j++);
				$meta_keywords[$language_code] = htmlspecialchars((string)$meta_keyword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$sort_order = $this->getCell($data, $i, $j++, '0');
			$image_name = $this->getCell($data, $i, $j++);
			$date_added = trim($this->getCell($data, $i, $j++));
			$date_added = ((is_string($date_added)) && (strlen($date_added) > 0)) ? $date_added : "NOW()";
			$date_modified = trim($this->getCell($data, $i, $j++));
			$date_modified = ((is_string($date_modified)) && (strlen($date_modified) > 0)) ? $date_modified : "NOW()";
			$seo_keyword = $this->getCell($data, $i, $j++);
			$store_ids = $this->getCell($data, $i, $j++);
			$layout = $this->getCell($data, $i, $j++, '');
			$status = $this->getCell($data, $i, $j++, 'true');

			$store_ids_clean = trim($this->clean($store_ids, false));
			$layout_clean = trim($this->clean($layout, false));

			$category = [
				'category_id'       => $category_id,
				'parent_id'         => $parent_id,
				'names'             => $names,
				'descriptions'      => $descriptions,
				'meta_descriptions' => $meta_descriptions,
				'meta_keywords'     => $meta_keywords,
				'sort_order'        => $sort_order,
				'image'             => $image_name,
				'date_added'        => $date_added,
				'date_modified'     => $date_modified,
				'seo_keyword'       => $seo_keyword,
				'store_ids'         => ($store_ids_clean === "") ? [] : explode(",", $store_ids_clean),
				'layout'            => ($layout_clean === "") ? [] : explode(",", $layout_clean),
				'status'            => $status,
			];

			if ($incremental && $available_category_ids && in_array((int)$category_id, $available_category_ids)) {
				$this->deleteCategory($category_id);
			}

			$this->moreCategoryCells($i, $j, $data, $category);
			$this->storeCategoryIntoDatabase($category, $languages, $layout_ids, $available_store_ids, $url_alias_ids);
		}

		$this->load->model('catalog/category');

		if (method_exists($this->model_catalog_category, 'repairCategories')) {
			$this->model_catalog_category->repairCategories(0);
		}
	}

	/**
	 * Category Filters Import
	 */
	protected function storeCategoryFilterIntoDatabase(&$category_filter, $languages): void {
		$sql = "INSERT INTO `" . DB_PREFIX . "category_filter`";
		$sql .= " (`category_id`,`filter_id`) VALUES (" . (int)$category_filter['category_id'] . ", " . (int)$category_filter['filter_id'] . ")";

		$this->db->query($sql);
	}

	protected function deleteCategoryFilters(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "category_filter`");
	}

	protected function deleteCategoryFilter(int $category_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_filter` WHERE category_id = '" . (int)$category_id . "'");
	}

	protected function deleteUnlistedCategoryFilters(array $unlisted_category_ids = []): void {
		foreach ($unlisted_category_ids as $category_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_filter` WHERE category_id = '" . (int)$category_id . "'");
		}
	}

	protected function moreCategoryFilterCells($i, $j, $worksheet, &$category_filter): void {
		return;
	}

	protected function uploadCategoryFilters($reader, $incremental, &$available_category_ids): void {
		$data = $reader->getSheetByName('CategoryFilters');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_category_ids = $available_category_ids;
		} else {
			$this->deleteCategoryFilters();
		}

		if (!$this->config->get('export_import_settings_use_filter_group_id')) {
			$filter_group_ids = $this->getFilterGroupIds();
		}

		if (!$this->config->get('export_import_settings_use_filter_id')) {
			$filter_ids = $this->getFilterIds();
		}

		$languages = $this->getLanguages();
		$previous_category_id = 0;
		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$category_id = trim($this->getCell($data, $i, $j++));

			if ($category_id === '') {
				continue;
			}

			if ($this->config->get('export_import_settings_use_filter_group_id')) {
				$filter_group_id = $this->getCell($data, $i, $j++, '');
			} else {
				$filter_group_name = $this->getCell($data, $i, $j++);
				$filter_group_id = $filter_group_ids[$filter_group_name] ?? '';
			}

			if ($filter_group_id === '') {
				continue;
			}

			if ($this->config->get('export_import_settings_use_filter_id')) {
				$filter_id = $this->getCell($data, $i, $j++, '');
			} else {
				$filter_name = $this->getCell($data, $i, $j++);
				$filter_id = $filter_ids[$filter_group_id][$filter_name] ?? '';
			}

			if ($filter_id === '') {
				continue;
			}

			$category_filter = [
				'category_id'     => $category_id,
				'filter_group_id' => $filter_group_id,
				'filter_id'       => $filter_id,
			];

			if ($incremental && $category_id !== $previous_category_id) {
				$this->deleteCategoryFilter($category_id);

				if (isset($unlisted_category_ids[$category_id])) {
					unset($unlisted_category_ids[$category_id]);
				}
			}

			$this->moreCategoryFilterCells($i, $j, $data, $category_filter);
			$this->storeCategoryFilterIntoDatabase($category_filter, $languages);
			$previous_category_id = $category_id;
		}

		if ($incremental) {
			$this->deleteUnlistedCategoryFilters($unlisted_category_ids);
		}
	}

	/**
	 * Products Import
	 */
	protected function storeManufacturerIntoDatabase(&$manufacturers, &$manufacturer_name, &$manufacturer_description, &$store_ids, &$available_store_ids): void {
		$language_ids = $this->getLanguages();

		foreach ($store_ids as $store_id) {
			if (!in_array($store_id, $available_store_ids)) {
				continue;
			}

			if (!isset($manufacturers[$manufacturer_name]['manufacturer_id'])) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer` SET `image` = '', sort_order = '0', status = '1'");

				$manufacturer_id = $this->db->getLastId();

				foreach ($language_ids as $language_id) {
					$sql = "INSERT INTO `" . DB_PREFIX . "manufacturer_description`";
					$sql .= " SET manufacturer_id = '" . (int)$manufacturer_id . "',";
					$sql .= " language_id = '" . (int)$language_id . "',";
					$sql .= " `name` = '" . $this->db->escape($manufacturer_name) . "',";
					$sql .= " description = '" . $this->db->escape($manufacturer_description) . "'";

					$this->db->query($sql);
				}

				if (!isset($manufacturers[$manufacturer_name])) {
					$manufacturers[$manufacturer_name] = [];
				}

				$manufacturers[$manufacturer_name]['manufacturer_id'] = $manufacturer_id;
			}

			if (!isset($manufacturers[$manufacturer_name]['store_ids'])) {
				$manufacturers[$manufacturer_name]['store_ids'] = [];
			}

			if (!in_array($store_id, $manufacturers[$manufacturer_name]['store_ids'])) {
				$manufacturer_id = $manufacturers[$manufacturer_name]['manufacturer_id'];

				$sql = "INSERT INTO `" . DB_PREFIX . "manufacturer_to_store`";
				$sql .= " SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'";

				$this->db->query($sql);

				$manufacturers[$manufacturer_name]['store_ids'][] = $store_id;
			}
		}
	}

	protected function storeProductIntoDatabase(&$product, $languages, &$layout_ids, &$available_store_ids, &$manufacturers, &$weight_class_ids, &$length_class_ids, &$url_alias_ids): void {
		$product_id = $product['product_id'];
		$names = $product['names'];
		$categories = $product['categories'];
		$quantity = $product['quantity'];
		$model = $this->db->escape($product['model']);
		$manufacturer_name = $this->db->escape($product['manufacturer_name']);
		$image = $this->db->escape($product['image']);
		$label = $this->db->escape($product['label']);
		$shipping = ((strtoupper($product['shipping']) === "YES") || (strtoupper($product['shipping']) === "Y") || (strtoupper($product['shipping']) === "TRUE")) ? 1 : 0;
		$price = trim($product['price']);
		$cost = trim($product['cost']);
		$quote = ((strtoupper($product['quote']) === "TRUE") || (strtoupper($product['quote']) === "YES") || (strtoupper($product['quote']) === "ENABLED")) ? 1 : 0;
		$age_minimum = $product['age_minimum'];
		$points = $product['points'];
		$date_added = $product['date_added'];
		$date_modified = $product['date_modified'];
		$date_available = $product['date_available'];
		$palette_id = $product['palette_id'];
		$weight = ($product['weight'] === "") ? 0 : $product['weight'];
		$weight_class_id = isset($weight_class_ids[$product['weight_unit']]) ? $weight_class_ids[$product['weight_unit']] : 0;
		$status = ((strtoupper($product['status']) === "TRUE") || (strtoupper($product['status']) === "YES") || (strtoupper($product['status']) === "ENABLED")) ? 1 : 0;
		$tax_class_id = $product['tax_class_id'];
		$descriptions = $product['descriptions'];
		$stock_status_id = $product['stock_status_id'];
		$meta_descriptions = $product['meta_descriptions'];
		$length = $product['length'];
		$width = $product['width'];
		$height = $product['height'];
		$keyword = $this->db->escape($product['seo_keyword']);
		$length_class_id = isset($length_class_ids[$product['measurement_unit']]) ? $length_class_ids[$product['measurement_unit']] : 0;
		$sku = $this->db->escape($product['sku']);
		$upc = $this->db->escape($product['upc']);
		$ean = $this->db->escape($product['ean']);
		$jan = $this->db->escape($product['jan']);
		$isbn = $this->db->escape($product['isbn']);
		$mpn = $this->db->escape($product['mpn']);
		$location = $this->db->escape($product['location']);
		$store_ids = $product['store_ids'];
		$layout = $product['layout'];
		$related_ids = $product['related_ids'];
		$location_ids = $product['location_ids'];
		$subtract = ((strtoupper($product['subtract']) === "TRUE") || (strtoupper($product['subtract']) === "YES") || (strtoupper($product['subtract']) === "ENABLED")) ? 1 : 0;
		$minimum = $product['minimum'];
		$meta_keywords = $product['meta_keywords'];
		$tags = $product['tags'];
		$sort_order = $product['sort_order'];
		$viewed = $product['viewed'];

		if ($manufacturer_name) {
			$manufacturer_description = $manufacturers[$manufacturer_name]['description'] ?? '';
			$this->storeManufacturerIntoDatabase($manufacturers, $manufacturer_name, $manufacturer_description, $store_ids, $available_store_ids);
			$manufacturer_id = $manufacturers[$manufacturer_name]['manufacturer_id'];
		} else {
			$manufacturer_id = 0;
		}

		$sql = "INSERT INTO `" . DB_PREFIX . "product`";
		$sql .= " (`product_id`,`quantity`,`sku`,`upc`,`ean`,`jan`,`isbn`,`mpn`,`location`,`stock_status_id`,`model`,`manufacturer_id`,`image`,`label`,`shipping`,`price`,`cost`,`quote`,`age_minimum`,`points`,`date_added`,`date_modified`,`date_available`,`palette_id`,`weight`,`weight_class_id`,`status`,`tax_class_id`,`length`,`width`,`height`,`length_class_id`,`sort_order`,`subtract`,`minimum`,`viewed`)";
		$sql .= " VALUES ($product_id, $quantity, '$sku', '$upc', '$ean', '$jan', '$isbn', '$mpn',";
		$sql .= " '$location', $stock_status_id, '$model', $manufacturer_id, '$image', '$label', $shipping, $price, $cost, '$quote', $age_minimum, $points,";
		$sql .= " '$date_added', '$date_modified', '$date_available',";
		$sql .= " $palette_id, $weight, $weight_class_id, $status,";
		$sql .= " $tax_class_id, $length, $width, $height, '$length_class_id', '$sort_order', '$subtract', '$minimum', $viewed)";

		$this->db->query($sql);

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$name = isset($names[$language_code]) ? $this->db->escape($names[$language_code]) : '';
			$description = isset($descriptions[$language_code]) ? $this->db->escape($descriptions[$language_code]) : '';
			$meta_description = isset($meta_descriptions[$language_code]) ? $this->db->escape($meta_descriptions[$language_code]) : '';
			$meta_keyword = isset($meta_keywords[$language_code]) ? $this->db->escape($meta_keywords[$language_code]) : '';
			$tag = isset($tags[$language_code]) ? $this->db->escape($tags[$language_code]) : '';

			$sql = "INSERT INTO `" . DB_PREFIX . "product_description`";
			$sql .= " (`product_id`, `language_id`, `name`, `description`, `meta_description`, `meta_keyword`, `tag`)";
			$sql .= " VALUES ($product_id, $language_id, '$name', '$description', '$meta_description', '$meta_keyword', '$tag')";

			$this->db->query($sql);

			$sql = "INSERT INTO `" . DB_PREFIX . "product_tag`";
			$sql .= " (`product_id`,`language_id`,`tag`) VALUES ($product_id, $language_id, '$tag')";

			$this->db->query($sql);
		}

		if (count($categories) > 0) {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_to_category` (`product_id`,`category_id`) VALUES";
			$first = true;

			foreach ($categories as $category_id) {
				$sql .= ($first) ? "\n" : ",\n";
				$first = false;
				$sql .= " ($product_id, $category_id)";
			}

			$this->db->query($sql);
		}

		if ($product['video_code']) {
			$product_ids = $this->getExistingVideoProductIds();

			if (!in_array((int)$product_id, $product_ids)) {
				$sql = "INSERT INTO `" . DB_PREFIX . "product_youtube` (`product_id`,`video_code`) VALUES ($product_id, '" . $product['video_code'] . "')";
			} else {
				$sql = "UPDATE `" . DB_PREFIX . "product_youtube` SET video_code = '" . $this->db->escape($product['video_code']) . "' WHERE product_id = '" . (int)$product_id . "'";
			}

			$this->db->query($sql);
		}

		if ($product['tax_local_rate_id']) {
			$product_ids = $this->getExistingProductTaxLocalRateIds();

			if (!in_array((int)$product_id, $product_ids)) {
				$sql = "INSERT INTO `" . DB_PREFIX . "product_tax_local_rate` (`product_id`,`tax_local_rate_id`) VALUES ($product_id, '" . $product['tax_local_rate_id'] . "')";
			} else {
				$sql = "UPDATE `" . DB_PREFIX . "product_tax_local_rate` SET tax_local_rate_id = '" . (int)$product['tax_local_rate_id'] . "' WHERE product_id = '" . (int)$product_id . "'";
			}

			$this->db->query($sql);
		}

		if ($keyword) {
			if (isset($url_alias_ids[$product_id])) {
				$url_alias_id = $url_alias_ids[$product_id];

				$sql = "INSERT INTO `" . DB_PREFIX . "url_alias`";
				$sql .= " (`url_alias_id`,`query`,`keyword`) VALUES ($url_alias_id, 'product_id=$product_id', '$keyword')";

				unset($url_alias_ids[$product_id]);
			} else {
				$sql = "INSERT INTO `" . DB_PREFIX . "url_alias`";
				$sql .= " (`query`,`keyword`) VALUES ('product_id=$product_id', '$keyword')";
			}

			$this->db->query($sql);
		}

		foreach ($store_ids as $store_id) {
			if (in_array((int)$store_id, $available_store_ids)) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_store` (`product_id`,`store_id`) VALUES ($product_id, $store_id)");
			}
		}

		$layouts = [];

		foreach ($layout as $layout_part) {
			$next_layout = explode(':', $layout_part);

			if ($next_layout === false || count($next_layout) === 1) {
				$next_layout = [0, $layout_part];
			}

			if ((count($next_layout) === 2) && (in_array((int)$next_layout[0], $available_store_ids)) && (is_string($next_layout[1]))) {
				$store_id = (int)$next_layout[0];
				$layout_name = $next_layout[1];

				if (isset($layout_ids[$layout_name]) && !isset($layouts[$store_id])) {
					$layouts[$store_id] = (int)$layout_ids[$layout_name];
				}
			}
		}

		foreach ($layouts as $store_id => $layout_id) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_layout` (`product_id`,`store_id`,`layout_id`) VALUES ($product_id, $store_id, $layout_id)");
		}

		if (count($related_ids) > 0) {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_related` (`product_id`,`related_id`) VALUES";
			$first = true;

			foreach ($related_ids as $related_id) {
				$sql .= ($first) ? "\n" : ",\n";
				$first = false;
				$sql .= " ($product_id, $related_id)";
			}

			$this->db->query($sql);
		}

		if (count($location_ids) > 0) {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_to_location` (`product_id`,`location_id`) VALUES";
			$first = true;

			foreach ($location_ids as $location_id) {
				$sql .= ($first) ? "\n" : ",\n";
				$first = false;
				$sql .= " ($product_id, $location_id)";
			}

			$this->db->query($sql);
		}
	}

	protected function deleteProducts(&$url_alias_ids): void {
		$sql  = "TRUNCATE TABLE `" . DB_PREFIX . "product`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "product_color`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "product_description`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "product_to_category`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "product_to_store`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "product_related`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "product_to_layout`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "product_to_location`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "product_tax_local_rate`;\n";
		$sql .= "TRUNCATE TABLE `" . DB_PREFIX . "product_tag`;\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` LIKE 'product_id=%';\n";

		$this->multiquery($sql);

		$alias_query = $this->db->query("SELECT (MAX(url_alias_id)+1) AS next_url_alias_id FROM `" . DB_PREFIX . "url_alias`");

		$next_url_alias_id = $alias_query->row['next_url_alias_id'];

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "url_alias` AUTO_INCREMENT = " . (int)$next_url_alias_id);

		$remove = [];

		foreach ($url_alias_ids as $product_id => $url_alias_id) {
			if ($url_alias_id >= $next_url_alias_id) {
				$remove[$product_id] = $url_alias_id;
			}
		}

		foreach ($remove as $product_id => $url_alias_id) {
			unset($url_alias_ids[$product_id]);
		}
	}

	protected function deleteProduct(&$product_id): void {
		$sql = "DELETE FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "product_color` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "product_description` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "product_related` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "product_to_layout` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "product_to_location` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "product_tax_local_rate` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "product_tag` WHERE product_id = '" . (int)$product_id . "';\n";
		$sql .= "DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` LIKE 'product_id=" . (int)$product_id . "';\n";

		$this->multiquery($sql);
	}

	protected function moreProductCells($i, $j, $worksheet, &$product): void {
		return;
	}

	protected function uploadProducts($reader, $incremental, &$available_product_ids = []): void {
		$data = $reader->getSheetByName('Products');

		if ($data === null) {
			return;
		}

		$view_counts = $this->getProductViewCounts();
		$url_alias_ids = $this->getProductUrlAliasIds();
		$available_product_ids = [];

		if ($incremental) {
			$available_product_ids = $this->getAvailableProductIds($data);
		} else {
			$this->deleteProducts($url_alias_ids);
		}

		$layout_ids = $this->getLayoutIds();
		$available_store_ids = $this->getAvailableStoreIds();
		$languages = $this->getLanguages();
		$default_weight_unit = $this->getDefaultWeightUnit();
		$default_measurement_unit = $this->getDefaultMeasurementUnit();
		$default_stock_status_id = $this->config->get('config_stock_status_id');
		$manufacturers = $this->getManufacturers();
		$weight_class_ids = $this->getWeightClassIds();
		$length_class_ids = $this->getLengthClassIds();
		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === "") {
				continue;
			}

			$names = [];

			while ($this->startsWith($first_row[$j - 1], "name(")) {
				$language_code = substr($first_row[$j - 1], strlen("name("), strlen($first_row[$j - 1]) - strlen("name(") - 1);
				$name = $this->getCell($data, $i, $j++);
				$names[$language_code] = htmlspecialchars((string)$name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$categories = $this->getCell($data, $i, $j++);
			$sku = $this->getCell($data, $i, $j++, '');
			$upc = $this->getCell($data, $i, $j++, '');
			$ean = $this->getCell($data, $i, $j++, '');
			$jan = $this->getCell($data, $i, $j++, '');
			$isbn = $this->getCell($data, $i, $j++, '');
			$mpn = $this->getCell($data, $i, $j++, '');
			$location = $this->getCell($data, $i, $j++, '');
			$quantity = $this->getCell($data, $i, $j++, '0');
			$model = $this->getCell($data, $i, $j++, '   ');
			$manufacturer_name = $this->getCell($data, $i, $j++);
			$image_name = $this->getCell($data, $i, $j++);
			$label_name = $this->getCell($data, $i, $j++);
			$video_code = $this->getCell($data, $i, $j++);
			$shipping = $this->getCell($data, $i, $j++, 'Yes');
			$price = $this->getCell($data, $i, $j++, '0.00');
			$cost = $this->getCell($data, $i, $j++, '0.00');
			$quote = $this->getCell($data, $i, $j++, 'false');
			$age_minimum = $this->getCell($data, $i, $j++, '');
			$points = $this->getCell($data, $i, $j++, '0');
			$date_added = $this->getCell($data, $i, $j++);
			$date_added = ((is_string($date_added)) && (strlen($date_added) > 0)) ? $date_added : "NOW()";
			$date_modified = $this->getCell($data, $i, $j++);
			$date_modified = ((is_string($date_modified)) && (strlen($date_modified) > 0)) ? $date_modified : "NOW()";
			$date_available = $this->getCell($data, $i, $j++);
			$date_available = ((is_string($date_available)) && (strlen($date_available) > 0)) ? $date_available : "NOW()";
			$palette_id = $this->getCell($data, $i, $j++, '0');
			$weight = $this->getCell($data, $i, $j++, '0');
			$weight_unit = $this->getCell($data, $i, $j++, $default_weight_unit);
			$length = $this->getCell($data, $i, $j++, '0');
			$width = $this->getCell($data, $i, $j++, '0');
			$height = $this->getCell($data, $i, $j++, '0');
			$measurement_unit = $this->getCell($data, $i, $j++, $default_measurement_unit);
			$status = $this->getCell($data, $i, $j++, 'true');
			$tax_class_id = $this->getCell($data, $i, $j++, '0');
			$tax_local_rate_id = $this->getCell($data, $i, $j++, '0');
			$keyword = $this->getCell($data, $i, $j++);

			$descriptions = [];

			while ($this->startsWith($first_row[$j - 1], "description(")) {
				$language_code = substr($first_row[$j - 1], strlen("description("), strlen($first_row[$j - 1]) - strlen("description(") - 1);
				$description = $this->getCell($data, $i, $j++);
				$descriptions[$language_code] = htmlspecialchars((string)$description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$meta_descriptions = [];

			while ($this->startsWith($first_row[$j - 1], "meta_description(")) {
				$language_code = substr($first_row[$j - 1], strlen("meta_description("), strlen($first_row[$j - 1]) - strlen("meta_description(") - 1);
				$meta_description = $this->getCell($data, $i, $j++);
				$meta_descriptions[$language_code] = htmlspecialchars((string)$meta_description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$meta_keywords = [];

			while ($this->startsWith($first_row[$j - 1], "meta_keywords(")) {
				$language_code = substr($first_row[$j - 1], strlen("meta_keywords("), strlen($first_row[$j - 1]) - strlen("meta_keywords(") - 1);
				$meta_keyword = $this->getCell($data, $i, $j++);
				$meta_keywords[$language_code] = htmlspecialchars((string)$meta_keyword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$stock_status_id = $this->getCell($data, $i, $j++, $default_stock_status_id);
			$store_ids = $this->getCell($data, $i, $j++);
			$layout = $this->getCell($data, $i, $j++);
			$related = $this->getCell($data, $i, $j++);
			$location = $this->getCell($data, $i, $j++);

			$tags = [];

			while ($this->startsWith($first_row[$j - 1], "tags(")) {
				$language_code = substr($first_row[$j - 1], strlen("tags("), strlen($first_row[$j - 1]) - strlen("tags(") - 1);
				$tag = $this->getCell($data, $i, $j++);
				$tags[$language_code] = htmlspecialchars((string)$tag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$sort_order = $this->getCell($data, $i, $j++, '0');
			$subtract = $this->getCell($data, $i, $j++, 'true');
			$minimum = $this->getCell($data, $i, $j++, '1');

			$store_ids_clean = trim($this->clean($store_ids, false));
			$categories_clean = trim($this->clean($categories, false));

			$product = [
				'product_id'        => $product_id,
				'names'             => $names,
				'categories'        => ($categories_clean === "") ? [] : explode(",", $categories_clean),
				'quantity'          => $quantity,
				'model'             => $model,
				'manufacturer_name' => $manufacturer_name,
				'image'             => $image_name,
				'label'             => $label_name,
				'video_code'        => $video_code,
				'shipping'          => $shipping,
				'price'             => $price,
				'cost'              => $cost,
				'quote'             => $quote,
				'age_minimum'       => $age_minimum,
				'points'            => $points,
				'date_added'        => $date_added,
				'date_modified'     => $date_modified,
				'date_available'    => $date_available,
				'palette_id'        => $palette_id,
				'weight'            => $weight,
				'weight_unit'       => $weight_unit,
				'status'            => $status,
				'tax_class_id'      => $tax_class_id,
				'tax_local_rate_id' => $tax_local_rate_id,
				'viewed'            => $view_counts[$product_id] ?? 0,
				'descriptions'      => $descriptions,
				'stock_status_id'   => $stock_status_id,
				'meta_descriptions' => $meta_descriptions,
				'length'            => $length,
				'width'             => $width,
				'height'            => $height,
				'seo_keyword'       => $keyword,
				'measurement_unit'  => $measurement_unit,
				'sku'               => $sku,
				'upc'               => $upc,
				'ean'               => $ean,
				'jan'               => $jan,
				'isbn'              => $isbn,
				'mpn'               => $mpn,
				'location'          => $location,
				'store_ids'         => ($store_ids_clean === "") ? [] : explode(",", $store_ids_clean),
				'related_ids'       => ($related === "") ? [] : explode(",", $related),
				'location_ids'      => ($location === "") ? [] : explode(",", $location),
				'layout'            => ($layout === "") ? [] : explode(",", $layout),
				'subtract'          => $subtract,
				'minimum'           => $minimum,
				'meta_keywords'     => $meta_keywords,
				'tags'              => $tags,
				'sort_order'        => $sort_order,
			];

			if ($incremental) {
				$this->deleteProduct($product_id);
			}

			$this->moreProductCells($i, $j, $data, $product);
			$this->storeProductIntoDatabase($product, $languages, $layout_ids, $available_store_ids, $manufacturers, $weight_class_ids, $length_class_ids, $url_alias_ids);
		}
	}

	protected function getProductViewCounts(): array {
		$view_counts = [];

		foreach ($this->db->query("SELECT product_id, viewed FROM `" . DB_PREFIX . "product`")->rows as $row) {
			$view_counts[$row['product_id']] = $row['viewed'];
		}

		return $view_counts;
	}

	/**
	 * Additional Images Import
	 */
	protected function storeAdditionalImageIntoDatabase(&$image, &$old_product_image_ids): void {
		$product_id = $image['product_id'];
		$image_name = $image['image_name'];
		$palette_color_id = $image['palette_color_id'];
		$sort_order = $image['sort_order'];

		if (isset($old_product_image_ids[$product_id][$image_name])) {
			$product_image_id = $old_product_image_ids[$product_id][$image_name];

			$sql = "INSERT INTO `" . DB_PREFIX . "product_image`";
			$sql .= " (`product_image_id`,`product_id`,`image`,`palette_color_id`,`sort_order`)";
			$sql .= " VALUES ($product_image_id, $product_id, '" . $this->db->escape($image_name) . "', $palette_color_id, $sort_order)";

			unset($old_product_image_ids[$product_id][$image_name]);
		} else {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_image`";
			$sql .= " (`product_id`,`image`,`palette_color_id`,`sort_order`)";
			$sql .= " VALUES ($product_id, '" . $this->db->escape($image_name) . "', $palette_color_id, $sort_order)";
		}

		$this->db->query($sql);
	}

	protected function deleteAdditionalImages(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_image`");
	}

	protected function deleteAdditionalImage(&$product_id): array {
		$old_product_image_ids = [];

		$sql = "SELECT product_image_id, product_id, `image` FROM `" . DB_PREFIX . "product_image`";
		$sql .= " WHERE product_id = '" . (int)$product_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$old_product_image_ids[$row['product_id']][$row['image']] = $row['product_image_id'];
		}

		if ($old_product_image_ids) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_image` WHERE product_id = '" . (int)$product_id . "'");
		}

		return $old_product_image_ids;
	}

	protected function deleteUnlistedAdditionalImages(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_image` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreAdditionalImageCells($i, $j, $worksheet, &$image): void {
		return;
	}

	protected function uploadAdditionalImages($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('AdditionalImages');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteAdditionalImages();
		}

		$old_product_image_ids = [];
		$previous_product_id = 0;

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			$j = 1;

			if ($i === 0) {
				continue;
			}

			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === "") {
				continue;
			}

			$image_name = $this->getCell($data, $i, $j++, '');
			$palette_color_id = $this->getCell($data, $i, $j++);
			$sort_order = $this->getCell($data, $i, $j++, '0');

			$image = [
				'product_id'       => $product_id,
				'image_name'       => $image_name,
				'palette_color_id' => $palette_color_id,
				'sort_order'       => $sort_order,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$old_product_image_ids = $this->deleteAdditionalImage($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreAdditionalImageCells($i, $j, $data, $image);
			$this->storeAdditionalImageIntoDatabase($image, $old_product_image_ids);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedAdditionalImages($unlisted_product_ids);
		}
	}

	/**
	 * Specials Import
	 */
	protected function storeSpecialIntoDatabase(&$special, &$old_product_special_ids, &$customer_group_ids): void {
		$product_id = $special['product_id'];
		$customer_group_id = $customer_group_ids[$special['customer_group']] ?? $this->config->get('config_customer_group_id');
		$priority = $special['priority'];
		$price = $special['price'];
		$date_start = $special['date_start'];
		$date_end = $special['date_end'];

		if (isset($old_product_special_ids[$product_id][$customer_group_id])) {
			$product_special_id = $old_product_special_ids[$product_id][$customer_group_id];

			$sql = "INSERT INTO `" . DB_PREFIX . "product_special`";
			$sql .= " (`product_special_id`,`product_id`,`customer_group_id`,`priority`,`price`,`date_start`,`date_end`)";
			$sql .= " VALUES ($product_special_id, $product_id, $customer_group_id, $priority, $price, '$date_start', '$date_end')";

			unset($old_product_special_ids[$product_id][$customer_group_id]);
		} else {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_special`";
			$sql .= " (`product_id`,`customer_group_id`,`priority`,`price`,`date_start`,`date_end`)";
			$sql .= " VALUES ($product_id, $customer_group_id, $priority, $price, '$date_start', '$date_end')";
		}

		$this->db->query($sql);
	}

	protected function deleteSpecials(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_special`");
	}

	protected function deleteSpecial(&$product_id): array {
		$old_product_special_ids = [];

		$sql = "SELECT product_special_id, product_id, customer_group_id FROM `" . DB_PREFIX . "product_special`";
		$sql .= " WHERE product_id = '" . (int)$product_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$old_product_special_ids[$row['product_id']][$row['customer_group_id']] = $row['product_special_id'];
		}

		if ($old_product_special_ids) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE product_id = '" . (int)$product_id . "'");
		}

		return $old_product_special_ids;
	}

	protected function deleteUnlistedSpecials(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreSpecialCells($i, $j, $worksheet, &$special): void {
		return;
	}

	protected function uploadSpecials($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('Specials');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteSpecials();
		}

		$customer_group_ids = $this->getCustomerGroupIds();
		$old_product_special_ids = [];
		$previous_product_id = 0;

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			$j = 1;

			if ($i === 0) {
				continue;
			}

			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === "") {
				continue;
			}

			$customer_group = trim($this->getCell($data, $i, $j++));

			if ($customer_group === "") {
				continue;
			}

			$priority = $this->getCell($data, $i, $j++, '0');
			$price = $this->getCell($data, $i, $j++, '0');
			$date_start = $this->getCell($data, $i, $j++, '0000-00-00');
			$date_end = $this->getCell($data, $i, $j++, '0000-00-00');

			$special = [
				'product_id'     => $product_id,
				'customer_group' => $customer_group,
				'priority'       => $priority,
				'price'          => $price,
				'date_start'     => $date_start,
				'date_end'       => $date_end,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$old_product_special_ids = $this->deleteSpecial($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreSpecialCells($i, $j, $data, $special);
			$this->storeSpecialIntoDatabase($special, $old_product_special_ids, $customer_group_ids);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedSpecials($unlisted_product_ids);
		}
	}

	/**
	 * Discounts Import
	 */
	protected function storeDiscountIntoDatabase(&$discount, &$old_product_discount_ids, &$customer_group_ids): void {
		$product_id = $discount['product_id'];
		$customer_group_id = $customer_group_ids[$discount['customer_group']] ?? $this->config->get('config_customer_group_id');
		$quantity = $discount['quantity'];
		$priority = $discount['priority'];
		$price = $discount['price'];
		$date_start = $discount['date_start'];
		$date_end = $discount['date_end'];

		if (isset($old_product_discount_ids[$product_id][$customer_group_id][$quantity])) {
			$product_discount_id = $old_product_discount_ids[$product_id][$customer_group_id][$quantity];

			$sql = "INSERT INTO `" . DB_PREFIX . "product_discount`";
			$sql .= " (`product_discount_id`,`product_id`,`customer_group_id`,`quantity`,`priority`,`price`,`date_start`,`date_end`)";
			$sql .= " VALUES ($product_discount_id, $product_id, $customer_group_id, $quantity, $priority, $price, '$date_start', '$date_end')";

			unset($old_product_discount_ids[$product_id][$customer_group_id][$quantity]);
		} else {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_discount`";
			$sql .= " (`product_id`,`customer_group_id`,`quantity`,`priority`,`price`,`date_start`,`date_end`)";
			$sql .= " VALUES ($product_id, $customer_group_id, $quantity, $priority, $price, '$date_start', '$date_end')";
		}

		$this->db->query($sql);
	}

	protected function deleteDiscounts(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_discount`");
	}

	protected function deleteDiscount(&$product_id): array {
		$old_product_discount_ids = [];

		$sql = "SELECT product_discount_id, product_id, customer_group_id, quantity";
		$sql .= " FROM `" . DB_PREFIX . "product_discount`";
		$sql .= " WHERE product_id = '" . (int)$product_id . "'";
		$sql .= " ORDER BY product_id ASC, customer_group_id ASC, quantity ASC";

		foreach ($this->db->query($sql)->rows as $row) {
			$old_product_discount_ids[$row['product_id']][$row['customer_group_id']][$row['quantity']] = $row['product_discount_id'];
		}

		if ($old_product_discount_ids) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_discount` WHERE product_id = '" . (int)$product_id . "'");
		}

		return $old_product_discount_ids;
	}

	protected function deleteUnlistedDiscounts(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_discount` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreDiscountCells($i, $j, $worksheet, &$discount): void {
		return;
	}

	protected function uploadDiscounts($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('Discounts');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteDiscounts();
		}

		$customer_group_ids = $this->getCustomerGroupIds();
		$old_product_discount_ids = [];
		$previous_product_id = 0;

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			$j = 1;

			if ($i === 0) {
				continue;
			}

			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === "") {
				continue;
			}

			$customer_group = trim($this->getCell($data, $i, $j++));

			if ($customer_group === "") {
				continue;
			}

			$quantity = $this->getCell($data, $i, $j++, '0');
			$priority = $this->getCell($data, $i, $j++, '0');
			$price = $this->getCell($data, $i, $j++, '0');
			$date_start = $this->getCell($data, $i, $j++, '0000-00-00');
			$date_end = $this->getCell($data, $i, $j++, '0000-00-00');

			$discount = [
				'product_id'     => $product_id,
				'customer_group' => $customer_group,
				'quantity'       => $quantity,
				'priority'       => $priority,
				'price'          => $price,
				'date_start'     => $date_start,
				'date_end'       => $date_end,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$old_product_discount_ids = $this->deleteDiscount($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreDiscountCells($i, $j, $data, $discount);
			$this->storeDiscountIntoDatabase($discount, $old_product_discount_ids, $customer_group_ids);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedDiscounts($unlisted_product_ids);
		}
	}

	/**
	 * Rewards Import
	 */
	protected function storeRewardIntoDatabase(&$reward, &$old_product_reward_ids, &$customer_group_ids): void {
		$product_id = $reward['product_id'];
		$customer_group_id = $customer_group_ids[$reward['customer_group']] ?? $this->config->get('config_customer_group_id');
		$points = $reward['points'];

		if (isset($old_product_reward_ids[$product_id][$customer_group_id])) {
			$product_reward_id = $old_product_reward_ids[$product_id][$customer_group_id];

			$sql = "INSERT INTO `" . DB_PREFIX . "product_reward`";
			$sql .= " (`product_reward_id`,`product_id`,`customer_group_id`,`points`)";
			$sql .= " VALUES ($product_reward_id, $product_id, $customer_group_id, $points)";

			unset($old_product_reward_ids[$product_id][$customer_group_id]);
		} else {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_reward`";
			$sql .= " (`product_id`,`customer_group_id`,`points`) VALUES ($product_id, $customer_group_id, $points)";
		}

		$this->db->query($sql);
	}

	protected function deleteRewards(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_reward`");
	}

	protected function deleteReward(&$product_id): array {
		$old_product_reward_ids = [];

		$sql = "SELECT product_reward_id, product_id, customer_group_id FROM `" . DB_PREFIX . "product_reward`";
		$sql .= " WHERE product_id = '" . (int)$product_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$old_product_reward_ids[$row['product_id']][$row['customer_group_id']] = $row['product_reward_id'];
		}

		if ($old_product_reward_ids) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_reward` WHERE product_id = '" . (int)$product_id . "'");
		}

		return $old_product_reward_ids;
	}

	protected function deleteUnlistedRewards(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_reward` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreRewardCells($i, $j, $worksheet, &$reward): void {
		return;
	}

	protected function uploadRewards($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('Rewards');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteRewards();
		}

		$customer_group_ids = $this->getCustomerGroupIds();
		$old_product_reward_ids = [];
		$previous_product_id = 0;

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			$j = 1;

			if ($i === 0) {
				continue;
			}

			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === "") {
				continue;
			}

			$customer_group = trim($this->getCell($data, $i, $j++));

			if ($customer_group === "") {
				continue;
			}

			$points = $this->getCell($data, $i, $j++, '0');

			$reward = [
				'product_id'     => $product_id,
				'customer_group' => $customer_group,
				'points'         => $points,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$old_product_reward_ids = $this->deleteReward($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreRewardCells($i, $j, $data, $reward);
			$this->storeRewardIntoDatabase($reward, $old_product_reward_ids, $customer_group_ids);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedRewards($unlisted_product_ids);
		}
	}

	/**
	 * Product Options Import
	 */
	protected function storeProductOptionIntoDatabase(&$product_option, &$old_product_option_ids): void {
		$product_id = $product_option['product_id'];
		$option_id = $product_option['option_id'];
		$option_value = $product_option['option_value'];
		$required = ((strtoupper($product_option['required']) === "TRUE") || (strtoupper($product_option['required']) === "YES") || (strtoupper($product_option['required']) === "ENABLED")) ? 1 : 0;

		if (isset($old_product_option_ids[$product_id][$option_id])) {
			$product_option_id = $old_product_option_ids[$product_id][$option_id];

			$sql = "INSERT INTO `" . DB_PREFIX . "product_option`";
			$sql .= " (`product_option_id`,`product_id`,`option_id`,`option_value`,`required`)";
			$sql .= " VALUES ($product_option_id, $product_id, $option_id, '" . $this->db->escape($option_value) . "', '$required')";

			unset($old_product_option_ids[$product_id][$option_id]);
		} else {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_option`";
			$sql .= " (`product_id`,`option_id`,`option_value`,`required`)";
			$sql .= " VALUES ($product_id, $option_id, '" . $this->db->escape($option_value) . "', '$required')";
		}

		$this->db->query($sql);
	}

	protected function deleteProductOptions(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_option`");
	}

	protected function deleteProductOption(&$product_id): array {
		$old_product_option_ids = [];

		$sql = "SELECT product_option_id, product_id, option_id FROM `" . DB_PREFIX . "product_option`";
		$sql .= " WHERE product_id = '" . (int)$product_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$old_product_option_ids[$row['product_id']][$row['option_id']] = $row['product_option_id'];
		}

		if ($old_product_option_ids) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE product_id = '" . (int)$product_id . "'");
		}

		return $old_product_option_ids;
	}

	protected function deleteUnlistedProductOptions(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreProductOptionCells($i, $j, $worksheet, &$product_option): void {
		return;
	}

	protected function uploadProductOptions($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('ProductOptions');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteProductOptions();
		}

		if (!$this->config->get('export_import_settings_use_option_id')) {
			$option_ids = $this->getOptionIds();
		}

		$old_product_option_ids = [];
		$previous_product_id = 0;

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			$j = 1;

			if ($i === 0) {
				continue;
			}

			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === '') {
				continue;
			}

			if ($this->config->get('export_import_settings_use_option_id')) {
				$option_id = $this->getCell($data, $i, $j++, '');
			} else {
				$option_name = $this->getCell($data, $i, $j++);
				$option_id = $option_ids[$option_name] ?? '';
			}

			if ($option_id === '') {
				continue;
			}

			$option_value = $this->getCell($data, $i, $j++, '');
			$required = $this->getCell($data, $i, $j++, '0');

			$product_option = [
				'product_id'   => $product_id,
				'option_id'    => $option_id,
				'option_value' => $option_value,
				'required'     => $required,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$old_product_option_ids = $this->deleteProductOption($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreProductOptionCells($i, $j, $data, $product_option);
			$this->storeProductOptionIntoDatabase($product_option, $old_product_option_ids);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedProductOptions($unlisted_product_ids);
		}
	}

	/**
	 * Product Option Values Import
	 */
	protected function storeProductOptionValueIntoDatabase(&$product_option_value, &$old_product_option_value_ids): void {
		$product_id = $product_option_value['product_id'];
		$option_id = $product_option_value['option_id'];
		$option_value_id = $product_option_value['option_value_id'];
		$quantity = $product_option_value['quantity'];
		$subtract = ((strtoupper($product_option_value['subtract']) === "TRUE") || (strtoupper($product_option_value['subtract']) === "YES") || (strtoupper($product_option_value['subtract']) === "ENABLED")) ? 1 : 0;
		$price = $product_option_value['price'];
		$price_prefix = $product_option_value['price_prefix'];
		$points = $product_option_value['points'];
		$points_prefix = $product_option_value['points_prefix'];
		$weight = $product_option_value['weight'];
		$weight_prefix = $product_option_value['weight_prefix'];
		$product_option_id = $product_option_value['product_option_id'];

		if (isset($old_product_option_value_ids[$product_id][$option_id][$option_value_id])) {
			$product_option_value_id = $old_product_option_value_ids[$product_id][$option_id][$option_value_id];

			$sql = "INSERT INTO `" . DB_PREFIX . "product_option_value`";
			$sql .= " (`product_option_value_id`,`product_option_id`,`product_id`,`option_id`,`option_value_id`,`quantity`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`)";
			$sql .= " VALUES ($product_option_value_id, $product_option_id, $product_id, $option_id, $option_value_id, $quantity, $subtract, $price, '$price_prefix', $points, '$points_prefix', $weight, '$weight_prefix')";

			unset($old_product_option_value_ids[$product_id][$option_id][$option_value_id]);
		} else {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_option_value`";
			$sql .= " (`product_option_id`,`product_id`,`option_id`,`option_value_id`,`quantity`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`)";
			$sql .= " VALUES ($product_option_id, $product_id, $option_id, $option_value_id, $quantity, $subtract, $price, '$price_prefix', $points, '$points_prefix', $weight, '$weight_prefix')";
		}

		$this->db->query($sql);
	}

	protected function deleteProductOptionValues(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_option_value`");
	}

	protected function deleteProductOptionValue(&$product_id): array {
		$old_product_option_value_ids = [];

		$sql = "SELECT product_option_value_id, product_id, option_id, option_value_id FROM `" . DB_PREFIX . "product_option_value`";
		$sql .= " WHERE product_id = '" . (int)$product_id . "'";

		foreach ($this->db->query($sql)->rows as $row) {
			$old_product_option_value_ids[$row['product_id']][$row['option_id']][$row['option_value_id']] = $row['product_option_value_id'];
		}

		if ($old_product_option_value_ids) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option_value` WHERE product_id = '" . (int)$product_id . "'");
		}

		return $old_product_option_value_ids;
	}

	protected function deleteUnlistedProductOptionValues(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option_value` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreProductOptionValueCells($i, $j, $worksheet, &$product_option_value): void {
		return;
	}

	protected function uploadProductOptionValues($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('ProductOptionValues');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteProductOptionValues();
		}

		if (!$this->config->get('export_import_settings_use_option_id')) {
			$option_ids = $this->getOptionIds();
		}

		if (!$this->config->get('export_import_settings_use_option_value_id')) {
			$option_value_ids = $this->getOptionValueIds();
		}

		$old_product_option_ids = [];
		$old_product_option_value_ids = [];
		$previous_product_id = 0;
		$product_option_id = 0;

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			$j = 1;

			if ($i === 0) {
				continue;
			}

			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === '') {
				continue;
			}

			if ($this->config->get('export_import_settings_use_option_id')) {
				$option_id = $this->getCell($data, $i, $j++, '');
			} else {
				$option_name = $this->getCell($data, $i, $j++);
				$option_id   = $option_ids[$option_name] ?? '';
			}

			if ($option_id === '') {
				continue;
			}

			if ($this->config->get('export_import_settings_use_option_value_id')) {
				$option_value_id = $this->getCell($data, $i, $j++, '');
			} else {
				$option_value_name = $this->getCell($data, $i, $j++);
				$option_value_id   = $option_value_ids[$option_id][$option_value_name] ?? '';
			}

			if ($option_value_id === '') {
				continue;
			}

			$quantity = $this->getCell($data, $i, $j++, '0');
			$subtract = $this->getCell($data, $i, $j++, 'false');
			$price = $this->getCell($data, $i, $j++, '0');
			$price_prefix = $this->getCell($data, $i, $j++, '+');
			$points = $this->getCell($data, $i, $j++, '0');
			$points_prefix = $this->getCell($data, $i, $j++, '+');
			$weight = $this->getCell($data, $i, $j++, '0.00');
			$weight_prefix = $this->getCell($data, $i, $j++, '+');

			if ($product_id !== $previous_product_id) {
				$old_product_option_ids = $this->getProductOptionIds($product_id);
			}

			$product_option_value = [
				'product_id'        => $product_id,
				'option_id'         => $option_id,
				'option_value_id'   => $option_value_id,
				'quantity'          => $quantity,
				'subtract'          => $subtract,
				'price'             => $price,
				'price_prefix'      => $price_prefix,
				'points'            => $points,
				'points_prefix'     => $points_prefix,
				'weight'            => $weight,
				'weight_prefix'     => $weight_prefix,
				'product_option_id' => $old_product_option_ids[$option_id] ?? 0,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$old_product_option_value_ids = $this->deleteProductOptionValue($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreProductOptionValueCells($i, $j, $data, $product_option_value);
			$this->storeProductOptionValueIntoDatabase($product_option_value, $old_product_option_value_ids);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedProductOptionValues($unlisted_product_ids);
		}
	}

	/**
	 * Product Colors Import
	 */
	protected function storeProductColorIntoDatabase(&$product_color): void {
		$sql = "INSERT INTO `" . DB_PREFIX . "product_color`";
		$sql .= " (`product_color_id`,`product_id`,`palette_color_id`)";
		$sql .= " VALUES (" . (int)$product_color['product_color_id'] . ", " . (int)$product_color['product_id'] . ", " . (int)$product_color['palette_color_id'] . ")";

		$this->db->query($sql);
	}

	protected function deleteProductColors(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_color`");
	}

	protected function deleteProductColor(&$product_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_color` WHERE product_id = '" . (int)$product_id . "'");
	}

	protected function deleteUnlistedProductColors(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_color` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreProductColorCells($i, $j, $worksheet, &$product_color): void {
		return;
	}

	protected function uploadProductColors($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('ProductColors');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteProductColors();
		}

		$previous_product_id = 0;
		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === '') {
				continue;
			}

			$product_color_id = trim($this->getCell($data, $i, $j++));

			if ($product_color_id === '') {
				continue;
			}

			$palette_color_id = trim($this->getCell($data, $i, $j++));

			if ($palette_color_id === '') {
				continue;
			}

			$product_color = [
				'product_id'       => $product_id,
				'product_color_id' => $product_color_id,
				'palette_color_id' => $palette_color_id,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$this->deleteProductColor($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreProductColorCells($i, $j, $data, $product_color);
			$this->storeProductColorIntoDatabase($product_color);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedProductColors($unlisted_product_ids);
		}
	}

	/**
	 * Product Fields Import
	 */
	protected function storeProductFieldIntoDatabase(&$product_field, $languages): void {
		$product_id = $product_field['product_id'];
		$field_id = $product_field['field_id'];
		$texts = $product_field['texts'];

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$text = isset($texts[$language_code]) ? $this->db->escape($texts[$language_code]) : '';

			$sql = "INSERT INTO `" . DB_PREFIX . "product_field`";
			$sql .= " (`product_id`,`field_id`,`language_id`,`text`) VALUES ($product_id, $field_id, $language_id, '$text')";

			$this->db->query($sql);
		}
	}

	protected function deleteProductFields(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_field`");
	}

	protected function deleteProductField(&$product_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_field` WHERE product_id = '" . (int)$product_id . "'");
	}

	protected function deleteUnlistedProductFields(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_field` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreProductFieldCells($i, $j, $worksheet, &$product_field): void {
		return;
	}

	protected function uploadProductFields($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('ProductFields');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteProductFields();
		}

		$languages = $this->getLanguages();
		$previous_product_id = 0;
		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === '') {
				continue;
			}

			$field_id = trim($this->getCell($data, $i, $j++));

			if ($field_id === '') {
				continue;
			}

			$texts = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "text(")) {
				$language_code = substr($first_row[$j - 1], strlen("text("), strlen($first_row[$j - 1]) - strlen("text(") - 1);
				$text = $this->getCell($data, $i, $j++);
				$texts[$language_code] = htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$product_field = [
				'product_id' => $product_id,
				'field_id'   => $field_id,
				'texts'      => $texts,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$this->deleteProductField($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreProductFieldCells($i, $j, $data, $product_field);
			$this->storeProductFieldIntoDatabase($product_field, $languages);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedProductFields($unlisted_product_ids);
		}
	}

	/**
	 * Product Attributes Import
	 */
	protected function storeProductAttributeIntoDatabase(&$product_attribute, $languages): void {
		$product_id = $product_attribute['product_id'];
		$attribute_id = $product_attribute['attribute_id'];
		$texts = $product_attribute['texts'];

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$text = isset($texts[$language_code]) ? $this->db->escape($texts[$language_code]) : '';

			$sql = "INSERT INTO `" . DB_PREFIX . "product_attribute`";
			$sql .= " (`product_id`,`attribute_id`,`language_id`,`text`) VALUES ($product_id, $attribute_id, $language_id, '$text')";

			$this->db->query($sql);
		}
	}

	protected function deleteProductAttributes(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_attribute`");
	}

	protected function deleteProductAttribute(&$product_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE product_id = '" . (int)$product_id . "'");
	}

	protected function deleteUnlistedProductAttributes(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreProductAttributeCells($i, $j, $worksheet, &$product_attribute): void {
		return;
	}

	protected function uploadProductAttributes($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('ProductAttributes');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteProductAttributes();
		}

		if (!$this->config->get('export_import_settings_use_attribute_group_id')) {
			$attribute_group_ids = $this->getAttributeGroupIds();
		}

		if (!$this->config->get('export_import_settings_use_attribute_id')) {
			$attribute_ids = $this->getAttributeIds();
		}

		$languages = $this->getLanguages();
		$previous_product_id = 0;
		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === '') {
				continue;
			}

			if ($this->config->get('export_import_settings_use_attribute_group_id')) {
				$attribute_group_id = $this->getCell($data, $i, $j++, '');
			} else {
				$attribute_group_name = $this->getCell($data, $i, $j++);
				$attribute_group_id = $attribute_group_ids[$attribute_group_name] ?? '';
			}

			if ($attribute_group_id === '') {
				continue;
			}

			if ($this->config->get('export_import_settings_use_attribute_id')) {
				$attribute_id = $this->getCell($data, $i, $j++, '');
			} else {
				$attribute_name = $this->getCell($data, $i, $j++);
				$attribute_id = $attribute_ids[$attribute_group_id][$attribute_name] ?? '';
			}

			if ($attribute_id === '') {
				continue;
			}

			$texts = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "text(")) {
				$language_code = substr($first_row[$j - 1], strlen("text("), strlen($first_row[$j - 1]) - strlen("text(") - 1);
				$text = $this->getCell($data, $i, $j++);
				$texts[$language_code] = htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$product_attribute = [
				'product_id'         => $product_id,
				'attribute_group_id' => $attribute_group_id,
				'attribute_id'       => $attribute_id,
				'texts'              => $texts,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$this->deleteProductAttribute($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreProductAttributeCells($i, $j, $data, $product_attribute);
			$this->storeProductAttributeIntoDatabase($product_attribute, $languages);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedProductAttributes($unlisted_product_ids);
		}
	}

	/**
	 * Product Filters Import
	 */
	protected function storeProductFilterIntoDatabase(&$product_filter, $languages): void {
		$sql = "INSERT INTO `" . DB_PREFIX . "product_filter`";
		$sql .= " (`product_id`,`filter_id`) VALUES (" . (int)$product_filter['product_id'] . ", " . (int)$product_filter['filter_id'] . ")";

		$this->db->query($sql);
	}

	protected function deleteProductFilters(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "product_filter`");
	}

	protected function deleteProductFilter(&$product_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_filter` WHERE product_id = '" . (int)$product_id . "'");
	}

	protected function deleteUnlistedProductFilters(&$unlisted_product_ids): void {
		foreach ($unlisted_product_ids as $product_id) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_filter` WHERE product_id = '" . (int)$product_id . "'");
		}
	}

	protected function moreProductFilterCells($i, $j, $worksheet, &$product_filter): void {
		return;
	}

	protected function uploadProductFilters($reader, $incremental, &$available_product_ids): void {
		$data = $reader->getSheetByName('ProductFilters');

		if ($data === null) {
			return;
		}

		if ($incremental) {
			$unlisted_product_ids = $available_product_ids;
		} else {
			$this->deleteProductFilters();
		}

		if (!$this->config->get('export_import_settings_use_filter_group_id')) {
			$filter_group_ids = $this->getFilterGroupIds();
		}

		if (!$this->config->get('export_import_settings_use_filter_id')) {
			$filter_ids = $this->getFilterIds();
		}

		$languages = $this->getLanguages();
		$previous_product_id = 0;
		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$product_id = trim($this->getCell($data, $i, $j++));

			if ($product_id === '') {
				continue;
			}

			if ($this->config->get('export_import_settings_use_filter_group_id')) {
				$filter_group_id = $this->getCell($data, $i, $j++, '');
			} else {
				$filter_group_name = $this->getCell($data, $i, $j++);
				$filter_group_id = $filter_group_ids[$filter_group_name] ?? '';
			}

			if ($filter_group_id === '') {
				continue;
			}

			if ($this->config->get('export_import_settings_use_filter_id')) {
				$filter_id = $this->getCell($data, $i, $j++, '');
			} else {
				$filter_name = $this->getCell($data, $i, $j++);
				$filter_id = $filter_ids[$filter_group_id][$filter_name] ?? '';
			}

			if ($filter_id === '') {
				continue;
			}

			$product_filter = [
				'product_id'      => $product_id,
				'filter_group_id' => $filter_group_id,
				'filter_id'       => $filter_id,
			];

			if ($incremental && $product_id !== $previous_product_id) {
				$this->deleteProductFilter($product_id);

				if (isset($unlisted_product_ids[$product_id])) {
					unset($unlisted_product_ids[$product_id]);
				}
			}

			$this->moreProductFilterCells($i, $j, $data, $product_filter);
			$this->storeProductFilterIntoDatabase($product_filter, $languages);
			$previous_product_id = $product_id;
		}

		if ($incremental) {
			$this->deleteUnlistedProductFilters($unlisted_product_ids);
		}
	}

	/**
	 * Options Import
	 */
	protected function storeOptionIntoDatabase(&$option, $languages): void {
		$option_id = $option['option_id'];
		$sort_order = $option['sort_order'];
		$names = $option['names'];

		$this->db->query("INSERT INTO `" . DB_PREFIX . "option` (`option_id`,`type`,`sort_order`) VALUES ($option_id, '" . $this->db->escape($option['type']) . "', $sort_order)");

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$name = isset($names[$language_code]) ? $this->db->escape($names[$language_code]) : '';

			$this->db->query("INSERT INTO `" . DB_PREFIX . "option_description` (`option_id`,`language_id`,`name`) VALUES ($option_id, $language_id, '$name')");
		}
	}

	protected function deleteOptions(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_description`");
	}

	protected function deleteOption(&$option_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option` WHERE option_id = '" . (int)$option_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option_description` WHERE option_id = '" . (int)$option_id . "'");
	}

	protected function moreOptionCells($i, $j, $worksheet, &$option): void {
		return;
	}

	protected function uploadOptions($reader, $incremental): void {
		$data = $reader->getSheetByName('Options');

		if ($data === null) {
			return;
		}

		$languages = $this->getLanguages();

		if (!$incremental) {
			$this->deleteOptions();
		}

		$first_row = [];
		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$option_id = trim($this->getCell($data, $i, $j++));

			if ($option_id === '') {
				continue;
			}

			$type = $this->getCell($data, $i, $j++, '');
			$sort_order = $this->getCell($data, $i, $j++, '0');
			$names = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "name(")) {
				$language_code = substr($first_row[$j - 1], strlen("name("), strlen($first_row[$j - 1]) - strlen("name(") - 1);
				$name = $this->getCell($data, $i, $j++);
				$names[$language_code] = htmlspecialchars((string)$name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$option = [
				'option_id'  => $option_id,
				'type'       => $type,
				'sort_order' => $sort_order,
				'names'      => $names,
			];

			if ($incremental) {
				$this->deleteOption($option_id);
			}

			$this->moreOptionCells($i, $j, $data, $option);
			$this->storeOptionIntoDatabase($option, $languages);
		}
	}

	/**
	 * Option Values Import
	 */
	protected function storeOptionValueIntoDatabase(&$option_value, $languages, $exist_image = true): void {
		$option_value_id = $option_value['option_value_id'];
		$option_id = $option_value['option_id'];
		$sort_order = $option_value['sort_order'];
		$names = $option_value['names'];

		if ($exist_image) {
			$image = $option_value['image'];
			$sql = "INSERT INTO `" . DB_PREFIX . "option_value` (`option_value_id`,`option_id`,`image`,`sort_order`) VALUES ($option_value_id, $option_id, '" . $this->db->escape($image) . "', $sort_order)";
		} else {
			$sql = "INSERT INTO `" . DB_PREFIX . "option_value` (`option_value_id`,`option_id`,`sort_order`) VALUES ($option_value_id, $option_id, $sort_order)";
		}

		$this->db->query($sql);

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$name = isset($names[$language_code]) ? $this->db->escape($names[$language_code]) : '';

			$this->db->query("INSERT INTO `" . DB_PREFIX . "option_value_description` (`option_value_id`,`language_id`,`option_id`,`name`) VALUES ($option_value_id, $language_id, $option_id, '$name')");
		}
	}

	protected function deleteOptionValues(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_value`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "option_value_description`");
	}

	protected function deleteOptionValue(&$option_value_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option_value` WHERE option_value_id = '" . (int)$option_value_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "option_value_description` WHERE option_value_id = '" . (int)$option_value_id . "'");
	}

	protected function moreOptionValueCells($i, $j, $worksheet, &$option): void {
		return;
	}

	protected function uploadOptionValues($reader, $incremental): void {
		$data = $reader->getSheetByName('OptionValues');

		if ($data === null) {
			return;
		}

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "option_value` LIKE 'image'");
		$exist_image = ($query->num_rows > 0);
		$languages = $this->getLanguages();

		if (!$incremental) {
			$this->deleteOptionValues();
		}

		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$option_value_id = trim($this->getCell($data, $i, $j++));

			if ($option_value_id === '') {
				continue;
			}

			$option_id = trim($this->getCell($data, $i, $j++));

			if ($option_id === '') {
				continue;
			}

			if ($exist_image) {
				$image = $this->getCell($data, $i, $j++, '');
			}

			$sort_order = $this->getCell($data, $i, $j++, '0');
			$names = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "name(")) {
				$language_code = substr($first_row[$j - 1], strlen("name("), strlen($first_row[$j - 1]) - strlen("name(") - 1);
				$name = $this->getCell($data, $i, $j++);
				$names[$language_code] = htmlspecialchars((string)$name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$option_value = [
				'option_value_id' => $option_value_id,
				'option_id'       => $option_id,
				'sort_order'      => $sort_order,
				'names'           => $names,
			];

			if ($exist_image) {
				$option_value['image'] = $image;
			}

			if ($incremental) {
				$this->deleteOptionValue($option_value_id);
			}

			$this->moreOptionValueCells($i, $j, $data, $option_value);
			$this->storeOptionValueIntoDatabase($option_value, $languages, $exist_image);
		}
	}

	/**
	 * Attribute Groups Import
	 */
	protected function storeAttributeGroupIntoDatabase(&$attribute_group, $languages): void {
		$attribute_group_id = $attribute_group['attribute_group_id'];
		$sort_order = $attribute_group['sort_order'];
		$names = $attribute_group['names'];

		$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute_group` (`attribute_group_id`,`sort_order`) VALUES ($attribute_group_id, $sort_order)");

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$name = isset($names[$language_code]) ? $this->db->escape($names[$language_code]) : '';

			$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute_group_description` (`attribute_group_id`,`language_id`,`name`) VALUES ($attribute_group_id, $language_id, '$name')");
		}
	}

	protected function deleteAttributeGroups(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute_group`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute_group_description`");
	}

	protected function deleteAttributeGroup(&$attribute_group_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute_group` WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute_group_description` WHERE attribute_group_id = '" . (int)$attribute_group_id . "'");
	}

	protected function moreAttributeGroupCells($i, $j, $worksheet, &$attribute_group): void {
		return;
	}

	protected function uploadAttributeGroups($reader, $incremental): void {
		$data = $reader->getSheetByName('AttributeGroups');

		if ($data === null) {
			return;
		}

		$languages = $this->getLanguages();

		if (!$incremental) {
			$this->deleteAttributeGroups();
		}

		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$attribute_group_id = trim($this->getCell($data, $i, $j++));

			if ($attribute_group_id === '') {
				continue;
			}

			$sort_order = $this->getCell($data, $i, $j++, '0');
			$names = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "name(")) {
				$language_code = substr($first_row[$j - 1], strlen("name("), strlen($first_row[$j - 1]) - strlen("name(") - 1);
				$name = $this->getCell($data, $i, $j++);
				$names[$language_code] = htmlspecialchars((string)$name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$attribute_group = [
				'attribute_group_id' => $attribute_group_id,
				'sort_order'         => $sort_order,
				'names'              => $names,
			];

			if ($incremental) {
				$this->deleteAttributeGroup($attribute_group_id);
			}

			$this->moreAttributeGroupCells($i, $j, $data, $attribute_group);
			$this->storeAttributeGroupIntoDatabase($attribute_group, $languages);
		}
	}

	/**
	 * Attributes Import
	 */
	protected function storeAttributeIntoDatabase(&$attribute, $languages): void {
		$attribute_id = $attribute['attribute_id'];
		$attribute_group_id = $attribute['attribute_group_id'];
		$sort_order = $attribute['sort_order'];
		$names = $attribute['names'];

		$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute` (`attribute_id`,`attribute_group_id`,`sort_order`) VALUES ($attribute_id, $attribute_group_id, $sort_order)");

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$name = isset($names[$language_code]) ? $this->db->escape($names[$language_code]) : '';

			$this->db->query("INSERT INTO `" . DB_PREFIX . "attribute_description` (`attribute_id`,`language_id`,`name`) VALUES ($attribute_id, $language_id, '$name')");
		}
	}

	protected function deleteAttributes(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "attribute_description`");
	}

	protected function deleteAttribute(&$attribute_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute` WHERE attribute_id = '" . (int)$attribute_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "attribute_description` WHERE attribute_id = '" . (int)$attribute_id . "'");
	}

	protected function moreAttributeCells($i, $j, $worksheet, &$attribute): void {
		return;
	}

	protected function uploadAttributes($reader, $incremental): void {
		$data = $reader->getSheetByName('Attributes');

		if ($data === null) {
			return;
		}

		$languages = $this->getLanguages();

		if (!$incremental) {
			$this->deleteAttributes();
		}

		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$attribute_id = trim($this->getCell($data, $i, $j++));

			if ($attribute_id === '') {
				continue;
			}

			$attribute_group_id = trim($this->getCell($data, $i, $j++));

			if ($attribute_group_id === '') {
				continue;
			}

			$sort_order = $this->getCell($data, $i, $j++, '0');
			$names = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "name(")) {
				$language_code = substr($first_row[$j - 1], strlen("name("), strlen($first_row[$j - 1]) - strlen("name(") - 1);
				$name = $this->getCell($data, $i, $j++);
				$names[$language_code] = htmlspecialchars((string)$name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$attribute = [
				'attribute_id'       => $attribute_id,
				'attribute_group_id' => $attribute_group_id,
				'sort_order'         => $sort_order,
				'names'              => $names,
			];

			if ($incremental) {
				$this->deleteAttribute($attribute_id);
			}

			$this->moreAttributeCells($i, $j, $data, $attribute);
			$this->storeAttributeIntoDatabase($attribute, $languages);
		}
	}

	/**
	 * Filter Groups Import
	 */
	protected function storeFilterGroupIntoDatabase(&$filter_group, $languages): void {
		$filter_group_id = $filter_group['filter_group_id'];
		$sort_order = $filter_group['sort_order'];
		$names = $filter_group['names'];

		$this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group` (`filter_group_id`,`sort_order`) VALUES ($filter_group_id, $sort_order)");

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$name = isset($names[$language_code]) ? $this->db->escape($names[$language_code]) : '';

			$this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group_description` (`filter_group_id`,`language_id`,`name`) VALUES ($filter_group_id, $language_id, '$name')");
		}
	}

	protected function deleteFilterGroups(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter_group`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter_group_description`");
	}

	protected function deleteFilterGroup(&$filter_group_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter_group` WHERE filter_group_id = '" . (int)$filter_group_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter_group_description` WHERE filter_group_id = '" . (int)$filter_group_id . "'");
	}

	protected function moreFilterGroupCells($i, $j, $worksheet, &$filter_group): void {
		return;
	}

	protected function uploadFilterGroups($reader, $incremental): void {
		$data = $reader->getSheetByName('FilterGroups');

		if ($data === null) {
			return;
		}

		$languages = $this->getLanguages();

		if (!$incremental) {
			$this->deleteFilterGroups();
		}

		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$filter_group_id = trim($this->getCell($data, $i, $j++));

			if ($filter_group_id === '') {
				continue;
			}

			$sort_order = $this->getCell($data, $i, $j++, '0');
			$names = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "name(")) {
				$language_code = substr($first_row[$j - 1], strlen("name("), strlen($first_row[$j - 1]) - strlen("name(") - 1);
				$name = $this->getCell($data, $i, $j++);
				$names[$language_code] = htmlspecialchars((string)$name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$filter_group = [
				'filter_group_id' => $filter_group_id,
				'sort_order'      => $sort_order,
				'names'           => $names,
			];

			if ($incremental) {
				$this->deleteFilterGroup($filter_group_id);
			}

			$this->moreFilterGroupCells($i, $j, $data, $filter_group);
			$this->storeFilterGroupIntoDatabase($filter_group, $languages);
		}
	}

	/**
	 * Filters Import
	 */
	protected function storeFilterIntoDatabase(&$filter, $languages): void {
		$filter_id = $filter['filter_id'];
		$filter_group_id = $filter['filter_group_id'];
		$sort_order = $filter['sort_order'];
		$names = $filter['names'];

		$this->db->query("INSERT INTO `" . DB_PREFIX . "filter` (`filter_id`,`filter_group_id`,`sort_order`) VALUES ($filter_id, $filter_group_id, $sort_order)");

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$name = isset($names[$language_code]) ? $this->db->escape($names[$language_code]) : '';

			$this->db->query("INSERT INTO `" . DB_PREFIX . "filter_description` (`filter_id`,`language_id`,`filter_group_id`,`name`) VALUES ($filter_id, $language_id, $filter_group_id, '$name')");
		}
	}

	protected function deleteFilters(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "filter_description`");
	}

	protected function deleteFilter($filter_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter` WHERE filter_id = '" . (int)$filter_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "filter_description` WHERE filter_id = '" . (int)$filter_id . "'");
	}

	protected function moreFilterCells($i, $j, $worksheet, &$filter): void {
		return;
	}

	protected function uploadFilters($reader, $incremental): void {
		$data = $reader->getSheetByName('Filters');

		if ($data === null) {
			return;
		}

		$languages = $this->getLanguages();

		if (!$incremental) {
			$this->deleteFilters();
		}

		$first_row = [];
		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$filter_id = trim($this->getCell($data, $i, $j++));

			if ($filter_id === '') {
				continue;
			}

			$filter_group_id = trim($this->getCell($data, $i, $j++));

			if ($filter_group_id === '') {
				continue;
			}

			$sort_order = $this->getCell($data, $i, $j++, '0');
			$names = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "name(")) {
				$language_code = substr($first_row[$j - 1], strlen("name("), strlen($first_row[$j - 1]) - strlen("name(") - 1);
				$name = $this->getCell($data, $i, $j++);
				$names[$language_code] = htmlspecialchars((string)$name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$filter = [
				'filter_id'       => $filter_id,
				'filter_group_id' => $filter_group_id,
				'sort_order'      => $sort_order,
				'names'           => $names,
			];

			if ($incremental) {
				$this->deleteFilter($filter_id);
			}

			$this->moreFilterCells($i, $j, $data, $filter);
			$this->storeFilterIntoDatabase($filter, $languages);
		}
	}

	/**
	 * Fields Import
	 */
	protected function storeFieldIntoDatabase(&$field, $languages): void {
		$field_id = $field['field_id'];
		$sort_order = $field['sort_order'];
		$status = $field['status'];
		$titles = $field['titles'];
		$descriptions = $field['descriptions'];

		$this->db->query("INSERT INTO `" . DB_PREFIX . "field` (`field_id`,`sort_order`,`status`) VALUES ($field_id, $sort_order, $status)");

		foreach ($languages as $language) {
			$language_code = $language['code'];
			$language_id = $language['language_id'];
			$title = isset($titles[$language_code]) ? $this->db->escape($titles[$language_code]) : '';
			$description = isset($descriptions[$language_code]) ? $this->db->escape($descriptions[$language_code]) : '';

			$this->db->query("INSERT INTO `" . DB_PREFIX . "field_description` (`field_id`,`language_id`,`title`, `description`) VALUES ($field_id, $language_id, '$title', '$description')");
		}
	}

	protected function deleteFields(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "field`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "field_description`");
	}

	protected function deleteField($field_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "field` WHERE field_id = '" . (int)$field_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "field_description` WHERE field_id = '" . (int)$field_id . "'");
	}

	protected function moreFieldCells($i, $j, $worksheet, &$field): void {
		return;
	}

	protected function uploadFields($reader, $incremental): void {
		$data = $reader->getSheetByName('Fields');

		if ($data === null) {
			return;
		}

		$languages = $this->getLanguages();

		if (!$incremental) {
			$this->deleteFields();
		}

		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$field_id = trim($this->getCell($data, $i, $j++));

			if ($field_id === '') {
				continue;
			}

			$sort_order = $this->getCell($data, $i, $j++);
			$status = $this->getCell($data, $i, $j++);
			$titles = [];

			while ($this->startsWith($first_row[$j - 1], "title(")) {
				$language_code = substr($first_row[$j - 1], strlen("title("), strlen($first_row[$j - 1]) - strlen("title(") - 1);
				$title = $this->getCell($data, $i, $j++);
				$titles[$language_code] = htmlspecialchars((string)$title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$descriptions_data = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "description(")) {
				$language_code = substr($first_row[$j - 1], strlen("description("), strlen($first_row[$j - 1]) - strlen("description(") - 1);
				$description = $this->getCell($data, $i, $j++);
				$descriptions_data[$language_code] = htmlspecialchars((string)$description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$field = [
				'field_id'     => $field_id,
				'sort_order'   => $sort_order,
				'status'       => $status,
				'titles'       => $titles,
				'descriptions' => $descriptions_data,
			];

			if ($incremental) {
				$this->deleteField($field_id);
			}

			$this->moreFieldCells($i, $j, $data, $field);
			$this->storeFieldIntoDatabase($field, $languages);
		}
	}

	/**
	 * Palettes Import
	 */
	protected function storePaletteIntoDatabase(&$palette, $languages): void {
		$palette_color_id = $palette['palette_color_id'];
		$palette_id = $palette['palette_id'];
		$name = $palette['name'];
		$color = $palette['color'];
		$skin = $palette['skin'];
		$titles = $palette['titles'];

		$palette_color_ids = $this->getPaletteColorIds();

		if (in_array($palette_color_id, $palette_color_ids)) {
			$this->db->query("UPDATE `" . DB_PREFIX . "palette_color` SET palette_id = '" . (int)$palette_id . "', `color` = '" . $this->db->escape($color) . "', skin = '" . $this->db->escape($skin) . "' WHERE palette_color_id = '" . (int)$palette_color_id . "'");

			foreach ($languages as $language) {
				$language_code = $language['code'];
				$language_id = $language['language_id'];
				$title = isset($titles[$language_code]) ? $this->db->escape($titles[$language_code]) : '';

				$this->db->query("UPDATE `" . DB_PREFIX . "palette_color_description` SET language_id = '" . (int)$language_id . "', palette_id = '" . (int)$palette_id . "', `title` = '" . $this->db->escape($title) . "' WHERE palette_color_id = '" . (int)$palette_color_id . "'");
			}
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "palette_color` SET palette_color_id = '" . (int)$palette_color_id . "', palette_id = '" . (int)$palette_id . "', `color` = '" . $this->db->escape($color) . "', skin = '" . $this->db->escape($skin) . "'");

			foreach ($languages as $language) {
				$language_code = $language['code'];
				$language_id = $language['language_id'];
				$title = isset($titles[$language_code]) ? $this->db->escape($titles[$language_code]) : '';

				$this->db->query("INSERT INTO `" . DB_PREFIX . "palette_color_description` SET palette_color_id = '" . (int)$palette_color_id . "', language_id = '" . (int)$language_id . "', palette_id = '" . (int)$palette_id . "', `title` = '" . $title . "'");
			}
		}

		$palette_ids = $this->getAvailablePaletteIds();

		if (in_array($palette_id, $palette_ids)) {
			$this->db->query("UPDATE `" . DB_PREFIX . "palette` SET `name` = '" . $this->db->escape($name) . "' WHERE palette_id = '" . (int)$palette_id . "'");
		} else {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "palette` WHERE palette_id = '" . (int)$palette_id . "'");
			$this->db->query("INSERT INTO `" . DB_PREFIX . "palette` (`palette_id`,`name`) VALUES ($palette_id, '$name')");
		}
	}

	protected function getPaletteColorIds(): array {
		$palette_color_ids = [];

		$palette_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "palette_color`");

		if ($palette_query->rows) {
			$palette_color_ids = $palette_query->rows;
		}

		return $palette_color_ids;
	}

	protected function getAvailablePaletteIds(): array {
		$palette_ids = [];

		$palette_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "palette`");

		if ($palette_query->rows) {
			$palette_ids = $palette_query->rows;
		}

		return $palette_ids;
	}

	protected function deletePalettes(): void {
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "palette_color`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "palette_color_description`");
		$this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "palette`");
	}

	protected function deletePalette(int $palette_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "palette_color` WHERE palette_id = '" . (int)$palette_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "palette_color_description` WHERE palette_id = '" . (int)$palette_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "palette` WHERE palette_id = '" . (int)$palette_id . "'");
	}

	protected function morePaletteCells($i, $j, $worksheet, &$palette): void {
		return;
	}

	protected function uploadPalettes($reader, $incremental): void {
		$data = $reader->getSheetByName('Palettes');

		if ($data === null) {
			return;
		}

		$languages = $this->getLanguages();
		$incremental = false; // Not incremental at this time

		if (!$incremental) {
			$this->deletePalettes();
		}

		$first_row = [];

		$k = $data->getHighestRow();

		for ($i = 0; $i < $k; $i++) {
			if ($i === 0) {
				$max_col = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

				for ($j = 1; $j <= $max_col; $j++) {
					$first_row[] = $this->getCell($data, $i, $j);
				}

				continue;
			}

			$j = 1;
			$palette_color_id = trim($this->getCell($data, $i, $j++));

			if ($palette_color_id === '') {
				continue;
			}

			$palette_id = trim($this->getCell($data, $i, $j++));

			if ($palette_id === '') {
				continue;
			}

			$name = $this->getCell($data, $i, $j++);
			$color = $this->getCell($data, $i, $j++);
			$skin = $this->getCell($data, $i, $j++);

			$titles = [];

			while (($j <= $max_col) && $this->startsWith($first_row[$j - 1], "title(")) {
				$language_code = substr($first_row[$j - 1], strlen("title("), strlen($first_row[$j - 1]) - strlen("title(") - 1);
				$title = $this->getCell($data, $i, $j++);
				$titles[$language_code] = htmlspecialchars((string)$title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			}

			$palette = [
				'palette_color_id' => $palette_color_id,
				'palette_id'       => $palette_id,
				'name'             => $name,
				'color'            => $color,
				'skin'             => $skin,
				'titles'           => $titles,
			];

			if ($incremental) {
				$this->deletePalette($palette_id);
			}

			$this->morePaletteCells($i, $j, $data, $palette);
			$this->storePaletteIntoDatabase($palette, $languages);
		}
	}

	/**
	 * Validation
	 */
	protected function validateHeading(&$data, &$expected, &$multilingual): bool {
		$heading = [];

		$k = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

		for ($j = 1; $j <= $k; $j++) {
			$entry = $this->getCell($data, 0, $j);
			$bracket_start = strripos($entry, '(', 0);

			if ($bracket_start === false) {
				if (in_array($entry, $multilingual)) {
					return false;
				}

				$heading[] = strtolower($entry);
			} else {
				$name = strtolower(substr($entry, 0, $bracket_start));

				if (!in_array($name, $multilingual)) {
					return false;
				}

				$bracket_end = strripos($entry, ')', $bracket_start);

				if ($bracket_end <= $bracket_start || $bracket_end + 1 !== strlen($entry)) {
					return false;
				}

				if (count($heading) <= 0 || $heading[count($heading) - 1] !== $name) {
					$heading[] = $name;
				}
			}
		}

		for ($i = 0; $i < count($expected); $i++) {
			if (!isset($heading[$i]) || $heading[$i] !== $expected[$i]) {
				return false;
			}
		}

		return true;
	}

	protected function validateCustomers(&$reader): bool {
		$data = $reader->getSheetByName('Customers');

		if ($data === null) {
			return true;
		}

		$expected = ["customer_id", "customer_group", "store_id", "firstname", "lastname", "email", "telephone", "gender", "date_of_birth", "password", "salt", "cart", "wishlist", "newsletter", "address_id", "ip", "status", "approved", "token", "date_added"];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateAddresses(&$reader): bool {
		$data = $reader->getSheetByName('Addresses');

		if ($data === null) {
			return true;
		}

		$expected = ["customer_id", "firstname", "lastname", "company", "company_id", "tax_id", "address_1", "address_2", "city", "postcode", "zone", "country", "default"];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateCategories(&$reader): bool {
		$data = $reader->getSheetByName('Categories');

		if ($data === null) {
			return true;
		}

		$expected = ["category_id", "parent_id", "name", "description", "meta_description", "meta_keywords", "sort_order", "image_name", "date_added", "date_modified", "seo_keyword", "store_ids", "layout", "status"];
		$multilingual = ["name", "description", "meta_description", "meta_keywords"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateCategoryFilters(&$reader): bool {
		$data = $reader->getSheetByName('CategoryFilters');

		if ($data === null) {
			return true;
		}

		if (!$this->existFilter()) {
			throw new Exception($this->language->get('error_filter_not_supported'));
		}

		$use_fg_id = $this->config->get('export_import_settings_use_filter_group_id');
		$use_f_id = $this->config->get('export_import_settings_use_filter_id');

		$expected = ["category_id"];
		$expected[] = $use_fg_id ? "filter_group_id" : "filter_group";
		$expected[] = $use_f_id ? "filter_id" : "filter";

		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateProducts(&$reader): bool {
		$data = $reader->getSheetByName('Products');

		if ($data === null) {
			return true;
		}

		$expected = array_merge(
			["product_id", "name", "categories", "sku", "upc", "ean", "jan", "isbn", "mpn"],
			["location", "quantity", "model", "manufacturer_name", "image_name", "label_name", "video_code", "shipping", "price", "cost", "quote", "age_minimum", "points", "date_added"],
			["date_modified", "date_available", "palette_id", "weight", "weight_unit", "length", "width", "height", "length_unit", "status", "tax_class_id", "tax_local_rate_id", "seo_keyword"],
			["description", "meta_description", "meta_keywords", "stock_status_id", "store_ids", "layout", "related_ids", "location_ids", "tags", "sort_order", "subtract", "minimum", "viewed"]
		);

		$multilingual = ["name", "description", "meta_description", "meta_keywords", "tags"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateAdditionalImages(&$reader): bool {
		$data = $reader->getSheetByName('AdditionalImages');

		if ($data === null) {
			return true;
		}

		$expected = ["product_id", "image", "palette_color_id", "sort_order"];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateSpecials(&$reader): bool {
		$data = $reader->getSheetByName('Specials');

		if ($data === null) {
			return true;
		}

		$expected = ["product_id", "customer_group", "priority", "price", "date_start", "date_end"];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateDiscounts(&$reader): bool {
		$data = $reader->getSheetByName('Discounts');

		if ($data === null) {
			return true;
		}

		$expected = ["product_id", "customer_group", "quantity", "priority", "price", "date_start", "date_end"];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateRewards(&$reader): bool {
		$data = $reader->getSheetByName('Rewards');

		if ($data === null) {
			return true;
		}

		$expected = ["product_id", "customer_group", "points"];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateProductOptions(&$reader): bool {
		$data = $reader->getSheetByName('ProductOptions');

		if ($data === null) {
			return true;
		}

		$expected = ["product_id", $this->config->get('export_import_settings_use_option_id') ? "option_id" : "option", "default_option_value", "required"];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateProductOptionValues(&$reader): bool {
		$data = $reader->getSheetByName('ProductOptionValues');

		if ($data === null) {
			return true;
		}

		$use_o_id = $this->config->get('export_import_settings_use_option_id');
		$use_ov_id = $this->config->get('export_import_settings_use_option_value_id');

		$expected = [
			"product_id",
			$use_o_id ? "option_id" : "option",
			$use_ov_id ? "option_value_id" : "option_value",
			"quantity", "subtract", "price", "price_prefix", "points", "points_prefix", "weight", "weight_prefix"
		];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateProductColors(&$reader): bool {
		$data = $reader->getSheetByName('ProductColors');

		if ($data === null) {
			return true;
		}

		$expected = ["product_id", "product_color_id", "palette_color_id"];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateProductFields(&$reader): bool {
		$data = $reader->getSheetByName('ProductFields');

		if ($data === null) {
			return true;
		}

		if (!$this->existField()) {
			throw new Exception($this->language->get('error_field_not_supported'));
		}

		$expected = ["product_id", "field_id", "text"];
		$multilingual = ["text"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateProductAttributes(&$reader): bool {
		$data = $reader->getSheetByName('ProductAttributes');

		if ($data === null) {
			return true;
		}

		$use_ag_id = $this->config->get('export_import_settings_use_attribute_group_id');
		$use_a_id  = $this->config->get('export_import_settings_use_attribute_id');

		$expected = ["product_id", $use_ag_id ? "attribute_group_id" : "attribute_group", $use_a_id ? "attribute_id" : "attribute", "text"];
		$multilingual = ["text"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateProductFilters(&$reader): bool {
		$data = $reader->getSheetByName('ProductFilters');

		if ($data === null) {
			return true;
		}

		if (!$this->existFilter()) {
			throw new Exception($this->language->get('error_filter_not_supported'));
		}

		$use_fg_id = $this->config->get('export_import_settings_use_filter_group_id');
		$use_f_id = $this->config->get('export_import_settings_use_filter_id');

		$expected = ["product_id", $use_fg_id ? "filter_group_id" : "filter_group", $use_f_id ? "filter_id" : "filter"];
		$multilingual = [];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateOptions(&$reader): bool {
		$data = $reader->getSheetByName('Options');

		if ($data === null) {
			return true;
		}

		$expected = ["option_id", "type", "sort_order", "name"];
		$multilingual = ["name"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateOptionValues(&$reader): bool {
		$data = $reader->getSheetByName('OptionValues');

		if ($data === null) {
			return true;
		}

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "option_value` LIKE 'image'");
		$exist_image = ($query->num_rows > 0);

		$expected = $exist_image
			? ["option_value_id", "option_id", "image", "sort_order", "name"]
			: ["option_value_id", "option_id", "sort_order", "name"];
		$multilingual = ["name"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateAttributeGroups(&$reader): bool {
		$data = $reader->getSheetByName('AttributeGroups');

		if ($data === null) {
			return true;
		}

		$expected = ["attribute_group_id", "sort_order", "name"];
		$multilingual = ["name"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateAttributes(&$reader): bool {
		$data = $reader->getSheetByName('Attributes');

		if ($data === null) {
			return true;
		}

		$expected = ["attribute_id", "attribute_group_id", "sort_order", "name"];
		$multilingual = ["name"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateFilterGroups($reader): bool {
		$data = $reader->getSheetByName('FilterGroups');

		if ($data === null) {
			return true;
		}

		if (!$this->existFilter()) {
			throw new Exception($this->language->get('error_filter_not_supported'));
		}

		$expected = ["filter_group_id", "sort_order", "name"];
		$multilingual = ["name"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateFilters(&$reader): bool {
		$data = $reader->getSheetByName('Filters');

		if ($data === null) {
			return true;
		}

		if (!$this->existFilter()) {
			throw new Exception($this->language->get('error_filter_not_supported'));
		}

		$expected = ["filter_id", "filter_group_id", "sort_order", "name"];
		$multilingual = ["name"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateFields(&$reader): bool {
		$data = $reader->getSheetByName('Fields');

		if ($data === null) {
			return true;
		}

		if (!$this->existField()) {
			throw new Exception($this->language->get('error_field_not_supported'));
		}

		$expected = ["field_id", "sort_order", "status", "title", "description"];
		$multilingual = ["title", "description"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validatePalettes(&$reader): bool {
		$data = $reader->getSheetByName('Palettes');

		if ($data === null) {
			return true;
		}

		$expected = ["palette_color_id", "palette_id", "name", "color", "skin", "title"];
		$multilingual = ["title"];

		return $this->validateHeading($data, $expected, $multilingual);
	}

	protected function validateCategoryIdColumns(&$reader): bool {
		$data = $reader->getSheetByName('Categories');

		if ($data === null) {
			return true;
		}

		$ok = true;
		$previous_category_id = 0;
		$has_missing = false;
		$category_ids = [];

		$k = $data->getHighestRow();

		for ($i = 1; $i < $k; $i++) {
			$category_id = $this->getCell($data, $i, 1);

			if ($category_id === "") {
				if (!$has_missing) {
					$this->log->write(str_replace('%1', 'Categories', $this->language->get('error_missing_category_id')));
					$has_missing = true;
				}
				$ok = false;
				continue;
			}

			if (!$this->isInteger($category_id)) {
				$this->log->write(str_replace(['%1', '%2'], ['Categories', $category_id], $this->language->get('error_invalid_category_id')));
				$ok = false;
				continue;
			}

			if (in_array($category_id, $category_ids)) {
				$this->log->write(str_replace(['%1', '%2'], ['Categories', $category_id], $this->language->get('error_duplicate_category_id')));
				$ok = false;
			}

			$category_ids[] = $category_id;

			if ($category_id < $previous_category_id) {
				$this->log->write(str_replace(['%1', '%2'], ['Categories', $category_id], $this->language->get('error_wrong_order_category_id')));
				$ok = false;
			}

			$previous_category_id = $category_id;
		}

		foreach (['CategoryFilters'] as $worksheet) {
			$data = $reader->getSheetByName($worksheet);

			if ($data === null) {
				continue;
			}

			$previous_category_id = 0;
			$has_missing = false;
			$unlisted = [];

			$k = $data->getHighestRow();

			for ($i = 1; $i < $k; $i++) {
				$category_id = $this->getCell($data, $i, 1);

				if ($category_id === "") {
					if (!$has_missing) {
						$this->log->write(str_replace('%1', $worksheet, $this->language->get('error_missing_category_id')));
						$has_missing = true;
					}
					$ok = false;
					continue;
				}

				if (!$this->isInteger($category_id)) {
					$this->log->write(str_replace(['%1', '%2'], [$worksheet, $category_id], $this->language->get('error_invalid_category_id')));
					$ok = false;
					continue;
				}

				if (!in_array($category_id, $category_ids) && !in_array($category_id, $unlisted)) {
					$unlisted[] = $category_id;
					$this->log->write(str_replace(['%1', '%2'], [$worksheet, $category_id], $this->language->get('error_unlisted_category_id')));
					$ok = false;
				}

				if ($category_id < $previous_category_id) {
					$this->log->write(str_replace(['%1', '%2'], [$worksheet, $category_id], $this->language->get('error_wrong_order_category_id')));
					$ok = false;
				}

				$previous_category_id = $category_id;
			}
		}

		return $ok;
	}

	protected function validateProductIdColumns(&$reader): bool {
		$data = $reader->getSheetByName('Products');

		if ($data === null) {
			return true;
		}

		$ok = true;
		$has_missing = false;
		$product_ids = [];

		$k = $data->getHighestRow();

		for ($i = 1; $i < $k; $i++) {
			$product_id = trim($this->getCell($data, $i, 1));

			if ($product_id === "") {
				if (!$has_missing) {
					$this->log->write(str_replace('%1', 'Products', $this->language->get('error_missing_product_id')));
					$has_missing = true;
				}
				$ok = false;
				continue;
			}

			if (!ctype_digit($product_id)) {
				$this->log->write(str_replace(['%1', '%2'], ['Products', $product_id], $this->language->get('error_invalid_product_id')));
				$ok = false;
				continue;
			}

			if (in_array($product_id, $product_ids)) {
				$this->log->write(str_replace(['%1', '%2'], ['Products', $product_id], $this->language->get('error_duplicate_product_id')));
				$ok = false;
				continue;
			}

			$product_ids[] = $product_id;
		}

		$worksheets = ['AdditionalImages', 'Specials', 'Discounts', 'Rewards', 'ProductOptions', 'ProductOptionValues', 'ProductColors', 'ProductFields', 'ProductAttributes'];

		foreach ($worksheets as $worksheet) {
			$data = $reader->getSheetByName($worksheet);

			if ($data === null) {
				continue;
			}

			$has_missing = false;
			$unlisted = [];

			$k = $data->getHighestRow();

			for ($i = 1; $i < $k; $i++) {
				$product_id = trim($this->getCell($data, $i, 1));

				if ($product_id === "") {
					if (!$has_missing) {
						$this->log->write(str_replace('%1', $worksheet, $this->language->get('error_missing_product_id')));
						$has_missing = true;
					}
					$ok = false;
					continue;
				}

				if (!ctype_digit($product_id)) {
					$this->log->write(str_replace(['%1', '%2'], [$worksheet, $product_id], $this->language->get('error_invalid_product_id')));
					$ok = false;
					continue;
				}

				if (!in_array($product_id, $product_ids) && !in_array($product_id, $unlisted)) {
					$unlisted[] = $product_id;
					$this->log->write(str_replace(['%1', '%2'], [$worksheet, $product_id], $this->language->get('error_unlisted_product_id')));
					$ok = false;
					continue;
				}
			}
		}

		return $ok;
	}

	protected function validateCustomerIdColumns(&$reader): bool {
		$data = $reader->getSheetByName('Customers');

		if ($data === null) {
			return true;
		}

		$ok = true;
		$previous_customer_id = 0;
		$has_missing = false;
		$customer_ids = [];

		$k = $data->getHighestRow();

		for ($i = 1; $i < $k; $i++) {
			$customer_id = $this->getCell($data, $i, 1);

			if ($customer_id === "") {
				if (!$has_missing) {
					$this->log->write(str_replace('%1', 'Customers', $this->language->get('error_missing_customer_id')));
					$has_missing = true;
				}
				$ok = false;
				continue;
			}

			if (!$this->isInteger($customer_id)) {
				$this->log->write(str_replace(['%1', '%2'], ['Customers', $customer_id], $this->language->get('error_invalid_customer_id')));
				$ok = false;
				continue;
			}

			if (in_array($customer_id, $customer_ids)) {
				$this->log->write(str_replace(['%1', '%2'], ['Customers', $customer_id], $this->language->get('error_duplicate_customer_id')));
				$ok = false;
			}

			$customer_ids[] = $customer_id;

			if ($customer_id < $previous_customer_id) {
				$this->log->write(str_replace(['%1', '%2'], ['Customers', $customer_id], $this->language->get('error_wrong_order_customer_id')));
				$ok = false;
			}

			$previous_customer_id = $customer_id;
		}

		foreach (['Addresses'] as $worksheet) {
			$data = $reader->getSheetByName($worksheet);

			if ($data === null) {
				continue;
			}

			$previous_customer_id = 0;
			$has_missing = false;
			$unlisted = [];

			$k = $data->getHighestRow();

			for ($i = 1; $i < $k; $i++) {
				$customer_id = $this->getCell($data, $i, 1);

				if ($customer_id === "") {
					if (!$has_missing) {
						$this->log->write(str_replace('%1', $worksheet, $this->language->get('error_missing_customer_id')));
						$has_missing = true;
					}
					$ok = false;
					continue;
				}

				if (!$this->isInteger($customer_id)) {
					$this->log->write(str_replace(['%1', '%2'], [$worksheet, $customer_id], $this->language->get('error_invalid_customer_id')));
					$ok = false;
					continue;
				}

				if (!in_array($customer_id, $customer_ids) && !in_array($customer_id, $unlisted)) {
					$unlisted[] = $customer_id;
					$this->log->write(str_replace(['%1', '%2'], [$worksheet, $customer_id], $this->language->get('error_unlisted_customer_id')));
				}

				$ok = false;

				if ($customer_id < $previous_customer_id) {
					$this->log->write(str_replace(['%1', '%2'], [$worksheet, $customer_id], $this->language->get('error_wrong_order_customer_id')));
					$ok = false;
				}

				$previous_customer_id = $customer_id;
			}
		}

		return $ok;
	}

	protected function validateAddressCountriesAndZones(&$reader): bool {
		$data = $reader->getSheetByName('Addresses');

		if ($data === null) {
			return true;
		}

		$ok = true;
		$country_col = 0;
		$zone_col = 0;

		$k = PHPExcel_Cell::columnIndexFromString($data->getHighestColumn());

		for ($j = 1; $j <= $k; $j++) {
			$entry = $this->getCell($data, 0, $j);

			if ($entry === 'country') {
				$country_col = $j;
			} elseif ($entry === 'zone') {
				$zone_col = $j;
			}
		}

		if ($country_col === 0) {
			$this->log->write(str_replace('%1', 'Addresses', $this->language->get('error_missing_country_col')));
			$ok = false;
		}

		if ($zone_col === 0) {
			$this->log->write(str_replace('%1', 'Addresses', $this->language->get('error_missing_zone_col')));
			$ok = false;
		}

		if (!$ok) {
			return false;
		}

		$available_country_ids = $this->getAvailableCountryIds();
		$available_zone_ids = $this->getAvailableZoneIds();
		$undefined_countries = [];
		$undefined_zones = [];

		$k = $data->getHighestRow();

		for ($i = 1; $i < $k; $i++) {
			$country = $this->getCell($data, $i, $country_col);
			$zone = $this->getCell($data, $i, $zone_col);

			if (!isset($available_country_ids[$country])) {
				$country = html_entity_decode($country, ENT_QUOTES, 'UTF-8');

				if (!isset($available_country_ids[$country])) {
					if (!in_array($country, $undefined_countries)) {
						$undefined_countries[] = $country;
						$this->log->write(str_replace(['%1', '%2'], [$country, 'Addresses'], $this->language->get('error_undefined_country')));
						$ok = false;
					}
					continue;
				}
			}

			if ($zone !== '') {
				if (!isset($available_zone_ids[$country][$zone])) {
					$zone = html_entity_decode($zone, ENT_QUOTES, 'UTF-8');
				}

				if (!isset($available_zone_ids[$country][$zone])) {
					$zone = htmlentities($zone, ENT_NOQUOTES, 'UTF-8');
				}

				if (!isset($available_zone_ids[$country][$zone])) {
					$zone = html_entity_decode($zone, ENT_QUOTES, 'UTF-8');
					$zone = htmlentities($zone, ENT_QUOTES, 'UTF-8');
				}

				if (!isset($available_zone_ids[$country][$zone])) {
					$zone = html_entity_decode($zone, ENT_QUOTES, 'UTF-8');
					$zone = htmlentities($zone, ENT_NOQUOTES, 'UTF-8');
					$zone = str_replace("'", "&#39;", $zone);
				}

				if (!isset($available_zone_ids[$country][$zone])) {
					if (!isset($undefined_zones[$country])) {
						$undefined_zones[$country] = [];
					}

					if (!in_array($zone, $undefined_zones[$country])) {
						$undefined_zones[$country][] = $zone;
						$this->log->write(str_replace(['%1', '%2', '%3'], [$country, $zone, 'Addresses'], $this->language->get('error_undefined_zone')));
						$ok = false;
					}
				}
			}
		}

		return $ok;
	}

	protected function validateCustomerGroupColumns(&$reader): bool {
		$ok = true;
		$customer_groups = [];
		$customer_group_ids = $this->getCustomerGroupIds();

		foreach (['Specials', 'Discounts', 'Rewards', 'Customers'] as $worksheet) {
			$data = $reader->getSheetByName($worksheet);

			if ($data === null) {
				continue;
			}

			$has_missing = false;

			$k = $data->getHighestRow();

			for ($i = 1; $i < $k; $i++) {
				$customer_group = trim($this->getCell($data, $i, 2));

				if ($customer_group === "") {
					if (!$has_missing) {
						$this->log->write(str_replace('%1', $worksheet, $this->language->get('error_missing_customer_group')));
						$has_missing = true;
					}
					$ok = false;
					continue;
				}

				if (!in_array($customer_group, $customer_groups)) {
					if (!isset($customer_group_ids[$customer_group])) {
						$this->log->write(str_replace(['%1', '%2'], [$worksheet, $customer_group], $this->language->get('error_invalid_customer_group')));
						$ok = false;
						continue;
					}

					$customer_groups[] = $customer_group;
				}
			}
		}

		return $ok;
	}

	protected function validateOptionColumns(&$reader): bool {
		$ok = true;
		$use_option_id = $this->config->get('export_import_settings_use_option_id');
		$use_option_value_id = $this->config->get('export_import_settings_use_option_value_id');
		$language_id = $this->getDefaultLanguageId();

		$sql  = "SELECT od.option_id, od.name AS option_name, ovd.option_value_id, ovd.name AS option_value_name";
		$sql .= " FROM `" . DB_PREFIX . "option_description` od";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ovd.option_id = od.option_id) AND ovd.language_id = '" . (int)$language_id . "'";
		$sql .= " WHERE od.language_id = '" . (int)$language_id . "'";

		$options = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$key = $use_option_id ? $row['option_id'] : htmlspecialchars_decode($row['option_name']);

			if (!isset($options[$key])) {
				$options[$key] = [];
			}

			$val = $use_option_value_id ? $row['option_value_id'] : htmlspecialchars_decode($row['option_value_name']);

			if (!is_null($val)) {
				$options[$key][$val] = true;
			}
		}

		$product_options = [];
		$data = $reader->getSheetByName('ProductOptions');

		if ($data === null) {
			return $ok;
		}

		$has_missing = false;

		$k = $data->getHighestRow();

		for ($i = 1; $i < $k; $i++) {
			$product_id = trim($this->getCell($data, $i, 1));

			if ($product_id === "") {
				continue;
			}

			if ($use_option_id) {
				$option_key = trim($this->getCell($data, $i, 2));
				$err_missing = 'error_missing_option_id';
				$err_invalid = 'error_invalid_option_id';
			} else {
				$option_key = trim($this->getCell($data, $i, 2));
				$err_missing = 'error_missing_option_name';
				$err_invalid = 'error_invalid_option_name';
			}

			if ($option_key === "") {
				if (!$has_missing) {
					$this->log->write(str_replace('%1', 'ProductOptions', $this->language->get($err_missing)));
					$has_missing = true;
				}
				$ok = false;
				continue;
			}

			if (!isset($options[$option_key])) {
				$this->log->write(str_replace(['%1', '%2'], ['ProductOptions', $option_key], $this->language->get($err_invalid)));
				$ok = false;
				continue;
			}

			$product_options[$product_id][$option_key] = true;
		}

		$data = $reader->getSheetByName('ProductOptionValues');

		if ($data === null) {
			return $ok;
		}

		$has_missing_o = false;
		$has_missing_v = false;

		$k = $data->getHighestRow();

		for ($i = 1; $i < $k; $i++) {
			$product_id = trim($this->getCell($data, $i, 1));

			if ($product_id === "") {
				continue;
			}

			$option_key = trim($this->getCell($data, $i, 2));

			if ($option_key === "") {
				if (!$has_missing_o) {
					$this->log->write(str_replace('%1', 'ProductOptionValues', $this->language->get($use_option_id ? 'error_missing_option_id' : 'error_missing_option_name')));
					$has_missing_o = true;
				}
				$ok = false;
				continue;
			}

			if (!isset($options[$option_key])) {
				$this->log->write(str_replace(['%1', '%2'], ['ProductOptionValues', $option_key], $this->language->get($use_option_id ? 'error_invalid_option_id' : 'error_invalid_option_name')));
				$ok = false;
				continue;
			}

			if (!isset($product_options[$product_id][$option_key])) {
				$this->log->write(str_replace(['%1', '%2', '%3', '%4'], ['ProductOptionValues', $product_id, $option_key, 'ProductOptions'], $this->language->get($use_option_id ? 'error_invalid_product_id_option_id' : 'error_invalid_product_id_option_name')));
				$ok = false;
				continue;
			}

			$value_key = trim($this->getCell($data, $i, 3));

			if ($value_key === "") {
				if (!$has_missing_v) {
					$this->log->write(str_replace('%1', 'ProductOptionValues', $this->language->get($use_option_value_id ? 'error_missing_option_value_id' : 'error_missing_option_value_name')));
					$has_missing_v = true;
				}
				$ok = false;
				continue;
			}

			if (!isset($options[$option_key][$value_key])) {
				$this->log->write(str_replace(['%1', '%2', '%3'], ['ProductOptionValues', $option_key, $value_key], $this->language->get($use_option_id ? ($use_option_value_id ? 'error_invalid_option_id_option_value_id' : 'error_invalid_option_id_option_value_name') : ($use_option_value_id ? 'error_invalid_option_name_option_value_id' : 'error_invalid_option_name_option_value_name'))));
				$ok = false;
				continue;
			}
		}

		return $ok;
	}

	protected function validateAttributeColumns(&$reader): bool {
		$ok = true;
		$use_ag_id = $this->config->get('export_import_settings_use_attribute_group_id');
		$use_a_id  = $this->config->get('export_import_settings_use_attribute_id');
		$language_id = $this->getDefaultLanguageId();

		$sql = "SELECT agd.attribute_group_id, agd.name AS `attribute_group_name`, ad.attribute_id, ad.name AS `attribute_name`";
		$sql .= " FROM `" . DB_PREFIX . "attribute_group_description` agd";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "attribute` a ON (a.attribute_group_id = agd.attribute_group_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (ad.attribute_id = a.attribute_id)";
		$sql .= " WHERE ad.language_id = '" . (int)$language_id . "'";
		$sql .= " AND agd.language_id = '" . (int)$language_id . "'";

		$attribute_groups = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$group_key = $use_ag_id ? $row['attribute_group_id'] : htmlspecialchars_decode($row['attribute_group_name']);

			if (!isset($attribute_groups[$group_key])) {
				$attribute_groups[$group_key] = [];
			}

			$attr_key = $use_a_id ? $row['attribute_id'] : htmlspecialchars_decode($row['attribute_name']);

			if (!is_null($attr_key)) {
				$attribute_groups[$group_key][$attr_key] = true;
			}
		}

		$data = $reader->getSheetByName('ProductAttributes');

		if ($data === null) {
			return $ok;
		}

		$has_missing_g = false;
		$has_missing_a = false;

		$k = $data->getHighestRow();

		for ($i = 1; $i < $k; $i++) {
			$product_id = trim($this->getCell($data, $i, 1));

			if ($product_id === "") {
				continue;
			}

			$group_key = trim($this->getCell($data, $i, 2));

			if ($group_key === "") {
				if (!$has_missing_g) {
					$this->log->write(str_replace('%1', 'ProductAttributes', $this->language->get($use_ag_id ? 'error_missing_attribute_group_id' : 'error_missing_attribute_group_name')));
					$has_missing_g = true;
				}
				$ok = false;
				continue;
			}

			if (!isset($attribute_groups[$group_key])) {
				$this->log->write(str_replace(['%1', '%2'], ['ProductAttributes', $group_key], $this->language->get($use_ag_id ? 'error_invalid_attribute_group_id' : 'error_invalid_attribute_group_name')));
				$ok = false;
				continue;
			}

			$attr_key = trim($this->getCell($data, $i, 3));

			if ($attr_key === "") {
				if (!$has_missing_a) {
					$this->log->write(str_replace('%1', 'ProductAttributes', $this->language->get($use_a_id ? 'error_missing_attribute_id' : 'error_missing_attribute_name')));
					$has_missing_a = true;
				}
				$ok = false;
				continue;
			}

			if (!isset($attribute_groups[$group_key][$attr_key])) {
				$this->log->write(str_replace(['%1', '%2', '%3'], ['ProductAttributes', $group_key, $attr_key], $this->language->get($use_ag_id ? ($use_a_id ? 'error_invalid_attribute_group_id_attribute_id' : 'error_invalid_attribute_group_id_attribute_name') : ($use_a_id ? 'error_invalid_attribute_group_name_attribute_id' : 'error_invalid_attribute_group_name_attribute_name'))));
				$ok = false;
				continue;
			}
		}

		return $ok;
	}

	protected function validateFilterColumns(&$reader): bool {
		$ok = true;
		$use_fg_id = $this->config->get('export_import_settings_use_filter_group_id');
		$use_f_id = $this->config->get('export_import_settings_use_filter_id');
		$language_id = $this->getDefaultLanguageId();

		$sql = "SELECT fgd.filter_group_id, fgd.name AS `filter_group_name`, fd.filter_id, fd.name AS `filter_name`";
		$sql .= " FROM `" . DB_PREFIX . "filter_group_description` fgd";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "filter` f ON (f.filter_group_id = fgd.filter_group_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "filter_description` fd ON (fd.filter_id = f.filter_id)";
		$sql .= " WHERE fd.language_id = '" . (int)$language_id . "'";
		$sql .= " AND fgd.language_id = '" . (int)$language_id . "'";

		$filter_groups = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$group_key = $use_fg_id ? $row['filter_group_id'] : htmlspecialchars_decode($row['filter_group_name']);

			if (!isset($filter_groups[$group_key])) {
				$filter_groups[$group_key] = [];
			}

			$filter_key = $use_f_id ? $row['filter_id'] : htmlspecialchars_decode($row['filter_name']);

			if (!is_null($filter_key)) {
				$filter_groups[$group_key][$filter_key] = true;
			}
		}

		foreach (['ProductFilters', 'CategoryFilters'] as $worksheet_name) {
			$data = $reader->getSheetByName('ProductFilters');

			if ($data === null) {
				return $ok;
			}

			$has_missing_g = false;
			$has_missing_f = false;

			$k = $data->getHighestRow();

			for ($i = 1; $i < $k; $i++) {
				$id = trim($this->getCell($data, $i, 1));

				if ($id === "") {
					continue;
				}

				$group_key = trim($this->getCell($data, $i, 2));

				if ($group_key === "") {
					if (!$has_missing_g) {
						$this->log->write(str_replace('%1', $worksheet_name, $this->language->get($use_fg_id ? 'error_missing_filter_group_id' : 'error_missing_filter_group_name')));
						$has_missing_g = true;
					}
					$ok = false;
					continue;
				}

				if (!isset($filter_groups[$group_key])) {
					$this->log->write(str_replace(['%1', '%2'], [$worksheet_name, $group_key], $this->language->get($use_fg_id ? 'error_invalid_filter_group_id' : 'error_invalid_filter_group_name')));
					$ok = false;
					continue;
				}

				$filter_key = trim($this->getCell($data, $i, 3));

				if ($filter_key === "") {
					if (!$has_missing_f) {
						$this->log->write(str_replace('%1', $worksheet_name, $this->language->get($use_f_id ? 'error_missing_filter_id' : 'error_missing_filter_name')));
						$has_missing_f = true;
					}
					$ok = false;
					continue;
				}

				if (!isset($filter_groups[$group_key][$filter_key])) {
					$this->log->write(str_replace(['%1', '%2', '%3'], [$worksheet_name, $group_key, $filter_key], $this->language->get($use_fg_id ? ($use_f_id ? 'error_invalid_filter_group_id_filter_id' : 'error_invalid_filter_group_id_filter_name') : ($use_f_id ? 'error_invalid_filter_group_name_filter_id' : 'error_invalid_filter_group_name_filter_name'))));
					$ok = false;
					continue;
				}
			}
		}

		return $ok;
	}

	protected function validateFieldColumns(&$reader): bool {
		$ok = true;
		$language_id = $this->getDefaultLanguageId();

		$sql = "SELECT *, fd.field_id AS `field_id` FROM `" . DB_PREFIX . "field` f";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "field_description` fd ON (f.field_id = fd.field_id)";
		$sql .= " WHERE fd.language_id = '" . (int)$language_id . "'";

		$fields = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$fields[$row['field_id']] = [];
		}

		$data = $reader->getSheetByName('ProductFields');

		if ($data === null) {
			return $ok;
		}

		$has_missing = false;

		$k = $data->getHighestRow();

		for ($i = 1; $i < $k; $i++) {
			$field_id = trim($this->getCell($data, $i, 1));

			if ($field_id === "") {
				if (!$has_missing) {
					$this->log->write(str_replace('%1', 'ProductFields', $this->language->get('error_missing_field_id')));
					$has_missing = true;
				}
				$ok = false;
				continue;
			}
		}

		return $ok;
	}

	protected function validatePaletteColumns(&$reader): bool {
		$ok = true;
		$language_id = $this->getDefaultLanguageId();

		$sql = "SELECT *, pcd.palette_id AS `palette_id` FROM `" . DB_PREFIX . "palette` p";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "palette_color` pc ON (p.palette_id = pc.palette_id)";
		$sql .= " LEFT JOIN `" . DB_PREFIX . "palette_color_description` pcd ON (p.palette_id = pcd.palette_id)";
		$sql .= " WHERE pcd.language_id = '" . (int)$language_id . "'";

		$palettes = [];

		foreach ($this->db->query($sql)->rows as $row) {
			$palettes[$row['palette_color_id']] = [];
		}

		$data = $reader->getSheetByName('Palettes');

		if ($data === null) {
			return $ok;
		}

		$has_missing = false;

		$k = $data->getHighestRow();

		for ($i = 1; $i < $k; $i++) {
			$palette_color_id = trim($this->getCell($data, $i, 1));

			if ($palette_color_id === "") {
				if (!$has_missing) {
					$this->log->write(str_replace('%1', 'Palettes', $this->language->get('error_missing_palette_id')));
					$has_missing = true;
				}
				$ok = false;
				continue;
			}
		}

		return $ok;
	}

	protected function validateIncrementalOnly(&$reader, $incremental): bool {
		$ok = true;

		foreach (['Customers', 'Addresses'] as $worksheet) {
			$data = $reader->getSheetByName($worksheet);

			if ($data && !$incremental) {
				$this->log->write(str_replace('%1', $worksheet, $this->language->get('error_incremental_only')));
				$ok = false;
			}
		}

		return $ok;
	}

	protected function validateWorksheetNames(&$reader): bool {
		$allowed = ['Customers', 'Addresses', 'Categories', 'CategoryFilters', 'Products', 'AdditionalImages', 'Specials', 'Discounts', 'Rewards', 'ProductOptions', 'ProductOptionValues', 'ProductColors', 'ProductFields', 'ProductAttributes', 'ProductFilters', 'Options', 'OptionValues', 'AttributeGroups', 'Attributes', 'FilterGroups', 'Filters', 'Fields', 'Palettes'];

		foreach ($reader->getSheetNames() as $worksheet) {
			if (in_array($worksheet, $allowed)) {
				return true;
			}
		}

		return false;
	}

	protected function validateUpload($reader): bool {
		$ok = true;

		if (!$this->validateWorksheetNames($reader)) {
			$this->log->write($this->language->get('error_worksheets'));
			$ok = false;
		}

		$heading_checks = [
			'validateCustomers'           => 'error_customers_header',
			'validateAddresses'           => 'error_addresses_header',
			'validateCategories'          => 'error_categories_header',
			'validateCategoryFilters'     => 'error_category_filters_header',
			'validateProducts'            => 'error_products_header',
			'validateAdditionalImages'    => 'error_additional_images_header',
			'validateSpecials'            => 'error_specials_header',
			'validateDiscounts'           => 'error_discounts_header',
			'validateRewards'             => 'error_rewards_header',
			'validateProductOptions'      => 'error_product_options_header',
			'validateProductOptionValues' => 'error_product_option_values_header',
			'validateProductColors'       => 'error_product_colors_header',
			'validateProductFields'       => 'error_product_fields_header',
			'validateProductAttributes'   => 'error_product_attributes_header',
			'validateProductFilters'      => 'error_product_filters_header',
			'validateOptions'             => 'error_options_header',
			'validateOptionValues'        => 'error_option_values_header',
			'validateAttributeGroups'     => 'error_attribute_groups_header',
			'validateAttributes'          => 'error_attributes_header',
			'validateFilterGroups'        => 'error_filter_groups_header',
			'validateFilters'             => 'error_filters_header',
			'validateFields'              => 'error_fields_header',
			'validatePalettes'            => 'error_palettes_header',
		];

		foreach ($heading_checks as $method => $error_key) {
			if (!$this->$method($reader)) {
				$this->log->write($this->language->get($error_key));
				$ok = false;
			}
		}

		// Worksheet dependency ordering checks
		$names = $reader->getSheetNames();
		$exist = array_fill_keys(['customers', 'addresses', 'categories', 'category_filters', 'products', 'additional_images', 'specials', 'discounts', 'rewards', 'product_colors', 'product_fields', 'product_attributes', 'product_filters', 'product_options', 'product_option_values', 'options', 'option_values', 'attributes', 'attribute_groups', 'filters', 'filter_groups', 'fields', 'palettes'], false);

		$map = [
			'Customers' => 'customers', 'Addresses' => 'addresses',
			'Categories' => 'categories', 'CategoryFilters' => 'category_filters',
			'Products' => 'products', 'AdditionalImages' => 'additional_images',
			'Specials' => 'specials', 'Discounts' => 'discounts', 'Rewards' => 'rewards',
			'ProductColors' => 'product_colors', 'ProductFields' => 'product_fields',
			'ProductAttributes' => 'product_attributes', 'ProductFilters' => 'product_filters',
			'ProductOptions' => 'product_options', 'ProductOptionValues' => 'product_option_values',
			'Options' => 'options', 'OptionValues' => 'option_values',
			'Attributes' => 'attributes', 'AttributeGroups' => 'attribute_groups',
			'Filters' => 'filters', 'FilterGroups' => 'filter_groups',
			'Fields' => 'fields', 'Palettes' => 'palettes',
		];

		$deps = [
			'Addresses'           => ['customers', 'error_addresses'],
			'CategoryFilters'     => ['categories', 'error_category_filters'],
			'ProductOptions'      => ['products', 'error_product_options'],
			'ProductOptionValues' => ['product_options', 'error_product_option_values_2'],
			'AdditionalImages'    => ['products', 'error_additional_images'],
			'Specials'            => ['products', 'error_specials'],
			'Discounts'           => ['products', 'error_discounts'],
			'Rewards'             => ['products', 'error_rewards'],
			'ProductColors'       => ['products', 'error_product_colors'],
			'ProductFields'       => ['products', 'error_product_fields'],
			'ProductAttributes'   => ['products', 'error_product_attributes'],
			'ProductFilters'      => ['products', 'error_product_filters'],
			'OptionValues'        => ['options', 'error_option_values'],
			'Attributes'          => ['attribute_groups', 'error_attributes'],
			'Filters'             => ['filter_groups', 'error_filters'],
		];

		foreach ($names as $name) {
			if (isset($map[$name])) {
				$key = $map[$name];

				if (isset($deps[$name])) {
					[$dep_key, $err_key] = $deps[$name];

					if (!$exist[$dep_key]) {
						$this->log->write($this->language->get($err_key));
						$ok = false;
					}
				}

				$exist[$key] = true;
			}
		}

		// Post-ordering checks
		$post_checks = [
			['customers', 'addresses', 'error_addresses_2'],
			['product_options', 'product_option_values', 'error_product_option_values_3'],
			['attribute_groups', 'attributes', 'error_attributes_2'],
			['filter_groups', 'filters', 'error_filters_2'],
			['options', 'option_values', 'error_option_values_2'],
		];

		foreach ($post_checks as [$a, $b, $err]) {
			if ($exist[$a] && !$exist[$b]) {
				$this->log->write($this->language->get($err));
				$ok = false;
			}
		}

		if (!$ok) {
			return false;
		}

		// Column-level validation
		if (!$this->validateCustomerIdColumns($reader)) { $ok = false; }
		if (!$this->validateCustomerGroupColumns($reader)) { $ok = false; }
		if (!$this->validateAddressCountriesAndZones($reader)) { $ok = false; }
		if (!$this->validateProductIdColumns($reader)) { return false; }
		if (!$this->validateOptionColumns($reader)) { $ok = false; }
		if (!$this->validateAttributeColumns($reader)) { $ok = false; }

		if ($this->existFilter()) {
			if (!$this->validateFilterColumns($reader)) { $ok = false; }
		}

		if ($this->existField()) {
			if (!$this->validateFieldColumns($reader)) { $ok = false; }
		}

		if (!$this->validatePaletteColumns($reader)) { $ok = false; }

		return $ok;
	}

	/**
	 * Upload Entry Point
	 */
	public function upload($filename, $incremental = false) {
		global $registry;
		$registry = $this->registry;

		set_error_handler('error_handler_for_export_import', E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_export_import');

		try {
			$this->session->data['export_import_nochange'] = 1;

			$cwd = getcwd();
			chdir(DIR_SYSTEM . 'vendor');
			require_once('phpexcel/PHPExcel.php');
			chdir($cwd);

			if ($this->config->get('export_import_settings_use_import_cache')) {
				PHPExcel_Settings::setCacheStorageMethod(
					PHPExcel_CachedObjectStorageFactory::CACHETOPHPTEMP,
					['memoryCacheSize' => '16MB']
				);
			}

			$inputFileType = PHPExcel_IOFactory::identify($filename);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);

			$reader = $objReader->load($filename);

			if (!$this->validateUpload($reader)) {
				return false;
			}

			$this->clearCache();
			$this->session->data['export_import_nochange'] = 0;

			$available_customer_ids = [];
			$available_product_ids = [];
			$available_category_ids = [];

			$this->uploadCustomers($reader, $incremental, $available_customer_ids);
			$this->uploadAddresses($reader, $incremental, $available_customer_ids);
			$this->uploadCategories($reader, $incremental, $available_category_ids);
			$this->uploadCategoryFilters($reader, $incremental, $available_category_ids);
			$this->uploadProducts($reader, $incremental, $available_product_ids);
			$this->uploadAdditionalImages($reader, $incremental, $available_product_ids);
			$this->uploadSpecials($reader, $incremental, $available_product_ids);
			$this->uploadDiscounts($reader, $incremental, $available_product_ids);
			$this->uploadRewards($reader, $incremental, $available_product_ids);
			$this->uploadProductOptions($reader, $incremental, $available_product_ids);
			$this->uploadProductOptionValues($reader, $incremental, $available_product_ids);
			$this->uploadProductColors($reader, $incremental, $available_product_ids);
			$this->uploadProductFields($reader, $incremental, $available_product_ids);
			$this->uploadProductAttributes($reader, $incremental, $available_product_ids);
			$this->uploadProductFilters($reader, $incremental, $available_product_ids);
			$this->uploadOptions($reader, $incremental);
			$this->uploadOptionValues($reader, $incremental);
			$this->uploadAttributeGroups($reader, $incremental);
			$this->uploadAttributes($reader, $incremental);
			$this->uploadFilterGroups($reader, $incremental);
			$this->uploadFilters($reader, $incremental);
			$this->uploadFields($reader, $incremental);
			$this->uploadPalettes($reader, $incremental);

			return true;

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

			return false;
		}
	}
}
