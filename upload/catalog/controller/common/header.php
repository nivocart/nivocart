<?php
/**
 * Class ControllerCommonHeader
 *
 * @package NivoCart
 */
class ControllerCommonHeader extends Controller {
	/** Error array Placeholder */

	protected function index() {
		$this->data['title'] = $this->document->getTitle();

		// Resolve server base URL
		if ((isset($this->request->server['HTTPS']) && in_array($this->request->server['HTTPS'], ['on', '1'], true)) ||
			(isset($this->request->server['SERVER_PORT']) && $this->request->server['SERVER_PORT'] === '443') ||
			(isset($this->request->server['HTTP_X_FORWARDED_PROTO']) && $this->request->server['HTTP_X_FORWARDED_PROTO'] === 'https')) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if ($this->config->get('config_meta_google')) {
			$this->document->addMeta(html_entity_decode($this->config->get('config_meta_google'), ENT_QUOTES, 'UTF-8'));
		}

		if ($this->config->get('config_meta_bing')) {
			$this->document->addMeta(html_entity_decode($this->config->get('config_meta_bing'), ENT_QUOTES, 'UTF-8'));
		}

		if ($this->config->get('config_meta_yandex')) {
			$this->document->addMeta(html_entity_decode($this->config->get('config_meta_yandex'), ENT_QUOTES, 'UTF-8'));
		}

		if ($this->config->get('config_meta_baidu')) {
			$this->document->addMeta(html_entity_decode($this->config->get('config_meta_baidu'), ENT_QUOTES, 'UTF-8'));
		}

		$page_keywords = $this->document->getKeywords();

		$default_keywords = $this->config->get('config_meta_keyword');

		$this->language->load('common/header');

		$this->data['base'] = $server;
		$this->data['description'] = $this->document->getDescription();
		$this->data['keywords'] = ($page_keywords) ? $page_keywords : $default_keywords;
		$this->data['metas'] = $this->document->getMeta();
		$this->data['links'] = $this->document->getLinks();
		$this->data['styles'] = $this->document->getStyles();
		$this->data['lang'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');
		$this->data['name'] = $this->config->get('config_name');
		$this->data['version'] = VERSION;

		if ($this->config->get('config_icon') && file_exists(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->data['icon'] = $server . 'image/' . $this->config->get('config_icon');
		} else {
			$this->data['icon'] = '';
		}

		if ($this->config->get('config_logo') && file_exists(DIR_IMAGE . $this->config->get('config_logo'))) {
			$this->data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$this->data['logo'] = '';
		}

		$this->data['text_home'] = $this->language->get('text_home');
		$this->data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist'])) ? count($this->session->data['wishlist']) : 0);
		$this->data['text_shopping_cart'] = $this->language->get('text_shopping_cart');
		$this->data['text_welcome'] = sprintf($this->language->get('text_welcome'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));
		$this->data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', 'SSL'), $this->customer->getFirstName() . ' ' . $this->customer->getLastName(), $this->url->link('account/logout', '', 'SSL'));
		$this->data['text_signin'] = $this->language->get('text_signin');
		$this->data['text_account'] = $this->language->get('text_account');
		$this->data['text_checkout'] = $this->language->get('text_checkout');
		$this->data['text_search'] = $this->language->get('text_search');

		$this->data['home'] = $this->url->link('common/home', '', 'SSL');
		$this->data['wishlist'] = $this->url->link('account/wishlist', '', 'SSL');
		$this->data['logged'] = $this->customer->isLogged();
		$this->data['account'] = $this->url->link('account/account', '', 'SSL');
		$this->data['shopping_cart'] = $this->url->link('checkout/cart', '', 'SSL');
		$this->data['checkout'] = $this->url->link('checkout/checkout', '', 'SSL');

		$this->data['google_analytics'] = html_entity_decode($this->config->get('config_google_analytics'), ENT_QUOTES, 'UTF-8');

		// Robot detector
		$status = true;

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$robots = explode("\n", str_replace(["\r\n", "\r"], "\n", trim($this->config->get('config_robots'))));

			foreach ($robots as $robot) {
				if ($robot && strpos($this->request->server['HTTP_USER_AGENT'], trim($robot)) !== false) {
					$status = false;
					break;
				}
			}
		}

		// Ip Blocking
		$this->load->model('tool/online');

		if (isset($this->request->server['REMOTE_ADDR'])) {
			$is_blocked = $this->model_tool_online->isBlockedIp($this->request->server['REMOTE_ADDR']);

			if ($is_blocked) {
				$this->redirect($this->url->link('error/forbidden', '', 'SSL'));
			}
		}

		// Multi-store cookie
		$this->load->model('setting/store');

		$this->data['stores'] = [];

		if ($this->config->get('config_shared') && $status) {
			$this->data['stores'][] = $server . 'catalog/view/javascript/crossdomain.php?session_id=' . $this->session->getId();

			$stores_array = [];

			$stores = $this->model_setting_store->getStores($stores_array);

			foreach ($stores as $store) {
				$this->data['stores'][] = $store['url'] . 'catalog/view/javascript/crossdomain.php?session_id=' . $this->session->getId();
			}
		}

		// Search
		if (isset($this->request->get['search'])) {
			$this->data['search'] = $this->request->get['search'];
		} else {
			$this->data['search'] = '';
		}

		// Rss
		$this->data['rss'] = $this->config->get('rss_feed_status');

		$rss_currency = $this->currency->getCode();

		$this->data['rss_href'] = $this->url->link('feed/rss_feed', 'currency=' . $rss_currency, 'SSL');

		// Cookie Consent
		$this->data['text_message'] = $this->language->get('text_message');
		$this->data['text_policy'] = $this->language->get('text_policy');
		$this->data['text_accept'] = $this->language->get('text_accept');

		$this->data['cookie_consent'] = $this->config->get('config_cookie_consent');

		$cookie_theme = $this->config->get('config_cookie_theme');

		if ($cookie_theme === "dark") {
			$this->data['cookie_popup'] = "#323435";
			$this->data['cookie_text'] = "#FCFCFC";
			$this->data['cookie_button'] = "#14A7D0";
		} else {
			$this->data['cookie_popup'] = "#EDEDED";
			$this->data['cookie_text'] = "#777777";
			$this->data['cookie_button'] = "#14A7D0";
		}

		$this->data['cookie_position'] = $this->config->get('config_cookie_position');

		$cookie_privacy = $this->config->get('config_cookie_privacy');

		$this->data['cookie_privacy'] = $this->url->link('information/information', 'information_id=' . $cookie_privacy, 'SSL');

		$cookie_age = $this->config->get('config_cookie_age');

		$this->data['cookie_age'] = ($cookie_age) ? $cookie_age : 365;

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

		// Template
		$this->data['template'] = $template;

		$this->children = [
			'node/language',
			'node/cart'
		];

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/header.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/header.tpl';
		} else {
			$this->template = 'default/template/common/header.tpl';
		}

		$this->render();
	}
}
