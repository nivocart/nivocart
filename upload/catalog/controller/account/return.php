<?php
/**
 * Class ControllerAccountReturn
 *
 * @package NivoCart
 */
class ControllerAccountReturn extends Controller {
	private $error = [];

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		if (!$this->customer->isSecure() || $this->customer->loginExpired()) {
			$this->customer->logout();

			$this->session->data['redirect'] = $this->url->link('account/return', '', 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->language->load('account/return');

		$this->document->setTitle($this->language->get('heading_title'));

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

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return', $url, 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_return_id'] = $this->language->get('text_return_id');
		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_empty'] = $this->language->get('text_empty');

		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->load->model('account/return');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->data['returns'] = [];

		$return_total = $this->model_account_return->getTotalReturns();

		$results = $this->model_account_return->getReturns(($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['returns'][] = [
				'return_id'  => $result['return_id'],
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'href'       => $this->url->link('account/return/info', 'return_id=' . $result['return_id'] . $url, 'SSL')
			];
		}

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/return', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('account/return_list');

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

	public function info() {
		$this->language->load('account/return');

		if (isset($this->request->get['return_id'])) {
			$return_id = $this->request->get['return_id'];
		} else {
			$return_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		if (!$this->customer->isSecure() || $this->customer->loginExpired()) {
			$this->customer->logout();

			$this->session->data['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->load->model('account/return');

		$return_info = $this->model_account_return->getReturn($return_id);

		if ($return_info) {
			$this->document->setTitle($this->language->get('text_return'));

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

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->data['breadcrumbs'][] = [
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/return', $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			];

			$this->data['breadcrumbs'][] = [
				'text'      => $this->language->get('text_return'),
				'href'      => $this->url->link('account/return/info', 'return_id=' . $this->request->get['return_id'] . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			];

			$this->data['heading_title'] = $this->language->get('text_return');

			$this->data['text_return_detail'] = $this->language->get('text_return_detail');
			$this->data['text_return_id'] = $this->language->get('text_return_id');
			$this->data['text_order_id'] = $this->language->get('text_order_id');
			$this->data['text_date_ordered'] = $this->language->get('text_date_ordered');
			$this->data['text_customer'] = $this->language->get('text_customer');
			$this->data['text_email'] = $this->language->get('text_email');
			$this->data['text_telephone'] = $this->language->get('text_telephone');
			$this->data['text_status'] = $this->language->get('text_status');
			$this->data['text_date_added'] = $this->language->get('text_date_added');
			$this->data['text_product'] = $this->language->get('text_product');
			$this->data['text_comment'] = $this->language->get('text_comment');
			$this->data['text_history'] = $this->language->get('text_history');

			$this->data['column_product'] = $this->language->get('column_product');
			$this->data['column_model'] = $this->language->get('column_model');
			$this->data['column_quantity'] = $this->language->get('column_quantity');
			$this->data['column_opened'] = $this->language->get('column_opened');
			$this->data['column_reason'] = $this->language->get('column_reason');
			$this->data['column_action'] = $this->language->get('column_action');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
			$this->data['column_status'] = $this->language->get('column_status');
			$this->data['column_comment'] = $this->language->get('column_comment');

			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['return_id'] = $return_info['return_id'];
			$this->data['order_id'] = $return_info['order_id'];
			$this->data['date_ordered'] = date($this->language->get('date_format_short'), strtotime($return_info['date_ordered']));
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($return_info['date_added']));
			$this->data['firstname'] = $return_info['firstname'];
			$this->data['lastname'] = $return_info['lastname'];
			$this->data['email'] = $return_info['email'];
			$this->data['telephone'] = $return_info['telephone'];
			$this->data['product'] = $return_info['product'];
			$this->data['model'] = $return_info['model'];
			$this->data['quantity'] = $return_info['quantity'];
			$this->data['reason'] = $return_info['reason'];
			$this->data['opened'] = $return_info['opened'] ? $this->language->get('text_yes') : $this->language->get('text_no');
			$this->data['comment'] = nl2br($return_info['comment']);
			$this->data['action'] = $return_info['action'];

			$this->data['histories'] = [];

			$results = $this->model_account_return->getReturnHistories($this->request->get['return_id']);

			foreach ($results as $result) {
				$this->data['histories'][] = [
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => nl2br($result['comment'])
				];
			}

			$this->data['continue'] = $this->url->link('account/return', $url, 'SSL');

			// Theme
			$this->data['template'] = $this->config->get('config_template');

			$this->resolveTemplate('account/return_info');

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

		} else {
			$this->document->setTitle($this->language->get('text_return'));

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
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/return', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			];

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->data['breadcrumbs'][] = [
				'text'      => $this->language->get('text_return'),
				'href'      => $this->url->link('account/return/info', 'return_id=' . $return_id . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			];

			$this->data['heading_title'] = $this->language->get('text_return');

			$this->data['text_error'] = $this->language->get('text_error');

			$this->data['button_continue'] = $this->language->get('button_continue');

			$this->data['continue'] = $this->url->link('account/return', '', 'SSL');

			// Theme
			$this->data['template'] = $this->config->get('config_template');

			$this->resolveTemplate('error/not_found');

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

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$this->response->setOutput($this->render());
		}
	}

	/**
	 * AJAX endpoint — called when the customer submits their Order ID.
	 *
	 * Expects POST: order_id
	 * Returns JSON:
	 *   success  => bool
	 *   error    => string   (on failure)
	 *   data     => {
	 *     date_ordered : string  (Y-m-d)
	 *     products     : [{ product_id, name, model, quantity }]
	 *   }
	 */
	public function ajaxorder() {
		$this->language->load('account/return');

		// Only accept POST XHR
		if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
			$this->response->addHeader('HTTP/1.1 405 Method Not Allowed');
			$this->response->setOutput(json_encode(['success' => false, 'error' => 'Method not allowed']));
			return;
		}

		if (!$this->customer->isLogged()) {
			$this->response->addHeader('HTTP/1.1 403 Forbidden');
			$this->response->setOutput(json_encode(['success' => false, 'error' => $this->language->get('error_login')]));
			return;
		}

		$order_id = isset($this->request->post['order_id']) ? (int)$this->request->post['order_id'] : 0;

		if (!$order_id) {
			$this->response->setOutput(json_encode([
				'success' => false,
				'error'   => $this->language->get('error_order_id')
			]));
			return;
		}

		// Validate order belongs to this customer — direct db query avoids
		// any model registry conflict with the currently-executing controller.
		$order_query = $this->db->query("SELECT order_id, date_added FROM `" . DB_PREFIX . "order`" . " WHERE order_id = '" . (int)$order_id . "'" . " AND customer_id = '" . (int)$this->customer->getId() . "'");

		if (!$order_query->num_rows) {
			$this->response->setOutput(json_encode([
				'success' => false,
				'error'   => $this->language->get('error_order_not_found')
			]));
			return;
		}

		$order_info = $order_query->row;

		// Fetch products straight from order_product — no model load needed
		$products_query = $this->db->query("SELECT product_id, `name`, model, quantity FROM `" . DB_PREFIX . "order_product`" . " WHERE order_id = '" . (int)$order_id . "'" . " ORDER BY `name` ASC");

		$products = $products_query->rows;

		if (empty($products)) {
			$this->response->setOutput(json_encode([
				'success' => false,
				'error'   => $this->language->get('error_order_no_products')
			]));
			return;
		}

		$age_days    = $this->calcOrderAgeDays($order_info['date_added']);
		$age_blocked = $age_days > 180;
		if ($age_days > 180) {
			$age_warning = $this->language->get('error_age_180');
		} elseif ($age_days > 60) {
			$age_warning = $this->language->get('text_warning_age_60');
		} elseif ($age_days > 30) {
			$age_warning = $this->language->get('text_warning_age_30');
		} else {
			$age_warning = '';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode([
			'success' => true,
			'data'    => [
				'date_ordered' => date('Y-m-d', strtotime($order_info['date_added'])),
				'products'     => $products,
				'age_warning'  => $age_warning,
				'age_blocked'  => $age_blocked,
			]
		]));
	}

	public function insert() {
		if ($this->config->get('config_secure') && !$this->request->isSecure()) {
			$this->redirect($this->url->link('account/return/insert', '', 'SSL'), 301);
		}

		$this->language->load('account/return');

		$this->load->model('account/return');

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			// Age check — re-query DB so the posted date_ordered cannot be spoofed
			$order_id_post = isset($this->request->post['order_id']) ? (int)$this->request->post['order_id'] : 0;
			if ($order_id_post) {
				$age_q = $this->db->query(
					"SELECT date_added FROM `" . DB_PREFIX . "order` " .
					"WHERE order_id = '" . $order_id_post . "' " .
					"AND customer_id = '" . (int)$this->customer->getId() . "'"
				);
				if ($age_q->num_rows && $this->calcOrderAgeDays($age_q->row['date_added']) > 180) {
					$this->error['warning'] = $this->language->get('error_age_180');
				}
			}
			if (empty($this->error) && $this->validate()) {
				$this->model_account_return->addReturn($this->request->post);
				unset($this->session->data['captcha']);
				$this->redirect($this->url->link('account/return/success', '', 'SSL'));
			}
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');

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
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return/insert', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_description'] = $this->language->get('text_description');
		$this->data['text_order'] = $this->language->get('text_order');
		$this->data['text_product'] = $this->language->get('text_product');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		$this->data['entry_order_id'] = $this->language->get('entry_order_id');
		$this->data['entry_date_ordered'] = $this->language->get('entry_date_ordered');
		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
		$this->data['entry_email'] = $this->language->get('entry_email');
		$this->data['entry_telephone'] = $this->language->get('entry_telephone');
		$this->data['entry_product'] = $this->language->get('entry_product');
		$this->data['entry_model'] = $this->language->get('entry_model');
		$this->data['entry_quantity'] = $this->language->get('entry_quantity');
		$this->data['entry_reason'] = $this->language->get('entry_reason');
		$this->data['entry_action_requested'] = $this->language->get('entry_action_requested');
		$this->data['entry_opened'] = $this->language->get('entry_opened');
		$this->data['entry_fault_detail'] = $this->language->get('entry_fault_detail');
		$this->data['entry_captcha'] = $this->language->get('entry_captcha');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');
		$this->data['button_lookup_order'] = $this->language->get('button_lookup_order');

		// Errors
		foreach (['warning', 'order_id', 'firstname', 'lastname', 'email', 'telephone', 'product', 'model', 'reason', 'captcha'] as $key) {
			$this->data['error_' . $key] = isset($this->error[$key]) ? $this->error[$key] : '';
		}

		// AJAX endpoint URL passed to JS
		$this->data['ajax_order_url'] = $this->url->link('account/return/ajaxorder', '', 'SSL');

		$this->data['action'] = $this->url->link('account/return/insert', '', 'SSL');

		// Pre-fill customer details from logged-in account
		$this->data['firstname'] = isset($this->request->post['firstname']) ? $this->request->post['firstname'] : $this->customer->getFirstName();
		$this->data['lastname'] = isset($this->request->post['lastname']) ? $this->request->post['lastname'] : $this->customer->getLastName();
		$this->data['email'] = isset($this->request->post['email']) ? $this->request->post['email'] : $this->customer->getEmail();
		$this->data['telephone'] = isset($this->request->post['telephone']) ? $this->request->post['telephone'] : $this->customer->getTelephone();

		// Order / product fields — restored from POST on validation failure
		$this->data['order_id'] = isset($this->request->post['order_id']) ? $this->request->post['order_id'] : '';
		$this->data['date_ordered'] = isset($this->request->post['date_ordered']) ? $this->request->post['date_ordered'] : '';
		$this->data['product'] = isset($this->request->post['product']) ? $this->request->post['product'] : '';
		$this->data['model'] = isset($this->request->post['model']) ? $this->request->post['model'] : '';
		$this->data['quantity'] = isset($this->request->post['quantity']) ? $this->request->post['quantity'] : 1;
		$this->data['opened'] = isset($this->request->post['opened']) ? $this->request->post['opened'] : false;
		$this->data['return_reason_id'] = isset($this->request->post['return_reason_id']) ? $this->request->post['return_reason_id'] : '';
		$this->data['return_action_id'] = isset($this->request->post['return_action_id']) ? $this->request->post['return_action_id'] : '';
		$this->data['comment'] = isset($this->request->post['comment']) ? $this->request->post['comment'] : '';
		$this->data['captcha'] = isset($this->request->post['captcha']) ? $this->request->post['captcha'] : '';

		$this->load->model('localisation/return_reason');

		$this->data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons([]);

		$this->load->model('localisation/return_action');

		$this->data['return_actions'] = $this->model_localisation_return_action->getReturnActions([]);

		// Create session Captcha
		$this->load->library('captcha');

		$captcha = new Captcha();

		$this->session->data['captcha'] = $captcha->getCode();

		$this->data['captcha_image'] = $this->session->data['captcha'];

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));

			if ($information_info) {
				$this->data['text_agree'] = sprintf(
					$this->language->get('text_agree'),
					$this->url->link('information/information/info', 'information_id=' . $this->config->get('config_return_id'), 'SSL'),
					$information_info['title'],
					$information_info['title']
				);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}

		$this->data['agree'] = isset($this->request->post['agree']) ? $this->request->post['agree'] : false;

		$this->data['back'] = $this->url->link('account/account', '', 'SSL');

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('account/return_form');

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
		if ($this->config->get('config_secure') && !$this->request->isSecure()) {
			$this->redirect($this->url->link('account/return/success', '', 'SSL'));
		}

		$this->language->load('account/return');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/return', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_message'] = $this->language->get('text_message');
		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

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

	// -------------------------------------------------------------------------
	// Guest return flow (no account required)
	// -------------------------------------------------------------------------

	/**
	 * Step 1 — order lookup form.
	 * Guest enters Order ID + order email. On success, order data is stored in
	 * the session and the guest is redirected to guestInsert().
	 */
	public function guest() {
		if ($this->config->get('config_secure') && !$this->request->isSecure()) {
			$this->redirect($this->url->link('account/return/guest', '', 'SSL'), 301);
		}

		$this->language->load('account/return');

		$this->document->setTitle($this->language->get('text_guest_heading'));

		$this->data['breadcrumbs'] = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			],
			[
				'text'      => $this->language->get('text_guest_heading'),
				'href'      => $this->url->link('account/return/guest', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			]
		];

		$this->data['heading_title']     = $this->language->get('text_guest_heading');
		$this->data['text_guest_lookup'] = $this->language->get('text_guest_lookup');
		$this->data['entry_order_id']    = $this->language->get('entry_order_id');
		$this->data['entry_email_order'] = $this->language->get('entry_email_order');
		$this->data['button_lookup']     = $this->language->get('button_lookup');
		$this->data['error_warning']     = '';
		$this->data['order_id']          = '';
		$this->data['email']             = '';

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$order_id = isset($this->request->post['order_id']) ? (int)$this->request->post['order_id'] : 0;
			$email    = isset($this->request->post['email'])    ? trim($this->request->post['email'])    : '';

			$this->data['order_id'] = $order_id;
			$this->data['email']    = $email;

			if (!$order_id || empty($email)) {
				$this->data['error_warning'] = $this->language->get('error_guest_order_not_found');
			} else {
				$order_query = $this->db->query(
					"SELECT o.order_id, o.firstname, o.lastname, o.email, o.telephone, o.date_added " .
					"FROM `" . DB_PREFIX . "order` o " .
					"WHERE o.order_id = '" . (int)$order_id . "' " .
					"AND LOWER(o.email) = LOWER('" . $this->db->escape($email) . "')"
				);

				if (!$order_query->num_rows) {
					$this->data['error_warning'] = $this->language->get('error_guest_order_not_found');
				} else {
					$order    = $order_query->row;
					$age_days = $this->calcOrderAgeDays($order['date_added']);

					if ($age_days > 180) {
						$this->data['error_warning'] = $this->language->get('error_age_180');
					} else {
						$products_query = $this->db->query(
							"SELECT product_id, `name`, model, quantity FROM `" . DB_PREFIX . "order_product` " .
							"WHERE order_id = '" . (int)$order_id . "' ORDER BY `name` ASC"
						);

						$warning_level = '';
						if ($age_days > 60) {
							$warning_level = 'hard';
						} elseif ($age_days > 30) {
							$warning_level = 'soft';
						}

						$this->session->data['guest_return_order'] = [
							'order_id'          => (int)$order['order_id'],
							'firstname'         => $order['firstname'],
							'lastname'          => $order['lastname'],
							'email'             => $order['email'],
							'telephone'         => $order['telephone'],
							'date_ordered'      => date('Y-m-d', strtotime($order['date_added'])),
							'products'          => $products_query->rows,
							'age_warning_level' => $warning_level
						];

						$this->redirect($this->url->link('account/return/guestInsert', '', 'SSL'));
					}
				}
			}
		}

		$this->data['action'] = $this->url->link('account/return/guest', '', 'SSL');

		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('account/return_guest');

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
	 * Step 2 — full return form (pre-filled from validated order in session).
	 * Also handles the final POST submission.
	 */
	public function guestInsert() {
		if ($this->config->get('config_secure') && !$this->request->isSecure()) {
			$this->redirect($this->url->link('account/return/guestInsert', '', 'SSL'), 301);
		}

		$this->language->load('account/return');

		// Guard: must have a validated order in session
		if (empty($this->session->data['guest_return_order'])) {
			$this->redirect($this->url->link('account/return/guest', '', 'SSL'));
		}

		$order = $this->session->data['guest_return_order'];

		$this->load->model('account/return');

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			// Age check — use date_ordered from verified session (cannot be spoofed via POST)
			if ($this->calcOrderAgeDays($order['date_ordered']) > 180) {
				$this->error['warning'] = $this->language->get('error_age_180');
			} elseif ($this->validateGuest()) {
				$post_data                 = $this->request->post;
				$post_data['customer_id']  = 0;
				$post_data['order_id']     = $order['order_id'];
				$post_data['date_ordered'] = $order['date_ordered'];
				$post_data['email']        = $order['email'];   // locked to verified order email

				$this->model_account_return->addReturn($post_data);

				unset($this->session->data['captcha']);
				unset($this->session->data['guest_return_order']);

				$this->redirect($this->url->link('account/return/guestSuccess', '', 'SSL'));
			}
		}

		$this->document->setTitle($this->language->get('text_guest_heading'));
		$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.css');

		$this->data['breadcrumbs'] = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			],
			[
				'text'      => $this->language->get('text_guest_heading'),
				'href'      => $this->url->link('account/return/guest', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			],
			[
				'text'      => $this->language->get('text_return'),
				'href'      => $this->url->link('account/return/guestInsert', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			]
		];

		$this->data['heading_title']          = $this->language->get('text_guest_heading');
		$this->data['text_description']       = $this->language->get('text_guest_form_intro');
		$this->data['text_order']             = $this->language->get('text_order');
		$this->data['text_product']           = $this->language->get('text_product');
		$this->data['text_yes']               = $this->language->get('text_yes');
		$this->data['text_no']                = $this->language->get('text_no');

		$this->data['entry_order_id']         = $this->language->get('entry_order_id');
		$this->data['entry_date_ordered']     = $this->language->get('entry_date_ordered');
		$this->data['entry_firstname']        = $this->language->get('entry_firstname');
		$this->data['entry_lastname']         = $this->language->get('entry_lastname');
		$this->data['entry_email']            = $this->language->get('entry_email');
		$this->data['entry_telephone']        = $this->language->get('entry_telephone');
		$this->data['entry_product']          = $this->language->get('entry_product');
		$this->data['entry_model']            = $this->language->get('entry_model');
		$this->data['entry_quantity']         = $this->language->get('entry_quantity');
		$this->data['entry_reason']           = $this->language->get('entry_reason');
		$this->data['entry_action_requested'] = $this->language->get('entry_action_requested');
		$this->data['entry_opened']           = $this->language->get('entry_opened');
		$this->data['entry_fault_detail']     = $this->language->get('entry_fault_detail');
		$this->data['entry_captcha']          = $this->language->get('entry_captcha');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back']     = $this->language->get('button_back');

		foreach (['warning', 'firstname', 'lastname', 'telephone', 'product', 'model', 'reason', 'captcha'] as $key) {
			$this->data['error_' . $key] = isset($this->error[$key]) ? $this->error[$key] : '';
		}

		// Age warning (30/60 day — informational, form still submits)
		$warning_level = $order['age_warning_level'] ?? '';
		if ($warning_level === 'hard') {
			$this->data['age_warning'] = $this->language->get('text_warning_age_60');
		} elseif ($warning_level === 'soft') {
			$this->data['age_warning'] = $this->language->get('text_warning_age_30');
		} else {
			$this->data['age_warning'] = '';
		}

		$this->data['action'] = $this->url->link('account/return/guestInsert', '', 'SSL');

		// Order data locked from validated session
		$this->data['order_id']     = $order['order_id'];
		$this->data['date_ordered'] = $order['date_ordered'];
		$this->data['email']        = $order['email'];

		// Personal details: from POST on validation failure, otherwise from order record
		$this->data['firstname'] = isset($this->request->post['firstname']) ? $this->request->post['firstname'] : $order['firstname'];
		$this->data['lastname']  = isset($this->request->post['lastname'])  ? $this->request->post['lastname']  : $order['lastname'];
		$this->data['telephone'] = isset($this->request->post['telephone']) ? $this->request->post['telephone'] : $order['telephone'];

		// Product list from order (for the dropdown / pre-fill)
		$this->data['products'] = $order['products'];

		$single = count($order['products']) === 1;
		$this->data['product'] = isset($this->request->post['product']) ? $this->request->post['product']
			: ($single ? $order['products'][0]['name']  : '');
		$this->data['model']   = isset($this->request->post['model'])   ? $this->request->post['model']
			: ($single ? $order['products'][0]['model'] : '');
		$this->data['quantity']         = isset($this->request->post['quantity'])         ? $this->request->post['quantity']         : 1;
		$this->data['opened']           = isset($this->request->post['opened'])           ? $this->request->post['opened']           : false;
		$this->data['return_reason_id'] = isset($this->request->post['return_reason_id']) ? $this->request->post['return_reason_id'] : '';
		$this->data['return_action_id'] = isset($this->request->post['return_action_id']) ? $this->request->post['return_action_id'] : '';
		$this->data['comment']          = isset($this->request->post['comment'])          ? $this->request->post['comment']          : '';
		$this->data['captcha']          = isset($this->request->post['captcha'])          ? $this->request->post['captcha']          : '';

		$this->load->model('localisation/return_reason');
		$this->data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons([]);

		$this->load->model('localisation/return_action');
		$this->data['return_actions'] = $this->model_localisation_return_action->getReturnActions([]);

		$this->load->library('captcha');
		$captcha = new Captcha();
		$this->session->data['captcha'] = $captcha->getCode();
		$this->data['captcha_image']    = $this->session->data['captcha'];

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));
			if ($information_info) {
				$this->data['text_agree'] = sprintf(
					$this->language->get('text_agree'),
					$this->url->link('information/information/info', 'information_id=' . $this->config->get('config_return_id'), 'SSL'),
					$information_info['title'],
					$information_info['title']
				);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}

