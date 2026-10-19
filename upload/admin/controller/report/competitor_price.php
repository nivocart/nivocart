<?php
/**
 * Class ControllerReportCompetitorPrice
 *
 * Competitor Price report: lists the products that a competitor sells for less,
 * matched by exact EAN, comparing VAT-inclusive prices (our Special price when one
 * is active). Prices are looked up on eBay through its Browse API and cached, so
 * opening the report never calls eBay; the Scan button refreshes the cache.
 *
 * @package NivoCart
 */
require_once DIR_SYSTEM . 'library/ebay_browse.php';

class ControllerReportCompetitorPrice extends Controller {
	/** Products looked up per AJAX request. */
	private const BATCH_SIZE = 10;

	/** Pause between two eBay requests, in microseconds. */
	private const REQUEST_PAUSE = 150000;

	private array $error = [];

	// =================================================================
	// REPORT PAGE
	// =================================================================

	public function index(): void {
		$this->language->load('report/competitor_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('report/competitor_price');

		$token = $this->session->data['token'];

		$this->data['installed'] = $this->model_report_competitor_price->isInstalled();

		// Settings (defaults when nothing has been saved yet)
		$marketplaces = EbayBrowse::getMarketplaces();

		$default_country = (string)$this->model_report_competitor_price->getSetting('country', 'GB');

		$country = strtoupper((string)($this->request->get['country'] ?? $default_country));

		if (!isset($marketplaces[$country])) {
			$country = 'GB';
		}

		$default_limit = max(1, min(1000, (int)$this->model_report_competitor_price->getSetting('results_limit', 50)));

		$limit = isset($this->request->get['limit']) ? max(1, min(1000, (int)$this->request->get['limit'])) : $default_limit;

		$include_postage = (bool)$this->model_report_competitor_price->getSetting('include_postage', '1');

		$recheck_days = max(1, min(90, (int)$this->model_report_competitor_price->getSetting('recheck_days', 7)));

		$this->data['rows'] = [];
		$this->data['total_cheaper'] = 0;
		$this->data['stats'] = ['enabled' => 0, 'no_ean' => 0, 'invalid_ean' => 0, 'checked' => 0];

		if ($this->data['installed']) {
			$report = $this->model_report_competitor_price->getReport($country, $include_postage, $limit);

			$base = (string)$this->config->get('config_currency');

			foreach ($report['rows'] as $row) {
				$this->data['rows'][] = [
					'name'          => $row['name'],
					'href'          => $this->url->link('catalog/product/update', 'token=' . $token . '&product_id=' . $row['product_id'], 'SSL'),
					'ean'           => $row['ean'],
					'has_special'   => $row['has_special'],
					'own_price'     => $this->currency->format($row['own_price'], $base),
					'competitor'    => $this->currency->format($row['competitor_total'], $base),
					'postage'       => ($row['competitor_postage'] === null) ? '' : $this->currency->format($row['competitor_postage'], $base),
					'seller'        => $row['seller'],
					'url'           => $row['url'],
					'gap'           => $this->currency->format($row['gap'], $base),
					'gap_percent'   => number_format($row['gap_percent'], 1),
					'floor'         => ($row['floor'] === null) ? '' : $this->currency->format($row['floor'], $base),
					'can_match'     => $row['can_match'],
					'date_checked'  => date($this->language->get('date_format_short'), strtotime($row['date_checked']))
				];
			}

			$this->data['total_cheaper'] = $report['total'];
			$this->data['stats'] = $this->model_report_competitor_price->getStats($country);
		}

		$this->data['country'] = $country;
		$this->data['limit'] = $limit;
		$this->data['recheck_days'] = $recheck_days;

		$this->data['countries'] = [];

		foreach (array_keys($marketplaces) as $code) {
			$this->data['countries'][$code] = $this->language->get('text_country_' . strtolower($code));
		}

		// Settings form values
		$this->data['setting_client_id'] = (string)$this->model_report_competitor_price->getSetting('client_id', '');
		$this->data['setting_has_secret'] = ((string)$this->model_report_competitor_price->getSetting('client_secret', '') !== '');
		$this->data['setting_country'] = $default_country;
		$this->data['setting_recheck_days'] = $recheck_days;
		$this->data['setting_results_limit'] = $default_limit;
		$this->data['setting_include_postage'] = $include_postage;
		$this->data['setting_ignore_sellers'] = (string)$this->model_report_competitor_price->getSetting('ignore_sellers', '');

		// Language
		foreach ([
			'heading_title', 'text_info', 'text_ean_required', 'text_install_message', 'text_special_note',
			'text_no_results', 'text_stats', 'text_stat_enabled', 'text_stat_no_ean', 'text_stat_invalid',
			'text_stat_checked', 'text_stat_cheaper', 'text_showing', 'text_yes', 'text_no', 'text_unknown',
			'tab_scan_report', 'tab_settings', 'text_settings', 'text_scan', 'text_scan_help', 'text_postage_added', 'text_never_scanned',
			'entry_country', 'entry_limit', 'entry_client_id', 'entry_client_secret', 'entry_recheck_days',
			'entry_results_limit', 'entry_include_postage', 'entry_ignore_sellers', 'entry_default_country',
			'help_client_id', 'help_client_secret', 'help_recheck_days', 'help_results_limit',
			'help_include_postage', 'help_ignore_sellers', 'text_secret_saved',
			'column_product', 'column_ean', 'column_price', 'column_competitor', 'column_seller',
			'column_gap', 'column_floor', 'column_match', 'column_checked',
			'button_close', 'button_show', 'button_save', 'button_scan', 'button_test', 'button_install',
			'button_clear_cache', 'text_confirm_clear'
		] as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		$this->data['text_showing'] = sprintf($this->language->get('text_showing'), count($this->data['rows']), $this->data['total_cheaper']);

		// URLs
		$this->data['close'] = $this->url->link('common/home', 'token=' . $token, 'SSL');
		$this->data['action_report'] = $this->url->link('report/competitor_price', 'token=' . $token, 'SSL');
		$this->data['action_settings'] = $this->url->link('report/competitor_price/saveSettings', 'token=' . $token, 'SSL');
		$this->data['action_install'] = $this->url->link('report/competitor_price/installDatabase', 'token=' . $token, 'SSL');
		$this->data['action_clear'] = $this->url->link('report/competitor_price/clearCache', 'token=' . $token . '&country=' . $country, 'SSL');

		// Script values (decoded: they go into JavaScript strings, not HTML attributes)
		$this->data['cp_js'] = [
			'url'   => [
				'scan' => str_replace('&amp;', '&', $this->url->link('report/competitor_price/scan', 'token=' . $token, 'SSL')),
				'test' => str_replace('&amp;', '&', $this->url->link('report/competitor_price/testConnection', 'token=' . $token, 'SSL')),
				'page'  => str_replace('&amp;', '&', $this->url->link('report/competitor_price', 'token=' . $token, 'SSL')),
				'clear' => str_replace('&amp;', '&', $this->url->link('report/competitor_price/clearCache', 'token=' . $token, 'SSL'))
			],
			'text'  => [
				'text_scanning'      => $this->language->get('text_scanning'),
				'text_scan_progress' => $this->language->get('text_scan_progress'),
				'text_scan_complete' => $this->language->get('text_scan_complete'),
				'text_scan_limit'    => $this->language->get('text_scan_limit'),
				'text_scan_nothing'  => $this->language->get('text_scan_nothing'),
				'text_scan_errors'   => $this->language->get('text_scan_errors'),
				'text_test_ok'       => $this->language->get('text_test_ok'),
				'text_confirm_clear' => $this->language->get('text_confirm_clear'),
				'button_clear_cache' => $this->language->get('button_clear_cache'),
				'error_ajax'         => $this->language->get('error_ajax')
			],
			'limit' => $limit
		];

		// Flash messages
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $token, 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('report/competitor_price', 'token=' . $token, 'SSL'),
			'separator' => ' :: '
		];

		$this->template = 'report/competitor_price.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	// =================================================================
	// ACTIONS
	// =================================================================

	public function installDatabase(): void {
		$this->language->load('report/competitor_price');

		$this->load->model('report/competitor_price');

		if ($this->validatePermission()) {
			$this->model_report_competitor_price->install();

			$this->session->data['success'] = $this->language->get('text_database_installed');
		} else {
			$this->session->data['error'] = $this->error['warning'];
		}

		$this->redirect($this->url->link('report/competitor_price', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function saveSettings(): void {
		$this->language->load('report/competitor_price');

		$this->load->model('report/competitor_price');

		$url = 'token=' . $this->session->data['token'];

		if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
			$this->redirect($this->url->link('report/competitor_price', $url, 'SSL'));
		}

		if (!$this->validatePermission()) {
			$this->session->data['error'] = $this->error['warning'];

			$this->redirect($this->url->link('report/competitor_price', $url, 'SSL'));
		}

		$country = strtoupper((string)($this->request->post['country'] ?? 'GB'));

		if (!isset(EbayBrowse::getMarketplaces()[$country])) {
			$country = 'GB';
		}

		$client_id = trim((string)($this->request->post['client_id'] ?? ''));
		$client_secret = trim((string)($this->request->post['client_secret'] ?? ''));

		// Changed credentials invalidate the stored access token.
		if ($client_id !== (string)$this->model_report_competitor_price->getSetting('client_id', '') || $client_secret !== '') {
			$this->model_report_competitor_price->setSetting('token', '');
			$this->model_report_competitor_price->setSetting('token_expires', '0');
		}

		$this->model_report_competitor_price->setSetting('client_id', $client_id);

		// An empty secret field keeps the stored one.
		if ($client_secret !== '') {
			$this->model_report_competitor_price->setSetting('client_secret', $client_secret);
		}

		$sellers = [];

		foreach (explode(',', (string)($this->request->post['ignore_sellers'] ?? '')) as $seller) {
			$seller = strtolower(trim($seller));

			if ($seller !== '' && preg_match('/^[a-z0-9._\-]{1,64}$/', $seller)) {
				$sellers[] = $seller;
			}
		}

		$this->model_report_competitor_price->setSetting('country', $country);
		$this->model_report_competitor_price->setSetting('recheck_days', (string)max(1, min(90, (int)($this->request->post['recheck_days'] ?? 7))));
		$this->model_report_competitor_price->setSetting('results_limit', (string)max(1, min(1000, (int)($this->request->post['results_limit'] ?? 50))));
		$this->model_report_competitor_price->setSetting('include_postage', empty($this->request->post['include_postage']) ? '0' : '1');
		$this->model_report_competitor_price->setSetting('ignore_sellers', implode(',', array_unique($sellers)));

		$this->session->data['success'] = $this->language->get('text_settings_saved');

		$this->redirect($this->url->link('report/competitor_price', $url . '&country=' . $country, 'SSL'));
	}

	public function clearCache(): void {
		$this->language->load('report/competitor_price');

		$this->load->model('report/competitor_price');

		$country = strtoupper((string)($this->request->get['country'] ?? 'GB'));

		if (!isset(EbayBrowse::getMarketplaces()[$country])) {
			$country = 'GB';
		}

		if ($this->validatePermission() && $this->model_report_competitor_price->isInstalled()) {
			$this->model_report_competitor_price->clearCache($country);

			$this->session->data['success'] = $this->language->get('text_cache_cleared');
		} elseif (isset($this->error['warning'])) {
			$this->session->data['error'] = $this->error['warning'];
		}

		$this->redirect($this->url->link('report/competitor_price', 'token=' . $this->session->data['token'] . '&country=' . $country, 'SSL'));
	}

	/**
	 * Check the eBay credentials by requesting an access token. Returns JSON.
	 */
	public function testConnection(): void {
		$this->language->load('report/competitor_price');

		$this->load->model('report/competitor_price');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->error['warning']]);

			return;
		}

		try {
			$ebay = new EbayBrowse(
				(string)$this->model_report_competitor_price->getSetting('client_id', ''),
				(string)$this->model_report_competitor_price->getSetting('client_secret', '')
			);

			$this->storeToken($ebay->requestToken());

			$this->jsonResponse(['success' => true]);
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
		}
	}

	/**
	 * Look up one batch of products on eBay and cache the results. Returns JSON.
	 *
	 * Request: country, after_id (last product_id already handled; 0 to start).
	 * The browser repeats the call until has_more is false or the results limit is reached.
	 */
	public function scan(): void {
		$this->language->load('report/competitor_price');

		$this->load->model('report/competitor_price');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->error['warning']]);

