<?php
class ControllerBlogSearch extends Controller {

	public function index() {
		$this->language->load('blog/article');

		if ($this->config->get('blog_heading')) {
			$this->document->setTitle($this->config->get('blog_heading'));
		} else {
			$this->document->setTitle($this->language->get('heading_title'));
		}

		$this->load->model('blog/article');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/theme/default/stylesheet/blog_custom.css');

		if ($this->config->get('blog_heading')) {
			$this->data['heading_title'] = $this->config->get('blog_heading');
		} else {
			$this->data['heading_title'] = $this->language->get('heading_title');
		}

		$this->data['articles'] = array();

		if (isset($this->request->get['blog_search'])) {
			$blog_search = $this->request->get['blog_search'];
		} else {
			$blog_search = '';
		}

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
			'blog_search' => $blog_search,
			'start'       => ($page - 1) * $limit,
			'limit'       => $limit
		);

		$blog_total = $this->model_blog_article->getTotalArticle($data);

		$results = $this->model_blog_article->getArticles($data);

		foreach ($results as $result) {
			$description = utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 300) . '...';

			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], 100, 100);
			} else {
				$image = '';
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
				'href'            => $this->url->link('blog/article/view', 'blog_article_id=' . $result['blog_article_id'], 'SSL'),
				'author_href'     => $this->url->link('blog/author', 'blog_author_id=' . $result['blog_author_id'], 'SSL'),
				'comment_href'    => $this->url->link('blog/article/view', 'blog_article_id=' . $result['blog_article_id'], 'SSL'),
				'allow_comment'   => $result['allow_comment'],
				'total_comment'   => $total_comments
			);
		}

		$this->data['button_continue_reading'] = $this->language->get('button_continue_reading');

		$this->data['text_no_found'] = $this->language->get('text_no_found');

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('blog/article', '', 'SSL'),
			'separator' => ' :: '
		);

		$pagination = new Pagination();

		$pagination->total = $blog_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('blog/article', '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/blog/article.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/blog/article.tpl';
		} else {
			$this->template = 'default/template/blog/article.tpl';
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
