<?php
/**
 * Class ModelFraudMaxMind
 *
 * @package NivoCart
 */
class ModelFraudMaxMind extends Model {
	/**
	 * Functions Check, GetFraud
	 */
	public function check(array $data = []) {
		// Do not perform a fraud check if MaxMind is disabled or credentials are missing.
		if (!$this->config->get('maxmind_status') || !$this->config->get('maxmind_key') || !$this->config->get('maxmind_account_id')) {
			return $this->config->get('config_order_status_id');
		}

		$order_id = $data['order_id'];

		$existing = $this->getFraud((int)$order_id);

		if ($existing) {
			// Already screened — return the stored risk score decision.
			$risk_score = (float)$existing['risk_score'];

			if ($risk_score > $this->config->get('maxmind_score') && $this->config->get('maxmind_key')) {
				return $this->config->get('maxmind_order_status_id');
			}

			return $this->config->get('config_order_status_id');
		}

		// Build JSON request body for minFraud Score v2.
		$email_lower = strtolower(trim($data['email'] ?? ''));
		$email_hash = hash('sha256', $email_lower);
		$email_domain = substr(strrchr($data['email'] ?? '', '@'), 1);

		$request = [
			'device' => [
				'ip_address'      => $data['ip'],
				'user_agent'      => $data['user_agent'] ?? '',
				'accept_language' => $data['accept_language'] ?? '',
			],
			'event' => [
				'transaction_id' => (string)$order_id,
				'type'           => 'purchase',
			],
			'account' => [
				'user_id' => (string)($data['customer_id'] ?? 0),
			],
			'email' => [
				'address' => $email_hash,
				'domain'  => $email_domain,
			],
			'billing' => [
				'first_name'   => $data['payment_firstname'] ?? '',
				'last_name'    => $data['payment_lastname'] ?? '',
				'address'      => $data['payment_address_1'] ?? '',
				'city'         => $data['payment_city'] ?? '',
				'region'       => $data['payment_zone'] ?? '',
				'postal'       => $data['payment_postcode'] ?? '',
				'country'      => $data['payment_iso_code_2'] ?? '',
				'phone_number' => $data['telephone'] ?? '',
			],
			'order' => [
				'amount'   => (float)$this->currency->format($data['total'], $data['currency_code'], $data['currency_value'], false),
				'currency' => $data['currency_code'] ?? 'USD',
			],
			'payment' => [
				'processor' => $this->mapPaymentProcessor($data['payment_code'] ?? ''),
			],
		];

		// Shipping address (only if physical shipping is used).
		if (!empty($data['shipping_method'])) {
			$request['shipping'] = [
				'address' => $data['shipping_address_1'] ?? '',
				'city'    => $data['shipping_city'] ?? '',
				'region'  => $data['shipping_zone'] ?? '',
				'postal'  => $data['shipping_postcode'] ?? '',
				'country' => $data['shipping_iso_code_2'] ?? '',
			];
		}

		// Coupon code.
		if (!empty($data['coupon_code'])) {
			$request['order']['discount_code'] = $data['coupon_code'];
		}

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, 'https://minfraud.maxmind.com/minfraud/v2.0/score');
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request));
		curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
		curl_setopt($curl, CURLOPT_USERPWD, $this->config->get('maxmind_account_id') . ':' . $this->config->get('maxmind_key'));
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = curl_exec($curl);

		unset($curl);

		$risk_score = 0;

		if ($response !== false && $response !== '') {
			$json = json_decode($response);

			if ($json !== null) {
				// Helpers for safe nested property access.
				$g = static fn (?object $obj, string $prop): string =>
					($obj !== null && isset($obj->{$prop})) ? (string)$obj->{$prop} : '';
				$gf = static fn (?object $obj, string $prop): float =>
					($obj !== null && isset($obj->{$prop})) ? (float)$obj->{$prop} : 0.0;
				$gi = static fn (?object $obj, string $prop): int =>
					($obj !== null && isset($obj->{$prop})) ? (int)$obj->{$prop} : 0;
				$yn = static fn (?object $obj, string $prop): string =>
					($obj !== null && isset($obj->{$prop})) ? ($obj->{$prop} ? 'Yes' : 'No') : '';

				$ip = $json->ip_address ?? null;
				$ip_co = $ip->country ?? null;
				$ip_cont = $ip->continent ?? null;
				$ip_city = $ip->city ?? null;
				$ip_post = $ip->postal ?? null;
				$ip_loc = $ip->location ?? null;
				$ip_tr = $ip->traits ?? null;
				$ip_sub = $ip->subdivisions ?? [];
				$ip_sub0 = $ip_sub[0] ?? null;
				$bill = $json->billing_address ?? null;
				$ship = $json->shipping_address ?? null;
				$email = $json->email ?? null;
				$cc = $json->credit_card ?? null;
				$cc_iss = $cc->issuer ?? null;

				$risk_score = $gf($json, 'risk_score');

				// Collect any warnings into the error field.
				$warnings = [];

				if (!empty($json->warnings) && is_array($json->warnings)) {
					foreach ($json->warnings as $w) {
						if (isset($w->warning)) {
							$warnings[] = $w->warning;
						}
					}
				}
				$error_text = implode('; ', $warnings);

				$this->db->query("INSERT INTO `" . DB_PREFIX . "maxmind` SET
					order_id = '" . (int)$order_id . "',
					customer_id = '" . (int)($data['customer_id'] ?? 0) . "',
					country_match = '" . $this->db->escape($yn($bill, 'is_in_ip_country')) . "',
					country_code = '" . $this->db->escape($g($ip_co, 'code')) . "',
					high_risk_country = '" . $this->db->escape($yn($ip_co, 'is_high_risk')) . "',
					distance = '" . $gi($bill, 'distance_to_ip_location') . "',
					ip_region = '" . $this->db->escape($g($ip_sub0, 'iso_code')) . "',
					ip_city = '" . $this->db->escape($g($ip_city, 'name')) . "',
					ip_latitude = '" . $gf($ip_loc, 'latitude') . "',
					ip_longitude = '" . $gf($ip_loc, 'longitude') . "',
					ip_isp = '" . $this->db->escape($g($ip_tr, 'isp')) . "',
					ip_org = '" . $this->db->escape($g($ip_tr, 'organization')) . "',
					ip_asnum = '" . $gi($ip_tr, 'autonomous_system_number') . "',
					ip_user_type = '" . $this->db->escape($g($ip_tr, 'user_type')) . "',
					ip_country_confidence = '" . $this->db->escape($g($ip_co, 'confidence')) . "',
					ip_region_confidence = '" . $this->db->escape($g($ip_sub0, 'confidence')) . "',
					ip_city_confidence = '" . $this->db->escape($g($ip_city, 'confidence')) . "',
					ip_postal_confidence = '" . $this->db->escape($g($ip_post, 'confidence')) . "',
					ip_postal_code = '" . $this->db->escape($g($ip_post, 'code')) . "',
					ip_accuracy_radius = '" . $gi($ip_loc, 'accuracy_radius') . "',
					ip_net_speed_cell = '" . $this->db->escape($g($ip_tr, 'connection_type')) . "',
					ip_metro_code = '0',
					ip_area_code = '0',
					ip_time_zone = '" . $this->db->escape($g($ip_loc, 'time_zone')) . "',
					ip_region_name = '" . $this->db->escape($g($ip_sub0, 'name')) . "',
					ip_domain = '',
					ip_country_name = '" . $this->db->escape($g($ip_co, 'name')) . "',
					ip_continent_code = '" . $this->db->escape($g($ip_cont, 'code')) . "',
					ip_corporate_proxy = '" . $this->db->escape($yn($ip_tr, 'is_legitimate_proxy')) . "',
					anonymous_proxy = '" . $this->db->escape($yn($ip_tr, 'is_anonymous')) . "',
					proxy_score = '" . $gf($ip, 'risk') . "',
					is_trans_proxy = '',
					free_mail = '" . $this->db->escape($yn($email, 'is_free')) . "',
					carder_email = '" . $this->db->escape($yn($email, 'is_high_risk')) . "',
					high_risk_username = '',
					high_risk_password = '',
					bin_match = '" . $this->db->escape($yn($cc, 'is_issued_in_ip_country')) . "',
					bin_country = '" . $this->db->escape($g($cc, 'country')) . "',
					bin_name_match = '" . $this->db->escape($yn($cc_iss, 'matches_provided_name')) . "',
					bin_name = '" . $this->db->escape($g($cc_iss, 'name')) . "',
					bin_phone_match = '" . $this->db->escape($yn($cc_iss, 'matches_provided_phone_number')) . "',
					bin_phone = '" . $this->db->escape($g($cc_iss, 'phone_number')) . "',
					customer_phone_in_billing_location = '',
					ship_forward = '" . $this->db->escape($yn($ship, 'is_ship_forward')) . "',
					city_postal_match = '" . $this->db->escape($yn($bill, 'is_postal_in_city')) . "',
					ship_city_postal_match = '" . $this->db->escape($yn($ship, 'is_postal_in_city')) . "',
					`score` = '0',
					explanation = '',
					risk_score = '" . $risk_score . "',
					queries_remaining = '" . $gi($json, 'queries_remaining') . "',
					maxmind_id = '" . $this->db->escape($g($json, 'id')) . "',
					`error` = '" . $this->db->escape($error_text) . "',
					email_is_disposable = '" . $this->db->escape($yn($email, 'is_disposable')) . "',
					email_is_high_risk = '" . $this->db->escape($yn($email, 'is_high_risk')) . "',
					credit_card_brand = '" . $this->db->escape($g($cc, 'brand')) . "',
					credit_card_type = '" . $this->db->escape($g($cc, 'type')) . "',
					credit_card_is_prepaid = '" . $this->db->escape($yn($cc, 'is_prepaid')) . "',
					ship_is_high_risk = '" . $this->db->escape($yn($ship, 'is_high_risk')) . "',
					date_added = NOW()
				");
			}
		}

		if ($risk_score > $this->config->get('maxmind_score') && $this->config->get('maxmind_key')) {
			return $this->config->get('maxmind_order_status_id');
		}

		return $this->config->get('config_order_status_id');
	}

	public function getFraud(int $order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "maxmind` WHERE order_id = '" . (int)$order_id . "'");

		return $query->row;
	}

	/**
	 * Map NivoCart payment code to a minFraud v2 payment processor value.
	 */
	private function mapPaymentProcessor(string $code): string {
		$code = strtolower($code);

		$map = [
			'paypal'    => 'paypal',
			'stripe'    => 'stripe',
			'braintree' => 'braintree',
			'square'    => 'square',
			'authnet'   => 'authorizenet',
			'authorize' => 'authorizenet',
			'worldpay'  => 'worldpay',
			'sagepay'   => 'sagepay',
			'klarna'    => 'klarna',
			'amazon'    => 'amazon',
		];

		foreach ($map as $key => $processor) {
			if (str_contains($code, $key)) {
				return $processor;
			}
		}

		return 'other';
	}
}
