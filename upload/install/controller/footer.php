<?php
/**
 * Class Footer
 *
 * @package NivoCart
 */
class ControllerFooter extends Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->data['text_project_home'] = $this->language->get('text_project_home');
		$this->data['text_footer'] = $this->language->get('text_footer');

		$this->template = 'footer.tpl';

		$this->render();
	}
}
