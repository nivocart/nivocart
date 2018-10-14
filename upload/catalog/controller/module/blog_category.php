<?php
class ControllerModuleBlogCategory extends Controller {
	private $_name = 'blog_category';

	public function index($setting) {
		$this->language->load('module/' . $this->_name);

		$this->data['heading_title'] = $this->language->get('heading_title');

		// Module
		$this->data['theme'] = $this->config->get($this->_name . '_theme');
		$this->data['title'] = $this->config->get($this->_name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$this->load->model('blog/article');

		$this->data['text_search_article'] = $this->language->get('text_search_article');

		$this->data['button_search'] = $this->language->get('button_search');

		if (isset($this->request->get['blog_category_id'])) {
			$parts = explode('_', (string)$this->request->get['blog_category_id']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$this->data['category_id'] = $parts[0];
		} else {
			$this->data['category_id'] = 0;
		}

		if (isset($parts[1])) {
			$this->data['child_id'] = $parts[1];
		} else {
			$this->data['child_id'] = 0;
		}

		$this->data['categories'] = array();

		$categories = $this->model_blog_article->getCategories(0);

		foreach ($categories as $category) {
			$children_data = array();

			$children = $this->model_blog_article->getCategories($category['blog_category_id']);

			foreach ($children as $child) {
				$article_total = $this->model_blog_article->getTotalArticles($child['blog_category_id']);

				$children_data[] = array(
					'category_id' => $child['blog_category_id'],
					'name'        => $child['name'],
					'href'        => $this->url->link('blog/category', 'blog_category_id=' . $category['blog_category_id'] . '_' . $child['blog_category_id'], 'SSL')
				);
			}

			$this->data['categories'][] = array(
				'blog_category_id' => $category['blog_category_id'],
				'name'             => $category['name'],
				'children'         => $children_data,
				'href'             => $this->url->link('blog/category', 'blog_category_id=' . $category['blog_category_id'], 'SSL')
			);
		}

		if (isset($this->request->get['blog_search'])) {
			$this->data['blog_search'] = $this->request->get['blog_search'];
		} else {
			$this->data['blog_search'] = '';
		}

		// Template
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/blog_category.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/blog_category.tpl';
		} else {
			$this->template = 'default/template/module/blog_category.tpl';
		}

		$this->render();
	}
}
