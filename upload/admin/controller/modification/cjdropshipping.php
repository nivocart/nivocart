<?php
/**
 * Class ControllerModificationCJDropshipping
 *
 * Admin controller for the CJDropshipping integration.
 * Handles: install/uninstall, channel management, manual product
 * import, stock/price sync triggers, order dispatch, tracking sync,
 * and webhook registration.
 *
 * Shares the dropship tables with other dropship connectors.
 * Install uses CREATE TABLE IF NOT EXISTS throughout so several
 * connectors can coexist safely.
 *
 * Intended location: admin/controller/modification/cjdropshipping.php
 *
 * @package NivoCart
 */
class ControllerModificationCJDropshipping extends Controller {
	private array $error = [];
	private string $name = 'cjdropshipping';

	// =================================================================
	// INDEX — DB check gate
	// =================================================================

	public function index(): void {
		if ($this->tablesMissing()) {
			$this->language->load('modification/' . $this->name);

			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['install_cjdropshipping'] = $this->url->link('modification/cjdropshipping/installDatabase', 'token=' . $this->session->data['token'], 'SSL');

			$this->data['text_install_message'] = $this->language->get('text_install_message');
			$this->data['text_upgrade'] = $this->language->get('text_upgrade');

			$this->data['close'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL');
			$this->data['button_close'] = $this->language->get('button_close');

			$this->data['breadcrumbs'] = $this->getBreadcrumbs();

			$this->template = 'modification/cjdropshipping_notification.tpl';
			$this->children = ['common/header', 'common/footer'];

			$this->response->setOutput($this->render());
			return;
		}

		$this->dashboard();
	}

	/**
	 * Returns true if any of the shared dropship DB tables are missing (install needed).
	 * Checks directly via SHOW TABLES — no child controller required.
	 * Protected so it cannot be reached as a route.
	 */
	protected function tablesMissing(): bool {
		$tables = [
			DB_PREFIX . 'dropship_channel',
			DB_PREFIX . 'dropship_order',
			DB_PREFIX . 'dropship_variant',
			DB_PREFIX . 'product_dropship',
			DB_PREFIX . 'product_variant'
		];

		foreach ($tables as $table) {
			$result = $this->db->query("SHOW TABLES LIKE '" . $this->db->escape($table) . "'");

			if (!$result->num_rows) {
				return true; // table missing — install needed
			}
		}

		return false; // all tables present
	}

	/**
	 * Create the database tables from the dashboard notice (direct "Install Database" button).
	 * Redirects back to the dashboard.
	 */
	public function installDatabase(): void {
		$this->language->load('modification/' . $this->name);

		if ($this->validatePermission()) {
			$this->install();

			$this->session->data['success'] = $this->language->get('text_database_installed');
		} else {
			$this->session->data['error'] = $this->error['warning'];
		}

		$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
	}

	// =================================================================
	// DASHBOARD
	// =================================================================

	public function dashboard(): void {
		// Direct URL access: show the install notice instead of querying missing tables
		if ($this->tablesMissing()) {
			$this->index();
			return;
		}

		$this->language->load('modification/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('modification/cjdropshipping');

		$token = $this->session->data['token'];

		$this->data['heading_title'] = $this->language->get('heading_title');

		// Close button
		$this->data['close'] = $this->url->link('extension/modification', 'token=' . $token, 'SSL');
		$this->data['button_close'] = $this->language->get('button_close');

		// Form action URLs go into HTML attributes — leave &amp; as-is
		$this->data['channel_add_url'] = $this->url->link('modification/cjdropshipping/addChannel', 'token=' . $token, 'SSL');
		$this->data['channel_edit_url'] = $this->url->link('modification/cjdropshipping/editChannel', 'token=' . $token, 'SSL');

		// JS-bound URLs — must decode &amp; so the token parameter arrives correctly
		$js_urls = [];

		foreach ([
			'channel_delete' => 'deleteChannel',
			'import'         => 'importProducts',
			'sync_stock'     => 'syncStock',
			'sync_prices'    => 'syncPrices',
			'sync_tracking'  => 'syncTracking',
			'register'       => 'registerWebhooks',
			'test'           => 'testConnection',
			'variants'       => 'getVariants',
			'purge_stale'    => 'purgeStale',
			'count_orphan'   => 'countOrphans',
			'purge_dup'      => 'purgeDuplicates',
			'count_dup'      => 'countDuplicates',
			'orders'         => 'getOrders',
			'dispatch'       => 'dispatchOrder',
			'tracking_email' => 'sendTrackingEmail'
		] as $key => $method) {
			$js_urls[$key] = str_replace('&amp;', '&', $this->url->link('modification/cjdropshipping/' . $method, 'token=' . $token, 'SSL'));
		}

		$js_urls['category'] = str_replace('&amp;', '&', $this->url->link('catalog/category/autocomplete', 'token=' . $token, 'SSL'));

		// Language strings passed to template
		foreach ([
			'tab_channels', 'tab_products', 'tab_orders', 'tab_about',
			'text_add_channel', 'text_edit_channel', 'text_no_channels',
			'text_channel_active', 'text_channel_inactive', 'text_none',
			'text_loading', 'text_orders_select', 'text_note', 'text_cj_payment_note',
			'text_cj_version', 'text_cj_author', 'text_cj_support',
			'text_cj_license', 'text_cj_tables',
			'cj_version', 'cj_author', 'cj_support', 'cj_license',
			'column_channel_name', 'column_status', 'column_action',
			'column_product_name', 'column_sku', 'column_variant_key', 'column_stock',
			'column_supplier_cost', 'column_last_stock_sync', 'column_last_price_sync',
			'entry_channel_name', 'entry_api_key', 'entry_webhook_secret',
			'entry_location_name', 'entry_shipping_map', 'entry_status',
			'entry_import_channel', 'entry_import_category',
			'entry_import_keyword', 'entry_import_country',
			'help_api_key', 'help_webhook_secret', 'help_shipping_map',
			'help_import_category', 'help_import_keyword',
			'placeholder_category', 'placeholder_keyword', 'title_generate_secret',
			'button_add_channel', 'button_edit', 'button_delete', 'button_save',
			'button_cancel', 'button_refresh', 'button_import', 'button_sync_stock',
			'button_sync_prices', 'button_sync_tracking',
			'button_register_webhooks', 'button_test_connection', 'button_cj_dashboard',
			'column_order_id', 'column_tracking_number',
			'column_customer', 'column_date', 'column_total', 'column_order_status',
			'column_cjd_status', 'column_action', 'column_email_tracking', 'column_email_sent',
			'header_set_option', 'text_cj_currency_note',
			'text_products_maintenance',
			'button_purge_stale', 'text_purge_confirm',
			'button_purge_duplicates', 'text_purge_dup_confirm',
			'text_prev', 'text_next'
		] as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		// Strings used by the dashboard script (rendered with json_encode in the template)
		$js_text = [];

		foreach ([
			'text_loading', 'text_none', 'text_no_channels', 'text_confirm_delete',
			'text_status_pending', 'text_status_dispatched', 'text_status_error',
			'text_importing', 'text_import_complete', 'text_stock_sync_complete',
			'text_price_sync_complete', 'text_tracking_sync_complete', 'text_dispatch_complete',
			'text_sync_errors', 'text_webhooks_registered', 'text_tracking_email_sent',
			'text_progress_variants', 'text_progress_pids', 'text_x_of_y', 'text_prices_updated',
			'text_page_variants', 'text_page_orders', 'text_no_orders', 'text_sending',
			'text_order_result', 'text_track_17track',
			'text_test_auth', 'text_test_api', 'text_test_token', 'text_test_expires',
			'text_purge_none', 'text_purge_found', 'text_purge_complete',
			'text_purge_dup_none', 'text_purge_dup_found', 'text_purge_dup_complete',
			'text_purge_dup_sample', 'text_purge_confirm', 'text_purge_dup_confirm',
			'button_purge_stale', 'button_purge_duplicates', 'button_purge_confirm', 'button_cancel',
			'button_dispatch', 'button_email_tracking', 'button_test_connection',
			'text_add_channel', 'text_edit_channel', 'button_delete',
			'error_channel_id', 'error_sync_failed', 'error_ajax'
		] as $key) {
			$js_text[$key] = $this->language->get($key);
		}

		$this->data['cj_js'] = [
			'url'  => $js_urls,
			'text' => $js_text
		];

		// Import country choices (warehouse country codes accepted by the CJ API)
		$this->data['countries'] = [
			'CN' => $this->language->get('text_country_cn'),
			'GB' => $this->language->get('text_country_gb'),
			'US' => $this->language->get('text_country_us'),
			'DE' => $this->language->get('text_country_de'),
			'FR' => $this->language->get('text_country_fr')
		];

		// About panel: list the tables with the configured prefix
		$tables = [];

		foreach (['dropship_channel', 'product_dropship', 'dropship_variant', 'dropship_order', 'product_variant'] as $table) {
			$tables[] = '- ' . DB_PREFIX . $table;
		}

		$this->data['cj_tables'] = implode('<br />', $tables);

		// Channel list — CJ only
		$this->data['channels'] = $this->model_modification_cjdropshipping->getChannels();

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

		$this->data['breadcrumbs'] = $this->getBreadcrumbs();

		$this->template = 'modification/cjdropshipping_dashboard.tpl';
		$this->children = ['common/header', 'common/footer'];

		$this->response->setOutput($this->render());
	}

	// =================================================================
	// CHANNEL MANAGEMENT
	// =================================================================

	public function addChannel(): void {
		$this->language->load('modification/' . $this->name);

		if ($this->tablesMissing()) {
			$this->session->data['error'] = $this->language->get('error_database');

			$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		$this->load->model('modification/cjdropshipping');

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$message = $this->validateChannel($this->request->post);

			if ($message) {
				$this->session->data['error'] = $message;
			} else {
				$data = $this->request->post;

				$data['shipping_map'] = $this->decodeShippingMap($data['shipping_map'] ?? '{}');

				try {
					$this->model_modification_cjdropshipping->addChannel($data);

					$this->session->data['success'] = $this->language->get('text_channel_added');
				} catch (\Exception $e) {
					$this->session->data['error'] = $e->getMessage();
				}
			}
		}

		$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function editChannel(): void {
		$this->language->load('modification/' . $this->name);

		if ($this->tablesMissing()) {
			$this->session->data['error'] = $this->language->get('error_database');

			$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		$this->load->model('modification/cjdropshipping');

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$channel_id = (int)($this->request->post['channel_id'] ?? 0);

			$message = ($channel_id < 1) ? $this->language->get('error_channel_id') : $this->validateChannel($this->request->post);

			if ($message) {
				$this->session->data['error'] = $message;
			} else {
				$data = $this->request->post;

				$data['shipping_map'] = $this->decodeShippingMap($data['shipping_map'] ?? '{}');

				try {
					$this->model_modification_cjdropshipping->editChannel($channel_id, $data);

					$this->session->data['success'] = $this->language->get('text_channel_updated');
				} catch (\Exception $e) {
					$this->session->data['error'] = $e->getMessage();
				}
			}
		}

		$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function deleteChannel(): void {
		$this->language->load('modification/' . $this->name);

		if ($this->tablesMissing()) {
			$this->session->data['error'] = $this->language->get('error_database');

			$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->session->data['error'] = $this->error['warning'];
		} else {
			$channel_id = (int)($this->request->get['channel_id'] ?? 0);

			if ($channel_id < 1) {
				$this->session->data['error'] = $this->language->get('error_channel_id');
			} else {
				try {
					$this->model_modification_cjdropshipping->deleteChannel($channel_id);

					$this->session->data['success'] = $this->language->get('text_channel_deleted');
				} catch (\Exception $e) {
					$this->session->data['error'] = $e->getMessage();
				}
			}
		}

		$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
	}

	// =================================================================
	// PRODUCT IMPORT
	// =================================================================

	/**
	 * Trigger a manual product import from CJ.
	 * Expects GET: channel_id, category_id, keyword (optional), country (optional), page (default 1).
	 * Returns JSON for AJAX calls from the dashboard.
	 */
	public function importProducts(): void {
		// Each page fetches 5 products with 0.5s delays between detail calls (~3s net).
		// Give generous headroom for slow CJ API responses and network overhead.
		@set_time_limit(120);

		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$channel_id = (int)($this->request->get['channel_id'] ?? 0);
		$category_id = (int)($this->request->get['category_id'] ?? 0);
		$page = max(1, (int)($this->request->get['page'] ?? 1));
		$source = trim($this->request->get['source'] ?? 'my_products'); // 'my_products' (default) or 'catalog'
		$keyword = trim($this->request->get['keyword'] ?? '');
		$country = trim($this->request->get['country'] ?? 'GB');

		if ($channel_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_channel_id')]);
			return;
		}

		try {
			if ($source === 'my_products') {
				// Import from the seller's CJ "My Products" list — avoids 410 errors
				$filter = [];

				if ($keyword) {
					$filter['keyword'] = $keyword; // myProduct/query uses 'keyword' (not 'keyWord')
				}

				$result = $this->model_modification_cjdropshipping->importMyProducts($channel_id, $category_id, $filter, 1, $page);
			} else {
				// Default: import from the general CJ catalog
				$filter = ['countryCode' => $country];

				if ($keyword) {
					$filter['keyWord'] = $keyword;
				}

				$result = $this->model_modification_cjdropshipping->importProducts($channel_id, $category_id, $filter, 1, $page);
			}

			$this->jsonResponse([
				'success'  => true,
				'imported' => $result['imported'],
				'skipped'  => $result['skipped'],
				'errors'   => $result['errors'],
				'page'     => $result['page'],
				'total'    => $result['total'],
				'has_more' => $result['has_more']
			]);
		} catch (\Throwable $e) {
			$this->jsonResponse(['error' => 'importProducts error: ' . $e->getMessage()]);
		}
	}

	// =================================================================

	/**
	 * Trigger a manual stock sync. Returns JSON.
	 */
	public function syncStock(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$channel_id = (int)($this->request->get['channel_id'] ?? 0);

		if ($channel_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_channel_id')]);
			return;
		}

		$offset = max(0, (int)($this->request->get['offset'] ?? 0));

		try {
			$result = $this->model_modification_cjdropshipping->syncStock($channel_id, $offset, 10);

			$this->jsonResponse([
				'success'   => true,
				'updated'   => $result['updated'],
				'errors'    => $result['errors'],
				'total'     => $result['total'],
				'processed' => $result['processed'],
				'has_more'  => $result['has_more'],
			]);
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
		}
	}

	/**
	 * Trigger a manual price sync. Returns JSON.
	 * Supports offset-based batching — call repeatedly until has_more is false.
	 */
	public function syncPrices(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$channel_id = (int)($this->request->get['channel_id'] ?? 0);

		if ($channel_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_channel_id')]);
			return;
		}

		$offset = max(0, (int)($this->request->get['offset'] ?? 0));

		try {
			$result = $this->model_modification_cjdropshipping->syncPrices($channel_id, $offset, 5);

			$this->jsonResponse([
				'success'   => true,
				'updated'   => $result['updated'],
				'errors'    => $result['errors'],
				'total'     => $result['total'],
				'processed' => $result['processed'],
				'has_more'  => $result['has_more'],
			]);
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
		}
	}

	/**
	 * Trigger a manual tracking sync. Returns JSON.
	 */
	public function syncTracking(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$channel_id = (int)($this->request->get['channel_id'] ?? 0);

		if ($channel_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_channel_id')]);
			return;
		}

		try {
			$result = $this->model_modification_cjdropshipping->syncTracking($channel_id);

			$this->jsonResponse(['success' => true, 'updated' => $result['updated'], 'errors' => $result['errors']]);
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
		}
	}

	// =================================================================
	// ORDER LISTING
	// =================================================================

	/**
	 * Return orders containing CJDropshipping products for the given channel.
	 * Expects GET: channel_id. Returns JSON {rows: []}.
	 */
	public function getOrders(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$channel_id = (int)($this->request->get['channel_id'] ?? 0);

		if ($channel_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_channel_id')]);
			return;
		}

		$start = (int)($this->request->get['start'] ?? 0);
		$limit = (int)($this->request->get['limit'] ?? 20);
		if ($limit < 1 || $limit > 100) {
			$limit = 20;
		}

		$rows = $this->model_modification_cjdropshipping->getOrdersForDispatch($channel_id, $start, $limit);
		$total = $this->model_modification_cjdropshipping->countOrdersForDispatch($channel_id);

		$this->jsonResponse(['rows' => $rows, 'total' => $total, 'start' => $start, 'limit' => $limit]);
	}

	/**
	 * Send a tracking notification email to the customer.
	 * Expects GET: order_id, channel_id. Returns JSON.
	 */
	public function sendTrackingEmail(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$order_id = (int)($this->request->get['order_id']   ?? 0);
		$channel_id = (int)($this->request->get['channel_id'] ?? 0);

		if ($order_id < 1 || $channel_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_order_id')]);
			return;
		}

		// Fetch order info + tracking numbers
		$row = $this->db->query("
			SELECT
				o.order_id,
				o.firstname,
				o.lastname,
				o.email,
				COALESCE(GROUP_CONCAT(
					DISTINCT NULLIF(do2.tracking_number, '')
					ORDER BY do2.dropship_order_id SEPARATOR ', '
				), '') AS tracking,
				COALESCE(GROUP_CONCAT(
					DISTINCT NULLIF(do2.tracking_carrier, '')
					ORDER BY do2.dropship_order_id SEPARATOR ', '
				), '') AS tracking_carrier
			FROM `" . DB_PREFIX . "order` o
			LEFT JOIN `" . DB_PREFIX . "dropship_order` do2
				ON do2.order_id = o.order_id
				AND do2.channel_id = '" . (int)$channel_id . "'
			WHERE o.order_id = '" . (int)$order_id . "'
			GROUP BY o.order_id, o.firstname, o.lastname, o.email
		")->row;

		if (empty($row) || empty($row['tracking'])) {
			$this->jsonResponse(['error' => $this->language->get('error_no_tracking')]);
			return;
		}

		$store_name = $this->config->get('config_name');
		$store_email = $this->config->get('config_email');
		$customer = trim($row['firstname'] . ' ' . $row['lastname']);
		$tracking = $row['tracking'];
		$carrier = $row['tracking_carrier'];

		$subject = html_entity_decode(sprintf($this->language->get('email_tracking_subject'), $store_name, $order_id), ENT_QUOTES, 'UTF-8');

		$text = html_entity_decode(sprintf($this->language->get('email_tracking_body'), $customer, $order_id, $tracking, $carrier ?: 'Unknown', $store_name), ENT_QUOTES, 'UTF-8');

		// HTML version via template
		$template_file = DIR_APPLICATION . 'view/template/mail/order_shipped.tpl';
		$html = '';

		if (file_exists($template_file)) {
			$data = [
				'store_name'       => $store_name,
				'customer'         => $customer,
				'order_id'         => $order_id,
				'tracking'         => $tracking,
				'tracking_carrier' => $carrier,
			];
			extract($data);
			ob_start();
			include($template_file);
			$html = ob_get_clean();
		}

		// Send email
		$mail = new Mail();
		$mail->setTo($row['email']);
		$mail->setFrom($store_email);
		$mail->setSender($store_name);
		$mail->setSubject($subject);
		$mail->setText($text);
		if ($html) {
			$mail->setHtml($html);
		}
		$mail->send();

		// Mark DB record
		$this->model_modification_cjdropshipping->markTrackingEmailSent($order_id, $channel_id);

		$this->jsonResponse([
			'success' => $this->language->get('text_tracking_email_sent'),
			'sent_at' => date('Y-m-d H:i'),
		]);
	}

	// =================================================================
	// ORDER DISPATCH
	// =================================================================

	/**
	 * Dispatch a NivoCart order to CJ.
	 * Expects GET: order_id. Returns JSON.
	 */
	public function dispatchOrder(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$order_id = (int)($this->request->get['order_id'] ?? 0);

		if ($order_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_order_id')]);
			return;
		}

		try {
			$result = $this->model_modification_cjdropshipping->dispatchOrder($order_id);

			$this->jsonResponse([
				'success'    => true,
				'dispatched' => $result['dispatched'],
				'errors'     => $result['errors']
			]);
		} catch (\Exception $e) {
			$this->jsonResponse(['error' => $e->getMessage()]);
		}
	}

	// =================================================================
	// WEBHOOK REGISTRATION
	// =================================================================

	/**
	 * Register NivoCart webhook URLs with CJ for a channel.
	 * Expects GET: channel_id. Returns JSON.
	 * The base URL is derived from HTTP_CATALOG config constant.
	 */
	public function registerWebhooks(): void {
		// CJ pings all three webhook URLs before returning — give it room to breathe.
		@set_time_limit(120);

		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		try {
			if (!$this->validatePermission()) {
				$this->jsonResponse(['error' => $this->language->get('error_permission')]);
				return;
			}

			if ($this->tablesMissing()) {
				$this->jsonResponse(['error' => $this->language->get('error_database')]);
				return;
			}

			$channel_id = (int)($this->request->get['channel_id'] ?? 0);

			if ($channel_id < 1) {
				$this->jsonResponse(['error' => $this->language->get('error_channel_id')]);
				return;
			}

			$baseUrl = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG;

			$success = $this->model_modification_cjdropshipping->registerWebhooks($channel_id, $baseUrl);

			if ($success) {
				$this->jsonResponse(['success' => true, 'message' => $this->language->get('text_webhooks_registered')]);
			} else {
				$this->jsonResponse(['error' => $this->language->get('error_webhooks_failed')]);
			}
		} catch (\Throwable $e) {
			$this->jsonResponse(['error' => 'registerWebhooks exception: ' . $e->getMessage()]);
		}
	}

	// =================================================================
	// DIAGNOSTICS
	// =================================================================

	public function testConnection(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$channel_id = (int)($this->request->get['channel_id'] ?? 0);

		if ($channel_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_channel_id')]);
			return;
		}

		$result = $this->model_modification_cjdropshipping->testConnection($channel_id);

		$this->jsonResponse($result);
	}

	// =================================================================
	// LIST MAPPED VARIANTS (AJAX)
	// =================================================================

	/**
	 * Return JSON list of mapped variants for a channel.
	 * Used by the dashboard product list panel.
	 * GET: channel_id, start (optional), limit (optional, default 50)
	 */
	public function getVariants(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$channel_id = (int)($this->request->get['channel_id'] ?? 0);

		if ($channel_id < 1) {
			$this->jsonResponse(['error' => $this->language->get('error_channel_id')]);
			return;
		}

		$start = max(0, (int)($this->request->get['start'] ?? 0));
		$limit = min(200, max(1, (int)($this->request->get['limit'] ?? 20)));

		$rows = $this->model_modification_cjdropshipping->getMappedProducts($channel_id, ['start' => $start, 'limit' => $limit]);
		$total = $this->model_modification_cjdropshipping->getTotalMappedProducts($channel_id);

		$this->jsonResponse(['rows' => $rows, 'total' => $total]);
	}

	// =================================================================
	// PURGE STALE PRODUCTS
	// =================================================================

	/**
	 * AJAX: return count of orphaned dropship variant rows. Returns JSON.
	 */
	public function countOrphans(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$count = $this->model_modification_cjdropshipping->countOrphanProducts();

		$this->jsonResponse(['count' => $count]);
	}

	/**
	 * AJAX: purge all orphaned dropship records. Returns JSON.
	 */
	public function purgeStale(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		try {
			$deleted = $this->model_modification_cjdropshipping->purgeStaleProducts();
			$this->jsonResponse(['success' => true, 'deleted' => $deleted]);
		} catch (\Throwable $e) {
			$this->jsonResponse(['error' => 'purgeStale error: ' . $e->getMessage()]);
		}
	}

	// =================================================================
	// PURGE DUPLICATE PRODUCTS
	// =================================================================

	/**
	 * AJAX: return count of duplicate products (same supplier_vid imported more than once
	 * within the same channel — i.e. the same CJ variant was imported twice, giving it two
	 * NivoCart product rows). Also returns a small sample for UI preview/debugging.
	 */
	public function countDuplicates(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		$count = $this->model_modification_cjdropshipping->countDuplicateProducts();
		$sample = $count > 0 ? $this->model_modification_cjdropshipping->getDuplicateSample(3) : [];

		$this->jsonResponse(['count' => $count, 'sample' => $sample]);
	}

	/**
	 * AJAX: delete all duplicate products, keeping the original (lowest product_id) for each CJ product.
	 */
	public function purgeDuplicates(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/cjdropshipping');

		if (!$this->validatePermission()) {
			$this->jsonResponse(['error' => $this->language->get('error_permission')]);
			return;
		}

		if ($this->tablesMissing()) {
			$this->jsonResponse(['error' => $this->language->get('error_database')]);
			return;
		}

		try {
			$deleted = $this->model_modification_cjdropshipping->purgeDuplicateProducts();
			$this->jsonResponse(['success' => true, 'deleted' => $deleted]);
		} catch (\Throwable $e) {
			$this->jsonResponse(['error' => 'purgeDuplicates error: ' . $e->getMessage()]);
		}
	}

	// =================================================================
	// INSTALL / UNINSTALL
	// =================================================================

	public function install(): void {
		// Called by the Modifications manager (extension/modification/install).
		// Not available as a direct route.
		if ($this->isDirectRoute('install')) {
			return;
		}

		// The dropship tables are shared between dropship connectors.
		// CREATE TABLE IF NOT EXISTS ensures whichever connector installs first
		// creates the tables; the others pass silently without data loss.

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropship_channel` (
			`channel_id` int NOT NULL AUTO_INCREMENT,
			`name` varchar(64) NOT NULL,
			`provider` varchar(32) NOT NULL COMMENT 'cjdropshipping | other dropship connectors',
			`consumer_key` varchar(128) NOT NULL,
			`secret_key` varchar(128) NOT NULL,
			`access_token` text NOT NULL,
			`token_expires_at` datetime NULL DEFAULT NULL,
			`authkey` varchar(32) NOT NULL DEFAULT '',
			`location_id` varchar(64) NOT NULL DEFAULT '00000000-0000-0000-0000-000000000000',
			`location_name` varchar(64) NOT NULL DEFAULT 'Default',
			`webhook_secret` varchar(255) NOT NULL DEFAULT '',
			`shipping_map` text NOT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '1',
			`date_added` datetime NOT NULL,
			`date_modified` datetime NOT NULL,
			PRIMARY KEY (`channel_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_dropship` (
			`dropship_id` int NOT NULL AUTO_INCREMENT,
			`product_id` int NOT NULL COMMENT 'FK nc_product.product_id',
			`channel_id` int NOT NULL COMMENT 'FK nc_dropship_channel.channel_id',
			`supplier_pid` varchar(128) NOT NULL DEFAULT '' COMMENT 'Provider parent product ID',
			`supplier_guid` varchar(128) NOT NULL DEFAULT '',
			`supplier_authkey` varchar(64) NOT NULL DEFAULT '',
			`supplier_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
			`rrp` decimal(15,4) NOT NULL DEFAULT '0.0000',
			`vat_rate` decimal(5,2) NOT NULL DEFAULT '20.00',
			`last_price_sync` datetime NULL DEFAULT NULL,
			`last_stock_sync` datetime NULL DEFAULT NULL,
			`last_image_sync` datetime NULL DEFAULT NULL,
			`is_active` tinyint(1) NOT NULL DEFAULT '1',
			`date_added` datetime NOT NULL,
			`date_modified` datetime NOT NULL,
			PRIMARY KEY (`dropship_id`),
			UNIQUE KEY `uniq_product_channel` (`product_id`, `channel_id`),
			KEY `idx_supplier_pid` (`supplier_pid`),
			KEY `idx_channel` (`channel_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropship_variant` (
			`variant_id` int NOT NULL AUTO_INCREMENT,
			`dropship_id` int NOT NULL COMMENT 'FK nc_product_dropship.dropship_id',
			`product_id` int NOT NULL COMMENT 'FK nc_product.product_id',
			`channel_id` int NOT NULL COMMENT 'FK nc_dropship_channel.channel_id',
			`supplier_vid` varchar(128) NOT NULL DEFAULT '',
			`supplier_sku` varchar(128) NOT NULL DEFAULT '',
			`barcode` varchar(64) NOT NULL DEFAULT '',
			`variant_key` varchar(255) NOT NULL DEFAULT '',
			`variant_name` varchar(255) NOT NULL DEFAULT '',
			`weight` decimal(10,4) NOT NULL DEFAULT '0.0000',
			`length` decimal(10,4) NOT NULL DEFAULT '0.0000',
			`width` decimal(10,4) NOT NULL DEFAULT '0.0000',
			`height` decimal(10,4) NOT NULL DEFAULT '0.0000',
			`supplier_cost` decimal(15,4) NOT NULL DEFAULT '0.0000',
			`rrp` decimal(15,4) NOT NULL DEFAULT '0.0000',
			`currency_code` varchar(3) NOT NULL DEFAULT 'GBP',
			`quantity` int NOT NULL DEFAULT '0',
			`warehouse_code` varchar(16) NOT NULL DEFAULT '',
			`image` varchar(255) NOT NULL DEFAULT '',
			`last_stock_sync` datetime NULL DEFAULT NULL,
			`last_price_sync` datetime NULL DEFAULT NULL,
			`is_active` tinyint(1) NOT NULL DEFAULT '1',
			`date_added` datetime NOT NULL,
			`date_modified` datetime NOT NULL,
			PRIMARY KEY (`variant_id`),
			UNIQUE KEY `uniq_channel_vid` (`channel_id`, `supplier_vid`),
			KEY `idx_dropship_id` (`dropship_id`),
			KEY `idx_product_id` (`product_id`),
			KEY `idx_supplier_sku` (`supplier_sku`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropship_order` (
			`dropship_order_id` int NOT NULL AUTO_INCREMENT,
			`order_id` int NOT NULL,
			`order_product_id` int NOT NULL,
			`channel_id` int NOT NULL,
			`variant_id` int NULL DEFAULT NULL,
			`supplier_vid` varchar(128) NOT NULL DEFAULT '',
			`supplier_order_ref` varchar(128) NOT NULL DEFAULT '',
			`supplier_order_id` varchar(128) NOT NULL DEFAULT '',
			`dispatch_status` tinyint(1) NOT NULL DEFAULT '0',
			`tracking_number` varchar(128) NOT NULL DEFAULT '',
			`tracking_carrier` varchar(64) NOT NULL DEFAULT '',
			`dispatch_error` varchar(255) NOT NULL DEFAULT '',
			`dispatched_at` datetime NULL DEFAULT NULL,
			`tracking_email_sent` datetime NULL DEFAULT NULL COMMENT 'When tracking notification was last emailed to customer',
			`date_added` datetime NOT NULL,
			`date_modified` datetime NOT NULL,
			PRIMARY KEY (`dropship_order_id`),
			UNIQUE KEY `uniq_order_product` (`order_id`, `order_product_id`),
			KEY `idx_order` (`order_id`),
			KEY `idx_channel` (`channel_id`),
			KEY `idx_variant` (`variant_id`),
			KEY `idx_dispatch_status` (`dispatch_status`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_variant` (
			`id` int NOT NULL AUTO_INCREMENT,
			`pid` varchar(64) NOT NULL DEFAULT '' COMMENT 'DS parent product ID',
			`product_id` int NOT NULL COMMENT 'FK nc_product.product_id',
			`variant_id` int NOT NULL COMMENT 'FK nc_dropship_variant.variant_id',
			`channel_id` int NOT NULL COMMENT 'FK nc_dropship_channel.channel_id',
			`sort_order` tinyint UNSIGNED NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `idx_pid_channel` (`pid`, `channel_id`),
			KEY `idx_product_id` (`product_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
	}

	public function uninstall(): void {
		// Called by the Modifications manager (extension/modification/uninstall).
		// Not available as a direct route.
		if ($this->isDirectRoute('uninstall')) {
			return;
		}

		// Guard: only drop shared tables if no other dropship channels remain.
		// This prevents data loss for other connectors when only CJDropshipping is uninstalled.
		$remaining = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "dropship_channel` WHERE `provider` != 'cjdropshipping'");

		if ((int)$remaining->row['total'] > 0) {
			// Other connectors still active — only remove CJ channel rows and their data
			$cj_channels = $this->db->query("SELECT `channel_id` FROM `" . DB_PREFIX . "dropship_channel` WHERE `provider` = 'cjdropshipping'");

			foreach ($cj_channels->rows as $ch) {
				$channel_id = (int)$ch['channel_id'];

				$this->db->query("DELETE FROM `" . DB_PREFIX . "dropship_order` WHERE `channel_id` = '" . $channel_id . "'");
				$this->db->query("DELETE dv FROM `" . DB_PREFIX . "dropship_variant` dv INNER JOIN `" . DB_PREFIX . "product_dropship` pd ON dv.dropship_id = pd.dropship_id WHERE pd.`channel_id` = '" . $channel_id . "'");
				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_variant` WHERE `channel_id` = '" . $channel_id . "'");
				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_dropship` WHERE `channel_id` = '" . $channel_id . "'");
				$this->db->query("DELETE FROM `" . DB_PREFIX . "dropship_channel` WHERE `channel_id` = '" . $channel_id . "'");
			}

		} else {
			// No other connectors — safe to drop all shared tables
			$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dropship_order`");
			$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_variant`");
			$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dropship_variant`");
			$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "product_dropship`");
			$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dropship_channel`");
		}
	}

	// =================================================================
	// PRIVATE HELPERS
	// =================================================================

	protected function validatePermission(): bool {
		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		}

		return true;
	}

	/**
	 * True when install()/uninstall() were requested as a route of this controller
	 * rather than called by the Modifications manager.
	 */
	protected function isDirectRoute(string $method): bool {
		return ($this->request->get['route'] ?? '') === 'modification/' . $this->name . '/' . $method;
	}

	protected function getBreadcrumbs(): array {
		return [
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
	}

	/**
	 * Validate the add/edit channel form. Returns an error message, or an empty string when valid.
	 */
	protected function validateChannel(array $post): string {
		if (!$this->validatePermission()) {
			return $this->error['warning'];
		}

		if (trim($post['name'] ?? '') === '') {
			return $this->language->get('error_channel_name');
		}

		if (trim($post['consumer_key'] ?? '') === '') {
			return $this->language->get('error_api_key');
		}

		$map = trim($post['shipping_map'] ?? '');

		if ($map !== '' && !is_array(json_decode($map, true))) {
			return $this->language->get('error_shipping_map_json');
		}

		return '';
	}

	private function decodeShippingMap(string $raw): array {
		$decoded = json_decode(trim($raw), true);

		if (!is_array($decoded)) {
			return [];
		}

		$clean = [];

		foreach ($decoded as $k => $v) {
			$k = trim((string)$k);
			$v = trim((string)$v);

			if ($k !== '' && $v !== '') {
				$clean[$k] = $v;
			}
		}

		return $clean;
	}

	private function jsonResponse(array $data): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
}
