<?php
/**
 * Class ModelToolSeoUrlManager
 *
 * @package NivoCart
 */
class ModelToolSeoUrlManager extends Model {
	/**
	 * Functions Add, Edit, Delete, Get
	 */
	public function addUrl(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET `query` = '" . $this->db->escape($data['query']) . "', keyword = '" . $this->db->escape($data['keyword']) . "'");

		$url_alias_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_url_alias_id'] = $url_alias_id;

		$this->cache->delete('seo_url_map');
		$this->cache->delete('seo_url_audit');
	}

	public function editUrl(int $url_alias_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "url_alias` SET `query` = '" . $this->db->escape($data['query']) . "', keyword = '" . $this->db->escape($data['keyword']) . "' WHERE url_alias_id = '" . (int)$url_alias_id . "'");

		$this->cache->delete('seo_url_map');
		$this->cache->delete('seo_url_audit');
	}

	public function deleteUrl(int $url_alias_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE url_alias_id = " . (int)$url_alias_id);

		$this->cache->delete('seo_url_map');
		$this->cache->delete('seo_url_audit');
	}

	public function getUrl(int $url_alias_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "url_alias` WHERE url_alias_id = '" . (int)$url_alias_id . "'");

		return $query->row;
	}

	public function getUrls(array $data = []): array {
		$sql = "SELECT url_alias_id, `query`, keyword FROM `" . DB_PREFIX . "url_alias`";

		$sort_data = [
			'url_alias_id',
			'query',
			'keyword'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY keyword";
		}

		if (isset($data['order']) && ($data['order'] === 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) && isset($data['limit'])) {
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

	public function getTotalUniqueKeywords(): int {
		$query = $this->db->query("SELECT COUNT(DISTINCT keyword) AS `keyword_total` FROM `" . DB_PREFIX . "url_alias`");

		return $query->row['keyword_total'];
	}

	/**
	 * Audit
	 *
	 * Entity definitions used by the SEO URL audit (enabled entries only are checked for missing keywords).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function getAuditTypes(): array {
		return [
			'product'      => ['table' => 'product', 'id' => 'product_id', 'description' => 'product_description', 'name' => 'name', 'status' => true],
			'category'     => ['table' => 'category', 'id' => 'category_id', 'description' => 'category_description', 'name' => 'name', 'status' => true],
			'manufacturer' => ['table' => 'manufacturer', 'id' => 'manufacturer_id', 'description' => 'manufacturer_description', 'name' => 'name', 'status' => false],
			'information'  => ['table' => 'information', 'id' => 'information_id', 'description' => 'information_description', 'name' => 'title', 'status' => true],
			'news'         => ['table' => 'news', 'id' => 'news_id', 'description' => 'news_description', 'name' => 'title', 'status' => true]
		];
	}

	/**
	 * Check a single SEO keyword against the strict slug rules (a-z, 0-9 and single hyphens, 3 to 255 characters).
	 *
	 * @param string $keyword
	 *
	 * @return array<int, string> list of reason codes, empty when the keyword is valid
	 */
	public function validateKeyword(string $keyword): array {
		$reasons = [];

		if (preg_match('/\s/u', $keyword)) {
			$reasons[] = 'space';
		}

		if ($keyword !== mb_strtolower($keyword, 'UTF-8')) {
			$reasons[] = 'uppercase';
		}

		if (preg_match('/[^a-z0-9-]/', preg_replace('/\s/u', '', mb_strtolower($keyword, 'UTF-8')))) {
			$reasons[] = 'characters';
		}

		if (preg_match('/^-|-$/', trim($keyword))) {
			$reasons[] = 'hyphen_edge';
		}

		if (str_contains($keyword, '--')) {
			$reasons[] = 'hyphen_double';
		}

		if (mb_strlen(trim($keyword), 'UTF-8') < 3) {
			$reasons[] = 'short';
		}

		if (mb_strlen($keyword, 'UTF-8') > 255) {
			$reasons[] = 'long';
		}

		return $reasons;
	}

	/**
	 * Get Audit
	 *
	 * Checks every Product, Category, Manufacturer, Information and News entry for a missing, duplicated or
	 * mistyped SEO keyword, and every alias for a deleted or malformed target. The summary is cached for the Dashboard.
	 *
	 * @return array{rows: array<int, array<string, mixed>>, summary: array<string, mixed>}
	 */
	public function getAudit(): array {
		$types = $this->getAuditTypes();

		$type_list = implode('|', array_keys($types));

		$problems = ['missing', 'duplicate', 'mistyped', 'orphan'];

		$rows = [];

		$summary = ['total' => 0, 'time' => time(), 'types' => []];

		foreach ($problems as $problem) {
			$summary[$problem] = 0;
		}

		// Load every alias once and index it by entity and by keyword
		$by_entity = [];
		$by_keyword = [];
		$aliases = [];
		$flagged = [];

		$query = $this->db->query("SELECT url_alias_id, `query`, keyword FROM `" . DB_PREFIX . "url_alias` ORDER BY url_alias_id ASC");

		foreach ($query->rows as $result) {
			$result['type'] = 'other';
			$result['id'] = 0;

			if (preg_match('/^(' . $type_list . ')_id=([1-9][0-9]*)$/', $result['query'], $match)) {
				$result['type'] = $match[1];
				$result['id'] = (int)$match[2];

				$by_entity[$match[1]][(int)$match[2]][] = $result;
			} elseif (preg_match('/^(' . $type_list . ')s?_?id/i', $result['query'])) {
				$rows[] = $this->getAuditRow('mistyped', $result, 'query');
			}

			$keyword = mb_strtolower(trim($result['keyword']), 'UTF-8');

			if ($keyword !== '') {
				$by_keyword[$keyword][] = $result;
			}

			$aliases[] = $result;
		}

		// Missing, orphaned and multiple aliases per entity
		foreach ($types as $type => $info) {
			$sql = "SELECT `" . $info['id'] . "` AS `id`" . ($info['status'] ? ", `status`" : "") . " FROM `" . DB_PREFIX . $info['table'] . "`";

			$entities = [];

			foreach ($this->db->query($sql)->rows as $result) {
				$entities[(int)$result['id']] = $info['status'] ? (int)$result['status'] : 1;
			}

			foreach ($entities as $id => $status) {
				$entity_aliases = $by_entity[$type][$id] ?? [];

				if ($status) {
					$usable = false;

					foreach ($entity_aliases as $entity_alias) {
						if (trim($entity_alias['keyword']) !== '') {
							$usable = true;

							break;
						}
					}

					if (!$usable) {
						$rows[] = $this->getAuditRow('missing', $entity_aliases ? $entity_aliases[0] : ['type' => $type, 'id' => $id, 'url_alias_id' => 0, 'query' => $info['id'] . '=' . $id, 'keyword' => ''], $entity_aliases ? 'empty' : 'none');
					}
				}

				if (count($entity_aliases) > 1) {
					foreach ($entity_aliases as $entity_alias) {
						$flagged[$entity_alias['url_alias_id']] = true;

						$rows[] = $this->getAuditRow('duplicate', $entity_alias, 'multiple');
					}
				}
			}

			foreach (($by_entity[$type] ?? []) as $id => $entity_aliases) {
				if (!isset($entities[$id])) {
					foreach ($entity_aliases as $entity_alias) {
						$rows[] = $this->getAuditRow('orphan', $entity_alias, 'deleted');
					}
				}
			}
		}

		// Mistyped keywords (existing entries only, orphans are reported separately)
		foreach ($aliases as $alias) {
			if ($alias['type'] === 'other' || trim($alias['keyword']) === '') {
				continue;
			}

			$reasons = $this->validateKeyword($alias['keyword']);

			if ($reasons) {
				$rows[] = $this->getAuditRow('mistyped', $alias, implode(',', $reasons));
			}
		}

		// Keywords shared by more than one alias
		foreach ($by_keyword as $group) {
			if (count($group) > 1) {
				foreach ($group as $alias) {
					if (!isset($flagged[$alias['url_alias_id']])) {
						$rows[] = $this->getAuditRow('duplicate', $alias, 'keyword');
					}
				}
			}
		}

		// Stable order: problem, then type, then entry
		$order = array_flip($problems);

		usort($rows, static function (array $a, array $b) use ($order): int {
			return [$order[$a['problem']], $a['problem'] === 'duplicate' ? mb_strtolower($a['keyword'], 'UTF-8') : '', $a['type'], $a['id'], $a['url_alias_id']]
				<=> [$order[$b['problem']], $b['problem'] === 'duplicate' ? mb_strtolower($b['keyword'], 'UTF-8') : '', $b['type'], $b['id'], $b['url_alias_id']];
		});

		foreach ($rows as $row) {
			$summary['total']++;
			$summary[$row['problem']]++;

			if (!isset($summary['types'][$row['type']][$row['problem']])) {
				$summary['types'][$row['type']][$row['problem']] = 0;
			}

			$summary['types'][$row['type']][$row['problem']]++;
		}

		$this->cache->set('seo_url_audit', ['time' => $summary['time'], 'summary' => $summary]);

		return ['rows' => $rows, 'summary' => $summary];
	}

	/**
	 * Get Audit Summary
	 *
	 * Counts only, for the Dashboard. Served from cache for up to 5 minutes, and the cache is cleared
	 * whenever the SEO URL Manager changes an alias or the audit is run.
	 *
	 * @return array<string, mixed>
	 */
	public function getAuditSummary(): array {
		$cached = $this->cache->get('seo_url_audit');

		if (is_array($cached) && isset($cached['time'], $cached['summary']) && ((time() - (int)$cached['time']) < 300)) {
			return $cached['summary'];
		}

		return $this->getAudit()['summary'];
	}

	/**
	 * Get Audit Names
	 *
	 * @param string          $type
	 * @param array<int, int> $ids
	 *
	 * @return array<int, string> entry names keyed by ID
	 */
	public function getAuditNames(string $type, array $ids): array {
		$types = $this->getAuditTypes();

		$ids = array_filter(array_map('intval', $ids));

		if (!isset($types[$type]) || !$ids) {
			return [];
		}

		$info = $types[$type];

		$names = [];

		$query = $this->db->query("SELECT `" . $info['id'] . "` AS `id`, `" . $info['name'] . "` AS `name` FROM `" . DB_PREFIX . $info['description'] . "` WHERE `" . $info['id'] . "` IN (" . implode(',', $ids) . ") AND `language_id` = '" . (int)$this->config->get('config_language_id') . "'");

		foreach ($query->rows as $result) {
			$names[(int)$result['id']] = $result['name'];
		}

		return $names;
	}

	/**
	 * @param string               $problem
	 * @param array<string, mixed> $alias
	 * @param string               $detail
	 *
	 * @return array<string, mixed>
	 */
	private function getAuditRow(string $problem, array $alias, string $detail): array {
		return [
			'problem'      => $problem,
			'type'         => $alias['type'],
			'id'           => (int)$alias['id'],
			'url_alias_id' => (int)$alias['url_alias_id'],
			'query'        => $alias['query'],
			'keyword'      => $alias['keyword'],
			'detail'       => $detail
		];
	}
}
