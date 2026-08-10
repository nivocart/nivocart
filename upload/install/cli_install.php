<?php
/**
 * NivoCart CLI Installer
 *
 * Usage (Linux/macOS):
 *
 *   cd install
 *   php cli_install.php install \
 *     --db_hostname localhost \
 *     --db_username root \
 *     --db_password password \
 *     --db_database nivocart \
 *     --db_port 3306 \
 *     --username admin \
 *     --password admin \
 *     --email youremail@example.com \
 *     --agree_tnc yes \
 *     --http_server http://localhost/nivocart/
 *
 * Optional flags:
 *   --demo_data yes   Install with demo data (default: clean install)
 *   --maintenance yes Enable maintenance mode after install
 *   --rewrite yes     Enable SEO URL rewriting via .htaccess
 */

ini_set('display_errors', '1');

error_reporting(E_ALL);

// -----------------------------------------------------------------------
// Bootstrap
// -----------------------------------------------------------------------
define('DIR_APPLICATION', str_replace('\\', '/', realpath(__DIR__)) . '/');
define('DIR_SYSTEM', str_replace('\\', '/', realpath(__DIR__ . '/../system')) . '/');
define('DIR_NIVOCART', str_replace('\\', '/', realpath(__DIR__ . '/..')) . '/');
define('DIR_DATABASE', DIR_SYSTEM . 'database/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');

define('NC_VERSION', '2.2.0');

require_once DIR_SYSTEM . 'startup.php';

$registry = new Registry();
$loader = new Loader($registry);
$registry->set('load', $loader);

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// -----------------------------------------------------------------------
// Entry point
// -----------------------------------------------------------------------
$argv = $_SERVER['argv'];
$script = array_shift($argv);
$subcommand = array_shift($argv) ?? '';

match ($subcommand) {
    'install' => runInstall($argv),
    default   => printUsage(),
};

// -----------------------------------------------------------------------
// Functions
// -----------------------------------------------------------------------

function runInstall(array $argv): void {
    try {
        $options = parseOptions($argv);

        define('HTTP_NIVOCART', $options['http_server']);

        [$valid, $missing] = validateOptions($options);

        if (!$valid) {
            echo 'FAILED! The following inputs were missing or invalid: ' . implode(', ', $missing) . "\n\n";
            exit(1);
        }

        [$passed, $error] = checkRequirements();

        if (!$passed) {
            echo 'FAILED! Pre-installation check failed: ' . $error . "\n\n";
            exit(1);
        }

        setupDb($options);
        writeConfigFiles($options);
        setupHtaccess($options);
        setDirectoryPermissions();

        echo "SUCCESS: NivoCart was successfully installed!\n";
        echo 'Store: ' . $options['http_server'] . "\n";
        echo 'Admin: ' . $options['http_server'] . "admin/\n\n";

    } catch (ErrorException $e) {
        echo 'FAILED: ' . $e->getMessage() . "\n";
        exit(1);
    }
}

function printUsage(): void {
    echo "NivoCart CLI Installer\n";
    echo "======================\n\n";
    echo "Usage:\n";
    echo "  php cli_install.php install [options]\n\n";
    echo "Required options:\n";
    echo "  --db_hostname   Database hostname      (default: localhost)\n";
    echo "  --db_username   Database username\n";
    echo "  --db_password   Database password\n";
    echo "  --db_database   Database name          (default: nivocart)\n";
    echo "  --db_port       Database port          (default: 3306)\n";
    echo "  --username      Admin username         (default: admin)\n";
    echo "  --password      Admin password\n";
    echo "  --email         Admin email address\n";
    echo "  --agree_tnc     Agree to terms (yes)\n";
    echo "  --http_server   Store URL              (e.g. http://localhost/nivocart/)\n\n";
    echo "Optional options:\n";
    echo "  --demo_data     yes = install with demo data (default: clean install)\n";
    echo "  --maintenance   yes = enable maintenance mode after install\n";
    echo "  --rewrite       yes = enable SEO URL rewriting via .htaccess\n\n";
}

function parseOptions(array $argv): array {
    $defaults = [
        'db_hostname' => 'localhost',
        'db_database' => 'nivocart',
        'db_prefix'   => 'nc_',
        'db_driver'   => 'mysqli',
        'db_port'     => '3306',
        'username'    => 'admin',
        'agree_tnc'   => 'no',
    ];

    $options = [];
    $total = count($argv);

    for ($i = 0; $i < $total; $i += 2) {
        if (!preg_match('/^--(.+)$/', $argv[$i], $match)) {
            throw new \Exception("Unexpected argument '{$argv[$i]}' — expected an option name starting with '--'");
        }

        $options[$match[1]] = $argv[$i + 1] ?? '';
    }

    return array_merge($defaults, $options);
}

