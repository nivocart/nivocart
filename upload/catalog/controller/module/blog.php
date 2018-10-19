<?php
class ControllerModuleBlog extends Controller {
	private $_name = 'blog';

	protected function index($setting) {
		static $module = 0;

		$this->language->load('module/' . $this->_name);

		$this->document->addStyle('catalog/view/theme/default/stylesheet/blog-custom.css');

		$this->data['heading_title'] = $this->language->get('heading_title');

		// Module
		$this->data['theme'] = $this->config->get($this->_name . '_theme');

		$this->data['title'] = $this->config->get($this->_name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$this->load->model('blog/article');
		$this->load->model('tool/image');

		$this->data['articles'] = array();

		$data = array(
			'start' => 0,
			'limit' => $setting['limit']
		);

		$results = $this->model_blog_article->getArticleModuleWise($data);

		foreach ($results as $result) {
			$description = utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, 300) . '...';

			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], 120, 120);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 120, 120);
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
				'featured_found'  => '',
				'description'     => $description,
				'date_added'      => date($this->language->get('text_date_format'), strtotime($result['date_modified'])),
				'href'            => $this->url->link('blog/article_info', 'blog_article_id=' . $result['blog_article_id'], 'SSL'),
				'author_href'     => $this->url->link('blog/article_author', 'blog_author_id=' . $result['blog_author_id'], 'SSL'),
				'comment_href'    => $this->url->link('blog/article_info', 'blog_article_id=' . $result['blog_article_id'] . '#comment-section', 'SSL'),
				'allow_comment'   => $result['allow_comment'],
				'total_comment'   => $total_comments
			);
		}

		$this->data['article_link'] = $this->url->link('blog/article_list', '', 'SSL');

		$this->data['text_not_found'] = $this->language->get('text_not_found');

		$this->data['button_continue_reading'] = $this->language->get('button_continue_reading');

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		if ($setting['position'] == 'content_left' || $setting['position'] == 'content_right') {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/blog_side.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/module/blog_side.tpl';
			} else {
				$this->template = 'default/template/module/blog_side.tpl';
			}
		} else {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/blog.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/module/blog.tpl';
			} else {
				$this->template = 'default/template/module/blog.tpl';
			}
		}

		$this->render();
	}
}
