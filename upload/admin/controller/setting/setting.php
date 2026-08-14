<?php
/**
 * Class ControllerSettingSetting
 *
 * @package NivoCart
 */
class ControllerSettingSetting extends Controller {
	private $error = [];

	// ─── Language key lists ──────────────────────────────────────────────────

	/** Keys loaded verbatim from the language file into $this->data */
	private const LANG_KEYS = [
		// Generic text
		'text_default', 'text_select', 'text_none', 'text_yes', 'text_no',
		'text_required', 'text_choice', 'text_automatic', 'text_hide',
		'text_characters', 'text_company', 'text_currencies', 'text_datetime',
		'text_product', 'text_location', 'text_preview', 'text_tax',
		'text_account', 'text_checkout', 'text_stock', 'text_supplier',
		'text_affiliate', 'text_return', 'text_reward', 'text_coupon',
		'text_voucher', 'text_administration', 'text_cookies', 'text_black',
		'text_white', 'text_top', 'text_bottom', 'text_news',
		'text_notifications', 'text_image_resize', 'text_image_labels',
		'text_image_manager', 'text_browse', 'text_clear', 'text_shipping',
		'text_payment', 'text_mail', 'text_verification', 'text_analytic',
		'text_security', 'text_search_page', 'text_block_page', 'text_upload',
		'text_home',
		// Info
		'info_meta_name',
		// Tabs
		'tab_general', 'tab_store', 'tab_local', 'tab_checkout', 'tab_option',
		'tab_preference', 'tab_image', 'tab_ftp', 'tab_mail', 'tab_media',
		'tab_server',
		// Entries
		'entry_name', 'entry_owner', 'entry_address', 'entry_email',
		'entry_email_noreply', 'entry_telephone', 'entry_company_id',
		'entry_company_tax_id', 'entry_bank_name', 'entry_bank_sort_code',
		'entry_bank_account', 'entry_title', 'entry_meta_description',
		'entry_meta_keyword', 'entry_layout', 'entry_template',
		'entry_country', 'entry_zone', 'entry_language', 'entry_admin_language',
		'entry_length_class', 'entry_weight_class', 'entry_currency',
		'entry_currency_auto', 'entry_alpha_vantage', 'entry_date_format',
		'entry_time_offset', 'entry_store_address', 'entry_store_latitude',
		'entry_store_longitude', 'entry_store_location', 'entry_map_code',
		'entry_map_display', 'entry_checkout', 'entry_invoice_prefix',
		'entry_auto_invoice', 'entry_cart_weight', 'entry_tax_breakdown',
		'entry_order_edit', 'entry_order_status', 'entry_complete_status',
		'entry_abandoned_cart', 'entry_guest_checkout', 'entry_one_page_checkout',
		'entry_one_page_phone', 'entry_one_page_newsletter', 'entry_checkout_comments',
		'entry_empty_category',
		'entry_product_count', 'entry_download', 'entry_review',
		'entry_review_login', 'entry_tax', 'entry_vat', 'entry_tax_default',
		'entry_tax_customer', 'entry_stock_display', 'entry_stock_warning',
		'entry_stock_checkout', 'entry_stock_status', 'entry_supplier_group',
		'entry_customer_approval', 'entry_customer_online', 'entry_customer_group',
		'entry_customer_group_display', 'entry_customer_price',
		'entry_customer_redirect', 'entry_customer_gender', 'entry_customer_dob',
		'entry_picklist_status', 'entry_account_captcha', 'entry_account',
		'entry_force_delete', 'entry_affiliate_approval', 'entry_affiliate_auto',
		'entry_affiliate_commission', 'entry_login_attempts', 'entry_affiliate',
		'entry_affiliate_mail', 'entry_affiliate_activity', 'entry_affiliate_disable',
		'entry_return', 'entry_return_status', 'entry_return_disable',
		'entry_reward_rate', 'entry_reward_display', 'entry_coupon_special',
		'entry_voucher_min', 'entry_voucher_max', 'entry_admin_stylesheet',
		'entry_admin_width_limit', 'entry_admin_menu_icons', 'entry_admin_limit',
		'entry_catalog_limit', 'entry_pagination_hi', 'entry_pagination_lo',
		'entry_autocomplete_category', 'entry_autocomplete_product',
		'entry_autocomplete_offer', 'entry_auto_seo_url', 'entry_user_group_display',
		'entry_catalog_barcode', 'entry_admin_barcode', 'entry_barcode_type',
		'entry_buy_now', 'entry_lightbox', 'entry_share_sharethis',
		'entry_price_free', 'entry_price_hide', 'entry_cookie_consent',
		'entry_cookie_theme', 'entry_cookie_position', 'entry_cookie_privacy',
		'entry_cookie_age', 'entry_news_sharethis', 'entry_news_chars',
		'entry_notifications', 'entry_notification_pending',
		'entry_notification_complete', 'entry_notification_return',
		'entry_notification_online', 'entry_notification_deleted',
		'entry_notification_approval', 'entry_notification_stock',
		'entry_notification_low', 'entry_notification_review',
		'entry_notification_affiliate', 'entry_notification_comment',
		'entry_logo', 'entry_icon', 'entry_apple_icon', 'entry_image_category', 'entry_image_thumb',
		'entry_image_popup', 'entry_image_product', 'entry_image_additional',
		'entry_image_brand', 'entry_image_related', 'entry_image_compare',
		'entry_image_wishlist', 'entry_image_newsthumb', 'entry_image_newspopup',
		'entry_image_cart', 'entry_label_size_ratio', 'entry_label_stock',
		'entry_label_offer', 'entry_label_special', 'entry_ftp_status',
		'entry_ftp_host', 'entry_ftp_port', 'entry_ftp_username',
		'entry_ftp_password', 'entry_ftp_root', 'entry_mail_parameter',
		'entry_alert_mail', 'entry_account_mail', 'entry_alert_emails',
		'entry_facebook', 'entry_twitter', 'entry_google', 'entry_pinterest',
		'entry_instagram', 'entry_teams', 'entry_sharethis', 'entry_meta_google',
		'entry_meta_bing', 'entry_meta_yandex', 'entry_meta_baidu',
		'entry_google_analytics', 'entry_matomo_analytics', 'entry_maintenance',
		'entry_seo_url', 'entry_seo_url_cache', 'entry_encryption',
		'entry_compression', 'entry_error_display', 'entry_error_log',
		'entry_error_filename', 'entry_mail_filename', 'entry_quote_filename',
		'entry_secure', 'entry_shared', 'entry_robots', 'entry_robots_online',
		'entry_password', 'entry_ban_page', 'entry_sitemap_links',
		'entry_file_max_size', 'entry_file_extension_allowed',
		'entry_file_mime_allowed',
		// Help
		'help_title', 'help_meta_description', 'help_meta_keyword',
		'help_currency', 'help_currency_auto', 'help_alpha_vantage',
		'help_date_format', 'help_time_offset', 'help_store_latitude',
		'help_store_longitude', 'help_store_location', 'help_map_code',
		'help_map_display', 'help_guest_checkout', 'help_checkout',
		'help_checkout_comments', 'help_invoice_prefix', 'help_auto_invoice', 'help_cart_weight',
		'help_tax_breakdown', 'help_order_edit', 'help_order_status',
		'help_complete_status', 'help_abandoned_cart', 'help_empty_category',
		'help_product_count', 'help_review', 'help_review_login', 'help_vat',
		'help_tax_default', 'help_tax_customer', 'help_stock_display',
		'help_stock_warning', 'help_stock_checkout', 'help_stock_status',
		'help_supplier_group', 'help_customer_approval', 'help_customer_online',
		'help_customer_group', 'help_customer_group_display', 'help_customer_price',
		'help_customer_redirect', 'help_customer_dob', 'help_picklist_status',
		'help_account_captcha', 'help_account', 'help_force_delete',
		'help_affiliate_approval', 'help_affiliate_auto', 'help_affiliate_commission',
		'help_login_attempts', 'help_affiliate', 'help_affiliate_mail',
		'help_affiliate_activity', 'help_affiliate_disable', 'help_return',
		'help_return_status', 'help_return_disable', 'help_reward_rate',
		'help_coupon_special', 'help_voucher_min', 'help_voucher_max',
		'help_admin_width_limit', 'help_admin_limit', 'help_catalog_limit',
		'help_pagination_hi', 'help_pagination_lo', 'help_autocomplete_category',
		'help_autocomplete_product', 'help_autocomplete_offer', 'help_auto_seo_url',
		'help_user_group_display', 'help_catalog_barcode', 'help_admin_barcode',
		'help_buy_now', 'help_lightbox', 'help_share_sharethis', 'help_price_free',
		'help_price_hide', 'help_cookie_privacy', 'help_cookie_age',
		'help_news_sharethis', 'help_news_chars', 'help_notification_return',
		'help_notification_online', 'help_notification_review',
		'help_notification_affiliate', 'help_notification_comment',
		'help_logo', 'help_icon', 'help_apple_icon', 'help_image_category', 'help_image_thumb',
		'help_image_popup', 'help_image_product', 'help_image_additional',
		'help_image_brand', 'help_image_related', 'help_image_compare',
		'help_image_wishlist', 'help_image_newsthumb', 'help_image_newspopup',
		'help_image_cart', 'help_label_size_ratio', 'help_label_stock',
		'help_label_offer', 'help_label_special', 'help_ftp_root',
		'help_mail_parameter', 'help_account_mail', 'help_alert_mail',
		'help_alert_emails', 'help_sharethis', 'help_meta_google', 'help_meta_bing',
		'help_meta_yandex', 'help_meta_baidu', 'help_google_analytics',
		'help_matomo_analytics', 'help_maintenance', 'help_seo_url',
		'help_seo_url_cache', 'help_encryption', 'help_compression',
		'help_secure', 'help_shared', 'help_robots', 'help_robots_online',
		'help_password', 'help_ban_page', 'help_sitemap_links',
		'help_file_max_size', 'help_file_extension_allowed', 'help_file_mime_allowed',
		// Buttons
		'button_themes', 'button_save', 'button_apply', 'button_cancel',
		// Date formats
		'date_format_short', 'date_format_long',
	];

