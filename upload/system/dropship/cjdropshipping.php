<?php
/**
 * CJDropshipping Dropship Adapter
 *
 * Handles all communication with the CJDropshipping API v2:
 *   - Authentication (apiKey → accessToken, refresh)
 *   - Product listing, detail, and variant queries
 *   - Inventory (stock) queries
 *   - Order creation (two-step: createOrderV2 → addCart → addCartConfirm)
 *   - Order status and tracking queries
 *   - Webhook registration
 *
 * Intended location: system/dropship/cjdropshipping.php
 *
 * Usage (from a model or controller):
 *   $cj = new CJDropshipping($channel);
 *   $products = $cj->getProducts(['categoryId' => 'xxx', 'countryCode' => 'GB']);
 *
 * @package  NivoCart
 * @version  1.0.0
 */

class CJDropshipping {
	// -----------------------------------------------------------------
	// CJ API base URL — all endpoints share this root
	// -----------------------------------------------------------------
	private const BASE_URL = 'https://developers.cjdropshipping.com/api2.0/v1';
	private const TIMEOUT = 30;
	private const CONNECT_TIMEOUT = 10;

	// -----------------------------------------------------------------
	// Channel config — populated from nc_dropship_channel row
	// -----------------------------------------------------------------
	private string $apiKey;         // CJ API Key (obtained from CJ dashboard → Apps → API)
	private string $accessToken;
	private string $tokenExpiresAt;
	private string $webhookSecret;  // Shared secret registered with CJ webhooks

	// =================================================================
	// Constructor
	// =================================================================

	/**
	 * @param array $channel  A row from nc_dropship_channel.
	 *                        Expected keys: consumer_key (API key), access_token,
	 *                        token_expires_at, webhook_secret.
	 *                        Note: CJ uses a single apiKey for auth — consumer_key
	 *                        stores it; secret_key is unused by CJ.
	 */
	public function __construct(array $channel) {
		if (empty($channel['consumer_key'])) {
			throw new \Exception('CJDropshipping: API key (consumer_key) is required.');
		}

		$this->apiKey = $channel['consumer_key'];
		$this->accessToken = $channel['access_token'] ?? '';
		$this->tokenExpiresAt = $channel['token_expires_at'] ?? '';
		$this->webhookSecret = $channel['webhook_secret'] ?? '';
	}

	// =================================================================
	// PUBLIC — AUTHENTICATION
	// =================================================================

	/**
	 * Request a new access token from CJ using the stored apiKey.
	 * CJ caches tokens for 24 hours — repeated calls within that window
	 * return the same token. Token lifespan is 180 days.
	 *
	 * @return array ['access_token' => string, 'expires_at' => string]
	 * @throws \Exception
	 */
	public function requestToken(): array {
		$response = $this->post(self::BASE_URL . '/authentication/getAccessToken', [
			'apiKey' => $this->apiKey
		], false); // no auth header on this call

		if (empty($response['data']['accessToken'])) {
			throw new \Exception('CJDropshipping: Failed to obtain access token. ' . ($response['message'] ?? ''));
		}

		$this->accessToken = $response['data']['accessToken'];
		$this->tokenExpiresAt = $response['data']['accessTokenExpiryDate'] ?? '';

		return [
			'access_token' => $this->accessToken,
			'expires_at'   => $this->tokenExpiresAt
		];
	}

	/**
	 * Refresh an existing access token using the refresh token.
	 * Call this when the access token has expired but the refresh token
	 * (also 180 days) is still valid.
	 *
	 * @param  string $refreshToken  The refresh_token stored in nc_dropship_channel.secret_key.
	 * @return array  ['access_token' => string, 'expires_at' => string, 'refresh_token' => string]
	 * @throws \Exception
	 */
	public function refreshToken(string $refreshToken): array {
		$response = $this->post(self::BASE_URL . '/authentication/refreshAccessToken', [
			'refreshToken' => $refreshToken
		], false);

		if (empty($response['data']['accessToken'])) {
			throw new \Exception('CJDropshipping: Failed to refresh access token. ' . ($response['message'] ?? ''));
		}

		$this->accessToken = $response['data']['accessToken'];
		$this->tokenExpiresAt = $response['data']['accessTokenExpiryDate'] ?? '';

		return [
			'access_token'  => $this->accessToken,
			'expires_at'    => $this->tokenExpiresAt,
			'refresh_token' => $response['data']['refreshToken'] ?? $refreshToken
		];
	}

