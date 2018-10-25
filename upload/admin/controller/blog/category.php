<?php
class ControllerBlogCategory extends Controller {
	private $error = array();

	public function index() {
		$url = $this->request->get['route'];

		if ($this->validateDatabase()) {
			$this->language->load('blog/install');

			$this->document->setTitle($this->language->get('error_database'));

			$this->data['install_blog'] = $this->url->link('modification/blog', 'token=' . $this->session->data['token'] . '&url=' . $url, 'SSL');

			$this->data['text_install_message'] = $this->language->get('text_install_message');
			$this->data['text_upgrade'] = $this->language->get('text_upgrade');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			);

			$this->template = 'blog/notification.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);

			$this->response->setOutput($this->render());
		} else {
			$this->getData();
		}
	}

	public function validateDatabase() {
		$database_not_found = $this->getChild('blog/install');

		if ($database_not_found) {
			return true;
		}

		return false;
	}

	public function getData() {
		$this->language->load('blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/category');

		$this->getList();
	}

	public function insert() {
		$this->language->load('blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_blog_category->addCategory($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->post['apply'])) {
				$blog_category_id = $this->session->data['new_blog_category_id'];

				if ($blog_category_id) {
					unset($this->session->data['new_blog_category_id']);

					$this->redirect($this->url->link('blog/category/update', 'token=' . $this->session->data['token'] . '&blog_category_id=' . $blog_category_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('blog/category', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function update() {
		$this->language->load('blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_blog_category->editCategory($this->request->get['blog_category_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->post['apply'])) {
				$blog_category_id = $this->request->get['blog_category_id'];

				if ($blog_category_id) {
					$this->redirect($this->url->link('blog/category/update', 'token=' . $this->session->data['token'] . '&blog_category_id=' . $blog_category_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('blog/category', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->language->load('blog/category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('blog/category');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $blog_category_id) {
				$this->model_blog_category->deleteCategory($blog_category_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->redirect($this->url->link('blog/category', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'bcd.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('blog/category', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		);

		$this->data['insert'] = $this->url->link('blog/category/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('blog/category/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		// Pagination
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$this->data['categories'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$category_total = $this->model_blog_category->getTotalCategories($data);

		$results = $this->model_blog_category->getCategories(0);

		foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('blog/category/update', 'token=' . $this->session->data['token'] . '&blog_category_id=' . $result['blog_category_id'] . $url, 'SSL')
			);

			$this->data['categories'][] = array(
				'blog_category_id' => $result['blog_category_id'],
				'name'             => $result['name'],
				'sort_order'       => $result['sort_order'],
				'status'           => $result['status'],
				'selected'         => isset($this->request->post['selected']) && in_array($result['blog_category_id'], $this->request->post['selected']),
				'action'           => $action
			);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_confirm'] = $this->language->get('text_confirm');
		$this->data['text_confirm_delete'] = $this->language->get('text_confirm_delete');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_name'] = $this->url->link('blog/category', 'token=' . $this->session->data['token'] . '&sort=bcd.name' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('blog/category', 'token=' . $this->session->data['token'] . '&sort=bc.status' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('blog/category', 'token=' . $this->session->data['token'] . '&sort=bc.sort_order' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();

		$pagination->total = $category_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('blog/category', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'blog/category_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
	   	$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['text_image_manager'] = $this->language->get('text_image_manager');
		$this->data['text_browse'] = $this->language->get('text_browse');
		$this->data['text_clear'] = $this->language->get('text_clear');
		$this->data['text_percent'] = $this->language->get('text_percent');
		$this->data['text_amount'] = $this->language->get('text_amount');

		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_meta_keyword'] = $this->language->get('entry_meta_keyword');
		$this->data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$this->data['entry_description'] = $this->language->get('entry_description');
		$this->data['entry_store'] = $this->language->get('entry_store');
		$this->data['entry_keyword'] = $this->language->get('entry_keyword');
		$this->data['entry_parent'] = $this->language->get('entry_parent');
		$this->data['entry_image'] = $this->language->get('entry_image');
		$this->data['entry_top'] = $this->language->get('entry_top');
		$this->data['entry_column'] = $this->language->get('entry_column');
		$this->data['entry_article_limit'] = $this->language->get('entry_article_limit');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_layout'] = $this->language->get('entry_layout');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_data'] = $this->language->get('tab_data');
		$this->data['tab_design'] = $this->language->get('tab_design');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->request->get['blog_category_id'])) {
			$this->data['blog_category_id'] = $this->request->get['blog_category_id'];
		} else {
			$this->data['blog_category_id'] = 0;
		}

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

	 	if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = array();
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('blog/category', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		);

		if (isset($this->request->get['blog_category_id'])) {
			$this->data['action'] = $this->url->link('blog/category/update', 'token=' . $this->session->data['token'] . '&blog_category_id=' . $this->request->get['blog_category_id'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('blog/category/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('blog/category', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['blog_category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$category_info = $this->model_blog_category->getCategory($this->request->get['blog_category_id']);
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['category_description'])) {
			$this->data['category_description'] = $this->request->post['category_description'];
		} elseif (isset($this->request->get['blog_category_id'])) {
			$this->data['category_description'] = $this->model_blog_category->getCategoryDescriptions($this->request->get['blog_category_id']);
		} else {
			$this->data['category_description'] = array();
		}

		$categories = $this->model_blog_category->getCategories(0);

		// Remove own id from list
		if (!empty($category_info)) {
			foreach ($categories as $key => $category) {
				if ($category['blog_category_id'] == $category_info['blog_category_id']) {
					unset($categories[$key]);
				}
			}
		}

		$this->data['categories'] = $categories;

		if (isset($this->request->post['parent_id'])) {
			$this->data['parent_id'] = $this->request->post['parent_id'];
		} elseif (!empty($category_info)) {
			$this->data['parent_id'] = $category_info['parent_id'];
		} else {
			$this->data['parent_id'] = 0;
		}

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->post['category_store'])) {
			$this->data['category_store'] = $this->request->post['category_store'];
		} elseif (isset($this->request->get['blog_category_id'])) {
			$this->data['category_store'] = $this->model_blog_category->getCategoryStores($this->request->get['blog_category_id']);
		} else {
			$this->data['category_store'] = array(0);
		}

		if (isset($this->request->post['keyword'])) {
			$this->data['keyword'] = $this->request->post['keyword'];
		} elseif (!empty($category_info)) {
			$this->data['keyword'] = $category_info['keyword'];
		} else {
			$this->data['keyword'] = '';
		}

		$this->load->model('tool/image');

		$this->data['no_image'] = $this->model_tool_image->resize('no_image.png', 120, 120);

		if (isset($this->request->post['image'])) {
			$this->data['image'] = $this->request->post['image'];
		} elseif (!empty($category_info)) {
			$this->data['image'] = $category_info['image'];
		} else {
			$this->data['image'] = '';
		}

		if (!empty($category_info) && $category_info['image'] && file_exists(DIR_IMAGE . $category_info['image'])) {
			$this->data['thumb'] = $this->model_tool_image->resize($category_info['image'], 120, 120);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize('no_image.png', 120, 120);
		}

		if (isset($this->request->post['top'])) {
			$this->data['top'] = $this->request->post['top'];
		} elseif (!empty($category_info)) {
			$this->data['top'] = $category_info['top'];
		} else {
			$this->data['top'] = 0;
		}

		if (isset($this->request->post['column'])) {
			$this->data['column'] = $this->request->post['column'];
		} elseif (!empty($category_info)) {
			$this->data['column'] = $category_info['column'];
		} else {
			$this->data['column'] = 0;
		}

		if (isset($this->request->post['blog_category_column'])) {
			$this->data['blog_category_column'] = $this->request->post['blog_category_column'];
		} elseif (!empty($category_info)) {
			$this->data['blog_category_column'] = $category_info['blog_category_column'];
		} else {
			$this->data['blog_category_column'] = 0;
		}

		if (isset($this->request->post['sort_order'])) {
			$this->data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($category_info)) {
			$this->data['sort_order'] = $category_info['sort_order'];
		} else {
			$this->data['sort_order'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($category_info)) {
			$this->data['status'] = $category_info['status'];
		} else {
			$this->data['status'] = 1;
		}

		$blog_layout_id = $this->model_blog_category->getBlogCategoryLayoutId();

		if (isset($this->request->post['category_layout'])) {
			$this->data['category_layout'] = $this->request->post['category_layout'];
		} elseif (isset($this->request->get['blog_category_id'])) {
			$this->data['category_layout'] = $this->model_blog_category->getCategoryLayouts($this->request->get['blog_category_id']);
		} elseif (isset($blog_layout_id)) {
			$this->data['category_layout'] = $blog_layout_id;
		} else {
			$this->data['category_layout'] = array();
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->template = 'blog/category_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'blog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['category_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'blog/category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('blog/category');

		foreach ($this->request->post['selected'] as $simple_blog_catgory_id) {
			$article_total = $this->model_blog_category->getTotalArticleCategoryWise($simple_blog_catgory_id);

			if ($article_total) {
				$this->error['warning'] = sprintf($this->language->get('error_article'), $article_total);
			}
		}

		return empty($this->error);
	}
}