	/**
	 * Config keys and their optional default values.
	 * 'default' => null means: use $this->config->get() with no fallback.
	 * 'default' => <scalar/array> means: use that value when config is empty/missing.
	 * 'default' => 'CALLBACK_xxx' means: special runtime logic (resolved below).
	 *
	 * Array keys without 'default' inherit null.
	 */
	private const CONFIG_DEFAULTS = [
		// General
		'config_name'             => [],
		'config_owner'            => [],
		'config_address'          => [],
		'config_email'            => [],
		'config_email_noreply'    => ['default' => 'CALLBACK_email_noreply'],
		'config_telephone'        => [],
		'config_company_id'       => [],
		'config_company_tax_id'   => [],
		'config_bank_name'        => [],
		'config_bank_sort_code'   => [],
		'config_bank_account'     => [],
		// Store
		'config_title'            => [],
		'config_meta_description' => [],
		'config_meta_keyword'     => [],
		'config_template'         => [],
		'config_layout_id'        => [],
		'config_admin_stylesheet' => ['default' => 'dark'],
		'config_admin_width_limit' => [],
		'config_admin_menu_icons' => [],
		'config_admin_limit'      => [],
		'config_catalog_limit'    => [],
		// Local
		'config_country_id'       => [],
		'config_zone_id'          => [],
		'config_language'         => [],
		'config_admin_language'   => [],
		'config_length_class_id'  => [],
		'config_weight_class_id'  => [],
		'config_currency'         => [],
		'config_currency_auto'    => [],
		'config_alpha_vantage'    => [],
		'config_date_format'      => [],
		'config_time_offset'      => ['default' => '0'],
		'config_store_address'    => [],
		'config_store_latitude'   => [],
		'config_store_longitude'  => [],
		'config_store_location'   => [],
		'config_map_code'         => [],
		'config_map_display'      => [],
		// Checkout
		'config_invoice_prefix'   => ['default' => 'CALLBACK_invoice_prefix'],
		'config_auto_invoice'     => [],
		'config_cart_weight'      => [],
		'config_order_edit'       => ['default' => 7],
		'config_order_status_id'  => [],
		'config_complete_status_id' => [],
		'config_abandoned_cart'   => ['default' => 7],
		'config_tax'              => [],
		'config_tax_breakdown'    => [],
		'config_vat'              => [],
		'config_tax_default'      => [],
		'config_tax_customer'     => [],
		'config_guest_checkout'   => [],
		'config_checkout_phone'   => [],
		'config_checkout_newsletter' => [],
		'config_checkout_id'      => [],
		'config_checkout_comments' => [],
		// Option
		'config_empty_category'   => [],
		'config_product_count'    => [],
		'config_download'         => [],
		'config_review_status'    => [],
		'config_review_login'     => [],
		'config_stock_display'    => [],
		'config_stock_warning'    => [],
		'config_stock_checkout'   => [],
		'config_stock_status_id'  => [],
		'config_supplier_group_id' => [],
		'config_customer_online'   => [],
		'config_customer_group_id' => [],
		'config_customer_group_display' => ['default' => []],
		'config_customer_price'    => [],
		'config_customer_redirect' => [],
		'config_customer_gender'   => [],
		'config_customer_dob'      => [],
		'config_picklist_status'  => [],
		'config_account_captcha'  => [],
		'config_account_id'       => [],
		'config_force_delete'     => [],
		'config_affiliate_approval' => [],
		'config_affiliate_auto'     => [],
		'config_affiliate_commission' => ['default' => '5.00'],
		'config_login_attempts'     => ['default' => 5],
		'config_affiliate_id'       => [],
		'config_affiliate_mail'     => ['default' => ''],
		'config_affiliate_activity' => [],
		'config_affiliate_disable'  => [],
		'config_return_id'        => [],
		'config_return_status_id' => [],
		'config_return_disable'   => [],
		'config_reward_rate'      => ['default' => 100],
		'config_reward_display'   => [],
		'config_coupon_special'   => [],
		'config_voucher_min'      => [],
		'config_voucher_max'      => [],
		// Preference
		'config_pagination_hi'    => [],
		'config_pagination_lo'    => [],
		'config_autocomplete_category' => [],
		'config_autocomplete_product'  => [],
		'config_autocomplete_offer'    => [],
		'config_auto_seo_url'       => ['default' => []],
		'config_user_group_display' => ['default' => []],
		'config_catalog_barcode'  => [],
		'config_admin_barcode'    => [],
		'config_barcode_type'     => ['default' => 'TYPE_CODE_128'],
		'config_buy_now'          => [],
		'config_lightbox'         => [],
		'config_share_sharethis'  => [],
		'config_price_free'       => [],
		'config_price_hide'       => [],
		'config_cookie_consent'   => [],
		'config_cookie_theme'     => [],
		'config_cookie_position'  => [],
		'config_cookie_privacy'   => [],
		'config_cookie_age'       => [],
		'config_news_sharethis'   => [],
		'config_news_chars'       => [],
		'config_notifications'    => [],
		'config_notification_pending'   => [],
		'config_notification_complete'  => [],
		'config_notification_return'    => [],
		'config_notification_online'    => [],
		'config_notification_deleted'   => [],
		'config_notification_approval'  => [],
		'config_notification_stock'     => [],
		'config_notification_low'       => [],
		'config_notification_review'    => [],
		'config_notification_affiliate' => [],
		'config_notification_comment'   => [],
		// Image
		'config_logo'             => [],
		'config_icon'             => [],
		'config_apple_icon'       => [],
		'config_image_category_width'   => [], 'config_image_category_height'  => [],
		'config_image_thumb_width'      => [], 'config_image_thumb_height'     => [],
		'config_image_popup_width'      => [], 'config_image_popup_height'     => [],
		'config_image_product_width'    => [], 'config_image_product_height'   => [],
		'config_image_additional_width' => [], 'config_image_additional_height'=> [],
		'config_image_brand_width'      => [], 'config_image_brand_height'     => [],
		'config_image_related_width'    => [], 'config_image_related_height'   => [],
		'config_image_compare_width'    => [], 'config_image_compare_height'   => [],
		'config_image_wishlist_width'   => [], 'config_image_wishlist_height'  => [],
		'config_image_newsthumb_width'  => [], 'config_image_newsthumb_height' => [],
		'config_image_newspopup_width'  => [], 'config_image_newspopup_height' => [],
		'config_image_cart_width'       => [], 'config_image_cart_height'      => [],
		'config_label_size_ratio' => ['default' => '60'],
		'config_label_stock'      => [],
		'config_label_offer'      => [],
		'config_label_special'    => [],
		// FTP / Upload
		'config_ftp_status'       => [],
		'config_ftp_host'         => ['default' => 'CALLBACK_ftp_host'],
		'config_ftp_port'         => ['default' => 21],
		'config_ftp_username'     => [],
		'config_ftp_password'     => [],
		'config_ftp_root'         => [],
		'config_file_max_size'    => ['default' => 2048000],
		'config_file_extension_allowed' => [],
		'config_file_mime_allowed'      => [],
		// Mail
		'config_mail_parameter'   => [],
		'config_alert_mail'       => [],
		'config_account_mail'     => [],
		'config_alert_emails'     => [],
		// Media
		'config_facebook'         => [],
		'config_twitter'          => [],
		'config_google'           => [],
		'config_pinterest'        => [],
		'config_instagram'        => [],
		'config_teams'            => [],
		'config_sharethis'        => [],
		'config_meta_google'      => [],
		'config_meta_bing'        => [],
		'config_meta_yandex'      => [],
		'config_meta_baidu'       => [],
		'config_google_analytics' => [],
		'config_matomo_analytics' => [],
		// Server
		'config_maintenance'      => [],
		'config_seo_url'          => [],
		'config_seo_url_cache'    => ['default' => 'CALLBACK_seo_url_cache'],
		'config_encryption'       => [],
		'config_compression'      => [],
		'config_error_display'    => [],
		'config_error_log'        => [],
		'config_error_filename'   => [],
		'config_mail_filename'    => [],
		'config_quote_filename'   => [],
		'config_secure'           => [],
		'config_shared'           => [],
		'config_robots'           => [],
		'config_robots_online'    => [],
		'config_password'         => [],
		'config_ban_page'         => [],
		'config_sitemap_links'    => [],
	];

