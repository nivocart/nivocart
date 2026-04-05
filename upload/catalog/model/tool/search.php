<?php
class ModelToolSearch extends Model {

	public function liveSearch(int $customer_group_id, string $keywords, int $search_limit): array {
		// Check Limit is valid
		if ($search_limit && is_numeric($search_limit) && $search_limit > 0) {
			$limit = (int)$search_limit;
		} else {
			$limit = 10;
		}

		$parts = explode(' ', $keywords);

		$add = '';

		foreach ($parts as $part) {
			$add .= ' AND (LOWER(pd.name) LIKE "%' . mb_strtolower($this->db->escape($part), 'UTF-8') . '%"';
			$add .= ' OR LOWER(md.name) LIKE "%' . mb_strtolower($this->db->escape($part), 'UTF-8') . '%"';
			$add .= ' OR LOWER(pd.tag) LIKE "%' . mb_strtolower($this->db->escape($part), 'UTF-8') . '%"';
			$add .= ' OR LOWER(parameter.model) LIKE "%' . mb_strtolower($this->db->escape($part), 'UTF-8') . '%")';
		}

		$add = substr($add, 4);

		$sql = 'SELECT parameter.product_id, parameter.image, parameter.price, parameter.tax_class_id,';
		$sql .= ' (SELECT price FROM `' . DB_PREFIX . 'product_special` ps WHERE ps.product_id = parameter.product_id AND ps.customer_group_id = "' . (int)$customer_group_id . '" AND ((ps.date_start = "0000-00-00" OR ps.date_start < NOW()) AND (ps.date_end = "0000-00-00" OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC) AS `special`,';
		$sql .= ' parameter.model AS `model`, pd.name AS `name` FROM `' . DB_PREFIX . 'product` parameter';
		$sql .= ' LEFT OUTER JOIN `' . DB_PREFIX . 'manufacturer_description` md ON (parameter.manufacturer_id = md.manufacturer_id)';
		$sql .= ' LEFT JOIN `' . DB_PREFIX . 'product_description` pd ON (parameter.product_id = pd.product_id)';
		$sql .= ' LEFT JOIN `' . DB_PREFIX . 'product_to_store` p2s ON (parameter.product_id = p2s.product_id)';
		$sql .= ' WHERE ' . $add . ' AND parameter.status = 1';
		$sql .= ' AND pd.language_id = ' . (int)$this->config->get('config_language_id');
		$sql .= ' AND p2s.store_id = ' . (int)$this->config->get('config_store_id');
		$sql .= ' GROUP BY parameter.product_id';
		$sql .= ' ORDER BY LOWER(pd.name) ASC, LOWER(md.name) ASC, LOWER(pd.tag) ASC, LOWER(parameter.model) ASC';
		$sql .= ' LIMIT 0,' . (int)$limit;

		$search_result = $this->db->query($sql);

		return $search_result;
	}
}
