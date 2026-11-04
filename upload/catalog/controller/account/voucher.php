<?php
/**
 * Class ControllerAccountVoucher
 *
 * @package NivoCart
 */
class ControllerAccountVoucher extends Controller {
	private $error = [];

	/** Maximum number of gift vouchers in one cart and message length */
	private const MAX_VOUCHERS = 10;
	private const MAX_MESSAGE = 200;

	public function index() {
		if (!$this->config->get('config_checkout_voucher')) {
			$this->redirect($this->url->link('common/home'));
		}

		if ($this->config->get('config_secure') && !$this->request->isSecure()) {
			$this->redirect($this->url->link('account/voucher', '', 'SSL'), 301);
		}

		$this->language->load('account/voucher');

		$this->document->setTitle($this->language->get('heading_title'));

		if (!isset($this->session->data['vouchers'])) {
			$this->session->data['vouchers'] = [];
		}

		$presets = $this->getPresets();

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate($presets)) {
			$amount = $this->getPostedAmount($presets);

			$this->session->data['vouchers'][random_int(100000, 999999999)] = [
				'description' => sprintf($this->language->get('text_for'), $this->currency->format($amount, $this->config->get('config_currency')), $this->request->post['to_name']),
				'to_name'     => $this->request->post['to_name'],
				'to_email'    => $this->request->post['to_email'],
				'from_name'   => $this->request->post['from_name'],
				'from_email'  => $this->request->post['from_email'],
				'design'      => $this->request->post['design'],
				'consent'     => true,
				'message'     => $this->request->post['message'],
				'amount'      => $amount
			];

			$this->redirect($this->url->link('account/voucher/success'));
		}

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_voucher'),
			'href'      => $this->url->link('account/voucher', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_description'] = $this->language->get('text_description');
		$this->data['text_step_amount'] = $this->language->get('text_step_amount');
		$this->data['text_step_occasion'] = $this->language->get('text_step_occasion');
		$this->data['text_step_details'] = $this->language->get('text_step_details');
		$this->data['text_step_agree'] = $this->language->get('text_step_agree');
		$this->data['text_custom_amount'] = sprintf($this->language->get('text_custom_amount'), $this->currency->format($this->config->get('config_voucher_min'), $this->config->get('config_currency')), $this->currency->format($this->config->get('config_voucher_max'), $this->config->get('config_currency')));
		$this->data['text_preview'] = $this->language->get('text_preview');
		$this->data['text_preview_code'] = $this->language->get('text_preview_code');
		$this->data['text_characters'] = $this->language->get('text_characters');
		$this->data['text_agree'] = $this->language->get('text_agree');
		$this->data['text_age'] = $this->language->get('text_age');

		$months = (int)$this->config->get('config_voucher_expiry');

		$this->data['text_validity'] = ($months > 0) ? sprintf($this->language->get('text_validity'), $months) : $this->language->get('text_no_expiry');

		$this->data['entry_to_name'] = $this->language->get('entry_to_name');
		$this->data['entry_to_email'] = $this->language->get('entry_to_email');
		$this->data['entry_from_name'] = $this->language->get('entry_from_name');
		$this->data['entry_from_email'] = $this->language->get('entry_from_email');
		$this->data['entry_message'] = $this->language->get('entry_message');

		$this->data['button_add'] = $this->language->get('button_add');

		// Preview texts used by the page script
		$this->data['preview_to'] = $this->language->get('text_preview_to');
		$this->data['preview_from'] = $this->language->get('text_preview_from');
		$this->data['preview_name'] = $this->language->get('text_preview_name');
		$this->data['preview_you'] = $this->language->get('text_preview_you');

		$errors = ['warning', 'to_name', 'to_email', 'from_name', 'from_email', 'design', 'amount', 'message'];

		foreach ($errors as $key) {
			$this->data['error_' . $key] = $this->error[$key] ?? '';
		}

		$this->data['action'] = $this->url->link('account/voucher', '', 'SSL');

		$this->data['to_name'] = $this->request->post['to_name'] ?? '';
		$this->data['to_email'] = $this->request->post['to_email'] ?? '';

		if (isset($this->request->post['from_name'])) {
			$this->data['from_name'] = $this->request->post['from_name'];
		} elseif ($this->customer->isLogged()) {
			$this->data['from_name'] = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
		} else {
			$this->data['from_name'] = '';
		}

		if (isset($this->request->post['from_email'])) {
			$this->data['from_email'] = $this->request->post['from_email'];
		} elseif ($this->customer->isLogged()) {
			$this->data['from_email'] = $this->customer->getEmail();
		} else {
			$this->data['from_email'] = '';
		}

		// Occasions
		$this->language->load('mail/voucher');

		$this->data['designs'] = [];

		foreach (voucherDesigns() as $design_key) {
			$this->data['designs'][] = [
				'value' => $design_key,
				'name'  => $this->language->get('text_design_' . $design_key)
			];
		}

		$this->language->load('account/voucher');

		$this->data['design'] = $this->request->post['design'] ?? 'general';

		// Amounts
		$this->data['presets'] = [];

		foreach ($presets as $preset) {
			$this->data['presets'][] = [
				'value' => $preset,
				'text'  => $this->currency->format($preset, $this->config->get('config_currency'))
			];
		}

		if (isset($this->request->post['preset'])) {
			$this->data['preset'] = (float)$this->request->post['preset'];
		} elseif ($presets) {
			$this->data['preset'] = (float)$presets[min(1, count($presets) - 1)];
		} else {
			$this->data['preset'] = 0;
		}

		$this->data['amount'] = $this->request->post['amount'] ?? '';

		$this->data['symbol_left'] = $this->currency->getSymbolLeft($this->config->get('config_currency'));
		$this->data['symbol_right'] = $this->currency->getSymbolRight($this->config->get('config_currency'));

		$this->data['message'] = $this->request->post['message'] ?? '';
		$this->data['message_max'] = self::MAX_MESSAGE;

		$this->data['agree'] = $this->request->post['agree'] ?? false;
		$this->data['age'] = $this->request->post['age'] ?? false;

		// Terms and conditions page
		$this->data['terms'] = '';
		$this->data['text_terms'] = '';

		$information_id = (int)$this->config->get('config_voucher_information_id');

		if ($information_id) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($information_id);

			if ($information_info) {
				$this->data['terms'] = $this->url->link('information/information', 'information_id=' . $information_id, 'SSL');
				$this->data['text_terms'] = sprintf($this->language->get('text_terms'), $information_info['title']);
			}
		}

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('account/voucher');

