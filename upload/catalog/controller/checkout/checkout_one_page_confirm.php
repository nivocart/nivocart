<?php
/**
 * Class ControllerCheckoutCheckoutOnePageConfirm
 *
 * @package NivoCart
 *
 * Confirm page for the One Page checkout.
 * Called after checkout_one_page validates and saves the order data to session,
 * then redirects here with ?payment=1. This controller builds totals, calls
 * addOrder(), renders the order summary and payment button, then hands off
 * to the payment gateway confirm() method which redirects to checkout/success.
 */
class ControllerCheckoutCheckoutOnePageConfirm extends Controller {
	/** Error array Placeholder */

	public function index() {
		$redirect = '';

		// --- Guards (cart may already be empty if revisiting, so check order_id first) ---
		if (!isset($this->session->data['order_id'])) {
			// No order in session yet — validate the cart is still usable
			if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
				$redirect = $this->url->link('checkout/cart', '', 'SSL');
			}

			// Validate minimum quantity requirements
			if (!$redirect) {
				$products = $this->cart->getProducts();

				foreach ($products as $product) {
					$product_total = 0;

					foreach ($products as $product_2) {
						if ($product_2['product_id'] === $product['product_id']) {
							$product_total += $product_2['quantity'];
						}
					}

					if ($product['minimum'] > $product_total) {
						$redirect = $this->url->link('checkout/cart', '', 'SSL');
						break;
					}

					// Validate minimum age
					if ($this->config->get('config_customer_dob') && ($product['age_minimum'] > 0)) {
						if (!$this->customer->isLogged() || !$this->customer->isSecure()) {
							$redirect = $this->url->link('account/login', '', 'SSL');
							break;
						}
					}
				}
			}
		}

		// Validate shipping method is set
		if (!$redirect && $this->cart->hasShipping()) {
			if (!isset($this->session->data['shipping_method'])) {
				$redirect = $this->url->link('checkout/checkout_one_page', '', 'SSL');
			}
		}

		// Validate payment method is set
		if (!$redirect && !isset($this->session->data['payment_method'])) {
			$redirect = $this->url->link('checkout/checkout_one_page', '', 'SSL');
		}