function validateOptions(array $options): array {
    $required = [
        'db_hostname', 'db_username', 'db_password', 'db_database',
        'db_port', 'db_prefix', 'username', 'password', 'email',
        'agree_tnc', 'http_server',
    ];

    $missing = array_filter($required, fn($key) => !array_key_exists($key, $options) || $options[$key] === '');

    if (strtolower($options['agree_tnc'] ?? '') !== 'yes') {
        $missing[] = 'agree_tnc (must be "yes")';
    }

    // Ensure trailing slash on http_server
    if (isset($options['http_server']) && !str_ends_with($options['http_server'], '/')) {
        $options['http_server'] .= '/';
    }

    return [empty($missing), array_values($missing)];
}

function checkRequirements(): array {
    $checks = [
        fn() => version_compare(phpversion(), '8.1.0', '>=') ?: 'PHP 8.1 or above is required.',
        fn() => (bool)ini_get('file_uploads') ?: 'file_uploads must be enabled.',
        fn() => !ini_get('session.auto_start') ?: 'session.auto_start must be disabled.',
        fn() => extension_loaded('mysqli') ?: 'MySQLi extension is required.',
        fn() => extension_loaded('gd') ?: 'GD extension is required.',
        fn() => extension_loaded('curl') ?: 'cURL extension is required.',
        fn() => extension_loaded('dom') ?: 'DOM extension is required.',
        fn() => extension_loaded('xml') ?: 'XML extension is required.',
        fn() => function_exists('openssl_encrypt') ?: 'OpenSSL extension is required.',
        fn() => extension_loaded('zlib') ?: 'Zlib extension is required.',
        fn() => extension_loaded('zip') ?: 'ZIP extension is required.',
        fn() => extension_loaded('mbstring') ?: 'mbstring extension is required.',
        fn() => is_writable(DIR_NIVOCART . 'config.php') ?: 'config.php must be writable.',
        fn() => is_writable(DIR_NIVOCART . 'admin/config.php') ?: 'admin/config.php must be writable.',
        fn() => is_writable(DIR_SYSTEM . 'cache') ?: 'system/cache must be writable.',
        fn() => is_writable(DIR_SYSTEM . 'logs') ?: 'system/logs must be writable.',
        fn() => is_writable(DIR_SYSTEM . 'upload') ?: 'system/upload must be writable.',
        fn() => is_writable(DIR_NIVOCART . 'download') ?: 'download/ must be writable.',
        fn() => is_writable(DIR_NIVOCART . 'image') ?: 'image/ must be writable.',
        fn() => is_writable(DIR_NIVOCART . 'image/cache') ?: 'image/cache must be writable.',
        fn() => is_writable(DIR_NIVOCART . 'image/data') ?: 'image/data must be writable.',
    ];

    foreach ($checks as $check) {
        $result = $check();

        if ($result !== true) {
            return [false, $result];
        }
    }

    return [true, null];
}

