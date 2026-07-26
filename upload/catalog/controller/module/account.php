<?php
/**
 * Class ControllerModuleAccount
 *
 * @package NivoCart
 */
class ControllerModuleAccount extends Controller {
	private $name = 'account';

	protected function index($setting) {
		static $module = 0;

		$this->language->load('module/' . $this->name);

		$this->data['heading_title'] = $this->language->get('heading_title');

		// Module
		$this->data['theme'] = $this->config->get($this->name . '_theme');
		$this->data['title'] = $this->config->get($this->name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$this->data['mode'] = $this->config->get($this->name . '_mode');

		$this->data['code'] = $this->customer->getId();

		$this->data['text_register'] = $this->language->get('text_register');
		$this->data['text_login'] = $this->language->get('text_login');
		$this->data['text_logout'] = $this->language->get('text_logout');
		$this->data['text_forgotten'] = $this->language->get('text_forgotten');
		$this->data['text_account'] = $this->language->get('text_account');
		$this->data['text_edit'] = $this->language->get('text_edit');
		$this->data['text_password'] = $this->language->get('text_password');
		$this->data['text_address'] = $this->language->get('text_address');
		$this->data['text_wishlist'] = $this->language->get('text_wishlist');
		$this->data['text_order'] = $this->language->get('text_order');
		$this->data['text_download'] = $this->language->get('text_download');
		$this->data['text_reward'] = $this->language->get('text_reward');
		$this->data['text_return'] = $this->language->get('text_return');
		$this->data['text_addreturn'] = $this->language->get('text_addreturn');
		$this->data['text_transaction'] = $this->language->get('text_transaction');
		$this->data['text_newsletter'] = $this->language->get('text_newsletter');
		$this->data['text_code'] = $this->language->get('text_code');
		$this->data['text_credit'] = $this->language->get('text_credit');

		$this->data['entry_email_address'] = $this->language->get('entry_email_address');
		$this->data['entry_password'] = $this->language->get('entry_password');

		$this->data['logged'] = $this->customer->isLogged();

		$this->data['register'] = $this->url->link($this->name . '/register', '', 'SSL');
		$this->data['login'] = $this->url->link($this->name . '/login', '', 'SSL');
		$this->data['logout'] = $this->url->link($this->name . '/logout', '', 'SSL');
		$this->data['forgotten'] = $this->url->link($this->name . '/forgotten', '', 'SSL');
		$this->data['account'] = $this->url->link($this->name . '/account', '', 'SSL');
		$this->data['edit'] = $this->url->link($this->name . '/edit', '', 'SSL');
		$this->data['password'] = $this->url->link($this->name . '/password', '', 'SSL');
		$this->data['address'] = $this->url->link($this->name . '/address', '', 'SSL');
		$this->data['wishlist'] = $this->url->link($this->name . '/wishlist', '', 'SSL');
		$this->data['order'] = $this->url->link($this->name . '/order', '', 'SSL');
		$this->data['download'] = $this->url->link($this->name . '/download', '', 'SSL');
		$this->data['return'] = $this->url->link($this->name . '/return', '', 'SSL');
		$this->data['addreturn'] = $this->url->link($this->name . '/return/insert', '', 'SSL');
		$this->data['transaction'] = $this->url->link($this->name . '/transaction', '', 'SSL');
		$this->data['newsletter'] = $this->url->link($this->name . '/newsletter', '', 'SSL');

		$this->data['button_login'] = $this->language->get('button_login');
		$this->data['button_logout'] = $this->language->get('button_logout');

		$this->data['action'] = $this->url->link($this->name . '/login', '', 'SSL');
		$this->data['logout'] = $this->url->link('account/logout', '', 'SSL');

		// Reward
		$this->data['reward'] = $this->config->get('reward_status') ? $this->url->link('account/reward', '', 'SSL') : '';

		// Returns
		$this->data['allow_return'] = $this->config->get('config_return_disable') ? false : true;

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		if (!$this->customer->isLogged() || ($this->customer->isLogged() && $this->config->get($this->name . '_mode') > 0)) {
			$this->resolveTemplate('module/' . $this->name);

			$this->render();
		}
	}
}
