<?php
/**
 * Class ControllerToolConfiguration
 *
 * @package NivoCart
 */
class ControllerToolConfiguration extends Controller {
	private $error = [];
	private $name = 'configuration';

	public function index() {
		$this->language->load('tool/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->getConfiguration();
	}

	public function getConfiguration() {
		$this->language->load('tool/' . $this->name);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_on'] = $this->language->get('text_on');
		$this->data['text_off'] = $this->language->get('text_off');
		$this->data['text_system_core'] = $this->language->get('text_system_core');
		$this->data['text_version'] = sprintf($this->language->get('text_version'), VERSION);
		$this->data['text_system_name'] = $this->language->get('text_system_name');
		$this->data['text_revision'] = sprintf($this->language->get('text_revision'), RELEASED);
		$this->data['text_theme'] = $this->language->get('text_theme');
		$this->data['text_timezone'] = $this->language->get('text_timezone');
		$this->data['text_phptime'] = $this->language->get('text_phptime');
		$this->data['text_dbtime'] = $this->language->get('text_dbtime');
		$this->data['text_dbname'] = $this->language->get('text_dbname');
		$this->data['text_dbengine'] = $this->language->get('text_dbengine');
		$this->data['text_rewrite_base'] = $this->language->get('text_rewrite_base');
		$this->data['text_present'] = $this->language->get('text_present');
		$this->data['text_missing'] = $this->language->get('text_missing');
		$this->data['text_unknown'] = $this->language->get('text_unknown');
		$this->data['text_store_info'] = $this->language->get('text_store_info');
		$this->data['text_setting_info'] = $this->language->get('text_setting_info');
		$this->data['text_image_info'] = $this->language->get('text_image_info');
		$this->data['text_integrity_info'] = $this->language->get('text_integrity_info');
		$this->data['text_server_info'] = $this->language->get('text_server_info');

		$this->data['text_update_check'] = $this->language->get('text_update_check');
		$this->data['text_latest_version'] = $this->language->get('text_latest_version');
		$this->data['text_not_checked'] = $this->language->get('text_not_checked');
		$this->data['text_checking'] = $this->language->get('text_checking');
		$this->data['text_update_available'] = $this->language->get('text_update_available');
		$this->data['text_up_to_date'] = $this->language->get('text_up_to_date');
		$this->data['text_view_release'] = $this->language->get('text_view_release');
		$this->data['text_release_notes'] = $this->language->get('text_release_notes');
		$this->data['text_next_check'] = $this->language->get('text_next_check');
		$this->data['text_check_failed'] = $this->language->get('text_check_failed');

		$this->data['button_check_update'] = $this->language->get('button_check_update');

		$this->data['tab_store'] = $this->language->get('tab_store');
		$this->data['tab_setting'] = $this->language->get('tab_setting');
		$this->data['tab_image'] = $this->language->get('tab_image');
		$this->data['tab_integrity'] = $this->language->get('tab_integrity');
		$this->data['tab_server'] = $this->language->get('tab_server');

		$this->data['column_php'] = $this->language->get('column_php');
		$this->data['column_extension'] = $this->language->get('column_extension');
		$this->data['column_directories'] = $this->language->get('column_directories');
		$this->data['column_required'] = $this->language->get('column_required');
		$this->data['column_current'] = $this->language->get('column_current');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_gd_library'] = $this->language->get('column_gd_library');
		$this->data['column_php_library'] = $this->language->get('column_php_library');
		$this->data['column_database_files'] = $this->language->get('column_database_files');
		$this->data['column_engine_files'] = $this->language->get('column_engine_files');
		$this->data['column_helper_files'] = $this->language->get('column_helper_files');
		$this->data['column_library_files'] = $this->language->get('column_library_files');

		$this->data['text_phpversion'] = $this->language->get('text_phpversion');
		$this->data['text_registerglobals'] = $this->language->get('text_registerglobals');
		$this->data['text_magicquotes'] = $this->language->get('text_magicquotes');
		$this->data['text_fileuploads'] = $this->language->get('text_fileuploads');
		$this->data['text_autostart'] = $this->language->get('text_autostart');

		$this->data['text_mysql'] = $this->language->get('text_mysql');
		$this->data['text_gd'] = $this->language->get('text_gd');
		$this->data['text_curl'] = $this->language->get('text_curl');
		$this->data['text_dom'] = $this->language->get('text_dom');
		$this->data['text_xml'] = $this->language->get('text_xml');
		$this->data['text_mcrypt'] = $this->language->get('text_mcrypt');
		$this->data['text_openssl'] = $this->language->get('text_openssl');
		$this->data['text_zlib'] = $this->language->get('text_zlib');
		$this->data['text_zip'] = $this->language->get('text_zip');
		$this->data['text_mbstring'] = $this->language->get('text_mbstring');

		$this->data['button_close'] = $this->language->get('button_close');

		$this->data['close'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		// Template
		$this->data['templates'] = [];

		$themePath = DIR_CATALOG . 'view/theme/';

		if (is_dir($themePath)) {
			$iterator = new FilesystemIterator($themePath, FilesystemIterator::SKIP_DOTS);

			foreach ($iterator as $entry) {
				if ($entry->isDir()) {
					$this->data['templates'][] = $entry->getFilename();
				}
			}
		}

		if (isset($this->request->post['config_template'])) {
			$this->data['config_template'] = $this->request->post['config_template'];
		} else {
			$this->data['config_template'] = $this->config->get('config_template');
		}

		// -----------------------
		// Store Information
		// -----------------------
		// Time
		$date_timezone = ini_get('date.timezone');
		$default_timezone = date_default_timezone_get();

		if ($date_timezone) {
			$this->data['server_zone'] = $date_timezone;
		} elseif ($default_timezone) {
			$this->data['server_zone'] = $default_timezone;
		} else {
			$this->data['server_zone'] = $this->language->get('text_no_timezone');
		}

		$this->data['server_time'] = date('Y-m-d H:i:s');
		$this->data['database_time'] = $this->db->query("SELECT NOW() AS now")->row['now'];

		// Database
		$database_name = DB_DRIVER;

		if ($database_name === 'mysqli') {
			$this->data['database_name'] = 'MySQLi / MariaDB';
		} else {
			$this->data['database_name'] = 'MySQL';
		}

		// Engines
		$this->load->model('tool/database');

		$engines = $this->model_tool_database->getEngines();

		foreach ($engines as $engine) {
			if ($engine === 'InnoDB') {
				$this->data['engine'] = true;
			} else {
				$this->data['engine'] = false;
			}
		}

		$this->data['text_innodb'] = 'InnoDB';
		$this->data['text_myisam'] = 'MyISAM';

		$this->load->model('tool/system');

		$this->data['base_path'] = $this->model_tool_system->getRewriteBase();

		// -----------------------
		// Settings Information
		// -----------------------
		// Check install directory exists
		if (is_dir(dirname(DIR_APPLICATION) . '/install')) {
			$this->data['error_install'] = $this->language->get('error_install');
		} else {
			$this->data['error_install'] = '';
		}

		// Check write permissions
		$this->data['cache'] = DIR_SYSTEM . 'cache';
		$this->data['logs'] = DIR_SYSTEM . 'logs';
		$this->data['upload'] = DIR_SYSTEM . 'upload';
		$this->data['download'] = DIR_DOWNLOAD;
		$this->data['image'] = DIR_IMAGE;
		$this->data['image_cache'] = DIR_IMAGE . 'cache';
		$this->data['image_data'] = DIR_IMAGE . 'data';

		// -----------------------
		// Images Information
		// -----------------------
		// Images
		if (extension_loaded('gd')) {
			$gd_infos = gd_info();

			$php_gif = (imagetypes() & IMG_GIF) ? true : false;
			$php_jpg = (imagetypes() & IMG_JPG) ? true : false;
			$php_png = (imagetypes() & IMG_PNG) ? true : false;
			$php_wbmp = (imagetypes() & IMG_WBMP) ? true : false;
			$php_xpm = (imagetypes() & IMG_XPM) ? true : false;

			if (phpversion() >= '7.1.0') {
				$php_webp = (imagetypes() & IMG_WEBP) ? true : false;
			} else {
				$php_webp = false;
			}

			if (phpversion() >= '7.2.0') {
				$php_bmp = (imagetypes() & IMG_BMP) ? true : false;
			} else {
				$php_bmp = false;
			}

			$gd_loaded = true;

		} else {
			$gd_infos = '';

			$php_gif = false;
			$php_jpg = false;
			$php_png = false;
			$php_wbmp = false;
			$php_xpm = false;
			$php_webp = false;
			$php_bmp = false;

			$gd_loaded = false;
		}

		$this->data['gd_infos'] = $gd_infos;

		$this->data['php_bmp'] = $php_bmp;
		$this->data['php_gif'] = $php_gif;
		$this->data['php_jpg'] = $php_jpg;
		$this->data['php_png'] = $php_png;
		$this->data['php_wbmp'] = $php_wbmp;
		$this->data['php_xpm'] = $php_xpm;
		$this->data['php_webp'] = $php_webp;

		$this->data['gd_loaded'] = $gd_loaded;

		// -----------------------
		// System Files Integrity
		// -----------------------
		// Ensure correct slashes are used
		$ds = function (string $path): string {
			return str_replace('\\', '/', $path);
		};

		// Scan existing PHP files
		$integrityPaths = [
			'databases' => DIR_SYSTEM . 'database/',
			'engines'   => DIR_SYSTEM . 'engine/',
			'helpers'   => DIR_SYSTEM . 'helper/',
			'libraries' => DIR_SYSTEM . 'library/',
		];

		foreach ($integrityPaths as $key => $path) {
			$this->data[$key] = $this->scanPhpFiles($path);
		}

		// Set comparison arrays
		$this->data['database_files'] = [
			'mysqli' => $ds(DIR_SYSTEM . 'database/mysqli.php')
		];

		$this->data['engine_files'] = [
			'action'     => $ds(DIR_SYSTEM . 'engine/action.php'),
			'controller' => $ds(DIR_SYSTEM . 'engine/controller.php'),
			'front'      => $ds(DIR_SYSTEM . 'engine/front.php'),
			'loader'     => $ds(DIR_SYSTEM . 'engine/loader.php'),
			'model'      => $ds(DIR_SYSTEM . 'engine/model.php'),
			'registry'   => $ds(DIR_SYSTEM . 'engine/registry.php')
		];

		$this->data['helper_files'] = [
			'agent'  => $ds(DIR_SYSTEM . 'helper/agent.php'),
			'minify' => $ds(DIR_SYSTEM . 'helper/minify.php'),
			'pdf'    => $ds(DIR_SYSTEM . 'helper/pdf.php'),
			'totals' => $ds(DIR_SYSTEM . 'helper/totals.php'),
			'vat'    => $ds(DIR_SYSTEM . 'helper/vat.php')
		];

		$this->data['library_files'] = [
			'affiliate'  => $ds(DIR_SYSTEM . 'library/affiliate.php'),
			'browser'    => $ds(DIR_SYSTEM . 'library/browser.php'),
			'cache'      => $ds(DIR_SYSTEM . 'library/cache.php'),
			'captcha'    => $ds(DIR_SYSTEM . 'library/captcha.php'),
			'cart'       => $ds(DIR_SYSTEM . 'library/cart.php'),
			'config'     => $ds(DIR_SYSTEM . 'library/config.php'),
			'currency'   => $ds(DIR_SYSTEM . 'library/currency.php'),
			'customer'   => $ds(DIR_SYSTEM . 'library/customer.php'),
			'db'         => $ds(DIR_SYSTEM . 'library/db.php'),
			'dbmemory'   => $ds(DIR_SYSTEM . 'library/dbmemory.php'),
			'document'   => $ds(DIR_SYSTEM . 'library/document.php'),
			'encryption' => $ds(DIR_SYSTEM . 'library/encryption.php'),
			'image'      => $ds(DIR_SYSTEM . 'library/image.php'),
			'language'   => $ds(DIR_SYSTEM . 'library/language.php'),
			'length'     => $ds(DIR_SYSTEM . 'library/length.php'),
			'log'        => $ds(DIR_SYSTEM . 'library/log.php'),
			'mail'       => $ds(DIR_SYSTEM . 'library/mail.php'),
			'offer'      => $ds(DIR_SYSTEM . 'library/offer.php'),
			'pagination' => $ds(DIR_SYSTEM . 'library/pagination.php'),
			'request'    => $ds(DIR_SYSTEM . 'library/request.php'),
			'response'   => $ds(DIR_SYSTEM . 'library/response.php'),
			'session'    => $ds(DIR_SYSTEM . 'library/session.php'),
			'tax'        => $ds(DIR_SYSTEM . 'library/tax.php'),
			'template'   => $ds(DIR_SYSTEM . 'library/template.php'),
			'url'        => $ds(DIR_SYSTEM . 'library/url.php'),
			'user'       => $ds(DIR_SYSTEM . 'library/user.php'),
			'weight'     => $ds(DIR_SYSTEM . 'library/weight.php')
		];

		// -----------------------
		// Server Information
		// -----------------------
		$server_requests = [
			'PHP_SELF',
			'GATEWAY_INTERFACE',
			'SERVER_ADDR',
			'SERVER_NAME',
			'SERVER_SOFTWARE',
			'SERVER_PROTOCOL',
			'DOCUMENT_ROOT',
			'HTTP_ACCEPT',
			'HTTP_ACCEPT_CHARSET',
			'HTTP_ACCEPT_ENCODING',
			'HTTP_ACCEPT_LANGUAGE',
			'HTTP_CONNECTION',
			'HTTP_HOST',
			'HTTPS',
			'SCRIPT_FILENAME',
			'SERVER_ADMIN',
			'SERVER_PORT'
		];

		foreach ($server_requests as $argument) {
			if (isset($_SERVER[$argument])) {
				$response = $_SERVER[$argument];
			} else {
				$response = '';
			}

			$this->data['server_responses'][] = [
				'request'  => $argument,
				'response' => $response
			];
		}

		// -----------------------
		// Update Check
		// -----------------------
		$this->load->model('tool/system');

		$cache_age = $this->model_tool_system->getUpdateCacheAge();

		// Pass remaining cooldown in seconds (0 means button is active)
		$this->data['update_cooldown'] = ($cache_age !== null && $cache_age < 86400) ? (86400 - $cache_age) : 0;

		$this->data['update_check_url'] = $this->url->link('tool/configuration/checkUpdate', 'token=' . $this->session->data['token'], 'SSL');

		// Render
		$this->template = 'tool/' . $this->name . '.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'tool/configuration')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}

	/**
	 * Scan a directory and return full paths of all .php files found
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	private function scanPhpFiles(string $path): array {
		$results = [];

		if (!is_dir($path)) {
			return $results;
		}

		$iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);

		foreach ($iterator as $entry) {
			if ($entry->isFile() && $entry->getExtension() === 'php') {
				$results[] = str_replace('\\', '/', $entry->getPathname());
			}
		}

		return $results;
	}

	/**
	 * Check for update — AJAX endpoint
	 *
	 * @return void
	 */
	public function checkUpdate(): void {
		$this->language->load('tool/' . $this->name);

		if (!$this->user->hasPermission('modify', 'tool/configuration')) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(['status' => 'error', 'message' => $this->language->get('error_permission')]));
			return;
		}

		$this->load->model('tool/system');

		$result = $this->model_tool_system->checkForUpdate();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($result));
	}
}
