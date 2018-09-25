<?php
class ModelModificationBlog extends Model {

	public function getCategories($data = array()) {
		$sql = "SELECT bc.category_id, bcd.name FROM `" . DB_PREFIX . "blog_category` bc LEFT JOIN `" . DB_PREFIX . "blog_category_description` bcd ON (bc.category_id = bcd.category_id) WHERE bcd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($this->request->get['filter_name']) && $this->request->get['filter_name']) {
			$sql .= " AND bcd.name LIKE '" . $this->db->escape($this->request->get['filter_name']) . "%'";
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

	public function getTotalCategories() {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "blog_category` bc LEFT JOIN `" . DB_PREFIX . "blog_category_description` bcd ON (bc.category_id = bcd.category_id) WHERE bcd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($this->request->get['filter_name']) && $this->request->get['filter_name']) {
			$sql .= " AND bcd.name LIKE '" . $this->db->escape($this->request->get['filter_name']) . "%'";
		}

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

	public function category() {
		$category_id = $this->request->get['category_id'];

		$query1 = $this->db->query("SELECT parent_id, `image`, status, sort_order FROM `" . DB_PREFIX . "blog_category` WHERE category_id = '" . (int)$category_id . "'");
		$query2 = $this->db->query("SELECT language_id, `name`, description, meta_title, meta_keywords, meta_description, keyword FROM `" . DB_PREFIX . "blog_category_description` WHERE category_id = '" . (int)$category_id . "'");
		$query3 = $this->db->query("SELECT store_id, layout_id FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE category_id = '" . (int)$category_id . "'");
		$query4 = $this->db->query("SELECT store_id FROM `" . DB_PREFIX . "blog_category_to_store` WHERE category_id = '" . (int)$category_id . "'");

		$result = array(
			'name' => array(
				'value' => array()
			),
			'description' => array(),
			'meta_title' => array(
				'value' => array()
			),
			'meta_keywords' => array(
				'value' => array()
			),
			'meta_description' => array(
				'value' => array()
			),
			'keyword' => array(
				'value' => array()
			),
			'parent_id' => $query1->row['parent_id'],
			'image' => $query1->row['image'],
			'status' => $query1->row['status'],
			'sort_order' => $query1->row['sort_order'],
			'layouts' => array(),
			'store_ids' => array()
		);

		foreach ($query2->rows as $row) {
			$result['name']['value'][$row['language_id']] = $row['name'];
			$result['description'][$row['language_id']] = $row['description'];
			$result['meta_title']['value'][$row['language_id']] = $row['meta_title'];
			$result['meta_keywords']['value'][$row['language_id']] = $row['meta_keywords'];
			$result['meta_description']['value'][$row['language_id']] = $row['meta_description'];
			$result['keyword']['value'][$row['language_id']] = $row['keyword'];
		}

		foreach ($query3->rows as $row) {
			$result['layouts'][$row['store_id']] = $row['layout_id'];
		}

		foreach ($query4->rows as $row) {
			$result['store_ids'][] = $row['store_id'];
		}

		return $result;
	}

	public function addCategory($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category` SET parent_id = '" . (int)$data['parent_id'] . "', `image` = '" . $this->db->escape($data['image']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$category_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_category_id'] = $category_id;

		foreach ($data['blog_category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_description` SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_keywords = '" . $this->db->escape($value['meta_keywords']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', keyword = '" . $this->db->escape($value['keyword']) . "'");
		}

		if (isset($data['blog_category_layout'])) {
			foreach ($data['blog_category_layout'] as $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_layout` SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		if (isset($data['blog_category_store'])) {
			foreach ($data['blog_category_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_store` SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
	}

	public function editCategory($category_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "blog_category` SET parent_id = '" . (int)$data['parent_id'] . "', `image` = '" . $this->db->escape($data['image']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE category_id = '" . (int)$category_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_description` WHERE category_id = '" . (int)$category_id . "'");

		foreach ($data['blog_category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_description` SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_keywords = '" . $this->db->escape($value['meta_keywords']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', keyword = '" . $this->db->escape($value['keyword']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['blog_category_layout'])) {
			foreach ($data['blog_category_layout'] as $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_layout` SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_store` WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['blog_category_store'])) {
			foreach ($data['blog_category_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_category_to_store` SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
	}

	public function deleteCategory($category_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category` WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_description` WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_store` WHERE category_id = '" . (int)$category_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_to_category` WHERE category_id = '" . (int)$category_id . "'");
	}

	// Posts
	public function getPosts($data = array()) {
		$sql = "SELECT p.post_id, pd.name, IF(p.views IS NULL, 0, p.views) AS views, (SELECT COUNT(*) FROM `" . DB_PREFIX . "blog_comments` WHERE post_id = p.post_id) AS comments";
		$sql .= "FROM `" . DB_PREFIX . "blog_post` p LEFT JOIN `" . DB_PREFIX . "blog_post_description` pd ON (p.post_id = pd.post_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($this->request->get['filter_name']) && $this->request->get['filter_name']) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($this->request->get['filter_name']) . "%'";
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

	public function getTotalPosts() {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "blog_post` p LEFT JOIN `" . DB_PREFIX . "blog_post_description` pd ON (p.post_id = pd.post_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($this->request->get['filter_name']) && $this->request->get['filter_name']) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($this->request->get['filter_name']) . "%'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function post() {
		$post_id = $this->request->get['post_id'];

		$query1 = $this->db->query("SELECT `image`, `comments`, status, sort_order, date_created, author_id FROM `" . DB_PREFIX . "blog_post` WHERE post_id = '" . (int)$post_id . "'");
		$query2 = $this->db->query("SELECT language_id, `name`, description, meta_title, meta_keywords, meta_description, keyword, tags FROM `" . DB_PREFIX . "blog_post_description` WHERE post_id = '" . (int)$post_id . "'");
		$query3 = $this->db->query("SELECT category_id FROM `" . DB_PREFIX . "blog_post_to_category` WHERE post_id = '" . (int)$post_id . "'");
		$query4 = $this->db->query("SELECT p.product_id AS product_id, pd.name AS `name` FROM `" . DB_PREFIX . "blog_post_to_product` p LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id = p.product_id) WHERE post_id = '" . (int)$post_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
		$query5 = $this->db->query("SELECT store_id, layout_id FROM `" . DB_PREFIX . "blog_post_to_layout` WHERE post_id = '" . (int)$post_id . "'");
		$query6 = $this->db->query("SELECT store_id FROM `" . DB_PREFIX . "blog_post_to_store` WHERE post_id = '" . (int)$post_id . "'");

		$result = array(
			'name' => array(
				'value' => array()
			),
			'description' => array(),
			'meta_title' => array(
				'value' => array()
			),
			'meta_keywords' => array(
				'value' => array()
			),
			'meta_description' => array(
				'value' => array()
			),
			'keyword' => array(
				'value' => array()
			),
			'tags' => array(
				'value' => array()
			),
			'image' => $query1->row['image'],
			'comments' => $query1->row['comments'],
			'status' => $query1->row['status'],
			'sort_order' => $query1->row['sort_order'],
			'date_created' => $query1->row['date_created'],
			'author_id' => $query1->row['author_id'],
			'categories' => array(),
			'products' => array(),
			'layouts' => array(),
			'store_ids' => array()
		);

		foreach ($query2->rows as $row) {
			$result['name']['value'][$row['language_id']] = $row['name'];
			$result['description'][$row['language_id']] = $row['description'];
			$result['meta_title']['value'][$row['language_id']] = $row['meta_title'];
			$result['meta_keywords']['value'][$row['language_id']] = $row['meta_keywords'];
			$result['meta_description']['value'][$row['language_id']] = $row['meta_description'];
			$result['keyword']['value'][$row['language_id']] = $row['keyword'];
			$result['tags']['value'][$row['language_id']] = $row['tags'];
		}

		foreach ($query3->rows as $row) {
			$result['categories'][] = array(
				'category_id' => $row['category_id']
			);
		}

		foreach ($query4->rows as $row) {
			$result['products'][] = array(
				'data' => array(
					'id'    => $row['product_id'],
					'name'  => $row['name']
				)
			);
		}

		foreach ($query5->rows as $row) {
			$result['layouts'][$row['store_id']] = $row['layout_id'];
		}

		foreach ($query6->rows as $row) {
			$result['store_ids'][] = $row['store_id'];
		}

		return $result;
	}

	public function addPost($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post` SET `image` = '" . $this->db->escape($data['image']) . "', `comments` = '" . $this->db->escape($data['comments']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_created = NOW(), author_id = '" . (int)$data['author_id'] . "'");

		$post_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_post_id'] = $post_id;

		foreach ($data['blog_post_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_description` SET post_id = '" . (int)$post_id . "', language_id = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_keywords = '" . $this->db->escape($value['meta_keywords']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', keyword = '" . $this->db->escape($value['keyword']) . "', tags = '" . $this->db->escape($value['tags']) . "'");
		}

		if (isset($data['blog_post_to_category'])) {
			foreach ($data['blog_post_to_category'] as $category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_to_category` SET post_id = '" . (int)$post_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['blog_post_to_product'])) {
			foreach ($data['blog_post_to_product'] as $product_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_to_product` SET post_id = '" . (int)$post_id . "', product_id = '" . (int)$product_id . "'");
			}
        }

		if (isset($data['blog_post_to_layout'])) {
			foreach ($data['blog_post_to_layout'] as $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_to_layout` SET post_id = '" . (int)$post_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		if (isset($data['blog_post_to_store'])) {
			foreach ($data['blog_post_to_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_to_store` SET post_id = '" . (int)$post_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
	}

	public function editPost($post_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "blog_post` SET `image` = '" . $this->db->escape($data['image']) . "', `comments` = '" . $this->db->escape($data['comments']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', author_id = '" . (int)$data['author_id'] . "', date_updated = NOW() WHERE post_id = '" . (int)$post_id . "'");

		if (isset($data['date_created'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_post` SET date_created = '" . $this->db->escape($data['date_created']) . "' WHERE post_id = '" . (int)$post_id . "'");
		} else {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_post` SET date_created = NOW() WHERE post_id = '" . (int)$post_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_description` WHERE post_id = '" . (int)$post_id . "'");

		foreach ($data['blog_post_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_description` SET post_id = '" . (int)$post_id . "', language_id = '" . (int)$language_id . "', `name` = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_keywords = '" . $this->db->escape($value['meta_keywords']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', keyword = '" . $this->db->escape($value['keyword']) . "', tags = '" . $this->db->escape($value['tags']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_to_category` WHERE post_id = '" . (int)$post_id . "'");

		if (isset($data['blog_post_to_category'])) {
			foreach ($data['blog_post_to_category'] as $category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_to_category` SET post_id = '" . (int)$post_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_to_product` WHERE post_id = '" . (int)$post_id . "'");

		if (isset($data['blog_post_to_product'])) {
			foreach ($data['blog_post_to_product'] as $product_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_to_product` SET post_id = '" . (int)$post_id . "', product_id = '" . (int)$product_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_to_layout` WHERE post_id = '" . (int)$post_id . "'");

		if (isset($data['blog_post_to_layout'])) {
			foreach ($data['blog_post_to_layout'] as $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_to_layout` SET post_id = '" . (int)$post_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_to_store` WHERE post_id = '" . (int)$post_id . "'");

		if (isset($data['blog_post_to_store'])) {
			foreach ($data['blog_post_to_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_post_to_store` SET post_id = '" . (int)$post_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
	}

	public function deletePost($post_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post` WHERE post_id = '" . (int)$post_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_description` WHERE post_id = '" . (int)$post_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_to_category` WHERE post_id = '" . (int)$post_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_to_layout` WHERE post_id = '" . (int)$post_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_to_store` WHERE post_id = '" . (int)$post_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_post_to_product` WHERE post_id = '" . (int)$post_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_comments` WHERE post_id = '" . (int)$post_id . "'");
	}

	// Authors
	public function authors() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user`");

		return $query->rows;
	}

	// Comments
	public function getComments($data = array()) {
		$sql = "SELECT bc.comment_id, bc.name AS author, bpd.name AS post_name, bc.parent_id AS parent_id, bc.status AS status";
		$sql .= " FROM `" . DB_PREFIX . "blog_comments` bc LEFT JOIN `" . DB_PREFIX . "blog_post_description` bpd ON (bc.post_id = bpd.post_id) WHERE bpd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

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

	public function getTotalComments() {
		$sql = "SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "blog_comments` bc LEFT JOIN `" . DB_PREFIX . "blog_post_description` bpd ON (bc.post_id = bpd.post_id) WHERE bpd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function comment($comment_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_comments` WHERE comment_id = '" . (int)$comment_id . "'");

		return $query->row;
	}

	public function editComment($comment_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "blog_comments` SET `name` = '" . $this->db->escape($data['image']) . "', website = '" . $this->db->escape($data['website']) . "', email   = '" . $this->db->escape($data['email']) . "', `comment` = '" . $this->db->escape($data['comment']) . "', status  = '" . (int)$data['status'] . "' WHERE comment_id = '" . (int)$comment_id . "'");

		return null;
	}

	public function delete_comment($comment_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_comments` WHERE comment_id = '" . (int)$comment_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_comments` WHERE parent_id = '" . (int)$comment_id . "'");

		return null;
	}
}
