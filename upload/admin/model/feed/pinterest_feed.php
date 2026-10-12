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
	public function getTopCategories(): array {
		$query = $this->db->query("SELECT c.`category_id`, cd.`name` FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.`category_id` = cd.`category_id`) WHERE c.`parent_id` = '0' AND cd.`language_id` = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.`sort_order` ASC, cd.`name` ASC");

		return $query->rows;
	}
}
