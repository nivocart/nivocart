<?php
class ModelToolSeoUrl extends Controller {
	private $db;

	public function __construct($registry) {
		parent::__construct($registry);

		$this->db = new Dbmemory($registry->get('db'));
	}

	public function index() {
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);

			// Remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE keyword = '" . $this->db->escape($part) . "'");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					if ($url[0] == 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] == 'category_id') {
						if (!isset($this->request->get['path'])) {
							$this->request->get['path'] = $url[1];
						} else {
							$this->request->get['path'] .= '_' . $url[1];
						}
					}

					if ($url[0] == 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					if ($url[0] == 'news_id') {
						$this->request->get['news_id'] = $url[1];
					}

					if ($url[0] == 'blog_article_id') {
						$this->request->get['blog_article_id'] = $url[1];
					}

					if ($url[0] == 'blog_author_id') {
						$this->request->get['blog_author_id'] = $url[1];
					}

					if ($url[0] == 'blog_category_id') {
						if (!isset($this->request->get['blog_category_id'])) {
							$this->request->get['blog_category_id'] = $url[1];
						} else {
							$this->request->get['blog_category_id'] .= '_' . $url[1];
						}
					}

					if ($this->config->get('config_seo_url')) {
						if ($query->row['query'] && $url[0] != 'blog_category_id' && $url[0] != 'blog_author_id' && $url[0] != 'blog_article_id' && $url[0] != 'news_id' && $url[0] != 'information_id' && $url[0] != 'manufacturer_id' && $url[0] != 'category_id' && $url[0] != 'product_id') {
							$this->request->get['route'] = $query->row['query'];
						} else {
							$this->request->get['route'] = 'error/not_found';
						}
					}
				}
			}

			if (isset($this->request->get['product_id'])) {
				$this->request->get['route'] = 'product/product';
			} elseif (isset($this->request->get['path'])) {
				$this->request->get['route'] = 'product/category';
			} elseif (isset($this->request->get['manufacturer_id'])) {
				$this->request->get['route'] = 'product/manufacturer/info';
			} elseif (isset($this->request->get['information_id'])) {
				$this->request->get['route'] = 'information/information';
			} elseif (isset($this->request->get['news_id'])) {
				$this->request->get['route'] = 'information/news';
			} elseif (isset($this->request->get['blog_article_id'])) {
				$this->request->get['route'] = 'blog/article_info';
			} elseif (isset($this->request->get['blog_author_id'])) {
				$this->request->get['route'] = 'blog/article_author';
			} elseif (isset($this->request->get['blog_category_id'])) {
				$this->request->get['route'] = 'blog/category';
			}

			if (isset($this->request->get['route'])) {
				return $this->forward($this->request->get['route']);
			}
		}

		if ((isset($this->request->get['route'])) && ($this->config->get('config_seo_url'))) {
			$route = (string)$this->request->get['route'];

			// Sanitize the call
			$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = '" . $route . "'");

			if ($query->num_rows) {
				header('Location:/' . $query->row['keyword'], true, 301);
			}
		}
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$url = '';

		$data = array();

		parse_str($url_info['query'], $data);

		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				if (($data['route'] == 'product/product' && $key == 'product_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id') || ($data['route'] == 'information/news' && $key == 'news_id')) {
					$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];
						unset($data[$key]);
					}

				} elseif (($data['route'] == 'blog/article_info' && $key == 'blog_article_id') || ($data['route'] == 'blog/article_author' && $key == 'blog_author_id')) {
					$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "'");

					if ($query->num_rows) {
						$url .= '/' . $query->row['keyword'];
						unset($data[$key]);
					}

				} elseif ($data['route'] == 'blog/category' && $key == 'blog_category_id') {
					$blog_categories = explode("_", $value);

					foreach ($blog_categories as $blog_category) {
						$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'blog_category_id=" . (int)$blog_category . "'");

						if ($query->num_rows) {
							$url .= '/' . $query->row['keyword'];
						}
					}

					unset($data[$key]);

				} elseif ($key == 'path') {
					$categories = explode('_', $value);

					foreach ($categories as $category) {
						$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'category_id=" . (int)$category . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						} else {
							$url = '';
							break;
						}
					}

					unset($data[$key]);

				} else {
					// Sanitize the call
					$clean_route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$data['route']);

					if ($this->config->get('config_seo_url')) {
						$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = '" . $clean_route . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];

							unset($data[$key]);
						}
					}
				}
			}
		}

		if ($url) {
			unset($data['route']);

			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((string)$value);
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			if (isset($url_info['scheme']) && isset($url_info['host'])) {
				return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
			} else {
				return HTTP_CATALOG . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
			}
		} else {
			return $link;
		}
	}
}
