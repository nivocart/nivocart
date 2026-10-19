<?php
/**
 * Library Class EbayBrowse
 *
 * Minimal client for the eBay Browse API, used by the Competitor Price report
 * to look up the lowest listed price for an exact EAN/GTIN on one eBay marketplace.
 *
 * Authentication: application access token (client credentials grant), valid for
 * about two hours. The caller is expected to store the token and pass it back in,
 * so it is not requested again for every product.
 *
 * @package NivoCart
 */
final class EbayBrowse {
	private const API_URL = 'https://api.ebay.com';
	private const SCOPE = 'https://api.ebay.com/oauth/api_scope';

	/**
	 * Marketplace (eBay site) used for each supported country code.
	 *
	 * @var array<string, array{marketplace: string, currency: string}>
	 */
	private const MARKETPLACES = [
		'GB' => ['marketplace' => 'EBAY_GB', 'currency' => 'GBP'],
		'IE' => ['marketplace' => 'EBAY_IE', 'currency' => 'EUR'],
		'DE' => ['marketplace' => 'EBAY_DE', 'currency' => 'EUR'],
		'FR' => ['marketplace' => 'EBAY_FR', 'currency' => 'EUR'],
		'IT' => ['marketplace' => 'EBAY_IT', 'currency' => 'EUR'],
		'ES' => ['marketplace' => 'EBAY_ES', 'currency' => 'EUR'],
		'NL' => ['marketplace' => 'EBAY_NL', 'currency' => 'EUR'],
		'US' => ['marketplace' => 'EBAY_US', 'currency' => 'USD'],
		'CA' => ['marketplace' => 'EBAY_CA', 'currency' => 'CAD'],
		'AU' => ['marketplace' => 'EBAY_AU', 'currency' => 'AUD']
	];

	private string $client_id;
	private string $client_secret;
	private string $access_token;
	private int $token_expires;