		if (!$redirect) {
			$this->language->load('checkout/checkout_one_page');
			$this->language->load('checkout/checkout');

			// --- Totals ---
			$total_data = [];
			$total = 0.0;
			$taxes = $this->cart->getTaxes();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('total');

			// Sort extensions by their configured sort_order
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

			// Sort the final total_data rows by sort_order
			usort($total_data, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);

			// --- Build order data array (only when order not already placed) ---
			$this->load->model('checkout/order');
			$this->load->model('localisation/country');
			$this->load->model('localisation/zone');

			if (!isset($this->session->data['order_id'])) {
				// Retrieve the validated customer/address data stored in session by checkout_one_page
				$order_session = isset($this->session->data['one_page_order']) ? $this->session->data['one_page_order'] : [];

				$data = [];

				$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
				$data['store_id'] = $this->config->get('config_store_id');
				$data['store_name'] = $this->config->get('config_name');
				$data['store_url'] = $data['store_id'] ? $this->config->get('config_url') : HTTP_SERVER;

				// Customer
				if ($this->customer->isLogged()) {
					$data['customer_id'] = $this->customer->getId();
					$data['customer_group_id'] = $this->customer->getCustomerGroupId();
					$data['firstname'] = $this->customer->getFirstName();
					$data['lastname'] = $this->customer->getLastName();
					$data['email'] = $this->customer->getEmail();
					$data['telephone'] = $this->customer->getTelephone();
					$data['gender'] = $this->customer->getGender();
					$data['date_of_birth'] = $this->customer->getDateOfBirth();
				} else {
					// Guest — pulled from session data stored by checkout_one_page
					$data['customer_id'] = 0;
					$data['customer_group_id'] = isset($order_session['customer_group_id']) ? $order_session['customer_group_id'] : $this->config->get('config_customer_group_id');
					$data['firstname'] = isset($order_session['firstname']) ? $order_session['firstname']     : '';
					$data['lastname'] = isset($order_session['lastname']) ? $order_session['lastname']      : '';
					$data['email'] = isset($order_session['email']) ? $order_session['email']         : '';
					$data['telephone'] = isset($order_session['telephone']) ? $order_session['telephone']     : '';
					$data['gender'] = isset($order_session['gender']) ? $order_session['gender']        : 1;
					$data['date_of_birth'] = isset($order_session['date_of_birth']) ? $order_session['date_of_birth'] : '0000-00-00';
				}

				// Payment address — from session
				$data['payment_firstname'] = isset($order_session['payment_firstname']) ? $order_session['payment_firstname'] : $data['firstname'];
				$data['payment_lastname'] = isset($order_session['payment_lastname']) ? $order_session['payment_lastname'] : $data['lastname'];
				$data['payment_company'] = isset($order_session['payment_company']) ? $order_session['payment_company'] : '';
				$data['payment_company_id'] = isset($order_session['payment_company_id']) ? $order_session['payment_company_id'] : '';
				$data['payment_tax_id'] = isset($order_session['payment_tax_id']) ? $order_session['payment_tax_id'] : '';
				$data['payment_address_1'] = isset($order_session['payment_address_1']) ? $order_session['payment_address_1'] : '';
				$data['payment_address_2'] = isset($order_session['payment_address_2']) ? $order_session['payment_address_2'] : '';
				$data['payment_city'] = isset($order_session['payment_city']) ? $order_session['payment_city'] : '';
				$data['payment_postcode'] = isset($order_session['payment_postcode']) ? $order_session['payment_postcode'] : '';
				$data['payment_zone'] = isset($order_session['payment_zone']) ? $order_session['payment_zone'] : '';
				$data['payment_zone_id'] = isset($order_session['payment_zone_id']) ? $order_session['payment_zone_id'] : '';
				$data['payment_country'] = isset($order_session['payment_country']) ? $order_session['payment_country'] : '';
				$data['payment_country_id'] = isset($order_session['payment_country_id']) ? $order_session['payment_country_id'] : '';
				$data['payment_address_format'] = '';

				// Payment method
				$data['payment_method'] = isset($this->session->data['payment_method']['title']) ? $this->session->data['payment_method']['title'] : '';
				$data['payment_code'] = isset($this->session->data['payment_method']['code']) ? $this->session->data['payment_method']['code'] : '';

				// Shipping address
				$data['shipping_firstname'] = isset($order_session['shipping_firstname']) ? $order_session['shipping_firstname'] : $data['firstname'];
				$data['shipping_lastname'] = isset($order_session['shipping_lastname']) ? $order_session['shipping_lastname'] : $data['lastname'];
				$data['shipping_company'] = isset($order_session['shipping_company']) ? $order_session['shipping_company'] : '';
				$data['shipping_address_1'] = isset($order_session['shipping_address_1']) ? $order_session['shipping_address_1'] : '';
				$data['shipping_address_2'] = isset($order_session['shipping_address_2']) ? $order_session['shipping_address_2'] : '';
				$data['shipping_city'] = isset($order_session['shipping_city']) ? $order_session['shipping_city'] : '';
				$data['shipping_postcode'] = isset($order_session['shipping_postcode']) ? $order_session['shipping_postcode'] : '';
				$data['shipping_zone'] = isset($order_session['shipping_zone']) ? $order_session['shipping_zone'] : '';
				$data['shipping_zone_id'] = isset($order_session['shipping_zone_id']) ? $order_session['shipping_zone_id'] : '';
				$data['shipping_country'] = isset($order_session['shipping_country']) ? $order_session['shipping_country'] : '';
				$data['shipping_country_id'] = isset($order_session['shipping_country_id']) ? $order_session['shipping_country_id'] : '';
				$data['shipping_address_format'] = '';

				// Shipping method
				$data['shipping_method'] = isset($this->session->data['shipping_method']['title']) ? $this->session->data['shipping_method']['title'] : '';
				$data['shipping_code'] = isset($this->session->data['shipping_method']['code']) ? $this->session->data['shipping_method']['code']  : '';

				// Products
				$product_data = [];

				foreach ($this->cart->getProducts() as $product) {
					$option_data = [];

					foreach ($product['option'] as $option) {
						$value = ($option['type'] !== 'file') ? $option['option_value'] : $this->encryption->decrypt($option['option_value']);

						$option_data[] = [
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $value,
							'type'                    => $option['type']
						];
					}

					$product_data[] = [
						'product_id' => $product['product_id'],
						'name'       => $product['name'],
						'model'      => $product['model'],
						'option'     => $option_data,
						'download'   => $product['download'],
						'quantity'   => $product['quantity'],
						'subtract'   => $product['subtract'],
						'price'      => $product['price'],
						'cost'       => $product['cost'],
						'total'      => $product['total'],
						'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
						'reward'     => $product['reward']
					];
				}

				// Gift vouchers
				$voucher_data = [];

				if (!empty($this->session->data['vouchers'])) {
					foreach ($this->session->data['vouchers'] as $voucher) {
						$voucher_data[] = [
							'description'      => $voucher['description'],
							'code'             => substr(md5(mt_rand()), 0, 10),
							'to_name'          => $voucher['to_name'],
							'to_email'         => $voucher['to_email'],
							'from_name'        => $voucher['from_name'],
							'from_email'       => $voucher['from_email'],
							'voucher_theme_id' => $voucher['voucher_theme_id'],
							'message'          => $voucher['message'],
							'amount'           => $voucher['amount']
						];
					}
				}

				$data['products'] = $product_data;
				$data['vouchers'] = $voucher_data;
				$data['totals'] = $total_data;
				$data['comment'] = isset($order_session['comment']) ? $order_session['comment'] : '';
				$data['total'] = $total;

				// Affiliate
				if (isset($this->request->cookie['tracking'])) {
					$this->load->model('affiliate/affiliate');

					$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
					$subtotal = $this->cart->getSubTotal();

					if ($affiliate_info) {
						$data['affiliate_id'] = $affiliate_info['affiliate_id'];
						$data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
					} else {
						$data['affiliate_id'] = 0;
						$data['commission'] = 0;
					}
				} else {
					$data['affiliate_id'] = 0;
					$data['commission'] = 0;
				}

				$data['language_id'] = $this->config->get('config_language_id');

				// Currency
				$this->load->model('localisation/currency');

				$currency_info = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));

