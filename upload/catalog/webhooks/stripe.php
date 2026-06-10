<?php
/**
 * Stripe Webhook Endpoint
 *
 * Location : catalog/webhooks/stripe.php
 * Register : https://yourdomain.com/catalog/webhooks/stripe.php
 *
 * Stripe Dashboard → Developers → Webhooks → Add endpoint
 * Events to subscribe to:
 *   - payment_intent.succeeded       (async fallback — browser closed before redirect)
 *   - payment_intent.payment_failed  (card declined, insufficient funds, etc.)
 *   - charge.dispute.created         (chargeback raised — flag order for review)
 *
 * NOTE: Add to .htaccess to prevent URL rewriting for this folder:
 *   RewriteRule ^catalog/webhooks/ - [L]
 */

// -------------------------------------------------------------------------
// Bootstrap NivoCart
// This file sits at catalog/webhooks/ — two levels below the root
// -------------------------------------------------------------------------
define('DIR_ROOT', realpath(__DIR__ . '/../../') . '/');

define('DIR_SYSTEM', DIR_ROOT . 'system/');
define('DIR_DATABASE', DIR_SYSTEM . 'database/');
define('DIR_LANGUAGE', DIR_ROOT . 'catalog/language/');
define('DIR_TEMPLATE', DIR_ROOT . 'catalog/view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_LOGS', DIR_ROOT . 'system/logs/');

// NivoCart config (DB credentials, settings)
require_once DIR_ROOT . 'config.php';

// Framework core — DB class and Log class are all we need here
require_once DIR_SYSTEM . 'database/' . DB_DRIVER . '.php';
require_once DIR_SYSTEM . 'library/log.php';

// Our Stripe library
require_once DIR_SYSTEM . 'vendor/stripe/stripe.php';

// -------------------------------------------------------------------------
// Initialise DB and Log
// -------------------------------------------------------------------------
try {
    $db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
} catch (Exception $e) {
    http_response_code(500);
    exit('DB connection failed');
}

$log = new Log('stripe_webhook.log');

// -------------------------------------------------------------------------
// Read raw payload BEFORE anything else — php://input is a stream
// -------------------------------------------------------------------------
$payload = file_get_contents('php://input');

$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// -------------------------------------------------------------------------
// Pull Stripe keys from NivoCart's settings table
// -------------------------------------------------------------------------
$result = $db->query("SELECT key, value FROM `" . DB_PREFIX . "setting` WHERE `group` = 'stripe_payments' AND `key` IN ('stripe_payments_secret_key', 'stripe_payments_publishable_key', 'stripe_payments_webhook_secret')");

$settings = [];

foreach ($result->rows as $row) {
    $settings[$row['key']] = $row['value'];
}

$secret_key = $settings['stripe_payments_secret_key'] ?? '';
$publishable_key = $settings['stripe_payments_publishable_key'] ?? '';
$webhook_secret = $settings['stripe_payments_webhook_secret'] ?? '';

if ($secret_key === '' || $webhook_secret === '') {
    $log->write('Stripe webhook: missing API keys in settings table.');
    http_response_code(500);
    exit;
}

// -------------------------------------------------------------------------
// Validate webhook signature
// -------------------------------------------------------------------------
$stripe = new Stripe($secret_key, $publishable_key, $webhook_secret);

