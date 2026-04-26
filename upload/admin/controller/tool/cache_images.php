<?php
/**
 * Class ControllerToolCacheImages
 *
 * @package NivoCart
 */
class ControllerToolCacheImages extends Controller {
	private $error = [];

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

		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_size'] = $this->language->get('column_size');

		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['delete'] = $this->url->link('tool/cache_images/delete', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

		// Get Cache images (needs more work)
		$this->data['cache_images'] = [];

		$suffix = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

		if (is_dir(DIR_IMAGE)) {
			$iterator = new FilesystemIterator(DIR_IMAGE, FilesystemIterator::SKIP_DOTS);

			foreach ($iterator as $entry) {
				if (!$entry->isFile()) {
					continue;
				}

				$filename = $entry->getFilename();

				if (!str_starts_with($filename, 'cache.')) {
					continue;
				}

				if ($filename === 'index.html') {
					continue;
				}

				// Expiry timestamp is the last segment after the final dot
				$time = substr(strrchr($filename, '.'), 1);

				if (!is_numeric($time)) {
					continue;
				}

				$size = $entry->getSize();
				$i = 0;

				while (($size / 1024) > 1) {
					$size = $size / 1024;
					$i++;
				}

				$this->data['cache_images'][] = [
					'name'     => $filename,
					'size'     => round(substr($size, 0, strpos($size, '.') + 4), 2, PHP_ROUND_HALF_UP) . $suffix[$i],
					'time'     => round((((int)$time - time()) / 60), 0, PHP_ROUND_HALF_UP),
					'selected' => isset($this->request->post['selected']) && in_array($filename, $this->request->post['selected'])
				];
			}
		}

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['attention'])) {
			$this->data['attention'] = $this->session->data['attention'];

			unset($this->session->data['attention']);
		} else {
			$this->data['attention'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->template = 'tool/cache_images.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	public function delete() {
		$this->language->load('tool/cache_images');

		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $name) {
				// Sanitise: strip any path traversal and accept only the bare filename
				$name = basename($name);

				// Ensure it matches the cache file naming convention before touching it
				if (!str_starts_with($name, 'cache.') || $name === 'index.html') {
					continue;
				}

				$file = DIR_IMAGE . $name;

				if (is_file($file)) {
					unlink($file);
				}
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('tool/cache_images', 'token=' . $this->session->data['token'], 'SSL'));

		} else {
			$this->session->data['attention'] = $this->language->get('text_attention');

			$this->redirect($this->url->link('tool/cache_images', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'tool/cache_images')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}
}
