<?php
/**
 * Class ControllerModuleCarousel
 *
 * @package NivoCart
 */
class ControllerModuleCarousel extends Controller {
	private $name = 'carousel';

	protected function index($setting) {
		static $module = 0;

		$this->language->load('module/' . $this->name);

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->document->addStyle('catalog/view/javascript/jquery/slick/slick.min.css');

		$this->document->addScript('catalog/view/javascript/jquery/slick/slick.min.js');

		// Module
		$this->data['theme'] = $this->config->get($this->name . '_theme');
		$this->data['title'] = $this->config->get($this->name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$this->data['duration'] = ($this->config->get($this->name . '_duration')) ? $this->config->get($this->name . '_duration') : 3000;
		$this->data['speed'] = ($this->config->get($this->name . '_speed')) ? $this->config->get($this->name . '_speed') : 300;

		// Responsive: "show" is the maximum number of images. Each extra image is only shown once the carousel is wide
		// enough for one more full image (image width + 10px of slide padding), so images are never squeezed or cropped.
		$show_max = max(1, (int)round($setting['show'], 0, PHP_ROUND_HALF_UP));
		$slide_width = (int)$setting['width'] + 10;

		$responsive = [];

		for ($i = 2; $i <= $show_max; $i++) {
			$responsive[] = [
				'breakpoint' => ($i * $slide_width) - 1,
				'settings'   => ['slidesToShow' => $i]
			];
		}

		$this->data['responsive'] = json_encode($responsive);

		// Auto
		$this->data['auto'] = $setting['auto'] ? 'true' : 'false';

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$this->data['banners'] = [];

		$results = $this->model_design_banner->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if (file_exists(DIR_IMAGE . $result['image'])) {
				if (!empty($result['link'])) {
					if ($result['external_link']) {
						$image_link = html_entity_decode($result['link'], ENT_QUOTES, 'UTF-8');
					} else {
						$image_link = $this->url->link($result['link'], '', 'SSL');
					}
				} else {
					$image_link = '';
				}

				$this->data['banners'][] = [
					'banner_image_id' => $result['banner_image_id'],
					'title'           => $result['title'],
					'link'            => $image_link,
					'image'           => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				];
			}
		}

		// Shuffle
		$random = $this->config->get($this->name . '_random');

		if ($random) {
			shuffle($this->data['banners']);
		}

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('module/' . $this->name);

		$this->render();
	}
}