		$this->data['agree'] = isset($this->request->post['agree']) ? $this->request->post['agree'] : false;
		$this->data['back']  = $this->url->link('account/return/guest', '', 'SSL');

		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('account/return_guest_form');

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
	 * Success page shown after a guest return is submitted.
	 */
	public function guestSuccess() {
		if ($this->config->get('config_secure') && !$this->request->isSecure()) {
			$this->redirect($this->url->link('account/return/guestSuccess', '', 'SSL'));
		}

		$this->language->load('account/return');

		$this->document->setTitle($this->language->get('text_guest_heading'));

		$this->data['breadcrumbs'] = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			],
			[
				'text'      => $this->language->get('text_guest_heading'),
				'href'      => $this->url->link('account/return/guest', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			]
		];

		$this->data['heading_title']    = $this->language->get('text_guest_heading');
		$this->data['text_message']     = $this->language->get('text_guest_success');
		$this->data['button_continue']  = $this->language->get('button_continue');
		$this->data['continue']         = $this->url->link('common/home', '', 'SSL');

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
	 * Guest return status lookup — Option B self-service tracking.
	 * Guest enters Order ID + email to view all returns on that order.
	 */
	public function guestTrack() {
		if ($this->config->get('config_secure') && !$this->request->isSecure()) {
			$this->redirect($this->url->link('account/return/guestTrack', '', 'SSL'), 301);
		}

		$this->language->load('account/return');

		$this->document->setTitle($this->language->get('text_guest_track_heading'));

		$this->data['breadcrumbs'] = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			],
			[
				'text'      => $this->language->get('text_guest_track_heading'),
				'href'      => $this->url->link('account/return/guestTrack', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			]
		];