function setupDb(array $options): void {
    $db = new DB(
        $options['db_driver'],
        htmlspecialchars_decode($options['db_hostname']),
        htmlspecialchars_decode($options['db_username']),
        htmlspecialchars_decode($options['db_password']),
        htmlspecialchars_decode($options['db_database']),
        $options['db_port']
    );

    // Choose SQL file — match web installer behaviour
    $sql_file = isset($options['demo_data']) && strtolower($options['demo_data']) === 'yes' ? DIR_APPLICATION . 'nivocart.sql' : DIR_APPLICATION . 'nivocart-clean.sql';

    if (!file_exists($sql_file)) {
        exit('Could not load SQL file: ' . $sql_file);
    }

    clearstatcache();
    set_time_limit(60);

    $lines = file($sql_file);

    if ($lines) {
        $db->query("SET CHARACTER SET utf8mb4");
        $db->query("SET @@session.sql_mode = ''");

        $sql = '';

        foreach ($lines as $line) {
            if (!$line || mb_substr($line, 0, 2, 'UTF-8') === '--' || mb_substr($line, 0, 1, 'UTF-8') === '#') {
                continue;
            }

            $sql .= $line;

            if (preg_match('/;\s*$/', $line)) {
                $sql = str_replace('DROP TABLE IF EXISTS `nc_', 'DROP TABLE IF EXISTS `' . $options['db_prefix'], $sql);
                $sql = str_replace('CREATE TABLE `nc_', 'CREATE TABLE `' . $options['db_prefix'], $sql);
                $sql = str_replace('INSERT INTO `nc_', 'INSERT INTO `' . $options['db_prefix'], $sql);

                $db->query($sql);
                $sql = '';
            }
        }

        // Admin user
        $salt = mb_substr(md5(uniqid(rand(), true)), 0, 9, 'UTF-8');

        $db->query("DELETE FROM `" . $options['db_prefix'] . "user` WHERE user_id = '1'");
        $db->query("INSERT INTO `" . $options['db_prefix'] . "user` SET
            user_id = '1',
            user_group_id = '1',
            username = '" . $db->escape($options['username']) . "',
            salt = '" . $db->escape($salt) . "',
            password = '" . $db->escape(sha1($salt . sha1($salt . sha1($options['password'])))) . "',
            status = '1',
            email = '" . $db->escape($options['email']) . "',
            date_added = NOW()"
        );

        // Settings
        $settings = [
            'config_email' => $options['email'],
            'config_url' => HTTP_NIVOCART,
            'config_encryption'  => bin2hex(random_bytes(16)),
            'config_maintenance' => (strtolower($options['maintenance'] ?? '') === 'yes') ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            $db->query("DELETE FROM `" . $options['db_prefix'] . "setting` WHERE `key` = '" . $db->escape($key) . "'");
            $db->query("INSERT INTO `" . $options['db_prefix'] . "setting` SET `group` = 'config', `key` = '" . $db->escape($key) . "', `value` = '" . $db->escape($value) . "'");
        }

        $db->query("INSERT INTO `" . $options['db_prefix'] . "version` SET `version` = '" . $db->escape(NC_VERSION) . "', date_added = NOW()");
    }
}

