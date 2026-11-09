<?php
/**
 * Class ControllerModificationDsMarginChecker
 *
 * Admin controller for the DS Margin Checker tool.
 * Lists all dropship products with their sale price vs total cost (supplier cost plus a hand-entered
 * shipping cost, converted to the store default currency), highlights products priced below their target
 * price (margin up to a Breakpoint, a second margin above it, and a minimum profit) and negative-margin
 * products, and allows inline price and shipping cost updates from the product list.
 *
 * Requires: nc_product_dropship, nc_dropship_variant and nc_dropship_channel tables (created by a
 * Dropshipping connector such as CJ Dropshipping).
 * Creates one small table of its own, nc_ds_margin_shipping (shipping cost per product).
 *
 * Location: admin/controller/modification/ds_margin_checker.php
 *
 * @package NivoCart
 */
class ControllerModificationDsMarginChecker extends Controller {
	private array $error = [];
	private string $name = 'ds_margin_checker';

	// Default margin threshold percentage
	private const DEFAULT_THRESHOLD = 20.0;

	// Default Breakpoint (Total Cost, store default currency). Only used when a second margin is chosen
	private const DEFAULT_BREAKPOINT = 40;

	// Total Costs shown in the Sample Prices table of the Settings page
	private const SAMPLE_COSTS = [5, 20, 60, 100, 300];

	// Sale price rounding rules (setting value => language key)
	private const ROUNDING_RULES = [
		'none' => 'text_rounding_none',
		'five' => 'text_rounding_five',
		'nine' => 'text_rounding_nine'
	];

	// Allowed values for the product list filter
	private const FILTERS = ['all', 'below', 'negative'];

	// =================================================================
	// INDEX — dependency check gate
	// =================================================================