		$this->data['heading_title']          = $this->language->get('text_guest_track_heading');
		$this->data['text_guest_track_intro'] = $this->language->get('text_guest_track_intro');
		$this->data['text_guest_track_no_returns'] = $this->language->get('text_guest_track_no_returns');
		$this->data['text_return_id']         = $this->language->get('text_return_id');
		$this->data['text_status']            = $this->language->get('text_status');
		$this->data['text_date_added']        = $this->language->get('text_date_added');
		$this->data['column_product']         = $this->language->get('column_product');
		$this->data['column_reason']          = $this->language->get('column_reason');
		$this->data['column_status']          = $this->language->get('column_status');
		$this->data['column_date_added']      = $this->language->get('column_date_added');
		$this->data['entry_order_id']         = $this->language->get('entry_order_id');
		$this->data['entry_email_order']      = $this->language->get('entry_email_order');
		$this->data['button_lookup']          = $this->language->get('button_lookup');
		$this->data['text_guest_no_account']  = sprintf(
			$this->language->get('text_guest_no_account'),
			$this->url->link('account/return/guest', '', 'SSL')
		);

		$this->data['error_warning'] = '';
		$this->data['returns']       = [];
		$this->data['order_id']      = '';
		$this->data['email']         = '';
		$this->data['searched']      = false;

		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			$order_id = isset($this->request->post['order_id']) ? (int)$this->request->post['order_id'] : 0;
			$email    = isset($this->request->post['email'])    ? trim($this->request->post['email'])    : '';