	/**
	 * @param string $client_id      eBay application ID (Client ID).
	 * @param string $client_secret  eBay Cert ID (Client Secret).
	 * @param string $access_token   Previously issued token, if any.
	 * @param int    $token_expires  Unix time at which that token expires.
	 */
	public function __construct(string $client_id, string $client_secret, string $access_token = '', int $token_expires = 0) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->access_token = $access_token;
		$this->token_expires = $token_expires;
	}

	/**
	 * Country codes this client can search.
	 *
	 * @return array<string, array{marketplace: string, currency: string}>
	 */
	public static function getMarketplaces(): array {
		return self::MARKETPLACES;
	}

	/**
	 * True when the stored token is missing or about to expire (5 minute margin).
	 */
	public function tokenNeedsRefresh(): bool {
		return $this->access_token === '' || ($this->token_expires - 300) <= time();
	}

	/**
	 * Request a new application access token.
	 *
	 * @return array{access_token: string, expires_at: int}
	 *
	 * @throws \Exception when eBay refuses the credentials or cannot be reached.
	 */
	public function requestToken(): array {
		if ($this->client_id === '' || $this->client_secret === '') {
			throw new \Exception('eBay: Client ID and Client Secret are required.');
		}

		$response = $this->request(
			'POST',
			self::API_URL . '/identity/v1/oauth2/token',
			[
				'Content-Type: application/x-www-form-urlencoded',
				'Authorization: Basic ' . base64_encode($this->client_id . ':' . $this->client_secret)
			],
			http_build_query(['grant_type' => 'client_credentials', 'scope' => self::SCOPE])
		);

		if (empty($response['body']['access_token'])) {
			$message = $response['body']['error_description'] ?? ('HTTP ' . $response['code']);

			throw new \Exception('eBay: could not obtain an access token. ' . $message);
		}

		$this->access_token = (string)$response['body']['access_token'];
		$this->token_expires = time() + (int)($response['body']['expires_in'] ?? 7200);

		return ['access_token' => $this->access_token, 'expires_at' => $this->token_expires];
	}

	/**
	 * Find the cheapest new, fixed-price listing for an exact GTIN, located in the given country.
	 *
	 * @param  string        $gtin            EAN / UPC / GTIN digits.
	 * @param  string        $country_code    Key of MARKETPLACES (e.g. GB).
	 * @param  bool          $include_postage Compare item price plus postage when the postage is known.
	 * @param  array<string> $ignore_sellers  Lower-case seller user names to leave out (your own shop).
	 * @return array{found: bool, offer_count: int, item_price?: float, postage?: ?float, currency?: string, seller?: string, url?: string}
	 *
	 * @throws \Exception on API or network errors.
	 */
	public function searchByGtin(string $gtin, string $country_code, bool $include_postage = true, array $ignore_sellers = []): array {
		if (!isset(self::MARKETPLACES[$country_code])) {
			throw new \Exception('eBay: unsupported country ' . $country_code . '.');
		}

		if ($this->tokenNeedsRefresh()) {
			$this->requestToken();
		}

		$params = [
			'gtin'   => $gtin,
			'limit'  => 50,
			'sort'   => 'price',
			'filter' => 'conditions:{NEW},itemLocationCountry:' . $country_code
		];

		$headers = [
			'X-EBAY-C-MARKETPLACE-ID: ' . self::MARKETPLACES[$country_code]['marketplace'],
			'X-EBAY-C-ENDUSERCTX: contextualLocation=country%3D' . $country_code
		];

		$url = self::API_URL . '/buy/browse/v1/item_summary/search?';

		$response = $this->authorisedGet($url . http_build_query($params, '', '&', PHP_QUERY_RFC3986), $headers);

		// Cheapest-first ordering and the delivery-location header are optional extras:
		// if eBay rejects them, repeat the lookup with the plain request.
		if ($response['code'] === 400) {
			unset($params['sort']);

			$response = $this->authorisedGet($url . http_build_query($params, '', '&', PHP_QUERY_RFC3986), [$headers[0]]);
		}

		// eBay answers 404 when nothing matches the GTIN.
		if ($response['code'] === 404) {
			return ['found' => false, 'offer_count' => 0];
		}

		if ($response['code'] !== 200) {
			$message = $response['body']['errors'][0]['message'] ?? ('HTTP ' . $response['code']);

			throw new \Exception('eBay search failed: ' . $message);
		}

		$best = null;
		$best_total = 0.0;
		$count = 0;

		foreach (($response['body']['itemSummaries'] ?? []) as $item) {
			$options = $item['buyingOptions'] ?? [];

			// Auctions have no fixed price to compare with.
			if (!in_array('FIXED_PRICE', $options, true)) {
				continue;
			}

			if (empty($item['price']['value']) || empty($item['price']['currency'])) {
				continue;
			}

			$seller = (string)($item['seller']['username'] ?? '');

			if ($seller !== '' && in_array(strtolower($seller), $ignore_sellers, true)) {
				continue;
			}

			$price = (float)$item['price']['value'];
			$postage = $this->lowestPostage($item);
			$total = $price + (($include_postage && $postage !== null) ? $postage : 0.0);

			$count++;

			if ($best === null || $total < $best_total) {
				$best_total = $total;
				$best = [
					'item_price' => $price,
					'postage'    => $postage,
					'currency'   => (string)$item['price']['currency'],
					'seller'     => $seller,
					'url'        => (string)($item['itemWebUrl'] ?? '')
				];
			}
		}

		if ($best === null) {
			return ['found' => false, 'offer_count' => 0];
		}

		return ['found' => true, 'offer_count' => $count] + $best;
	}

	/**
	 * Lowest postage cost offered on a listing, or null when eBay does not state one.
	 */
	private function lowestPostage(array $item): ?float {
		$lowest = null;

		foreach (($item['shippingOptions'] ?? []) as $option) {
			if (!isset($option['shippingCost']['value'])) {
				continue;
			}

			$cost = (float)$option['shippingCost']['value'];

			if ($lowest === null || $cost < $lowest) {
				$lowest = $cost;
			}
		}

		return $lowest;
	}

	/**
	 * GET with the bearer token; refreshes the token once on 401 and backs off on rate limits.
	 *
	 * @return array{code: int, body: array}
	 */
	private function authorisedGet(string $url, array $headers): array {
		$refreshed = false;
		$retries = 0;

		while (true) {
			$response = $this->request('GET', $url, array_merge(['Authorization: Bearer ' . $this->access_token, 'Accept: application/json'], $headers));

			if ($response['code'] === 401 && !$refreshed) {
				$this->requestToken();
				$refreshed = true;

				continue;
			}

			if (in_array($response['code'], [429, 500, 502, 503, 504], true) && $retries < 3) {
				$retries++;

				sleep(2 * $retries);

				continue;
			}

			return $response;
		}
	}

	/**
	 * Current token, so the caller can store it for the next request.
	 *
	 * @return array{access_token: string, expires_at: int}
	 */
	public function getToken(): array {
		return ['access_token' => $this->access_token, 'expires_at' => $this->token_expires];
	}

	/**
	 * @return array{code: int, body: array}
	 *
	 * @throws \Exception when the request cannot be sent.
	 */
	private function request(string $method, string $url, array $headers, string $body = ''): array {
		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		if ($method === 'POST') {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
		}

		$result = curl_exec($curl);
		$code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$error = curl_error($curl);

		unset($curl);

		if ($result === false) {
			throw new \Exception('eBay request failed: ' . $error);
		}

		$decoded = json_decode((string)$result, true);

		return ['code' => $code, 'body' => is_array($decoded) ? $decoded : []];
	}
}
