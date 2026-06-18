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

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/footer_payment.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/footer_payment.tpl';
		} else {
			$this->template = 'default/template/common/footer_payment.tpl';
		}

		$this->render();
	}
}
