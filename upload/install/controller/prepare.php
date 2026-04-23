<?php
/**
 * Class Prepare
 *
 * @package NivoCart
 */
class ControllerPrepare extends Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$this->response->redirect($this->url->link('step_1', '', 'SSL'));
		}

		$this->document->setTitle($this->language->get('heading_prepare'));

		$this->data['heading_prepare'] = $this->language->get('heading_prepare');

		$this->data['text_prepare'] = $this->language->get('text_prepare');

		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['action'] = $this->url->link('prepare', '', 'SSL');

		$this->template = 'prepare.tpl';
		$this->children = [
			'header',
			'footer'
		];

		$this->response->setOutput($this->render());
	}
}
