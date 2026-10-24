<?php
/**
 * Class ControllerFeedPinterestFeed
 *
 * @package NivoCart
 */
class ControllerFeedPinterestFeed extends Controller {
	private const TAXONOMY_URL = 'https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt';

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
		$this->data['entry_extra_images'] = $this->language->get('entry_extra_images');
		$this->data['entry_category_default'] = $this->language->get('entry_category_default');
		$this->data['entry_category_map'] = $this->language->get('entry_category_map');
		$this->data['entry_cache'] = $this->language->get('entry_cache');

		$this->data['help_currency'] = $this->language->get('help_currency');
		$this->data['help_cache_hours'] = $this->language->get('help_cache_hours');
		$this->data['help_min_image'] = $this->language->get('help_min_image');
		$this->data['help_extra_images'] = $this->language->get('help_extra_images');
		$this->data['entry_video'] = $this->language->get('entry_video');
		$this->data['help_video'] = $this->language->get('help_video');
		$this->data['help_category'] = $this->language->get('help_category');
		$this->data['help_category_map'] = $this->language->get('help_category_map');

		$this->data['entry_taxonomy'] = $this->language->get('entry_taxonomy');
		$this->data['help_taxonomy'] = $this->language->get('help_taxonomy');
		$this->data['text_taxonomy_link'] = $this->language->get('text_taxonomy_link');
		$this->data['text_taxonomy_placeholder'] = $this->language->get('text_taxonomy_placeholder');
		$this->data['text_taxonomy_target'] = $this->language->get('text_taxonomy_target');
		$this->data['text_taxonomy_empty'] = $this->language->get('text_taxonomy_empty');
		$this->data['text_taxonomy_default'] = rtrim($this->language->get('entry_category_default'), ':');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		// Error
		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['error_currency'] = $this->error['currency'] ?? '';
		$this->data['error_cache_hours'] = $this->error['cache_hours'] ?? '';
		$this->data['error_min_image'] = $this->error['min_image'] ?? '';
		$this->data['error_extra_images'] = $this->error['extra_images'] ?? '';
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
		$this->data['taxonomy'] = str_replace('&amp;', '&', $this->url->link('feed/pinterest_feed/taxonomy', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['taxonomy_url'] = self::TAXONOMY_URL;

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

		if (isset($this->request->post['pinterest_feed_extra_images'])) {
			$this->data['pinterest_feed_extra_images'] = $this->request->post['pinterest_feed_extra_images'];
		} elseif ($this->config->get('pinterest_feed_extra_images') !== null) {
			$this->data['pinterest_feed_extra_images'] = $this->config->get('pinterest_feed_extra_images');
		} else {
			$this->data['pinterest_feed_extra_images'] = 3;
		}

		if (isset($this->request->post['pinterest_feed_video'])) {
			$this->data['pinterest_feed_video'] = $this->request->post['pinterest_feed_video'];
		} elseif ($this->config->get('pinterest_feed_video') !== null) {
			$this->data['pinterest_feed_video'] = $this->config->get('pinterest_feed_video');
		} else {
			$this->data['pinterest_feed_video'] = 1;
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

		foreach ($this->model_feed_pinterest_feed->getMappableCategories() as $category) {
			$this->data['categories'][] = [
				'category_id' => $category['category_id'],
				'name'        => $category['name'],
				'indent'      => $category['level'] ? '&nbsp;&nbsp;&nbsp;&nbsp;- ' : '',
				'level'       => $category['level'],
				'value'       => $saved[$category['category_id']] ?? '',
				'error'       => isset($this->error['category_map'][(int)$category['category_id']]) ? $this->language->get('error_category') : ''
			];
		}

		$this->data['data_feed'] = ($this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG) . 'index.php?route=feed/pinterest_feed';

		// Cache information written by the catalog feed
		$this->data['cache_info'] = '';
		$this->data['cache_skipped'] = '';
		$this->data['cache_videos'] = '';
		$this->data['variants_info'] = '';

		$meta_file = $this->getCacheDir() . 'pinterest_feed.json';

		if (is_file($meta_file)) {
			$meta = json_decode((string)file_get_contents($meta_file), true);

			if (is_array($meta)) {
				$skipped = ($meta['skipped_image'] ?? 0) + ($meta['skipped_price'] ?? 0) + ($meta['skipped_other'] ?? 0);

				$this->data['cache_info'] = sprintf($this->language->get('text_cache_info'), date('d/m/Y H:i', (int)($meta['generated'] ?? 0)), (int)($meta['items'] ?? 0), $skipped);
				$this->data['cache_skipped'] = sprintf($this->language->get('text_cache_skipped'), (int)($meta['skipped_image'] ?? 0), (int)($meta['skipped_price'] ?? 0), (int)($meta['skipped_other'] ?? 0));
				$this->data['cache_videos'] = sprintf($this->language->get('text_cache_videos'), (int)($meta['videos'] ?? 0), (int)($meta['videos_left_out'] ?? 0));
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

	/**
	 * Ajax search in the Google Product Taxonomy: returns up to 25 matching "ID - path" entries as JSON.
	 */
	public function taxonomy() {
		$this->language->load('feed/pinterest_feed');

		$json = [];

		$term = mb_strtolower(trim(html_entity_decode((string)($this->request->get['q'] ?? ''), ENT_QUOTES, 'UTF-8')), 'UTF-8');

		if (!$this->user->hasPermission('modify', 'feed/pinterest_feed')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif (mb_strlen($term, 'UTF-8') >= 2) {
			$rows = $this->getTaxonomy();

			if (!$rows) {
				$json['error'] = $this->language->get('error_taxonomy');
			} else {
				$terms = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY);
				$found = [];

				foreach ($rows as $row) {
					$path = mb_strtolower($row['path'], 'UTF-8');

					if (count($terms) === 1 && ctype_digit($terms[0])) {
						if (str_starts_with((string)$row['id'], $terms[0])) {
							$found[] = [0, strlen($row['path']), $row];
						}

						continue;
					}

					$all = true;

					foreach ($terms as $word) {
						if (mb_strpos($path, $word, 0, 'UTF-8') === false) {
							$all = false;

							break;
						}
					}

					if (!$all) {
						continue;
					}

					// Matches in the last part of the path (the category itself) come first
					$parts = explode(' > ', $path);
					$last = end($parts);
					$in_last = true;

					foreach ($terms as $word) {
						if (mb_strpos($last, $word, 0, 'UTF-8') === false) {
							$in_last = false;

							break;
						}
					}

					$found[] = [$in_last ? 0 : 1, strlen($row['path']), $row];
				}

				usort($found, fn($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

				$json['results'] = [];

				foreach (array_slice($found, 0, 25) as $item) {
					$json['results'][] = [
						'id'   => $item[2]['id'],
						'path' => $item[2]['path']
					];
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Reads the Google Product Taxonomy from the local copy, downloading it when it is missing or older than 30 days.
	 *
	 * @return array<int, array{id: int, path: string}>
	 */
	protected function getTaxonomy(): array {
		$file = $this->getCacheDir() . 'pinterest_taxonomy.txt';

		if (!is_file($file) || (time() - filemtime($file)) > 2592000) {
			$this->downloadTaxonomy($file);
		}

		if (!is_file($file)) {
			return [];
		}

		$rows = [];

		foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
			if (preg_match('/^(\d+) - (.+)$/u', $line, $match)) {
				$rows[] = [
					'id'   => (int)$match[1],
					'path' => trim($match[2])
				];
			}
		}

		return $rows;
	}

	protected function downloadTaxonomy(string $file): void {
		if (!function_exists('curl_init')) {
			return;
		}

		$curl = curl_init(self::TAXONOMY_URL);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);

		$body = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		unset($curl);

		if ($code === 200 && is_string($body) && strlen($body) > 10000 && str_contains($body, 'Animals & Pet Supplies')) {
			$tmp = $file . '.tmp';

			if (file_put_contents($tmp, $body) !== false) {
				rename($tmp, $file);
			}
		} elseif (is_file($file)) {
			// Keep the old copy and try again in a day
			touch($file, time() - 2592000 + 86400);
		}
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

		$extra_images = trim((string)($this->request->post['pinterest_feed_extra_images'] ?? ''));

		$this->request->post['pinterest_feed_extra_images'] = $extra_images;

		if (!ctype_digit($extra_images) || (int)$extra_images > 10) {
			$this->error['extra_images'] = $this->language->get('error_extra_images');
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
