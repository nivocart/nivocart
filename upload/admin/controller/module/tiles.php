<?php
/**
 * Class ControllerModuleTiles
 *
 * @package NivoCart
 */
class ControllerModuleTiles extends Controller {
	private $error = [];
	private $name = 'tiles';

	public function index() {
		$this->language->load('module/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
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
		$this->data['text_none'] = $this->language->get('text_none');

		$this->data['entry_theme'] = $this->language->get('entry_theme');
		$this->data['entry_title'] = $this->language->get('entry_title');
		$this->data['entry_shape'] = $this->language->get('entry_shape');
		$this->data['entry_hover'] = $this->language->get('entry_hover');

		$this->data['entry_tile'] = $this->language->get('entry_tile');
		$this->data['entry_dimension'] = $this->language->get('entry_dimension');
		$this->data['entry_min_height'] = $this->language->get('entry_min_height');
		$this->data['entry_gap'] = $this->language->get('entry_gap');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_manager'] = $this->language->get('button_manager');
		$this->data['button_add_module'] = $this->language->get('button_add_module');
		$this->data['button_remove'] = $this->language->get('button_remove');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
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
		$this->data['manager'] = $this->url->link('design/tile', 'token=' . $this->session->data['token'], 'SSL');

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

		$this->data['languages'] = $languages;

		// Global: Tile Shape
		$this->data['shapes'] = $this->model_setting_setting->getShapes();

		if (isset($this->request->post[$this->name . '_shape'])) {
			$this->data[$this->name . '_shape'] = $this->request->post[$this->name . '_shape'];
		} else {
			$this->data[$this->name . '_shape'] = $this->config->get($this->name . '_shape');
		}

		// Global: Hover Effect
		$this->data['hovers'] = [
			'none' => $this->language->get('text_hover_none'),
			'lift' => $this->language->get('text_hover_lift'),
			'zoom' => $this->language->get('text_hover_zoom')
		];

		if (isset($this->request->post[$this->name . '_hover'])) {
			$this->data[$this->name . '_hover'] = $this->request->post[$this->name . '_hover'];
		} else {
			$this->data[$this->name . '_hover'] = $this->config->get($this->name . '_hover');
		}

		// Placements
		$this->data['modules'] = [];

		if (isset($this->request->post[$this->name . '_module'])) {
			$this->data['modules'] = $this->request->post[$this->name . '_module'];
		} elseif ($this->config->get($this->name . '_module')) {
			$this->data['modules'] = $this->config->get($this->name . '_module');
		}

		$this->load->model('design/tile');

		$this->data['tiles'] = [];

		foreach ($this->model_design_tile->getTiles([]) as $tile) {
			$this->data['tiles'][$tile['tile_id']] = $tile['name'];
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = [];

		foreach ($this->model_design_layout->getLayouts([]) as $layout) {
			$this->data['layouts'][$layout['layout_id']] = $layout['name'];
		}

		$this->data['positions'] = [
			'content_higher' => $this->language->get('text_content_higher'),
			'content_high'   => $this->language->get('text_content_high'),
			'content_left'   => $this->language->get('text_content_left'),
			'content_right'  => $this->language->get('text_content_right'),
			'content_low'    => $this->language->get('text_content_low'),
			'content_lower'  => $this->language->get('text_content_lower')
		];

		$this->data['gaps'] = $this->getGaps();

		$this->template = 'module/' . $this->name . '.tpl';
		$this->children = ['common/header', 'common/footer'];

		$this->response->setOutput($this->render());
	}

	protected function getGaps() {
		return [0, 5, 10, 15, 20, 30];
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/' . $this->name)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->error['warning']) && isset($this->request->post[$this->name . '_module'])) {
			$row_errors = [];

			$row = 0;

			foreach ($this->request->post[$this->name . '_module'] as $module) {
				$row++;

				if (empty($module['width']) || empty($module['height']) || ((int)$module['width'] < 1) || ((int)$module['height'] < 1)) {
					$row_errors[] = sprintf($this->language->get('error_dimension'), $row);
				}

				if (isset($module['min_height']) && ($module['min_height'] !== '') && (((int)$module['min_height'] < 0) || ((int)$module['min_height'] > 1000))) {
					$row_errors[] = sprintf($this->language->get('error_min_height'), $row);
				}
			}

			if ($row_errors) {
				$this->error['warning'] = implode('<br />', $row_errors);
			}
		}

		return empty($this->error);
	}
}
