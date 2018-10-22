<?php
class ModelBlogAuthor extends Model {

	public function addAuthor($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_author` SET `name` = '" . $this->db->escape($data['name']) . "', status='" . (int)$data['status'] . "', date_added = NOW(), date_modified = NOW()");

		$blog_author_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_blog_author_id'] = $blog_author_id;

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_author` SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE blog_author_id = '" . (int)$blog_author_id . "'");
		}

		foreach ($data['author_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_author_description` SET blog_author_id = '" . (int)$blog_author_id . "', language_id = '" . (int)$language_id . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		$this->cache->delete('blog_author');
	}

	public function editAuthor($blog_author_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "blog_author` SET `name` = '" . $this->db->escape($data['name']) . "', status='" . (int)$data['status'] . "', date_modified = NOW() WHERE blog_author_id = '" . (int)$blog_author_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_author` SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE blog_author_id = '" . (int)$blog_author_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_author_description` WHERE blog_author_id = '" . (int)$blog_author_id . "'");

		foreach ($data['author_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_author_description` SET blog_author_id = '" . (int)$blog_author_id . "', language_id = '" . (int)$language_id . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
		}

		$this->cache->delete('blog_author');
	}

	public function deleteAuthor($blog_author_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_author` WHERE blog_author_id = '" . (int)$blog_author_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_author_description` WHERE blog_author_id = '" . (int)$blog_author_id . "'");

		$this->cache->delete('blog_author');
	}

	public function getAuthor($blog_author_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "blog_author` WHERE blog_author_id = '" . (int)$blog_author_id . "'");

		return $query->row;
	}

	public function getTotalAuthors($data = array()) {
		$query = $this->db->query("SELECT COUNT(DISTINCT(ba.blog_author_id)) AS `total` FROM `" . DB_PREFIX . "blog_author` ba LEFT JOIN `" . DB_PREFIX . "blog_author_description` bad ON (ba.blog_author_id = bad.blog_author_id) WHERE bad.language_id='" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}

	public function getAuthors($data = array()) {
		$sql = "SELECT ba.* FROM `" . DB_PREFIX . "blog_author` ba LEFT JOIN `" . DB_PREFIX . "blog_author_description` bad ON (ba.blog_author_id = bad.blog_author_id) WHERE bad.language_id='" . (int)$this->config->get('config_language_id') . "'";

		if (isset($data['filter_author']) && $data['filter_author'] != '') {
			$sql .= " AND ba.name LIKE '" . $this->db->escape($data['filter_author']) . "%'";
		}

		$sort_data = array(
			'ba.name',
			'ba.status',
			'ba.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY ba.name";
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

		return $query->rows;
	}

	public function getAuthorDescriptions($blog_author_id) {
		$author_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_author_description WHERE blog_author_id = '" . (int)$blog_author_id . "'");

		foreach ($query->rows as $result) {
			$author_description_data[$result['language_id']] = array(
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description'],
				'description'      => $result['description']
			);
		}

		return $author_description_data;
	}

	public function getAuthorName($blog_author_id) {
		$query = $this->db->query("SELECT name FROM `" . DB_PREFIX . "blog_author` WHERE blog_author_id = '" . (int)$blog_author_id . "'");

		return $query->row['name'];
	}

	public function checkAuthorName($name, $blog_author_id = 0) {
		if (!$blog_author_id) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_author` WHERE LCASE(name) = '" . $this->db->escape(utf8_strtolower($name)) . "'");
		} else {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_author` WHERE LCASE(name) = '" . $this->db->escape(utf8_strtolower($name)) . "' AND blog_author_id <> '" . (int)$blog_author_id . "'");
		}

		return $query->num_rows;
	}

	public function getTotalArticleByAuthorId($blog_author_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article` WHERE blog_author_id = '" . (int)$blog_author_id . "'");

		return $query->num_rows;
	}
}
