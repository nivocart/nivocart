<?php
/**
 * CJDropshipping Webhook Receiver
 *
 * Receives and processes inbound webhook notifications from CJDropshipping.
 * Handles three event types:
 *   - stock     — product quantity changes (per warehouse)
 *   - order     — order status changes
 *   - logistics — tracking number updates
 *
 * CJ sends a POST request with a JSON body, signed with HMAC-SHA256 of the
 * raw body using the webhook_secret stored in nc_dropship_channel (sent in
 * the X-CJ-Sign header).
 *
 * Signature handling is ADVISORY. CJ requires the endpoint to be reachable
 * without authentication (a 401 makes CJ mark the URL as failed), so a
 * signature mismatch is logged but the request is still answered with 200.
 * As a safeguard, only records that already exist in the store (a known
 * variant for stock updates, a known CJ order reference for order and
 * logistics updates) are ever changed; anything else is ignored.
 *
 * Mismatches and ignored updates are written to:
 *   system/logs/cjdropshipping_webhook.log
 *
 * Registration:
 *   In your CJ developer account → Webhook Settings, or via the
 *   "Register Webhooks" button in the NivoCart CJDropshipping dashboard.
 *   Endpoint URLs (set per channel_id):
 *     Stock:     https://yourdomain.com/catalog/webhooks/cjdropshipping.php?type=stock&channel_id=N
 *     Order:     https://yourdomain.com/catalog/webhooks/cjdropshipping.php?type=order&channel_id=N
 *     Logistics: https://yourdomain.com/catalog/webhooks/cjdropshipping.php?type=logistics&channel_id=N
 *
 * NOTE: Add to .htaccess to prevent URL rewriting for this folder:
 *   RewriteRule ^catalog/webhooks/ - [L]
 *
 * Intended location: catalog/webhooks/cjdropshipping.php
 *
 * @package NivoCart
 */

// -----------------------------------------------------------------
// Bootstrap — same pattern as the Klarna and Stripe webhooks, no startup.php
// catalog/webhooks/ is two levels below store root
// -----------------------------------------------------------------
define('DIR_ROOT', realpath(__DIR__ . '/../../') . '/');

require_once DIR_ROOT . 'config.php';
require_once DIR_SYSTEM . 'database/mysqli.php';
require_once DIR_SYSTEM . 'engine/registry.php';
require_once DIR_SYSTEM . 'engine/model.php';
require_once DIR_SYSTEM . 'library/log.php';

try {
	$db = new DBMySQLi(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
} catch (Exception $e) {
	http_response_code(500);
	exit('DB connection failed');
}

$log = new Log('cjdropshipping_webhook.log');

$registry = new Registry();
$registry->set('db', $db);
$registry->set('log', $log);

// Load adapter and model
require_once DIR_SYSTEM . 'dropship/cjdropshipping.php';
require_once DIR_ROOT . 'admin/model/modification/cjdropshipping.php';

// -----------------------------------------------------------------
// Request validation
// -----------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit('Method Not Allowed');
}

$channel_id = isset($_GET['channel_id']) ? (int)$_GET['channel_id'] : 0;

if ($channel_id < 1) {
	http_response_code(400);
	exit('Missing channel_id');
}

$type = isset($_GET['type']) ? trim($_GET['type']) : '';

if (!in_array($type, ['stock', 'order', 'logistics'], true)) {
	http_response_code(400);
	exit('Invalid or missing type parameter');
}

$rawBody = file_get_contents('php://input');

// CJ may send an empty-body verification ping when registering the webhook URL.
// Acknowledge it with 200 OK so registration succeeds.
if (empty($rawBody)) {
	http_response_code(200);
	exit('OK');
}

// CJ sends the HMAC signature in X-CJ-Sign header
$signHeader = $_SERVER['HTTP_X_CJ_SIGN'] ?? '';

// Remote address, used for log entries only
$remote = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// -----------------------------------------------------------------
// Load channel config
// -----------------------------------------------------------------
$model = new ModelModificationCJDropshipping($registry);

$channel = $model->getActiveChannel($channel_id);

if (!$channel) {
	http_response_code(404);
	exit('Channel not found');
}

