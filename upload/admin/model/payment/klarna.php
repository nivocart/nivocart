<?php
/**
 * Class ModelPaymentKlarna (Admin)
 *
 * Admin-side model for the Klarna Payments extension. Scope is
 * deliberately narrow: validating region credentials when the settings
 * form is saved. Session creation, order placement, and all checkout-time
 * logic live in catalog/model/payment/klarna.php — the two are kept fully
 * separate rather than sharing a library, so a change on one side can
 * never accidentally affect the other.
 *
 * @package NivoCart
 */
class ModelPaymentKlarna extends Model {
	/**
	 * Klarna Payments API base URLs per region, live and playground.
	 * Klarna issues one merchant account per region; there is no
	 * per-country host — the country only affects request payloads,
	 * not which host you call.
	 */
	private $hosts = [
		'eu' => [
			'live'       => 'https://api.klarna.com',
			'playground' => 'https://api.playground.klarna.com'
		],
		'na' => [
			'live'       => 'https://api-na.klarna.com',
			'playground' => 'https://api-na.playground.klarna.com'
		],
		'oc' => [
			'live'       => 'https://api-oc.klarna.com',
			'playground' => 'https://api-oc.playground.klarna.com'
		]
	];

	/**
	 * Tests a region's credentials by calling the Order Management list
	 * endpoint with a tight result limit. This only requires valid Basic
	 * Auth to succeed — it doesn't touch the Payments product, create
	 * anything, or require an existing order to exist.
	 *
	 * @param  string $region    'eu' | 'na' | 'oc'
	 * @param  string $username  Klarna API username for this region
	 * @param  string $password  Klarna API password for this region
	 * @param  string $server    'live' | 'playground'
	 * @return true|string       true on success, otherwise a human-readable error
	 */
	public function testCredentials(string $region, string $username, string $password, string $server) {
		if (!isset($this->hosts[$region][$server])) {
			return 'Unknown region/server combination: ' . $region . '/' . $server;
		}

		$base_url = $this->hosts[$region][$server];

		// Order management list endpoint, limited to 1 result — cheapest
		// possible authenticated call. A 200 here means the credentials
		// are valid for this region/server regardless of whether any
		// orders exist yet.
		$url = $base_url . '/ordermanagement/v1/orders?size=1';

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_TIMEOUT, 15);
		curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . $password);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json'
		]);

		$response = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($curl);
		$curl_errno = curl_errno($curl);

		unset($curl);

		if ($response === false) {
			return 'Network error (' . $curl_errno . '): ' . $curl_error;
		}

		// 200 = valid credentials, list returned (possibly empty).
		if ($http_code === 200) {
			return true;
		}

		// 401 = invalid username/password for this region/server.
		if ($http_code === 401) {
			return 'Invalid username or password for this region/server.';
		}

		// 403 = credentials valid but lack permission for this resource —
		// still proves the credentials themselves are accepted.
		if ($http_code === 403) {
			return true;
		}

		$body = json_decode($response, true);
		$detail = $body['error_messages'][0] ?? $body['title'] ?? ('HTTP ' . $http_code);

		return 'Unexpected response: ' . $detail;
	}
}
