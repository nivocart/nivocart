<?php
class ControllerBlogArticleList extends Controller {

	public function index() {
		$this->language->load('blog/article');

		$this->document->setTitle(($this->config->get('blog_heading')) ? $this->config->get('blog_heading') : $this->language->get('heading_title'));

		$this->document->addStyle('catalog/view/theme/default/stylesheet/blog-system.css');

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('blog/article_list', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		);

		$this->load->model('blog/article');
		$this->load->model('tool/image');

		$this->data['heading_title'] = ($this->config->get('blog_heading')) ? $this->config->get('blog_heading') : $this->language->get('heading_title');

		$this->data['author_details'] = $this->config->get('blog_author_details');

		$this->data['articles'] = array();

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_catalog_limit');
		}

		$data = array(
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		);

		$blog_total = $this->model_blog_article->getTotalArticle($data);

		$results = $this->model_blog_article->getArticles($data);

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

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$pagination = new Pagination();
		$pagination->total = $blog_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('blog/article_list', $url, 'SSL');

		$this->data['pagination'] = $pagination->render();

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/blog/article_list.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/blog/article_list.tpl';
		} else {
			$this->template = 'default/template/blog/article_list.tpl';
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
	}
}
