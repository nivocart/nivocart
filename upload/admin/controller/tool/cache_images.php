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
		$this->data['text_total_size'] = $this->language->get('text_total_size');

        $this->data['column_name'] = $this->language->get('column_name');
        $this->data['column_size'] = $this->language->get('column_size');
		$this->data['column_modified'] = $this->language->get('column_modified');

        $this->data['button_delete'] = $this->language->get('button_delete');
        $this->data['button_cancel'] = $this->language->get('button_cancel');

        $this->data['delete'] = $this->url->link('tool/cache_images/delete', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['cancel'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

        // Scan image cache directory recursively
        $this->data['cache_images'] = [];
        $this->data['cache_total_size'] = 0;

        $cache_dir = DIR_IMAGE . 'cache/';

        if (is_dir($cache_dir)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cache_dir, FilesystemIterator::SKIP_DOTS)
            );

            $suffix = ['B', 'KB', 'MB', 'GB', 'TB'];

            foreach ($iterator as $entry) {
                if (!$entry->isFile()) {
                    continue;
                }

                // Skip index.html placeholder files
                if ($entry->getFilename() === 'index.html') {
                    continue;
                }

                // Only process known image extensions
                $ext = strtolower($entry->getExtension());

                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    continue;
                }

                $full_path = $entry->getPathname();
                $relative_path = str_replace('\\', '/', substr($full_path, strlen(DIR_IMAGE)));
                $size_bytes = $entry->getSize();

                $this->data['cache_total_size'] += $size_bytes;

                // Format file size
                $size = $size_bytes;
                $i = 0;

                while ($size >= 1024 && $i < count($suffix) - 1) {
                    $size /= 1024;
                    $i++;
                }

                $this->data['cache_images'][] = [
                    'name'     => $relative_path,
                    'size'     => round($size, 2, PHP_ROUND_HALF_UP) . ' ' . $suffix[$i],
                    'modified' => date('Y-m-d H:i:s', $entry->getMTime()),
                    'selected' => isset($this->request->post['selected']) && in_array($relative_path, $this->request->post['selected'])
                ];
            }

            // Format total size
            $total = $this->data['cache_total_size'];
            $i = 0;

            while ($total >= 1024 && $i < count($suffix) - 1) {
                $total /= 1024;
                $i++;
            }

            $this->data['cache_total_size'] = round($total, 2, PHP_ROUND_HALF_UP) . ' ' . $suffix[$i];
        }

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

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $relative_path) {
                // Normalise separators and strip any traversal attempts
                $relative_path = str_replace('\\', '/', $relative_path);

                if (str_contains($relative_path, '..')) {
                    continue;
                }

                // Must sit inside cache/
                if (!str_starts_with($relative_path, 'cache/')) {
                    continue;
                }

                // Only allow image extensions
                $ext = strtolower(pathinfo($relative_path, PATHINFO_EXTENSION));

                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    continue;
                }

                $file = DIR_IMAGE . $relative_path;

                if (is_file($file)) {
                    unlink($file);
                }
            }

            $this->session->data['success'] = $this->language->get('text_success');

        } else {
            $this->session->data['attention'] = $this->language->get('text_attention');
        }

        $this->redirect($this->url->link('tool/cache_images', 'token=' . $this->session->data['token'], 'SSL'));
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'tool/cache_images')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return empty($this->error);
    }
}
