<?php
/**
 * Class ModelPaymentKlarna (Catalog)
 *
 * Catalog-side model for Klarna Payments. One class, two responsibilities
 * that both have to live here because checkout.php's loading convention
 * requires it:
 *
 *   1. getMethod() — called by checkout.php to decide whether Klarna
 *      appears in the payment method list for a given address/total.
 *   2. Session/order lifecycle — region/country resolution against the
 *      `klarna` settings group, session creation and updates for the
 *      embedded widget, and order creation from an authorization token
 *      once the customer has authorized payment client-side.
 *
 * Deliberately separate from admin/model/payment/klarna.php — see
 * NivoCart convention notes: system/vendor/ is reserved for genuinely
 * shared core libraries (e.g. Stripe), not per-context payment models.
 *
 * @package NivoCart
 */
class ModelPaymentKlarna extends Model {
	/**
	 * Klarna Payments API base URLs per region, live and playground.
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
	 * Country → region map. Must stay in sync with the country lists in
	 * admin/controller/payment/klarna.php. Kept as a flat lookup here
	 * (rather than re-deriving from settings) so resolution doesn't
	 * depend on a country having been explicitly configured yet — it's
	 * still useful to know "this country belongs to region X" even
	 * before that region/country pair has been enabled.
	 */
	private $country_region_map = [
		// EU
		'AT' => 'eu', 'BE' => 'eu', 'DE' => 'eu', 'DK' => 'eu', 'FI' => 'eu',
		'FR' => 'eu', 'GR' => 'eu', 'IE' => 'eu', 'IT' => 'eu', 'NL' => 'eu',
		'NO' => 'eu', 'PL' => 'eu', 'PT' => 'eu', 'ES' => 'eu', 'SE' => 'eu',
		'CH' => 'eu', 'GB' => 'eu',
		// NA
		'US' => 'na', 'CA' => 'na',
		// OC
		'AU' => 'oc', 'NZ' => 'oc',
	];

	/**
	 * ISO 3166-1 alpha-2 → locale used in the Klarna session request.
	 * Falls back to 'en-{country}' for anything not explicitly listed.
	 */
	private $locale_map = [
		'AT' => 'de-AT', 'BE' => 'nl-BE', 'DE' => 'de-DE', 'DK' => 'da-DK',
		'FI' => 'fi-FI', 'FR' => 'fr-FR', 'GR' => 'el-GR', 'IE' => 'en-IE',
		'IT' => 'it-IT', 'NL' => 'nl-NL', 'NO' => 'nb-NO', 'PL' => 'pl-PL',
		'PT' => 'pt-PT', 'ES' => 'es-ES', 'SE' => 'sv-SE', 'CH' => 'de-CH',
		'GB' => 'en-GB', 'US' => 'en-US', 'CA' => 'en-CA', 'AU' => 'en-AU',
		'NZ' => 'en-NZ',
	];

	/**
	 * ISO 3166-1 alpha-3 → alpha-2 fallback, covering only the countries
	 * we actually support. getMethod() is called from checkout.php with
	 * an $address array that reliably carries iso_code_3 (carried over
	 * from the legacy Klarna integration's usage) but may or may not
	 * also carry iso_code_2 depending on how that address record was
	 * built. Every other entry point in this model (session creation,
	 * order creation) is called later in checkout once the full order
	 * record exists and iso_code_2 is confirmed available there — this
	 * map exists purely so getMethod() doesn't have to care which one
	 * it was handed.
	 */
	private $iso3_to_iso2_map = [
		'AUT' => 'AT', 'BEL' => 'BE', 'DEU' => 'DE', 'DNK' => 'DK', 'FIN' => 'FI',
		'FRA' => 'FR', 'GRC' => 'GR', 'IRL' => 'IE', 'ITA' => 'IT', 'NLD' => 'NL',
		'NOR' => 'NO', 'POL' => 'PL', 'PRT' => 'PT', 'ESP' => 'ES', 'SWE' => 'SE',
		'CHE' => 'CH', 'GBR' => 'GB',
		'USA' => 'US', 'CAN' => 'CA',
		'AUS' => 'AU', 'NZL' => 'NZ',
	];

