<?php
class ControllerModuleBlogCategory extends Controller {
	private $error = array();
	private $_name = 'blog_category';

	public function index() {
		$this->language->load('module/' . $this->_name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting($this->_name, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['apply'])) {
				$this->redirect($this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_status_ready'] = $this->language->get('text_status_ready');
		$this->data['text_status_not_ready'] = $this->language->get('text_status_not_ready');

		$this->data['text_content_higher'] = $this->language->get('text_content_higher');
		$this->data['text_content_high'] = $this->language->get('text_content_high');
		$this->data['text_content_left'] = $this->language->get('text_content_left');
		$this->data['text_content_right'] = $this->language->get('text_content_right');
		$this->data['text_content_low'] = $this->language->get('text_content_low');
		$this->data['text_content_lower'] = $this->language->get('text_content_lower');

		$this->data['entry_theme'] = $this->language->get('entry_theme');
		$this->data['entry_title'] = $this->language->get('entry_title');

		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');
		$this->data['button_manager'] = $this->language->get('button_manager');

		// Check Blog Status
		$this->load->model('blog/status');

		$blog_tables = $this->model_blog_status->checkBlog();

		if ($blog_tables) {
			$this->data['blog_ready'] = true;
		} else {
			$this->data['blog_ready'] = false;
		}

		// Error
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('module/' . $this->_name, 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		// Manager
		$this->data['manager'] = $this->url->link('modification/blogs', 'token=' . $this->session->data['token'], 'SSL');

		// Module
		if (isset($this->request->post[$this->_name . '_theme'])) {
			$this->data[$this->_name . '_theme'] = $this->request->post[$this->_name . '_theme'];
		} else {
			$this->data[$this->_name . '_theme'] = $this->config->get($this->_name . '_theme');
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			if (isset($this->request->post[$this->_name . '_title' . $language['language_id']])) {
				$this->data[$this->_name . '_title' . $language['language_id']] = $this->request->post[$this->_name . '_title' . $language['language_id']];
			} else {
				$this->data[$this->_name . '_title' . $language['language_id']] = $this->config->get($this->_name . '_title' . $language['language_id']);
			}
		}

		$this->data['languages'] = $languages;

		if (isset($this->request->post[$this->_name . '_title'])) {
			$this->data[$this->_name . '_title'] = $this->request->post[$this->_name . '_title'];
		} else {
			$this->data[$this->_name . '_title'] = $this->config->get($this->_name . '_title');
		}

		$this->data['modules'] = array();

		if (isset($this->request->post[$this->_name . '_module'])) {
			$this->data['modules'] = $this->request->post[$this->_name . '_module'];
		} elseif ($this->config->get($this->_name . '_module')) {
			$this->data['modules'] = $this->config->get($this->_name . '_module');
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->template = 'module/' . $this->_name . '.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/' . $this->_name)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}
}
