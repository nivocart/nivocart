<?php
/**
 * Class ControllerCommonSeoUrl
 *
 * @package NivoCart
 */
class ControllerCommonSeoUrl extends Controller {
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

		// Get the RewriteBase for the canonical urls
		$this->load->model('tool/system');

		$RewriteBase = $this->model_tool_system->getRewriteBase();

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);

			// Remove any empty arrays from trailing
			if (mb_strlen(end($parts), 'UTF-8') === 0) {
				array_pop($parts);
			}

			// Video watch pages and the page listing them (no url_alias entries)
			$video_action = $this->getVideoRoute($parts);

			if ($video_action) {
				return $this->forward($video_action);
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE keyword = '" . $this->db->escape($part) . "'");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					// Get custom seo url links (static pages only)
					if ($this->config->get('config_seo_url')) {
						if (($query->row['query'] && $url[0] !== 'product_id') ||
							($query->row['query'] && $url[0] !== 'category_id') ||
							($query->row['query'] && $url[0] !== 'manufacturer_id') ||
							($query->row['query'] && $url[0] !== 'information_id') ||
							($query->row['query'] && $url[0] !== 'news_id') ||
							($query->row['query'] && $url[0] !== 'blog_article_id') ||
							($query->row['query'] && $url[0] !== 'blog_author_id') ||
							($query->row['query'] && $url[0] !== 'blog_category_id')
						) {
							$this->request->get['route'] = $query->row['query'];
						}
					}

					if ($url[0] === 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] === 'category_id') {
						if (!isset($this->request->get['path'])) {
							$this->request->get['path'] = $url[1];
						} else {
							$this->request->get['path'] .= '_' . $url[1];
						}
					}

					if ($url[0] === 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] === 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					if ($url[0] === 'news_id') {
						$this->request->get['news_id'] = $url[1];
					}

					if ($url[0] === 'blog_article_id') {
						$this->request->get['blog_article_id'] = $url[1];
					}

					if ($url[0] === 'blog_author_id') {
						$this->request->get['blog_author_id'] = $url[1];
					}

					if ($url[0] === 'gallery_id') {
						$this->request->get['gallery_id'] = $url[1];
					}

					if ($url[0] === 'blog_category_id') {
						if (!isset($this->request->get['blog_category_id'])) {
							$this->request->get['blog_category_id'] = $url[1];
						} else {
							$this->request->get['blog_category_id'] .= '_' . $url[1];
						}
					}
				}
			}

			// Assign route based on what GET params were populated
			if (isset($this->request->get['product_id'])) {
				// 301 redirect to canonical product URL, stripping manufacturer/category prefix
				$canonical = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'product_id=" . (int)$this->request->get['product_id'] . "'");

				if ($canonical->num_rows && $canonical->row['keyword']) {
					$requested = trim($this->request->get['_route_'], '/');

					if ($requested !== $canonical->row['keyword']) {
						header('Location:' . $RewriteBase . '/' . $canonical->row['keyword'], true, 301);
						exit;	// Exit required here
					}
				}

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
			} elseif (isset($this->request->get['gallery_id'])) {
				$this->request->get['route'] = 'modification/gallery/info';
			}

			if (isset($this->request->get['route'])) {
				return $this->forward($this->request->get['route']);
			}

			// No alias matched and no route was found: show the Not Found page (404) instead of the home page
			if (trim($this->request->get['_route_'], '/') !== '') {
				return $this->forward('error/not_found');
			}
		}

		if ((isset($this->request->get['route'])) && is_string($this->request->get['route']) && ($this->config->get('config_seo_url'))) {
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = '" . $this->db->escape($this->request->get['route']) . "'");

			if ($query->num_rows) {
				header('Location:/' . $query->row['keyword'], true, 301);
			}
		}
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$url = '';

		$data = [];

		parse_str($url_info['query'], $data);

		if (isset($data['route']) && ($data['route'] === 'product/video' || $data['route'] === 'product/video/watch')) {
			return $this->rewriteVideo($link, $url_info, $data);
		}

		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				if (($data['route'] === 'product/product' && $key === 'product_id') ||
					($data['route'] === 'product/product' && $key === 'manufacturer_id' && $key === 'product_id') ||
					(($data['route'] === 'product/manufacturer/info' || $data['route'] === 'product/product') && $key === 'manufacturer_id') ||
					($data['route'] === 'information/information' && $key === 'information_id') || ($data['route'] === 'information/news' && $key === 'news_id') ||
					($data['route'] === 'blog/article_info' && $key === 'blog_article_id') || ($data['route'] === 'blog/article_author' && $key === 'blog_author_id') ||
					($data['route'] === 'modification/gallery/info' && $key === 'gallery_id')
				) {
					$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];

						unset($data[$key]);
					}

				} elseif ($data['route'] === 'blog/category' && $key === 'blog_category_id' && !is_array($value)) {
					$blog_categories = explode("_", $value);

					foreach ($blog_categories as $blog_category) {
						$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'blog_category_id=" . (int)$blog_category . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						}
					}

					unset($data[$key]);

				} elseif ($key === 'path' && !is_array($value)) {
					$categories = explode('_', $value);

					foreach ($categories as $category) {
						$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'category_id=" . (int)$category . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						}
					}

					unset($data[$key]);

				} else {
					// Sanitize the call
					$clean_route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$data['route']);

					if ($this->config->get('config_seo_url')) {
						$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = '" . $this->db->escape($clean_route) . "'");

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
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
		} else {
			return $link;
		}
	}

	/**
	 * Route of a video address: "videos" or "video/<name>-<code>". Sets the route and the video code.
	 *
	 * @param array<int, string> $parts Parts of the requested path
	 *
	 * @return string Route, or an empty string when the path is not a video address
	 */
	private function getVideoRoute(array $parts): string {
		if (!$this->config->get('config_seo_url')) {
			return '';
		}

		if (count($parts) === 2 && $parts[0] === 'video' && preg_match('/^[a-z0-9-]+-([a-f0-9]{8})$/', $parts[1], $match)) {
			$this->request->get['video'] = $match[1];
			$this->request->get['route'] = 'product/video/watch';
		} elseif (count($parts) === 1 && $parts[0] === 'videos') {
			// An SEO URL entered in the administration with the same keyword keeps priority
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE keyword = 'videos'");

			if ($query->num_rows) {
				return '';
			}

			$this->request->get['route'] = 'product/video';
		} else {
			return '';
		}

		return $this->request->get['route'];
	}

	/**
	 * SEO address of the video pages.
	 *
	 * @param string                $link     Original link, returned when the video is unknown
	 * @param array<string, mixed>  $url_info Parts of the original link
	 * @param array<string, mixed>  $data     Query values of the original link
	 *
	 * @return string
	 */
	private function rewriteVideo(string $link, array $url_info, array $data): string {
		if ($data['route'] === 'product/video') {
			$path = 'videos';
		} else {
			$videos = videoWatchList($this->db, (int)$this->config->get('config_store_id'));

			$code = (isset($data['video']) && is_string($data['video'])) ? $data['video'] : '';

			if (!isset($videos[$code])) {
				return $link;
			}

			$path = videoWatchPath($videos[$code]);

			unset($data['video']);
		}

		unset($data['route']);

		$url = $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . '/' . $path;

		if ($data) {
			// The page placeholder of the pagination must stay readable
			$url .= '?' . str_replace('%7Bpage%7D', '{page}', http_build_query($data, '', '&amp;', PHP_QUERY_RFC3986));
		}

		return $url;
	}
}