	// =========================================================================
	// Payment method listing (called by checkout.php)
	// =========================================================================

	/**
	 * Returns the Klarna payment method entry for the payment method
	 * radio list, or an empty array if Klarna shouldn't be offered for
	 * this address/total combination.
	 *
	 * This replaces the old XML-RPC era getMethod(), which built its
	 * availability check and title around financing tiers (pclasses)
	 * that no longer exist under the Payments API. Under Payments,
	 * available pay-now/pay-later/financing options are resolved
	 * dynamically *inside the widget itself* once rendered — this
	 * method only needs to decide whether Klarna is even worth showing
	 * as an option, not which financing plan applies.
	 *
	 * @param  array $address  Must include country_id, zone_id, and at
	 *                         least one of iso_code_2 / iso_code_3.
	 *                         (Older call sites only guaranteed
	 *                         iso_code_3 — both are accepted here.)
	 * @param  float $total    Order total, store currency
	 * @return array Empty if unavailable, otherwise a method entry with
	 *               code/title/sort_order.
	 */
	public function getMethod(array $address, float $total): array {
		$this->language->load('payment/klarna');

		$country_code = $address['iso_code_2'] ?? $address['iso_code_3'] ?? '';

		if ($country_code === '') {
			return [];
		}

		$country_code = $this->normalizeCountryCode($country_code);

		if (!$this->isAvailable($country_code)) {
			return [];
		}

		$region_code = $this->getRegionForCountry($country_code);
		$klarna = $this->config->get('klarna');
		$region = $klarna[$region_code];
		$country = $region['countries'][$country_code];

		// Geo zone restriction — 0/empty means "all zones".
		if (!empty($country['geo_zone_id'])) {
			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE geo_zone_id = '" . (int)$country['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

			if (!$zone_query->num_rows) {
				return [];
			}
		}

		// Klarna Payments has no fixed minimum/maximum order total at
		// the integration level (unlike the old Invoice product's
		// total/total_max settings) — availability by amount, if any,
		// is enforced by Klarna themselves per payment method category
		// once the widget loads, not here.

		return [
			'code'       => 'klarna',
			'title'      => $this->language->get('text_title'),
			'sort_order' => $country['sort_order'] ?? 0,
		];
	}

	// =========================================================================
	// Region / country resolution
	// =========================================================================

	/**
	 * Normalizes a country code to ISO-2, accepting either ISO-2 or
	 * ISO-3 input. Returns the input uppercased if it's neither a known
	 * ISO-2 key nor a mappable ISO-3 code — callers should treat an
	 * unrecognized result as "not available" rather than guess further.
	 */
	private function normalizeCountryCode(string $country_code): string {
		$country_code = strtoupper(trim($country_code));

		if (strlen($country_code) === 3 && isset($this->iso3_to_iso2_map[$country_code])) {
			return $this->iso3_to_iso2_map[$country_code];
		}

		return $country_code;
	}

	/**
	 * Resolves a country code (ISO-2 or ISO-3) to its region code, or
	 * null if the country isn't mapped to any Klarna region at all.
	 */
	public function getRegionForCountry(string $country_code): ?string {
		$country_code = $this->normalizeCountryCode($country_code);

		return $this->country_region_map[$country_code] ?? null;
	}

	/**
	 * Returns true if Klarna is actually available for the given country
	 * right now — region enabled, country enabled within that region,
	 * and credentials present. Accepts ISO-2 or ISO-3.
	 */
	public function isAvailable(string $country_code): bool {
		$country_code = $this->normalizeCountryCode($country_code);
		$region_code = $this->getRegionForCountry($country_code);

		if ($region_code === null) {
			return false;
		}

		$klarna = $this->config->get('klarna');
		$region = $klarna[$region_code] ?? null;

		if (empty($region['status']) || empty($region['username']) || empty($region['password'])) {
			return false;
		}

		$country = $region['countries'][$country_code] ?? null;

		return !empty($country['status']);
	}