			$this->data['order_id'] = $order_id;
			$this->data['email']    = $email;
			$this->data['searched'] = true;

			if (!$order_id || empty($email)) {
				$this->data['error_warning'] = $this->language->get('error_guest_order_not_found');
			} else {
				$this->load->model('account/return');
				$results = $this->model_account_return->getReturnsByGuest($order_id, $email);

				if (empty($results)) {
					$this->data['error_warning'] = $this->language->get('text_guest_track_no_returns');
				} else {
					foreach ($results as $result) {
						$this->data['returns'][] = [
							'return_id'  => $result['return_id'],
							'product'    => $result['product'],
							'reason'     => $result['reason'],
							'status'     => $result['status'],
							'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
						];
					}
				}
			}
		}

		$this->data['action'] = $this->url->link('account/return/guestTrack', '', 'SSL');

		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('account/return_guest_track');

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
	 * Validates the guest return form submission.
	 * Email is not validated here — it is locked to the verified order email from session.
	 */
	protected function validateGuest(): bool {
		if ((mb_strlen($this->request->post['firstname'], 'UTF-8') < 1) || (mb_strlen($this->request->post['firstname'], 'UTF-8') > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((mb_strlen($this->request->post['lastname'], 'UTF-8') < 1) || (mb_strlen($this->request->post['lastname'], 'UTF-8') > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((mb_strlen($this->request->post['telephone'], 'UTF-8') < 3) || (mb_strlen($this->request->post['telephone'], 'UTF-8') > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((mb_strlen($this->request->post['product'], 'UTF-8') < 1) || (mb_strlen($this->request->post['product'], 'UTF-8') > 255)) {
			$this->error['product'] = $this->language->get('error_product');
		}

		if ((mb_strlen($this->request->post['model'], 'UTF-8') < 1) || (mb_strlen($this->request->post['model'], 'UTF-8') > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->language->get('error_reason');
		}

		if (!isset($this->request->post['captcha']) || empty($this->session->data['captcha']) || ($this->session->data['captcha'] !== $this->request->post['captcha'])) {
			$this->error['captcha'] = $this->language->get('error_captcha');
		}

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));
			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		return empty($this->error);
	}

	/**
	 * Calculate the effective age of an order in whole days.
	 * A +1-day grace period is added before the comparison so that an order
	 * placed late at night on the recorded date always gets the full benefit
	 * of each return window. Use strict > (not >=) at each threshold.
	 */
	private function calcOrderAgeDays(string $date_added): int {
		$order_time = strtotime($date_added) + 86400; // +1 grace day
		return (int)((time() - $order_time) / 86400);
	}

	protected function validate() {
		if (!$this->request->post['order_id']) {
			$this->error['order_id'] = $this->language->get('error_order_id');
		}

		if ((mb_strlen($this->request->post['firstname'], 'UTF-8') < 1) || (mb_strlen($this->request->post['firstname'], 'UTF-8') > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((mb_strlen($this->request->post['lastname'], 'UTF-8') < 1) || (mb_strlen($this->request->post['lastname'], 'UTF-8') > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((mb_strlen($this->request->post['email'], 'UTF-8') > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((mb_strlen($this->request->post['telephone'], 'UTF-8') < 3) || (mb_strlen($this->request->post['telephone'], 'UTF-8') > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((mb_strlen($this->request->post['product'], 'UTF-8') < 1) || (mb_strlen($this->request->post['product'], 'UTF-8') > 255)) {
			$this->error['product'] = $this->language->get('error_product');
		}

		if ((mb_strlen($this->request->post['model'], 'UTF-8') < 1) || (mb_strlen($this->request->post['model'], 'UTF-8') > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->language->get('error_reason');
		}

		if (!isset($this->request->post['captcha']) || empty($this->session->data['captcha']) || ($this->session->data['captcha'] !== ($this->request->post['captcha']))) {
			$this->error['captcha'] = $this->language->get('error_captcha');
		}

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}

		return empty($this->error);
	}
}