	/**
	 * Returns true if the stored access token is missing or has expired.
	 * Refresh 5 minutes early to avoid edge-case expiry mid-request.
	 */
	public function tokenNeedsRefresh(): bool {
		if (empty($this->accessToken)) {
			return true;
		}

		// If we have a token but no expiry date, treat it as valid rather than
		// triggering an infinite re-auth loop — CJ does not always return
		// accessTokenExpiryDate in the authentication response.
		if (empty($this->tokenExpiresAt)) {
			return false;
		}

		return (strtotime($this->tokenExpiresAt) - 300) <= time();
	}

	// =================================================================
	// PUBLIC — DIAGNOSTICS
	// =================================================================

	/**
	 * Test the connection and return diagnostic info.
	 * Calls requestToken() explicitly then hits /product/list with pageSize=1.
	 * Returns an array suitable for logging.
	 */
	public function testConnection(): array {
		$info = [];

		// Step 1: force a fresh token
		try {
			$this->accessToken = '';
			$this->tokenExpiresAt = '';
			$token = $this->requestToken();
			$info['token_prefix'] = substr($token['access_token'], 0, 20) . '...';
			$info['token_expires'] = $token['expires_at'] ?: '(not returned by CJ)';
			$info['token_raw'] = $token['access_token'];
			$info['token_expires_raw'] = $token['expires_at'];
			$info['auth'] = 'OK';
		} catch (\Exception $e) {
			$info['auth'] = 'FAILED: ' . $e->getMessage();
			return $info;
		}

		// Step 2: try the product list endpoint
		try {
			$this->get(self::BASE_URL . '/product/list', ['pageNum' => 1, 'pageSize' => 1]);
			$info['product_api'] = 'OK';
		} catch (\Exception $e) {
			$info['product_api'] = 'FAILED: ' . $e->getMessage();
		}

		return $info;
	}

	// =================================================================
	// PUBLIC — PRODUCTS
	// =================================================================

	/**
	 * Get a page of products from CJ, filtered by the provided criteria.
	 * Use countryCode='GB' to filter to UK-warehoused stock.
	 *
	 * Key params:
	 *   categoryId  string  — CJ third-level category ID
	 *   countryCode string  — e.g. 'GB', 'CN', 'US'
	 *   pageNum     int     — 1-based page number
	 *   pageSize    int     — max 200
	 *   keyWord     string  — product name/SKU search
	 *   minPrice    float
	 *   maxPrice    float
	 *
	 * @param  array $params  Filter/pagination parameters.
	 * @return array          ['pageNum', 'pageSize', 'total', 'list' => [...]]
	 * @throws \Exception
	 */
	public function getProducts(array $params = []): array {
		$defaults = [
			'pageNum'  => 1,
			'pageSize' => 100,
		];

		$query = array_merge($defaults, $params);

		$response = $this->get(self::BASE_URL . '/product/list', $query);

		if (!isset($response['data']['list'])) {
			throw new \Exception('CJDropshipping: Unexpected response from product/list.');
		}

		return $response['data'];
	}

