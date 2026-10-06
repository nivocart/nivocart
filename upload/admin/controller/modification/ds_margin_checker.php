<?php
/**
 * Class ControllerModificationDsMarginChecker
 *
 * Admin controller for the DS Margin Checker tool.
 * Lists all dropship products with their current sale price vs supplier cost,
 * highlights below-threshold and negative-margin products, and allows
 * inline price updates from the product list.
 *
 * Requires: nc_product_dropship and nc_dropship_channel tables (created by a
 * Dropshipping connector such as CJ Dropshipping).
 * Does not create its own database tables.
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

		$threshold = (float)$this->model_modification_ds_margin_checker->getSetting('threshold', self::DEFAULT_THRESHOLD);

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['error_dependency'] = '';
		$this->data['threshold'] = $threshold;

		// Navigation URLs
		$this->data['list_url'] = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['settings_url'] = $this->url->link('modification/ds_margin_checker/settings', 'token=' . $this->session->data['token'], 'SSL');

		// Dashboard KPI stats
		$stats = $this->model_modification_ds_margin_checker->getDashboardStats($threshold);

		$this->data['total_products'] = $stats['total'];
		$this->data['below_threshold'] = $stats['below_threshold'];
		$this->data['negative_margin'] = $stats['negative_margin'];
		$this->data['avg_margin'] = $stats['avg_margin'];
		$this->data['worst_performers'] = $stats['worst_performers'];

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
		$this->data['column_cost_inc_vat'] = $this->language->get('column_cost_inc_vat');
		$this->data['column_margin_pct'] = $this->language->get('column_margin_pct');
		$this->data['column_status'] = $this->language->get('column_status');

		// Buttons
		$this->data['button_view_list'] = $this->language->get('button_view_list');
		$this->data['button_settings'] = $this->language->get('button_settings');

		// Status badge labels
		$this->data['status_good'] = $this->language->get('status_good');
		$this->data['status_low'] = $this->language->get('status_low');
		$this->data['status_negative'] = $this->language->get('status_negative');

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
	 * Filter options: all | below (below threshold) | negative (negative margin).
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

		$threshold = (float)$this->model_modification_ds_margin_checker->getSetting('threshold', self::DEFAULT_THRESHOLD);

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

		$products = $this->model_modification_ds_margin_checker->getProducts($data, $threshold);
		$total = $this->model_modification_ds_margin_checker->getTotalProducts($data, $threshold);

		$this->data['products'] = $products;
		$this->data['total'] = $total;
		$this->data['page'] = $page;
		$this->data['limit'] = $limit;
		$this->data['filter'] = $filter;
		$this->data['threshold'] = $threshold;
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

		// Filter button URLs
		$this->data['filter_all_url'] = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'] . '&filter=all', 'SSL');
		$this->data['filter_below_url'] = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'] . '&filter=below', 'SSL');
		$this->data['filter_negative_url'] = $this->url->link('modification/ds_margin_checker/list', 'token=' . $this->session->data['token'] . '&filter=negative', 'SSL');

		// Buttons
		$this->data['button_dashboard'] = $this->language->get('button_dashboard');
		$this->data['button_settings'] = $this->language->get('button_settings');
		$this->data['button_update_price'] = $this->language->get('button_update_price');

		// Filter labels
		$this->data['filter_all'] = $this->language->get('filter_all');
		$this->data['filter_below'] = $this->language->get('filter_below');
		$this->data['filter_negative'] = $this->language->get('filter_negative');

		// Table column headings
		$this->data['column_product'] = $this->language->get('column_product');
		$this->data['column_sku'] = $this->language->get('column_sku');
		$this->data['column_cost_ex_vat'] = $this->language->get('column_cost_ex_vat');
		$this->data['column_cost_inc_vat'] = $this->language->get('column_cost_inc_vat');
		$this->data['column_sale_price'] = $this->language->get('column_sale_price');
		$this->data['column_margin_pct'] = $this->language->get('column_margin_pct');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_channel'] = $this->language->get('column_channel');
		$this->data['column_action'] = $this->language->get('column_action');

		// Status badge labels
		$this->data['status_good'] = $this->language->get('status_good');
		$this->data['status_low'] = $this->language->get('status_low');
		$this->data['status_negative'] = $this->language->get('status_negative');

		// Text / error strings
		$this->data['text_no_products'] = $this->language->get('text_no_products');
		$this->data['error_price'] = $this->language->get('error_price');
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

		if ($product_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_product_id')]);
			return;
		}

		if ($price === null || $price === '' || !is_numeric($price) || (float)$price < 0) {
			$this->jsonResponse(['error' => $this->language->get('error_price')]);
			return;
		}

		$price = (float)$price;

		try {
			$this->model_modification_ds_margin_checker->updateProductPrice($product_id, $price);

			$product = $this->model_modification_ds_margin_checker->getProduct($product_id);

			$this->jsonResponse([
				'success'    => true,
				'message'    => $this->language->get('text_price_updated'),
				'new_price'  => number_format($price, 2, '.', ''),
				'margin_pct' => $product['margin_pct'] ?? 0
			]);
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
		}
	}

	// =================================================================
	// SETTINGS
	// =================================================================

	public function settings(): void {
		$this->language->load('modification/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('modification/ds_margin_checker');

		if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validatePermission()) {
			$threshold = (float)($this->request->post['threshold'] ?? self::DEFAULT_THRESHOLD);
			$threshold = max(0.0, min(100.0, $threshold));

			$this->model_modification_ds_margin_checker->setSetting('threshold', $threshold);

			$this->session->data['success'] = $this->language->get('text_settings_saved');

			$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		$this->data['threshold'] = (float)$this->model_modification_ds_margin_checker->getSetting('threshold', self::DEFAULT_THRESHOLD);

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['save_url'] = $this->url->link('modification/ds_margin_checker/settings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['dashboard_url'] = $this->url->link('modification/ds_margin_checker', 'token=' . $this->session->data['token'], 'SSL');

		// Language strings
		$this->data['tab_settings'] = $this->language->get('tab_settings');

		$this->data['entry_threshold'] = $this->language->get('entry_threshold');

		$this->data['help_threshold'] = $this->language->get('help_threshold');

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
	 * No tables to create — saves default threshold setting only.
	 */
	public function install(): void {
		$this->load->model('modification/ds_margin_checker');

		$this->model_modification_ds_margin_checker->setSetting('threshold', self::DEFAULT_THRESHOLD);
	}

	/**
	 * Called when the modification is uninstalled.
	 * Removes the threshold setting from nc_setting.
	 */
	public function uninstall(): void {
		$this->load->model('modification/ds_margin_checker');

		$this->model_modification_ds_margin_checker->deleteAllSettings();
	}

	// =================================================================
	// PRIVATE HELPERS
	// =================================================================

	/**
	 * Returns true if the required dropship dependency tables are missing.
	 * DS Margin Checker reads from nc_product_dropship and nc_dropship_channel,
	 * so those tables must exist before the dashboard can load.
	 */
	protected function dependenciesMissing(): bool {
		$required = [
			DB_PREFIX . 'product_dropship',
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

	private function jsonResponse(array $data): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}
