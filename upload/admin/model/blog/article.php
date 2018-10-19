<?php
class ModelBlogArticle extends Model {

	public function addArticle($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article` SET blog_author_id = '" . (int)$data['blog_author_id'] . "', allow_comment = '" . (int)$data['allow_comment'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_added = NOW(), date_modified = NOW()");

		$blog_article_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_blog_article_id'] = $blog_article_id;

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET image = '" . $this->db->escape($data['image']) . "' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		}

		if (isset($data['featured_image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET featured_image = '" . $this->db->escape($data['featured_image']) . "' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		}

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_description` SET blog_article_id = '" . (int)$blog_article_id . "', language_id = '" . (int)$language_id . "', article_title = '" . $this->db->escape($value['article_title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (!empty($data['article_additional_description'])) {
			foreach ($data['article_additional_description'] as $key => $additional_value) {
				foreach ($additional_value as $val_key => $value) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_description_additional` SET blog_article_id = '" . (int)$blog_article_id . "', language_id = '" . (int)$val_key . "', additional_description = '" . $this->db->escape($value['additional']) . "'");
				}
			}
		}

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_to_category` SET blog_article_id = '" . (int)$blog_article_id . "', blog_category_id = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['article_store'])) {
			foreach ($data['article_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_to_store` SET blog_article_id = '" . (int)$blog_article_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['article_layout'])) {
			foreach ($data['article_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_to_layout` SET blog_article_id = '" . (int)$blog_article_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}

		if ($data['related_article'] == 'category_wise') {
			if (isset($data['category_wise'])) {
				$option = array();

				$option['category_wise'] = $data['category_wise'];

				$options = serialize($option);

				$product_list = $this->getProductCategoryWise($data['category_wise']);

				foreach ($product_list as $product_id) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_product_related` SET blog_article_id = '" . (int)$blog_article_id . "', product_id = '" . (int)$product_id . "'");
				}

				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '" . $this->db->escape($options) . "' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			} else {
				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '' WHERE blog_article_id='" . (int)$blog_article_id . "'");
			}

		} elseif ($data['related_article'] == 'manufacturer_wise') {
			if (isset($data['manufacturer_wise'])) {
				$option = array();

				$option['manufacturer_wise'] = $data['manufacturer_wise'];

				$options = serialize($option);

				$product_list = $this->getProductManufacturerWise($data['manufacturer_wise']);

				foreach ($product_list as $product_id) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_product_related` SET blog_article_id = '" . (int)$blog_article_id . "', product_id = '" . (int)$product_id . "'");
				}

				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '" . $this->db->escape($options) . "' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			} else {
				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			}

		} else {
			if (isset($data['product_wise'])) {
				foreach ($data['product_wise'] as $product_id) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_product_related` SET blog_article_id = '" . (int)$blog_article_id . "', product_id = '" . (int)$product_id . "'");
				}

				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			} else {
				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			}
		}

		if (isset($this->request->post['blog_related_articles'])) {
			foreach ($this->request->post['blog_related_articles'] as $related_article) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_related_article` SET blog_article_id = '" . (int)$blog_article_id . "', blog_article_related_id = '" . (int)$related_article['blog_article_related_id'] . "', sort_order = '" . (int)$related_article['sort_order'] . "', status = '" . (int)$related_article['status'] . "', date_added = NOW()");
			}
		}

		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET `query` = 'blog_article_id=" . (int)$blog_article_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
	}

	public function editArticle($blog_article_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET blog_author_id = '" . (int)$data['blog_author_id'] . "', allow_comment = '" . (int)$data['allow_comment'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified=NOW() WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET image = '" . $this->db->escape($data['image']) . "' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		}

		if (isset($data['featured_image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET featured_image = '" . $this->db->escape($data['featured_image']) . "' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_description` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		foreach ($data['article_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_description` SET blog_article_id = '" . (int)$blog_article_id . "', language_id = '" . (int)$language_id . "', article_title = '" . $this->db->escape($value['article_title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_description_additional` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		if (!empty($data['article_additional_description'])) {
			foreach ($data['article_additional_description'] as $key => $additional_value) {
				foreach ($additional_value as $val_key => $value) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_description_additional` SET blog_article_id = '" . (int)$blog_article_id . "', language_id = '" . (int)$val_key . "', additional_description = '" . $this->db->escape($value['additional']) . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_category` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		if (isset($data['article_category'])) {
			foreach ($data['article_category'] as $category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_to_category` SET blog_article_id = '" . (int)$blog_article_id . "', blog_category_id = '" . (int)$category_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_store` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		if (isset($data['article_store'])) {
			foreach ($data['article_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_to_store` SET blog_article_id = '" . (int)$blog_article_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_layout` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		if (isset($data['article_layout'])) {
			foreach ($data['article_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_to_layout` SET blog_article_id = '" . (int)$blog_article_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_product_related` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		if ($data['related_article'] == 'category_wise') {
			if (isset($data['category_wise'])) {
				$option = array();

				$option['category_wise'] = $data['category_wise'];

				$options = serialize($option);

				$product_list = $this->getProductCategoryWise($data['category_wise']);

				foreach ($product_list as $product_id) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_product_related` SET blog_article_id = '" . (int)$blog_article_id . "', product_id = '" . (int)$product_id . "'");
				}

				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '" . $this->db->escape($options) . "' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			} else {
				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			}

		} elseif ($data['related_article'] == 'manufacturer_wise') {
			if (isset($data['manufacturer_wise'])) {
				$option = array();

				$option['manufacturer_wise'] = $data['manufacturer_wise'];

				$options = serialize($option);

				$product_list = $this->getProductManufacturerWise($data['manufacturer_wise']);

				foreach ($product_list as $product_id) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_product_related` SET blog_article_id = '" . (int)$blog_article_id . "', product_id = '" . (int)$product_id . "'");
				}

				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '" . $this->db->escape($options) . "' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			} else {
				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			}

		} else {
			if (isset($data['product_wise'])) {
				foreach ($data['product_wise'] as $product_id) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_article_product_related` SET blog_article_id = '" . (int)$blog_article_id . "', product_id = '" . (int)$product_id . "'");
				}

				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			} else {
				$this->db->query("UPDATE `" . DB_PREFIX . "blog_article` SET article_related_method = '" . $this->db->escape($data['related_article']) . "', article_related_option = '' WHERE blog_article_id = '" . (int)$blog_article_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_related_article` WHERE blog_article_id='" . (int)$blog_article_id . "'");

		if (isset($this->request->post['blog_related_articles'])) {
			foreach ($this->request->post['blog_related_articles'] as $related_article) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_related_article` SET blog_article_id = '" . (int)$blog_article_id . "', blog_article_related_id = '" . (int)$related_article['blog_article_related_id'] . "', sort_order = '" . (int)$related_article['sort_order'] . "', status = '" . (int)$related_article['status'] . "', date_added = NOW()");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'blog_article_id=" . (int)$blog_article_id. "'");

		if (isset($data['keyword'])) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET query = 'blog_article_id=" . (int)$blog_article_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

	}

	public function deleteArticle($blog_article_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_description` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_description_additional` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_category` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_store` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_layout` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_product_related` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_related_article` WHERE blog_article_id = '" . (int)$blog_article_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_view` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'blog_article_id=" . (int)$blog_article_id. "'");
	}

	public function checkDeleteArticle($blog_article_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_related_article` WHERE blog_article_related_id = '" . (int)$blog_article_id . "'");

		return $query->num_rows;
	}

	public function getArticle($blog_article_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'blog_article_id=" . (int)$blog_article_id . "') AS keyword FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) WHERE ba.blog_article_id = '" . (int)$blog_article_id . "' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getTotalArticle($data = array()) {
		$sql = "SELECT COUNT(DISTINCT(ba.blog_article_id)) AS `total` FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) WHERE bad.language_id='" . (int)$this->config->get('config_language_id'). "'";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getArticles($data = array()) {
		$sql = "SELECT ba.*, bau.name AS author_name, bad.article_title AS article_title FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) WHERE bad.language_id='" . (int)$this->config->get('config_language_id'). "'";

		if (isset($data['filter_article']) && !empty($data['filter_article'])) {
			$sql .= " AND bad.article_title LIKE '" . $this->db->escape($data['filter_article']) . "%'";
		}

		$sort_data = array(
			'bad.article_title',
			'bau.name',
			'ba.sort_order',
			'ba.status',
			'ba.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY ba.date_added";
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

	public function getArticleDescriptions($blog_article_id) {
		$blog_article_description_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_description` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		foreach ($query->rows as $result) {
			$blog_article_description_data[$result['language_id']] = array(
				'blog_article_description_id' => $result['blog_article_description_id'],
				'article_title'               => $result['article_title'],
				'description'                 => $result['description'],
				'meta_description'            => $result['meta_description'],
				'meta_keyword'                => $result['meta_keyword']
			);
		}

		return $blog_article_description_data;
	}

	public function getArticleAdditionalDescriptions($blog_article_id) {
		$blog_article_additional_description = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_description_additional` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		if ($query->num_rows > 1) {
			$this->load->model('localisation/language');

			$language_total = $this->model_localisation_language->getTotalLanguages();

			$addition_blog_array = array();
			$counter = 0;

			foreach ($query->rows as $key => $result) {
				$counter++;

				$addition_blog_array[$result['language_id']] = array('additional' => $result['additional_description']);

				if ($counter == $language_total) {
					$blog_article_additional_description[] = $addition_blog_array;
					$addition_blog_array = array();
					$counter = 0;
				}
			}

		} else {
			foreach ($query->rows as $result) {
				$blog_article_additional_description[][$result['language_id']] = array('additional' => $result['additional_description']);
			}
		}

		return $blog_article_additional_description;
	}

	public function getArticleCategories($blog_article_id) {
		$article_category_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_to_category` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		foreach ($query->rows as $result) {
			$article_category_data[] = $result['blog_category_id'];
		}

		return $article_category_data;
	}

	public function getArticleStore($blog_article_id) {
		$article_store_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_to_store` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		foreach ($query->rows as $result) {
			$article_store_data[] = $result['store_id'];
		}

		return $article_store_data;
	}

	public function getArticleLayouts($blog_article_id) {
		$article_layout_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_to_layout` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		foreach ($query->rows as $result) {
			$article_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $article_layout_data;
	}

	public function getProductManufacturerWise($manufacturers) {
		$product_list = array();

		foreach ($manufacturers as $manufacturer) {
			$query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product` WHERE manufacturer_id = '" . (int)$manufacturer . "'");

			foreach ($query->rows as $result) {
				if (!in_array($result['product_id'], $product_list)) {
					$product_list[] = $result['product_id'];
				}
			}

			return $product_list;
		}
	}

	public function getProductCategoryWise($categories) {
		$product_list = array();

		foreach ($categories as $category_id) {
			$query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product_to_category` WHERE category_id = '" . (int)$category_id . "'");

			foreach ($query->rows as $result) {
				if (!in_array($result['product_id'], $product_list)) {
					$product_list[] = $result['product_id'];
				}
			}
		}

		return $product_list;
	}

	public function getArticleProduct($blog_article_id) {
		$query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "blog_article_product_related` WHERE blog_article_id = '" . (int)$blog_article_id . "'");

		return $query->rows;
	}

	public function checkAuthorName($author_name) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_author` WHERE LCASE(name) = '" . $this->db->escape(utf8_strtolower($author_name)) . "'");

		return $query->num_rows;
	}

	public function checkArticleName($language_id, $article_name, $blog_article_id = 0) {
		if (!$blog_article_id) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_description` WHERE LCASE(article_title) = '" . $this->db->escape(utf8_strtolower($article_name)) . "' AND language_id = '" . (int)$language_id . "'");

			return $query->num_rows;

		} else {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_description` WHERE LCASE(article_title) = '" . $this->db->escape(utf8_strtolower($article_name)) . "' AND language_id = '" . (int)$language_id . "' AND blog_article_id <> '" . (int)$blog_article_id . "'");

			return $query->num_rows;
		}
	}

	public function getRelatedArticles($blog_article_id) {
		$blog_related_article_data = array();

		$query = $this->db->query("SELECT bra.*, bad.article_title AS article_title FROM `" . DB_PREFIX . "blog_related_article` bra LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (bad.blog_article_id = bra.blog_article_related_id) WHERE bra.blog_article_id = '" . (int)$blog_article_id . "'");

		foreach ($query->rows as $row) {
			$blog_related_article_data[] = array(
				'blog_article_related_id' => $row['blog_article_related_id'],
				'article_title'           => $row['article_title'],
				'sort_order'              => $row['sort_order'],
				'status'                  => $row['status']
			);
		}

		return $blog_related_article_data;
	}

	public function getArticlesRelated($data, $blog_article_id) {
		$query = "SELECT * FROM `" . DB_PREFIX . "blog_article_description` WHERE LCASE(article_title) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%' AND blog_article_id <> '" . (int)$blog_article_id . "'";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$query .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($query);

		return $query->rows;
	}
}