	/**
	 * Returns the full settings bundle needed to talk to Klarna for a
	 * given country: region code, host, credentials, locale. Returns
	 * null if not available — callers must check before use. Accepts
	 * ISO-2 or ISO-3.
	 */
	private function resolveContext(string $country_code): ?array {
		if (!$this->isAvailable($country_code)) {
			return null;
		}

		$country_code = $this->normalizeCountryCode($country_code);
		$region_code = $this->country_region_map[$country_code];

		$klarna = $this->config->get('klarna');
		$region = $klarna[$region_code];

		$server = $region['server'] ?? 'playground';

		if (!isset($this->hosts[$region_code][$server])) {
			return null;
		}

		return [
			'region_code'        => $region_code,
			'base_url'           => $this->hosts[$region_code][$server],
			'username'           => $region['username'],
			'password'           => $region['password'],
			'locale'             => $this->locale_map[$country_code] ?? ('en-' . $country_code),
			'pending_status_id'  => (int)($region['pending_status_id'] ?? 0),
			'accepted_status_id' => (int)($region['accepted_status_id'] ?? 0),
		];
	}

	// =========================================================================
	// Session creation / update
	// =========================================================================

	/**
	 * Creates a new Klarna Payments session for the current cart.
	 *
	 * @param  string $country_code  Payment country, 2-letter ISO code
	 * @param  string $currency_code Active store currency code
	 * @param  float  $total         Order total (in store currency, decimal)
	 * @return array  ['error' => string] on failure, otherwise the session data
	 *                including client_token, session_id, payment_method_categories
	 */
	public function createSession(string $country_code, string $currency_code, float $total): array {
		$context = $this->resolveContext($country_code);

		if ($context === null) {
			return ['error' => 'Klarna is not available for this country.'];
		}

		$body = $this->buildSessionBody($country_code, $currency_code, $total, $context['locale']);

		$result = $this->request($context, 'POST', '/payments/v1/sessions', $body);

		if (isset($result['error'])) {
			return $result;
		}

		// Stash session_id + region against the order session so
		// updateSession()/createOrder() don't need it passed back in
		// from the client.
		$this->session->data['klarna_session_id'] = $result['session_id'] ?? null;
		$this->session->data['klarna_region'] = $context['region_code'];

		return [
			'client_token'              => $result['client_token'] ?? null,
			'session_id'                => $result['session_id'] ?? null,
			'payment_method_categories' => $result['payment_method_categories'] ?? [],
		];
	}

	/**
	 * Re-syncs an existing session after a cart-affecting change
	 * (coupon applied, shipping method changed, etc). Klarna requires
	 * the session amount to match what will actually be authorized.
	 */
	public function updateSession(string $country_code, string $currency_code, float $total): array {
		$session_id = $this->session->data['klarna_session_id'] ?? null;

		if (!$session_id) {
			// No existing session to update — caller should create one instead.
			return $this->createSession($country_code, $currency_code, $total);
		}

		$context = $this->resolveContext($country_code);

		if ($context === null) {
			return ['error' => 'Klarna is not available for this country.'];
		}

		$body = $this->buildSessionBody($country_code, $currency_code, $total, $context['locale']);

		$result = $this->request($context, 'POST', '/payments/v1/sessions/' . $session_id, $body);

		if (isset($result['error'])) {
			return $result;
		}

		// Session update responses don't include a fresh client_token —
		// the widget keeps using the one it already has.
		return ['session_id' => $session_id];
	}

