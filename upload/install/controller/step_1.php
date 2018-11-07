<?php
class ControllerStep1 extends Controller {
	private $error = array();

	public function index() {
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->response->redirect($this->url->link('step_2', '', 'SSL'));
		}

		$this->document->setTitle($this->language->get('heading_step_1'));

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['heading_step_1'] = $this->language->get('heading_step_1');

		$this->data['text_terms'] = $this->language->get('text_terms');

		$this->data['entry_agree'] = $this->language->get('entry_agree');

		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['action'] = $this->url->link('step_1', '', 'SSL');

		$this->template = 'step_1.tpl';
		$this->children = array(
			'header',
			'footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!isset($this->request->post['agree'])) {
			$this->error['warning'] = $this->language->get('error_license');
		}

		return empty($this->error);
	}
}
