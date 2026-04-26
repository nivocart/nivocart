<?php
/**
 * Class ControllerCommonLogout
 *
 * @package NivoCart
 */
class ControllerCommonLogout extends Controller {
	/** Error array Placeholder */

	public function index() {
		$this->user->logout();

		unset($this->session->data['token']);

		$this->redirect($this->url->link('common/login', '', 'SSL'));
	}
}
