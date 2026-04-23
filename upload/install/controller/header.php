<?php
/**
 * Class Header
 *
 * @package NivoCart
 */
class ControllerHeader extends Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->data['title'] = $this->document->getTitle();
		$this->data['description'] = $this->document->getDescription();
		$this->data['links'] = $this->document->getLinks();
		$this->data['styles'] = $this->document->getStyles();
		$this->data['scripts'] = $this->document->getScripts();

		$this->data['base'] = HTTP_SERVER;

		$this->template = 'header.tpl';

		$this->render();
	}
}
