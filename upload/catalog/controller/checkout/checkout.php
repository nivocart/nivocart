<?php
/**
 * Class ControllerCheckoutCheckout
 *
 * @package NivoCart
 */
class ControllerCheckoutCheckout extends Controller {
	private $error = [];

	public function index() {
		// Secure redirect
		if ($this->config->get('config_secure') && !$this->request->isSecure()) {
			$this->redirect($this->url->link('checkout/checkout', '', 'SSL'));
			return;
		}

		// Customer Login redirect
		// If guest checkout is enabled, unlogged users may proceed.
		// If guest checkout is disabled, all unlogged users must log in first.
		if (!$this->customer->isLogged()) {
			if (!$this->config->get('config_guest_checkout')) {
				$this->redirect($this->url->link('account/login', '', 'SSL'));
				return;
			}
		} elseif (!$this->customer->isSecure()) {
			$this->redirect($this->url->link('account/login', '', 'SSL'));
			return;
		}

		// Validate cart has products and has stock
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$this->redirect($this->url->link('checkout/cart', '', 'SSL'));
			return;
		}

		// Validate minimum quantity requirements
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] === $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$this->redirect($this->url->link('checkout/cart', '', 'SSL'));
				return;
			}

			// Validate minimum age — requires a logged-in account
			if ($this->config->get('config_customer_dob') && ($product['age_minimum'] > 0)) {
				if (!$this->customer->isLogged() || !$this->customer->isSecure()) {
					$this->redirect($this->url->link('account/login', '', 'SSL'));
					return;
				}
			}
		}

		// Clear any stale order/shipping session data on fresh page load
		if (!isset($this->request->get['payment'])) {
			unset($this->session->data['order_id']);
			unset($this->session->data['check_shipping_address']);
		}

		if (isset($this->session->data['check_shipping_address'])) {
			$this->data['check_shipping_address'] = $this->session->data['check_shipping_address'];
		} else {
			$this->data['check_shipping_address'] = 1;
		}

		$this->language->load('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addStyle('catalog/view/javascript/jquery/colorbox/colorbox.min.css');
		$this->document->addScript('catalog/view/javascript/jquery/colorbox/jquery.colorbox-min.js');

		// Breadcrumbs
		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_cart'),
			'href'      => $this->url->link('checkout/cart', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		// Language strings
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_cart'] = $this->language->get('text_cart');
		$this->data['text_checkout_payment_address'] = $this->language->get('text_checkout_payment_address');
		$this->data['text_checkout_shipping_address'] = $this->language->get('text_checkout_shipping_address');
		$this->data['text_guest_login'] = sprintf($this->language->get('text_guest_login'), $this->url->link('account/login', '', 'SSL'));
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_female'] = $this->language->get('text_female');
		$this->data['text_male'] = $this->language->get('text_male');
		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');
		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
		$this->data['text_comments'] = $this->language->get('text_comments');

		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
		$this->data['entry_email'] = $this->language->get('entry_email');
		$this->data['entry_telephone'] = $this->language->get('entry_telephone');
		$this->data['entry_gender'] = $this->language->get('entry_gender');
		$this->data['entry_date_of_birth'] = $this->language->get('entry_date_of_birth');
		$this->data['entry_company'] = $this->language->get('entry_company');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_company_id'] = $this->language->get('entry_company_id');
		$this->data['entry_tax_id'] = $this->language->get('entry_tax_id');
		$this->data['entry_address_1'] = $this->language->get('entry_address_1');
		$this->data['entry_address_2'] = $this->language->get('entry_address_2');
		$this->data['entry_postcode'] = $this->language->get('entry_postcode');
		$this->data['entry_city'] = $this->language->get('entry_city');
		$this->data['entry_country'] = $this->language->get('entry_country');
		$this->data['entry_zone'] = $this->language->get('entry_zone');
		$this->data['entry_shipping'] = $this->language->get('entry_shipping');

		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['logged'] = $this->customer->isLogged();
		$this->data['guest_checkout'] = (bool)$this->config->get('config_guest_checkout');
		$this->data['shipping_required'] = $this->cart->hasShipping();

		$this->data['one_page_cart'] = $this->url->link('checkout/cart', '', 'SSL');
		$this->data['login_url'] = $this->url->link('account/login', '', 'SSL');
		$this->data['action'] = $this->url->link('checkout/checkout', '', 'SSL');

		// Error warning — stock only (coupon/voucher/reward no longer handled here)
		if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
			$this->data['error_warning'] = $this->language->get('error_stock');
		} else {
			$this->data['error_warning'] = '';
		}

		$this->load->model('checkout/order');
		$this->load->model('account/address');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

		// -----------------------------------------------------------------------
		// POST — validate, store session data, return JSON redirect to confirm
		// -----------------------------------------------------------------------
		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			if ($this->validate()) {
				$customer_info = $this->request->post;

				if (!$this->customer->isLogged()) {
					if ($this->config->get('config_guest_checkout')) {
						// ---------------------------------------------------------
						// Guest path — store identity in session only, no account
						// ---------------------------------------------------------
						$this->session->data['guest'] = [
							'firstname' => $customer_info['firstname'],
							'lastname'  => $customer_info['lastname'],
							'email'     => $customer_info['email'],
							'telephone' => isset($customer_info['telephone']) ? $customer_info['telephone'] : '',
						];
					} else {
						// ---------------------------------------------------------
						// Auto-register path — guest checkout is OFF
						// Create an account and log the customer in automatically
						// ---------------------------------------------------------
						$this->load->model('account/customer');
						$this->load->model('checkout/checkout_tools');

						$newsletter = ($this->config->get('config_checkout_newsletter') === 1) ? 1 : 0;

						$customer_data = [
							'customer_group_id' => $customer_info['customer_group_id'],
							'firstname'         => $customer_info['firstname'],
							'lastname'          => $customer_info['lastname'],
							'email'             => $customer_info['email'],
							'telephone'         => isset($customer_info['telephone']) ? $customer_info['telephone'] : '000',
							'gender'            => isset($customer_info['gender']) ? $customer_info['gender'] : 0,
							'date_of_birth'     => isset($customer_info['date_of_birth']) ? $customer_info['date_of_birth'] : '1970-01-01',
							'password'          => $this->model_checkout_checkout_tools->generatePassword(),
							'newsletter'        => $newsletter,
							'company'           => $customer_info['company'],
							'company_id'        => $customer_info['company_id'],
							'tax_id'            => $customer_info['tax_id'],
							'address_1'         => $customer_info['address_1'],
							'address_2'         => $customer_info['address_2'],
							'postcode'          => $customer_info['postcode'],
							'city'              => $customer_info['city'],
							'zone_id'           => $customer_info['zone_id'],
							'country_id'        => $customer_info['country_id']
						];

						$this->model_account_customer->addCustomer($customer_data);

						$customer_status = $this->model_account_customer->getCustomerByEmail($customer_info['email']);

						if ($customer_status && !$customer_status['approved']) {
							$this->redirect($this->url->link('checkout/cart', '', 'SSL'));
							return;
						} else {
							$this->customer->login($customer_data['email'], $customer_data['password']);
						}
					}
				}

				// Ensure logged-in customer has a default address (registered only)
				if ($this->customer->isLogged()) {
					$default_address_id = $this->model_account_address->getDefaultAddressId($this->customer->getId());

					if (!$default_address_id) {
						$this->model_account_address->addAddress([
							'customer_id' => $this->customer->getId(),
							'firstname'   => $customer_info['firstname'],
							'lastname'    => $customer_info['lastname'],
							'company'     => $customer_info['company'],
							'company_id'  => $customer_info['company_id'],
							'tax_id'      => $customer_info['tax_id'],
							'address_1'   => $customer_info['address_1'],
							'address_2'   => $customer_info['address_2'],
							'postcode'    => $customer_info['postcode'],
							'city'        => $customer_info['city'],
							'zone_id'     => $customer_info['zone_id'],
							'country_id'  => $customer_info['country_id'],
							'default'     => 1
						]);
					}
				}

				// Resolve country and zone names
				$country_info = $this->model_localisation_country->getCountry((int)$customer_info['country_id']);
				$country_name = $country_info ? $country_info['name'] : '';

				$zone_info = $this->model_localisation_zone->getZone((int)$customer_info['zone_id']);
				$zone_name = $zone_info ? $zone_info['name'] : '';

				// Payment address
				$payment = [
					'firstname'      => $customer_info['firstname'],
					'lastname'       => $customer_info['lastname'],
					'company'        => $customer_info['company'],
					'company_id'     => $customer_info['company_id'],
					'tax_id'         => $customer_info['tax_id'],
					'address_1'      => $customer_info['address_1'],
					'address_2'      => $customer_info['address_2'],
					'city'           => $customer_info['city'],
					'postcode'       => $customer_info['postcode'],
					'zone'           => $zone_name,
					'zone_id'        => $customer_info['zone_id'],
					'country'        => $country_name,
					'country_id'     => $customer_info['country_id'],
					'payment_method' => isset($this->session->data['payment_method']['title']) ? $this->session->data['payment_method']['title'] : (isset($customer_info['payment_method']) ? $customer_info['payment_method'] : ''),
					'payment_code'   => isset($this->session->data['payment_method']['code']) ? $this->session->data['payment_method']['code'] : (isset($customer_info['code']) ? $customer_info['code'] : '')
				];

				// Shipping address
				if (isset($customer_info['check_shipping_address'])) {
					// Same as payment address
					$shipping = [
						'firstname'  => $customer_info['firstname'],
						'lastname'   => $customer_info['lastname'],
						'company'    => $customer_info['company'],
						'address_1'  => $customer_info['address_1'],
						'address_2'  => $customer_info['address_2'],
						'city'       => $customer_info['city'],
						'postcode'   => $customer_info['postcode'],
						'zone'       => $zone_name,
						'zone_id'    => $customer_info['zone_id'],
						'country'    => $country_name,
						'country_id' => $customer_info['country_id']
					];

					$this->session->data['check_shipping_address'] = 1;
				} else {
					$s_country_info = $this->model_localisation_country->getCountry((int)$customer_info['shipping_country_id']);
					$s_country_name = $s_country_info ? $s_country_info['name'] : '';

					$s_zone_info = $this->model_localisation_zone->getZone((int)$customer_info['shipping_zone_id']);
					$s_zone_name = $s_zone_info ? $s_zone_info['name'] : '';

					$shipping = [
						'firstname'  => $customer_info['shipping_firstname'],
						'lastname'   => $customer_info['shipping_lastname'],
						'company'    => $customer_info['shipping_company'],
						'address_1'  => $customer_info['shipping_address_1'],
						'address_2'  => $customer_info['shipping_address_2'],
						'city'       => $customer_info['shipping_city'],
						'postcode'   => $customer_info['shipping_postcode'],
						'zone'       => $s_zone_name,
						'zone_id'    => $customer_info['shipping_zone_id'],
						'country'    => $s_country_name,
						'country_id' => $customer_info['shipping_country_id']
					];

					$this->session->data['check_shipping_address'] = 0;
				}

				// Store all order data in session for the confirm controller
				$this->session->data['one_page_order'] = [
					'customer_group_id'   => isset($customer_info['customer_group_id']) ? $customer_info['customer_group_id'] : '',
					'firstname'           => $payment['firstname'],
					'lastname'            => $payment['lastname'],
					'email'               => isset($customer_info['email']) ? $customer_info['email'] : '',
					'telephone'           => isset($customer_info['telephone']) ? $customer_info['telephone'] : '',
					'gender'              => isset($customer_info['gender']) ? $customer_info['gender'] : 0,
					'date_of_birth'       => isset($customer_info['date_of_birth']) ? $customer_info['date_of_birth'] : '1970-01-01',
					'payment_firstname'   => $payment['firstname'],
					'payment_lastname'    => $payment['lastname'],
					'payment_company'     => $payment['company'],
					'payment_company_id'  => $payment['company_id'],
					'payment_tax_id'      => $payment['tax_id'],
					'payment_address_1'   => $payment['address_1'],
					'payment_address_2'   => $payment['address_2'],
					'payment_city'        => $payment['city'],
					'payment_postcode'    => $payment['postcode'],
					'payment_zone'        => $payment['zone'],
					'payment_zone_id'     => $payment['zone_id'],
					'payment_country'     => $payment['country'],
					'payment_country_id'  => $payment['country_id'],
					'payment_method'      => $payment['payment_method'],
					'payment_code'        => $payment['payment_code'],
					'shipping_firstname'  => $shipping['firstname'],
					'shipping_lastname'   => $shipping['lastname'],
					'shipping_company'    => $shipping['company'],
					'shipping_address_1'  => $shipping['address_1'],
					'shipping_address_2'  => $shipping['address_2'],
					'shipping_city'       => $shipping['city'],
					'shipping_postcode'   => $shipping['postcode'],
					'shipping_zone'       => $shipping['zone'],
					'shipping_zone_id'    => $shipping['zone_id'],
					'shipping_country'    => $shipping['country'],
					'shipping_country_id' => $shipping['country_id'],
					'shipping_method'     => isset($this->session->data['shipping_method']['title']) ? $this->session->data['shipping_method']['title'] : '',
					'shipping_code'       => isset($this->session->data['shipping_method']['code']) ? $this->session->data['shipping_method']['code'] : '',
					'comment'             => isset($customer_info['comment']) ? $customer_info['comment'] : ''
				];

				// Return JSON redirect to confirm controller
				$json = ['redirect' => $this->url->link('checkout/checkout_confirm', '', 'SSL')];

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
				return;

			} else {
				// Validation failed — return errors as JSON
				$json = ['error' => $this->error];

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
				return;
			}
		}

		// -----------------------------------------------------------------------
		// GET — build display data for the form
		// -----------------------------------------------------------------------

		// Error messages
		$error_fields = [
			'firstname', 'lastname', 'email', 'telephone', 'date_of_birth',
			'company_id', 'tax_id', 'address_1', 'city', 'postcode', 'country', 'zone',
			'shipping_firstname', 'shipping_lastname', 'shipping_address_1',
			'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_zone',
			'shipping_method', 'payment_method', 'agree'
		];

		foreach ($error_fields as $field) {
			$this->data['error_' . $field] = isset($this->error[$field]) ? $this->error[$field] : '';
		}

		// 'exists' overwrites email error
		if (isset($this->error['exists'])) {
			$this->data['error_email'] = $this->error['exists'];
		}

		// Customer address (logged-in registered customers only)
		$customer_address = [];

		if ($this->customer->isLogged() && $this->customer->isSecure()) {
			$default_address_id = $this->model_account_address->getDefaultAddressId($this->customer->getId());

			if ($default_address_id) {
				$customer_address = $this->model_account_address->getAddress($default_address_id);
			}
		}

		// Shipping options
		if (isset($this->session->data['check_shipping_address'])) {
			$this->data['check_shipping_address'] = $this->session->data['check_shipping_address'];
		} else {
			$this->data['check_shipping_address'] = 1;
		}

		// Customer fields
		$this->data['one_page_phone'] = $this->config->get('config_checkout_phone');
		$this->data['one_page_gender'] = $this->config->get('config_customer_gender');
		$this->data['one_page_dob'] = $this->config->get('config_customer_dob');

		$customer_fields = [
			'firstname'     => ['post', 'customer', ''],
			'lastname'      => ['post', 'customer', ''],
			'email'         => ['post', 'customer', ''],
			'telephone'     => ['post', 'customer', ''],
			'gender'        => ['post', 'customer', 0],
			'date_of_birth' => ['post', 'customer', '']
		];

		foreach ($customer_fields as $field => $sources) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif ($this->customer->isLogged()) {
				$method = 'get' . str_replace('_', '', ucwords($field, '_'));
				$this->data[$field] = method_exists($this->customer, $method) ? $this->customer->$method() : $sources[2];
			} else {
				$this->data[$field] = $sources[2];
			}
		}

		// Address fields
		$address_fields = ['company', 'company_id', 'tax_id', 'address_1', 'address_2', 'city', 'postcode'];

		foreach ($address_fields as $field) {
			if (isset($this->request->post[$field])) {
				$this->data[$field] = $this->request->post[$field];
			} elseif (!empty($customer_address[$field])) {
				$this->data[$field] = $customer_address[$field];
			} else {
				$this->data[$field] = '';
			}
		}

		// Country / Zone
		if (isset($this->request->post['country_id'])) {
			$this->data['country_id'] = $this->request->post['country_id'];
		} elseif (!empty($customer_address['country_id'])) {
			$this->data['country_id'] = $customer_address['country_id'];
		} else {
			$this->data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->request->post['zone_id'])) {
			$this->data['zone_id'] = $this->request->post['zone_id'];
		} elseif (!empty($customer_address['zone_id'])) {
			$this->data['zone_id'] = $customer_address['zone_id'];
		} else {
			$this->data['zone_id'] = '';
		}

		$country_info = $this->model_localisation_country->getCountry((int)$this->data['country_id']);
		$this->data['country_name'] = $country_info ? $country_info['name'] : '';

		$zone_info = $this->model_localisation_zone->getZone((int)$this->data['zone_id']);
		$this->data['zone_name'] = $zone_info ? $zone_info['name'] : '';

		// Shipping address fields
		$shipping_address_fields = [
			'shipping_firstname', 'shipping_lastname', 'shipping_company',
			'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode'
		];

		foreach ($shipping_address_fields as $field) {
			$this->data[$field] = isset($this->request->post[$field]) ? $this->request->post[$field] : '';
		}

		if (isset($this->request->post['shipping_country_id'])) {
			$this->data['shipping_country_id'] = $this->request->post['shipping_country_id'];
		} else {
			$this->data['shipping_country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->request->post['shipping_zone_id'])) {
			$this->data['shipping_zone_id'] = $this->request->post['shipping_zone_id'];
		} else {
			$this->data['shipping_zone_id'] = '';
		}

		$s_country_info = $this->model_localisation_country->getCountry((int)$this->data['shipping_country_id']);
		$this->data['shipping_country_name'] = $s_country_info ? $s_country_info['name'] : '';

		$s_zone_info = $this->model_localisation_zone->getZone((int)$this->data['shipping_zone_id']);
		$this->data['shipping_zone_name'] = $s_zone_info ? $s_zone_info['name'] : '';

		// Comment
		$this->data['comment'] = isset($this->request->post['comment']) ? $this->request->post['comment'] : '';

		// Customer groups
		$this->load->model('account/customer_group');

		$this->data['customer_groups'] = [];

		if (is_array($this->config->get('config_customer_group_display'))) {
			$customer_groups = $this->model_account_customer_group->getCustomerGroups();

			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$this->data['customer_groups'][] = $customer_group;
				}
			}
		}

		if (isset($this->request->post['customer_group_id'])) {
			$this->data['customer_group_id'] = $this->request->post['customer_group_id'];
		} elseif ($this->customer->isLogged()) {
			$this->data['customer_group_id'] = $this->customer->getCustomerGroupId();
		} else {
			$this->data['customer_group_id'] = $this->config->get('config_customer_group_id');
		}

		// Terms and Conditions
		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			if ($information_info) {
				$this->data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('config_checkout_id'), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}

		$this->data['agree'] = (isset($this->request->post['agree']) && $this->config->get('config_checkout_id')) ? $this->request->post['agree'] : '';

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		// Shipping address data for quote calculation
		$shipping_address = [
			'firstname'      => $this->data['shipping_firstname'] ?: $this->data['firstname'],
			'lastname'       => $this->data['shipping_lastname'] ?: $this->data['lastname'],
			'company'        => $this->data['shipping_company'],
			'address_1'      => $this->data['shipping_address_1'] ?: $this->data['address_1'],
			'address_2'      => $this->data['shipping_address_2'] ?: $this->data['address_2'],
			'city'           => $this->data['shipping_city'] ?: $this->data['city'],
			'postcode'       => $this->data['shipping_postcode'] ?: $this->data['postcode'],
			'zone'           => $this->data['shipping_zone_name'] ?: $this->data['zone_name'],
			'zone_id'        => $this->data['shipping_zone_id'] ?: $this->data['zone_id'],
			'country'        => $this->data['shipping_country_name'] ?: $this->data['country_name'],
			'country_id'     => $this->data['shipping_country_id'] ?: $this->data['country_id'],
			'address_format' => ''
		];

		$payment_address = [
			'firstname'  => $this->data['firstname'],
			'lastname'   => $this->data['lastname'],
			'company'    => $this->data['company'],
			'company_id' => $this->data['company_id'],
			'tax_id'     => $this->data['tax_id'],
			'address_1'  => $this->data['address_1'],
			'address_2'  => $this->data['address_2'],
			'city'       => $this->data['city'],
			'postcode'   => $this->data['postcode'],
			'zone'       => $this->data['zone_name'],
			'zone_id'    => $this->data['zone_id'],
			'country'    => $this->data['country_name'],
			'country_id' => $this->data['country_id']
		];

		// Shipping methods
		$quote_data = [];

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('shipping');

		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('shipping/' . $result['code']);

				$quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address);

				if ($quote) {
					$quote_data[$result['code']] = [
						'title'      => $quote['title'],
						'quote'      => $quote['quote'],
						'sort_order' => $quote['sort_order'],
						'error'      => $quote['error']
					];
				}
			}
		}

		$sort_order = [];

		foreach ($quote_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $quote_data);

		$this->data['shipping_methods'] = $quote_data;
		$this->session->data['shipping_methods'] = $this->data['shipping_methods'];

		$this->data['shipping_method_code'] = (isset($this->session->data['shipping_method']) && $this->session->data['shipping_method']) ? $this->session->data['shipping_method']['code'] : '';

		// Payment methods
		if (!empty($payment_address)) {
			$total_data = [];
			$total = 0.0;
			$taxes = $this->cart->getTaxes();

			$results = $this->model_setting_extension->getExtensions('total');

			usort($results, fn($a, $b) => $this->config->get($a['code'] . '_sort_order') <=> $this->config->get($b['code'] . '_sort_order'));

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);

					$model = $this->{'model_total_' . $result['code']};
					$contribution = $model->getTotal($taxes, $total);

					$total_data = array_merge($total_data, $contribution['total_data']);
					$total += $contribution['total'];
					$taxes += $contribution['taxes'];
				}
			}

			$method_data = [];

			$results = $this->model_setting_extension->getExtensions('payment');

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('payment/' . $result['code']);

					$method = $this->{'model_payment_' . $result['code']}->getMethod($payment_address, $total);

					if ($method) {
						$method_data[$result['code']] = $method;
					}
				}
			}

			$sort_order = [];

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->data['payment_methods'] = $method_data;
			$this->session->data['payment_methods'] = $this->data['payment_methods'];
		}

		// Payment images
		$this->load->model('design/payment');
		$this->load->model('tool/image');

		$this->data['payment_images'] = [];

		$image_results = $this->model_design_payment->getPaymentImages([]);

		if ($image_results) {
			foreach ($image_results as $image_result) {
				$method_image = ($image_result['image'] && file_exists(DIR_IMAGE . $image_result['image'])) ? $this->model_tool_image->resize($image_result['image'], 140, 35) : '';

				$this->data['payment_images'][] = [
					'payment' => strtolower($image_result['payment']),
					'image'   => $method_image,
					'status'  => $image_result['status']
				];
			}
		}

		// PayPal fee
		$paypal_fee = 0;
		$paypal_fee_total = $this->config->get('paypal_fee_total');

		if (empty($paypal_fee_total) || ($this->cart->getTotal() < $paypal_fee_total)) {
			if ($this->config->get('paypal_fee_fee_type') === 'F') {
				$paypal_fee = $this->config->get('paypal_fee_fee');
			} else {
				$paypal_fee = ($this->cart->getTotal() * $this->config->get('paypal_fee_fee')) / 100;

				$min = $this->config->get('paypal_fee_fee_min');
				$max = $this->config->get('paypal_fee_fee_max');

				if (!empty($min) && ($paypal_fee < $min)) {
					$paypal_fee = $min;
				}

				if (!empty($max) && ($paypal_fee > $max)) {
					$paypal_fee = $max;
				}
			}
		}

		$this->data['paypal_fee'] = ($paypal_fee > 0) ? $this->currency->format($paypal_fee, $this->config->get('config_currency')) : false;

		$this->data['payment_method_code'] = isset($this->session->data['payment_method']['code']) ? $this->session->data['payment_method']['code'] : '';

		if (isset($this->request->get['quickconfirm'])) {
			$this->data['quickconfirm'] = $this->request->get['quickconfirm'];
		}

		// ----------------------------------------------------------------
		// Payment widget data — generic, gateway-agnostic
		// ----------------------------------------------------------------

		$this->load->model('localisation/currency');

		$currency_info = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));
		$currency_code = $currency_info ? $currency_info['code'] : $this->config->get('config_currency');

		$this->load->model('checkout/payment_widget');

		$payment_widget_data = $this->model_checkout_payment_widget->getWidgetData($method_data, $total, $currency_code);

		$this->data['payment_widget_data'] = json_encode($payment_widget_data);
		$this->data['template'] = $this->config->get('config_template');

		// ----------------------------------------------------------------
		// Gateway-specific assets
		// ----------------------------------------------------------------
		$payment_gateway_scripts = [];

		if (isset($method_data['stripe_payments'])) {
			$payment_gateway_scripts[] = 'https://js.stripe.com/v3/';
		}

		$this->data['payment_gateway_scripts'] = $payment_gateway_scripts;

		$local_gateway_scripts = [];

		foreach (array_keys($payment_widget_data) as $gw_code) {
			$local_gateway_scripts[] = 'catalog/view/javascript/payment/' . $gw_code . '.js';
		}

		$this->data['local_gateway_scripts'] = $local_gateway_scripts;

		// Theme
		$this->resolveTemplate('checkout/checkout');

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

	public function validate() {
		$this->language->load('checkout/checkout');

		if ((mb_strlen($this->request->post['firstname'] ?? '', 'UTF-8') < 1) || (mb_strlen($this->request->post['firstname'] ?? '', 'UTF-8') > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((mb_strlen($this->request->post['lastname'] ?? '', 'UTF-8') < 1) || (mb_strlen($this->request->post['lastname'] ?? '', 'UTF-8') > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if (isset($this->request->post['email'])) {
			if ((mb_strlen($this->request->post['email'], 'UTF-8') > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
				$this->error['email'] = $this->language->get('error_email');
			}

			if (!$this->customer->isLogged()) {
				if (!$this->config->get('config_guest_checkout')) {
					$this->load->model('account/customer');

					if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
						$this->error['exists'] = $this->language->get('error_exists');
					}
				}
			}

			$this->load->model('tool/email');

			if (!$this->model_tool_email->verifyMail($this->request->post['email'])) {
				$this->error['email'] = $this->language->get('error_email');
			}
		}

		if ($this->config->get('config_checkout_phone')) {
			if ((mb_strlen($this->request->post['telephone'] ?? '', 'UTF-8') < 3) || (mb_strlen($this->request->post['telephone'] ?? '', 'UTF-8') > 32)) {
				$this->error['telephone'] = $this->language->get('error_telephone');
			}
		}

		if ($this->config->get('config_customer_dob')) {
			if (isset($this->request->post['date_of_birth']) && (mb_strlen($this->request->post['date_of_birth'], 'UTF-8') === 10)) {
				if ($this->request->post['date_of_birth'] !== date('Y-m-d', strtotime($this->request->post['date_of_birth']))) {
					$this->error['date_of_birth'] = $this->language->get('error_date_of_birth');
				}
			} else {
				$this->error['date_of_birth'] = $this->language->get('error_date_of_birth');
			}
		}

		$this->load->model('account/customer_group');

		if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$customer_group = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		if ($customer_group) {
			if ($customer_group['company_id_display'] && $customer_group['company_id_required'] && empty($this->request->post['company_id'])) {
				$this->error['company_id'] = $this->language->get('error_company_id');
			}

			if ($customer_group['tax_id_display'] && $customer_group['tax_id_required'] && empty($this->request->post['tax_id'])) {
				$this->error['tax_id'] = $this->language->get('error_tax_id');
			}
		}

		if ((mb_strlen($this->request->post['address_1'] ?? '', 'UTF-8') < 3) || (mb_strlen($this->request->post['address_1'] ?? '', 'UTF-8') > 128)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}

		if ((mb_strlen($this->request->post['city'] ?? '', 'UTF-8') < 2) || (mb_strlen($this->request->post['city'] ?? '', 'UTF-8') > 128)) {
			$this->error['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = !empty($this->request->post['country_id']) ? $this->model_localisation_country->getCountry((int)$this->request->post['country_id']) : null;

		if ($country_info) {
			if ($country_info['postcode_required'] && (mb_strlen($this->request->post['postcode'] ?? '', 'UTF-8') < 2) || (mb_strlen($this->request->post['postcode'] ?? '', 'UTF-8') > 10)) {
				$this->error['postcode'] = $this->language->get('error_postcode');
			}

			if ($customer_group && $customer_group['tax_id_display']) {
				$this->load->helper('vat');

				if ($this->config->get('config_vat') && $this->request->post['tax_id'] !== '' && (vat_validation($country_info['iso_code_2'], $this->request->post['tax_id']) === 'invalid')) {
					$this->error['tax_id'] = $this->language->get('error_vat');
				}
			}
		}

		if (!isset($this->request->post['country_id']) || $this->request->post['country_id'] === '') {
			$this->error['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] === '') {
			$this->error['zone'] = $this->language->get('error_zone');
		}

		if (!isset($this->request->post['check_shipping_address'])) {
			if ((mb_strlen($this->request->post['shipping_firstname'] ?? '', 'UTF-8') < 1) || (mb_strlen($this->request->post['shipping_firstname'] ?? '', 'UTF-8') > 32)) {
				$this->error['shipping_firstname'] = $this->language->get('error_firstname');
			}

			if ((mb_strlen($this->request->post['shipping_lastname'] ?? '', 'UTF-8') < 1) || (mb_strlen($this->request->post['shipping_lastname'] ?? '', 'UTF-8') > 32)) {
				$this->error['shipping_lastname'] = $this->language->get('error_lastname');
			}

			if ((mb_strlen($this->request->post['shipping_address_1'] ?? '', 'UTF-8') < 3) || (mb_strlen($this->request->post['shipping_address_1'] ?? '', 'UTF-8') > 128)) {
				$this->error['shipping_address_1'] = $this->language->get('error_address_1');
			}

			if ((mb_strlen($this->request->post['shipping_city'] ?? '', 'UTF-8') < 2) || (mb_strlen($this->request->post['shipping_city'] ?? '', 'UTF-8') > 128)) {
				$this->error['shipping_city'] = $this->language->get('error_city');
			}

			if (empty($this->request->post['shipping_country_id'])) {
				$this->error['shipping_country'] = $this->language->get('error_country');
			}

			if (!isset($this->request->post['shipping_zone_id']) || $this->request->post['shipping_zone_id'] == '') {
				$this->error['shipping_zone'] = $this->language->get('error_zone');
			}
		}

		if (!isset($this->session->data['shipping_method']) || empty($this->session->data['shipping_method'])) {
			$this->error['shipping_method'] = $this->language->get('error_shipping');
		}

		if (!isset($this->session->data['payment_method']) || empty($this->session->data['payment_method'])) {
			$this->error['payment_method'] = $this->language->get('error_payment');
		}

		if (!isset($this->request->post['agree']) && $this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			$this->error['agree'] = sprintf($this->language->get('error_agree'), $information_info ? $information_info['title'] : '');
		}

		return empty($this->error);
	}

	public function country() {
		$json = [];

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = [
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function shippingMethod() {
		$json = [];

		if (isset($this->request->post['shipping_method'])) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
		} else {
			unset($this->session->data['shipping_method']);
		}

		$json['code'] = $this->session->data['shipping_method']['title'] ?? '';

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function paymentMethod() {
		$json = [];

		if (isset($this->request->post['payment_method'])) {
			$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
		} else {
			unset($this->session->data['payment_method']);
		}

		$json['code'] = $this->session->data['payment_method']['title'];

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
