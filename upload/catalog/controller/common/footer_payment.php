<?php
/**
 * Class ControllerCommonFooterPayment
 *
 * Trimmed footer for standalone payment redirect pages.
 *
 * @package NivoCart
 */
class ControllerCommonFooterPayment extends Controller {
	/** Error array Placeholder */

	public function index() {
		$this->data['template'] = $this->config->get('config_template');

		$this->data['scripts'] = $this->document->getScripts();

		$this->resolveTemplate('common/footer_payment');
		$this->render();
	}
}