		$this->children = [
			'common/content_higher',
			'common/content_high',
			'common/content_left',
			'common/content_right',
			'common/content_low',
			'common/content_lower',
			'common/footer',
			'common/header'
		];

		$this->response->setOutput($this->render());
	}

	public function success() {
		$this->language->load('account/voucher');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/voucher', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_message'] = $this->language->get('text_message');

		$this->data['button_continue'] = $this->language->get('button_cart');

		$this->data['continue'] = $this->url->link('checkout/cart', '', 'SSL');

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('common/success');

		$this->children = [
			'common/content_higher',
			'common/content_high',
			'common/content_left',
			'common/content_right',
			'common/content_low',
			'common/content_lower',
			'common/footer',
			'common/header'
		];

		$this->response->setOutput($this->render());
	}

	/**
	 * Gift vouchers bought by the logged in customer.
	 */
	public function purchased() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/voucher/purchased', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->language->load('account/voucher');

		$this->document->setTitle($this->language->get('heading_purchased'));

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_purchased'),
			'href'      => $this->url->link('account/voucher/purchased', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['heading_title'] = $this->language->get('heading_purchased');

		$this->data['text_none'] = $this->language->get('text_none');

		$this->data['column_recipient'] = $this->language->get('column_recipient');
		$this->data['column_value'] = $this->language->get('column_value');
		$this->data['column_balance'] = $this->language->get('column_balance');
		$this->data['column_sent'] = $this->language->get('column_sent');
		$this->data['column_expiry'] = $this->language->get('column_expiry');
		$this->data['column_state'] = $this->language->get('column_state');

		$this->data['button_back'] = $this->language->get('button_back');
		$this->data['button_add'] = $this->language->get('button_add');

		$this->data['back'] = $this->url->link('account/account', '', 'SSL');
		$this->data['add'] = $this->url->link('account/voucher', '', 'SSL');

		$this->load->model('checkout/voucher');

		$date_format = $this->language->get('date_format_short');

		if ($date_format === 'date_format_short') {
			$date_format = 'd/m/Y';
		}

		$this->data['vouchers'] = [];

		foreach ($this->model_checkout_voucher->getVouchersByCustomer($this->customer->getId()) as $result) {
			$this->data['vouchers'][] = [
				'recipient' => $result['to_name'],
				'email'     => $result['to_email'],
				'value'     => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'balance'   => $this->currency->format(max(0, $result['balance']), $this->config->get('config_currency')),
				'sent'      => $result['date_sent'] ? date($date_format, strtotime($result['date_sent'])) : $this->language->get('text_not_sent'),
				'expiry'    => $result['date_expire'] ? date($date_format, strtotime($result['date_expire'])) : '-',
				'state'     => $this->language->get('text_state_' . $result['state'])
			];
		}

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('account/voucher_list');

		$this->children = [
			'common/content_higher',
			'common/content_high',
			'common/content_left',
			'common/content_right',
			'common/content_low',
			'common/content_lower',
			'common/footer',
			'common/header'
		];

		$this->response->setOutput($this->render());
	}

	/**
	 * Preset amounts from the settings, within the minimum and maximum.
	 */
	private function getPresets(): array {
		$min = (float)$this->config->get('config_voucher_min');
		$max = (float)$this->config->get('config_voucher_max');

		$presets = [];

		foreach (explode(',', (string)$this->config->get('config_voucher_presets')) as $value) {
			$value = trim($value);

			if (is_numeric($value) && (float)$value > 0 && (float)$value >= $min && (float)$value <= $max) {
				$presets[] = round((float)$value, 2);
			}
		}

		$presets = array_values(array_unique($presets));

		sort($presets);

		return $presets;
	}

	/**
	 * Amount in the store currency: a typed amount wins over a preset button.
	 */
	private function getPostedAmount(array $presets) {
		$custom = trim((string)($this->request->post['amount'] ?? ''));

		if ($custom !== '') {
			$custom = str_replace(',', '.', $custom);

			return is_numeric($custom) ? round((float)$custom, 2) : false;
		}

		$preset = isset($this->request->post['preset']) ? round((float)$this->request->post['preset'], 2) : 0;

		return in_array($preset, $presets, true) ? $preset : false;
	}

	protected function validate(array $presets) {
		if (count($this->session->data['vouchers']) >= self::MAX_VOUCHERS) {
			$this->error['warning'] = sprintf($this->language->get('error_limit'), self::MAX_VOUCHERS);

			return false;
		}

		$post = $this->request->post;

		if ((mb_strlen($post['to_name'] ?? '', 'UTF-8') < 1) || (mb_strlen($post['to_name'], 'UTF-8') > 64)) {
			$this->error['to_name'] = $this->language->get('error_to_name');
		}

		if (!emailIsValid($post['to_email'] ?? '')) {
			$this->error['to_email'] = $this->language->get('error_email');
		}

		if ((mb_strlen($post['from_name'] ?? '', 'UTF-8') < 1) || (mb_strlen($post['from_name'], 'UTF-8') > 64)) {
			$this->error['from_name'] = $this->language->get('error_from_name');
		}

		if (!emailIsValid($post['from_email'] ?? '')) {
			$this->error['from_email'] = $this->language->get('error_email');
		}

		if (!isset($post['design']) || !voucherDesignIsValid($post['design'])) {
			$this->error['design'] = $this->language->get('error_design');
		}

		if (mb_strlen(html_entity_decode($post['message'] ?? '', ENT_QUOTES, 'UTF-8'), 'UTF-8') > self::MAX_MESSAGE) {
			$this->error['message'] = $this->language->get('error_message');
		}

		$amount = $this->getPostedAmount($presets);

		if ($amount === false || $amount < (float)$this->config->get('config_voucher_min') || $amount > (float)$this->config->get('config_voucher_max')) {
			$this->error['amount'] = sprintf($this->language->get('error_amount'), $this->currency->format($this->config->get('config_voucher_min'), $this->config->get('config_currency')), $this->currency->format($this->config->get('config_voucher_max'), $this->config->get('config_currency')));
		}

		if (!isset($post['agree'])) {
			$this->error['warning'] = $this->language->get('error_agree');
		} elseif (!isset($post['age'])) {
			$this->error['warning'] = $this->language->get('error_age');
		}

		return empty($this->error);
	}
}
