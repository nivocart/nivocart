<?php
/**
 * Class ControllerNodeLanguage
 *
 * @package NivoCart
 */
class ControllerNodeLanguage extends Controller {
	/** Error array Placeholder */

	protected function index() {
		if (isset($this->request->post['language_code'])) {
			$this->session->data['language'] = $this->request->post['language_code'];

			// Added strpos check to pass McAfee PCI compliance test
			if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) === 0 || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) === 0)) {
				$this->redirect($this->request->post['redirect']);
			} else {
				$this->redirect($this->url->link('common/home', '', 'SSL'));
			}
		}

		$this->language->load('node/language');

		$this->data['text_language'] = $this->language->get('text_language');

		// Resolve server base URL
		if ((isset($this->request->server['HTTPS']) && in_array($this->request->server['HTTPS'], ['on', '1'], true)) ||
			(isset($this->request->server['SERVER_PORT']) && $this->request->server['SERVER_PORT'] === '443') ||
			(isset($this->request->server['HTTP_X_FORWARDED_PROTO']) && $this->request->server['HTTP_X_FORWARDED_PROTO'] === 'https')) {
			$connection = 'SSL';
		} else {
			$connection = 'NONSSL';
		}

		$this->data['action'] = $this->url->link('module/language', '', $connection);

		$this->data['language_code'] = $this->session->data['language'];

		$this->load->model('localisation/language');

		$this->data['languages'] = [];

		$results = $this->model_localisation_language->getLanguages();

		foreach ($results as $result) {
			if ($result['status']) {
				$this->data['languages'][] = [
					'name'  => $result['name'],
					'code'  => $result['code'],
					'image' => $result['image']
				];
			}
		}

		if (!isset($this->request->get['route'])) {
			$this->data['redirect'] = $this->url->link('common/home');
		} else {
			$data = $this->request->get;

			unset($data['_route_']);

			$route = $data['route'];

			unset($data['route']);

			$url = '';

			if ($data) {
				$url = '&' . urldecode(http_build_query($data, '', '&'));
			}

			$this->data['redirect'] = $this->url->link($route, $url, $connection);
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/node/language.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/node/language.tpl';
		} else {
			$this->template = 'default/template/node/language.tpl';
		}

		$this->render();
	}
}
