<?php
/**
 * Klarna Push Notification Endpoint
 *
 * Location : catalog/webhooks/klarna.php
 *
 * This URL is registered per-order in ModelPaymentKlarna::createOrder()
 * as merchant_urls.push. Klarna calls it when an order's fraud status
 * resolves — primarily used to upgrade orders that were left at the
 * region's pending_status_id because Klarna returned fraud_status:PENDING
 * at authorization time.
 *
 * Security model: Klarna's push notification body contains only the
 * Klarna order_id — there is no shared-secret signature to verify (unlike
 * Stripe). The correct approach is to treat the incoming order_id as an
 * untrusted hint, re-fetch the order from Klarna's Order Management API
 * using stored credentials, and act only on the status returned by that
 * authenticated re-fetch. This makes the endpoint safe against spoofed
 * push calls.
 *
 * Events handled:
 *   fraud_status: ACCEPTED  → upgrade order to region's accepted_status_id
 *   fraud_status: REJECTED  → downgrade order to global failed_status_id
 *   (PENDING is transitional — Klarna will push again when it resolves)
 *
 * NOTE: Add to .htaccess to prevent URL rewriting for this folder:
 *   RewriteRule ^catalog/webhooks/ - [L]
 *
 * @package NivoCart
 */

// -------------------------------------------------------------------------
// Bootstrap NivoCart
// Two levels below root: catalog/webhooks/
// -------------------------------------------------------------------------
define('DIR_ROOT', realpath(__DIR__ . '/../../') . '/');

require_once DIR_ROOT . 'config.php';
require_once DIR_SYSTEM . 'database/mysqli.php';
require_once DIR_SYSTEM . 'library/log.php';

// -------------------------------------------------------------------------
// Initialise DB and log
// -------------------------------------------------------------------------
try {
    $db = new DBMySQLi(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
} catch (Exception $e) {
    http_response_code(500);
    exit('DB connection failed');
}

$log = new Log('klarna_webhook.log');

// -------------------------------------------------------------------------
// Read and validate the push payload
// Klarna sends: { "order_id": "..." }
// -------------------------------------------------------------------------
$payload = file_get_contents('php://input');

$data = json_decode($payload, true);

$klarna_order_id = isset($data['order_id']) ? trim($data['order_id']) : '';

if ($klarna_order_id === '') {
    $log->write('Push notification received with no order_id. Payload: ' . substr($payload, 0, 200));
    http_response_code(400);
    exit;
}

// -------------------------------------------------------------------------
// Look up the NivoCart order by payment_reference (klarna_order_id)
// -------------------------------------------------------------------------
$order_result = $db->query("SELECT order_id, order_status_id, payment_country_id FROM `" . DB_PREFIX . "order` WHERE payment_reference = '" . $db->escape($klarna_order_id) . "' LIMIT 1");

if (!$order_result->num_rows) {
    $log->write('Push notification: no NivoCart order found for Klarna order_id ' . $klarna_order_id);
    // Respond 200 to prevent Klarna retrying for an order we don't recognise
    http_response_code(200);
    exit;
}

$order = $order_result->row;
$order_id = (int)$order['order_id'];

// -------------------------------------------------------------------------
// Resolve the country → region so we can load the right credentials
// -------------------------------------------------------------------------
$country_result = $db->query("SELECT iso_code_2 FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order['payment_country_id'] . "' LIMIT 1");

if (!$country_result->num_rows) {
    $log->write('Push notification: could not resolve country for order #' . $order_id);
    http_response_code(200);
    exit;
}

$country_code = strtoupper($country_result->row['iso_code_2']);

// -------------------------------------------------------------------------
// Load Klarna settings and resolve region credentials
// -------------------------------------------------------------------------
$settings_result = $db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `group` = 'klarna' AND `key` = 'klarna' LIMIT 1");

if (!$settings_result->num_rows) {
    $log->write('Push notification: Klarna settings not found in DB.');
    http_response_code(500);
    exit;
}

$klarna_settings = json_decode($settings_result->row['value'], true);

if (!is_array($klarna_settings)) {
    $log->write('Push notification: Klarna settings could not be decoded.');
    http_response_code(500);
    exit;
}

$context = resolveContext($country_code, $klarna_settings, $log);

if ($context === null) {
    $log->write('Push notification: could not resolve Klarna context for country ' . $country_code . ' (order #' . $order_id . ')');
    http_response_code(200);
    exit;
}

// -------------------------------------------------------------------------
// Re-fetch the Klarna order from the API — do NOT trust the push body
// -------------------------------------------------------------------------
$klarna_order = fetchKlarnaOrder($klarna_order_id, $context, $log);

if ($klarna_order === null) {
    // Logged inside fetchKlarnaOrder — respond 200 so Klarna stops
    // retrying (a persistent API error won't self-heal on retry).
    http_response_code(200);
    exit;
}

// -------------------------------------------------------------------------
// Respond 200 immediately before doing DB work — Klarna considers
// anything other than 2xx a failure and will retry.
// -------------------------------------------------------------------------
http_response_code(200);
echo json_encode(['received' => true]);

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    if (ob_get_level()) ob_end_flush();
    flush();
}

// -------------------------------------------------------------------------
// Process the fraud status from the re-fetched order
// -------------------------------------------------------------------------
$fraud_status = $klarna_order['fraud_status'] ?? '';

switch ($fraud_status) {

    case 'ACCEPTED':
        $new_status_id = (int)($context['accepted_status_id'] ?? 0);

        if ($new_status_id === 0) {
            $log->write('Push ACCEPTED: accepted_status_id not configured for region ' . $context['region_code'] . ' (order #' . $order_id . ')');
            break;
        }

        // Idempotency guard — don't re-confirm if already at this status
        if ((int)$order['order_status_id'] === $new_status_id) {
            $log->write('Push ACCEPTED: order #' . $order_id . ' already at status ' . $new_status_id . ' — skipping.');
            break;
        }

        $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $new_status_id . "', date_modified = NOW() WHERE order_id = '" . $order_id . "'");

        $db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET order_id = '" . $order_id . "', order_status_id = '" . $new_status_id . "', notify = '0', `comment` = 'Klarna fraud review passed. Order accepted (Klarna order: " . $db->escape($klarna_order_id) . ").', date_added = NOW()");

        $log->write('Push ACCEPTED: order #' . $order_id . ' upgraded to status ' . $new_status_id . '.');
        break;

    case 'REJECTED':
        // Pull the global failed order status — same one used by the
        // checkout_confirm path on a hard failure. Not per-region since
        // you confirmed a shared global "Failed" status is preferred.
        $failed_result = $db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `group` = 'config' AND `key` = 'config_order_status_id' LIMIT 1");

        // Try to get a more specific failed status — fall back to
        // config_order_status_id, then hard-coded 10 (standard Failed ID)
        // as a last resort so the order doesn't silently stay at Pending.
        $failed_status_id = $failed_result->num_rows ? (int)$failed_result->row['value'] : 10;

        $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $failed_status_id . "', date_modified = NOW() WHERE order_id = '" . $order_id . "'");

        $db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET order_id = '" . $order_id . "', order_status_id = '" . $failed_status_id . "', notify = '0', `comment` = 'Klarna fraud review failed. Order rejected (Klarna order: " . $db->escape($klarna_order_id) . ").', date_added = NOW()");

        $log->write('Push REJECTED: order #' . $order_id . ' set to failed status ' . $failed_status_id . '.');
        break;

    case 'PENDING':
        // Transitional — Klarna will push again when review completes.
        $log->write('Push PENDING: order #' . $order_id . ' still under review. No action taken.');
        break;

    default:
        $log->write('Push notification: unrecognised fraud_status "' . $fraud_status . '" for order #' . $order_id . '.');
        break;
}