	/**
	 * Builds the order_lines + order amount payload shared by session
	 * create and update calls. Mirrors the PP Standard product-loop
	 * pattern: one line per cart product, plus a single remainder line
	 * for shipping/surcharges if the order total doesn't match the
	 * product subtotal.
	 *
	 * Tax handling: NivoCart prices are tax-inclusive and tax is
	 * computed as a flat amount per rate (see ModelTotalTax::getTotal()),
	 * not per-product. There is no reliable per-line tax_rate available,
	 * so order_lines carry tax_rate/total_tax_amount = 0 (honest, since
	 * the unit price already includes whatever tax applies) and the
	 * order-level order_tax_amount instead reflects the real total pulled
	 * from $this->cart->getTaxes() — the same source checkout_confirm.php
	 * already aggregates into the order totals. This avoids fabricating
	 * per-product tax rates we don't actually have.
	 */
	private function buildSessionBody(string $country_code, string $currency_code, float $total, string $locale): array {
		$order_lines = [];
		$subtotal_minor = 0;

		foreach ($this->cart->getProducts() as $product) {
			$unit_price_minor = $this->toMinorUnits($product['price'], $currency_code);
			$quantity = (int)$product['quantity'];
			$line_total_minor = $unit_price_minor * $quantity;

			$subtotal_minor += $line_total_minor;

			$order_lines[] = [
				'type'             => 'physical',
				'name'             => $product['name'],
				'reference'        => $product['model'],
				'quantity'         => $quantity,
				'unit_price'       => $unit_price_minor,
				'total_amount'     => $line_total_minor,
				'tax_rate'         => 0,
				'total_tax_amount' => 0,
			];
		}

		$total_minor = $this->toMinorUnits($total, $currency_code);
		$remainder_minor = $total_minor - $subtotal_minor;

		if ($remainder_minor !== 0) {
			// Positive: shipping/handling/tax on top of products.
			// Negative: a discount/voucher reducing the order below
			// the product subtotal — Klarna order_lines support
			// negative amounts for discount lines.
			$order_lines[] = [
				'type'             => $remainder_minor > 0 ? 'shipping_fee' : 'discount',
				'name'             => $remainder_minor > 0 ? 'Shipping & charges' : 'Discount',
				'quantity'         => 1,
				'unit_price'       => $remainder_minor,
				'total_amount'     => $remainder_minor,
				'tax_rate'         => 0,
				'total_tax_amount' => 0,
			];
		}

		// Real tax total, summed across rates the same way
		// checkout_confirm.php already aggregates them — not fabricated
		// per-line, but accurate at the order level.
		$tax_total = array_sum($this->cart->getTaxes());
		$order_tax_amount = $this->toMinorUnits($tax_total, $currency_code);

		return [
			'purchase_country'  => $this->normalizeCountryCode($country_code),
			'purchase_currency' => strtoupper($currency_code),
			'locale'            => $locale,
			'order_amount'      => $total_minor,
			'order_tax_amount'  => $order_tax_amount,
			'order_lines'       => $order_lines,
		];
	}

	// =========================================================================
	// Order creation from authorization
	// =========================================================================

