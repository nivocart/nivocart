<?php
/**
 * Class ControllerToolImagesCleanup
 *
 * Scans an image data folder (e.g. image/data/dropship/) and identifies
 * images that are no longer referenced by any product or dropship variant,
 * then allows the admin user to delete those orphan files in bulk.
 *
 * Menu visibility: only shown when the nc_dropship_variant table exists
 * (i.e. Avasam or CJD is installed). The check lives in the header controller.
 *
 * @package NivoCart
 */
class ControllerToolImagesCleanup extends Controller {
	private $error = [];

	// -----------------------------------------------------------------------
	// index() — display folder selector and (when a folder is chosen) results
	// -----------------------------------------------------------------------

	public function index(): void {
		$this->language->load('tool/images_cleanup');

		$this->load->model('tool/images_cleanup');

		$this->document->setTitle($this->language->get('heading_title'));

		// Breadcrumbs
		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false,
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/images_cleanup', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: ',
		];

		// Language strings passed to template
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_no_orphans'] = $this->language->get('text_no_orphans');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_total_size'] = $this->language->get('text_total_size');
		$this->data['text_select_folder'] = $this->language->get('text_select_folder');
		$this->data['text_clean_summary'] = $this->language->get('text_clean_summary');
		$this->data['text_confirm_delete'] = $this->language->get('text_confirm_delete');
		$this->data['text_confirm'] = $this->language->get('text_confirm');

		$this->data['entry_folder'] = $this->language->get('entry_folder');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_size'] = $this->language->get('column_size');
		$this->data['column_modified'] = $this->language->get('column_modified');

		$this->data['button_scan'] = $this->language->get('button_scan');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		// URLs
		$this->data['delete'] = $this->url->link('tool/images_cleanup/delete', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['token'] = $this->session->data['token'];

		// -------------------------------------------------------------------
		// Build folder list — only include subfolders that actually exist
		// -------------------------------------------------------------------
		$folders = [];

		// Avasam subfolder — only if the directory exists
		if (is_dir(DIR_IMAGE . 'data/dropship/avasam')) {
			$folders['dropship/avasam'] = 'Avasam (data/dropship/avasam/)';
		}

		// CJDropshipping subfolder — only if the directory exists
		if (is_dir(DIR_IMAGE . 'data/dropship/cjd')) {
			$folders['dropship/cjd'] = 'CJDropshipping (data/dropship/cjd/)';
		}

		// Legacy flat dropship folder — only show when neither subfolder has been created yet
		if (is_dir(DIR_IMAGE . 'data/dropship')
			&& !is_dir(DIR_IMAGE . 'data/dropship/avasam')
			&& !is_dir(DIR_IMAGE . 'data/dropship/cjd')
		) {
			$folders['dropship'] = 'Dropship (data/dropship/)';
		}

		if (is_dir(DIR_IMAGE . 'data/demo')) {
			$folders['demo'] = 'Demo (data/demo/)';
		}

		$this->data['folders'] = $folders;

		// -------------------------------------------------------------------
		// Resolve the requested folder from GET, validate against known list
		// -------------------------------------------------------------------
		$selected_folder = isset($this->request->get['folder']) ? trim($this->request->get['folder']) : '';

		if (!array_key_exists($selected_folder, $folders)) {
			$selected_folder = '';
		}

		$this->data['selected_folder'] = $selected_folder;

		// -------------------------------------------------------------------
		// Defaults (no scan yet)
		// -------------------------------------------------------------------
		$this->data['scanned'] = false;
		$this->data['orphans'] = [];
		$this->data['orphan_count'] = 0;
		$this->data['orphan_total_size'] = '0 B';
		$this->data['summary'] = '';

		// -------------------------------------------------------------------
		// Perform scan when a valid folder is selected
		// -------------------------------------------------------------------
		if ($selected_folder) {
			$this->data['scanned'] = true;

			$folder_path = DIR_IMAGE . 'data/' . $selected_folder . '/';
			$folder_prefix = 'data/' . $selected_folder . '/';
			$suffix = ['B', 'KB', 'MB', 'GB', 'TB'];

			// Step 1: collect all image files in the selected folder
			$filesystem_images = []; // relative_path => ['size_bytes' => int, 'modified' => string]

			if (is_dir($folder_path)) {
				$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($folder_path, FilesystemIterator::SKIP_DOTS)
				);

				foreach ($iterator as $entry) {
					if (!$entry->isFile()) {
						continue;
					}

					// Skip placeholder files
					if ($entry->getFilename() === 'index.html') {
						continue;
					}

					// Only recognised image extensions
					$ext = strtolower($entry->getExtension());

					if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
						continue;
					}

					$full_path = $entry->getPathname();

					$relative_path = str_replace('\\', '/', substr($full_path, strlen(DIR_IMAGE)));

					$filesystem_images[$relative_path] = [
						'size_bytes' => $entry->getSize(),
						'modified'   => date('Y-m-d H:i:s', $entry->getMTime())
					];
				}
			}

			// Step 2: fetch all DB-referenced image paths for this folder
			$referenced = $this->model_tool_images_cleanup->getReferencedImages($folder_prefix);

			// Step 3: compute orphans (on disk but not in DB)
			$total_bytes = 0;
			$orphans = [];

			foreach ($filesystem_images as $rel_path => $meta) {
				if (isset($referenced[$rel_path])) {
					continue; // still used by a product or variant
				}

				$total_bytes += $meta['size_bytes'];

				// Format individual file size
				$size = $meta['size_bytes'];
				$i = 0;

				while ($size >= 1024 && $i < count($suffix) - 1) {
					$size /= 1024;
					$i++;
				}

				$orphans[] = [
					'name'     => $rel_path,
					'size'     => round($size, 2, PHP_ROUND_HALF_UP) . ' ' . $suffix[$i],
					'modified' => $meta['modified']
				];
			}

			// Format total orphan size
			$t = $total_bytes;
			$i = 0;

			while ($t >= 1024 && $i < count($suffix) - 1) {
				$t /= 1024;
				$i++;
			}

			$total_formatted = round($t, 2, PHP_ROUND_HALF_UP) . ' ' . $suffix[$i];

			$this->data['orphans'] = $orphans;
			$this->data['orphan_count'] = count($orphans);
			$this->data['orphan_total_size'] = $total_formatted;
			$this->data['summary'] = sprintf($this->language->get('text_scan_summary'), count($orphans), $total_formatted);
		}

		// Session flash messages
		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['attention'] = $this->session->data['attention'] ?? '';
		$this->data['success'] = $this->session->data['success'] ?? '';

		unset($this->session->data['attention'], $this->session->data['success']);

		$this->template = 'tool/images_cleanup.tpl';
		$this->children = ['common/header', 'common/footer'];

		$this->response->setOutput($this->render());
	}

	// -----------------------------------------------------------------------
	// delete() — permanently remove the selected orphan images
	// -----------------------------------------------------------------------

	public function delete(): void {
		$this->language->load('tool/images_cleanup');

		// Read the folder that was being scanned (so we can redirect back)
		$folder = isset($this->request->post['folder']) ? trim($this->request->post['folder']) : '';

		// Strict folder name validation — alphanumeric, underscores, hyphens, and one optional slash for subfolders (e.g. dropship/avasam)
		if (!preg_match('/^[a-zA-Z0-9_-]+(\/[a-zA-Z0-9_-]+)?$/', $folder)) {
			$folder = '';
		}

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			$deleted = 0;
			$freed_bytes = 0;
			$suffix = ['B', 'KB', 'MB', 'GB', 'TB'];

			// Allowed prefix for this folder (prevents deleting outside it)
			$folder_prefix = $folder ? 'data/' . $folder . '/' : 'data/';

			foreach ($this->request->post['selected'] as $relative_path) {
				// Normalise directory separators
				$relative_path = str_replace('\\', '/', $relative_path);

				// Reject path traversal attempts
				if (str_contains($relative_path, '..')) {
					continue;
				}

				// Must sit inside the selected folder prefix
				if (!str_starts_with($relative_path, $folder_prefix)) {
					continue;
				}

				// Also enforce the 'data/' root as an additional safety net
				if (!str_starts_with($relative_path, 'data/')) {
					continue;
				}

				// Only recognised image extensions
				$ext = strtolower(pathinfo($relative_path, PATHINFO_EXTENSION));

				if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
					continue;
				}

				$file = DIR_IMAGE . $relative_path;

				if (is_file($file)) {
					$freed_bytes += filesize($file);
					unlink($file);
					$deleted++;
				}
			}

			// Format freed size
			$t = $freed_bytes;
			$i = 0;

			while ($t >= 1024 && $i < count($suffix) - 1) {
				$t /= 1024;
				$i++;
			}

			$freed_formatted = round($t, 2, PHP_ROUND_HALF_UP) . ' ' . $suffix[$i];

			$this->session->data['success'] = sprintf($this->language->get('text_success'), $deleted, $freed_formatted);

		} else {
			$this->session->data['attention'] = $this->language->get('text_attention');
		}

		// Redirect back to the scan results for the same folder
		$params = 'token=' . $this->session->data['token'];

		if ($folder) {
			$params .= '&folder=' . urlencode($folder);
		}

		$this->redirect($this->url->link('tool/images_cleanup', $params, 'SSL'));
	}

	// -----------------------------------------------------------------------
	// Permission guard
	// -----------------------------------------------------------------------

	protected function validateDelete(): bool {
		if (!$this->user->hasPermission('modify', 'tool/images_cleanup')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}
}
