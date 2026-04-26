<?php
/**
 * Class ControllerModuleNews
 *
 * @package NivoCart
 */
class ControllerModuleNews extends Controller {
	private $_name = 'news';

	protected function index($setting) {
		static $module = 0;

		$this->language->load('module/' . $this->_name);

		$this->data['heading_title'] = $this->language->get('heading_title');

		// Module
		$this->data['theme'] = $this->config->get($this->_name . '_theme');

		$this->data['title'] = $this->config->get($this->_name . '_title' . $this->config->get('config_language_id'));

		if (!$this->data['title']) {
			$this->data['title'] = $this->data['heading_title'];
		}

		$this->data['show_button'] = $this->config->get($this->_name . '_headline');

		$this->data['text_more'] = $this->language->get('text_more');

		$this->data['button_list'] = $this->language->get('button_list');

		$this->data['news_list'] = $this->url->link('information/news_list', '', 'SSL');

		$this->load->model('catalog/news');
		$this->load->model('tool/image');

		$numchars = $setting['numchars'];

		$chars = (isset($numchars)) ? $numchars : 100;

		$this->data['news'] = [];

		$results = $this->model_catalog_news->getNewsShort($setting['limit']);

		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
			} else {
				$image = false;
			}

			$news_length = mb_strlen($result['description'], 'UTF-8');

			if ($news_length > $chars) {
				$description = '<p>' . substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $chars) . ' ...</p>';
			} else {
				$description = '<p>' . html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8') . '</p>';
			}

			$this->data['news'][] = [
				'title'       => $result['title'],
				'image'       => $image,
				'description' => $description,
				'posted'      => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'href'        => $this->url->link('information/news', 'news_id=' . $result['news_id'], 'SSL')
			];
		}

		$this->data['module'] = $module++;

		// Template
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/' . $this->_name . '.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/' . $this->_name . '.tpl';
		} else {
			$this->template = 'default/template/module/' . $this->_name . '.tpl';
		}

		$this->render();
	}
}
