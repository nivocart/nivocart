<?php
class ModelBlogReport extends Model {

	public function getTotalBlogViewed($data = array()) {
		$sql = "SELECT COUNT(DISTINCT(bv.blog_view_id)) AS `total` FROM `" . DB_PREFIX . "blog_view` bv LEFT JOIN `" . DB_PREFIX . "blog_article` ba ON (bv.blog_article_id = ba.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bv.view > 0";

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(bv.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(bv.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalBlogViews($data = array()) {
		$sql = "SELECT SUM(bv.view) AS `total` FROM `" . DB_PREFIX . "blog_view` bv LEFT JOIN `" . DB_PREFIX . "blog_article` ba ON (bv.blog_article_id = ba.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getBlogViewed($data = array()) {
		$sql = "SELECT bv.*, bad.article_title AS article_title, bau.name AS author_name FROM `" . DB_PREFIX . "blog_view` bv LEFT JOIN `" . DB_PREFIX . "blog_article` ba ON (bv.blog_article_id = ba.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN `" . DB_PREFIX . "blog_author` bau ON (ba.blog_author_id = bau.blog_author_id) WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(bv.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(bv.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$sql .= " GROUP BY bv.blog_article_id";

		$sort_data = array(
			'bv.view',
			'ba.article_title',
			'bau.name'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY bv.view";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
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
}
