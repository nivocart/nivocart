<?php
class ModelBlogArticle extends Model {

	public function getArticles($data = array()) {
		$sql = "SELECT ba.*, bad.*, bau.name AS author_name FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_store` ba2s ON (ba.blog_article_id = ba2s.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id)";
		$sql .= " WHERE ba.status = '1' AND bau.status = '1' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$sql .= " ORDER BY ba.date_modified DESC";

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

	public function getTotalArticle($data = array()) {
		$sql = "SELECT COUNT(DISTINCT(ba.blog_article_id)) AS `total` FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_store` ba2s ON (ba.blog_article_id = ba2s.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id)";
		$sql .= " WHERE ba.status = '1' AND bau.status = '1' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$sql .= " ORDER BY ba.date_modified DESC";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getCategory($blog_category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "blog_category bc LEFT JOIN " . DB_PREFIX . "blog_category_description bcd ON (bc.blog_category_id = bcd.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category_to_store bc2s ON (bc.blog_category_id = bc2s.blog_category_id) WHERE bc.blog_category_id = '" . (int)$blog_category_id . "' AND bcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bc2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bc.status = '1'");

		return $query->row;
	}

	public function getCategories($parent_id = 0) {
		if ($parent_id == 0) {
			$blog_category_data = array();

			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_category bc LEFT JOIN " . DB_PREFIX . "blog_category_description bcd ON (bc.blog_category_id = bcd.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category_to_store bc2s ON (bc.blog_category_id = bc2s.blog_category_id) WHERE bc.parent_id = '" . (int)$parent_id . "' AND bcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bc2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bc.status = '1' ORDER BY bc.sort_order, LCASE(bcd.name)");

			foreach ($query->rows as $result) {
				$blog_category_data[$result['blog_category_id']] = $this->getCategory($result['blog_category_id']);
			}

			return $blog_category_data;

		} else {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_category bc LEFT JOIN " . DB_PREFIX . "blog_category_description bcd ON (bc.blog_category_id = bcd.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category_to_store bc2s ON (bc.blog_category_id = bc2s.blog_category_id) WHERE bc.parent_id = '" . (int)$parent_id . "' AND bcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bc2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bc.status = '1' ORDER BY bc.sort_order, LCASE(bcd.name)");

			return $query->rows;
		}
	}

	public function getTotalCategories($parent_id = 0) {
		$sql = "SELECT COUNT(DISTINCT(bc.blog_category_id)) AS `total` FROM " . DB_PREFIX . "blog_category bc LEFT JOIN " . DB_PREFIX . "blog_category_description bcd ON (bc.blog_category_id = bcd.blog_category_id) LEFT JOIN " . DB_PREFIX . "blog_category_to_store bc2s ON (bc.blog_category_id = bc2s.blog_category_id)";
		$sql .= " WHERE bc.parent_id = '" . (int)$parent_id . "' AND bcd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bc2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bc.status = '1' ORDER BY bc.sort_order, LCASE(bcd.name) ASC";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalArticles($blog_category_id) {
		$query = $this->db->query("SELECT COUNT(DISTINCT(blog_article_id)) AS `total` FROM `" . DB_PREFIX . "blog_article_to_category` WHERE blog_category_id = '" . (int)$blog_category_id . "'");

		return $query->row['total'];
	}

	public function getTotalComments($blog_article_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "blog_comment` WHERE blog_article_id = '" . (int)$blog_article_id . "' AND status = '1'");

		return $query->row['total'];
	}

	public function getAdditionalDescription($blog_article_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_description_additional` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		return $query->rows;
	}

	public function getArticle($blog_article_id) {
		$query = $this->db->query("SELECT ba.*, bad.*, bau.name AS author_name FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_store` ba2s ON (ba.blog_article_id = ba2s.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) WHERE ba.blog_article_id = '" . (int)$blog_article_id . "' AND bau.status = '1' AND ba.status = '1' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row;
	}

	public function addBlogView($blog_article_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_view` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		if ($query->num_rows) {
			$counter = $query->row['view'];

			$counter++;

			$this->db->query("UPDATE `" . DB_PREFIX . "blog_view` SET view = '" . (int)$counter . "', date_modified = NOW() WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_view` SET blog_article_id = '" . (int)$blog_article_id . "', view = '1', date_added = NOW(), date_modified = NOW()");
		}
	}

	public function getArticleAdditionalDescription($blog_article_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_description_additional` WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND blog_article_id = '" . (int)$blog_article_id . "'");

		return $query->rows;
	}

	public function getArticleProductRelated($blog_article_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_product_related` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		return $query->rows;
	}

	public function getTotalCommentsByArticleId($blog_article_id) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "blog_comment` WHERE blog_article_id = '" . (int)$blog_article_id . "' AND status = '1' AND blog_article_reply_id = '0'");

		return $query->row['total'];
	}

	public function getCommentsByArticle($blog_article_id, $start = 0, $limit = 20, $blog_comment_id = 0) {
		if (!$blog_comment_id) {
			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = 20;
			}

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_comment` WHERE blog_article_id = '" . (int)$blog_article_id . "' AND status = '1' AND blog_article_reply_id = '0' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

			return $query->rows;

		} else {
			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = 1000;
			}

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_comment` WHERE blog_article_reply_id = '" . (int)$blog_comment_id . "' AND status = '1' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

			return $query->rows;
		}
	}

