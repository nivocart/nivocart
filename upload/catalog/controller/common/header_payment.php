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

		// Resolve server base URL
		if ((isset($this->request->server['HTTPS']) && in_array($this->request->server['HTTPS'], ['on', '1'], true)) ||
			(isset($this->request->server['SERVER_PORT']) && $this->request->server['SERVER_PORT'] === '443') ||
			(isset($this->request->server['HTTP_X_FORWARDED_PROTO']) && $this->request->server['HTTP_X_FORWARDED_PROTO'] === 'https')
		) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		$this->data['base'] = $server;
		$this->data['lang'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');

		$this->data['description'] = $this->document->getDescription();
		$this->data['keywords'] = $this->document->getKeyWords();

		$this->data['icon'] = $this->config->get('config_icon');
		$this->data['links'] = $this->document->getLinks();
		$this->data['metas'] = $this->document->getMeta();
		$this->data['styles'] = $this->document->getStyles();

		$this->data['google_analytics'] = $this->config->get('config_google_analytics') ?? '';

		// Theme
		$template = $this->config->get('config_template');

		$display_size = $this->config->get($template . '_widescreen');

		if ($display_size === 'full') {
			$this->data['display_size'] = 'full';
		} elseif ($display_size === 'wide') {
			$this->data['display_size'] = 'wide';
		} elseif ($display_size === 'normal') {
			$this->data['display_size'] = 'normal';
		} else {
			$this->data['display_size'] = 'normal';
		}

		// Minify main stylesheet
		$web_root = rtrim(dirname(DIR_APPLICATION), '/\\') . '/';
		$css_source = DIR_TEMPLATE . $template . '/stylesheet/stylesheet.css';

		$this->data['stylesheet_main'] = minifyCss($css_source);
		$this->data['stylesheet_main_fallback'] = $server . 'catalog/view/theme/' . $template . '/stylesheet/stylesheet.css';

		// Template
		$this->data['template'] = $template;

		if (file_exists(DIR_TEMPLATE . $template . '/template/common/header_payment.tpl')) {
			$this->template = $template . '/template/common/header_payment.tpl';
		} else {
			$this->template = 'default/template/common/header_payment.tpl';
		}

		$this->render();
	}
}