	/**
	 * Get products from the seller's CJ "My Products" shop list.
	 * These are products manually added to the CJ seller account via the CJ dashboard
	 * (Products → My Products → Browse & Add). Only products in this list can be
	 * ordered through CJ; using this endpoint avoids the 410 "Shop Product not exists"
	 * error that occurs when trying to import from the general catalog.
	 *
	 * @param  array $params  Optional: pageNum, pageSize, productNameEn (keyword search), productSku
	 * @return array          ['pageNum', 'pageSize', 'total', 'list' => [...]]
	 * @throws \Exception
	 */
	/**
	 * Get products from the seller's "My Products" list (products manually
	 * added to the CJ seller account via the CJ dashboard).
	 *
	 * Endpoint: GET /product/myProduct/query
	 *
	 * @param  array $params  Optional filters:
	 *                        keyword     (string) — search by SKU, SPU, or name
	 *                        categoryId  (string)
	 *                        pageNum     (int)    — default 1
	 *                        pageSize    (int)    — default 20
	 * @return array          Pagination + 'list' of products
	 * @throws \Exception
	 */
	public function getMyProducts(array $params = []): array {
		$defaults = [
			'pageNum'  => 1,
			'pageSize' => 20,
		];

		$query = array_merge($defaults, $params);

		$response = $this->get(self::BASE_URL . '/product/myProduct/query', $query);

		if (!isset($response['data'])) {
			throw new \Exception('CJDropshipping: Unexpected response from /product/myProduct/query. Response: ' . json_encode($response));
		}

		// API returns products in data.content (not data.list like the general catalog)
		if (!isset($response['data']['content'])) {
			$response['data']['content'] = [];
		}

		return $response['data'];
	}

	/**
	 * Get full product detail including all variants, images, and dimensions.
	 * Pass pid or productSku — at least one is required.
	 *
	 * @param  string $pid         CJ product ID (preferred).
	 * @param  string $productSku  CJ product SPU (alternative).
	 * @param  string $countryCode Filter variants to those with inventory in this country.
	 * @return array               Full product detail object from CJ.
	 * @throws \Exception
	 */
	public function getProductDetail(string $pid = '', string $productSku = '', string $countryCode = ''): array {
		if (empty($pid) && empty($productSku)) {
			throw new \Exception('CJDropshipping: pid or productSku is required for getProductDetail.');
		}

		$params = [];

		// Only send countryCode if specified — passing 'GB' filters out CN-warehoused
		// variants, returning an empty variants array for most products.
		if ($countryCode !== '') {
			$params['countryCode'] = $countryCode;
		}

		if ($pid) {
			$params['pid'] = $pid;
		} else {
			$params['productSku'] = $productSku;
		}

		$response = $this->get(self::BASE_URL . '/product/query', $params);

		if (empty($response['data'])) {
			throw new \Exception('CJDropshipping: No product found for pid=' . $pid . ' sku=' . $productSku);
		}

		return $response['data'];
	}

	/**
	 * Get all variants for a product.
	 *
	 * @param  string $pid         CJ product ID.
	 * @param  string $countryCode Only return variants with stock in this country.
	 * @return array               List of variant objects.
	 * @throws \Exception
	 */
	public function getVariants(string $pid, string $countryCode = 'GB'): array {
		$response = $this->get(self::BASE_URL . '/product/variant/query', [
			'pid'         => $pid,
			'countryCode' => $countryCode
		]);

		if (!isset($response['data']) || !is_array($response['data'])) {
			throw new \Exception('CJDropshipping: Unexpected response from product/variant/query for pid=' . $pid);
		}

		return $response['data'];
	}

	/**
	 * Get CJ product categories (three-level hierarchy).
	 * Use to map CJ category IDs to NivoCart categories at import time.
	 *
	 * @return array  List of first-level category objects, each containing nested lists.
	 * @throws \Exception
	 */
	public function getCategories(): array {
		$response = $this->get(self::BASE_URL . '/product/getCategory', []);

		if (!isset($response['data']) || !is_array($response['data'])) {
			throw new \Exception('CJDropshipping: Unexpected response from product/getCategory.');
		}

		return $response['data'];
	}

	/**
	 * Get all available CJ global warehouses.
	 * Use to display warehouse options in admin and to set fromCountryCode on orders.
	 *
	 * @return array  List of warehouse objects with countryCode, areaEn, areaId.
	 * @throws \Exception
	 */
	public function getWarehouses(): array {
		$response = $this->get(self::BASE_URL . '/product/globalWarehouseList', []);

		if (!isset($response['data']) || !is_array($response['data'])) {
			throw new \Exception('CJDropshipping: Unexpected response from product/globalWarehouseList.');
		}

		return $response['data'];
	}