	/**
	 * Converts a client-side authorization token into a real Klarna
	 * order. Called from checkout_confirm.php's _confirmInteractivePayment()
	 * after addOrder() has already created the NivoCart-side order at
	 * Pending status.
	 *
	 * @param  string $authorization_token
	 * @param  string $country_code
	 * @param  string $currency_code
	 * @param  float  $total
	 * @return array  ['error' => string] on failure, otherwise
	 *                ['klarna_order_id' => ..., 'accepted_status_id' => ...,
	 *                 'pending_status_id' => ..., 'fraud_status' => ...]
	 */
	public function createOrder(string $authorization_token, string $country_code, string $currency_code, float $total): array {
		$context = $this->resolveContext($country_code);

		if ($context === null) {
			return ['error' => 'Klarna is not available for this country.'];
		}

		$body = $this->buildSessionBody($country_code, $currency_code, $total, $context['locale']);

		// Register the push notification URL so Klarna can notify us when
		// a PENDING fraud review resolves. The URL is per-order (not a
		// global dashboard setting like Stripe) — Klarna calls it with
		// {"order_id": "..."} and catalog/webhooks/klarna.php handles it.
		// HTTP_SERVER gives us the store root; webhooks sit two levels
		// below in catalog/webhooks/.
		$body['merchant_urls'] = [
			'push' => HTTP_SERVER . 'catalog/webhooks/klarna.php'
		];

		// merchant_reference1 is added separately via updateOrderReference()
		// after addOrder() allocates the NivoCart order_id — the two calls
		// can't be combined because the order_id doesn't exist yet here.

		$result = $this->request(
			$context,
			'POST',
			'/payments/v1/authorizations/' . rawurlencode($authorization_token) . '/order',
			$body
		);

		if (isset($result['error'])) {
			return $result;
		}

		return [
			'klarna_order_id'    => $result['order_id'] ?? null,
			'fraud_status'       => $result['fraud_status'] ?? null,
			'accepted_status_id' => $context['accepted_status_id'],
			'pending_status_id'  => $context['pending_status_id'],
			'region_code'        => $context['region_code'],
		];
	}

	/**
	 * Attaches the NivoCart order_id to an already-created Klarna order
	 * as merchant_reference1, for cross-referencing in Klarna's
	 * dashboard and in push notification payloads. Best-effort — a
	 * failure here doesn't roll back the order, just gets logged by the
	 * caller if desired.
	 */
	public function updateOrderReference(string $klarna_order_id, string $country_code, int $order_id): array {
		$context = $this->resolveContext($country_code);

		if ($context === null) {
			return ['error' => 'Klarna is not available for this country.'];
		}

		return $this->request(
			$context,
			'PATCH',
			'/ordermanagement/v1/orders/' . rawurlencode($klarna_order_id) . '/merchant-references',
			['merchant_reference1' => (string)$order_id]
		);
	}

	// =========================================================================
	// HTTP / formatting helpers
	// =========================================================================

	/**
	 * Converts a decimal store-currency amount into Klarna's required
	 * minor-unit integer (e.g. 19.99 GBP -> 1999). Uses the same
	 * currency formatting NivoCart already relies on elsewhere so
	 * rounding behaviour stays consistent with the rest of the cart.
	 */
	private function toMinorUnits(float $amount, string $currency_code): int {
		$formatted = $this->currency->format($amount, $currency_code, false, false);

		return (int)round(((float)$formatted) * 100);
	}

	/**
	 * Raw cURL wrapper for all Klarna REST calls. Basic Auth, JSON
	 * in/out. Returns the decoded response body on 2xx, or
	 * ['error' => ...] on any failure (network, non-2xx, bad JSON).
	 */
	private function request(array $context, string $method, string $path, ?array $body = null): array {
		$url = $context['base_url'] . $path;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		curl_setopt($curl, CURLOPT_USERPWD, $context['username'] . ':' . $context['password']);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json'
		]);

		if ($body !== null) {
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
		}

		$response = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($curl);
		$curl_errno = curl_errno($curl);

		curl_close($curl);

		if ($response === false) {
			$log = new Log('klarna.log');
			$log->write('Network error calling ' . $method . ' ' . $path . ' (' . $curl_errno . '): ' . $curl_error);

			return ['error' => 'Network error contacting Klarna. Please try again.'];
		}

		$decoded = ($response !== '') ? json_decode($response, true) : [];

		if ($http_code < 200 || $http_code >= 300) {
			$detail = $decoded['error_messages'][0] ?? $decoded['title'] ?? ('HTTP ' . $http_code);

			$log = new Log('klarna.log');
			$log->write('Klarna API error calling ' . $method . ' ' . $path . ': ' . $detail . ' | raw: ' . substr($response, 0, 500));

			return ['error' => $detail];
		}

		return is_array($decoded) ? $decoded : [];
	}
}
