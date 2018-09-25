<?php
class ControllerFooter extends Controller {

	public function index() {
		$this->data['text_project_home'] = $this->language->get('text_project_home');
		$this->data['text_project_forum'] = $this->language->get('text_project_forum');
		$this->data['text_footer'] = $this->language->get('text_footer');

		$this->template = 'footer.tpl';

		$this->render();
	}
}
