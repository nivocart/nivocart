<?php
/**
 * Class ControllerCommonFooterLogin
 *
 * @package NivoCart
 */
class ControllerCommonFooterLogin extends Controller {
	/** Error array Placeholder */

	protected function index() {
		$this->template = 'common/footer_login.tpl';

		$this->render();
	}
}