	// =================================================================
	// PUBLIC — INVENTORY
	// =================================================================

	/**
	 * Query stock level for a specific variant by its vid.
	 * Returns inventory broken down by warehouse/country.
	 *
	 * @param  string $vid  CJ variant ID.
	 * @return array        List of inventory objects per warehouse.
	 * @throws \Exception
	 */
	public function getInventoryByVid(string $vid): array {
		$response = $this->get(self::BASE_URL . '/product/stock/queryByVid', [
			'vid' => $vid
		]);

		if (!isset($response['data']) || !is_array($response['data'])) {
			throw new \Exception('CJDropshipping: Unexpected response from stock/queryByVid for vid=' . $vid);
		}

		return $response['data'];
	}

	/**
	 * Query stock level for a variant by SKU.
	 * Useful for batch sync when vid is not known.
	 *
	 * @param  string $sku  CJ variant SKU or SPU.
	 * @return array        List of inventory objects per warehouse.
	 * @throws \Exception
	 */
	public function getInventoryBySku(string $sku): array {
		$response = $this->get(self::BASE_URL . '/product/stock/queryBySku', [
			'sku' => $sku
		]);

		if (!isset($response['data']) || !is_array($response['data'])) {
			throw new \Exception('CJDropshipping: Unexpected response from stock/queryBySku for sku=' . $sku);
		}

		return $response['data'];
	}

	// =================================================================
	// PUBLIC — ORDERS
	// =================================================================

	// =================================================================
	// PUBLIC — SHOP / CONNECTIONS
	// =================================================================

	/**
	 * Create a product connection between a CJ product/variant and a NivoCart product/variant.
	 *
	 * CJ requires this call so that:
	 *   1. The NivoCart product_id is registered against the CJ product for order fulfillment.
	 *   2. Inventory data becomes visible via the API for connected products.
	 *
	 * In Phase 1 (one NivoCart product per CJ variant) this is called once per variant,
	 * with a single-entry variantList.
	 *
	 * Required fields per CJ docs (shop.html#_3-2-create-product-connection-post):
	 *   cjProductId        — CJ parent product ID (pid)
	 *   platformProductId  — NivoCart product_id as a string
	 *   variantList        — array of {cjVariantId, platformVariantId}
	 *   defaultArea        — warehouse area integer (1 = CN/Global, 2 = US)
	 *   logistics          — CJ logistics service name
	 *
	 * @param  string $cjProductId        CJ parent product ID (pid).
	 * @param  string $platformProductId  NivoCart product_id as a string.
	 * @param  array  $variantList        Each entry: ['cjVariantId' => vid, 'platformVariantId' => product_id_string].
	 * @param  int    $defaultArea        CJ warehouse area (1 = CN global default).
	 * @param  string $logistics          CJ logistics service name (e.g. 'CJPacket Ordinary').
	 * @param  bool   $ignoreInventory    Pass ignoreCheckInventory=1 to bypass US warehouse check.
	 * @return array                      CJ response data (may be empty on success).
	 * @throws \Exception
	 */
	public function createProductConnection(string $cjProductId, string $platformProductId, array $variantList, int $defaultArea = 1, string $logistics = 'CJPacket Ordinary', bool $ignoreInventory = true): array {
		if (empty($cjProductId) || empty($platformProductId) || empty($variantList)) {
			throw new \Exception('CJDropshipping createProductConnection: cjProductId, platformProductId, and variantList are required.');
		}

		$payload = [
			'cjProductId'       => $cjProductId,
			'platformProductId' => $platformProductId,
			'variantList'       => $variantList,
			'defaultArea'       => $defaultArea,
			'logistics'         => $logistics,
		];

		if ($ignoreInventory) {
			$payload['ignoreCheckInventory'] = 1;
		}

		$response = $this->post(self::BASE_URL . '/product/conn/connection', $payload);

		// CJ returns result:true on success; data may be null/empty
		if (empty($response['result'])) {
			throw new \Exception(
				'CJDropshipping createProductConnection failed for pid=' . $cjProductId
				. ': ' . ($response['message'] ?? 'Unknown error')
			);
		}

		return $response['data'] ?? [];
	}

