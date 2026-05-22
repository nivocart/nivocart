<?php
/**
 * Class ControllerModuleSlideshow
 *
 * @package NivoCart
 */
class ControllerModuleSlideshow extends Controller {
	private $error = [];
	private $name = 'slideshow';
	private $plugin = 'flexslider';
	private $version = 'v2.7.2';

	public function index() {
		$this->language->load('module/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			// POST uses json_encode
			$this->model_setting_setting->editSetting($this->name, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['apply'])) {
				$this->redirect($this->url->link('module/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		$this->data['text_content_higher'] = $this->language->get('text_content_higher');
		$this->data['text_content_high'] = $this->language->get('text_content_high');
		$this->data['text_content_left'] = $this->language->get('text_content_left');
		$this->data['text_content_right'] = $this->language->get('text_content_right');
		$this->data['text_content_low'] = $this->language->get('text_content_low');
		$this->data['text_content_lower'] = $this->language->get('text_content_lower');

		$this->data['entry_theme'] = $this->language->get('entry_theme');
		$this->data['entry_title'] = $this->language->get('entry_title');
		$this->data['entry_transition'] = $this->language->get('entry_transition');
		$this->data['entry_duration'] = $this->language->get('entry_duration');
		$this->data['entry_speed'] = $this->language->get('entry_speed');
		$this->data['entry_random'] = $this->language->get('entry_random');
		$this->data['entry_dots'] = $this->language->get('entry_dots');
		$this->data['entry_arrows'] = $this->language->get('entry_arrows');

		$this->data['entry_banner'] = $this->language->get('entry_banner');
		$this->data['entry_dimension'] = $this->language->get('entry_dimension');
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

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['dimension'])) {
			$this->data['error_dimension'] = $this->error['dimension'];
		} else {
			$this->data['error_dimension'] = [];
		}

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['action'] = $this->url->link('module/' . $this->name, 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		// Manager
		$this->data['manager'] = $this->url->link('design/banner', 'token=' . $this->session->data['token'], 'SSL');

		// Plugin
		$this->data[$this->name . '_plugin'] = $this->plugin;
		$this->data[$this->name . '_version'] = $this->version;

		// Module
		if (isset($this->request->post[$this->name . '_theme'])) {
			$this->data[$this->name . '_theme'] = $this->request->post[$this->name . '_theme'];
		} else {
			$this->data[$this->name . '_theme'] = $this->config->get($this->name . '_theme');
		}

		$this->load->model('localisation/language');

		$languages_array = [];

		$languages = $this->model_localisation_language->getLanguages($languages_array);

		foreach ($languages as $language) {
			if (isset($this->request->post[$this->name . '_title' . $language['language_id']])) {
				$this->data[$this->name . '_title' . $language['language_id']] = $this->request->post[$this->name . '_title' . $language['language_id']];
			} else {
				$this->data[$this->name . '_title' . $language['language_id']] = $this->config->get($this->name . '_title' . $language['language_id']);
			}
		}

		$this->data['languages'] = $languages;

		if (isset($this->request->post[$this->name . '_title'])) {
			$this->data[$this->name . '_title'] = $this->request->post[$this->name . '_title'];
		} else {
			$this->data[$this->name . '_title'] = $this->config->get($this->name . '_title');
		}

		if (isset($this->request->post[$this->name . '_transition'])) {
			$this->data[$this->name . '_transition'] = $this->request->post[$this->name . '_transition'];
		} else {
			$this->data[$this->name . '_transition'] = $this->config->get($this->name . '_transition');
		}

		if (isset($this->request->post[$this->name . '_duration'])) {
			$this->data[$this->name . '_duration'] = $this->request->post[$this->name . '_duration'];
		} else {
			$this->data[$this->name . '_duration'] = $this->config->get($this->name . '_duration');
		}

		if (isset($this->request->post[$this->name . '_speed'])) {
			$this->data[$this->name . '_speed'] = $this->request->post[$this->name . '_speed'];
		} else {
			$this->data[$this->name . '_speed'] = $this->config->get($this->name . '_speed');
		}

		if (isset($this->request->post[$this->name . '_random'])) {
			$this->data[$this->name . '_random'] = $this->request->post[$this->name . '_random'];
		} else {
			$this->data[$this->name . '_random'] = $this->config->get($this->name . '_random');
		}

		if (isset($this->request->post[$this->name . '_dots'])) {
			$this->data[$this->name . '_dots'] = $this->request->post[$this->name . '_dots'];
		} else {
			$this->data[$this->name . '_dots'] = $this->config->get($this->name . '_dots');
		}

		if (isset($this->request->post[$this->name . '_arrows'])) {
			$this->data[$this->name . '_arrows'] = $this->request->post[$this->name . '_arrows'];
		} else {
			$this->data[$this->name . '_arrows'] = $this->config->get($this->name . '_arrows');
		}

		$this->data['modules'] = [];

		if (isset($this->request->post[$this->name . '_module'])) {
			$this->data['modules'] = $this->request->post[$this->name . '_module'];
		} elseif ($this->config->get($this->name . '_module')) {
			$this->data['modules'] = $this->config->get($this->name . '_module');
		}

		$this->load->model('design/layout');

		$layouts_array = [];

		$this->data['layouts'] = $this->model_design_layout->getLayouts($layouts_array);

		$this->load->model('design/banner');

		$banners_array = [];

		$this->data['banners'] = $this->model_design_banner->getBanners($banners_array);

		$this->template = 'module/' . $this->name . '.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/' . $this->name)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['slideshow_module'])) {
			foreach ($this->request->post['slideshow_module'] as $key => $value) {
				if (!$value['width'] || !$value['height']) {
					$this->error['dimension'][$key] = $this->language->get('error_dimension');
				}
			}
		}

		return empty($this->error);
	}
}
