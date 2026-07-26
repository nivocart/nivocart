<?php
/**
 * Class ControllerModuleHtml
 *
 * @package NivoCart
 */
class ControllerModuleHtml extends Controller {
	private $name = 'html';

	protected function index($setting) {
		static $module = 0;

		$this->language->load('module/' . $this->name);

		$this->data['heading_title'] = $this->language->get('heading_title');

		// Module
		for ($i = 1; $i <= 10; $i++) {
			$this->data['theme' . $i] = $this->config->get($this->name . '_theme' . $i);

			$this->data['title' . $i] = $this->config->get($this->name . '_title' . $i . '_' . $this->config->get('config_language_id'));

			if (!$this->data['title' . $i]) {
				$this->data['title' . $i] = $this->data['heading_title'];
			}

			$this->data['code' . $i] = html_entity_decode($this->config->get($this->name . '_code' . $i), ENT_QUOTES, 'UTF-8');

			$position = $setting['tab_id'];

			if ($position && $position === "tab" . $i) {
				$this->data['code'] = $this->data['code' . $i];
				$this->data['theme'] = $this->data['theme' . $i];
				$this->data['title'] = $this->data['title' . $i];
			}
		}

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('module/' . $this->name);

		$this->render();
	}
}
