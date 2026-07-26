<?php
/**
 * Class ControllerModuleWelcome
 *
 * @package NivoCart
 */
class ControllerModuleWelcome extends Controller {
	private $name = 'welcome';

	protected function index($setting) {
		static $module = 0;

		$this->language->load('module/welcome');

		$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('configname'));

		// Module
		$this->data['theme'] = $setting['theme'];

		$this->data['title'] = html_entity_decode($setting['title'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$this->data['message'] = html_entity_decode($setting['description'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('module/' . $this->name);

		$this->render();
	}
}
