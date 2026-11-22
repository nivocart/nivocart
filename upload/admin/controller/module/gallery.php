<?php
/**
 * Class ControllerModuleGallery
 *
 * @package NivoCart
 */
class ControllerModuleGallery extends Controller {
	private $error = [];
	private $name = 'gallery';
	private $plugin = 'flexslider';
	private $version = 'v2.7.2';

	public function index() {
		$this->language->load('module/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && ($this->validate())) {
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

		$this->data['heading_module_style'] = $this->language->get('heading_module_style');
		$this->data['heading_module_parameters'] = $this->language->get('heading_module_parameters');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_horizontal'] = $this->language->get('text_horizontal');
		$this->data['text_vertical'] = $this->language->get('text_vertical');

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

		$this->data['entry_filter'] = $this->language->get('entry_filter');
		$this->data['entry_limit'] = $this->language->get('entry_limit');
		$this->data['entry_links'] = $this->language->get('entry_links');
		$this->data['entry_month'] = $this->language->get('entry_month');
		$this->data['entry_month_title'] = $this->language->get('entry_month_title');
		$this->data['text_all'] = $this->language->get('text_all');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_not_installed'] = $this->language->get('text_not_installed');
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
		$this->data['button_manager'] = $this->language->get('button_gallery');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['error_limit'] = $this->error['limit'] ?? [];

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
		$this->data['manager'] = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'], 'SSL');

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

		$languages = $this->model_localisation_language->getLanguages([]);

		foreach ($languages as $language) {
			if (isset($this->request->post[$this->name . '_title' . $language['language_id']])) {
				$this->data[$this->name . '_title' . $language['language_id']] = $this->request->post[$this->name . '_title' . $language['language_id']];
			} else {
				$this->data[$this->name . '_title' . $language['language_id']] = $this->config->get($this->name . '_title' . $language['language_id']);
			}
		}

		foreach ($languages as $language) {
			if (isset($this->request->post[$this->name . '_month_title' . $language['language_id']])) {
				$this->data[$this->name . '_month_title' . $language['language_id']] = $this->request->post[$this->name . '_month_title' . $language['language_id']];
			} else {
				$this->data[$this->name . '_month_title' . $language['language_id']] = $this->config->get($this->name . '_month_title' . $language['language_id']);
			}
		}

		$this->data['languages'] = $languages;

		foreach (['links', 'month'] as $key) {
			if (isset($this->request->post[$this->name . '_' . $key])) {
				$this->data[$this->name . '_' . $key] = $this->request->post[$this->name . '_' . $key];
			} else {
				$this->data[$this->name . '_' . $key] = $this->config->get($this->name . '_' . $key);
			}
		}

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

		$this->data['modules'] = [];

		if (isset($this->request->post[$this->name . '_module'])) {
			$this->data['modules'] = $this->request->post[$this->name . '_module'];
		} elseif ($this->config->get($this->name . '_module')) {
			$this->data['modules'] = $this->config->get($this->name . '_module');
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts([]);

		// Filters (set in Extensions > Modifications > Gallery) and the approved images for the image of the month
		$this->data['filters'] = [];
		$this->data['approved'] = [];
		$this->data['installed'] = false;

		$this->load->model('modification/gallery');

		if ($this->model_modification_gallery->tableExists()) {
			$this->data['installed'] = true;

			$filters = $this->config->get('gallery_page_filters');

			if (is_string($filters)) {
				$filters = json_decode($filters, true);
			}

			foreach ((array)$filters as $filter) {
				if (is_array($filter) && !empty($filter['filter_id'])) {
					$names = (array)($filter['name'] ?? []);

					$this->data['filters'][(int)$filter['filter_id']] = (string)($names[(int)$this->config->get('config_language_id')] ?? (reset($names) ?: ''));
				}
			}

			foreach ($this->model_modification_gallery->getImages(['filter_status' => 1, 'sort' => 'date_added', 'order' => 'DESC', 'start' => 0, 'limit' => 200]) as $result) {
				$this->data['approved'][] = [
					'gallery_id' => (int)$result['gallery_id'],
					'name'       => sprintf('GAL-%05d', (int)$result['gallery_id']) . ' - ' . $result['pet_name']
				];
			}
		}

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

		if (isset($this->request->post[$this->name . '_module'])) {
			foreach ($this->request->post[$this->name . '_module'] as $key => $value) {
				if (!$value['width'] || !$value['height']) {
					$this->error['dimension'][$key] = $this->language->get('error_dimension');
				}

				if (!ctype_digit((string)($value['limit'] ?? '')) || ((int)$value['limit'] < 1) || ((int)$value['limit'] > 20)) {
					$this->error['limit'][$key] = $this->language->get('error_limit');
				}
			}
		}

		return empty($this->error);
	}
}
