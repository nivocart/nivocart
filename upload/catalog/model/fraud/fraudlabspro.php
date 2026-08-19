<?php
/**
 * Class ModelFraudFraudLabsPro
 *
 * @package NivoCart
 */
class ModelFraudFraudLabsPro extends Model {
	/**
	 * Functions Check, Get
	 */
	public function check(array $data = []) {
		$this->load->model('setting/extension');

		// Do not perform a fraud check if FraudLabs Pro is disabled or API key is not provided.
		if (!$this->config->get('fraudlabspro_status') || !$this->config->get('fraudlabspro_key')) {
			return;
		}

		// Overwrite client IP if simulate IP is provided.
		if (filter_var($this->config->get('fraudlabspro_simulate_ip'), FILTER_VALIDATE_IP)) {
			$ip = $this->config->get('fraudlabspro_simulate_ip');
		} else {
			$ip = $data['ip'];
		}

		$fraud_status_id = $this->config->get('config_order_status_id');

		$fraud_info = $this->getFraud($data['order_id']);

		if (empty($fraud_info)) {
			$request = [
				'key'           => $this->config->get('fraudlabspro_key'),
				'ip'            => $ip,
				'first_name'    => $data['payment_firstname'] ?? '',
				'last_name'     => $data['payment_lastname'] ?? '',
				'bill_addr'     => $data['payment_address_1'] ?? '',
				'bill_city'     => $data['payment_city'],
				'bill_state'    => $data['payment_zone'],
				'bill_country'  => $data['payment_iso_code_2'],
				'bill_zip_code' => $data['payment_postcode'],
				'user_phone'    => $data['telephone'],
				'email_hash'    => hash('sha256', strtolower(trim($data['email']))),
				'amount'        => $this->currency->format($data['total'], $data['currency_code'], $data['currency_value'], false),
				'quantity'      => 1,
				'currency'      => $data['currency_code'],
				'user_order_id' => $data['order_id'],
				'format'        => 'json',
			];

			// Shipping address (only if physical shipping is used)
			if ($data['shipping_method']) {
				$request['ship_addr'] = $data['shipping_address_1'];
				$request['ship_city'] = $data['shipping_city'];
				$request['ship_state'] = $data['shipping_zone'];
				$request['ship_zip_code'] = $data['shipping_postcode'];
				$request['ship_country'] = $data['shipping_iso_code_2'];
			}

			// Map NivoCart payment code to FraudLabs Pro payment_mode
			if (!empty($data['payment_code'])) {
				$request['payment_mode'] = $this->mapPaymentMode($data['payment_code']);
				$request['payment_gateway'] = $data['payment_code'];
			}

			// Coupon code
			if (!empty($data['coupon_code'])) {
				$request['coupon_code'] = $data['coupon_code'];
			}

			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, 'https://api.fraudlabspro.com/v2/order/screen');
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($request));
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT, 30);

			$response = curl_exec($curl);

			unset($curl);

			if ($response !== false && ($json = json_decode($response)) !== null) {
				// Helper: safely read a nested object property as string
				$g = static fn (?object $obj, string $prop): string =>
					($obj !== null && isset($obj->{$prop})) ? (string)$obj->{$prop} : '';

				// Helper: convert a JSON boolean property to 'Y' / 'N'
				$yn = static fn (?object $obj, string $prop): string =>
					($obj !== null && isset($obj->{$prop})) ? ($obj->{$prop} ? 'Y' : 'N') : '';

				$ip_geo = $json->ip_geolocation ?? null;
				$bill = $json->billing_address ?? null;
				$ship = $json->shipping_address ?? null;
				$email = $json->email_address ?? null;
				$phone = $json->phone_number ?? null;
				$card = $json->credit_card ?? null;

				$this->db->query("INSERT INTO `" . DB_PREFIX . "fraudlabspro` SET
					order_id = '" . (int)$data['order_id'] . "',
					ip_address = '" . $this->db->escape($ip) . "',
					ip_country = '" . $this->db->escape($g($ip_geo, 'country_code')) . "',
					ip_country_name = '" . $this->db->escape($g($ip_geo, 'country_name')) . "',
					ip_continent = '" . $this->db->escape($g($ip_geo, 'continent')) . "',
					ip_region = '" . $this->db->escape($g($ip_geo, 'region')) . "',
					ip_city = '" . $this->db->escape($g($ip_geo, 'city')) . "',
					ip_latitude = '" . $this->db->escape($g($ip_geo, 'latitude')) . "',
					ip_longitude = '" . $this->db->escape($g($ip_geo, 'longitude')) . "',
					ip_timezone = '" . $this->db->escape($g($ip_geo, 'timezone')) . "',
					ip_isp_name = '" . $this->db->escape($g($ip_geo, 'isp_name')) . "',
					is_proxy_ip_address = '" . $this->db->escape($yn($ip_geo, 'is_proxy')) . "',
					is_ip_blacklist = '" . $this->db->escape($yn($ip_geo, 'is_in_blacklist')) . "',
					is_country_match = '" . $this->db->escape($yn($bill, 'is_ip_country_match')) . "',
					distance_in_km = '" . (int)($bill->ip_distance_in_km ?? 0) . "',
					distance_in_mile = '" . (int)($bill->ip_distance_in_mile ?? 0) . "',
					is_address_ship_forward = '" . $this->db->escape($yn($ship, 'is_address_ship_forward')) . "',
					is_bill_ship_city_match = '" . $this->db->escape($yn($ship, 'is_bill_city_match')) . "',
					is_bill_ship_state_match = '" . $this->db->escape($yn($ship, 'is_bill_state_match')) . "',
					is_bill_ship_country_match = '" . $this->db->escape($yn($ship, 'is_bill_country_match')) . "',
					is_bill_ship_postal_match = '" . $this->db->escape($yn($ship, 'is_bill_postcode_match')) . "',
					ship_export_controlled = '" . $this->db->escape($yn($ship, 'is_export_controlled_country')) . "',
					ship_in_blacklist = '" . $this->db->escape($yn($ship, 'is_in_blacklist')) . "',
					is_free_email = '" . $this->db->escape($yn($email, 'is_free')) . "',
					email_is_disposable = '" . $this->db->escape($yn($email, 'is_disposable')) . "',
					email_domain_exists = '" . $this->db->escape($yn($email, 'is_domain_exist')) . "',
					is_new_domain_name = '" . $this->db->escape($yn($email, 'is_new_domain_name')) . "',
					is_email_blacklist = '" . $this->db->escape($yn($email, 'is_in_blacklist')) . "',
					phone_is_disposable = '" . $this->db->escape($yn($phone, 'is_disposable')) . "',
					phone_in_blacklist = '" . $this->db->escape($yn($phone, 'is_in_blacklist')) . "',
					card_brand = '" . $this->db->escape($g($card, 'card_brand')) . "',
					card_type = '" . $this->db->escape($g($card, 'card_type')) . "',
					is_bin_found = '" . $this->db->escape($yn($card, 'is_bin_exist')) . "',
					is_bin_prepaid = '" . $this->db->escape($yn($card, 'is_prepaid')) . "',
					is_credit_card_blacklist = '" . $this->db->escape($yn($card, 'is_in_blacklist')) . "',
					fraudlabspro_score = '" . (int)($json->fraudlabspro_score ?? 0) . "',
					fraudlabspro_status = '" . $this->db->escape($json->fraudlabspro_status ?? '') . "',
					fraudlabspro_id = '" . $this->db->escape($json->fraudlabspro_id ?? '') . "',
					fraudlabspro_credits = '" . (int)($json->remaining_credits ?? 0) . "',
					api_key = '" . $this->db->escape($this->config->get('fraudlabspro_key')) . "',
					is_high_risk_country = '',
					ip_elevation = '',
					ip_domain = '',
					ip_mobile_mnc = '',
					ip_mobile_mcc = '',
					ip_mobile_brand = '',
					ip_netspeed = '',
					ip_usage_type = '',
					is_bin_country_match = '',
					is_bin_name_match = '',
					is_bin_phone_match = '',
					is_device_blacklist = '',
					is_user_blacklist = '',
					fraudlabspro_distribution = '',
					fraudlabspro_error = '',
					fraudlabspro_message = ''
				");

				$risk_score = (int)($json->fraudlabspro_score ?? 0);

				// Abort status assignment on missing status (API error condition)
				if (empty($json->fraudlabspro_status ?? '')) {
					return $fraud_status_id;
				}

				if ($risk_score > $this->config->get('fraudlabspro_score')) {
					$fraud_status_id = $this->config->get('fraudlabspro_order_status_id');
				}

				$flp_status = $json->fraudlabspro_status ?? '';

				if ($flp_status === 'REVIEW') {
					$fraud_status_id = $this->config->get('fraudlabspro_review_status_id');
				}

				if ($flp_status === 'APPROVE') {
					$fraud_status_id = $this->config->get('fraudlabspro_approve_status_id');
				}

				if ($flp_status === 'REJECT') {
					$fraud_status_id = $this->config->get('fraudlabspro_reject_status_id');
				}
			}

		} else {
			$fraud_status_id = $data['order_status_id'];
		}

		return $fraud_status_id;
	}

	public function getFraud(int $order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "fraudlabspro` WHERE order_id = '" . (int)$order_id . "'");

		return $query->row;
	}

	/**
	 * Map NivoCart payment code to FraudLabs Pro payment_mode value.
	 */
	private function mapPaymentMode(string $code): string {
		$code = strtolower($code);

		$map = [
			'paypal'   => 'paypal',
			'cod'      => 'cod',
			'bank'     => 'bankdeposit',
			'cheque'   => 'bankdeposit',
			'wire'     => 'wired',
			'crypto'   => 'crypto',
			'bitcoin'  => 'crypto',
			'giftcard' => 'giftcard',
			'gift'     => 'giftcard',
		];

		foreach ($map as $key => $mode) {
			if (str_contains($code, $key)) {
				return $mode;
			}
		}

		return 'creditcard';
	}
}