try {
    $event = $stripe->validateWebhook($payload, $sig_header);
} catch (RuntimeException $e) {
    $log->write('Stripe webhook signature failed: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// -------------------------------------------------------------------------
// Route event — respond 200 first, then process
// Stripe considers anything other than 2xx a failure and will retry.
// -------------------------------------------------------------------------
http_response_code(200);
echo json_encode(['received' => true]);

// Flush response to Stripe before doing any DB work
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();  // PHP-FPM
} else {
    ob_end_flush();
    flush();
}

// -------------------------------------------------------------------------
// Process event
// -------------------------------------------------------------------------
$event_type = $event['type'] ?? '';
$object = $event['data']['object'] ?? [];

switch ($event_type) {
    case 'payment_intent.succeeded':
        handlePaymentSucceeded($object, $db, $log, $settings);
        break;

    case 'payment_intent.payment_failed':
        handlePaymentFailed($object, $db, $log);
        break;

    case 'charge.dispute.created':
        handleDisputeCreated($object, $db, $log);
        break;

    default:
        // Unsubscribed event received — ignore silently
        break;
}

exit;

// =========================================================================
// Event handlers
// =========================================================================

/**
 * payment_intent.succeeded
 *
 * Async fallback for the case where the customer's browser closed before
 * the JS redirect fired but Stripe completed the charge successfully.
 *
 * Idempotency: check order_status_id before doing anything — if it's
 * already the configured "paid" status, the synchronous path got there
 * first and we do nothing.
 */
function handlePaymentSucceeded(array $intent, object $db, object $log, array $settings): void {
    $payment_intent_id = $intent['id'] ?? '';

    $order_id = (int)($intent['metadata']['order_ref'] ?? 0);

    if ($order_id === 0 || $payment_intent_id === '') {
        $log->write('Stripe webhook payment_intent.succeeded: missing order_ref or intent ID.');
        return;
    }

    // Fetch current order status
    $result = $db->query("SELECT order_id, order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "' LIMIT 1");

    if (!$result->num_rows) {
        $log->write('Stripe webhook payment_intent.succeeded: order ' . $order_id . ' not found in DB.');
        return;
    }

    $order = $result->row;

    $paid_status_id = (int)($settings['stripe_payments_order_status_id'] ?? 0);
    $current_status_id = (int)$order['order_status_id'];

    // Idempotency guard — synchronous path already completed this order
    if ($paid_status_id > 0 && $current_status_id === $paid_status_id) {
        $log->write('Stripe webhook payment_intent.succeeded: order ' . $order_id . ' already complete — skipping.');
        return;
    }

    // Mark order as paid
    $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $paid_status_id . "', date_modified = NOW() WHERE order_id = '" . $order_id . "'");

    // Write to order history
    $db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET order_id = '" . $order_id . "', order_status_id = '" . $paid_status_id . "', notify = '0', `comment` = 'Payment confirmed via Stripe webhook (intent: " . $db->escape($payment_intent_id) . ")', date_added = NOW()");

    $log->write('Stripe webhook: order ' . $order_id . ' completed via async webhook path. Intent: ' . $payment_intent_id);
}

/**
 * payment_intent.payment_failed
 *
 * Card declined, insufficient funds, authentication failure, etc.
 * Logs the failure and updates the order status to the configured
 * failed status if one is set.
 */
function handlePaymentFailed(array $intent, object $db, object $log): void {
    $order_id = (int)($intent['metadata']['order_ref'] ?? 0);

    $failure_message = $intent['last_payment_error']['message'] ?? 'Unknown error';
    $failure_code = $intent['last_payment_error']['code'] ?? '';

    $log->write('Stripe webhook payment_intent.payment_failed: order ' . $order_id . ' — ' . $failure_message . ' [' . $failure_code . ']');

    if ($order_id === 0) {
        return;
    }

    // Stripe_payments_order_failed_id -> setting in the admin
    $failed_status_id = $this->config->get('stripe_payments_order_failed_id');

    $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $failed_status_id . "', date_modified = NOW() WHERE order_id = '" . $order_id . "'");

    $db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET order_id = '" . $order_id . "', order_status_id = '" . $failed_status_id . "', notify = '0', `comment` = 'Payment failed: " . $db->escape($failure_message) . " [" . $db->escape($failure_code) . "]', date_added = NOW()");
}

/**
 * charge.dispute.created
 *
 * A chargeback has been raised. Flag the order for manual admin review.
 */
function handleDisputeCreated(array $charge, object $db, object $log): void {
    $payment_intent_id = $charge['payment_intent'] ?? '';
    $amount = $charge['amount'] ?? 0;
    $reason = $charge['reason'] ?? 'unknown';

    $log->write('Stripe webhook charge.dispute.created: intent ' . $payment_intent_id . ', amount ' . $amount . ', reason: ' . $reason);

    if ($payment_intent_id === '') {
        return;
    }

    // Look up the order by payment intent ID stored in order history
    $result = $db->query("SELECT order_id FROM `" . DB_PREFIX . "order_history` WHERE `comment` LIKE '%" . $db->escape($payment_intent_id) . "%' ORDER BY date_added DESC LIMIT 1");

    if (!$result->num_rows) {
        $log->write('Stripe webhook charge.dispute.created: could not find order for intent ' . $payment_intent_id);
        return;
    }

    $order_id = (int)$result->row['order_id'];

	// Stripe_payments_order_disputed_id -> setting in the admin
    $disputed_status_id = $this->config->get('stripe_payments_order_disputed_id');

    $db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $disputed_status_id . "', date_modified = NOW() WHERE order_id = '" . $order_id . "'");

    $db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET order_id = '" . $order_id . "', order_status_id = '" . $disputed_status_id . "', notify = '0', `comment` = 'Stripe dispute raised. Reason: " . $db->escape($reason) . ". Intent: " . $db->escape($payment_intent_id) . "', date_added = NOW()");

    $log->write('Stripe webhook: order ' . $order_id . ' flagged as disputed.');
}
