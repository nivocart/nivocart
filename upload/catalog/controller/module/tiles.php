<?php
/**
 * Class ControllerModuleTiles
 *
 * @package NivoCart
 */
class ControllerModuleTiles extends Controller {
	private $name = 'tiles';

	protected function index($setting) {
		static $module = 0;

		$this->language->load('module/' . $this->name);

		$this->data['heading_title'] = $this->language->get('heading_title');

		if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/tiles.css')) {
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/tiles.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/tiles.css');
		}

		// Global module settings
		$this->data['theme'] = $this->config->get($this->name . '_theme');
		$this->data['title'] = $this->config->get($this->name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$shape = (string)$this->config->get($this->name . '_shape');
		$hover = (string)$this->config->get($this->name . '_hover');

		$this->data['shape'] = preg_match('/^rounded-[0-9]{1,2}$/', $shape) ? $shape : 'rounded-5';
		$this->data['hover'] = in_array($hover, ['none', 'lift', 'zoom'], true) ? $hover : 'none';

		// Per-placement settings
		$gap = isset($setting['gap']) ? (int)$setting['gap'] : 10;

		$this->data['gap'] = in_array($gap, [0, 5, 10, 15, 20, 30], true) ? $gap : 10;

		$width = !empty($setting['width']) ? (int)$setting['width'] : 600;
		$height = !empty($setting['height']) ? (int)$setting['height'] : 400;
		$min_height = !empty($setting['min_height']) ? min((int)$setting['min_height'], 1000) : 0;

		$styles = ['--tiles-ratio:' . $width . ' / ' . $height];

		if ($min_height > 0) {
			$styles[] = '--tiles-min-h:' . $min_height . 'px';
		}

		$this->data['style'] = implode(';', $styles) . ';';

		$this->load->model('design/tile');
		$this->load->model('tool/image');

		$layouts = ['top', 'cover', 'left'];
		$aligns = ['left', 'center', 'right'];
		$valigns = ['top', 'middle', 'bottom'];

		$this->data['tiles'] = [];

		$results = $this->model_design_tile->getTile($setting['tile_id']);

		foreach ($results as $result) {
			// Link
			if (!empty($result['link'])) {
				if ($result['external_link']) {
					$link = html_entity_decode($result['link'], ENT_QUOTES, 'UTF-8');
				} else {
					$link = $this->url->link($result['link'], '', 'SSL');
				}
			} else {
				$link = '';
			}

			// Image
			$image = '';

			if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
				$image = (string)$this->model_tool_image->resize($result['image'], $width, $height);
			}

			// Colours: custom properties are only written when valid hex, otherwise the theme defaults apply
			$colours = '';

			if (preg_match('/^[0-9A-Fa-f]{6}$/', $result['background'])) {
				$colours .= '--tile-bg:#' . $result['background'] . ';';
			}

			if (preg_match('/^[0-9A-Fa-f]{6}$/', $result['colour'])) {
				$colours .= '--tile-fg:#' . $result['colour'] . ';';
			}

			$this->data['tiles'][] = [
				'tile_item_id'  => $result['tile_item_id'],
				'title'         => $result['title'],
				'text'          => $result['text'],
				'link_text'     => $result['link_text'],
				'link'          => $link,
				'external_link' => $result['external_link'],
				'image'         => $image,
				'layout'        => ($image && in_array($result['layout'], $layouts, true)) ? $result['layout'] : 'top',
				'align'         => in_array($result['align'], $aligns, true) ? $result['align'] : 'center',
				'valign'        => in_array($result['valign'], $valigns, true) ? $result['valign'] : 'middle',
				'style'         => $colours
			];
		}

		// Up to 6 tiles per line, over 2 lines: 7 to 12 tiles are split evenly
		$total = count($this->data['tiles']);

		$this->data['columns'] = ($total > 6) ? (int)ceil($total / 2) : max($total, 1);

		$this->data['width'] = $width;
		$this->data['height'] = $height;

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('module/' . $this->name);

		$this->render();
	}
}
