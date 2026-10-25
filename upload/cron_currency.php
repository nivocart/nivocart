<?php
/**
 * NivoCart Currency Rates Cron
 *
 * CLI-only entry point. Updates the exchange rates of every enabled currency against the
 * default currency, without a browser and without waiting for an admin to open the dashboard.
 *
 * Usage:
 *   php cron_currency.php                Frankfurter first, FloatRates if Frankfurter fails
 *   php cron_currency.php frankfurter    Frankfurter only (primary source)
 *   php cron_currency.php floatrates     FloatRates only (backup source)
 *
 * The exact commands (with the real path) and the recommended schedule are shown at the
 * bottom of Localisation > Currency. Recommended (UTC):
 *   0 1 * * * /usr/bin/php /path/to/public_html/cron_currency.php floatrates > /dev/null 2>&1
 *   0 2 * * * /usr/bin/php /path/to/public_html/cron_currency.php frankfurter > /dev/null 2>&1
 *
 * The result of each run is written to nc_cron_log (task "currency_cron", shown on
 * Localisation > Currency) and to system/logs/currency_cron.log.
 *
 * @package NivoCart
 */

// -------------------------------------------------------
// Refuse web requests
// -------------------------------------------------------
if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit('Access denied: this script must be run from the command line.');
}

// -------------------------------------------------------
// Source from the command line
// -------------------------------------------------------
$source = strtolower(trim($argv[1] ?? ''));

if (!in_array($source, ['', 'frankfurter', 'floatrates'], true)) {
	exit('Usage: php cron_currency.php [frankfurter|floatrates]' . PHP_EOL);
}

// -------------------------------------------------------
// Configuration
// -------------------------------------------------------
if (!file_exists(__DIR__ . '/config.php')) {
	exit('Error: config.php not found. Run the installer first.' . PHP_EOL);
}

require_once __DIR__ . '/config.php';

// -------------------------------------------------------
// Startup (defines Registry, Loader, DB, Config, Log …)
// -------------------------------------------------------
require_once DIR_SYSTEM . 'startup.php';

// -------------------------------------------------------
// Lock: refuse to start while a previous run is still working
// -------------------------------------------------------
$lock_dir = defined('DIR_LOGS') ? DIR_LOGS : DIR_SYSTEM . 'logs/';
$lock = fopen($lock_dir . 'currency_cron.lock', 'c');

if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
	exit('[' . date('Y-m-d H:i:s') . '] Another currency update is still running. Nothing done.' . PHP_EOL);
}

// -------------------------------------------------------
// Bootstrap registry
// -------------------------------------------------------
$registry = new Registry();

$loader = new Loader($registry);
$registry->set('load', $loader);

$config = new Config();
$registry->set('config', $config);

$log = new Log('currency_cron.log');
$registry->set('log', $log);

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

// The storefront reads the rates through this cache, it must be cleared after an update
$cache = new Cache();
$registry->set('cache', $cache);

// Load store settings (store_id 0 only — cron has no store context)
$query = $db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE store_id = '0'");

foreach ($query->rows as $setting) {
	if (!$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], json_decode($setting['value'], true));
	}
}

// -------------------------------------------------------
// Currency model
// -------------------------------------------------------
require_once __DIR__ . '/admin/model/localisation/currency.php';

$model = new ModelLocalisationCurrency($registry);

// -------------------------------------------------------
// Run
// -------------------------------------------------------
echo '[' . date('Y-m-d H:i:s') . '] Currency rate update started (' . ($source ?: 'frankfurter, then floatrates') . ').' . PHP_EOL;

try {
	$result = $model->updateRates($source);
} catch (\Throwable $e) {
	$result = ['status' => 'error', 'updated' => 0, 'message' => 'Unexpected error: ' . $e->getMessage()];
}

$message = '[' . ($source ?: 'auto') . '] ' . $result['message'];

$log->write('Currency cron finished (' . $result['status'] . '). ' . $message);

$model->logRun($model::LOG_CRON, ['status' => $result['status'], 'updated' => $result['updated'], 'message' => $message]);

echo '[' . date('Y-m-d H:i:s') . '] ' . ucfirst($result['status']) . ': ' . $message . PHP_EOL;

flock($lock, LOCK_UN);
fclose($lock);

exit(($result['status'] === 'success') ? 0 : 1);
