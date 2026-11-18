<?php
/**
 * Class ControllerModuleTrustpilotWidget
 *
 * Trustpilot "Review Collector" widget (free plan).
 * The Trustpilot script is only loaded when the visitor has accepted cookies, unless the setting says otherwise.
 *
 * @package NivoCart
 */
class ControllerModuleTrustpilotWidget extends Controller {
	private $name = 'trustpilot_widget';

	protected function index($setting) {
		static $module = 0;
		static $script = false;

		if (!$this->config->get($this->name . '_business_id')) {
			return;
		}

		$this->language->load('module/' . $this->name);

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_review'] = $this->language->get('text_review');

		// Module
		$this->data['theme'] = $this->config->get($this->name . '_theme');
		$this->data['title'] = $this->config->get($this->name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$this->data['business_id'] = $this->config->get($this->name . '_business_id');

		// Trustpilot page name: the saved one, otherwise the domain of the store address (without www)
		$store_name = trim((string)$this->config->get($this->name . '_store_name'));

		if ($store_name === '') {
			$store_name = preg_replace('/^www\./i', '', (string)parse_url($this->config->get('config_url'), PHP_URL_HOST));
		}

		if ($store_name === '') {
			return;
		}

		$this->data['review_url'] = 'https://www.trustpilot.com/review/' . rawurlencode($store_name);
		$this->data['template_id'] = $this->config->get($this->name . '_template_id');
		$this->data['locale'] = $this->getLocale();
		$this->data['height'] = $this->config->get($this->name . '_height');
		$this->data['width'] = $this->config->get($this->name . '_width');

		$this->data['consent'] = !$this->config->get($this->name . '_consent') || $this->hasConsent();

		// The Trustpilot script is only needed once per page, however many widgets are shown
		$this->data['load_script'] = $this->data['consent'] && !$script;

		if ($this->data['load_script']) {
			$script = true;
		}

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('module/' . $this->name);

		$this->render();
	}

	/**
	 * Trustpilot locale: the one saved in the module, otherwise built from the language the visitor is using
	 * (a code such as en-gb becomes en-GB, a bare code such as fr is mapped to its usual locale).
	 *
	 * @return string
	 */
	private function getLocale(): string {
		$locale = (string)$this->config->get($this->name . '_locale');

		if ($locale !== '') {
			return $locale;
		}

		$code = strtolower((string)$this->session->data['language']);

		if (preg_match('/^([a-z]{2})[-_]([a-z]{2})$/', $code, $match)) {
			return $match[1] . '-' . strtoupper($match[2]);
		}

		$locales = [
			'da' => 'da-DK',
			'de' => 'de-DE',
			'en' => 'en-GB',
			'es' => 'es-ES',
			'fi' => 'fi-FI',
			'fr' => 'fr-FR',
			'it' => 'it-IT',
			'nb' => 'nb-NO',
			'nl' => 'nl-NL',
			'no' => 'nb-NO',
			'pl' => 'pl-PL',
			'pt' => 'pt-PT',
			'sv' => 'sv-SE'
		];

		return isset($locales[$code]) ? $locales[$code] : 'en-GB';
	}

	/**
	 * Same rule as the Google Analytics code in common/header:
	 * the saved preference of a logged in customer first, otherwise the cookie banner choice.
	 *
	 * @return bool
	 */
	private function hasConsent(): bool {
		if ($this->customer->isLogged()) {
			$this->load->model('account/customer');

			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

			if (isset($customer_info['cookie_analytics_consent']) && $customer_info['cookie_analytics_consent'] !== null) {
				return (int)$customer_info['cookie_analytics_consent'] === 1;
			}
		}

		return isset($this->request->cookie['cc_accepted']) && $this->request->cookie['cc_accepted'] === '1';
	}
}
