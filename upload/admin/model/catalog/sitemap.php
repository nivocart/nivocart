<?php
class ModelCatalogSitemap extends Model {

	public function getAllStores(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "store`");

		return $query->rows;
	}

	public function getAllCategories(int $store_id, $parent_id = 0): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$store_id . "' AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name) ASC");

		return $query->rows;
	}

	public function getAllProducts(int $store_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$store_id . "' AND p.status = '1' ORDER BY LCASE(pd.name) ASC");

		return $query->rows;
	}

	public function getAllManufacturers(int $store_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer` m  LEFT JOIN " . DB_PREFIX . "manufacturer_description md ON (m.manufacturer_id = md.manufacturer_id) LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE md.language_id = '" . (int)$this->config->get('config_language_id') . "' AND m2s.store_id = '" . (int)$store_id . "' AND m.status = '1' ORDER BY m.sort_order, LCASE(md.name) ASC");

		return $query->rows;
	}

	public function getAllNews(int $store_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "news` n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) LEFT JOIN " . DB_PREFIX . "news_to_store n2s ON (n.news_id = n2s.news_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n2s.store_id = '" . (int)$store_id . "' AND n.status = '1' ORDER BY LCASE(nd.title) ASC");

		return $query->rows;
	}

	public function getAllInformations(): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information` i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' AND i.status = '1' ORDER BY LCASE(id.title) ASC");

		return $query->rows;
	}

	public function getAllBlogArticles(int $store_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "blog_article` ba LEFT JOIN " . DB_PREFIX . "blog_article_description bad ON (ba.blog_article_id = bad.blog_article_id) LEFT JOIN " . DB_PREFIX . "blog_article_to_store ba2s ON (ba.blog_article_id = ba2s.blog_article_id) WHERE bad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ba2s.store_id = '" . (int)$store_id . "' AND ba.status = '1' ORDER BY ba.sort_order, LCASE(bad.article_title) ASC");

		return $query->rows;
	}

	public function getInformationStores(int $information_id): array {
		$information_store_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "information_to_store` WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_store_data[] = $result['store_id'];
		}

		return $information_store_data;
	}

	public function getStoreUrl(int $store_id) {
		$query = $this->db->query("SELECT DISTINCT url FROM `" . DB_PREFIX . "store` WHERE store_id = '" . (int)$store_id . "'");

		return $query->row;
	}
}
