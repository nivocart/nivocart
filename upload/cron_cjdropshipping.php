<?php
/**
 * NivoCart CJDropshipping Stock Sync Cron
 *
 * CLI-only entry point. Syncs the stock of every mapped variant on every active
 * CJDropshipping channel without a browser. It asks CJ for one product at a time (one call
 * updates all variants of that product), so the run stays short as the catalogue grows.
 * Variants CJ does not list are set to stock 0 (kept active, shown as out of stock).
 *
 * The exact command (with the real path) and the recommended schedule are shown at
 * the bottom of the CJD Dashboard. Example (03:00 China time = 19:00 UTC daily):
 *   0 19 * * * /usr/bin/php /path/to/public_html/cron_cjdropshipping.php > /dev/null 2>&1
 *
 * The result of each run is written to nc_cron_log (task "cjd_stock_sync", shown on
 * the dashboard) and to system/logs/cjdropshipping_cron.log.
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

set_time_limit(0);

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
$lock = fopen($lock_dir . 'cjdropshipping_stock_sync.lock', 'c');

if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
	exit('[' . date('Y-m-d H:i:s') . '] Another stock sync is still running. Nothing done.' . PHP_EOL);
}

// -------------------------------------------------------
// Bootstrap registry
// -------------------------------------------------------
$registry = new Registry();

$loader = new Loader($registry);
$registry->set('load', $loader);

$config = new Config();
$registry->set('config', $config);

$log = new Log('cjdropshipping_cron.log');
$registry->set('log', $log);

$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

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
// Cron library + CJDropshipping model
// -------------------------------------------------------
require_once DIR_SYSTEM . 'library/cron.php';
require_once __DIR__ . '/admin/model/modification/cjdropshipping.php';

$cron = new Cron($registry);
$model = new ModelModificationCJDropshipping($registry);

// -------------------------------------------------------
// Run
// -------------------------------------------------------
// One API call per CJ product updates all of its variants. Products are taken oldest-synced
// first, so if the time budget runs out the next run carries on with the products not done yet.
const CJD_BATCH_SIZE = 10;
const CJD_MAX_FAILED_BATCHES = 5;
const CJD_TIME_BUDGET = 1500; // Seconds: stop cleanly before a host that cuts runs at 30 minutes.

$started = time();
$total_updated = 0;
$total_zeroed = 0;
$total_errors = 0;
$failed = false;
$summary = [];

echo '[' . date('Y-m-d H:i:s') . '] CJDropshipping stock sync started.' . PHP_EOL;

// A "running" row is written first: if the host kills this run, no finish row follows and the
// dashboard shows the run as interrupted instead of showing nothing at all.
try {
	$cron->log('cjd_stock_sync', 0, 'running', 'Started. If no result follows, the run was interrupted.');
} catch (\Throwable $e) {
	$log->write('CJDropshipping stock cron: could not write nc_cron_log: ' . $e->getMessage());
}

$channels = $model->getChannels(['status' => 1]);

if (!$channels) {
	$failed = true;
	$summary[] = 'No active CJDropshipping channel.';
}

foreach ($channels as $channel) {
	$channel_id = (int)$channel['channel_id'];
	$skip_pids = [];
	$updated = 0;
	$zeroed = 0;
	$errors = 0;
	$total = 0;
	$failed_batches = 0;

	try {
		do {
			$result = $model->syncStockByPid($channel_id, $skip_pids, CJD_BATCH_SIZE);

			$skip_pids = array_merge($skip_pids, $result['pids']);
			$updated += $result['updated'];
			$zeroed += $result['zeroed'];
			$total = max($total, $result['total']);
			$errors += count($result['errors']);

			// Every product of the batch failed (not just a few): the CJ API is probably down or rate limited.
			if ($result['processed'] > 0 && count($result['errors']) >= $result['processed']) {
				$failed_batches++;
			} else {
				$failed_batches = 0;
			}

			if ($failed_batches >= CJD_MAX_FAILED_BATCHES) {
				throw new \Exception('Stopped after ' . CJD_MAX_FAILED_BATCHES . ' batches in a row with only errors (CJ API unreachable or rate limited?).');
			}

			if ($result['has_more'] && (time() - $started) >= CJD_TIME_BUDGET) {
				throw new \Exception('Time limit reached after ' . count($skip_pids) . '/' . $total . ' products; the rest comes first on the next run.');
			}
		} while ($result['has_more']);

		$summary[] = $channel['name'] . ': ' . $updated . ' variant(s) updated, ' . $zeroed . ' set to 0 (not listed by CJ), ' . $errors . ' error(s), ' . count($skip_pids) . '/' . $total . ' products.';
	} catch (\Throwable $e) {
		$failed = true;
		$summary[] = $channel['name'] . ': ' . $e->getMessage() . ' (' . $updated . ' variant(s) updated before stopping).';

		$log->write('CJDropshipping stock cron channel ' . $channel_id . ': ' . $e->getMessage());
	}

	$total_updated += $updated + $zeroed;
	$total_zeroed += $zeroed;
	$total_errors += $errors;
}

$message = implode(' ', $summary) . ' Duration: ' . gmdate('H:i:s', time() - $started) . '.';

$log->write('CJDropshipping stock cron finished. ' . $message);

try {
	$cron->log('cjd_stock_sync', $total_updated, $failed ? 'error' : 'success', mb_substr($message, 0, 250));
} catch (\Throwable $e) {
	$log->write('CJDropshipping stock cron: could not write nc_cron_log: ' . $e->getMessage());
}

echo '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

flock($lock, LOCK_UN);
fclose($lock);

exit($failed ? 1 : 0);
