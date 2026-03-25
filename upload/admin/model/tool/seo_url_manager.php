<?php
class ModelToolSeoUrlManager extends Model {

	public function addUrl(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET `query` = '" . $this->db->escape($data['query']) . "', keyword = '" . $this->db->escape($data['keyword']) . "'");

		$url_alias_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_url_alias_id'] = $url_alias_id;

		$this->cache->delete('seo_url_map');
	}

	public function editUrl(int $url_alias_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "url_alias` SET `query` = '" . $this->db->escape($data['query']) . "', keyword = '" . $this->db->escape($data['keyword']) . "' WHERE url_alias_id = '" . (int)$url_alias_id . "'");

		$this->cache->delete('seo_url_map');
	}

	public function deleteUrl(int $url_alias_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE url_alias_id = " . (int)$url_alias_id);

		$this->cache->delete('seo_url_map');
	}

	public function getUrl(int $url_alias_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "url_alias` WHERE url_alias_id = '" . (int)$url_alias_id . "'");

		return $query->row;
	}

	public function getUrls(array $data = []): array {
		$sql = "SELECT url_alias_id, query, keyword FROM `" . DB_PREFIX . "url_alias`";

		$sort_data = array(
			'url_alias_id',
			'query',
			'keyword'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY keyword";
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

	public function getTotalUrls(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "url_alias`");

		return $query->row['total'];
	}

	public function getTotalUniqueKeywords() {
		$query = $this->db->query("SELECT COUNT(DISTINCT keyword) AS keyword_total FROM `" . DB_PREFIX . "url_alias`");

		return $query->row['keyword_total'];
	}
}