	// =================================================================
	// PUBLIC — ORDERS
	// =================================================================

	/**
	 * Get available logistics options for a destination and product.
	 * Call this before createOrder to pick the right logisticName.
	 *
	 * @param  string $vid              CJ variant ID.
	 * @param  string $countryCode      Destination country code (e.g. 'GB').
	 * @param  string $fromCountryCode  Shipping origin country code (e.g. 'CN', 'GB').
	 * @return array                    List of available logistics options.
	 * @throws \Exception
	 */
	public function getLogisticsOptions(string $vid, string $countryCode, string $fromCountryCode = 'CN'): array {
		$response = $this->post(self::BASE_URL . '/logistic/freightCalculate', [
			'vid'              => $vid,
			'endCountryCode'   => $countryCode,
			'startCountryCode' => $fromCountryCode,
			'quantity'         => 1
		]);

		if (!isset($response['data']) || !is_array($response['data'])) {
			throw new \Exception('CJDropshipping: Unexpected response from logistic/freightCalculate.');
		}

		return $response['data'];
	}

	/**
	 * Create an order on CJ (Step 1 of the two-step flow).
	 * Uses payType=3 (create order only, no payment) — the model then calls
	 * addCart() and addCartConfirm() to complete payment from CJ balance.
	 *
	 * @param  array  $order         Row from nc_order.
	 * @param  array  $items         Array of items, each containing:
	 *                               'vid' (CJ variant ID), 'quantity', 'order_product_id'
	 * @param  string $logisticName  CJ logistics service name (from getLogisticsOptions).
	 * @param  string $fromCountryCode  Source warehouse country code.
	 * @return array                 CJ response data including orderId.
	 * @throws \Exception
	 */
	public function createOrder(array $order, array $items, string $logisticName, string $fromCountryCode = 'CN'): array {
		if (empty($items)) {
			throw new \Exception('CJDropshipping: Cannot dispatch an order with no items.');
		}

		$products = [];

		foreach ($items as $item) {
			if (empty($item['vid'])) {
				throw new \Exception('CJDropshipping: Missing CJ vid on order item.');
			}

			$products[] = [
				'vid'             => $item['vid'],
				'quantity'        => (int)($item['quantity'] ?? 1),
				'storeLineItemId' => (string)($item['order_product_id'] ?? '')
			];
		}

		$payload = [
			'orderNumber'          => (string)$order['order_id'],
			'shippingCountryCode'  => $this->resolveCountryCode($order['shipping_country']),
			'shippingCountry'      => $order['shipping_country'],
			'shippingProvince'     => $order['shipping_zone'] ?? '',
			'shippingCity'         => $order['shipping_city'],
			'shippingZip'          => $order['shipping_postcode'],
			'shippingCustomerName' => trim($order['shipping_firstname'] . ' ' . $order['shipping_lastname']),
			'shippingAddress'      => $order['shipping_address_1'],
			'shippingAddress2'     => $order['shipping_address_2'] ?? '',
			'shippingPhone'        => $order['telephone'],
			'email'                => $order['email'],
			'remark'               => $order['comment'] ?? '',
			'logisticName'         => $logisticName,
			'fromCountryCode'      => $fromCountryCode,
			'platform'             => 'api',
			'payType'              => 3, // create order only — payment handled separately via CJ balance
			'products'             => $products
		];

		$response = $this->post(self::BASE_URL . '/shopping/order/createOrderV2', $payload);

		if (empty($response['data']['orderId'])) {
			throw new \Exception('CJDropshipping createOrder failed: ' . ($response['message'] ?? 'Unknown error'));
		}

		return $response['data'];
	}

