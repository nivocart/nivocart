<?php
/**
 * Class ControllerCommonHeaderPayment
 *
 * Trimmed header for standalone payment redirect pages (e.g. PayPal Standard).
 * No navigation, no cart, no search — just the essential shell.
 *
 * @package NivoCart
 */
class ControllerCommonHeaderPayment extends Controller {
	/** Error array Placeholder */

	public function index() {
		$this->data['title'] = $this->document->getTitle();

		$this->data['base'] = HTTP_SERVER;
		$this->data['lang'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');

		$this->data['description'] = $this->document->getDescription();
		$this->data['keywords'] = $this->document->getKeyWords();

		$this->data['icon'] = $this->config->get('config_icon');
		$this->data['links'] = $this->document->getLinks();
		$this->data['metas'] = $this->document->getMeta();
		$this->data['styles'] = $this->document->getStyles();

		$this->data['display_size'] = $this->config->get('config_display_size') ?: 'wide';
		$this->data['google_analytics'] = $this->config->get('config_google_analytics') ?? '';

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/header_payment.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/header_payment.tpl';
		} else {
			$this->template = 'default/template/common/header_payment.tpl';
		}

		$this->render();
	}
}