	public function index(): void {
		if ($this->dependenciesMissing()) {
			$this->language->load('modification/' . $this->name);

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');
			$this->data['error_dependency'] = $this->language->get('error_dependency');

			$this->data['close'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL');
			$this->data['button_close'] = $this->language->get('button_close');

			$this->data['breadcrumbs'] = $this->getBreadcrumbs();

			$this->template = 'modification/ds_margin_checker_dashboard.tpl';
			$this->children = ['common/header', 'common/footer'];

			$this->response->setOutput($this->render());
			return;
		}

		$this->dashboard();
	}

	// =================================================================
	// DASHBOARD
	// =================================================================

	public function dashboard(): void {
		// Direct URL access: show the dependency warning instead of querying missing tables
		if ($this->dependenciesMissing()) {
			$this->index();
			return;
		}

		$this->language->load('modification/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('modification/ds_margin_checker');

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['error_dependency'] = '';

		// Navigation URLs
		$this->data['list_url'] = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['settings_url'] = $this->url->link('modification/ds_margin_checker/settings', 'token=' . $this->session->data['token'], 'SSL');

		// Dashboard KPI stats
		$stats = $this->model_modification_ds_margin_checker->getDashboardStats();

		$this->data['total_products'] = $stats['total'];
		$this->data['below_threshold'] = $stats['below_threshold'];
		$this->data['negative_margin'] = $stats['negative_margin'];
		$this->data['avg_margin'] = $stats['avg_margin'];
		$this->data['worst_performers'] = $stats['worst_performers'];
		$this->data['no_rate'] = $stats['no_rate'];
		$this->data['text_no_rate_warning'] = sprintf($this->language->get('text_no_rate_warning'), $stats['no_rate']);

		$default_currency = strtoupper((string)$this->config->get('config_currency'));

		// Flash messages
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		if (isset($this->session->data['error'])) {
			$this->data['error'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$this->data['error'] = '';
		}

		// Panel headings
		$this->data['tab_dashboard'] = $this->language->get('tab_dashboard');
		$this->data['tab_about'] = $this->language->get('tab_about');

		// KPI tile labels
		$this->data['text_total_products'] = $this->language->get('text_total_products');
		$this->data['text_below_threshold'] = $this->language->get('text_below_threshold');
		$this->data['text_negative_margin'] = $this->language->get('text_negative_margin');
		$this->data['text_avg_margin'] = $this->language->get('text_avg_margin');
		$this->data['text_worst_performers'] = $this->language->get('text_worst_performers');
		$this->data['text_no_dropship'] = $this->language->get('text_no_dropship');

		// Table column headings
		$this->data['column_product'] = $this->language->get('column_product');
		$this->data['column_sku'] = $this->language->get('column_sku');
		$this->data['column_sale_price'] = $this->language->get('column_sale_price');
		$this->data['column_total_cost_default'] = sprintf($this->language->get('column_total_cost_default'), $default_currency);
		$this->data['column_margin_pct'] = $this->language->get('column_margin_pct');
		$this->data['column_status'] = $this->language->get('column_status');

		// Buttons
		$this->data['button_view_list'] = $this->language->get('button_view_list');
		$this->data['button_settings'] = $this->language->get('button_settings');

		// Status badge labels
		$this->data['status_good'] = $this->language->get('status_good');
		$this->data['status_low'] = $this->language->get('status_low');
		$this->data['status_negative'] = $this->language->get('status_negative');
		$this->data['status_no_rate'] = $this->language->get('status_no_rate');

		// About panel — labels
		$this->data['text_version'] = $this->language->get('text_version');
		$this->data['text_author'] = $this->language->get('text_author');
		$this->data['text_support'] = $this->language->get('text_support');
		$this->data['text_license'] = $this->language->get('text_license');

		// About panel — values
		$this->data['mc_version'] = $this->language->get('mc_version');
		$this->data['mc_author'] = $this->language->get('mc_author');
		$this->data['mc_support'] = $this->language->get('mc_support');
		$this->data['mc_license'] = $this->language->get('mc_license');

		// Close button
		$this->data['close'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['button_close'] = $this->language->get('button_close');

		$this->data['breadcrumbs'] = $this->getBreadcrumbs();

		$this->template = 'modification/ds_margin_checker_dashboard.tpl';
		$this->children = ['common/header', 'common/footer'];

		$this->response->setOutput($this->render());
	}

	// =================================================================
	// PRODUCT LIST
	// =================================================================

	/**
	 * Full filterable, paginated product list with inline price editing.
	 * Filter options: all | below (below target price) | negative (negative margin).
	 */
	public function list(): void {
		// Direct URL access: show the dependency warning instead of querying missing tables
		if ($this->dependenciesMissing()) {
			$this->index();
			return;
		}

		$this->language->load('modification/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('modification/ds_margin_checker');

		// Pagination and filter params from GET (filter is whitelisted: it is echoed into URLs)
		$filter = $this->request->get['filter'] ?? 'all';

		if (!in_array($filter, self::FILTERS, true)) {
			$filter = 'all';
		}

		$page = max(1, (int)($this->request->get['page'] ?? 1));
		$limit = 20;
		$start = ($page - 1) * $limit;

		$data = [
			'filter' => $filter,
			'start'  => $start,
			'limit'  => $limit
		];

		$products = $this->model_modification_ds_margin_checker->getProducts($data);
		$total = $this->model_modification_ds_margin_checker->getTotalProducts($data);

		$this->data['products'] = $products;
		$this->data['total'] = $total;
		$this->data['page'] = $page;
		$this->data['limit'] = $limit;
		$this->data['filter'] = $filter;
		$this->data['heading_title'] = $this->language->get('heading_title');

		// Pagination
		$this->data['pagination'] = '';

		if ($total > $limit) {
			$this->load->library('pagination');

			$pagination = new Pagination();
			$pagination->total = $total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'] . '&filter=' . $filter . '&page={page}', 'SSL');

			$this->data['pagination'] = $pagination->render();
		}

		// Action URLs
		$this->data['dashboard_url'] = $this->url->link('modification/ds_margin_checker', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['settings_url'] = $this->url->link('modification/ds_margin_checker/settings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['update_price_url'] = 'index.php?route=modification/ds_margin_checker/updatePrice&token=' . $this->session->data['token'];
		$this->data['sync_shipping_url'] = 'index.php?route=modification/ds_margin_checker/syncShipping&token=' . $this->session->data['token'];
		$this->data['set_prices_url'] = 'index.php?route=modification/ds_margin_checker/setAllPrices&token=' . $this->session->data['token'];
		$this->data['preview_prices_url'] = 'index.php?route=modification/ds_margin_checker/previewPrices&token=' . $this->session->data['token'];

		// The sync button needs the CJ Dropshipping connector and at least one enabled CJ channel
		$this->data['cj_channels'] = is_file(DIR_APPLICATION . 'model/modification/cjdropshipping.php') ? $this->model_modification_ds_margin_checker->getCjChannelIds() : [];

		// Filter button URLs
		$this->data['filter_all_url'] = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'] . '&filter=all', 'SSL');
		$this->data['filter_below_url'] = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'] . '&filter=below', 'SSL');
		$this->data['filter_negative_url'] = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'] . '&filter=negative', 'SSL');

		// Buttons
		$this->data['button_dashboard'] = $this->language->get('button_dashboard');
		$this->data['button_settings'] = $this->language->get('button_settings');
		$this->data['button_update_price'] = $this->language->get('button_update_price');
		$this->data['button_sync_shipping'] = $this->language->get('button_sync_shipping');
		$this->data['button_refresh'] = $this->language->get('button_refresh');
		$this->data['button_set_prices'] = $this->language->get('button_set_prices');

		$this->data['text_set_prices_done'] = $this->language->get('text_set_prices_done');
		$this->data['text_set_prices_specials'] = $this->language->get('text_set_prices_specials');
		$this->data['text_preview_counts'] = $this->language->get('text_preview_counts');
		$this->data['text_preview_drops'] = $this->language->get('text_preview_drops');
		$this->data['text_preview_rises'] = $this->language->get('text_preview_rises');
		$this->data['text_preview_specials'] = $this->language->get('text_preview_specials');
		$this->data['text_preview_warning'] = $this->language->get('text_preview_warning');
		$this->data['text_preview_nothing'] = $this->language->get('text_preview_nothing');
		$this->data['text_sync_running'] = $this->language->get('text_sync_running');
		$this->data['text_sync_done'] = $this->language->get('text_sync_done');
		$this->data['text_sync_progress'] = $this->language->get('text_sync_progress');
		$this->data['text_sync_retry'] = $this->language->get('text_sync_retry');
		$this->data['text_sync_no_freight'] = $this->language->get('text_sync_no_freight');
		$this->data['text_sync_ranged'] = $this->language->get('text_sync_ranged');

		// Filter labels
		$this->data['filter_all'] = $this->language->get('filter_all');
		$this->data['filter_below'] = $this->language->get('filter_below');
		$this->data['filter_negative'] = $this->language->get('filter_negative');

		// Table column headings
		$this->data['column_product'] = $this->language->get('column_product');
		$this->data['column_sku'] = $this->language->get('column_sku');
		$default_currency = strtoupper((string)$this->config->get('config_currency'));

		$this->data['default_currency'] = $default_currency;
		$this->data['column_cost_original'] = $this->language->get('column_cost_original');
		$this->data['column_shipping_cost'] = $this->language->get('column_shipping_cost');
		$this->data['column_total_cost'] = $this->language->get('column_total_cost');
		$this->data['column_total_cost_default'] = sprintf($this->language->get('column_total_cost_default'), $default_currency);
		$this->data['text_rate_title'] = $this->language->get('text_rate_title');
		$this->data['column_sale_price'] = $this->language->get('column_sale_price');
		$this->data['column_margin_pct'] = $this->language->get('column_margin_pct');
		$this->data['text_target_price'] = $this->language->get('text_target_price');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_channel'] = $this->language->get('column_channel');
		$this->data['column_action'] = $this->language->get('column_action');

		// Status badge labels
		$this->data['status_good'] = $this->language->get('status_good');
		$this->data['status_low'] = $this->language->get('status_low');
		$this->data['status_negative'] = $this->language->get('status_negative');
		$this->data['status_no_rate'] = $this->language->get('status_no_rate');

		// Text / error strings
		$this->data['text_no_products'] = $this->language->get('text_no_products');
		$this->data['error_price'] = $this->language->get('error_price');
		$this->data['error_shipping'] = $this->language->get('error_shipping');
		$this->data['error_server'] = $this->language->get('error_server');

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([
			[
				'text' => $this->language->get('tab_products'),
				'href' => $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'], 'SSL')
			]
		]);

		$this->template = 'modification/ds_margin_checker_list.tpl';
		$this->children = ['common/header', 'common/footer'];

		$this->response->setOutput($this->render());
	}

	// =================================================================
	// AJAX — UPDATE PRICE
	// =================================================================

	/**
	 * Update a product's sale price.
	 * Expects POST: product_id (int), price (float >= 0).
	 * Needs modify permission on this tool AND on catalog/product.
	 * Returns JSON with success flag, updated margin_pct, and formatted new_price.
	 */
	public function updatePrice(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/ds_margin_checker');

		if ($this->dependenciesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_dependency')]);
			return;
		}

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->jsonResponse(['error' => $this->language->get('error_permission_product')]);
			return;
		}

		if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
			$this->jsonResponse(['error' => $this->language->get('error_method')]);
			return;
		}

		$product_id = (int)($this->request->post['product_id'] ?? 0);
		$price = $this->request->post['price'] ?? null;
		$shipping = $this->request->post['shipping_cost'] ?? '0';

		if ($product_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_product_id')]);
			return;
		}

		if ($price === null || $price === '' || !is_numeric($price) || (float)$price < 0) {
			$this->jsonResponse(['error' => $this->language->get('error_price')]);
			return;
		}

		if ($shipping === '' || !is_numeric($shipping) || (float)$shipping < 0) {
			$this->jsonResponse(['error' => $this->language->get('error_shipping')]);
			return;
		}

		$price = (float)$price;
		$shipping = (float)$shipping;

		try {
			$this->model_modification_ds_margin_checker->updateProductPrice($product_id, $price);
			$this->model_modification_ds_margin_checker->updateProductShipping($product_id, $shipping);

			$product = $this->model_modification_ds_margin_checker->getProduct($product_id);

			$this->jsonResponse([
				'success'    => true,
				'message'    => $this->language->get('text_price_updated'),
				'new_price'  => number_format($price, 2, '.', ''),
				'new_shipping' => number_format($shipping, 2, '.', ''),
				'total_cost_original' => number_format((float)($product['total_cost_original'] ?? 0), 2, '.', ''),
				'total_cost' => isset($product['total_cost']) ? number_format((float)$product['total_cost'], 2, '.', '') : null,
				'margin_pct' => $product['margin_pct'] ?? null,
				'target_price' => isset($product['target_price']) ? number_format((float)$product['target_price'], 2, '.', '') : null,
				'below_target' => (int)($product['below_target'] ?? 0)
			]);
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
		}
	}

	// =================================================================
	// AJAX — SYNC SHIPPING COSTS FROM CJ
	// =================================================================

	/**
	 * Sync one page of shipping costs from the CJ "Added Products" list.
	 * Expects POST: channel_id (int), page (int, 1-based).
	 * Needs modify permission on this tool. Returns the result of ModelModificationCJDropshipping::syncShippingCosts()
	 * as JSON, or an error.
	 */
	public function syncShipping(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/ds_margin_checker');

		if ($this->dependenciesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_dependency')]);
			return;
		}

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
			$this->jsonResponse(['error' => $this->language->get('error_method')]);
			return;
		}

		$channel_id = (int)($this->request->post['channel_id'] ?? 0);

		if (!is_file(DIR_APPLICATION . 'model/modification/cjdropshipping.php') || !in_array($channel_id, $this->model_modification_ds_margin_checker->getCjChannelIds(), true)) {
			$this->jsonResponse(['error' => $this->language->get('error_sync_channel')]);
			return;
		}

		$this->load->model('modification/cjdropshipping');

		try {
			$result = $this->model_modification_cjdropshipping->syncShippingCosts($channel_id, max(1, (int)($this->request->post['page'] ?? 1)));
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
			return;
		}

		$this->jsonResponse($result);
	}

	// =================================================================
	// AJAX — SET ALL PRICES AUTOMATICALLY
	// =================================================================

	/**
	 * Preview of "Set All Prices Automatically": nothing is written. Expects POST.
	 * Same permissions as setAllPrices(). Returns JSON: up, down, same, skipped, specials, drops, rises, rules_text.
	 */
	public function previewPrices(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/ds_margin_checker');

		$error = $this->getSetPricesError();

		if ($error) {
			$this->jsonResponse(['error' => $error]);
			return;
		}

		try {
			$rounding = $this->getRoundingRule();

			$result = $this->model_modification_ds_margin_checker->previewAllPrices($rounding);

			$result['rules_text'] = $this->getRulesText($this->model_modification_ds_margin_checker->getPricingRules(), $rounding);

			$this->jsonResponse($result);
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
		}
	}

	/**
	 * Set every dropship product's sale price from its Total Cost (default currency), the pricing rules
	 * (Settings) and the Sale Price Rounding Rule. Expects POST. Needs modify permission on this tool AND on catalog/product.
	 * Returns JSON: updated, unchanged, skipped, specials (products whose Special is at or above the new price or below their cost).
	 */
	public function setAllPrices(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/ds_margin_checker');

		$error = $this->getSetPricesError();

		if ($error) {
			$this->jsonResponse(['error' => $error]);
			return;
		}

		try {
			$this->jsonResponse($this->model_modification_ds_margin_checker->setAllPrices($this->getRoundingRule()));
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
		}
	}

	/**
	 * Sample prices for the Settings page, from the options chosen in the form (saved or not).
	 * Expects POST: threshold, margin_high, breakpoint, min_profit, price_rounding.
	 * Read only. Returns JSON: rows (cost, price, profit, margin: formatted text).
	 */
	public function samplePrices(): void {
		$this->load->model('modification/ds_margin_checker');

		$model = $this->model_modification_ds_margin_checker;

		$rounding = (string)($this->request->post['price_rounding'] ?? 'none');

		if (!isset(self::ROUNDING_RULES[$rounding])) {
			$rounding = 'none';
		}

		$rules = $model->getPricingRules([
			'margin'      => $this->request->post['threshold'] ?? null,
			'margin_high' => $this->request->post['margin_high'] ?? '',
			'breakpoint'  => $this->request->post['breakpoint'] ?? 0,
			'floor'       => $this->request->post['min_profit'] ?? 0
		]);

		$rows = [];

		foreach ($model->getSamplePrices(self::SAMPLE_COSTS, $rounding, $rules) as $row) {
			$rows[] = [
				'cost'   => $model->formatAmount($row['cost']),
				'price'  => $model->formatAmount($row['price']),
				'profit' => $model->formatAmount($row['profit']),
				'margin' => number_format($row['margin'], 1) . '%'
			];
		}

		$this->jsonResponse(['rows' => $rows]);
	}

	// =================================================================
	// SETTINGS
	// =================================================================

	public function settings(): void {
		$this->language->load('modification/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('modification/ds_margin_checker');

		$model = $this->model_modification_ds_margin_checker;

		$margin_high_options = $this->getMarginHighOptions();
		$breakpoint_options = $this->getBreakpointOptions();
		$min_profit_options = $this->getMinProfitOptions();

		$saved_margin_high = (string)$model->getSetting('margin_high', '');
		$saved_breakpoint = (string)$model->getSetting('breakpoint', self::DEFAULT_BREAKPOINT);
		$saved_min_profit = (string)$model->getSetting('min_profit', '0');

		if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validatePermission()) {
			$threshold = (float)($this->request->post['threshold'] ?? self::DEFAULT_THRESHOLD);
			$threshold = max(0.0, min(99.0, $threshold));

			$model->setSetting('threshold', $threshold);

			// The three dropdown values must be one of the offered options (or the value already saved)
			$model->setSetting('margin_high', $this->pickOption($this->request->post['margin_high'] ?? '', $margin_high_options, $saved_margin_high));
			$model->setSetting('breakpoint', $this->pickOption($this->request->post['breakpoint'] ?? '', $breakpoint_options, $saved_breakpoint));
			$model->setSetting('min_profit', $this->pickOption($this->request->post['min_profit'] ?? '', $min_profit_options, $saved_min_profit));

			$rounding = (string)($this->request->post['price_rounding'] ?? 'none');

			$model->setSetting('price_rounding', isset(self::ROUNDING_RULES[$rounding]) ? $rounding : 'none');

			$this->session->data['success'] = $this->language->get('text_settings_saved');

			$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		$this->data['threshold'] = min(99.0, (float)$model->getSetting('threshold', self::DEFAULT_THRESHOLD));
		$this->data['price_rounding'] = $this->getRoundingRule();

		$this->data['margin_high_options'] = $this->markSelected($margin_high_options, $saved_margin_high);
		$this->data['breakpoint_options'] = $this->markSelected($breakpoint_options, $saved_breakpoint);
		$this->data['min_profit_options'] = $this->markSelected($min_profit_options, $saved_min_profit);

		$this->data['rounding_options'] = [];

		foreach (self::ROUNDING_RULES as $value => $key) {
			$this->data['rounding_options'][$value] = $this->language->get($key);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['save_url'] = $this->url->link('modification/ds_margin_checker/settings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['sample_url'] = 'index.php?route=modification/ds_margin_checker/samplePrices&token=' . $this->session->data['token'];
		$this->data['dashboard_url'] = $this->url->link('modification/ds_margin_checker', 'token=' . $this->session->data['token'], 'SSL');

		// Language strings
		$this->data['tab_settings'] = $this->language->get('tab_settings');

		$this->data['entry_threshold'] = $this->language->get('entry_threshold');
		$this->data['help_threshold'] = $this->language->get('help_threshold');

		$this->data['entry_margin_high'] = $this->language->get('entry_margin_high');
		$this->data['help_margin_high'] = $this->language->get('help_margin_high');

		$this->data['entry_breakpoint'] = $this->language->get('entry_breakpoint');
		$this->data['help_breakpoint'] = $this->language->get('help_breakpoint');

		$this->data['entry_min_profit'] = $this->language->get('entry_min_profit');
		$this->data['help_min_profit'] = $this->language->get('help_min_profit');

		$this->data['entry_rounding'] = $this->language->get('entry_rounding');
		$this->data['help_rounding'] = $this->language->get('help_rounding');

		$this->data['entry_samples'] = $this->language->get('entry_samples');
		$this->data['help_samples'] = $this->language->get('help_samples');

		$this->data['column_total_cost'] = $this->language->get('column_total_cost');
		$this->data['column_sale_price'] = $this->language->get('column_sale_price');
		$this->data['column_profit'] = $this->language->get('column_profit');
		$this->data['column_margin_pct'] = $this->language->get('column_margin_pct');
		$this->data['error_server'] = $this->language->get('error_server');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_dashboard'] = $this->language->get('button_dashboard');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([
			[
				'text' => $this->language->get('tab_settings'),
				'href' => $this->url->link('modification/ds_margin_checker/settings', 'token=' . $this->session->data['token'], 'SSL')
			]
		]);

		$this->template = 'modification/ds_margin_checker_settings.tpl';
		$this->children = ['common/header', 'common/footer'];

		$this->response->setOutput($this->render());
	}

	// =================================================================
	// INSTALL / UNINSTALL
	// =================================================================

	/**
	 * Called when the modification is installed via the admin Modifications page.
	 * Saves the default settings (no tiers, no minimum profit) and creates the shipping cost table.
	 */
	public function install(): void {
		$this->load->model('modification/ds_margin_checker');

		$this->model_modification_ds_margin_checker->setSetting('threshold', self::DEFAULT_THRESHOLD);
		$this->model_modification_ds_margin_checker->setSetting('margin_high', '');
		$this->model_modification_ds_margin_checker->setSetting('breakpoint', self::DEFAULT_BREAKPOINT);
		$this->model_modification_ds_margin_checker->setSetting('min_profit', '0');
		$this->model_modification_ds_margin_checker->setSetting('price_rounding', 'none');
		$this->model_modification_ds_margin_checker->ensureShippingTable();
	}

	/**
	 * Called when the modification is uninstalled.
	 * Removes the Margin Checker settings and its shipping cost table.
	 */
	public function uninstall(): void {
		$this->load->model('modification/ds_margin_checker');

		$this->model_modification_ds_margin_checker->deleteAllSettings();
		$this->model_modification_ds_margin_checker->dropShippingTable();
	}

	// =================================================================
	// PRIVATE HELPERS
	// =================================================================

	/**
	 * Returns true if the required dropship dependency tables are missing.
	 * DS Margin Checker reads from nc_product_dropship, nc_dropship_variant and
	 * nc_dropship_channel, so those tables must exist before the dashboard can load.
	 */
	protected function dependenciesMissing(): bool {
		$required = [
			DB_PREFIX . 'product_dropship',
			DB_PREFIX . 'dropship_variant',
			DB_PREFIX . 'dropship_channel'
		];

		foreach ($required as $table) {
			$result = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape($table) . "'");

			if (!$result->num_rows) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Breadcrumbs: Home > Modifications > DS Margin Checker [> extra crumbs].
	 * Needs the modification language file to be loaded.
	 */
	protected function getBreadcrumbs(array $extra = []): array {
		$breadcrumbs = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			],
			[
				'text'      => $this->language->get('text_modification'),
				'href'      => $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			],
			[
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			]
		];

		foreach ($extra as $crumb) {
			$crumb['separator'] = ' :: ';

			$breadcrumbs[] = $crumb;
		}

		return $breadcrumbs;
	}

	protected function validatePermission(): bool {
		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
			$this->error['warning'] = $this->language->get('error_permission');

			return false;
		}

		return true;
	}

	/**
	 * The saved Sale Price Rounding Rule, falling back to 'none' for an unknown value.
	 */
	private function getRoundingRule(): string {
		$rule = (string)$this->model_modification_ds_margin_checker->getSetting('price_rounding', 'none');

		return isset(self::ROUNDING_RULES[$rule]) ? $rule : 'none';
	}

	/**
	 * Why "Set All Prices Automatically" (and its preview) cannot run now, or '' when it can.
	 */
	private function getSetPricesError(): string {
		if ($this->dependenciesMissing()) {
			return $this->language->get('error_dependency');
		}

		if (!$this->validatePermission()) {
			return $this->language->get('error_permission');
		}

		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			return $this->language->get('error_permission_product');
		}

		if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
			return $this->language->get('error_method');
		}

		return '';
	}

	/**
	 * A sentence describing the pricing rules, for the confirmation dialog.
	 */
	private function getRulesText(array $rules, string $rounding): string {
		$model = $this->model_modification_ds_margin_checker;

		if ($rules['tiered']) {
			$text = sprintf($this->language->get('text_rules_tiered'), $this->formatPercent($rules['margin_low']), $model->formatAmount($rules['breakpoint']), $this->formatPercent($rules['margin_high']));
		} else {
			$text = sprintf($this->language->get('text_rules_flat'), $this->formatPercent($rules['margin_low']));
		}

		if ($rules['floor'] > 0) {
			$text .= sprintf($this->language->get('text_rules_floor'), $model->formatAmount($rules['floor']));
		}

		return $text . sprintf($this->language->get('text_rules_rounding'), $this->language->get(self::ROUNDING_RULES[$rounding]));
	}

	private function formatPercent(float $value): string {
		return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%';
	}

	/**
	 * Options of "Margin above the Breakpoint": an empty value (same margin, no tiers) and 1% to 50%.
	 *
	 * @return array  List of ['value' => string, 'label' => string]
	 */
	private function getMarginHighOptions(): array {
		$options = [['value' => '', 'label' => $this->language->get('text_margin_same')]];

		for ($i = 1; $i <= 50; $i++) {
			$options[] = ['value' => (string)$i, 'label' => $i . '%'];
		}

		return $options;
	}

	/**
	 * Options of the Breakpoint (Total Cost): 10 to 100 by 5, 110 to 200 by 10, 225 to 500 by 25.
	 */
	private function getBreakpointOptions(): array {
		$values = array_merge(range(10, 100, 5), range(110, 200, 10), range(225, 500, 25));

		$options = [];

		foreach ($values as $value) {
			$options[] = ['value' => (string)$value, 'label' => $this->model_modification_ds_margin_checker->formatAmount((float)$value)];
		}

		return $options;
	}

	/**
	 * Options of the Minimum Profit: none, 0.25 to 5 by 0.25, 5.50 to 10 by 0.50.
	 */
	private function getMinProfitOptions(): array {
		$options = [['value' => '0', 'label' => $this->language->get('text_no_minimum')]];

		$values = [];

		for ($i = 1; $i <= 20; $i++) {
			$values[] = $i * 0.25;
		}

		for ($i = 11; $i <= 20; $i++) {
			$values[] = $i * 0.5;
		}

		foreach ($values as $value) {
			$options[] = ['value' => number_format($value, 2, '.', ''), 'label' => $this->model_modification_ds_margin_checker->formatAmount($value)];
		}

		return $options;
	}

	/**
	 * The posted value when it is one of the offered options, otherwise the value that is already saved.
	 * Numbers are compared as numbers (2.5 is 2.50).
	 */
	private function pickOption(mixed $posted, array $options, string $current): string {
		$posted = trim((string)$posted);

		foreach ($options as $option) {
			if ($this->sameOption($posted, $option['value'])) {
				return $option['value'];
			}
		}

		return $current;
	}

	/**
	 * Mark the saved value as selected. A saved value that is not in the list (changed by hand, or the list
	 * changed since) is added as an extra option so it is never lost.
	 */
	private function markSelected(array $options, string $selected): array {
		$found = false;

		foreach ($options as &$option) {
			$option['selected'] = $this->sameOption($selected, $option['value']);

			if ($option['selected']) {
				$found = true;
			}
		}

		unset($option);

		if (!$found && is_numeric($selected)) {
			$options[] = ['value' => $selected, 'label' => rtrim(rtrim(number_format((float)$selected, 4, '.', ''), '0'), '.'), 'selected' => true];

			usort($options, fn($a, $b) => ($a['value'] === '' ? -1 : (float)$a['value']) <=> ($b['value'] === '' ? -1 : (float)$b['value']));
		}

		return $options;
	}

	private function sameOption(string $a, string $b): bool {
		if ($a === '' || $b === '') {
			return $a === $b;
		}

		return is_numeric($a) && is_numeric($b) && abs((float)$a - (float)$b) < 0.00001;
	}

	private function jsonResponse(array $data): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}