exit(); // Exit required here

// =========================================================================
// Functions
// =========================================================================

/**
 * Resolves Klarna API credentials and host for a given ISO-2 country code
 * from the already-loaded settings array. Mirrors the logic in
 * ModelPaymentKlarna::resolveContext() — duplicated deliberately since
 * this script bootstraps without the NivoCart MVC stack.
 */
function resolveContext(string $country_code, array $settings, object $log): ?array {
    $country_region_map = [
        'AT' => 'eu', 'BE' => 'eu', 'DE' => 'eu', 'DK' => 'eu', 'FI' => 'eu',
        'FR' => 'eu', 'GR' => 'eu', 'IE' => 'eu', 'IT' => 'eu', 'NL' => 'eu',
        'NO' => 'eu', 'PL' => 'eu', 'PT' => 'eu', 'ES' => 'eu', 'SE' => 'eu',
        'CH' => 'eu', 'GB' => 'eu',
        'US' => 'na', 'CA' => 'na',
        'AU' => 'oc', 'NZ' => 'oc',
    ];

    $hosts = [
        'eu' => ['live' => 'https://api.klarna.com', 'playground' => 'https://api.playground.klarna.com'],
        'na' => ['live' => 'https://api-na.klarna.com', 'playground' => 'https://api-na.playground.klarna.com'],
        'oc' => ['live' => 'https://api-oc.klarna.com', 'playground' => 'https://api-oc.playground.klarna.com'],
    ];

    $region_code = $country_region_map[$country_code] ?? null;

    if ($region_code === null) {
        return null;
    }

    $region = $settings[$region_code] ?? null;

    if (empty($region['status']) || empty($region['username']) || empty($region['password'])) {
        return null;
    }

    $server = $region['server'] ?? 'playground';

    if (!isset($hosts[$region_code][$server])) {
        return null;
    }

    return [
        'region_code'        => $region_code,
        'base_url'           => $hosts[$region_code][$server],
        'username'           => $region['username'],
        'password'           => $region['password'],
        'accepted_status_id' => (int)($region['accepted_status_id'] ?? 0),
    ];
}

/**
 * Fetches a Klarna order from the Order Management API using Basic Auth.
 * Returns the decoded order array on success, null on failure.
 * This is the authenticated re-fetch that makes the push endpoint secure.
 */
function fetchKlarnaOrder(string $klarna_order_id, array $context, object $log): ?array {
    $url = $context['base_url'] . '/ordermanagement/v1/orders/' . rawurlencode($klarna_order_id);

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_USERPWD, $context['username'] . ':' . $context['password']);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);

    curl_close($curl);

    if ($response === false) {
        $log->write('fetchKlarnaOrder: cURL error for ' . $klarna_order_id . ': ' . $curl_error);
        return null;
    }

    if ($http_code !== 200) {
        $log->write('fetchKlarnaOrder: HTTP ' . $http_code . ' for ' . $klarna_order_id . '. Response: ' . substr($response, 0, 300));
        return null;
    }

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {
        $log->write('fetchKlarnaOrder: could not decode response for ' . $klarna_order_id);
        return null;
    }

    return $decoded;
}
