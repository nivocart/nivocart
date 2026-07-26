<?php
/**
 * Class ControllerCommonContentLeft
 *
 * @package NivoCart
 */
class ControllerCommonContentLeft extends Controller {
	/** Error array Placeholder */

	protected function index() {
		$route = isset($this->request->get['route']) ? (string)$this->request->get['route'] : 'common/home';

		$layout_id = 0;

		if ($route === 'product/category' && isset($this->request->get['path']) && !is_array($this->request->get['path'])) {
			$path = explode('_', (string)$this->request->get['path']);

			$this->load->model('catalog/category');

			$layout_id = (int)$this->model_catalog_category->getCategoryLayoutId(end($path));
		}

		$route_map = [
			'product/product'         => ['model' => 'catalog/product', 'key' => 'product_id', 'method' => 'getProductLayoutId'],
			'information/information' => ['model' => 'catalog/information', 'key' => 'information_id', 'method' => 'getInformationLayoutId'],
			'blog/article_info'       => ['model' => 'blog/article', 'key' => 'blog_article_id', 'method' => 'getBlogArticleLayoutId'],
			'blog/category'           => ['model' => 'blog/article', 'key' => 'blog_category_id', 'method' => 'getBlogCategoryLayoutId'],
		];

		if (!$layout_id && isset($route_map[$route])) {
			$entry = $route_map[$route];

			$param_key = $entry['key'];

			if (isset($this->request->get[$param_key]) && !is_array($this->request->get[$param_key])) {
				$value = (string)$this->request->get[$param_key];

				if (isset($entry['transform'])) {
					$value = ($entry['transform'])($value);
				}

				$this->load->model($entry['model']);

				$model_key = 'model_' . str_replace('/', '_', $entry['model']);

				$layout_id = (int)$this->{$model_key}->{$entry['method']}($value);
			}
		}

		if (!$layout_id) {
			$this->load->model('design/layout');

			$layout_id = (int)$this->model_design_layout->getLayout($route);
		}

		if (!$layout_id) {
			$layout_id = (int)$this->config->get('config_layout_id');
		}

		$module_data = [];

		$this->load->model('setting/extension');

		$extensions = $this->model_setting_extension->getExtensions('module');

		foreach ($extensions as $extension) {
			$modules = $this->config->get($extension['code'] . '_module');

			if ($modules) {
				foreach ($modules as $module) {
					if ($module['layout_id'] == $layout_id && $module['position'] === 'content_left' && $module['status']) {
						$module_data[] = [
							'code'       => $extension['code'],
							'setting'    => $module,
							'sort_order' => $module['sort_order']
						];
					}
				}
			}
		}

		usort($module_data, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);

		$this->data['modules'] = [];

		foreach ($module_data as $module) {
			$rendered = $this->getChild('module/' . $module['code'], $module['setting']);

			if ($rendered) {
				$this->data['modules'][] = $rendered;
			}
		}

		$this->resolveTemplate('common/content_left');
		$this->render();
	}
}
