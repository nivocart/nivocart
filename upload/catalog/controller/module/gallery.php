<?php
/**
 * Class ControllerModuleGallery
 *
 * Promotion box of the Gallery: the image of the month and a rotating set of approved images.
 *
 * Location: catalog/controller/module/gallery.php
 *
 * @package NivoCart
 */
class ControllerModuleGallery extends Controller {
	private $name = 'gallery';

	protected function index($setting) {
		static $module = 0;

		$this->language->load('module/' . $this->name);

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_view'] = $this->language->get('text_view');
		$this->data['text_submit'] = $this->language->get('text_submit');

		$this->data['images'] = [];
		$this->data['month'] = [];

		$this->load->model('modification/gallery');

		// Only when the Gallery is installed and switched on
		if ($this->config->get('gallery_page_status') && $this->model_modification_gallery->isInstalled()) {
			$this->document->addStyle('catalog/view/javascript/jquery/flexslider/flexslider.min.css');
			$this->document->addScript('catalog/view/javascript/jquery/flexslider/jquery.flexslider-min.js');

			$theme = $this->config->get('config_template');

			$folder = file_exists(DIR_TEMPLATE . $theme . '/stylesheet/gallery.css') ? $theme : 'default';

			$this->document->addStyle('catalog/view/theme/' . $folder . '/stylesheet/gallery.css?v=' . (int)filemtime(DIR_TEMPLATE . $folder . '/stylesheet/gallery.css'));

			$this->load->model('tool/image');

			$width = max(50, (int)($setting['width'] ?? 200));
			$height = max(50, (int)($setting['height'] ?? 200));
			$limit = min(20, max(1, (int)($setting['limit'] ?? 8)));

			// Image of the month (always the first, with its own label)
			$month_id = (int)$this->config->get($this->name . '_month');

			$month = $month_id ? $this->model_modification_gallery->getImage($month_id) : [];

			if ($month) {
				$thumb = $this->model_tool_image->resize($month['image'], $width, $height);

				if ($thumb) {
					$label = $this->config->get($this->name . '_month_title' . $this->config->get('config_language_id'));

					$this->data['month'] = [
						'label' => $label ?: $this->language->get('text_month'),
						'name'  => $month['pet_name'],
						'thumb' => $thumb,
						'href'  => $this->url->link('modification/gallery/info', 'gallery_id=' . $month['gallery_id'], 'SSL')
					];
				}
			}

			// Rotating images (never the image of the month twice)
			$random = $this->config->get($this->name . '_random');

			$results = $this->model_modification_gallery->getImages([
				'filter_id' => (int)($setting['filter_id'] ?? 0),
				'sort'      => $random ? 'random' : 'date',
				'start'     => 0,
				'limit'     => $limit + 1
			]);

			foreach ($results as $result) {
				if ((int)$result['gallery_id'] === $month_id) {
					continue;
				}

				$thumb = $this->model_tool_image->resize($result['image'], $width, $height);

				if ($thumb) {
					$this->data['images'][] = [
						'name'  => $result['pet_name'],
						'thumb' => $thumb,
						'href'  => $this->url->link('modification/gallery/info', 'gallery_id=' . $result['gallery_id'], 'SSL')
					];
				}

				if (count($this->data['images']) >= $limit) {
					break;
				}
			}
		}

		// Module
		$this->data['theme'] = $this->config->get($this->name . '_theme');
		$this->data['title'] = $this->config->get($this->name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$animation = $this->config->get($this->name . '_transition');

		$this->data['animation'] = ($animation === 'fade') ? 'fade' : 'slide';
		$this->data['direction'] = ($animation === 'horizontal') ? 'horizontal' : 'vertical';

		$this->data['duration'] = $this->config->get($this->name . '_duration') ?: 5000;
		$this->data['speed'] = $this->config->get($this->name . '_speed') ?: 500;

		$this->data['links'] = $this->config->get($this->name . '_links');

		$this->data['view'] = $this->url->link('modification/gallery', '', 'SSL');
		$this->data['submit'] = $this->url->link('modification/gallery/submit', '', 'SSL');

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('module/' . $this->name);

		$this->render();
	}
}
