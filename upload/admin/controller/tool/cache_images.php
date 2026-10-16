<?php
/**
 * Class ControllerToolCacheImages
 *
 * @package NivoCart
 */
class ControllerToolCacheImages extends Controller {
	private $error = [];

	private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
	private const LIMIT = 100;

	public function index() {
		$this->language->load('tool/cache_images');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/cache_images', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$texts = [
			'text_no_results', 'text_total_size', 'text_summary', 'text_files', 'text_oldest', 'text_newest',
			'text_older_30', 'text_older_60', 'text_older_90', 'text_orphans', 'text_legacy', 'text_dimensions',
			'text_none', 'text_status_ok', 'text_status_orphan', 'text_status_legacy', 'text_bulk', 'text_bulk_help',
			'text_older_than', 'text_days', 'text_all', 'text_filter_orphan', 'text_filter_legacy',
			'text_confirm_title', 'text_confirm_selected', 'text_confirm_older', 'text_confirm_orphans',
			'text_confirm_legacy', 'text_confirm_all', 'text_nothing_selected',
			'column_name', 'column_size', 'column_modified', 'column_status',
			'button_delete', 'button_cancel', 'button_filter', 'button_reset', 'button_delete_older',
			'button_delete_orphans', 'button_delete_legacy', 'button_delete_all'
		];

