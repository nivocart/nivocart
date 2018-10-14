<?php
class ModelBlogComment extends Model {

	public function addArticleComment($data) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_description` WHERE article_title = '" . $this->db->escape($data['article_title']) . "'");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_comment` SET blog_article_id = '" . (int)$query->row['blog_article_id'] . "', author='" . $this->db->escape($data['author_name']) . "', `comment` = '" . $this->db->escape($data['comment']) . "', status = '" . (int)$data['status'] . "', date_added = NOW(), date_modified = NOW()");

		$blog_comment_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_blog_comment_id'] = $blog_comment_id;

		if (isset($data['comment_reply'])) {
			foreach ($data['comment_reply'] as $reply) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_comment` SET blog_article_id = '" . (int)$query->row['blog_article_id'] . "', blog_article_reply_id='" . (int)$blog_comment_id . "', author='" . $this->db->escape($reply['author']) . "', `comment` = '" . $this->db->escape($reply['comment']) . "', status = '" . (int)$reply['status'] . "', date_added = NOW(), date_modified = NOW()");
			}
		}
	}

	public function editArticleComment($blog_comment_id, $data) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_description` WHERE article_title = '" . $this->db->escape($data['article_title']) . "'");

		$this->db->query("UPDATE `" . DB_PREFIX . "blog_comment` SET blog_article_id = '" . (int)$query->row['blog_article_id'] . "', author = '" . $this->db->escape($data['author_name']) . "', `comment` = '" . $this->db->escape($data['comment']) . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE blog_comment_id = '" . (int)$blog_comment_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_comment` WHERE blog_article_reply_id = '" . (int)$blog_comment_id . "'");

		if (isset($data['comment_reply'])) {
			foreach ($data['comment_reply'] as $reply) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "blog_comment` SET blog_article_id = '" . (int)$query->row['blog_article_id'] . "', blog_article_reply_id = '" . (int)$blog_comment_id . "', author='" . $this->db->escape($reply['author']) . "', `comment` = '" . $this->db->escape($reply['comment']) . "', status = '" . (int)$reply['status'] . "', date_added = NOW(), date_modified = NOW()");
			}
		}
	}

	public function deleteArticleComment($blog_comment_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_comment` WHERE blog_comment_id = '" . (int)$blog_comment_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_comment` WHERE blog_article_reply_id = '" . (int)$blog_comment_id . "'");
	}

	public function getArticleComment($blog_comment_id) {
		$query = $this->db->query("SELECT bc.*, bad.article_title AS article_title FROM `" . DB_PREFIX . "blog_comment` bc LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (bc.blog_article_id = bad.blog_article_id) WHERE bc.blog_comment_id = '" . (int)$blog_comment_id . "' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getTotalArticleComment($data = array()) {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "blog_comment` WHERE blog_article_reply_id = '" . $data['blog_article_reply_id'] . "'");

		return $query->row['total'];
	}

	public function getArticleComments($data = array()) {
		$sql = "SELECT bc.*, bad.article_title AS article_title FROM `" . DB_PREFIX . "blog_comment` bc LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (bc.blog_article_id = bad.blog_article_id) WHERE bc.blog_article_reply_id = '" . $data['blog_article_reply_id'] . "' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = array(
			'bad.article_title',
			'bc.author',
			'bc.status',
			'bc.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sbc.date_added";
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

	public function getCommentReply($blog_comment_id) {
		$comment_reply = array();

		$query = $this->db->query("SELECT bc.*, bad.article_title AS article_title FROM `" . DB_PREFIX . "blog_comment` bc LEFT JOIN `" . DB_PREFIX . "blog_article_description` bad ON (bc.blog_article_id = bad.blog_article_id) WHERE bc.blog_article_reply_id = '" . (int)$blog_comment_id . "' AND bad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		foreach ($query->rows as $result) {
			$comment_reply[] = array(
				'blog_comment_id' => $result['blog_comment_id'],
				'artile_title'    => $result['article_title'],
				'author'          => $result['author'],
				'comment'         => $result['comment'],
				'status'          => $result['status']
			);
		}

		return $comment_reply;
	}

	public function checkArticleTitle($article_title) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article_description` WHERE article_title = '" . $this->db->escape($article_title) . "'");

		return $query->num_rows;
	}
}