function writeConfigFiles(array $options): void {
    $esc = function (string $value): string {
        return addslashes(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
    };

    $server = HTTP_NIVOCART;

    // ------------------------------------------------------------------
    // Catalog config.php
    // ------------------------------------------------------------------
    $catalog = '<?php' . "\n";
    $catalog .= '// HTTP' . "\n";
    $catalog .= "define('HTTP_SERVER', '{$server}');\n";
    $catalog .= "define('HTTP_IMAGE', '{$server}image/');\n\n";
    $catalog .= '// HTTPS' . "\n";
    $catalog .= "define('HTTPS_SERVER', '{$server}');\n";
    $catalog .= "define('HTTPS_IMAGE', '{$server}image/');\n\n";
    $catalog .= '// DIR' . "\n";
    $catalog .= "define('DIR_APPLICATION', '" . DIR_NIVOCART . "catalog/');\n";
    $catalog .= "define('DIR_SYSTEM', '" . DIR_NIVOCART . "system/');\n";
    $catalog .= "define('DIR_DATABASE', '" . DIR_NIVOCART . "system/database/');\n";
    $catalog .= "define('DIR_LANGUAGE', '" . DIR_NIVOCART . "catalog/language/');\n";
    $catalog .= "define('DIR_TEMPLATE', '" . DIR_NIVOCART . "catalog/view/theme/');\n";
    $catalog .= "define('DIR_CONFIG', '" . DIR_NIVOCART . "system/config/');\n";
    $catalog .= "define('DIR_IMAGE', '" . DIR_NIVOCART . "image/');\n";
    $catalog .= "define('DIR_CACHE', '" . DIR_NIVOCART . "system/cache/');\n";
    $catalog .= "define('DIR_UPLOAD', '" . DIR_NIVOCART . "system/upload/');\n";
    $catalog .= "define('DIR_DOWNLOAD', '" . DIR_NIVOCART . "download/');\n";
    $catalog .= "define('DIR_LOGS', '" . DIR_NIVOCART . "system/logs/');\n\n";
    $catalog .= '// DB' . "\n";
    $catalog .= "define('DB_DRIVER', '" . $esc($options['db_driver']) . "');\n";
    $catalog .= "define('DB_HOSTNAME', '" . $esc($options['db_hostname']) . "');\n";
    $catalog .= "define('DB_USERNAME', '" . $esc($options['db_username']) . "');\n";
    $catalog .= "define('DB_PASSWORD', '" . $esc($options['db_password']) . "');\n";
    $catalog .= "define('DB_DATABASE', '" . $esc($options['db_database']) . "');\n";
    $catalog .= "define('DB_PORT', '" . $esc($options['db_port']) . "');\n";
    $catalog .= "define('DB_PREFIX', '" . $esc($options['db_prefix']) . "');\n";

    file_put_contents(DIR_NIVOCART . 'config.php', $catalog);

    // ------------------------------------------------------------------
    // Admin config.php
    // ------------------------------------------------------------------
    $admin = '<?php' . "\n";
    $admin .= '// HTTP' . "\n";
    $admin .= "define('HTTP_SERVER', '{$server}admin/');\n";
    $admin .= "define('HTTP_IMAGE', '{$server}image/');\n";
    $admin .= "define('HTTP_CATALOG', '{$server}');\n\n";
    $admin .= '// HTTPS' . "\n";
    $admin .= "define('HTTPS_SERVER', '{$server}admin/');\n";
    $admin .= "define('HTTPS_IMAGE', '{$server}image/');\n";
    $admin .= "define('HTTPS_CATALOG', '{$server}');\n\n";
    $admin .= '// DIR' . "\n";
    $admin .= "define('DIR_APPLICATION', '" . DIR_NIVOCART . "admin/');\n";
    $admin .= "define('DIR_SYSTEM', '" . DIR_NIVOCART . "system/');\n";
    $admin .= "define('DIR_DATABASE', '" . DIR_NIVOCART . "system/database/');\n";
    $admin .= "define('DIR_LANGUAGE', '" . DIR_NIVOCART . "admin/language/');\n";
    $admin .= "define('DIR_TEMPLATE', '" . DIR_NIVOCART . "admin/view/template/');\n";
    $admin .= "define('DIR_CONFIG', '" . DIR_NIVOCART . "system/config/');\n";
    $admin .= "define('DIR_IMAGE', '" . DIR_NIVOCART . "image/');\n";
    $admin .= "define('DIR_CACHE', '" . DIR_NIVOCART . "system/cache/');\n";
    $admin .= "define('DIR_UPLOAD', '" . DIR_NIVOCART . "system/upload/');\n";
    $admin .= "define('DIR_DOWNLOAD', '" . DIR_NIVOCART . "download/');\n";
    $admin .= "define('DIR_LOGS', '" . DIR_NIVOCART . "system/logs/');\n";
    $admin .= "define('DIR_CATALOG', '" . DIR_NIVOCART . "catalog/');\n\n";
    $admin .= '// DB' . "\n";
    $admin .= "define('DB_DRIVER', '" . $esc($options['db_driver']) . "');\n";
    $admin .= "define('DB_HOSTNAME', '" . $esc($options['db_hostname']) . "');\n";
    $admin .= "define('DB_USERNAME', '" . $esc($options['db_username']) . "');\n";
    $admin .= "define('DB_PASSWORD', '" . $esc($options['db_password']) . "');\n";
    $admin .= "define('DB_DATABASE', '" . $esc($options['db_database']) . "');\n";
    $admin .= "define('DB_PORT', '" . $esc($options['db_port']) . "');\n";
    $admin .= "define('DB_PREFIX', '" . $esc($options['db_prefix']) . "');\n";

    file_put_contents(DIR_NIVOCART . 'admin/config.php', $admin);
}

function setupHtaccess(array $options): void {
    if (strtolower($options['rewrite'] ?? '') !== 'yes') {
        return;
    }

    $htaccess_txt = DIR_NIVOCART . '.htaccess.txt';
    $htaccess = DIR_NIVOCART . '.htaccess';

    if (!file_exists($htaccess_txt) || !is_writable($htaccess_txt)) {
        return;
    }

    $mod_rewrite = function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules(), true) : (strtolower($_SERVER['HTTP_MOD_REWRITE'] ?? '') === 'on' || strtolower(getenv('HTTP_MOD_REWRITE') ?: '') === 'on');

    if (!$mod_rewrite) {
        return;
    }

    $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $path = $base ? $base . '/' : '/';
    $document = str_replace('RewriteBase /', 'RewriteBase ' . $path, file_get_contents($htaccess_txt));

    file_put_contents($htaccess_txt, $document);
    rename($htaccess_txt, $htaccess);
    chmod($htaccess, 0644);

    clearstatcache();
}

function setDirectoryPermissions(): void {
    $dirs = [
        DIR_NIVOCART . 'image/',
        DIR_NIVOCART . 'download/',
        DIR_SYSTEM . 'upload/',
        DIR_SYSTEM . 'cache/',
        DIR_SYSTEM . 'logs/',
    ];

    foreach ($dirs as $dir) {
        $dir = rtrim($dir, '/\\');

        if (!is_dir($dir)) {
            continue;
        }

        chmod($dir, 0755);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $entry) {
            chmod($entry->getPathname(), $entry->isDir() ? 0755 : 0644);
        }
    }
}
