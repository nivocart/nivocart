<?php
/**
 * Class ModelToolSitemap
 *
 * @package NivoCart
 */
class ModelToolSitemap extends Model {
	/**
	 * Functions Generate, Get
	 */
	public function generateText() {
		$this->language->load('tool/sitemap');

		$parent_id = 0;
		$current_path = '';

		$output = '';

		// Generating TEXT sitemap
		$fp = fopen('../sitemap.txt', 'w+');
		fwrite($fp, $this->getTextLinks());
		fwrite($fp, $this->getTextCategories($parent_id, $current_path));
		fclose($fp);

		$output .= "<img src=\"view/image/success.png\" alt=\"\" /> &nbsp; <b>" . HTTP_CATALOG . "sitemap.txt</b><br /><br />";

		return $output;
	}

	public function generateXml() {
		$this->language->load('tool/sitemap');

		$parent_id = 0;
		$current_path = '';

		$output = '';

		// Generating XML sitemap
		$fp = fopen('../sitemap.xml', 'w+');
		fwrite($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r");
		fwrite($fp, "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r");
		fwrite($fp, $this->getCommonPages());
		fwrite($fp, $this->getCategories($parent_id, $current_path));
		fwrite($fp, $this->getProducts());
		fwrite($fp, $this->getManufacturers());
		fwrite($fp, $this->getNews());
		fwrite($fp, $this->getInformationPages());
		fwrite($fp, $this->getBlogArticles());
		fwrite($fp, "</urlset>");
		fclose($fp);

		$output .= "<img src=\"view/image/success.png\" alt=\"\" /> &nbsp; <b>" . HTTP_CATALOG . "sitemap.xml</b><br /><br />";

		return $output;
	}

	public function generateGzip() {
		$this->language->load('tool/sitemap');

		$output = '';

		// Generating GZIP sitemap (from XML)
		if ($fp_out = gzopen('../sitemap.xml.gz', 'wb9')) {
			if ($fp_in = fopen('../sitemap.xml', 'rb')) {
				while (!feof($fp_in)) {
					gzwrite($fp_out, fread($fp_in, 10000));
				}
				fclose($fp_in);
			}

			gzclose($fp_out);
		}

		$output .= "<img src=\"view/image/success.png\" alt=\"\" /> &nbsp; <b>" . HTTP_CATALOG . "sitemap.xml.gz</b><br /><br />";

		return $output;
	}

	// Server base URL
	protected function getBase() {
		if ((isset($this->request->server['HTTPS']) && in_array($this->request->server['HTTPS'], ['on', '1'], true)) ||
			(isset($this->request->server['SERVER_PORT']) && $this->request->server['SERVER_PORT'] === '443') ||
			(isset($this->request->server['HTTP_X_FORWARDED_PROTO']) && $this->request->server['HTTP_X_FORWARDED_PROTO'] === 'https')
		) {
			$base = HTTPS_CATALOG;
		} else {
			$base = HTTP_CATALOG;
		}

		return $base;
	}

	/**
	 * Check whether a given route is enabled in the sitemap settings.
	 * Returns true when no setting exists yet (backwards-compatible default = all enabled).
	 */
	protected function isPageEnabled(string $route): bool {
		$pages = $this->config->get('config_sitemap_pages');

		if (empty($pages)) {
			return true;
		}

		return in_array($route, (array)$pages, true);
	}

	// Generators
	protected function getCommonPages() {
		$this->load->model('catalog/sitemap');

		$base = $this->getBase();

		$output = '';

		// Routes mapped to the config_sitemap_pages keys
		$static_routes = [
			'common/home'            => ['index.php', 'index.php?route=common/home'],
			'information/contact'    => ['index.php?route=information/contact'],
			'information/quote'      => ['index.php?route=information/quote'],
			'information/sitemap'    => ['index.php?route=information/sitemap'],
			'account/login'          => ['index.php?route=account/login'],
			'account/register'       => ['index.php?route=account/register'],
			'product/search'         => ['index.php?route=product/search'],
			'product/special'        => ['index.php?route=product/special'],
			'product/product_list'   => ['index.php?route=product/product_list'],
			'product/product_wall'   => ['index.php?route=product/product_wall'],
			'product/review_list'    => ['index.php?route=product/review_list'],
			'product/category_list'  => ['index.php?route=product/category_list'],
		];

		foreach ($static_routes as $route => $paths) {
			if ($this->isPageEnabled($route)) {
				foreach ($paths as $path) {
					$output .= $this->standardLinkNode($base . $path);
				}
			}
		}

		$stores_pag = $this->model_catalog_sitemap->getAllStores();

		if ($stores_pag) {
			foreach ($stores_pag as $store_pag) {
				if ((int)$store_pag['store_id'] !== 0) {
					$store_url = $store_pag['url'];

					foreach ($static_routes as $route => $paths) {
						if ($this->isPageEnabled($route)) {
							foreach ($paths as $path) {
								$output .= $this->standardLinkNode($store_url . $path);
							}
						}
					}
				}
			}
		}

		return $output;
	}

	protected function getCategories(int $parent_id, $current_path = '') {
		$this->load->model('catalog/sitemap');

		$base = $this->getBase();

		$store_id = 0;

		$output = '';

		$results = $this->model_catalog_sitemap->getAllCategories($parent_id, $store_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			$output .= $this->generateLinkNode($base . 'index.php?route=product/category&path=' . $new_path, "monthly", "1.0");
			$output .= $this->getCategories($result['category_id'], $new_path);
		}

		$stores_cat = $this->model_catalog_sitemap->getAllStores();

		if ($stores_cat) {
			foreach ($stores_cat as $store_cat) {
				if ((int)$store_cat['store_id'] !== 0) {
					$results = $this->model_catalog_sitemap->getAllCategories($parent_id, $store_cat['store_id']);

					foreach ($results as $result) {
						$store_url = $store_cat['url'];

						if (!$current_path) {
							$new_path = $result['category_id'];
						} else {
							$new_path = $current_path . '_' . $result['category_id'];
						}

						$output .= $this->generateLinkNode($store_url . 'index.php?route=product/category&path=' . $new_path, "monthly", "1.0");
						$output .= $this->getCategories($result['category_id'], $new_path);
					}
				}
			}
		}

		return $output;
	}

	protected function getProducts() {
		$this->load->model('catalog/sitemap');

		$base = $this->getBase();

		$store_id = 0;

		$output = '';

		$results = $this->model_catalog_sitemap->getAllProducts($store_id);

		foreach ($results as $result) {
			$output .= $this->generateLinkNode($base . 'index.php?route=product/product&product_id=' . $result['product_id'], "weekly", "1.0");
		}

		$stores_pro = $this->model_catalog_sitemap->getAllStores();

		if ($stores_pro) {
			foreach ($stores_pro as $store_pro) {
				if ((int)$store_pro['store_id'] !== 0) {
					$results = $this->model_catalog_sitemap->getAllProducts($store_pro['store_id']);

					foreach ($results as $result) {
						$store_url = $store_pro['url'];

						$output .= $this->generateLinkNode($store_url . 'index.php?route=product/product&product_id=' . $result['product_id'], "weekly", "1.0");
					}
				}
			}
		}

		return $output;
	}

	protected function getManufacturers() {
		$this->load->model('catalog/sitemap');

		$base = $this->getBase();

		$store_id = 0;

		$output = '';

		$results = $this->model_catalog_sitemap->getAllManufacturers($store_id);

		foreach ($results as $result) {
			$output .= $this->generateLinkNode($base . 'index.php?route=product/manufacturer/info&manufacturer_id=' . $result['manufacturer_id'], "weekly", "1.0");
		}

		$stores_man = $this->model_catalog_sitemap->getAllStores();

		if ($stores_man) {
			foreach ($stores_man as $store_man) {
				if ((int)$store_man['store_id'] !== 0) {
					$store_url = $store_man['url'];

					$results = $this->model_catalog_sitemap->getAllManufacturers($store_man['store_id']);

					foreach ($results as $result) {
						$output .= $this->generateLinkNode($store_url . 'index.php?route=product/manufacturer/info&manufacturer_id=' . $result['manufacturer_id'], "weekly", "1.0");
					}
				}
			}
		}

		return $output;
	}

	protected function getNews() {
		$this->load->model('catalog/sitemap');

		$base = $this->getBase();

		$store_id = 0;

		$output = '';

		$results = $this->model_catalog_sitemap->getAllNews($store_id);

		foreach ($results as $result) {
			$output .= $this->generateLinkNode($base . 'index.php?route=information/news&news_id=' . $result['news_id'], "weekly", "1.0");
		}

		$stores_new = $this->model_catalog_sitemap->getAllStores();

		if ($stores_new) {
			foreach ($stores_new as $store_new) {
				if ((int)$store_new['store_id'] !== 0) {
					$store_url = $store_new['url'];

					$results = $this->model_catalog_sitemap->getAllNews($store_new['store_id']);

					foreach ($results as $result) {
						$output .= $this->generateLinkNode($store_url . 'index.php?route=information/news&news_id=' . $result['news_id'], "weekly", "1.0");
					}
				}
			}
		}

		return $output;
	}

	protected function getInformationPages() {
		$this->load->model('catalog/sitemap');

		$base = $this->getBase();

		$store_id = 0;

		$output = '';

		$results = $this->model_catalog_sitemap->getAllInformations();

		foreach ($results as $result) {
			$output .= $this->generateLinkNode($base . 'index.php?route=information/information&information_id=' . $result['information_id'], "monthly", "1.0");
		}

		$stores_inf = $this->model_catalog_sitemap->getAllStores();

		if ($stores_inf) {
			foreach ($stores_inf as $store_inf) {
				if ((int)$store_inf['store_id'] !== 0) {
					$results = $this->model_catalog_sitemap->getAllInformations();

					foreach ($results as $result) {
						$store_info_ids = $this->model_catalog_sitemap->getInformationStores($result['information_id']);

						foreach ($store_info_ids as $store_info_id) {
							if ((int)$store_info_id !== 0) {
								$store_url = $this->model_catalog_sitemap->getStoreUrl($store_info_id);

								$output .= $this->generateLinkNode($store_url . 'index.php?route=information/information&information_id=' . $result['information_id'], "monthly", "1.0");
							}
						}
					}
				}
			}
		}

		return $output;
	}

	protected function getBlogArticles() {
		$this->load->model('catalog/sitemap');
		$this->load->model('blog/status');

		$base = $this->getBase();

		$store_id = 0;

		$output = '';

		// Check Blog Tables
		$blog_tables = $this->model_blog_status->checkBlog();

		if ($blog_tables) {
			$results = $this->model_catalog_sitemap->getAllBlogArticles($store_id);

			foreach ($results as $result) {
				$output .= $this->generateLinkNode($base . 'index.php?route=blog/article_info&blog_article_id=' . $result['blog_article_id'], "weekly", "1.0");
			}

			$stores_blog = $this->model_catalog_sitemap->getAllStores();

			if ($stores_blog) {
				foreach ($stores_blog as $store_blog) {
					if ((int)$store_blog['store_id'] !== 0) {
						$results = $this->model_catalog_sitemap->getAllBlogArticles($store_blog['store_id']);

						foreach ($results as $result) {
							$store_url = $store_blog['url'];

							$output .= $this->generateLinkNode($store_url . 'index.php?route=blog/article_info&blog_article_id=' . $result['blog_article_id'], "weekly", "1.0");
						}
					}
				}
			}
		}

		return $output;
	}

	// Text Sitemap - Links
	protected function getTextLinks() {
		$this->load->model('catalog/sitemap');

		$base = $this->getBase();

		$store_id = 0;

		$output = '';

		// Static common pages — filtered by sitemap settings
		$static_routes = [
			'common/home'           => ['index.php', 'index.php?route=common/home'],
			'information/contact'   => ['index.php?route=information/contact'],
			'information/quote'     => ['index.php?route=information/quote'],
			'information/sitemap'   => ['index.php?route=information/sitemap'],
			'account/login'         => ['index.php?route=account/login'],
			'account/register'      => ['index.php?route=account/register'],
			'product/search'        => ['index.php?route=product/search'],
			'product/special'       => ['index.php?route=product/special'],
			'product/product_list'  => ['index.php?route=product/product_list'],
			'product/product_wall'  => ['index.php?route=product/product_wall'],
			'product/review_list'   => ['index.php?route=product/review_list'],
			'product/category_list' => ['index.php?route=product/category_list'],
		];

		foreach ($static_routes as $route => $paths) {
			if ($this->isPageEnabled($route)) {
				foreach ($paths as $path) {
					$output .= mb_convert_encoding($base . $path, 'UTF-8') . "\r";
				}
			}
		}

		$stores_pag = $this->model_catalog_sitemap->getAllStores();

		if ($stores_pag) {
			foreach ($stores_pag as $store_pag) {
				if ((int)$store_pag['store_id'] !== 0) {
					$store_url = $store_pag['url'];

					foreach ($static_routes as $route => $paths) {
						if ($this->isPageEnabled($route)) {
							foreach ($paths as $path) {
								$output .= mb_convert_encoding($store_url . $path, 'UTF-8') . "\r";
							}
						}
					}
				}
			}
		}

		$this->load->model('tool/seo_url');

		// Products
		$store_id = 0;

		$products = $this->model_catalog_sitemap->getAllProducts($store_id);

		foreach ($products as $product) {
			$link_product = mb_convert_encoding($base . 'index.php?route=product/product&product_id=' . $product['product_id'], 'UTF-8');

			$link = $this->model_tool_seo_url->rewrite($link_product);

			$output .= str_replace("&", "&amp;", $link) . "\r";
		}

		$stores_pro = $this->model_catalog_sitemap->getAllStores();

		if ($stores_pro) {
			foreach ($stores_pro as $store_pro) {
				if ((int)$store_pro['store_id'] !== 0) {
					$products = $this->model_catalog_sitemap->getAllProducts($store_pro['store_id']);

					foreach ($products as $product) {
						$store_url = $store_pro['url'];

						$link_product = mb_convert_encoding($store_url . 'index.php?route=product/product&product_id=' . $product['product_id'], 'UTF-8');

						$link = $this->model_tool_seo_url->rewrite($link_product);

						$output .= str_replace("&", "&amp;", $link) . "\r";
					}
				}
			}
		}

		// Manufacturers
		$store_id = 0;

		$manufacturers = $this->model_catalog_sitemap->getAllManufacturers($store_id);

		foreach ($manufacturers as $manufacturer) {
			$link_manufacturer = mb_convert_encoding($base . 'index.php?route=product/manufacturer/info&manufacturer_id=' . $manufacturer['manufacturer_id'], 'UTF-8');

			$link = $this->model_tool_seo_url->rewrite($link_manufacturer);

			$output .= str_replace("&", "&amp;", $link) . "\r";
		}

		$stores_man = $this->model_catalog_sitemap->getAllStores();

		if ($stores_man) {
			foreach ($stores_man as $store_man) {
				if ((int)$store_man['store_id'] !== 0) {
					$store_url = $store_man['url'];

					$manufacturers = $this->model_catalog_sitemap->getAllManufacturers($store_man['store_id']);

					foreach ($manufacturers as $manufacturer) {
						$link_manufacturer = mb_convert_encoding($store_url . 'index.php?route=product/manufacturer/info&manufacturer_id=' . $manufacturer['manufacturer_id'], 'UTF-8');

						$link = $this->model_tool_seo_url->rewrite($link_manufacturer);

						$output .= str_replace("&", "&amp;", $link) . "\r";
					}
				}
			}
		}

		// News
		$store_id = 0;

		$news = $this->model_catalog_sitemap->getAllNews($store_id);

		foreach ($news as $new) {
			$link_news = mb_convert_encoding($base . 'index.php?route=information/news&news_id=' . $new['news_id'], 'UTF-8');

			$link = $this->model_tool_seo_url->rewrite($link_news);

			$output .= str_replace("&", "&amp;", $link) . "\r";
		}

		$stores_new = $this->model_catalog_sitemap->getAllStores();

		if ($stores_new) {
			foreach ($stores_new as $store_new) {
				if ((int)$store_new['store_id'] !== 0) {
					$store_url = $store_new['url'];

					$news = $this->model_catalog_sitemap->getAllNews($store_new['store_id']);

					foreach ($news as $new) {
						$link_news = mb_convert_encoding($store_url . 'index.php?route=information/news&news_id=' . $new['news_id'], 'UTF-8');

						$link = $this->model_tool_seo_url->rewrite($link_news);

						$output .= str_replace("&", "&amp;", $link) . "\r";
					}
				}
			}
		}

		// Information
		$informations = $this->model_catalog_sitemap->getAllInformations();

		foreach ($informations as $information) {
			$link_information = mb_convert_encoding($base . 'index.php?route=information/information&information_id=' . $information['information_id'], 'UTF-8');

			$link = $this->model_tool_seo_url->rewrite($link_information);

			$output .= str_replace("&", "&amp;", $link) . "\r";
		}

		$stores_inf = $this->model_catalog_sitemap->getAllStores();

		if ($stores_inf) {
			foreach ($stores_inf as $store_inf) {
				if ((int)$store_inf['store_id'] !== 0) {
					$store_info_ids = [];

					$informations = $this->model_catalog_sitemap->getAllInformations();

					foreach ($informations as $information) {
						$store_info_ids = $this->model_catalog_sitemap->getInformationStores($information['information_id']);

						foreach ($store_info_ids as $store_info_id) {
							if ($store_info_id !== 0) {
								$store_url = $this->model_catalog_sitemap->getStoreUrl($store_info_id);

								$link_information = mb_convert_encoding($store_url . 'index.php?route=information/information&information_id=' . $information['information_id'], 'UTF-8');

								$link = $this->model_tool_seo_url->rewrite($link_information);

								$output .= str_replace("&", "&amp;", $link) . "\r";
							}
						}
					}
				}
			}
		}

		// Blog Articles
		$this->load->model('blog/status');

		$blog_tables = $this->model_blog_status->checkBlog();

		if ($blog_tables) {
			$store_id = 0;

			$blog_articles = $this->model_catalog_sitemap->getAllBlogArticles($store_id);

			foreach ($blog_articles as $blog_article) {
				$link_blogs = mb_convert_encoding($base . 'index.php?route=blog/article_info&blog_article_id=' . $blog_article['blog_article_id'], 'UTF-8');

				$link = $this->model_tool_seo_url->rewrite($link_blogs);

				$output .= str_replace("&", "&amp;", $link) . "\r";
			}

			$stores_blog_article = $this->model_catalog_sitemap->getAllStores();

			if ($stores_blog_article) {
				foreach ($stores_blog_article as $store_blog_article) {
					if ((int)$store_blog_article['store_id'] !== 0) {
						$store_url = $store_blog_article['url'];

						$blog_articles = $this->model_catalog_sitemap->getAllBlogArticles($store_blog_article['store_id']);

						foreach ($blog_articles as $blog_article) {
							$link_blogs = mb_convert_encoding($store_url . 'index.php?route=blog/article_info&blog_article_id=' . $blog_article['blog_article_id'], 'UTF-8');

							$link = $this->model_tool_seo_url->rewrite($link_blogs);

							$output .= str_replace("&", "&amp;", $link) . "\r";
						}
					}
				}
			}
		}

		return $output;
	}

	// Text Sitemap - Categories
	protected function getTextCategories(int $parent_id, $current_path = '') {
		$this->load->model('catalog/sitemap');
		$this->load->model('tool/seo_url');

		$base = $this->getBase();

		$store_id = 0;

		$output = '';

		$results = $this->model_catalog_sitemap->getAllCategories($parent_id, $store_id);

		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			$link_category = mb_convert_encoding($base . 'index.php?route=product/category&path=' . $new_path, 'UTF-8');

			$link = $this->model_tool_seo_url->rewrite($link_category);

			$output .= str_replace("&", "&amp;", $link) . "\r";

			$output .= $this->getTextCategories($result['category_id'], $new_path);
		}

		$stores_cat = $this->model_catalog_sitemap->getAllStores();

		if ($stores_cat) {
			foreach ($stores_cat as $store_cat) {
				if ((int)$store_cat['store_id'] !== 0) {
					$results = $this->model_catalog_sitemap->getAllCategories($parent_id, $store_cat['store_id']);

					foreach ($results as $result) {
						$store_url = $store_cat['url'];

						if (!$current_path) {
							$new_path = $result['category_id'];
						} else {
							$new_path = $current_path . '_' . $result['category_id'];
						}

						$link_category = mb_convert_encoding($store_url . 'index.php?route=product/category&path=' . $new_path, 'UTF-8');

						$link = $this->model_tool_seo_url->rewrite($link_category);

						$output .= str_replace("&", "&amp;", $link) . "\r";

						$output .= $this->getTextCategories($result['category_id'], $new_path);
					}
				}
			}
		}

		return $output;
	}

	protected function generateLinkNode($link, $changefreq = 'monthly', $priority = '1.0') {
		$this->load->model('tool/seo_url');

		$link_node = $this->model_tool_seo_url->rewrite($link);

		$link_url = str_replace("&", "&amp;", $link_node);

		$output = "<url>\r";
		$output .= "<loc>" . $link_url . "</loc>\r";
		$output .= "<lastmod>" . date("Y-m-d") . "</lastmod>\r";
		$output .= "<changefreq>" . $changefreq . "</changefreq>\r";
		$output .= "<priority>" . $priority . "</priority>\r";
		$output .= "</url>\r";

		return $output;
	}

	protected function standardLinkNode($link, $changefreq = 'monthly', $priority = '1.0') {
		$link_url = str_replace("&", "&amp;", $link);

		$output = "<url>\r";
		$output .= "<loc>" . $link_url . "</loc>\r";
		$output .= "<lastmod>" . date("Y-m-d") . "</lastmod>\r";
		$output .= "<changefreq>" . $changefreq . "</changefreq>\r";
		$output .= "<priority>" . $priority . "</priority>\r";
		$output .= "</url>\r";

		return $output;
	}
}
