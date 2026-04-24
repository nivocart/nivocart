<?php
/**
 * Class ModelDesignLayout
 *
 * @package NivoCart
 */
class ModelDesignLayout extends Model {
	/**
	 * Functions Add, Edit, Delete, Get, Check
	 */
	public function addLayout(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "layout` SET `name` = '" . $this->db->escape($data['name']) . "'");

		$layout_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_layout_id'] = $layout_id;

		if (isset($data['layout_route'])) {
			foreach ($data['layout_route'] as $layout_route) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "layout_route` SET layout_id = '" . (int)$layout_id . "', store_id = '" . (int)$layout_route['store_id'] . "', route = '" . $this->db->escape($layout_route['route']) . "'");
			}
		}

		$this->cache->delete('store');
	}

	public function editLayout(int $layout_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "layout` SET `name` = '" . $this->db->escape($data['name']) . "' WHERE layout_id = '" . (int)$layout_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout_route` WHERE layout_id = '" . (int)$layout_id . "'");

		if (isset($data['layout_route'])) {
			foreach ($data['layout_route'] as $layout_route) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "layout_route` SET layout_id = '" . (int)$layout_id . "', store_id = '" . (int)$layout_route['store_id'] . "', route = '" . $this->db->escape($layout_route['route']) . "'");
			}
		}

		$this->cache->delete('store');
	}

	public function deleteLayout(int $layout_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout` WHERE layout_id = '" . (int)$layout_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "layout_route` WHERE layout_id = '" . (int)$layout_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_layout` WHERE layout_id = '" . (int)$layout_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_layout` WHERE layout_id = '" . (int)$layout_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE layout_id = '" . (int)$layout_id . "'");

		// Check if Blog tables are installed
		$blog_tables = $this->checkBlogStatus();

		if ($blog_tables) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_article_to_layout` WHERE layout_id = '" . (int)$layout_id . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "blog_category_to_layout` WHERE layout_id = '" . (int)$layout_id . "'");
		}

		$this->cache->delete('store');
	}

	public function getLayout(int $layout_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "layout` WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row;
	}

	public function getLayouts(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "layout`";

		$sort_data = ['name'];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY `name`";
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

	public function getLayoutRoutes(int $layout_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "layout_route` WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->rows;
	}

	public function getTotalLayouts(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "layout`");

		return $query->row['total'];
	}

	public function getTotalRoutesByLayoutId(int $layout_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "layout_route` WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}

	// Blog
	protected function checkBlogStatus(): bool {
		$table_name = $this->db->escape('blog_%');

		$table = DB_PREFIX . $table_name;

		$query = $this->db->query("SHOW TABLES LIKE '{$table}'");

		if ($query->num_rows) {
			return true;
		} else {
			return false;
		}
	}
}
