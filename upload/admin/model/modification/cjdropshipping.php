<?php
/**
 * Class ModelModificationCJDropshipping
 *
 * Admin model for the CJDropshipping integration.
 * Handles all DB operations and orchestrates the CJ adapter
 * for product import (with full variant support), stock/price sync,
 * and the three-step order dispatch flow (create → cart → confirm).
 *
 * Shares the dropship tables with other dropship connectors:
 *   nc_dropship_channel, nc_product_dropship, nc_dropship_variant, nc_dropship_order
 *
 * Intended location: admin/model/modification/cjdropshipping.php
 *
 * @package NivoCart
 */
require_once DIR_SYSTEM . 'dropship/cjdropshipping.php';

class ModelModificationCJDropshipping extends Model {
	// Image subfolder relative to DIR_IMAGE
	private const IMAGE_DIR = 'data/dropship/cjd/';

	// Supported image extensions for download
	private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

	// CJ prices are in USD — stored as-is, converted at display time
	private const CJ_CURRENCY = 'USD';

	// Default warehouse for UK-targeted imports
	private const DEFAULT_WAREHOUSE = 'GB';

	// =================================================================
	// CHANNEL (shared with other dropship connectors — no duplication, channel rows are
	// distinguished by provider = 'cjdropshipping')
	// =================================================================

	public function addChannel(array $data): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "dropship_channel` SET
				`name` = '" . $this->db->escape($data['name']) . "',
				`provider` = 'cjdropshipping',
				`consumer_key` = '" . $this->db->escape($data['consumer_key']) . "',
				`secret_key` = '" . $this->db->escape($data['secret_key'] ?? '') . "',
				`access_token` = '',
				`token_expires_at` = NULL,
				`authkey` = '',
				`location_id` = '',
				`location_name` = '" . $this->db->escape($data['location_name'] ?? 'Default') . "',
				`webhook_secret` = '" . $this->db->escape($data['webhook_secret'] ?? '') . "',
				`shipping_map` = '" . $this->db->escape(json_encode($data['shipping_map'] ?? [])) . "',
				`status` = '" . (int)($data['status'] ?? 1) . "',
				`date_added` = NOW(),
				`date_modified` = NOW()
		");