	/**
	 * Add a created CJ order to the cart (Step 2 of the payment flow).
	 * Only needed when paying from CJ balance (payType=2 flow).
	 *
	 * @param  array $cjOrderIds  List of CJ orderId strings from createOrder().
	 * @return array              CJ response data.
	 * @throws \Exception
	 */
	public function addToCart(array $cjOrderIds): array {
		$response = $this->post(self::BASE_URL . '/shopping/order/addCart', [
			'cjOrderIdList' => $cjOrderIds,
		]);

		if (empty($response['success'])) {
			throw new \Exception('CJDropshipping addToCart failed: ' . ($response['message'] ?? 'Unknown error'));
		}

		return $response['data'] ?? [];
	}

	/**
	 * Confirm the cart and trigger payment from CJ balance (Step 3).
	 *
	 * @param  array $cjOrderIds  List of CJ orderId strings.
	 * @return array              CJ response data.
	 * @throws \Exception
	 */
	public function confirmCart(array $cjOrderIds): array {
		$response = $this->post(self::BASE_URL . '/shopping/order/addCartConfirm', [
			'cjOrderIdList' => $cjOrderIds,
		]);

		if (empty($response['success'])) {
			throw new \Exception('CJDropshipping confirmCart failed: ' . ($response['message'] ?? 'Unknown error'));
		}

		return $response['data'] ?? [];
	}

	/**
	 * Query the status and tracking of one or more orders.
	 *
	 * @param  array $orderIds  List of CJ orderId strings.
	 * @return array            List of order detail objects.
	 * @throws \Exception
	 */
	public function getOrderStatus(array $orderIds): array {
		if (empty($orderIds)) {
			throw new \Exception('CJDropshipping: No order IDs provided to getOrderStatus.');
		}

		$response = $this->get(self::BASE_URL . '/shopping/order/list', [
			'orderIds' => implode(',', $orderIds),
			'pageSize' => count($orderIds),
		]);

		if (!isset($response['data']['list'])) {
			throw new \Exception('CJDropshipping: Unexpected response from order/list.');
		}

		return $response['data']['list'];
	}

	/**
	 * Get full detail for a single order including tracking number.
	 *
	 * @param  string $orderId  CJ orderId.
	 * @return array            Order detail object.
	 * @throws \Exception
	 */
	public function getOrderDetail(string $orderId): array {
		$response = $this->get(self::BASE_URL . '/shopping/order/getOrderDetail', [
			'orderId' => $orderId,
		]);

		if (empty($response['data'])) {
			throw new \Exception('CJDropshipping: Order not found: ' . $orderId);
		}

		return $response['data'];
	}

	// =================================================================
	// PUBLIC — WEBHOOKS
	// =================================================================

	/**
	 * Register webhook callback URLs with CJ.
	 * CJ supports four event types: product, stock, order, logistics.
	 * All four can point to the same URL — the payload identifies the type.
	 *
	 * @param  string $stockUrl     URL for stock update webhooks.
	 * @param  string $orderUrl     URL for order status webhooks.
	 * @param  string $logisticsUrl URL for logistics/tracking webhooks.
	 * @return array                CJ response.
	 * @throws \Exception
	 */
	public function registerWebhooks(string $stockUrl, string $orderUrl, string $logisticsUrl): array {
		$response = $this->post(self::BASE_URL . '/webhook/set', [
			'stock'     => ['type' => 'ENABLE', 'callbackUrls' => [$stockUrl]],
			'order'     => ['type' => 'ENABLE', 'callbackUrls' => [$orderUrl]],
			'logistics' => ['type' => 'ENABLE', 'callbackUrls' => [$logisticsUrl]],
		]);

		if (empty($response['result'])) {
			throw new \Exception('CJDropshipping registerWebhooks failed: ' . ($response['message'] ?? 'Unknown error'));
		}

		// CJ returns data:true (boolean) on success rather than an array — normalise it.
		$data = $response['data'] ?? [];
		return is_array($data) ? $data : [];
	}

