<?php
/**
 * Class ControllerModuleTrustpilotWidget
 *
 * Trustpilot "Review Collector" widget (free plan).
 *
 * @package NivoCart
 */
class ControllerModuleTrustpilotWidget extends Controller {
	private $error = [];
	private $name = 'trustpilot_widget';

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

		$this->data['text_info'] = $this->language->get('text_info');
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
		$this->data['entry_business_id'] = $this->language->get('entry_business_id');
		$this->data['entry_store_name'] = $this->language->get('entry_store_name');
		$this->data['entry_template_id'] = $this->language->get('entry_template_id');
		$this->data['entry_locale'] = $this->language->get('entry_locale');
		$this->data['entry_height'] = $this->language->get('entry_height');
		$this->data['entry_width'] = $this->language->get('entry_width');
		$this->data['entry_consent'] = $this->language->get('entry_consent');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		foreach (['business_id', 'store_name', 'template_id', 'locale', 'height', 'width'] as $field) {
			if (isset($this->error[$field])) {
				$this->data['error_' . $field] = $this->error[$field];
			} else {
				$this->data['error_' . $field] = '';
			}
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

		// Module settings (the Review Collector template ID is public and the same for every business)
		$defaults = [
			'theme'       => 1,
			'business_id' => '',
			'store_name'  => '',
			'template_id' => '56278e9abfbbba0bdcd568bc',
			'locale'      => '',
			'height'      => '52px',
			'width'       => '100%',
			'consent'     => 1
		];

		foreach ($defaults as $key => $default) {
			if (isset($this->request->post[$this->name . '_' . $key])) {
				$this->data[$this->name . '_' . $key] = $this->request->post[$this->name . '_' . $key];
			} elseif ($this->config->get($this->name . '_' . $key) !== null) {
				$this->data[$this->name . '_' . $key] = $this->config->get($this->name . '_' . $key);
			} else {
				$this->data[$this->name . '_' . $key] = $default;
			}
		}

		// Shown as the placeholder: the store name used when the field is left empty
		$this->data['default_store_name'] = preg_replace('/^www\./i', '', (string)parse_url(HTTP_CATALOG, PHP_URL_HOST));

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages([]);

		foreach ($languages as $language) {
			if (isset($this->request->post[$this->name . '_title' . $language['language_id']])) {
				$this->data[$this->name . '_title' . $language['language_id']] = $this->request->post[$this->name . '_title' . $language['language_id']];
			} else {
				$this->data[$this->name . '_title' . $language['language_id']] = $this->config->get($this->name . '_title' . $language['language_id']);
			}
		}

		$this->data['languages'] = $languages;

		$this->data['modules'] = [];

		if (isset($this->request->post[$this->name . '_module'])) {
			$this->data['modules'] = $this->request->post[$this->name . '_module'];
		} elseif ($this->config->get($this->name . '_module')) {
			$this->data['modules'] = $this->config->get($this->name . '_module');
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts([]);

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

		$post = $this->request->post;

		$business_id = isset($post[$this->name . '_business_id']) ? trim($post[$this->name . '_business_id']) : '';
		$template_id = isset($post[$this->name . '_template_id']) ? trim($post[$this->name . '_template_id']) : '';
		$store_name = isset($post[$this->name . '_store_name']) ? trim($post[$this->name . '_store_name']) : '';
		$locale = isset($post[$this->name . '_locale']) ? trim($post[$this->name . '_locale']) : '';
		$height = isset($post[$this->name . '_height']) ? trim($post[$this->name . '_height']) : '';
		$width = isset($post[$this->name . '_width']) ? trim($post[$this->name . '_width']) : '';

		if (!preg_match('/^[a-f0-9]{24}$/i', $business_id)) {
			$this->error['business_id'] = $this->language->get('error_business_id');
		}

		if (!preg_match('/^[a-f0-9]{24}$/i', $template_id)) {
			$this->error['template_id'] = $this->language->get('error_template_id');
		}

		// Optional: the domain name of the Trustpilot review page (trustpilot.com/review/<store name>)
		if ($store_name !== '' && (strlen($store_name) > 100 || !preg_match('/^[a-z0-9]([a-z0-9-]*\.)+[a-z0-9-]*[a-z0-9]$/i', $store_name))) {
			$this->error['store_name'] = $this->language->get('error_store_name');
		}

		// Optional: empty means the locale follows the language the visitor is using
		if ($locale !== '' && !preg_match('/^[a-z]{2}-[A-Z]{2}$/', $locale)) {
			$this->error['locale'] = $this->language->get('error_locale');
		}

		if (!preg_match('/^[1-9][0-9]{1,3}px$/', $height)) {
			$this->error['height'] = $this->language->get('error_height');
		}

		if (!preg_match('/^[1-9][0-9]{0,3}(px|%)$/', $width)) {
			$this->error['width'] = $this->language->get('error_width');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}
}