// -----------------------------------------------------------------
// Validate signature (advisory — never block CJ requests on failure)
// Per CJ IT guidance: the endpoint must not require authentication.
// If the webhook_secret is configured we verify and log mismatches,
// but we never return 401 — that causes CJ to mark the URL as failed.
// -----------------------------------------------------------------
$signatureFailed = false;

try {
	$adapter = new CJDropshipping($channel);
	$payload = $adapter->validateWebhook($rawBody, $signHeader);
} catch (\Exception $e) {
	$payload = json_decode($rawBody, true);

	if (!is_array($payload)) {
		// Body isn't valid JSON — this is likely CJ's registration verification ping.
		// Return 200 OK so CJ marks the URL as reachable and registration succeeds.
		http_response_code(200);
		exit('OK');
	}

	// Valid JSON that failed signature verification: carry on, but record it.
	$signatureFailed = true;

	$log->write('CJDropshipping webhook (' . $type . ', channel ' . $channel_id . ', from ' . $remote . '): ' . $e->getMessage() . ' Payload processed in advisory mode — only matching records will be updated.');
}

// -----------------------------------------------------------------
// Process payload
// -----------------------------------------------------------------
try {
	switch ($type) {
		case 'stock':
			/**
			 * CJ stock webhook payload shape:
			 * {
			 *   "type": "stock",
			 *   "data": [
			 *     {
			 *       "vid": string,
			 *       "sku": string,
			 *       "quantity": int,
			 *       "countryCode": string,
			 *       "updatedAt": string
			 *     },
			 *     ...
			 *   ]
			 * }
			 * Only variants already held for this channel are updated.
			 */
			if (empty($payload['data']) || !is_array($payload['data'])) {
				// No data — treat as CJ's verification ping and acknowledge.
				http_response_code(200);
				exit('OK');
			}

			$result = $model->processStockWebhook($channel_id, $payload);

			if ($signatureFailed) {
				$log->write('CJDropshipping webhook (stock, channel ' . $channel_id . ', unverified): ' . (int)$result['updated'] . ' variant(s) matched and updated, ' . count($payload['data']) . ' item(s) received.');
			}

			break;

		case 'order':
			/**
			 * CJ order status webhook payload shape:
			 * {
			 *   "type": "order",
			 *   "data": {
			 *     "orderId": string,
			 *     "orderStatus": string,
			 *     "updatedAt": string
			 *   }
			 * }
			 * Order status changes are informational. Only an order that was
			 * dispatched from this store (matching supplier_order_ref) is touched.
			 * Actual tracking updates arrive via the logistics webhook.
			 */
			$orderId = (isset($payload['data']['orderId']) && is_scalar($payload['data']['orderId'])) ? trim((string)$payload['data']['orderId']) : '';

			if ($orderId !== '') {
				$updated = $model->updateOrderSupplierId($channel_id, $orderId);

				if ($signatureFailed && !$updated) {
					$log->write('CJDropshipping webhook (order, channel ' . $channel_id . ', unverified): no matching order for reference "' . $orderId . '" — ignored.');
				}
			}

			break;

		case 'logistics':
			/**
			 * CJ logistics webhook payload shape:
			 * {
			 *   "type": "logistics",
			 *   "data": {
			 *     "orderId": string,
			 *     "trackNumber": string,
			 *     "trackingProvider": string,
			 *     "updatedAt": string
			 *   }
			 * }
			 * Only an order dispatched from this store (matching supplier_order_ref)
			 * that has no tracking number yet is updated.
			 */
			if (empty($payload['data'])) {
				// No data — treat as CJ's verification ping and acknowledge.
				http_response_code(200);
				exit('OK');
			}

			$result = $model->processLogisticsWebhook($channel_id, $payload);

			if ($signatureFailed && empty($result['updated'])) {
				$log->write('CJDropshipping webhook (logistics, channel ' . $channel_id . ', unverified): no matching order awaiting tracking — ignored.');
			}

			break;
	}

	// CJ expects a 200 OK to confirm receipt
	http_response_code(200);
	exit('OK');

} catch (\Throwable $e) {
	$log->write('CJDropshipping webhook (' . $type . ', channel ' . $channel_id . ') error: ' . $e->getMessage());

	http_response_code(500);
	exit('Internal error');
}