	public function addArticleComment($blog_article_id, $data) {
		if ($this->config->get('blog_comment_auto_approval')) {
			$status = 1;
		} else {
			$status = 0;
		}

		if ($data['reply_id']) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_comment` SET blog_article_id = '" . (int)$blog_article_id . "', blog_article_reply_id='" . (int)$data['reply_id'] . "', author = '" . $this->db->escape($data['name']) . "', `comment` = '" . $this->db->escape($data['text']) . "', status = '" . (int)$status . "', date_added = NOW(), date_modified = NOW()");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_comment` SET blog_article_id = '" . (int)$blog_article_id . "', author = '" . $this->db->escape($data['name']) . "', `comment` = '" . $this->db->escape($data['text']) . "', status = '" . (int)$status . "', date_added = NOW(), date_modified = NOW()");
		}
	}

	public function getTotalArticleCategoryWise($data = array()) {
		$query = $this->db->query("SELECT COUNT(DISTINCT(ba.blog_article_id)) AS `total` FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_store` ba2s ON (ba.blog_article_id = ba2s.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_category` bac ON (ba.blog_article_id = bac.blog_article_id) WHERE bac.blog_category_id = '" . (int)$data['blog_article_id'] . "' AND ba.status = '1' AND bau.status = '1' AND ba2s.store_id='" . (int)$this->config->get('config_store_id') . "'");

		return $query->row['total'];
	}

	public function getArticleCategoryWise($data = array()) {
		$sql = "SELECT ba.*, bad.*, bau.name AS author_name FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_store` ba2s ON (ba.blog_article_id = ba2s.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_category` bac ON (ba.blog_article_id = bac.blog_article_id) WHERE bac.blog_category_id = '" . (int)$data['blog_article_id'] . "' AND ba.status = '1' AND bau.status = '1' AND ba2s.store_id='" . (int)$this->config->get('config_store_id') . "' ORDER BY ba.date_modified DESC";

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

	public function getTotalArticleAuthorWise($blog_author_id) {
		$sql = $this->db->query("SELECT COUNT(DISTINCT(ba.blog_article_id)) AS `total` FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_store` ba2s ON (ba.blog_article_id = ba2s.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) WHERE ba.blog_author_id = '" . (int)$blog_author_id . "' AND ba.status = '1' AND bau.status = '1' AND bau.status = '1' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $sql->row['total'];
	}

	public function getArticleAuthorWise($data = array()) {
		$sql = "SELECT ba.*, bad.*, bau.name AS author_name FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_store` ba2s ON (ba.blog_article_id = ba2s.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON(ba.blog_author_id=bau.blog_author_id) WHERE ba.blog_author_id = '" . (int)$data['blog_author_id'] . "' AND ba.status = '1' AND bau.status = '1' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY ba.date_modified DESC";

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

	public function getAuthorInformation($blog_author_id) {
		$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_author` ba LEFT JOIN `" . DB_PREFIX . "blog_author_description` bau ON (ba.blog_author_id = bau.blog_author_id) WHERE ba.blog_author_id = '" . (int)$blog_author_id . "' AND ba.status = '1'");

		return $sql->row;
	}

	public function getArticleModuleWise($data = array()) {
		$sql = "SELECT ba.*, bad.*, bau.name AS author_name FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_store` ba2s ON (ba.blog_article_id = ba2s.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_category` bac ON (ba.blog_article_id = bac.blog_article_id) WHERE ba.status = '1' AND bau.status = '1' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (!empty($data['filter_category_id'])) {
			$sql .= " AND bac.blog_category_id='" . (int)$data['filter_category_id'] . "'";
		}

		$sql .= " GROUP BY ba.blog_article_id ORDER BY ba.date_modified DESC";

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

	public function getPopularArticlesModuleWise($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "blog_view`";

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

		if ($query->num_rows) {
			$sql = "SELECT ba.*, bad.*, bau.name AS author_name FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_store` ba2s ON (ba.blog_article_id = ba2s.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) LEFT JOIN `" . DB_PREFIX . "blog_article_to_category` bac ON (ba.blog_article_id = bac.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_view` bv ON (bv.blog_article_id = ba.blog_article_id) WHERE ba.status = '1' AND bau.status = '1' AND ba2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

			$sql .= " GROUP BY ba.blog_article_id ORDER BY bv.view DESC";

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$my_query = $this->db->query($sql);

			return $my_query->rows;
		} else {
			return '';
		}
	}

	public function getRelatedArticles($blog_article_id) {
		$this->load->model('tool/image');

		$blog_related_article_data = array();

		$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_related_article` WHERE blog_article_id = '" . (int)$blog_article_id . "' AND status = '1' ORDER BY sort_order");

		foreach ($sql->rows as $row) {
			$article_info = $this->db->query("SELECT ba.*, bad.article_title AS article_title, bad.description AS description, bau.blog_author_id AS blog_author_id, bau.name AS author_name FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) WHERE ba.blog_article_id='" . (int)$row['blog_article_related_id'] . "' AND ba.status = '1' AND bau.status = '1'");

			if ($article_info->row) {
				$total_comment = $this->getTotalComments($row['blog_article_related_id']);

				$image = $this->model_tool_image->resize($article_info->row['featured_image'], 150, 150);

				$blog_related_article_data[] = array(
					'blog_article_id' => $article_info->row['blog_article_id'],
					'article_title'   => $article_info->row['article_title'],
					'blog_author_id'  => $article_info->row['blog_author_id'],
					'image'           => $image,
					'description'     => $article_info->row['description'],
					'author_name'     => $article_info->row['author_name'],
					'date_added'      => date('F jS, Y', strtotime($article_info->row['date_added'])),
					'date_modified'   => date('F jS, Y', strtotime($article_info->row['date_modified'])),
					'total_comment'   => $total_comment
				);
			}
		}

		return $blog_related_article_data;
	}

	public function getAuthors() {
		$sql = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_author` WHERE status = '1'");

		return $sql->rows;
	}

	public function getBlogArticleLayoutId($blog_article_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_article_to_layout WHERE blog_article_id = '" . (int)$blog_article_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return false;
		}
	}

	public function getBlogCategoryLayoutId($blog_category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "blog_category_to_layout WHERE blog_category_id = '" . (int)$blog_category_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return false;
		}
	}
}
