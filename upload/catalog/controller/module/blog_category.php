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
		$this->load->model('blog/status');

		$blog_tables = $this->model_blog_status->checkBlog();

		if ($blog_tables) {
			$this->data['blog_categories'] = array();

			$blog_categories_1 = $this->model_blog_article->getCategories(0);

			foreach ($blog_categories_1 as $blog_category_1) {
				$level_2_data = array();

				$blog_categories_2 = $this->model_blog_article->getCategories($blog_category_1['blog_category_id']);

				foreach ($blog_categories_2 as $blog_category_2) {
					$level_2_data[] = array(
						'blog_category_id' => $blog_category_2['blog_category_id'],
						'name'             => $blog_category_2['name'],
						'href'             => $this->url->link('blog/category', 'blog_category_id=' . $blog_category_2['blog_category_id'], 'SSL')
					);
				}

				$this->data['blog_categories'][] = array(
					'blog_category_id' => $blog_category_1['blog_category_id'],
					'name'             => $blog_category_1['name'],
					'children'         => $level_2_data,
					'href'             => $this->url->link('blog/category', 'blog_category_id=' . $blog_category_1['blog_category_id'], 'SSL')
				);
			}
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