				if ($currency_info) {
					$data['currency_id'] = $currency_info['currency_id'];
					$data['currency_code'] = $currency_info['code'];
					$data['currency_value'] = $currency_info['value'];
				} else {
					$data['currency_id'] = 0;
					$data['currency_code'] = $this->config->get('config_currency');
					$data['currency_value'] = 1.00000000;
				}

				// IP
				$data['ip'] = $this->request->server['REMOTE_ADDR'];
				$data['forwarded_ip'] = !empty($this->request->server['HTTP_X_FORWARDED_FOR']) ? $this->request->server['HTTP_X_FORWARDED_FOR'] : (!empty($this->request->server['HTTP_CLIENT_IP']) ? $this->request->server['HTTP_CLIENT_IP'] : '');
				$data['user_agent'] = isset($this->request->server['HTTP_USER_AGENT']) ? $this->request->server['HTTP_USER_AGENT'] : '';
				$data['accept_language'] = isset($this->request->server['HTTP_ACCEPT_LANGUAGE']) ? $this->request->server['HTTP_ACCEPT_LANGUAGE'] : '';

				// Save the order — cart is cleared inside addOrder()
				$this->model_checkout_order->addOrder($data);

				$this->session->data['order_id'] = $this->db->getLastId();
			}

			// --- Template display data ---

			// Tax breakdown columns
			if ($this->config->get('config_tax_breakdown')) {
				$this->data['tax_breakdown'] = true;
				$this->data['tax_colspan'] = 6;
			} else {
				$this->data['tax_breakdown'] = false;
				$this->data['tax_colspan'] = 4;
			}

			$this->data['column_name'] = $this->language->get('column_name');
			$this->data['column_model'] = $this->language->get('column_model');
			$this->data['column_quantity'] = $this->language->get('column_quantity');
			$this->data['column_price'] = $this->language->get('column_price');
			$this->data['column_tax_value'] = $this->language->get('column_tax_value');
			$this->data['column_tax_percent'] = $this->language->get('column_tax_percent');
			$this->data['column_total'] = $this->language->get('column_total');

			$this->data['text_recurring_item'] = $this->language->get('text_recurring_item');
			$this->data['text_payment_profile'] = $this->language->get('text_payment_profile');
			$this->data['button_confirm'] = $this->language->get('button_confirm');
			$this->data['text_wait'] = $this->language->get('text_wait');

			// Products for display
			// Note: cart is empty after addOrder() so we read from the saved order
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			$frequencies = [
				'day'        => $this->language->get('text_day'),
				'week'       => $this->language->get('text_week'),
				'semi_month' => $this->language->get('text_semi_month'),
				'month'      => $this->language->get('text_month'),
				'year'       => $this->language->get('text_year')
			];

			$this->data['products'] = [];

			foreach ($this->cart->getProducts() as $product) {
				$option_data = [];

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['option_value'];
					} else {
						$filename = $this->encryption->decrypt($option['option_value']);

						$value = substr($filename, 0, strrpos($filename, '.'));
					}

					$option_data[] = [
						'name'  => $option['name'],
						'value' => (mb_strlen($value, 'UTF-8') > 20) ? substr($value, 0, 20) . '..' : $value
					];
				}

				// Profile
				$profile_description = '';

				if ($product['recurring']) {
					if ($product['recurring_trial']) {
						$recurring_price = $this->currency->format($this->tax->calculate(($product['recurring_trial_price'] * $product['quantity']), $product['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));

						$profile_description = sprintf($this->language->get('text_trial_description'), $recurring_price, $product['recurring_trial_cycle'], $frequencies[$product['recurring_trial_frequency']], $product['recurring_trial_duration']) . ' ';
					}

					$recurring_price = $this->currency->format($this->tax->calculate(($product['recurring_price'] * $product['quantity']), $product['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));

					if ($product['recurring_duration']) {
						$profile_description .= sprintf($this->language->get('text_payment_description'), $recurring_price, $product['recurring_cycle'], $frequencies[$product['recurring_frequency']], $product['recurring_duration']);
					} else {
						$profile_description .= sprintf($this->language->get('text_payment_until_canceled_description'), $recurring_price, $product['recurring_cycle'], $frequencies[$product['recurring_frequency']], $product['recurring_duration']);
					}
				}

				$product_tax_value = ($this->tax->calculate(($product['price'] * $product['quantity']), $product['tax_class_id'], $this->config->get('config_tax')) - ($product['price'] * $product['quantity']));

				$this->data['products'][] = [
					'key'                 => $product['key'],
					'product_id'          => $product['product_id'],
					'name'                => $product['name'],
					'model'               => $product['model'],
					'option'              => $option_data,
					'quantity'            => $product['quantity'],
					'subtract'            => $product['subtract'],
					'price'               => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency')),
					'tax_value'           => $this->currency->format($product_tax_value, $this->config->get('config_currency')),
					'tax_percent'         => number_format((($product_tax_value * 100) / (($product['price'] > 0) ? ($product['price'] * $product['quantity']) : $product['quantity'])), 2, '.', ''),
					'age_minimum'         => ($product['age_minimum'] > 0) ? ' (' . $product['age_minimum'] . '+)' : '',
					'total'               => $this->currency->format($this->tax->calculate(($product['price'] * $product['quantity']), $product['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency')),
					'href'                => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'SSL'),
					'recurring'           => $product['recurring'],
					'profile_name'        => $product['profile_name'],
					'profile_description' => $profile_description
				];
			}

			// Vouchers for display
			$this->data['vouchers'] = [];

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$this->data['vouchers'][] = [
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $this->config->get('config_currency'))
					];
				}
			}

			$this->data['totals'] = $total_data;

			$this->data['payment'] = $this->getChild('payment/' . $this->session->data['payment_method']['code']);

		} else {
			$this->data['redirect'] = $redirect;
		}

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/checkout_one_page_confirm.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/checkout/checkout_one_page_confirm.tpl';
		} else {
			$this->template = 'default/template/checkout/checkout_one_page_confirm.tpl';
		}

		$this->response->setOutput($this->render());
	}
}
