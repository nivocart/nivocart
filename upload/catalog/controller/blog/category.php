<?php
class ControllerBlogCategory extends Controller {

	public function index() {
		$this->language->load('blog/article');

		$this->load->model('blog/article');

		if (isset($this->request->get['limit']) && ((int)$this->request->get['limit'] < 1)) {
			$this->request->get['limit'] = $this->config->get('config_catalog_limit');
		} elseif (isset($this->request->get['limit']) && ((int)$this->request->get['limit'] > 100)) {
			$this->request->get['limit'] = 100;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_catalog_limit');
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		);

		if (isset($this->request->get['blog_category_id'])) {
			$blog_category_id = $this->request->get['blog_category_id'];
		} else {
			$blog_category_id = 0;
		}

		$category_info = $this->model_blog_article->getCategory($blog_category_id);

		if ($category_info) {
			$this->document->setTitle($category_info['name']);

			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);

			$this->document->addStyle('catalog/view/theme/default/stylesheet/blog-system.css');

			$this->load->model('tool/image');

			$this->data['heading_title'] = $category_info['name'];

			$this->data['text_refine'] = $this->language->get('text_refine');

			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['author_details'] = $this->config->get('blog_author_details');

			// Set the last category breadcrumb
			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if ($category_info['parent_id'] > 0) {
				$parent_name = $this->model_blog_article->getCategoryByName($category_info['parent_id']);

				$this->data['breadcrumbs'][] = array(
					'text'      => $parent_name,
					'href'      => $this->url->link('blog/category', 'blog_category_id=' . $category_info['parent_id'], 'SSL'),
					'separator' => $this->language->get('text_separator')
				);

				$this->data['breadcrumbs'][] = array(
					'text'      => $category_info['name'],
					'href'      => $this->url->link('blog/category', 'blog_category_id=' . $blog_category_id . $url, 'SSL'),
					'separator' => $this->language->get('text_separator')
				);

			} else {
				$this->data['breadcrumbs'][] = array(
					'text'      => $category_info['name'],
					'href'      => $this->url->link('blog/category', 'blog_category_id=' . $blog_category_id . $url, 'SSL'),
					'separator' => $this->language->get('text_separator')
				);
			}

			if ($category_info['image']) {
				$this->data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
			} else {
				$this->data['thumb'] = '';
			}

			if ($category_info['description']) {
				$this->data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
			} else {
				$this->data['description'] = '';
			}

			// Blog Category Sub-categories
			$this->data['categories'] = array();

			$results = $this->model_blog_article->getCategories($blog_category_id);

			foreach ($results as $result) {
				$category_name = $this->model_blog_article->getCategoryByName($result['blog_category_id']);
				$article_total = $this->model_blog_article->getTotalArticles($result['blog_category_id']);

				$this->data['categories'][] = array(
					'name' => $category_name . ' (' . $article_total . ')',
					'href' => $this->url->link('blog/category', 'blog_category_id=' . $result['blog_category_id'] . $url, 'SSL')
				);
			}

			// Blog Category articles
			$this->data['articles'] = array();

			if ($category_info['blog_category_column']) {
				$limit = $category_info['blog_category_column'];
			} else {
				$limit = 5;
			}

			$data = array(
				'blog_category_id' => $blog_category_id,
				'start'            => ($page - 1) * $limit,
				'limit'            => $limit
			);

			$blog_total = $this->model_blog_article->getTotalArticleCategoryWise($data);

			$results = $this->model_blog_article->getArticleCategoryWise($data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], 100, 100);
				} else {
					$image = '';
				}

				if ($result['description']) {
					$description = utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 300) . '...';
				} else {
					$description = '';
				}

				$total_comments = $this->model_blog_article->getTotalComments($result['blog_article_id']);

				if ($total_comments != 1) {
					$total_comments .= $this->language->get('text_comments');
				} else {
					$total_comments .= $this->language->get('text_comment');
				}

				$this->data['articles'][] = array(
					'blog_article_id' => $result['blog_article_id'],
					'article_title'   => $result['article_title'],
					'author_name'     => $result['author_name'],
					'image'           => $image,
					'date_added'      => date($this->language->get('text_date_format'), strtotime($result['date_modified'])),
					'description'     => $description,
					'href'            => $this->url->link('blog/article_info', 'blog_article_id=' . $result['blog_article_id'], 'SSL'),
					'author_href'     => $this->url->link('blog/article_author', 'blog_author_id=' . $result['blog_author_id'], 'SSL'),
					'comment_href'    => $this->url->link('blog/article_info', 'blog_article_id=' . $result['blog_article_id'], 'SSL'),
					'allow_comment'   => $result['allow_comment'],
					'total_comment'   => $total_comments
				);
			}

			$this->data['button_continue_reading'] = $this->language->get('button_continue_reading');

			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $blog_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('blog/category', 'blog_category_id=' . $this->request->get['blog_category_id'] . $url, 'SSL');

			$this->data['pagination'] = $pagination->render();

			$this->data['limit'] = $limit;

			$this->data['continue'] = $this->url->link('common/home', '', 'SSL');

			// Theme
			$this->data['template'] = $this->config->get('config_template');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/blog/article_category.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/blog/article_category.tpl';
			} else {
				$this->template = 'default/template/blog/article_category.tpl';
			}

			$this->children = array(
				'common/content_higher',
				'common/content_high',
				'common/content_left',
				'common/content_right',
				'common/content_low',
				'common/content_lower',
				'common/footer',
				'common/header'
			);

			$this->response->setOutput($this->render());

		} else {
			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_category_error'),
				'href'      => $this->url->link('blog/article_list', $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['heading_title'] = $this->language->get('text_category_error');

			$this->data['text_error'] = $this->language->get('text_category_error');

			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['continue'] = $this->url->link('common/home', '', 'SSL');

			// Theme
			$this->data['template'] = $this->config->get('config_template');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
			}

			$this->children = array(
				'common/content_higher',
				'common/content_high',
				'common/content_left',
				'common/content_right',
				'common/content_low',
				'common/content_lower',
				'common/footer',
				'common/header'
			);

			$this->response->addheader($this->request->server['SERVER_PROTOCOL'] . ' 404 not found');
			$this->response->setOutput($this->render());
		}
	}
}