		return $this->db->getLastId();
	}

	public function editChannel(int $channel_id, array $data): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "dropship_channel` SET
				`name` = '" . $this->db->escape($data['name']) . "',
				`consumer_key` = '" . $this->db->escape($data['consumer_key']) . "',
				`secret_key` = '" . $this->db->escape($data['secret_key'] ?? '') . "',
				`webhook_secret` = '" . $this->db->escape($data['webhook_secret'] ?? '') . "',
				`location_name` = '" . $this->db->escape($data['location_name'] ?? 'Default') . "',
				`shipping_map` = '" . $this->db->escape(json_encode($data['shipping_map'] ?? [])) . "',
				`status` = '" . (int)($data['status'] ?? 1) . "',
				`date_modified` = NOW()
			WHERE `channel_id` = '" . (int)$channel_id . "'
			AND `provider` = 'cjdropshipping'
		");
	}

	/**
	 * Persist refreshed access token. For CJ, secret_key stores the refresh token.
	 */
	public function updateChannelToken(int $channel_id, string $access_token, string $expires_at, string $refresh_token = ''): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "dropship_channel` SET `access_token` = '" . $this->db->escape($access_token) . "', `token_expires_at` = '" . $this->db->escape($expires_at) . "'" . (!empty($refresh_token) ? ", `secret_key` = '" . $this->db->escape($refresh_token) . "'" : '') . ", `date_modified` = NOW() WHERE `channel_id` = '" . (int)$channel_id . "'");
	}

	public function deleteChannel(int $channel_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "dropship_order` WHERE `channel_id` = '" . (int)$channel_id . "'");
		// Delete variant rows before product_dropship rows (FK order)
		$this->db->query("DELETE dv FROM `" . DB_PREFIX . "dropship_variant` dv INNER JOIN `" . DB_PREFIX . "product_dropship` pd ON dv.dropship_id = pd.dropship_id WHERE pd.`channel_id` = '" . (int)$channel_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_dropship` WHERE `channel_id` = '" . (int)$channel_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "dropship_channel` WHERE `channel_id` = '" . (int)$channel_id . "'");
	}

	public function getChannel(int $channel_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dropship_channel` WHERE `channel_id` = '" . (int)$channel_id . "' AND `provider` = 'cjdropshipping'");

		return is_object($query) ? $query->row : [];
	}

	/**
	 * Return an enabled CJDropshipping channel, or an empty array when the channel
	 * does not exist or is disabled. Used by the inbound webhook receiver.
	 */
	public function getActiveChannel(int $channel_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dropship_channel` WHERE `channel_id` = '" . (int)$channel_id . "' AND `provider` = 'cjdropshipping' AND `status` = '1'");

		return is_object($query) ? $query->row : [];
	}

	/**
	 * Record the CJ order id on a dropship order that was dispatched from this store.
	 * Only a row whose supplier_order_ref matches is touched.
	 *
	 * @return int Number of rows updated.
	 */
	public function updateOrderSupplierId(int $channel_id, string $supplier_order_ref): int {
		$this->db->query("UPDATE `" . DB_PREFIX . "dropship_order` SET `supplier_order_id` = '" . $this->db->escape($supplier_order_ref) . "', `date_modified` = NOW() WHERE `supplier_order_ref` = '" . $this->db->escape($supplier_order_ref) . "' AND `channel_id` = '" . (int)$channel_id . "'");

		return $this->db->countAffected();
	}

	public function getChannels(array $data = []): array {
		$sql = "SELECT * FROM `" . DB_PREFIX . "dropship_channel` WHERE `provider` = 'cjdropshipping'";

		if (isset($data['status'])) {
			$sql .= " AND `status` = '" . (int)$data['status'] . "'";
		}

		$sql .= " ORDER BY `name` ASC";

		$query = $this->db->query($sql);
		return is_object($query) ? $query->rows : [];
	}

	// =================================================================
	// ADAPTER FACTORY
	// =================================================================

	/**
	 * Load the CJ adapter for a channel, auto-refreshing the token if needed.
	 * CJ has two token refresh paths:
	 *   1. access_token expired but refresh_token (secret_key) still valid → refreshToken()
	 *   2. Both expired → requestToken() with apiKey
	 *
	 * @throws \Exception if channel not found or credentials missing.
	 */
	private function getAdapter(int $channel_id): CJDropshipping {
		$channel = $this->getChannel($channel_id);

		if (empty($channel)) {
			throw new \Exception('CJDropshipping model: channel_id ' . $channel_id . ' not found.');
		}

		$adapter = new CJDropshipping($channel);

		if ($adapter->tokenNeedsRefresh()) {
			// Try refresh token first (secret_key) — fall back to re-auth with apiKey
			if (!empty($channel['secret_key'])) {
				try {
					$token = $adapter->refreshToken($channel['secret_key']);

					$this->updateChannelToken(
						$channel_id,
						$token['access_token'],
						$token['expires_at'],
						$token['refresh_token']
					);
				} catch (\Exception $e) {
					// Refresh token expired — re-authenticate
					$token = $adapter->requestToken();
					$this->updateChannelToken($channel_id, $token['access_token'], $token['expires_at']);
				}
			} else {
				$token = $adapter->requestToken();
				$this->updateChannelToken($channel_id, $token['access_token'], $token['expires_at']);
			}
		}

		return $adapter;
	}

	// =================================================================
	// PRODUCT IMPORT
	// =================================================================

	/**
	 * Import products from CJ into NivoCart.
	 * Each CJ product may have multiple variants — each variant becomes
	 * one NivoCart product (Phase 1 strategy) with its own nc_dropship_variant row.
	 *
	 * @param  int    $channel_id
	 * @param  int    $category_id    NivoCart category to assign imported products to.
	 * @param  array  $filter         CJ product list filter params (categoryId, countryCode, keyWord, etc.)
	 * @param  int    $language_id
	 * @param  int    $page           Which page of CJ results to process (1-based). One page per AJAX call.
	 * @return array  ['imported' => int, 'skipped' => int, 'errors' => array, 'page' => int, 'total' => int, 'has_more' => bool]
	 */
	public function importProducts(int $channel_id, int $category_id, array $filter = [], int $language_id = 1, int $page = 1): array {
		try {
			$adapter = $this->getAdapter($channel_id);
		} catch (\Exception $e) {
			$this->log->write('CJDropshipping importProducts channel ' . $channel_id . ' (auth): ' . $e->getMessage());
			return ['imported' => 0, 'skipped' => 0, 'errors' => [$e->getMessage()], 'page' => $page, 'total' => 0, 'has_more' => false];
		}

		$this->ensureImageDirectory();

		// 5 products × 0.5s delay ≈ 2.5s of wait per call — well within PHP timeout.
		// Adapter retry logic handles any 429 throttle responses automatically.
		$pageSize = 5;
		$imported = 0;
		$skipped = 0;
		$errors = [];
		$total = 0;

		// Always filter to UK warehouse stock for UK-targeted imports.
		$filter = array_merge(['countryCode' => 'GB'], $filter);

		// Fetch exactly one page per call — the JS auto-paginates.
		try {
			$result = $adapter->getProducts(array_merge($filter, ['pageNum' => $page, 'pageSize' => $pageSize]));

			$products = $result['list'] ?? [];
			$total = (int)($result['total'] ?? 0);
		} catch (\Exception $e) {
			$this->log->write('CJDropshipping importProducts page ' . $page . ': ' . $e->getMessage());
			return ['imported' => 0, 'skipped' => 0, 'errors' => ['Page ' . $page . ': ' . $e->getMessage()], 'page' => $page, 'total' => 0, 'has_more' => false];
		}

		foreach ($products as $product) {
			try {
				$pid = $product['pid'] ?? '';

				if (empty($pid)) {
					$skipped++;
					continue;
				}

				// Fetch full detail including all variants.
				// Short pause to stay within CJ's /product/query rate limit.
				usleep(500000);

				$detail = $adapter->getProductDetail($pid); // no countryCode — fetch all variants regardless of warehouse
				$variants = $detail['variants'] ?? [];

				if (empty($variants)) {
					$skipped++;
					continue;
				}

				// Check if this CJ product (any variant) is already imported for this channel
				if ($this->getDropshipMappingByPid($pid, $channel_id)) {
					$skipped++;
					continue;
				}

				// Insert one NivoCart product per variant (Phase 1)
				foreach ($variants as $variant) {
					// Determine available stock:
					//
					// CJ only populates the `inventories` array when countryCode is passed to
					// /product/query; without it, total stock sits in `inventoryNum` on the variant.
					//
					// IMPORTANT — inventory on first import:
					//   CJ only populates inventoryNum/inventories for products already connected via
					//   the /product/conn/connection endpoint (which we intentionally skip — see note
					//   below). Both fields are therefore NULL on the first import pass.
					//   Skipping when qty=0 would prevent any variant from ever being imported.
					//
					// Strategy:
					//   • inventories populated  → use best warehouse qty; skip if still 0 (genuinely OOS)
					//   • inventoryNum is a number → use it; skip if 0
					//   • BOTH are null (unknown) → import with qty=0, inactive; stock sync fills real qty later.
					$inventories = $variant['inventories'];
					$inventoryNum = $variant['inventoryNum']; // may be null (not the same as 0)
					$stockUnknown = false;

					if (!empty($inventories) && is_array($inventories)) {
						$stockInfo = $this->extractBestAvailableStock($inventories, 'GB');
					} elseif ($inventoryNum !== null) {
						$stockInfo = ['qty' => (int)$inventoryNum, 'warehouse' => 'CN'];
					} else {
						// Null inventory — product not yet connected to our CJ account.
						// Import it so we can register the connection and unlock real stock data.
						$stockInfo = ['qty' => 0, 'warehouse' => 'CN'];
						$stockUnknown = true;
					}

					if (!$stockUnknown && $stockInfo['qty'] <= 0) {
						$this->log->write('CJDropshipping skip (no stock) variant ' . ($variant['variantSku'] ?? '?') . ' pid ' . $pid);
						$skipped++;
						continue;
					}

					try {
						$product_id = $this->insertProduct($detail, $variant, $category_id, $language_id);
						$dropship_id = $this->insertDropshipMapping($product_id, $channel_id, $detail, $variant);
						$variant_id = $this->insertVariantRow($product_id, $channel_id, $dropship_id, $variant, $stockInfo['qty'], $stockInfo['warehouse']);

						$this->insertProductVariant($pid, $product_id, $variant_id, $channel_id);

						// NOTE: createProductConnection (/product/conn/connection) is intentionally
						// skipped here. That endpoint requires the platform product to first be
						// registered in CJ's API shop via their "Save Product" + "Save Variant"
						// endpoints — without that prior step it always returns 410 "Shop Product
						// not exists". CJ order creation uses the vid directly via the order API,
						// so the connection is not needed for fulfillment in an API-only integration.
						// Implement the 3-step save → variant → connect flow if CJ's order routing
						// ever requires it.

						$imported++;
					} catch (\Exception $e) {
						$this->log->write('CJDropshipping import variant ' . ($variant['variantSku'] ?? '?') . ': ' . $e->getMessage());
						$errors[] = 'Variant ' . ($variant['variantSku'] ?? '?') . ': ' . $e->getMessage();
					}
				}

			} catch (\Exception $e) {
				$this->log->write('CJDropshipping importProducts pid ' . ($product['pid'] ?? '?') . ': ' . $e->getMessage());
				$errors[] = 'PID ' . ($product['pid'] ?? '?') . ': ' . $e->getMessage();
			}
		}

		$has_more = !empty($products) && ($page * $pageSize) < $total;

		return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors, 'page' => $page, 'total' => $total, 'has_more' => $has_more];
	}

	/**
	 * Import products from the seller's CJ "My Products" list
	 * (GET /product/myProduct/query).
	 *
	 * Only products the seller has manually added to their CJ account are
	 * returned, ensuring all imported products are ones Phil intends to sell.
	 *
	 * Response fields differ from the general catalog endpoint:
	 *   • productId   (not pid)
	 *   • pageNumber  (not pageNum)
	 *   • totalRecords (not total)
	 *
	 * @param  int    $channel_id
	 * @param  int    $category_id  NivoCart category to assign imported products
	 * @param  array  $filter       Optional: keyword, categoryId (CJ category)
	 * @param  int    $language_id
	 * @param  int    $page         1-based page number
	 * @return array  ['imported', 'skipped', 'errors', 'page', 'total', 'has_more']
	 */
	public function importMyProducts(int $channel_id, int $category_id, array $filter = [], int $language_id = 1, int $page = 1): array {
		try {
			$adapter = $this->getAdapter($channel_id);
		} catch (\Exception $e) {
			$this->log->write('CJDropshipping importMyProducts channel ' . $channel_id . ' (auth): ' . $e->getMessage());
			return ['imported' => 0, 'skipped' => 0, 'errors' => [$e->getMessage()], 'page' => $page, 'total' => 0, 'has_more' => false];
		}

		$this->ensureImageDirectory();

		$pageSize = 5;
		$imported = 0;
		$skipped = 0;
		$errors = [];
		$total = 0;

		// Build query — keyword is optional; no countryCode for this endpoint
		$params = array_merge($filter, ['pageNum' => $page, 'pageSize' => $pageSize]);

		try {
			$result = $adapter->getMyProducts($params);

			// /product/myProduct/query returns products in 'content', not 'list'
			$products = $result['content'] ?? $result['list'] ?? [];
			// /product/myProduct/query uses totalRecords, not total
			$total = (int)($result['totalRecords'] ?? $result['total'] ?? 0);
		} catch (\Exception $e) {
			$this->log->write('CJDropshipping importMyProducts page ' . $page . ': ' . $e->getMessage());
			return ['imported' => 0, 'skipped' => 0, 'errors' => ['Page ' . $page . ': ' . $e->getMessage()], 'page' => $page, 'total' => 0, 'has_more' => false];
		}

		foreach ($products as $product) {
			try {
				// /product/myProduct/query returns productId (not pid)
				$pid = $product['productId'] ?? $product['pid'] ?? '';

				if (empty($pid)) {
					$skipped++;
					continue;
				}

				usleep(500000); // 0.5s between detail fetches

				$detail = $adapter->getProductDetail((string)$pid);
				$variants = $detail['variants'] ?? [];

				if (empty($variants)) {
					$skipped++;
					continue;
				}

				if ($this->getDropshipMappingByPid((string)$pid, $channel_id)) {
					$skipped++;
					continue;
				}

				foreach ($variants as $variant) {
					$inventories = $variant['inventories'];
					$inventoryNum = $variant['inventoryNum'];
					$stockUnknown = false;

					if (!empty($inventories) && is_array($inventories)) {
						$stockInfo = $this->extractBestAvailableStock($inventories, 'GB');
					} elseif ($inventoryNum !== null) {
						$stockInfo = ['qty' => (int)$inventoryNum, 'warehouse' => 'CN'];
					} else {
						$stockInfo = ['qty' => 0, 'warehouse' => 'CN'];
						$stockUnknown = true;
					}

					if (!$stockUnknown && $stockInfo['qty'] <= 0) {
						$skipped++;
						continue;
					}

					try {
						$product_id = $this->insertProduct($detail, $variant, $category_id, $language_id);
						$dropship_id = $this->insertDropshipMapping($product_id, $channel_id, $detail, $variant);
						$variant_id = $this->insertVariantRow($product_id, $channel_id, $dropship_id, $variant, $stockInfo['qty'], $stockInfo['warehouse']);
						$this->insertProductVariant($pid, $product_id, $variant_id, $channel_id);

						// createProductConnection skipped — see note in importProducts().

						$imported++;
					} catch (\Exception $e) {
						$this->log->write('CJDropshipping importMyProducts variant ' . ($variant['variantSku'] ?? '?') . ': ' . $e->getMessage());
						$errors[] = 'Variant ' . ($variant['variantSku'] ?? '?') . ': ' . $e->getMessage();
					}
				}

			} catch (\Exception $e) {
				$this->log->write('CJDropshipping importMyProducts pid ' . ($product['productId'] ?? '?') . ': ' . $e->getMessage());
				$errors[] = 'PID ' . ($product['productId'] ?? '?') . ': ' . $e->getMessage();
			}
		}

		$has_more = !empty($products) && ($page * $pageSize) < $total;

		return ['imported' => $imported, 'skipped' => $skipped, 'errors' => $errors, 'page' => $page, 'total' => $total, 'has_more' => $has_more];
	}

	/**
	 * Insert a NivoCart product row for one CJ variant.
	 * Product name = "Product Name [VariantKey]" for Phase 1 disambiguation.
	 */
	private function insertProduct(array $detail, array $variant, int $category_id, int $language_id): int {
		$manufacturer_id = 0;

		if (!empty($detail['supplierName'])) {
			$manufacturer_id = $this->resolveManufacturer($detail['supplierName'], $language_id);
		}

		// Build variant-suffixed product name
		$baseName = $detail['productNameEn'] ?? '';
		$variantKey = $variant['variantKey'] ?? '';
		$name = $variantKey ? $baseName . ' [' . $variantKey . ']' : $baseName;

		// Download primary image — prefer variant image, fall back to product image
		$imageUrl = $variant['variantImage'] ?? $detail['bigImage'] ?? '';
		$safeSku = preg_replace('/[^A-Za-z0-9_\-]/', '_', $variant['variantSku'] ?? $detail['productSku'] ?? 'cj');
		$image = $imageUrl ? $this->downloadImage($imageUrl, $safeSku, 0) : '';

		// CJ weights are in grams, dimensions in mm — matches NivoCart defaults
		$this->db->query("INSERT INTO `" . DB_PREFIX . "product` SET
			`model` = '" . $this->db->escape($variant['variantSku'] ?? $detail['productSku'] ?? '') . "',
			`sku` = '" . $this->db->escape($variant['variantSku'] ?? '') . "',
			`ean` = '" . $this->db->escape($variant['barcode'] ?? '') . "',
			`mpn` = '',
			`image` = '" . $this->db->escape($image) . "',
			`manufacturer_id` = '" . (int)$manufacturer_id . "',
			`price` = '" . (float)($variant['variantSellPrice'] ?? $detail['sellPrice'] ?? 0) . "',
			`cost` = '" . (float)($variant['variantSellPrice'] ?? $detail['sellPrice'] ?? 0) . "',
			`quantity` = '999',
			`minimum` = '1',
			`subtract` = '1',
			`weight` = '" . (float)($variant['variantWeight'] ?? $detail['productWeight'] ?? 0) . "',
			`length` = '" . (float)($variant['variantLength'] ?? 0) . "',
			`width` = '" . (float)($variant['variantWidth'] ?? 0) . "',
			`height` = '" . (float)($variant['variantHeight'] ?? 0) . "',
			`status` = '1',
			`sort_order` = '0',
			`date_available` = NOW(),
			`date_added` = NOW(),
			`date_modified` = NOW()
		");

		$product_id = $this->db->getLastId();

		// Product description
		$this->db->query("INSERT INTO `" . DB_PREFIX . "product_description` SET
			`product_id` = '" . (int)$product_id . "',
			`language_id` = '" . (int)$language_id . "',
			`name` = '" . $this->db->escape($name) . "',
			`description` = '" . $this->db->escape($detail['description'] ?? '') . "',
			`meta_description` = '',
			`meta_keyword` = '',
			`tag` = ''
		");

		// Category assignment
		$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_category` SET `product_id` = '" . (int)$product_id . "', `category_id` = '" . (int)$category_id . "'");

		// Additional product images from productImageSet
		if (!empty($detail['productImageSet']) && is_array($detail['productImageSet'])) {
			$sort = 1;

			foreach (array_slice($detail['productImageSet'], 1, 7) as $imgUrl) {
				$additionalImage = $this->downloadImage($imgUrl, $safeSku, $sort);

				if ($additionalImage) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_image` SET `product_id` = '" . (int)$product_id . "', `image` = '" . $this->db->escape($additionalImage) . "', `palette_color_id` = '0', `sort_order` = '" . (int)$sort . "'");
				}

				$sort++;
			}
		}

		return $product_id;
	}

	/**
	 * Insert nc_product_dropship mapping row (one per CJ product/channel pair).
	 * Multiple variants of the same CJ product share one dropship_id.
	 */
	private function insertDropshipMapping(int $product_id, int $channel_id, array $detail, array $variant): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "product_dropship` SET
			`product_id` = '" . (int)$product_id . "',
			`channel_id` = '" . (int)$channel_id . "',
			`supplier_pid` = '" . $this->db->escape($detail['pid'] ?? '') . "',
			`supplier_guid` = '" . $this->db->escape($detail['pid'] ?? '') . "',
			`supplier_authkey` = '',
			`supplier_cost` = '" . (float)($variant['variantSellPrice'] ?? $detail['sellPrice'] ?? 0) . "',
			`rrp` = '" . (float)($variant['variantSugSellPrice'] ?? 0) . "',
			`vat_rate` = '20.00',
			`is_active` = '1',
			`date_added` = NOW(),
			`date_modified` = NOW()
		");

		return $this->db->getLastId();
	}

	/**
	 * Insert nc_dropship_variant row for one CJ variant.
	 */
	private function insertVariantRow(int $product_id, int $channel_id, int $dropship_id, array $variant, int $quantity, string $warehouse_code = ''): int {
		$image = '';

		if (!empty($variant['variantImage'])) {
			$safeSku = preg_replace('/[^A-Za-z0-9_\-]/', '_', $variant['variantSku'] ?? 'cj_v');
			$image = $this->downloadImage($variant['variantImage'], $safeSku . '_v', 0);
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "dropship_variant` SET
			`dropship_id` = '" . (int)$dropship_id . "',
			`product_id` = '" . (int)$product_id . "',
			`channel_id` = '" . (int)$channel_id . "',
			`supplier_vid` = '" . $this->db->escape($variant['vid'] ?? '') . "',
			`supplier_sku` = '" . $this->db->escape($variant['variantSku'] ?? '') . "',
			`barcode` = '" . $this->db->escape($variant['barcode'] ?? '') . "',
			`variant_key` = '" . $this->db->escape($variant['variantKey'] ?? '') . "',
			`variant_name` = '" . $this->db->escape($variant['variantNameEn'] ?? '') . "',
			`weight` = '" . (float)($variant['variantWeight'] ?? 0) . "',
			`length` = '" . (float)($variant['variantLength'] ?? 0) . "',
			`width` = '" . (float)($variant['variantWidth'] ?? 0) . "',
			`height` = '" . (float)($variant['variantHeight'] ?? 0) . "',
			`supplier_cost` = '" . (float)($variant['variantSellPrice'] ?? 0) . "',
			`rrp` = '" . (float)($variant['variantSugSellPrice'] ?? 0) . "',
			`currency_code` = '" . self::CJ_CURRENCY . "',
			`quantity` = '" . (int)$quantity . "',
			`warehouse_code` = '" . $this->db->escape($warehouse_code ?: self::DEFAULT_WAREHOUSE) . "',
			`image` = '" . $this->db->escape($image) . "',
			`is_active` = '1',
			`date_added` = NOW(),
			`date_modified` = NOW()
		");

		$variant_id = $this->db->getLastId();

		// Update nc_product.quantity with the available warehouse stock
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = '" . (int)$quantity . "', `date_modified` = NOW() WHERE `product_id` = '" . (int)$product_id . "'");

		return $variant_id;
	}

	/**
	 * Insert nc_product_variant row linking a product to its DS parent group.
	 */
	private function insertProductVariant(string $pid, int $product_id, int $variant_id, int $channel_id): void {
		if (empty($pid)) {
			return;
		}

		$this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "product_variant` SET
			`pid` = '" . $this->db->escape($pid) . "',
			`product_id` = '" . (int)$product_id . "',
			`variant_id` = '" . (int)$variant_id . "',
			`channel_id` = '" . (int)$channel_id . "',
			`sort_order` = '0'
		");
	}

	// =================================================================
	// MAPPING QUERIES
	// =================================================================

	/**
	 * Get dropship mapping by CJ pid (parent product ID) and channel.
	 * Used to detect duplicate imports at the product level.
	 */
	public function getDropshipMappingByPid(string $pid, int $channel_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_dropship` WHERE `supplier_pid` = '" . $this->db->escape($pid) . "' AND `channel_id` = '" . (int)$channel_id . "' LIMIT 1");

		return is_object($query) ? $query->row : [];
	}

	/**
	 * Get a variant row by its CJ vid and channel.
	 */
	public function getVariantByVid(string $vid, int $channel_id): array {
		$query = $this->db->query("SELECT dv.*, pd.supplier_pid, pd.supplier_authkey FROM `" . DB_PREFIX . "dropship_variant` dv LEFT JOIN `" . DB_PREFIX . "product_dropship` pd ON (dv.dropship_id = pd.dropship_id) WHERE dv.`supplier_vid` = '" . $this->db->escape($vid) . "' AND dv.`channel_id` = '" . (int)$channel_id . "' LIMIT 1");

		return is_object($query) ? $query->row : [];
	}

	/**
	 * Get the variant row for a NivoCart product_id on a given channel.
	 * For Phase 1 (one product per variant) this always returns one row.
	 */
	public function getVariantByProductId(int $product_id, int $channel_id): array {
		$query = $this->db->query("SELECT dv.*, pd.supplier_pid, pd.supplier_authkey, dc.shipping_map, dc.location_name FROM `" . DB_PREFIX . "dropship_variant` dv LEFT JOIN `" . DB_PREFIX . "product_dropship` pd ON (dv.dropship_id = pd.dropship_id) LEFT JOIN `" . DB_PREFIX . "dropship_channel` dc ON (dv.channel_id = dc.channel_id) WHERE dv.`product_id` = '" . (int)$product_id . "' AND dv.`channel_id` = '" . (int)$channel_id . "' AND dv.`is_active` = '1' LIMIT 1");

		return is_object($query) ? $query->row : [];
	}

	/**
	 * Return mapped products for a channel with basic product info, sorted by product name.
	 */
	public function getMappedProducts(int $channel_id, array $data = []): array {
		$lang_id = (int)$this->config->get('config_language_id');

		$sql = "SELECT dv.*, pd.supplier_pid, p.model, p.status, p.quantity, p.price, pdd.`name` AS product_name
				FROM `" . DB_PREFIX . "dropship_variant` dv
				LEFT JOIN `" . DB_PREFIX . "product_dropship` pd  ON (dv.dropship_id = pd.dropship_id)
				LEFT JOIN `" . DB_PREFIX . "product` p            ON (dv.product_id  = p.product_id)
				LEFT JOIN `" . DB_PREFIX . "product_description` pdd
					   ON (p.product_id = pdd.product_id AND pdd.language_id = '" . $lang_id . "')
				WHERE dv.`channel_id` = '" . (int)$channel_id . "'";

		if (isset($data['is_active'])) {
			$sql .= " AND dv.`is_active` = '" . (int)$data['is_active'] . "'";
		}

		$sql .= " ORDER BY pdd.`name` ASC, dv.`variant_key` ASC";

		if (isset($data['start'], $data['limit'])) {
			$data['start'] = max(0, (int)$data['start']);
			$data['limit'] = max(1, (int)$data['limit']);

			$sql .= " LIMIT " . $data['start'] . "," . $data['limit'];
		}

		return $this->db->query($sql)->rows;
	}

	public function getTotalMappedProducts(int $channel_id): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "dropship_variant` WHERE `channel_id` = '" . (int)$channel_id . "'");

		return (int)$query->row['total'];
	}

	/**
	 * Return all variant siblings that share the same DS parent (pid) as $product_id.
	 * Used by the catalog layer to render the variant selector on product pages.
	 * Returns an empty array if this product has no variant group (standalone product).
	 */
	public function getProductVariants(int $product_id): array {
		// Find the pid and channel for the current product
		$map = $this->db->query("SELECT `pid`, `channel_id` FROM `" . DB_PREFIX . "product_variant` WHERE `product_id` = '" . (int)$product_id . "' LIMIT 1");

		if (!is_object($map) || empty($map->row)) {
			return [];
		}

		$pid = $map->row['pid'];
		$channel_id = (int)$map->row['channel_id'];

		$query = $this->db->query("SELECT pv.`product_id`, dv.`variant_key`, dv.`image` FROM `" . DB_PREFIX . "product_variant` pv INNER JOIN `" . DB_PREFIX . "dropship_variant` dv ON pv.`variant_id` = dv.`variant_id` WHERE pv.`pid` = '" . $this->db->escape($pid) . "' AND pv.`channel_id` = '" . $channel_id . "' ORDER BY pv.`sort_order` ASC, pv.`id` ASC");

		return is_object($query) ? $query->rows : [];
	}

	// =================================================================
	// STOCK SYNC
	// =================================================================

	/**
	 * Sync stock for all mapped variants on a channel.
	 *
	 * Batches by CJ parent product (PID) — the same strategy syncPrices uses —
	 * so a single call to getVariants() covers all variants of each parent in one
	 * API request. For 70 NivoCart products spread across 25 CJ parent products
	 * this is 25 API calls instead of 70, keeping the total well within the
	 * server's PHP execution time limit.
	 *
	 * getVariants(pid, 'GB') returns all variants of a parent product with their
	 * per-warehouse inventory array (countryCode + totalInventory), exactly the
	 * shape extractMaxWarehouseStock() expects.
	 *
	 * @return array ['updated' => int, 'errors' => array]
	 */
	public function syncStock(int $channel_id, int $offset = 0, int $limit = 10): array {
		$adapter = $this->getAdapter($channel_id);

		// Total active variants for this channel — needed to compute has_more.
		$count_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "dropship_variant` WHERE `channel_id` = '" . (int)$channel_id . "' AND `is_active` = '1'");

		$total = (int)($count_query->row['total'] ?? 0);

		if ($total === 0) {
			return ['updated' => 0, 'errors' => [], 'total' => 0, 'processed' => 0, 'has_more' => false];
		}

		// Fetch the requested slice — no JOIN needed; product_id lives on the variant row.
		$variants_query = $this->db->query(
			"SELECT `variant_id`, `product_id`, `supplier_sku`
			 FROM `" . DB_PREFIX . "dropship_variant`
			 WHERE `channel_id` = '" . (int)$channel_id . "'
			   AND `is_active` = '1'
			 ORDER BY `variant_id` ASC
			 LIMIT " . (int)$limit . " OFFSET " . (int)$offset
		);

		if (!is_object($variants_query) || empty($variants_query->rows)) {
			return ['updated' => 0, 'errors' => [], 'total' => $total, 'processed' => 0, 'has_more' => false];
		}

		$updated = 0;
		$errors = [];

		foreach ($variants_query->rows as $row) {
			try {
				usleep(400000); // 0.4s between SKU calls — stays well within CJ rate limit

				// getInventoryBySku returns an array of per-warehouse inventory objects:
				// [ { "countryCode": "CN", "totalInventory": 42 }, ... ]
				$stockData = $adapter->getInventoryBySku($row['supplier_sku']);

				if (empty($stockData) || !is_array($stockData)) {
					continue; // No inventory data returned — leave this variant unchanged.
				}

				// Take the highest stock figure across all warehouses so CN-only products
				// don't read as 0 when only GB warehouses are checked.
				$qty = $this->extractMaxWarehouseStock($stockData);

				$this->db->query("UPDATE `" . DB_PREFIX . "dropship_variant` SET `quantity` = '" . (int)$qty . "', `last_stock_sync` = NOW(), `date_modified` = NOW() WHERE `variant_id` = '" . (int)$row['variant_id'] . "'");
				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = '" . (int)$qty . "', `date_modified` = NOW() WHERE `product_id` = '" . (int)$row['product_id'] . "'");

				$updated++;

			} catch (\Exception $e) {
				$this->log->write('CJDropshipping syncStock SKU ' . $row['supplier_sku'] . ': ' . $e->getMessage());
				$errors[] = $row['supplier_sku'] . ': ' . $e->getMessage();

				// CJD error 1602001 = product no longer exists on supplier side.
				// Zero the stock and deactivate so it drops off the storefront and
				// is excluded from all future syncs (is_active = 0 filter).
				if (str_contains($e->getMessage(), '1602001')) {
					$this->db->query("UPDATE `" . DB_PREFIX . "dropship_variant` SET `quantity` = '0', `is_active` = '0', `date_modified` = NOW() WHERE `variant_id` = '" . (int)$row['variant_id'] . "'");
					$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = '0', `status` = '0', `date_modified` = NOW() WHERE `product_id` = '" . (int)$row['product_id'] . "'");
				}
			}
		}

		$processed = count($variants_query->rows);
		$has_more = ($offset + $processed) < $total;

		return [
			'updated'   => $updated,
			'errors'    => $errors,
			'total'     => $total,
			'processed' => $processed,
			'has_more'  => $has_more,
		];
	}

	// =================================================================
	// PRICE SYNC
	// =================================================================

	/**
	 * Sync supplier cost price for a slice of parent PIDs.
	 * Batched to avoid PHP execution timeout on large catalogues.
	 * Updates nc_dropship_variant only — does NOT overwrite nc_product.price.
	 * CJ prices are in USD; currency_code is stored for conversion at display time.
	 *
	 * @param  int $channel_id
	 * @param  int $offset  Which PID (by sorted order) to start from.
	 * @param  int $limit   How many PIDs to process per call (default 5 — ~2.5 s API time).
	 * @return array ['updated' => int, 'errors' => array, 'total' => int, 'processed' => int, 'has_more' => bool]
	 */
	public function syncPrices(int $channel_id, int $offset = 0, int $limit = 5): array {
		$adapter = $this->getAdapter($channel_id);

		// Count distinct supplier PIDs for this channel.
		$count_query = $this->db->query(
			"SELECT COUNT(DISTINCT pd.`supplier_pid`) AS total
			 FROM `" . DB_PREFIX . "dropship_variant` dv
			 INNER JOIN `" . DB_PREFIX . "product_dropship` pd ON dv.`dropship_id` = pd.`dropship_id`
			 WHERE dv.`channel_id` = '" . (int)$channel_id . "'
			   AND dv.`is_active` = '1'"
		);

		$total = (int)($count_query->row['total'] ?? 0);

		if ($total === 0) {
			return ['updated' => 0, 'errors' => [], 'total' => 0, 'processed' => 0, 'has_more' => false];
		}

		// Fetch a sorted slice of distinct PIDs for this batch.
		$pid_query = $this->db->query(
			"SELECT DISTINCT pd.`supplier_pid`
			 FROM `" . DB_PREFIX . "dropship_variant` dv
			 INNER JOIN `" . DB_PREFIX . "product_dropship` pd ON dv.`dropship_id` = pd.`dropship_id`
			 WHERE dv.`channel_id` = '" . (int)$channel_id . "'
			   AND dv.`is_active` = '1'
			 ORDER BY pd.`supplier_pid` ASC
			 LIMIT " . (int)$limit . " OFFSET " . (int)$offset
		);

		if (!is_object($pid_query) || empty($pid_query->rows)) {
			return ['updated' => 0, 'errors' => [], 'total' => $total, 'processed' => 0, 'has_more' => false];
		}

		$pids = array_column($pid_query->rows, 'supplier_pid');

		$updated = 0;
		$errors = [];

		foreach ($pids as $pid) {
			// Fetch all active variants for this parent PID.
			$variants_query = $this->db->query(
				"SELECT dv.`variant_id`, dv.`supplier_sku`
				 FROM `" . DB_PREFIX . "dropship_variant` dv
				 INNER JOIN `" . DB_PREFIX . "product_dropship` pd ON dv.`dropship_id` = pd.`dropship_id`
				 WHERE pd.`supplier_pid` = '" . $this->db->escape($pid) . "'
				   AND dv.`channel_id` = '" . (int)$channel_id . "'
				   AND dv.`is_active` = '1'"
			);

			if (!is_object($variants_query) || empty($variants_query->rows)) {
				continue;
			}

			// Build SKU → variant_id lookup for this PID.
			$skuMap = [];

			foreach ($variants_query->rows as $row) {
				$skuMap[$row['supplier_sku']] = (int)$row['variant_id'];
			}

			try {
				usleep(500000); // 0.5s — stay within CJ rate limit
				$detail = $adapter->getProductDetail($pid);
				$variants = $detail['variants'] ?? [];

				foreach ($variants as $v) {
					$sku = $v['variantSku'] ?? '';

					if (!isset($skuMap[$sku])) {
						continue;
					}

					$variant_id = $skuMap[$sku];

					$this->db->query("UPDATE `" . DB_PREFIX . "dropship_variant` SET
						`supplier_cost` = '" . (float)($v['variantSellPrice'] ?? 0) . "',
						`rrp` = '" . (float)($v['variantSugSellPrice'] ?? 0) . "',
						`last_price_sync` = NOW(),
						`date_modified` = NOW()
						WHERE `variant_id` = '" . $variant_id . "'");

					$updated++;
				}

			} catch (\Exception $e) {
				$this->log->write('CJDropshipping syncPrices PID ' . $pid . ': ' . $e->getMessage());
				$errors[] = 'PID ' . $pid . ': ' . $e->getMessage();
			}
		}

		$processed = count($pids);
		$has_more = ($offset + $processed) < $total;

		return [
			'updated'   => $updated,
			'errors'    => $errors,
			'total'     => $total,
			'processed' => $processed,
			'has_more'  => $has_more,
		];
	}

	// =================================================================
	// ORDER DISPATCH
	// =================================================================

	/**
	 * Dispatch all CJ dropship items on a NivoCart order using the
	 * three-step CJ flow: createOrderV2 → addCart → addCartConfirm.
	 *
	 * Each CJ line item needs a variant vid and a logisticName.
	 * logisticName is resolved from the channel's shipping_map
	 * (NC shipping_code → CJ logistics name).
	 *
	 * @param  int  $order_id
	 * @return array ['dispatched' => int, 'errors' => array]
	 */

	// -------------------------------------------------------------------------
	// Orders for dispatch listing
	// -------------------------------------------------------------------------

	/**
	 * Return orders that contain at least one CJDropshipping product on the
	 * given channel, together with their current dispatch state.
	 *
	 * @param  int  $channel_id
	 * @return array
	 */
	public function getOrdersForDispatch(int $channel_id, int $start = 0, int $limit = 20): array {
		$query = $this->db->query("
			SELECT
				o.order_id,
				CONCAT(o.firstname, ' ', o.lastname) AS customer,
				o.email AS customer_email,
				o.date_added,
				o.total,
				o.currency_code,
				o.currency_value,
				COALESCE(MAX(os.name), '') AS order_status,
				COUNT(DISTINCT op.order_product_id) AS cjd_items,
				COALESCE(COUNT(DISTINCT CASE WHEN do2.dispatch_status = '1' THEN do2.dropship_order_id END), 0) AS dispatched_items,
				COALESCE(COUNT(DISTINCT CASE WHEN do2.dispatch_status = '2' THEN do2.dropship_order_id END), 0) AS failed_items,
				COALESCE(GROUP_CONCAT(DISTINCT NULLIF(do2.tracking_number, '') ORDER BY do2.dropship_order_id SEPARATOR ', '), '') AS tracking,
				COALESCE(GROUP_CONCAT(DISTINCT NULLIF(do2.tracking_carrier, '') ORDER BY do2.dropship_order_id SEPARATOR ', '), '') AS tracking_carrier,
				MAX(do2.tracking_email_sent) AS tracking_email_sent
			FROM `" . DB_PREFIX . "order` o
			INNER JOIN `" . DB_PREFIX . "order_product` op
				ON op.order_id = o.order_id
			INNER JOIN `" . DB_PREFIX . "dropship_variant` dv
				ON  dv.product_id = op.product_id
				AND dv.channel_id = '" . (int)$channel_id . "'
				AND dv.is_active = '1'
			LEFT JOIN `" . DB_PREFIX . "order_status` os
				ON  os.order_status_id = o.order_status_id
				AND os.language_id = '1'
			LEFT JOIN `" . DB_PREFIX . "dropship_order` do2
				ON  do2.order_id = o.order_id
				AND do2.order_product_id = op.order_product_id
				AND do2.channel_id = '" . (int)$channel_id . "'
			GROUP BY
				o.order_id, o.firstname, o.lastname, o.email,
				o.date_added, o.total, o.currency_code, o.currency_value
			HAVING NOT (
				COUNT(DISTINCT op.order_product_id) = COUNT(DISTINCT CASE WHEN do2.dispatch_status = '1' THEN do2.order_product_id END)
				AND COALESCE(MAX(NULLIF(do2.dispatched_at, '0000-00-00 00:00:00')), MAX(do2.date_added)) < DATE_SUB(NOW(), INTERVAL 60 DAY)
			)
			ORDER BY o.date_added DESC
			LIMIT " . (int)$start . ", " . (int)$limit . "
		");

		if (!is_object($query) || !isset($query->rows)) {
			return [];
		}

		return $query->rows;
	}

	public function countOrdersForDispatch(int $channel_id): int {
		$query = $this->db->query("
			SELECT COUNT(*) AS total FROM (
				SELECT o.order_id
				FROM `" . DB_PREFIX . "order` o
				INNER JOIN `" . DB_PREFIX . "order_product` op
					ON op.order_id = o.order_id
				INNER JOIN `" . DB_PREFIX . "dropship_variant` dv
					ON  dv.product_id = op.product_id
					AND dv.channel_id = '" . (int)$channel_id . "'
					AND dv.is_active = '1'
				LEFT JOIN `" . DB_PREFIX . "dropship_order` do2
					ON  do2.order_id = o.order_id
					AND do2.order_product_id = op.order_product_id
					AND do2.channel_id = '" . (int)$channel_id . "'
				GROUP BY o.order_id
				HAVING NOT (
					COUNT(DISTINCT op.order_product_id) = COUNT(DISTINCT CASE WHEN do2.dispatch_status = '1' THEN do2.order_product_id END)
					AND COALESCE(MAX(NULLIF(do2.dispatched_at, '0000-00-00 00:00:00')), MAX(do2.date_added)) < DATE_SUB(NOW(), INTERVAL 60 DAY)
				)
			) subq
		");

		return isset($query->row['total']) ? (int)$query->row['total'] : 0;
	}

	public function markTrackingEmailSent(int $order_id, int $channel_id): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "dropship_order` SET `tracking_email_sent` = NOW(), `date_modified` = NOW() WHERE `order_id` = '" . (int)$order_id . "' AND `channel_id` = '" . (int)$channel_id . "'");
	}

	public function dispatchOrder(int $order_id): array {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE `order_id` = '" . (int)$order_id . "'");

		if (empty($order_query->row)) {
			throw new \Exception('CJDropshipping dispatchOrder: order_id ' . $order_id . ' not found.');
		}

		$order = $order_query->row;

		$products_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE `order_id` = '" . (int)$order_id . "'");

		$dispatched = 0;
		$errors = [];

		// Group items by channel_id (supports mixed-channel orders in future)
		$channels = [];

		foreach ($products_query->rows as $op) {
			// Find a CJ variant for this product
			$variant_query = $this->db->query("SELECT dv.*, dc.shipping_map, dc.location_name FROM `" . DB_PREFIX . "dropship_variant` dv LEFT JOIN `" . DB_PREFIX . "dropship_channel` dc ON (dv.channel_id = dc.channel_id) WHERE dv.`product_id` = '" . (int)$op['product_id'] . "' AND dc.`provider` = 'cjdropshipping' AND dv.`is_active` = '1' LIMIT 1");

			if (empty($variant_query->row)) {
				continue; // Not a CJ product
			}

			$v = $variant_query->row;

			// Skip if already dispatched successfully
			$already = $this->db->query("SELECT `dropship_order_id` FROM `" . DB_PREFIX . "dropship_order` WHERE `order_id` = '" . (int)$order_id . "' AND `order_product_id` = '" . (int)$op['order_product_id'] . "' AND `dispatch_status` = '1'");

			if ($already->row) {
				continue;
			}

			$channel_id = (int)$v['channel_id'];

			$channels[$channel_id][] = [
				'order_product_id' => (int)$op['order_product_id'],
				'vid'              => $v['supplier_vid'],
				'variant_id'       => (int)$v['variant_id'],
				'quantity'         => (int)$op['quantity'],
				'shipping_map'     => $v['shipping_map'] ?? '{}'
			];
		}

		foreach ($channels as $channel_id => $items) {
			try {
				$adapter = $this->getAdapter($channel_id);

				$shippingMap = json_decode($items[0]['shipping_map'] ?? '{}', true) ?? [];
				$logisticName = $shippingMap[$order['shipping_code']] ?? 'CJPacket Ordinary';

				// Step 1: Create order on CJ
				$createResult = $adapter->createOrder($order, $items, $logisticName, 'CN');
				$cjOrderId = $createResult['orderId'];

				// Step 2: Add to cart
				$adapter->addToCart([$cjOrderId]);

				// Step 3: Confirm cart (pay from CJ balance)
				$adapter->confirmCart([$cjOrderId]);

				// Record each dispatched line item
				foreach ($items as $item) {
					$this->insertDropshipOrder(
						$order_id,
						$item['order_product_id'],
						$channel_id,
						$item['variant_id'],
						$item['vid'],
						$cjOrderId,
						'',
						1
					);
				}

				$dispatched += count($items);

			} catch (\Exception $e) {
				$this->log->write('CJDropshipping dispatchOrder order_id ' . $order_id . ' channel ' . $channel_id . ': ' . $e->getMessage());

				$errors[] = 'Channel ' . $channel_id . ': ' . $e->getMessage();

				foreach ($items as $item) {
					$this->insertDropshipOrder(
						$order_id,
						$item['order_product_id'],
						$channel_id,
						$item['variant_id'],
						$item['vid'],
						'',
						$e->getMessage(),
						2
					);
				}
			}
		}

		return ['dispatched' => $dispatched, 'errors' => $errors];
	}

	/**
	 * Poll CJ for tracking updates on all dispatched-but-untracked orders.
	 *
	 * @return array ['updated' => int, 'errors' => array]
	 */
	public function syncTracking(int $channel_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dropship_order` WHERE `channel_id` = '" . (int)$channel_id . "' AND `dispatch_status` = '1' AND `tracking_number` = '' AND `supplier_order_ref` != ''");

		if (empty($query->rows)) {
			return ['updated' => 0, 'errors' => []];
		}

		$adapter = $this->getAdapter($channel_id);
		$updated = 0;

		$errors = [];

		// Group by CJ order ID to minimise API calls
		$orderIds = array_unique(array_column($query->rows, 'supplier_order_ref'));

		foreach ($orderIds as $cjOrderId) {
			try {
				usleep(500000); // 0.5s — stay within CJ rate limit
				$detail = $adapter->getOrderDetail($cjOrderId);

				if (empty($detail['trackNumber'])) {
					continue;
				}

				$this->db->query("UPDATE `" . DB_PREFIX . "dropship_order` SET `tracking_number` = '" . $this->db->escape($detail['trackNumber']) . "', `tracking_carrier` = '" . $this->db->escape($detail['trackingProvider'] ?? '') . "', `dispatched_at` = '" . $this->db->escape($detail['paymentDate'] ?? '') . "', `date_modified` = NOW() WHERE `supplier_order_ref` = '" . $this->db->escape($cjOrderId) . "' AND `channel_id` = '" . (int)$channel_id . "'");

				$updated++;

			} catch (\Exception $e) {
				$this->log->write('CJDropshipping syncTracking orderId ' . $cjOrderId . ': ' . $e->getMessage());
				$errors[] = 'CJ order ' . $cjOrderId . ': ' . $e->getMessage();
			}
		}

		return ['updated' => $updated, 'errors' => $errors];
	}

	// =================================================================
	// DROPSHIP ORDER RECORDS
	// =================================================================

	private function insertDropshipOrder(int $order_id, int $order_product_id, int $channel_id, int $variant_id, string $supplier_vid, string $supplier_order_ref, string $error_message, int $status): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "dropship_order` (`order_id`, `order_product_id`, `channel_id`, `variant_id`, `supplier_vid`, `supplier_order_ref`, `dispatch_status`, `dispatch_error`, `date_added`, `date_modified`)
			VALUES (
				'" . (int)$order_id . "',
				'" . (int)$order_product_id . "',
				'" . (int)$channel_id . "',
				'" . (int)$variant_id . "',
				'" . $this->db->escape($supplier_vid) . "',
				'" . $this->db->escape($supplier_order_ref) . "',
				'" . (int)$status . "',
				'" . $this->db->escape($error_message) . "',
				NOW(), NOW()
			)
			ON DUPLICATE KEY UPDATE
				`supplier_order_ref` = '" . $this->db->escape($supplier_order_ref) . "',
				`dispatch_status` = '" . (int)$status . "',
				`dispatch_error` = '" . $this->db->escape($error_message) . "',
				`date_modified` = NOW()
		");
	}

	public function getDropshipOrders(int $order_id): array {
		$query = $this->db->query("SELECT do.*, dc.name AS channel_name FROM `" . DB_PREFIX . "dropship_order` do LEFT JOIN `" . DB_PREFIX . "dropship_channel` dc ON (do.channel_id = dc.channel_id) WHERE do.`order_id` = '" . (int)$order_id . "' ORDER BY do.`date_added` ASC");

		return $query->rows;
	}

	// =================================================================
	// WEBHOOK — STOCK + ORDER UPDATE
	// =================================================================

	/**
	 * Process a validated CJ stock webhook payload.
	 * Call from catalog/webhooks/cjdropshipping.php after validateWebhook().
	 *
	 * CJ stock webhook payload shape:
	 * { "type": "stock", "data": [ { "vid": string, "sku": string, "quantity": int,
	 *   "countryCode": string }, ... ] }
	 *
	 * @return array ['updated' => int, 'errors' => array]
	 */
	public function processStockWebhook(int $channel_id, array $payload): array {
		$items = $payload['data'] ?? [];
		$updated = 0;

		$errors = [];

		foreach ((is_array($items) ? $items : []) as $item) {
			// Only act on well-formed items for the UK warehouse
			if (!is_array($item) || ($item['countryCode'] ?? '') !== 'GB') {
				continue;
			}

			$vid = (is_array($item) && isset($item['vid']) && is_scalar($item['vid'])) ? (string)$item['vid'] : '';
			$qty = (int)($item['quantity'] ?? 0);

			if (empty($vid)) {
				continue;
			}

			$row = $this->getVariantByVid($vid, $channel_id);

			if (empty($row)) {
				continue;
			}

			try {
				$this->db->query("UPDATE `" . DB_PREFIX . "dropship_variant` SET `quantity` = '" . (int)$qty . "', `last_stock_sync` = NOW(), `date_modified` = NOW() WHERE `supplier_vid` = '" . $this->db->escape($vid) . "' AND `channel_id` = '" . (int)$channel_id . "'");

				$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `quantity` = '" . (int)$qty . "', `date_modified` = NOW() WHERE `product_id` = '" . (int)$row['product_id'] . "'");

				$updated++;

			} catch (\Exception $e) {
				$this->log->write('CJDropshipping stockWebhook vid ' . $vid . ': ' . $e->getMessage());
				$errors[] = 'vid ' . $vid . ': ' . $e->getMessage();
			}
		}

		return ['updated' => $updated, 'errors' => $errors];
	}

	/**
	 * Process a CJ logistics webhook payload (tracking number update).
	 *
	 * CJ logistics webhook payload shape:
	 * { "type": "logistics", "data": { "orderId": string, "trackNumber": string,
	 *   "trackingProvider": string } }
	 *
	 * @return array ['updated' => int]
	 */
	public function processLogisticsWebhook(int $channel_id, array $payload): array {
		$data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
		$cjOrderId = is_scalar($data['orderId'] ?? null) ? trim((string)$data['orderId']) : '';
		$trackNumber = is_scalar($data['trackNumber'] ?? null) ? trim((string)$data['trackNumber']) : '';
		$carrier = is_scalar($data['trackingProvider'] ?? null) ? trim((string)$data['trackingProvider']) : '';

		if (empty($cjOrderId) || empty($trackNumber)) {
			return ['updated' => 0];
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "dropship_order` SET `tracking_number` = '" . $this->db->escape($trackNumber) . "', `tracking_carrier` = '" . $this->db->escape($carrier) . "', `dispatched_at` = NOW(), `date_modified` = NOW() WHERE `supplier_order_ref` = '" . $this->db->escape($cjOrderId) . "' AND `channel_id` = '" . (int)$channel_id . "' AND `tracking_number` = ''");

		return ['updated' => $this->db->countAffected()];
	}

	// =================================================================
	// WEBHOOK REGISTRATION
	// =================================================================

	/**
	 * Register NivoCart webhook URLs with CJ.
	 * Call this once after adding a CJ channel — or when the domain changes.
	 *
	 * @param  int    $channel_id
	 * @param  string $baseUrl     Public catalog base URL (e.g. https://yourdomain.com/)
	 * @return bool
	 */
	public function registerWebhooks(int $channel_id, string $baseUrl): bool {
		try {
			$adapter = $this->getAdapter($channel_id);
		} catch (\Exception $e) {
			$this->log->write('CJDropshipping registerWebhooks channel ' . $channel_id . ' (auth): ' . $e->getMessage());
			return false;
		}

		$baseUrl = rtrim($baseUrl, '/') . '/';

		$stockUrl = $baseUrl . 'catalog/webhooks/cjdropshipping.php?type=stock&channel_id=' . $channel_id;
		$orderUrl = $baseUrl . 'catalog/webhooks/cjdropshipping.php?type=order&channel_id=' . $channel_id;
		$logisticsUrl = $baseUrl . 'catalog/webhooks/cjdropshipping.php?type=logistics&channel_id=' . $channel_id;

		try {
			$adapter->registerWebhooks($stockUrl, $orderUrl, $logisticsUrl);
			return true;
		} catch (\Exception $e) {
			$this->log->write('CJDropshipping registerWebhooks channel ' . $channel_id . ' (api): ' . $e->getMessage());
			return false;
		}
	}

	// =================================================================
	// DIAGNOSTICS
	// =================================================================

	public function testConnection(int $channel_id): array {
		$channel = $this->getChannel($channel_id);

		if (empty($channel)) {
			return ['error' => 'Channel ' . $channel_id . ' not found.'];
		}

		$adapter = new CJDropshipping($channel);
		$result = $adapter->testConnection();

		// Persist the freshly-obtained token so subsequent import/sync calls use it
		if ($result['auth'] === 'OK' && !empty($result['token_raw'])) {
			$this->updateChannelToken($channel_id, $result['token_raw'], $result['token_expires_raw'] ?? '');

			unset($result['token_raw'], $result['token_expires_raw']);
		}

		return $result;
	}

	// =================================================================
	// COUNTRY CODE LOOKUP
	// =================================================================

	/**
	 * Resolve a NivoCart country_id to a two-letter ISO code.
	 * More reliable than the adapter's name-based map for edge cases.
	 */
	public function getCountryCode(int $country_id): string {
		$query = $this->db->query("SELECT `iso_code_2` FROM `" . DB_PREFIX . "country` WHERE `country_id` = '" . (int)$country_id . "' LIMIT 1");

		return $query->row['iso_code_2'] ?? 'GB';
	}

	// =================================================================
	// IMAGE HELPERS
	// =================================================================

	private function ensureImageDirectory(): void {
		$dir = DIR_IMAGE . self::IMAGE_DIR;

		if (!is_dir($dir)) {
			if (!mkdir($dir, 0755, true)) {
				throw new \Exception('CJDropshipping: Failed to create image directory: ' . $dir);
			}
		}
	}

	/**
	 * Download a remote image and save it to DIR_IMAGE/data/dropship/.
	 * Returns the relative path for storage in nc_product.image, or '' on failure.
	 */
	private function downloadImage(string $url, string $safeSku, int $index = 0): string {
		if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
			return '';
		}

		$urlPath = parse_url($url, PHP_URL_PATH);
		$ext = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));

		if (!in_array($ext, self::IMAGE_EXTENSIONS, true)) {
			$ext = 'jpg';
		}

		$filename = $safeSku . '_' . $index . '.' . $ext;
		$dest = DIR_IMAGE . self::IMAGE_DIR . $filename;

		if (file_exists($dest)) {
			return self::IMAGE_DIR . $filename;
		}

		$ch = curl_init($url);

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
		]);

		$imageData = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);

		unset($ch);

		if ($curlError || $httpCode !== 200 || empty($imageData)) {
			$this->log->write('CJDropshipping downloadImage failed for ' . $safeSku . ' (' . $url . '): HTTP ' . $httpCode . ' ' . $curlError);
			return '';
		}

		if (file_put_contents($dest, $imageData) === false) {
			$this->log->write('CJDropshipping downloadImage: could not write ' . $dest);
			return '';
		}

		return self::IMAGE_DIR . $filename;
	}

	// =================================================================
	// MANUFACTURER HELPER
	// =================================================================

	private function resolveManufacturer(string $name, int $language_id = 1): int {
		$query = $this->db->query("SELECT `manufacturer_id` FROM `" . DB_PREFIX . "manufacturer_description` WHERE `name` = '" . $this->db->escape($name) . "' AND `language_id` = '" . (int)$language_id . "' LIMIT 1 ");

		if ($query->row) {
			return (int)$query->row['manufacturer_id'];
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer` (`sort_order`, `status`) VALUES ('0', '1')");

		$manufacturer_id = $this->db->getLastId();

		$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_description` (`manufacturer_id`, `language_id`, `name`, `description`) VALUES ('" . (int)$manufacturer_id . "', '" . (int)$language_id . "', '" . $this->db->escape($name) . "', '')");

		return $manufacturer_id;
	}

	// =================================================================
	// UTILITY
	// =================================================================

	/**
	 * Return the maximum stock quantity across all warehouses.
	 * Used by syncStock() so CN-only products show real availability
	 * rather than 0 (which only GB warehouse would return).
	 */
	private function extractMaxWarehouseStock(array $inventories): int {
		$max = 0;

		foreach ($inventories as $inv) {
			$qty = (int)($inv['totalInventory'] ?? $inv['totalInventoryNum'] ?? 0);

			if ($qty > $max) {
				$max = $qty;
			}
		}

		return $max;
	}

	/**
	 * Extract the best available stock for a variant at import time.
	 *
	 * Most CJ products are warehoused in CN and ship worldwide — there is no
	 * GB warehouse entry. This method prefers the GB warehouse but falls back
	 * to the first warehouse that carries any stock, so CN-stocked products
	 * are not silently skipped during import.
	 *
	 * @param  array  $inventories  variant.inventories from CJ product detail
	 * @param  string $preferred    Preferred warehouse country code (default 'GB')
	 * @return array  ['qty' => int, 'warehouse' => string]
	 */
	private function extractBestAvailableStock(array $inventories, string $preferred = 'GB'): array {
		$best = ['qty' => 0, 'warehouse' => ''];

		foreach ($inventories as $inv) {
			$code = (string)($inv['countryCode'] ?? '');
			$qty = (int)($inv['totalInventory'] ?? $inv['totalInventoryNum'] ?? 0);

			if ($code === $preferred && $qty > 0) {
				// Exact preferred-warehouse match — return immediately
				return ['qty' => $qty, 'warehouse' => $code];
			}

			// Keep the first non-zero warehouse as fallback
			if ($best['qty'] === 0 && $qty > 0) {
				$best = ['qty' => $qty, 'warehouse' => $code];
			}
		}

		return $best;
	}

	// =================================================================
	// PURGE STALE PRODUCTS
	// =================================================================

	/**
	 * Count dropship variant rows whose nc_product no longer exists.
	 */
	public function countOrphanProducts(): int {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "dropship_variant` dv LEFT JOIN `" . DB_PREFIX . "product` p ON dv.product_id = p.product_id WHERE p.product_id IS NULL");

		return (int)($query->row['total'] ?? 0);
	}

	/**
	 * Delete all dropship records (variant, mapping, variant-group) whose nc_product no longer exists.
	 * Returns the number of variant rows deleted.
	 */
	public function purgeStaleProducts(): int {
		$query = $this->db->query("SELECT DISTINCT dv.product_id FROM `" . DB_PREFIX . "dropship_variant` dv LEFT JOIN `" . DB_PREFIX . "product` p ON dv.product_id = p.product_id WHERE p.product_id IS NULL");

		if (!$query->rows) {
			return 0;
		}

		$ids = implode(',', array_map('intval', array_column($query->rows, 'product_id')));

		$count_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "dropship_variant` WHERE `product_id` IN (" . $ids . ")");

		$deleted = (int)($count_query->row['total'] ?? 0);

		$this->db->query("DELETE FROM `" . DB_PREFIX . "dropship_variant` WHERE `product_id` IN (" . $ids . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_dropship` WHERE `product_id` IN (" . $ids . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_variant` WHERE `product_id` IN (" . $ids . ")");

		return $deleted;
	}

	// =================================================================
	// PURGE DUPLICATE PRODUCTS
	// =================================================================

	/**
	 * Count products that are true duplicates of an already-imported CJ variant.
	 *
	 * Each NivoCart product represents one CJ variant (one supplier_vid). A real
	 * duplicate occurs only when the exact same supplier_vid appears more than once
	 * in nc_dropship_variant for the same channel — i.e. the same CJ variant was
	 * imported a second time. Grouping by supplier_pid (the parent product ID) is
	 * wrong because all variants of one CJ product share the same supplier_pid, so
	 * that approach falsely treats multi-variant products as duplicates.
	 */
	public function countDuplicateProducts(): int {
		$query = $this->db->query(
			"SELECT COUNT(*) AS total
			 FROM `" . DB_PREFIX . "dropship_variant` dv
			 INNER JOIN (
				 SELECT `channel_id`, `supplier_vid`, MIN(`product_id`) AS keep_id
				 FROM `" . DB_PREFIX . "dropship_variant`
				 WHERE `supplier_vid` != ''
				 GROUP BY `channel_id`, `supplier_vid`
				 HAVING COUNT(*) > 1
			 ) keeper
				 ON dv.`channel_id` = keeper.`channel_id`
				AND dv.`supplier_vid` = keeper.`supplier_vid`
			 WHERE dv.`product_id` != keeper.`keep_id`"
		);

		return (int)($query->row['total'] ?? 0);
	}

	/**
	 * Return a small sample of the rows that countDuplicateProducts() would count,
	 * for diagnostic and UI-preview purposes.
	 * Each row: product_id, supplier_sku, supplier_vid, product_name.
	 */
	public function getDuplicateSample(int $limit = 5): array {
		$query = $this->db->query(
			"SELECT dv.`product_id`, dv.`supplier_sku`, dv.`supplier_vid`,
					COALESCE(pd.`name`, '') AS product_name
			 FROM `" . DB_PREFIX . "dropship_variant` dv
			 LEFT JOIN `" . DB_PREFIX . "product_description` pd
				 ON dv.`product_id` = pd.`product_id` AND pd.`language_id` = 1
			 INNER JOIN (
				 SELECT `channel_id`, `supplier_vid`, MIN(`product_id`) AS keep_id
				 FROM `" . DB_PREFIX . "dropship_variant`
				 WHERE `supplier_vid` != ''
				 GROUP BY `channel_id`, `supplier_vid`
				 HAVING COUNT(*) > 1
			 ) keeper
				 ON dv.`channel_id` = keeper.`channel_id`
				AND dv.`supplier_vid` = keeper.`supplier_vid`
			 WHERE dv.`product_id` != keeper.`keep_id`
			 ORDER BY dv.`supplier_vid`, dv.`product_id`
			 LIMIT " . (int)$limit
		);

		return is_object($query) ? $query->rows : [];
	}

	/**
	 * Delete all duplicate products, keeping the original (lowest product_id)
	 * for each (channel_id, supplier_vid) pair.
	 *
	 * Removes the product from every table the import created:
	 *   nc_dropship_variant, nc_product_dropship, nc_product_variant,
	 *   nc_product_image, nc_product_to_category, nc_product_description, nc_product.
	 *
	 * @return int  Number of duplicate products deleted.
	 */
	public function purgeDuplicateProducts(): int {
		// Collect the product_ids to delete — those whose supplier_vid already
		// exists with a lower product_id in the same channel.
		$query = $this->db->query(
			"SELECT dv.`product_id`
			 FROM `" . DB_PREFIX . "dropship_variant` dv
			 INNER JOIN (
				 SELECT `channel_id`, `supplier_vid`, MIN(`product_id`) AS keep_id
				 FROM `" . DB_PREFIX . "dropship_variant`
				 WHERE `supplier_vid` != ''
				 GROUP BY `channel_id`, `supplier_vid`
				 HAVING COUNT(*) > 1
			 ) keeper
				 ON dv.`channel_id` = keeper.`channel_id`
				AND dv.`supplier_vid` = keeper.`supplier_vid`
			 WHERE dv.`product_id` != keeper.`keep_id`"
		);

		if (empty($query->rows)) {
			return 0;
		}

		$ids = implode(',', array_map('intval', array_column($query->rows, 'product_id')));

		$deleted = count($query->rows);

		// Delete child/mapping tables first, then the product rows themselves.
		$this->db->query("DELETE FROM `" . DB_PREFIX . "dropship_variant` WHERE `product_id` IN (" . $ids . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_dropship` WHERE `product_id` IN (" . $ids . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_variant` WHERE `product_id` IN (" . $ids . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_image` WHERE `product_id` IN (" . $ids . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` IN (" . $ids . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_description` WHERE `product_id` IN (" . $ids . ")");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product` WHERE `product_id` IN (" . $ids . ")");

		return $deleted;
	}
}
