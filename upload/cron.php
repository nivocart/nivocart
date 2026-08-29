<?php
/**
 * NivoCart Data Retention Cron
 *
 * CLI-only entry point. Bootstraps the DB and runs all data-retention tasks.
 * Recommended crontab entry (adjust path to match your installation):
 *   0 2 * * * php /var/www/html/upload/cron.php >> /var/log/nivocart_cron.log 2>&1
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
// Bootstrap registry
// -------------------------------------------------------
$registry = new Registry();

$loader = new Loader($registry);
$registry->set('load', $loader);

$config = new Config();
$registry->set('config', $config);

$log = new Log('cron.log');
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
// Cron library + Data Retention model
// -------------------------------------------------------
require_once DIR_SYSTEM . 'library/cron.php';
require_once __DIR__ . '/admin/model/tool/data_retention.php';

$cron = new Cron($registry);
$model = new ModelToolDataRetention($registry);

// -------------------------------------------------------
// Helper: run a task and log the result
// -------------------------------------------------------
$run = function (string $taskKey, string $taskLabel, callable $fn) use ($cron): void {
	try {
		$rows = $fn();
		$cron->log($taskKey, $rows);
		echo '[' . date('Y-m-d H:i:s') . '] ' . $taskLabel . ': ' . $rows . " row(s) affected\n";
	} catch (Exception $e) {
		$cron->log($taskKey, 0, 'error', $e->getMessage());
		echo '[' . date('Y-m-d H:i:s') . '] ' . $taskLabel . ' ERROR: ' . $e->getMessage() . "\n";
	}
};

// -------------------------------------------------------
// Run tasks
// -------------------------------------------------------
echo '[' . date('Y-m-d H:i:s') . '] NivoCart data-retention cron started.' . "\n";

$run('purge_ip_columns', 'Anonymise registration IPs (>90 days)', [$model, 'purgeIpColumns']);
$run('purge_ip_log', 'Delete login IP log rows (>90 days)', [$model, 'purgeIpLog']);
$run('purge_online_sessions', 'Delete stale online sessions (>2 hours)', [$model, 'purgeOnlineSessions']);
$run('purge_deleted_accounts', 'Hard-delete soft-deleted accounts (>2y)', [$model, 'purgeDeletedAccounts']);

echo '[' . date('Y-m-d H:i:s') . '] Data-retention cron completed.' . "\n";