	/**
	 * Validate an incoming CJ webhook request using the shared secret.
	 * CJ does not use JWT — it sends the webhook_secret as a plain header
	 * (X-CJ-Sign or similar — exact header confirmed at registration time).
	 * This method verifies the secret and decodes the payload.
	 *
	 * @param  string $rawBody    Raw POST body.
	 * @param  string $signHeader Value of the CJ signature header.
	 * @return array              Decoded webhook payload.
	 * @throws \Exception         If secret mismatch or body is malformed.
	 */
	public function validateWebhook(string $rawBody, string $signHeader): array {
		if (!empty($this->webhookSecret)) {
			// CJ signs with HMAC-SHA256 of the raw body using the shared secret
			$expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);

			if (!hash_equals($expected, strtolower($signHeader))) {
				throw new \Exception('CJDropshipping: Webhook signature verification failed.');
			}
		}

		$payload = json_decode($rawBody, true);

		if (!is_array($payload)) {
			throw new \Exception('CJDropshipping: Failed to decode webhook payload.');
		}

		return $payload;
	}

	// =================================================================
	// PUBLIC — DIAGNOSTICS
	// =================================================================

	/**
	 * Make a raw GET request to any CJ API path and return the full decoded response.
	 * Used by the probeMyProducts diagnostic to discover which endpoint exposes
	 * the seller's "My Products" list without hard-coding assumptions.
	 *
	 * @param  string $path    Path relative to BASE_URL, e.g. 'product/conn/connectionList'
	 * @param  array  $params  Query parameters.
	 * @return array           Full decoded CJ response (code, result, data, message, etc.)
	 * @throws \Exception
	 */
	public function rawGet(string $path, array $params = []): array {
		return $this->get(self::BASE_URL . '/' . ltrim($path, '/'), $params);
	}

	// =================================================================
	// PRIVATE — HTTP
	// =================================================================

	/**
	 * GET request to a CJ API endpoint.
	 *
	 * @param  string $url
	 * @param  array  $params  Query string parameters.
	 * @return array           Decoded response.
	 * @throws \Exception
	 */
	private function get(string $url, array $params): array {
		$maxRetries = 3;
		$retries = 0;
		$fullUrl = !empty($params) ? $url . '?' . http_build_query($params) : $url;

		while (true) {
			if ($this->tokenNeedsRefresh()) {
				$this->requestToken();
			}

			$ch = curl_init($fullUrl);

			curl_setopt_array($ch, [
				CURLOPT_HTTPGET        => true,
				CURLOPT_HTTPHEADER     => [
					'CJ-Access-Token: ' . $this->accessToken,
					'Content-Type: application/json',
				],
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => self::TIMEOUT,
				CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
			]);

			$body = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curlError = curl_error($ch);

			unset($ch);

			if ($this->isRateLimited($httpCode, $body) && $retries < $maxRetries) {
				$retries++;
				// Exponential backoff: 3 s, 6 s, 12 s — well under CJ's per-second limit
				sleep(3 * (int)(2 ** ($retries - 1)));
				continue;
			}

			return $this->handleResponse($body, $httpCode, $curlError, $url);
		}
	}

	/**
	 * POST JSON to a CJ API endpoint.
	 *
	 * @param  string $url
	 * @param  array  $payload
	 * @param  bool   $withAuth  Whether to send the CJ-Access-Token header.
	 * @return array             Decoded response.
	 * @throws \Exception
	 */
	private function post(string $url, array $payload, bool $withAuth = true): array {
		$json = json_encode($payload);

		if ($json === false) {
			throw new \Exception('CJDropshipping: Failed to encode request payload as JSON.');
		}

		$maxRetries = 3;
		$retries = 0;

		while (true) {
			$headers = ['Content-Type: application/json'];

			if ($withAuth) {
				if ($this->tokenNeedsRefresh()) {
					$this->requestToken();
				}
				$headers[] = 'CJ-Access-Token: ' . $this->accessToken;
			}

			$ch = curl_init($url);

			curl_setopt_array($ch, [
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => $json,
				CURLOPT_HTTPHEADER     => $headers,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT        => self::TIMEOUT,
				CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSL_VERIFYHOST => 2,
			]);

			$body = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curlError = curl_error($ch);

			unset($ch);

			if ($this->isRateLimited($httpCode, $body) && $retries < $maxRetries) {
				$retries++;
				sleep(3 * (int)(2 ** ($retries - 1)));
				continue;
			}

			return $this->handleResponse($body, $httpCode, $curlError, $url);
		}
	}

	/**
	 * Parse and validate an HTTP response from the CJ API.
	 * CJ returns code=200 inside the JSON body for success, regardless of HTTP status.
	 *
	 * @throws \Exception on cURL error, non-200 HTTP, JSON failure, or CJ error code.
	 */
	private function handleResponse(string|false $body, int $httpCode, string $curlError, string $url): array {
		if ($curlError) {
			throw new \Exception('CJDropshipping cURL error: ' . $curlError);
		}

		if ($httpCode === 401) {
			$detail = '';

			if ($body) {
				$parsed = json_decode($body, true);
				if (!empty($parsed['message'])) {
					$detail = ' — ' . $parsed['message'];
				} elseif (is_string($body) && strlen($body) < 300) {
					$detail = ' — ' . $body;
				}
			}

			throw new \Exception('CJDropshipping: Unauthorised (401) from ' . $url . $detail);
		}

		if ($httpCode === 429) {
			throw new \Exception('CJDropshipping: Rate limit exceeded (429) — wait a few minutes before retrying.');
		}

		if ($httpCode < 200 || $httpCode >= 300) {
			// Include CJ's response body in the exception so the caller can see the actual error
			$detail = '';

			if ($body) {
				$parsed = json_decode($body, true);
				if (!empty($parsed['message'])) {
					$detail = ' — ' . $parsed['message'];
				} elseif (is_string($body) && strlen($body) < 500) {
					$detail = ' — ' . $body;
				}
			}

			throw new \Exception('CJDropshipping: HTTP ' . $httpCode . ' from ' . $url . $detail);
		}

		$decoded = json_decode($body, true);

		if (!is_array($decoded)) {
			throw new \Exception('CJDropshipping: Failed to decode JSON response from ' . $url);
		}

		// CJ uses code=200 and result=true for success; any other code is an error
		if (isset($decoded['code']) && (int)$decoded['code'] !== 200) {
			throw new \Exception('CJDropshipping API error ' . $decoded['code'] . ': ' . ($decoded['message'] ?? 'Unknown'));
		}

		return $decoded;
	}

	// =================================================================
	// PRIVATE — HELPERS
	// =================================================================

	/**
	 * Returns true if this response indicates a rate-limit rejection.
	 *
	 * CJ can signal rate-limiting in two ways:
	 *   1. HTTP 429 status code.
	 *   2. HTTP 200 with {"code": 429, ...} in the JSON body (observed in practice).
	 * Both must be caught so failed responses are retried rather than misread
	 * as "no product data" and silently treated as out-of-stock.
	 */
	private function isRateLimited(int $httpCode, string|false $body): bool {
		if ($httpCode === 429) {
			return true;
		}

		if ($body) {
			$peek = json_decode($body, true);
			if (is_array($peek) && (int)($peek['code'] ?? 0) === 429) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Attempt to resolve a country name to a two-letter ISO code.
	 * CJ requires shippingCountryCode as a two-letter code.
	 * NivoCart stores the full country name in nc_order.shipping_country.
	 *
	 * This covers the most common cases — the model should populate
	 * shipping_country_id and use nc_country for a full lookup if needed.
	 */
	private function resolveCountryCode(string $countryName): string {
		$map = [
			'United Kingdom'  => 'GB',
			'United States'   => 'US',
			'Germany'         => 'DE',
			'France'          => 'FR',
			'Italy'           => 'IT',
			'Spain'           => 'ES',
			'Netherlands'     => 'NL',
			'Australia'       => 'AU',
			'Canada'          => 'CA',
			'China'           => 'CN',
			'Ireland'         => 'IE',
			'Belgium'         => 'BE',
			'Poland'          => 'PL',
			'Sweden'          => 'SE',
			'Denmark'         => 'DK',
			'Norway'          => 'NO',
			'Finland'         => 'FI',
			'Portugal'        => 'PT',
			'Austria'         => 'AT',
			'Switzerland'     => 'CH',
		];

		return $map[$countryName] ?? strtoupper(substr($countryName, 0, 2));
	}
}