	// ─── Error keys that map directly from $this->error ─────────────────────

	private const ERROR_KEYS = [
		'warning', 'name', 'owner', 'address', 'email', 'email_noreply',
		'telephone', 'title', 'customer_group_display', 'login_attempts',
		'reward_rate', 'voucher_min', 'voucher_max', 'catalog_limit',
		'admin_limit', 'preference_pagination', 'image_category', 'image_thumb',
		'image_popup', 'image_product', 'image_additional', 'image_brand',
		'image_related', 'image_compare', 'image_wishlist', 'image_newsthumb',
		'image_newspopup', 'image_cart', 'ftp_host', 'ftp_port', 'ftp_username',
		'ftp_password', 'error_filename', 'file_max_size', 'mail_filename',
		'quote_filename', 'encryption',
	];

	// ────────────────────────────────────────────────────────────────────────

	public function index(): void {
		$this->language->load('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('config', $this->request->post);

			if (!empty($this->request->post['config_currency_auto'])) {
				$this->load->model('localisation/currency');
				$this->model_localisation_currency->updateCurrencies();
			}

			if (!empty($this->request->post['config_seo_url']) && !file_exists('../.htaccess')) {
				$this->load->model('tool/system');
				$this->model_tool_system->setupSeo();
			}

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['apply'])) {
				$this->redirect($this->url->link('setting/setting', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL'));
			}

			return;
		}

		// ── Language strings ─────────────────────────────────────────────────
		$this->data['heading_title'] = $this->language->get('heading_title');

		foreach (self::LANG_KEYS as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		// help_store_address intentionally maps to a different language key
		$this->data['help_store_address'] = $this->language->get('help_file_extension_allowed');

		// ── Config values ────────────────────────────────────────────────────
		$post = $this->request->post;

		foreach (self::CONFIG_DEFAULTS as $key => $opts) {
			if (isset($post[$key])) {
				$this->data[$key] = $post[$key];
				continue;
			}

			$stored = $this->config->get($key);
			$default = $opts['default'] ?? null;

			if ($default === null) {
				$this->data[$key] = $stored;
				continue;
			}

			// Scalar/array defaults: use stored value when non-empty, else default
			if (!is_string($default) || strncmp($default, 'CALLBACK_', 9) !== 0) {
				$this->data[$key] = $stored ?: $default;
				continue;
			}

			// Runtime callbacks for values that need live server data
			$this->data[$key] = $this->resolveConfigCallback($default, $stored);
		}

		// ── Errors ───────────────────────────────────────────────────────────
		foreach (self::ERROR_KEYS as $error_key) {
			$dataKey = ($error_key === 'error_filename') ? 'error_error_filename' : 'error_' . $error_key;

			$this->data[$dataKey] = $this->error[$error_key] ?? '';
		}

		// ── Internal links ───────────────────────────────────────────────────
		$token = $this->session->data['token'];

		$this->data['customer_approval'] = $this->url->link('sale/customer_group', 'token=' . $token, 'SSL');
		$this->data['themes'] = $this->url->link('extension/theme', 'token=' . $token, 'SSL');
		$this->data['configure_theme'] = $this->url->link('extension/theme', 'token=' . $token, 'SSL');
		$this->data['configure_layout'] = $this->url->link('design/layout', 'token=' . $token, 'SSL');
		$this->data['configure_language'] = $this->url->link('localisation/language', 'token=' . $token, 'SSL');
		$this->data['configure_length_class'] = $this->url->link('localisation/length_class', 'token=' . $token, 'SSL');
		$this->data['configure_weight_class'] = $this->url->link('localisation/weight_class', 'token=' . $token, 'SSL');
		$this->data['configure_currency'] = $this->url->link('localisation/currency', 'token=' . $token, 'SSL');

		// ── Breadcrumbs ──────────────────────────────────────────────────────
		$this->data['breadcrumbs'] = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $token, 'SSL'),
				'separator' => false,
			],
			[
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('setting/setting', 'token=' . $token, 'SSL'),
				'separator' => ' :: ',
			],
		];

		// ── Success flash ────────────────────────────────────────────────────
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		// ── Form action / cancel ─────────────────────────────────────────────
		$this->data['action'] = $this->url->link('setting/setting', 'token=' . $token, 'SSL');
		$this->data['cancel'] = $this->url->link('setting/store', 'token=' . $token, 'SSL');

		$this->data['token'] = $token;

		// ── Dynamic select lists ─────────────────────────────────────────────
		$this->buildSelectLists();

		// ── Image thumbnails (logo, icon, labels) ───────────────────────────
		$this->buildImageThumbnails();

		// ── Static option lists ──────────────────────────────────────────────
		$this->data['date_formats'] = [
			['format' => 'short', 'title' => $this->language->get('date_format_short')],
			['format' => 'long', 'title' => $this->language->get('date_format_long')],
		];

		$this->data['time_offsets'] = [
			'+11', '+10', '+9', '+8', '+7', '+6', '+5', '+4', '+3', '+2', '+1',
			'0', '-1', '-2', '-3', '-4', '-5', '-6', '-7', '-8', '-9', '-10', '-11',
		];

		$this->data['seo_url_pages'] = ['Category', 'Product', 'Manufacturer', 'Information', 'News'];

		$this->data['barcode_types'] = [
			['format' => 'TYPE_CODE_39',  'title' => 'Barcode Code 39'],
			['format' => 'TYPE_CODE_93',  'title' => 'Barcode Code 93'],
			['format' => 'TYPE_CODE_128', 'title' => 'Barcode Code 128'],
		];

		$this->data['label_ratios'] = array_map(
			static fn($r) => ['ratio' => (string) $r, 'title' => $r . '%'],
			range(20, 65, 5)
		);

		$this->data['google_web'] = 'https://www.google.com/webmasters/tools/home';
		$this->data['bing_web'] = 'https://ssl.bing.com/webmaster/home/mysites';
		$this->data['yandex_web'] = 'http://webmaster.yandex.com/sites/';
		$this->data['baidu_web'] = 'http://zhanzhang.baidu.com/sitemap/index';
		$this->data['matomo_web'] = 'https://matomo.org/';

		// ── Templates list ───────────────────────────────────────────────────
		$this->data['templates'] = $this->getTemplateList();

		// ── Render ───────────────────────────────────────────────────────────
		$this->template = 'setting/setting.tpl';

		$this->children = ['common/header', 'common/footer'];

		$this->response->setOutput($this->render());
	}

	// ─── Private helpers ─────────────────────────────────────────────────────

	/**
	 * Resolve runtime callbacks for config defaults that depend on server state.
	 */
	private function resolveConfigCallback(string $callback, mixed $stored): mixed {
		return match ($callback) {
			'CALLBACK_email_noreply'  => $stored ?: 'noreply@' . $this->request->server['SERVER_NAME'],
			'CALLBACK_invoice_prefix' => $stored ?: 'INV-' . date('Y') . '-00',
			'CALLBACK_ftp_host'       => $stored ?: str_replace('www.', '', $this->request->server['HTTP_HOST']),
			'CALLBACK_seo_url_cache'  => $this->resolveSeoUrlCache($stored),
			default                   => $stored,
		};
	}

	/**
	 * SEO URL cache needs two config reads, so it gets its own method.
	 */
	private function resolveSeoUrlCache(mixed $stored): mixed {
		$post = $this->request->post;

		if (isset($post['config_seo_url_cache'])) {
			return (isset($post['config_seo_url']) || $this->config->get('config_seo_url')) ? $post['config_seo_url_cache'] : 0;
		}

		return $this->config->get('config_seo_url') ? $stored : 0;
	}

	/**
	 * Load all model-backed select lists into $this->data.
	 */
	private function buildSelectLists(): void {
		$this->load->model('localisation/country');
		$this->data['countries'] = $this->model_localisation_country->getCountries([]);

		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages([]);

		$this->load->model('localisation/length_class');
		$this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses([]);

		$this->load->model('localisation/weight_class');
		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses([]);

		$this->load->model('localisation/currency');
		$this->data['currencies'] = $this->model_localisation_currency->getCurrencies([]);

		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses([]);

		$this->load->model('localisation/stock_status');
		$this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses([]);

		$this->load->model('localisation/return_status');
		$this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses([]);

		$this->load->model('sale/supplier_group');
		$this->data['supplier_groups'] = $this->model_sale_supplier_group->getSupplierGroups([]);

		$this->load->model('sale/customer_group');
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups([]);

		$this->load->model('catalog/information');
		$this->data['informations'] = $this->model_catalog_information->getInformations([]);
		$this->data['information_pages'] = $this->model_catalog_information->getInformationPages();

		$this->load->model('design/layout');
		$this->data['layouts'] = $this->model_design_layout->getLayouts([]);

		$this->load->model('design/administration');
		$this->data['admin_stylesheets'] = $this->model_design_administration->getAdministrations([]);

		$this->load->model('user/user_group');
		$this->data['user_groups'] = $this->model_user_user_group->getUserGroups([]);
	}

	/**
	 * Build image thumbnail URLs for logo, icon, and label images.
	 */
	private function buildImageThumbnails(): void {
		$this->load->model('tool/image');

		$this->data['no_image'] = $this->model_tool_image->resize('no_image.png', 120, 120);

		$imageFields = ['logo' => 'config_logo', 'icon' => 'config_icon', 'apple_icon' => 'config_apple_icon'];

		foreach ($imageFields as $thumbKey => $configKey) {
			$path = $this->config->get($configKey);

			$this->data[$thumbKey] = ($path && file_exists(DIR_IMAGE . $path) && is_file(DIR_IMAGE . $path)) ? $this->model_tool_image->resize($path, 120, 120) : $this->model_tool_image->resize('no_image.png', 120, 120);
		}

		foreach (['label_stock', 'label_offer', 'label_special'] as $label) {
			$path = $this->config->get('config_' . $label);

			$this->data[$label] = ($path && file_exists(DIR_IMAGE . $path) && is_file(DIR_IMAGE . $path)) ? $this->model_tool_image->resize($path, 120, 120) : $this->model_tool_image->resize('no_image.png', 120, 120);
		}
	}

	/**
	 * Scan the theme directory and return a list of available templates.
	 */
	private function getTemplateList(): array {
		$server = $this->catalogBaseUrl();

		$themePath = DIR_CATALOG . 'view/theme/';

		$templates = [];

		if (!is_dir($themePath)) {
			return $templates;
		}

		$iterator = new FilesystemIterator($themePath, FilesystemIterator::SKIP_DOTS);

		foreach ($iterator as $entry) {
			if (!$entry->isDir()) {
				continue;
			}

			$name = $entry->getFilename();

			$image = file_exists(DIR_IMAGE . 'templates/' . $name . '.png') ? $server . 'image/templates/' . $name . '.png' : $server . 'image/templates/default.png';

			$templates[] = ['name' => $name, 'image' => $image];
		}

		return $templates;
	}

	/**
	 * Return the catalog base URL, honouring HTTPS proxies.
	 */
	private function catalogBaseUrl(): string {
		$srv = $this->request->server;

		$isHttps = (!empty($srv['HTTPS']) && in_array($srv['HTTPS'], ['on', '1'], true))
			|| (isset($srv['SERVER_PORT']) && $srv['SERVER_PORT'] === '443')
			|| (isset($srv['HTTP_X_FORWARDED_PROTO']) && $srv['HTTP_X_FORWARDED_PROTO'] === 'https');

		return $isHttps ? HTTPS_CATALOG : HTTP_CATALOG;
	}

	// ─── Validation ──────────────────────────────────────────────────────────

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'setting/setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$post = $this->request->post;

		$activeTab = $post['config_active_tab'] ?? 'general';

		// General (always validated)
		$this->validateLength('config_name', 'error_name', 3, 32);
		$this->validateLength('config_owner', 'error_owner', 3, 64);
		$this->validateLength('config_address', 'error_address', 3, 256);
		$this->validateEmail('config_email', 'error_email', 96);
		$this->validateEmail('config_email_noreply', 'error_email_noreply', 96);
		$this->validateLength('config_telephone','error_telephone', 3, 32);

		// Tab-specific
		match ($activeTab) {
			'store'      => $this->validateStore(),
			'option'     => $this->validateOption(),
			'preference' => $this->validatePreference(),
			'image'      => $this->validateImage(),
			'ftp'        => $this->validateFtp(),
			'server'     => $this->validateServer(),
			default      => null,
		};

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}

	private function validateStore(): void {
		$this->validateLength('config_title', 'error_title', 3, 32);
	}

	private function validateOption(): void {
		$post = $this->request->post;

		if (!empty($post['config_customer_group_display']) && !in_array($post['config_customer_group_id'], $post['config_customer_group_display'])) {
			$this->error['customer_group_display'] = $this->language->get('error_customer_group_display');
		}

		if (empty($post['config_login_attempts']) || $post['config_login_attempts'] < 1) {
			$this->error['login_attempts'] = $this->language->get('error_login_attempts');
		}

		$rate = $post['config_reward_rate'] ?? null;

		if (!$rate || (int) $rate != $rate || $rate < 1) {
			$this->error['reward_rate'] = $this->language->get('error_reward_rate');
		}

		if (empty($post['config_voucher_min'])) {
			$this->error['voucher_min'] = $this->language->get('error_voucher_min');
		}

		if (empty($post['config_voucher_max'])) {
			$this->error['voucher_max'] = $this->language->get('error_voucher_max');
		}
	}

	private function validatePreference(): void {
		$post = $this->request->post;

		if (empty($post['config_admin_limit'])) {
			$this->error['admin_limit'] = $this->language->get('error_limit');
		}

		if (empty($post['config_catalog_limit'])) {
			$this->error['catalog_limit'] = $this->language->get('error_limit');
		}

		if (empty($post['config_pagination_hi']) && empty($post['config_pagination_lo'])) {
			$this->error['preference_pagination'] = $this->language->get('error_preference_pagination');
		}
	}

	private function validateImage(): void {
		foreach ([
			'image_category', 'image_thumb', 'image_popup', 'image_product',
			'image_additional', 'image_brand', 'image_related', 'image_compare',
			'image_wishlist', 'image_newsthumb', 'image_newspopup', 'image_cart',
		] as $field) {
			$width = $this->request->post['config_' . $field . '_width'] ?? '';
			$height = $this->request->post['config_' . $field . '_height'] ?? '';

			if (empty($width) || empty($height)) {
				$this->error[$field] = $this->language->get('error_' . $field);
			}
		}
	}

	private function validateFtp(): void {
		$post = $this->request->post;

		if (empty($post['config_ftp_status'])) {
			return;
		}

		foreach (['ftp_host', 'ftp_port', 'ftp_username', 'ftp_password'] as $field) {
			if (empty($post['config_' . $field])) {
				$this->error[$field] = $this->language->get('error_' . $field);
			}
		}
	}

	private function validateServer(): void {
		$post = $this->request->post;

		foreach (['error_filename', 'mail_filename', 'quote_filename'] as $field) {
			if (empty($post['config_' . $field]) || !preg_match('/\.txt$/i', $post['config_' . $field])) {
				$this->error[$field] = $this->language->get('error_' . $field);
			}
		}

		if (empty($post['config_file_max_size']) || $post['config_file_max_size'] < 100000) {
			$this->error['file_max_size'] = $this->language->get('error_file_max_size');
		}

		$enc = $post['config_encryption'] ?? '';

		$len = mb_strlen($enc, 'UTF-8');

		if ($len < 8 || $len > 32) {
			$this->error['encryption'] = $this->language->get('error_encryption');
		}
	}

	// ─── Micro-validation helpers ─────────────────────────────────────────────

	/**
	 * Write an error keyed by the bare field name (strip leading 'error_' prefix).
	 * e.g. 'error_name' → $this->error['name']
	 */
	private function errorKey(string $error_key): string {
		return str_starts_with($error_key, 'error_') ? substr($error_key, 6) : $error_key;
	}

	private function validateLength(string $post_key, string $error_key, int $min, int $max): void {
		$val = $this->request->post[$post_key] ?? '';
		$len = mb_strlen($val, 'UTF-8');

		if ($len < $min || $len > $max) {
			$this->error[$this->errorKey($error_key)] = $this->language->get($error_key);
		}
	}

	private function validateEmail(string $post_key, string $error_key, int $max_length): void {
		$val = $this->request->post[$post_key] ?? '';

		if (mb_strlen($val, 'UTF-8') > $max_length || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $val)) {
			$this->error[$this->errorKey($error_key)] = $this->language->get($error_key);
		}
	}

	// ─── AJAX endpoints ───────────────────────────────────────────────────────

	public function template(): void {
		$server = $this->catalogBaseUrl();

		$name = basename($this->request->get['template'] ?? '');

		$image = file_exists(DIR_IMAGE . 'templates/' . $name . '.png') ? $server . 'image/templates/' . $name . '.png' : $server . 'image/no_image.png';

		$this->response->setOutput('<img src="' . $image . '" alt="" title="" style="border:1px solid #EEE;" />');
	}

	public function country(): void {
		$this->load->model('localisation/country');

		$json = [];

		$info = $this->model_localisation_country->getCountry((int) ($this->request->get['country_id'] ?? 0));

		if ($info) {
			$this->load->model('localisation/zone');

			$json = [
				'country_id'        => $info['country_id'],
				'name'              => $info['name'],
				'iso_code_2'        => $info['iso_code_2'],
				'iso_code_3'        => $info['iso_code_3'],
				'address_format'    => $info['address_format'],
				'postcode_required' => $info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($info['country_id']),
				'status'            => $info['status'],
			];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
