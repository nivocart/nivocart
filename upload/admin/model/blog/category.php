<?php
class ModelBlogCategory extends Model {

	public function addCategory($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category` SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (isset($data['column']) ? (int)$data['column'] : 0) . "', blog_category_column = '" . (int)$data['blog_category_column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_added = NOW(), date_modified = NOW()");

		$blog_category_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_blog_category_id'] = $blog_category_id;

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_category` SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_description` SET blog_category_id = '" . (int)$blog_category_id . "', language_id = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_store` SET blog_category_id = '" . (int)$blog_category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_layout` SET blog_category_id = '" . (int)$blog_category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}

		if ($data['keyword']) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET `query` = 'blog_category_id=" . (int)$blog_category_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('blog_category');
	}

	public function editCategory($blog_category_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "blog_category` SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (isset($data['column']) ? (int)$data['column'] : 0) . "', blog_category_column = '" . (int)$data['blog_category_column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_category` SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_description` WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_description` SET blog_category_id = '" . (int)$blog_category_id . "', language_id = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_store` WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_store` SET blog_category_id = '" . (int)$blog_category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_layout` SET blog_category_id = '" . (int)$blog_category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'blog_category_id=" . (int)$blog_category_id. "'");

		if ($data['keyword']) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET `query` = 'blog_category_id=" . (int)$blog_category_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		$this->cache->delete('blog_category');
	}

	public function deleteCategory($blog_category_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category` WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_description` WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_store` WHERE blog_category_id = '" . (int)$blog_category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'blog_category_id=" . (int)$blog_category_id . "'");

		$query = $this->db->query("SELECT blog_category_id FROM `" . DB_PREFIX . "blog_category` WHERE parent_id = '" . (int)$blog_category_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['blog_category_id']);
		}

		$this->cache->delete('blog_category');
	}

	public function getCategory($blog_category_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'blog_category_id=" . (int)$blog_category_id . "') AS keyword FROM `" . DB_PREFIX . "blog_category` WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		return $query->row;
	}

	public function getTotalCategories($data = array()) {
		$query = $this->db->query("SELECT COUNT(DISTINCT(bc.blog_category_id)) AS `total` FROM `" . DB_PREFIX . "blog_category` bc LEFT JOIN `" . DB_PREFIX . "blog_category_description` bcd ON (bc.blog_category_id = bcd.blog_category_id) WHERE bcd.language_id='" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}

	public function getCategories($parent_id = 0) {
		$category_data = array();

		$sql = "SELECT * FROM `" . DB_PREFIX . "blog_category` bc LEFT JOIN `" . DB_PREFIX . "blog_category_description` bcd ON (bc.blog_category_id = bcd.blog_category_id) WHERE bc.parent_id = '" . (int)$parent_id . "' AND bcd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = array(
			'bcd.name',
			'bc.sort_order',
			'bc.status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY bcd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$category_data[] = array(
				'blog_category_id' => $result['blog_category_id'],
				'name'             => $this->getPath($result['blog_category_id'], $this->config->get('config_language_id')),
				'status'           => $result['status'],
				'sort_order'       => $result['sort_order']
			);

			$category_data = array_merge($category_data, $this->getCategories($result['blog_category_id']));
		}

		return $category_data;
	}

	public function getPath($blog_category_id) {
		$query = $this->db->query("SELECT bcd.name AS name, bc.parent_id AS parent_id FROM `" . DB_PREFIX . "blog_category` bc LEFT JOIN " . DB_PREFIX . "blog_category_description bcd ON (bc.blog_category_id = bcd.blog_category_id) WHERE bc.blog_category_id = '" . (int)$blog_category_id . "' AND bcd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY bc.sort_order, bcd.name ASC");

		if ($query->row['parent_id']) {
			return $this->getPath($query->row['parent_id'], $this->config->get('config_language_id')) . $this->language->get('text_separator') . $query->row['name'];
		} else {
			return $query->row['name'];
		}
	}

	public function getCategoryDescriptions($blog_category_id) {
		$category_description_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_description` WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description'],
				'description'      => $result['description']
			);
		}

		return $category_description_data;
	}

	public function getCategoryStores($blog_category_id) {
		$category_store_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_to_store` WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		foreach ($query->rows as $result) {
			$category_store_data[] = $result['store_id'];
		}

		return $category_store_data;
	}

	public function getCategoryLayouts($blog_category_id) {
		$category_layout_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		foreach ($query->rows as $result) {
			$category_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $category_layout_data;
	}

	public function getTotalArticleCategoryWise($blog_category_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_to_category` WHERE blog_category_id='" . (int)$blog_category_id . "'");

		return $query->num_rows;
	}
}