			return;
		}

		if (!$this->model_report_competitor_price->isInstalled()) {
			$this->jsonResponse(['error' => $this->language->get('error_not_installed')]);

			return;
		}

		$country = strtoupper((string)($this->request->get['country'] ?? 'GB'));

		if (!isset(EbayBrowse::getMarketplaces()[$country])) {
			$this->jsonResponse(['error' => $this->language->get('error_country')]);

			return;
		}

		$client_id = (string)$this->model_report_competitor_price->getSetting('client_id', '');
		$client_secret = (string)$this->model_report_competitor_price->getSetting('client_secret', '');

		if ($client_id === '' || $client_secret === '') {
			$this->jsonResponse(['error' => $this->language->get('error_credentials')]);

			return;
		}

		$after_id = max(0, (int)($this->request->get['after_id'] ?? 0));
		$recheck_days = max(1, min(90, (int)$this->model_report_competitor_price->getSetting('recheck_days', 7)));
		$include_postage = (bool)$this->model_report_competitor_price->getSetting('include_postage', '1');
		$ignore = array_filter(explode(',', (string)$this->model_report_competitor_price->getSetting('ignore_sellers', '')));

		$ebay = new EbayBrowse(
			$client_id,
			$client_secret,
			(string)$this->model_report_competitor_price->getSetting('token', ''),
			(int)$this->model_report_competitor_price->getSetting('token_expires', 0)
		);

		$batch = $this->model_report_competitor_price->getScanBatch($country, $after_id, self::BATCH_SIZE, $recheck_days);

		$found = 0;
		$errors = [];
		$last_id = $after_id;

		foreach ($batch as $product) {
			$product_id = (int)$product['product_id'];
			$ean = (string)$product['ean'];

			$last_id = $product_id;

			// A wrong check digit would only return noise: skip the lookup and remember why.
			if (!$this->model_report_competitor_price->isValidGtin($ean)) {
				$this->model_report_competitor_price->saveResult($product_id, $ean, $country, ['status' => 'invalid']);

				continue;
			}

			try {
				usleep(self::REQUEST_PAUSE);

				$result = $ebay->searchByGtin($ean, $country, $include_postage, $ignore);

				$result['status'] = $result['found'] ? 'found' : 'none';

				$this->model_report_competitor_price->saveResult($product_id, $ean, $country, $result);

				if ($result['found']) {
					$found++;
				}
			} catch (\Exception $e) {
				// Credentials problems affect every product: stop instead of repeating them.
				if (str_contains($e->getMessage(), 'access token') || str_contains($e->getMessage(), 'Client ID')) {
					$this->storeToken($ebay->getToken());

					$this->jsonResponse(['error' => $e->getMessage()]);

					return;
				}

				$this->log->write('Competitor price EAN ' . $ean . ': ' . $e->getMessage());

				$errors[] = $ean . ': ' . $e->getMessage();
			}
		}

		$this->storeToken($ebay->getToken());

		$report = $this->model_report_competitor_price->getReport($country, $include_postage, 0, $recheck_days);

		$this->jsonResponse([
			'success'       => true,
			'processed'     => count($batch),
			'found'         => $found,
			'errors'        => $errors,
			'last_id'       => $last_id,
			'has_more'      => (count($batch) === self::BATCH_SIZE),
			'total'         => ($after_id === 0) ? $this->model_report_competitor_price->countScanCandidates($country, $recheck_days) : 0,
			'cheaper_total' => $report['total']
		]);
	}

	// =================================================================
	// HELPERS
	// =================================================================

	/**
	 * Keep the access token (valid about two hours) so the next batch can reuse it.
	 *
	 * @param array{access_token: string, expires_at: int} $token
	 */
	private function storeToken(array $token): void {
		$this->model_report_competitor_price->setSetting('token', $token['access_token']);
		$this->model_report_competitor_price->setSetting('token_expires', (string)$token['expires_at']);
	}

	protected function validatePermission(): bool {
		if (!$this->user->hasPermission('modify', 'report/competitor_price')) {
			$this->error['warning'] = $this->language->get('error_permission');

			return false;
		}

		return true;
	}

	private function jsonResponse(array $data): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}
