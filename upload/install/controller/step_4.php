<?php
class ControllerStep4 extends Controller {

	public function index() {
		$this->document->setTitle($this->language->get('heading_step_4'));

		$this->data['heading_step_4'] = $this->language->get('heading_step_4');

		$this->data['heading_next'] = $this->language->get('heading_next');
		$this->data['heading_setting'] = $this->language->get('heading_setting');
		$this->data['heading_security'] = $this->language->get('heading_security');
		$this->data['heading_server'] = $this->language->get('heading_server');

		$this->data['help_setting'] = $this->language->get('help_setting');
		$this->data['help_security'] = $this->language->get('help_security');
		$this->data['help_server'] = $this->language->get('help_server');
		$this->data['help_installer'] = $this->language->get('help_installer');

		$this->data['text_congratulation'] = $this->language->get('text_congratulation');
		$this->data['text_login'] = $this->language->get('text_login');

		$this->template = 'step_4.tpl';
		$this->children = array(
			'header',
			'footer'
		);

		$this->response->setOutput($this->render());
	}
}