		foreach ($texts as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		// Request values
		$filter_dimension = isset($this->request->get['filter_dimension']) ? (string)$this->request->get['filter_dimension'] : '';
		$filter_days = isset($this->request->get['filter_days']) ? max(0, (int)$this->request->get['filter_days']) : 0;
		$filter_status = isset($this->request->get['filter_status']) ? (string)$this->request->get['filter_status'] : '';

		if (!in_array($filter_status, ['', 'orphan', 'legacy'], true)) {
			$filter_status = '';
		}

		$sort = isset($this->request->get['sort']) ? (string)$this->request->get['sort'] : 'date';

		if (!in_array($sort, ['name', 'size', 'date'], true)) {
			$sort = 'date';
		}

		$order = (isset($this->request->get['order']) && $this->request->get['order'] === 'DESC') ? 'DESC' : 'ASC';
		$page = isset($this->request->get['page']) ? max(1, (int)$this->request->get['page']) : 1;

		// Scan the cache once, summarise everything, then filter
		$files = $this->scan();
		$now = time();

		$summary = [
			'files'    => count($files),
			'size'     => 0,
			'oldest'   => 0,
			'newest'   => 0,
			'older_30' => 0,
			'older_60' => 0,
			'older_90' => 0,
			'orphans'  => [0, 0],
			'legacy'   => [0, 0]
		];

		$dimensions = [];

		foreach ($files as $file) {
			$summary['size'] += $file['size'];

			if ($summary['oldest'] === 0 || $file['time'] < $summary['oldest']) {
				$summary['oldest'] = $file['time'];
			}

			if ($file['time'] > $summary['newest']) {
				$summary['newest'] = $file['time'];
			}

			$age = $now - $file['time'];

			if ($age > 30 * 86400) {
				$summary['older_30']++;
			}

			if ($age > 60 * 86400) {
				$summary['older_60']++;
			}

			if ($age > 90 * 86400) {
				$summary['older_90']++;
			}

			if ($file['orphan']) {
				$summary['orphans'][0]++;
				$summary['orphans'][1] += $file['size'];
			}

			if (!$file['webp']) {
				$summary['legacy'][0]++;
				$summary['legacy'][1] += $file['size'];
			}

			if (!isset($dimensions[$file['dim']])) {
				$dimensions[$file['dim']] = [0, 0];
			}

			$dimensions[$file['dim']][0]++;
			$dimensions[$file['dim']][1] += $file['size'];
		}

		uasort($dimensions, function ($a, $b) {
			return $b[0] <=> $a[0];
		});

		$this->data['summary'] = [
			'files'    => number_format($summary['files']),
			'size'     => $this->formatSize($summary['size']),
			'oldest'   => $summary['oldest'] ? date('Y-m-d H:i:s', $summary['oldest']) : '',
			'newest'   => $summary['newest'] ? date('Y-m-d H:i:s', $summary['newest']) : '',
			'older_30' => number_format($summary['older_30']),
			'older_60' => number_format($summary['older_60']),
			'older_90' => number_format($summary['older_90']),
			'orphans'  => number_format($summary['orphans'][0]) . ' (' . $this->formatSize($summary['orphans'][1]) . ')',
			'legacy'   => number_format($summary['legacy'][0]) . ' (' . $this->formatSize($summary['legacy'][1]) . ')'
		];

		$this->data['dimensions'] = [];

		foreach ($dimensions as $dim => $values) {
			$this->data['dimensions'][] = [
				'name'  => $dim,
				'files' => number_format($values[0]),
				'size'  => $this->formatSize($values[1]),
				'href'  => $this->url->link('tool/cache_images', 'token=' . $this->session->data['token'] . '&filter_dimension=' . urlencode($dim), 'SSL')
			];
		}

		// Apply filters
		$cutoff = $filter_days > 0 ? $now - ($filter_days * 86400) : 0;

		$filtered = [];

		foreach ($files as $file) {
			if ($filter_dimension !== '' && $file['dim'] !== $filter_dimension) {
				continue;
			}

			if ($cutoff && $file['time'] >= $cutoff) {
				continue;
			}

			if ($filter_status === 'orphan' && !$file['orphan']) {
				continue;
			}

			if ($filter_status === 'legacy' && $file['webp']) {
				continue;
			}

			$filtered[] = $file;
		}

		unset($files);

		// Sort
		$key = ['name' => 'path', 'size' => 'size', 'date' => 'time'][$sort];
		$direction = $order === 'DESC' ? -1 : 1;

		usort($filtered, function ($a, $b) use ($key, $direction) {
			$result = $a[$key] <=> $b[$key];

			if ($result === 0) {
				$result = $a['path'] <=> $b['path'];
			}

			return $result * $direction;
		});

		$total = count($filtered);
		$filtered_size = 0;

		foreach ($filtered as $file) {
			$filtered_size += $file['size'];
		}

		$rows = array_slice($filtered, ($page - 1) * self::LIMIT, self::LIMIT);

		$this->data['cache_images'] = [];

		foreach ($rows as $file) {
			$status = [];

			if ($file['orphan']) {
				$status[] = $this->language->get('text_status_orphan');
			}

			if (!$file['webp']) {
				$status[] = $this->language->get('text_status_legacy');
			}

			$this->data['cache_images'][] = [
				'name'     => htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'),
				'size'     => $this->formatSize($file['size']),
				'modified' => date('Y-m-d H:i:s', $file['time']),
				'status'   => $status ? implode(', ', $status) : $this->language->get('text_status_ok'),
				'problem'  => !empty($status)
			];
		}

		$this->data['cache_total_size'] = $this->formatSize($filtered_size);
		$this->data['cache_total_files'] = number_format($total);

		// Filter values for the template
		$this->data['filter_dimension'] = $filter_dimension;
		$this->data['filter_days'] = $filter_days ? $filter_days : '';
		$this->data['filter_status'] = $filter_status;
		$this->data['filtered'] = ($filter_dimension !== '' || $filter_days > 0 || $filter_status !== '');
		$this->data['dimension_options'] = array_keys($dimensions);

		// Sort links
		$url = '';

		if ($filter_dimension !== '') {
			$url .= '&filter_dimension=' . urlencode($filter_dimension);
		}

		if ($filter_days > 0) {
			$url .= '&filter_days=' . $filter_days;
		}

		if ($filter_status !== '') {
			$url .= '&filter_status=' . $filter_status;
		}

		$flip = ($order === 'ASC') ? 'DESC' : 'ASC';

		$this->data['sort_name'] = $this->url->link('tool/cache_images', 'token=' . $this->session->data['token'] . '&sort=name&order=' . ($sort === 'name' ? $flip : 'ASC') . $url, 'SSL');
		$this->data['sort_size'] = $this->url->link('tool/cache_images', 'token=' . $this->session->data['token'] . '&sort=size&order=' . ($sort === 'size' ? $flip : 'ASC') . $url, 'SSL');
		$this->data['sort_date'] = $this->url->link('tool/cache_images', 'token=' . $this->session->data['token'] . '&sort=date&order=' . ($sort === 'date' ? $flip : 'ASC') . $url, 'SSL');

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		// Pagination
		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = self::LIMIT;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('tool/cache_images', 'token=' . $this->session->data['token'] . '&sort=' . $sort . '&order=' . $order . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$this->data['token'] = $this->session->data['token'];
		$this->data['delete'] = $this->url->link('tool/cache_images/delete', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['purge'] = $this->url->link('tool/cache_images/purge', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['reset'] = $this->url->link('tool/cache_images', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['attention'] = $this->session->data['attention'] ?? '';
		$this->data['success'] = $this->session->data['success'] ?? '';

		unset($this->session->data['attention'], $this->session->data['success']);

		$this->template = 'tool/cache_images.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	public function delete() {
		$this->language->load('tool/cache_images');

		if (isset($this->request->post['selected']) && is_array($this->request->post['selected']) && $this->validateDelete()) {
			$count = 0;
			$bytes = 0;

			foreach ($this->request->post['selected'] as $relative_path) {
				$size = $this->deleteFile((string)$relative_path);

				if ($size !== false) {
					$count++;
					$bytes += $size;
				}
			}

			$this->removeEmptyDirectories();

			$this->session->data['success'] = sprintf($this->language->get('text_success'), number_format($count), $this->formatSize($bytes));
		} else {
			$this->session->data['attention'] = $this->language->get('text_attention');
		}

		$this->redirect($this->url->link('tool/cache_images', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function purge() {
		$this->language->load('tool/cache_images');

		$type = $this->request->post['type'] ?? '';

		if ($this->request->server['REQUEST_METHOD'] !== 'POST' || !in_array($type, ['older', 'orphans', 'legacy', 'all'], true)) {
			$this->session->data['attention'] = $this->language->get('text_invalid');
		} elseif (!$this->validateDelete()) {
			$this->session->data['attention'] = $this->language->get('error_permission');
		} else {
			$days = max(1, (int)($this->request->post['days'] ?? 0));
			$cutoff = time() - ($days * 86400);

			$count = 0;
			$bytes = 0;

			foreach ($this->scan($type === 'orphans') as $file) {
				if ($type === 'older' && $file['time'] >= $cutoff) {
					continue;
				}

				if ($type === 'orphans' && !$file['orphan']) {
					continue;
				}

				if ($type === 'legacy' && $file['webp']) {
					continue;
				}

				$size = $this->deleteFile($file['path']);

				if ($size !== false) {
					$count++;
					$bytes += $size;
				}
			}

			$this->removeEmptyDirectories();

			$this->session->data['success'] = sprintf($this->language->get('text_success'), number_format($count), $this->formatSize($bytes));
		}

		$this->redirect($this->url->link('tool/cache_images', 'token=' . $this->session->data['token'], 'SSL'));
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'tool/cache_images')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}

	/**
	 * Scan the image cache. Each entry holds the path relative to DIR_IMAGE, size, modified time,
	 * dimension (WxH), whether the original image is missing (orphan) and whether the file is WebP.
	 */
	private function scan(bool $orphans = true): array {
		$files = [];
		$cache_dir = DIR_IMAGE . 'cache/';

		if (!is_dir($cache_dir)) {
			return $files;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($cache_dir, FilesystemIterator::SKIP_DOTS)
		);

		$originals = [];

		foreach ($iterator as $entry) {
			if (!$entry->isFile() || $entry->getFilename() === 'index.html') {
				continue;
			}

			$ext = strtolower($entry->getExtension());

			if (!in_array($ext, self::EXTENSIONS, true)) {
				continue;
			}

			$path = str_replace('\\', '/', substr($entry->getPathname(), strlen(DIR_IMAGE)));

			$dim = '-';
			$orphan = false;

			if (preg_match('/^cache\/(.+)-(\d+x\d+)\.[A-Za-z]+$/', $path, $match)) {
				$dim = $match[2];

				if ($orphans) {
					$base = $match[1];

					if (!isset($originals[$base])) {
						$originals[$base] = $this->originalExists($base);
					}

					$orphan = !$originals[$base];
				}
			}

			$files[] = [
				'path'   => $path,
				'size'   => $entry->getSize(),
				'time'   => $entry->getMTime(),
				'dim'    => $dim,
				'orphan' => $orphan,
				'webp'   => ($ext === 'webp')
			];
		}

		return $files;
	}

	private function originalExists(string $base): bool {
		foreach (self::EXTENSIONS as $ext) {
			if (is_file(DIR_IMAGE . $base . '.' . $ext) || is_file(DIR_IMAGE . $base . '.' . strtoupper($ext))) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Delete one cache file after the path safety checks. Returns the freed bytes, or false.
	 */
	private function deleteFile(string $relative_path) {
		// Normalise separators and strip any traversal attempts
		$relative_path = str_replace('\\', '/', $relative_path);

		if (str_contains($relative_path, '..')) {
			return false;
		}

		// Must sit inside cache/
		if (!str_starts_with($relative_path, 'cache/')) {
			return false;
		}

		// Only allow image extensions
		if (!in_array(strtolower(pathinfo($relative_path, PATHINFO_EXTENSION)), self::EXTENSIONS, true)) {
			return false;
		}

		$file = DIR_IMAGE . $relative_path;

		if (!is_file($file)) {
			return false;
		}

		$size = filesize($file);

		if (!unlink($file)) {
			return false;
		}

		return (int)$size;
	}

	/**
	 * Remove empty sub folders left behind in the cache (the cache folder itself is kept).
	 */
	private function removeEmptyDirectories(): void {
		$cache_dir = DIR_IMAGE . 'cache/';

		if (!is_dir($cache_dir)) {
			return;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($cache_dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $entry) {
			if ($entry->isDir() && !$entry->isLink()) {
				$directory = new FilesystemIterator($entry->getPathname());

				// Only remove folders that are really empty
				if (!$directory->valid()) {
					unset($directory);

					rmdir($entry->getPathname());
				}
			}
		}
	}

	private function formatSize(int $bytes): string {
		$suffix = ['B', 'KB', 'MB', 'GB', 'TB'];
		$size = $bytes;
		$i = 0;

		while ($size >= 1024 && $i < count($suffix) - 1) {
			$size /= 1024;
			$i++;
		}

		return round($size, 2, PHP_ROUND_HALF_UP) . ' ' . $suffix[$i];
	}
}
