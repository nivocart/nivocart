<?php
/**
 * Class ControllerPaymentKlarna
 *
 * Admin settings screen for Klarna Payments (modern REST API).
 *
 * Credentials are issued per-region by Klarna (EU / NA / OC), not per
 * country, so settings are grouped by region. Each region contains a
 * sub-list of countries that can individually be enabled/disabled and
 * given a geo zone + sort order for the checkout payment method list.
 *
 * @package NivoCart
 */
class ControllerPaymentKlarna extends Controller {
	private $error = [];

	/**
	 * Region definitions — the countries listed under each region are
	 * the ones currently offered in the admin screen. Extending coverage
	 * later is just adding entries to these arrays.
	 */
	private $regions = [
		'eu' => [
			'AT', 'BE', 'DE', 'DK', 'FI', 'FR', 'GR', 'IE', 'IT',
			'NL', 'NO', 'PL', 'PT', 'ES', 'SE', 'CH', 'GB'
		],
		'na' => [
			'US', 'CA'
		],
		'oc' => [
			'AU', 'NZ'
		]
	];

	public function index() {
		$this->language->load('payment/klarna');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('klarna', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if (isset($this->request->post['apply'])) {
				$this->redirect($this->url->link('payment/klarna', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_klarna'] = $this->language->get('text_klarna');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_live'] = $this->language->get('text_live');
		$this->data['text_playground'] = $this->language->get('text_playground');

		$this->data['text_region_eu'] = $this->language->get('text_region_eu');
		$this->data['text_region_na'] = $this->language->get('text_region_na');
		$this->data['text_region_oc'] = $this->language->get('text_region_oc');

		$this->data['entry_username'] = $this->language->get('entry_username');
		$this->data['entry_password'] = $this->language->get('entry_password');
		$this->data['entry_server'] = $this->language->get('entry_server');
		$this->data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$this->data['entry_accepted_status'] = $this->language->get('entry_accepted_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_country'] = $this->language->get('entry_country');

		$this->data['help_username'] = $this->language->get('help_username');
		$this->data['help_password'] = $this->language->get('help_password');
		$this->data['help_server'] = $this->language->get('help_server');
		$this->data['help_pending_status'] = $this->language->get('help_pending_status');
		$this->data['help_accepted_status'] = $this->language->get('help_accepted_status');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_clear'] = $this->language->get('button_clear');

		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_log'] = $this->language->get('tab_log');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/klarna', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['action'] = $this->url->link('payment/klarna', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		// Region metadata for the template loop — code, label key, country list
		$this->data['regions'] = [];

		foreach ($this->regions as $region_code => $countries) {
			$this->data['regions'][] = [
				'code'      => $region_code,
				'label'     => $this->language->get('text_region_' . $region_code),
				'countries' => $countries
			];
		}

		// Country display names come from the language file so Phil can
		// translate/relabel without touching this controller.
		$this->data['country_names'] = [];

		foreach ($this->regions as $countries) {
			foreach ($countries as $country_code) {
				$key = 'text_country_' . strtolower($country_code);
				$this->data['country_names'][$country_code] = $this->language->get($key) ?: $country_code;
			}
		}

		if (isset($this->request->post['klarna'])) {
			$this->data['klarna'] = $this->request->post['klarna'];
		} else {
			$this->data['klarna'] = $this->config->get('klarna');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones([]);

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses([]);

		$file = DIR_LOGS . 'klarna.log';

		if (file_exists($file)) {
			$this->data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
		} else {
			$this->data['log'] = '';
		}

		$this->data['clear'] = $this->url->link('payment/klarna/clear', 'token=' . $this->session->data['token'], 'SSL');

		$this->template = 'payment/klarna.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	/**
	 * Validates the form and, for each enabled region with at least one
	 * enabled country, performs a lightweight credentials check against
	 * the live Klarna Payments API. Failures are logged but do not block
	 * saving — they're surfaced as a warning so a typo doesn't lock the
	 * admin out of fixing it.
	 */
	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/klarna')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!empty($this->error)) {
			return false;
		}

		$this->load->model('payment/klarna');

		$log = new Log('klarna.log');
		$credential_errors = [];

		$post = $this->request->post['klarna'] ?? [];

		foreach (array_keys($this->regions) as $region_code) {
			$region = $post[$region_code] ?? null;

			if (empty($region['status'])) {
				continue;
			}

			$has_active_country = false;

			if (!empty($region['countries']) && is_array($region['countries'])) {
				foreach ($region['countries'] as $country) {
					if (!empty($country['status'])) {
						$has_active_country = true;
						break;
					}
				}
			}

			if (!$has_active_country) {
				continue;
			}

			if (empty($region['username']) || empty($region['password'])) {
				$credential_errors[] = sprintf($this->language->get('error_credentials_missing'), strtoupper($region_code));
				continue;
			}

			$result = $this->model_payment_klarna->testCredentials(
				$region_code,
				$region['username'],
				$region['password'],
				$region['server'] ?? 'playground'
			);

			if ($result !== true) {
				$credential_errors[] = sprintf($this->language->get('error_credentials_invalid'), strtoupper($region_code), $result);
				$log->write('Credential check failed for region ' . strtoupper($region_code) . ': ' . $result);
			}
		}

		if ($credential_errors) {
			$this->error['warning'] = implode('<br />', $credential_errors);
		}

		return empty($this->error);
	}

	public function clear() {
		$this->language->load('payment/klarna');

		$file = DIR_LOGS . 'klarna.log';

		$handle = fopen($file, 'w+');

		fclose($handle);

		$this->session->data['success'] = $this->language->get('text_success');

		$this->redirect($this->url->link('payment/klarna', 'token=' . $this->session->data['token'], 'SSL'));
	}
}
