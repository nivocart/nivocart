<?php
/**
 * Class ModelFeedPinterestFeed
 *
 * @package NivoCart
 */
class ModelFeedPinterestFeed extends Model {
	/**
	 * Functions Get
	 */
	/**
	 * Top level categories, each followed by its sub-categories (level 1).
	 *
	 * @return array<int, array{category_id: int, name: string, level: int}>
	 */
	public function getMappableCategories(): array {
		$query = $this->db->query("SELECT c.`category_id`, c.`parent_id`, cd.`name` FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`) WHERE cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' AND (c.`parent_id` = '0' OR c.`parent_id` IN (SELECT `category_id` FROM `" . DB_PREFIX . "category` WHERE `parent_id` = '0')) ORDER BY c.`sort_order` ASC, cd.`name` ASC");

		$children = [];
		$top = [];

		foreach ($query->rows as $row) {
			if ((int)$row['parent_id'] === 0) {
				$top[] = $row;
			} else {
				$children[(int)$row['parent_id']][] = $row;
			}
		}

		$data = [];

		foreach ($top as $row) {
			$data[] = [
				'category_id' => (int)$row['category_id'],
				'name'        => $row['name'],
				'level'       => 0
			];

			foreach ($children[(int)$row['category_id']] ?? [] as $child) {
				$data[] = [
					'category_id' => (int)$child['category_id'],
					'name'        => $child['name'],
					'level'       => 1
				];
			}
		}

		return $data;
	}
}
