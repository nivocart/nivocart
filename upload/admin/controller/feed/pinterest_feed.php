<?php
/**
 * Class ControllerFeedPinterestFeed
 *
 * @package NivoCart
 */
class ControllerFeedPinterestFeed extends Controller {
	private $error = [];

	public function index() {
		$this->language->load('feed/pinterest_feed');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('feed/pinterest_feed');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			$apply = isset($this->request->post['apply']);

			unset($this->request->post['apply']);

			$this->model_setting_setting->editSetting('pinterest_feed', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if ($apply) {
				$this->redirect($this->url->link('feed/pinterest_feed', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_clear_cache'] = $this->language->get('text_clear_cache');

		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_data_feed'] = $this->language->get('entry_data_feed');
		$this->data['entry_currency'] = $this->language->get('entry_currency');
		$this->data['entry_cache_hours'] = $this->language->get('entry_cache_hours');
		$this->data['entry_min_image'] = $this->language->get('entry_min_image');
		$this->data['entry_category_default'] = $this->language->get('entry_category_default');
		$this->data['entry_category_map'] = $this->language->get('entry_category_map');
		$this->data['entry_cache'] = $this->language->get('entry_cache');

		$this->data['help_currency'] = $this->language->get('help_currency');
		$this->data['help_cache_hours'] = $this->language->get('help_cache_hours');
		$this->data['help_min_image'] = $this->language->get('help_min_image');
		$this->data['help_category'] = $this->language->get('help_category');
		$this->data['help_category_map'] = $this->language->get('help_category_map');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		// Error
		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['error_currency'] = $this->error['currency'] ?? '';
		$this->data['error_cache_hours'] = $this->error['cache_hours'] ?? '';
		$this->data['error_min_image'] = $this->error['min_image'] ?? '';
		$this->data['error_category'] = $this->error['category'] ?? '';

		// Success
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		// Breadcrumbs
		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_feed'),
			'href'      => $this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('feed/pinterest_feed', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['action'] = $this->url->link('feed/pinterest_feed', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['clear'] = $this->url->link('feed/pinterest_feed/clear', 'token=' . $this->session->data['token'], 'SSL');

		// Settings
		if (isset($this->request->post['pinterest_feed_status'])) {
			$this->data['pinterest_feed_status'] = $this->request->post['pinterest_feed_status'];
		} else {
			$this->data['pinterest_feed_status'] = $this->config->get('pinterest_feed_status');
		}

		if (isset($this->request->post['pinterest_feed_currency'])) {
			$this->data['pinterest_feed_currency'] = $this->request->post['pinterest_feed_currency'];
		} elseif ($this->config->get('pinterest_feed_currency')) {
			$this->data['pinterest_feed_currency'] = $this->config->get('pinterest_feed_currency');
		} else {
			$this->data['pinterest_feed_currency'] = $this->config->get('config_currency');
		}

		if (isset($this->request->post['pinterest_feed_cache_hours'])) {
			$this->data['pinterest_feed_cache_hours'] = $this->request->post['pinterest_feed_cache_hours'];
		} elseif ($this->config->get('pinterest_feed_cache_hours')) {
			$this->data['pinterest_feed_cache_hours'] = $this->config->get('pinterest_feed_cache_hours');
		} else {
			$this->data['pinterest_feed_cache_hours'] = 24;
		}

		if (isset($this->request->post['pinterest_feed_min_image'])) {
			$this->data['pinterest_feed_min_image'] = $this->request->post['pinterest_feed_min_image'];
		} elseif ($this->config->get('pinterest_feed_min_image')) {
			$this->data['pinterest_feed_min_image'] = $this->config->get('pinterest_feed_min_image');
		} else {
			$this->data['pinterest_feed_min_image'] = 600;
		}

		if (isset($this->request->post['pinterest_feed_category_default'])) {
			$this->data['pinterest_feed_category_default'] = $this->request->post['pinterest_feed_category_default'];
		} else {
			$this->data['pinterest_feed_category_default'] = $this->config->get('pinterest_feed_category_default');
		}

		if (isset($this->request->post['pinterest_feed_category'])) {
			$saved = $this->request->post['pinterest_feed_category'];
		} else {
			$saved = $this->config->get('pinterest_feed_category');
		}

		if (!is_array($saved)) {
			$saved = [];
		}

		$this->data['categories'] = [];

		foreach ($this->model_feed_pinterest_feed->getTopCategories() as $category) {
			$this->data['categories'][] = [
				'category_id' => $category['category_id'],
				'name'        => $category['name'],
				'value'       => $saved[$category['category_id']] ?? '',
				'error'       => isset($this->error['category_map'][(int)$category['category_id']]) ? $this->language->get('error_category') : ''
			];
		}

		$this->data['data_feed'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . 'index.php?route=feed/pinterest_feed';

		// Cache information written by the catalog feed
		$this->data['cache_info'] = '';
		$this->data['cache_skipped'] = '';
		$this->data['variants_info'] = '';

		$meta_file = $this->getCacheDir() . 'pinterest_feed.json';

		if (is_file($meta_file)) {
			$meta = json_decode((string)file_get_contents($meta_file), true);

			if (is_array($meta)) {
				$skipped = ($meta['skipped_image'] ?? 0) + ($meta['skipped_price'] ?? 0) + ($meta['skipped_other'] ?? 0);

				$this->data['cache_info'] = sprintf($this->language->get('text_cache_info'), date('d/m/Y H:i', (int)($meta['generated'] ?? 0)), (int)($meta['items'] ?? 0), $skipped);
				$this->data['cache_skipped'] = sprintf($this->language->get('text_cache_skipped'), (int)($meta['skipped_image'] ?? 0), (int)($meta['skipped_price'] ?? 0), (int)($meta['skipped_other'] ?? 0));
				$this->data['variants_info'] = !empty($meta['variants']) ? $this->language->get('text_variants_on') : $this->language->get('text_variants_off');
			}
		}

		if (!$this->data['cache_info']) {
			$this->data['cache_info'] = $this->language->get('text_cache_none');
		}

		$this->template = 'feed/pinterest_feed.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	public function clear() {
		$this->language->load('feed/pinterest_feed');

		if (!$this->user->hasPermission('modify', 'feed/pinterest_feed')) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} else {
			$cache_dir = $this->getCacheDir();

			foreach (['pinterest_feed.xml', 'pinterest_feed.json'] as $file) {
				if (is_file($cache_dir . $file)) {
					unlink($cache_dir . $file);
				}
			}

			$this->session->data['success'] = $this->language->get('text_cache_cleared');
		}

		$this->redirect($this->url->link('feed/pinterest_feed', 'token=' . $this->session->data['token'], 'SSL'));
	}

	protected function getCacheDir(): string {
		return defined('DIR_CACHE') ? DIR_CACHE : DIR_SYSTEM . 'cache/';
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'feed/pinterest_feed')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!preg_match('/^[A-Za-z]{3}$/', (string)($this->request->post['pinterest_feed_currency'] ?? ''))) {
			$this->error['currency'] = $this->language->get('error_currency');
		} else {
			$this->request->post['pinterest_feed_currency'] = strtoupper($this->request->post['pinterest_feed_currency']);
		}

		$hours = (int)($this->request->post['pinterest_feed_cache_hours'] ?? 0);

		if ($hours < 1 || $hours > 168) {
			$this->error['cache_hours'] = $this->language->get('error_cache_hours');
		}

		$min_image = (int)($this->request->post['pinterest_feed_min_image'] ?? 0);

		if ($min_image < 100 || $min_image > 5000) {
			$this->error['min_image'] = $this->language->get('error_min_image');
		}

		// Google category IDs: digits only, surrounding spaces are removed
		$map = $this->request->post['pinterest_feed_category'] ?? [];

		if (!is_array($map)) {
			$map = [];
		}

		foreach ($map as $category_id => $value) {
			$value = trim((string)$value);

			$map[$category_id] = $value;

			if ($value !== '' && !ctype_digit($value)) {
				$this->error['category_map'][(int)$category_id] = true;
			}
		}

		$this->request->post['pinterest_feed_category'] = $map;

		$default = trim((string)($this->request->post['pinterest_feed_category_default'] ?? ''));

		$this->request->post['pinterest_feed_category_default'] = $default;

		if ($default !== '' && !ctype_digit($default)) {
			$this->error['category'] = $this->language->get('error_category');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}
}
