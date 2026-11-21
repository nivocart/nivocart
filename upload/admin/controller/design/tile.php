<?php
/**
 * Class ControllerDesignTile
 *
 * @package NivoCart
 */
class ControllerDesignTile extends Controller {
	private $error = [];

	public function index() {
		$this->language->load('design/tile');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/tile');

		$this->getList();
	}

	public function insert() {
		$this->language->load('design/tile');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/tile');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			$this->model_design_tile->addTile($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$page_url = array_filter([
				'sort'  => $this->request->get['sort'] ?? null,
				'order' => $this->request->get['order'] ?? null,
				'page'  => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			if (isset($this->request->post['apply'])) {
				$tile_id = $this->session->data['new_tile_id'];

				if ($tile_id) {
					unset($this->session->data['new_tile_id']);

					$this->redirect($this->url->link('design/tile/update', 'token=' . $this->session->data['token'] . '&tile_id=' . $tile_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('design/tile', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function update() {
		$this->language->load('design/tile');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/tile');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			$this->model_design_tile->editTile($this->request->get['tile_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$page_url = array_filter([
				'sort'  => $this->request->get['sort'] ?? null,
				'order' => $this->request->get['order'] ?? null,
				'page'  => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			if (isset($this->request->post['apply'])) {
				$tile_id = $this->request->get['tile_id'];

				if ($tile_id) {
					$this->redirect($this->url->link('design/tile/update', 'token=' . $this->session->data['token'] . '&tile_id=' . $tile_id . $url, 'SSL'));
				}

			} else {
				$this->redirect($this->url->link('design/tile', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->language->load('design/tile');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('design/tile');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $tile_id) {
				$this->model_design_tile->deleteTile($tile_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$page_url = array_filter([
				'sort'  => $this->request->get['sort'] ?? null,
				'order' => $this->request->get['order'] ?? null,
				'page'  => $this->request->get['page'] ?? null
			]);

			$url = $page_url ? '&' . http_build_query($page_url) : '';

			$this->redirect($this->url->link('design/tile', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$page_url = array_filter([
			'sort'  => $this->request->get['sort'] ?? 'name',
			'order' => $this->request->get['order'] ?? 'ASC',
			'page'  => $this->request->get['page'] ?? 1
		]);

		$url = $page_url ? '&' . http_build_query($page_url) : '';

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('design/tile', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		$this->data['insert'] = $this->url->link('design/tile/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('design/tile/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		// Pagination
		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$this->data['tiles'] = [];

		$sort = $this->request->get['sort'] ?? 'name';
		$order = $this->request->get['order'] ?? 'ASC';
		$page = $this->request->get['page'] ?? 1;

		$data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		];

		$tile_total = $this->model_design_tile->getTotalTiles();

		$results = $this->model_design_tile->getTiles($data);

		foreach ($results as $result) {
			$action = [];

			$action[] = [
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('design/tile/update', 'token=' . $this->session->data['token'] . '&tile_id=' . $result['tile_id'] . $url, 'SSL')
			];

			$this->data['tiles'][] = [
				'tile_id'  => $result['tile_id'],
				'name'     => $result['name'],
				'items'    => $this->model_design_tile->getTotalItemsByTileId($result['tile_id']),
				'status'   => $result['status'],
				'selected' => isset($this->request->post['selected']) && in_array($result['tile_id'], $this->request->post['selected']),
				'action'   => $action
			];
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_confirm'] = $this->language->get('text_confirm');
		$this->data['text_confirm_delete'] = $this->language->get('text_confirm_delete');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_items'] = $this->language->get('column_items');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		// Html table sorting data
		$url = '';

		if ($order === 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_name'] = $this->url->link('design/tile', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('design/tile', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');

		// Pagination data
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $tile_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('design/tile', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'design/tile_list.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_link'] = $this->language->get('text_link');
		$this->data['text_limits'] = $this->language->get('text_limits');
		$this->data['text_image_manager'] = $this->language->get('text_image_manager');

		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_content'] = $this->language->get('entry_content');
		$this->data['entry_title'] = $this->language->get('entry_title');
		$this->data['entry_text'] = $this->language->get('entry_text');
		$this->data['entry_link_text'] = $this->language->get('entry_link_text');
		$this->data['entry_image'] = $this->language->get('entry_image');
		$this->data['entry_link'] = $this->language->get('entry_link');
		$this->data['entry_external_link'] = $this->language->get('entry_external_link');
		$this->data['entry_design'] = $this->language->get('entry_design');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_align'] = $this->language->get('entry_align');
		$this->data['entry_valign'] = $this->language->get('entry_valign');
		$this->data['entry_background'] = $this->language->get('entry_background');
		$this->data['entry_colour'] = $this->language->get('entry_colour');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_apply'] = $this->language->get('button_apply');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_tile'] = $this->language->get('button_add_tile');
		$this->data['button_remove'] = $this->language->get('button_remove');

		$this->data['token'] = $this->session->data['token'];

		// Errors
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
		}

		if (isset($this->error['items'])) {
			$this->data['error_items'] = $this->error['items'];
		} else {
			$this->data['error_items'] = '';
		}

		// Row errors, keyed by row number, then by field name
		$this->data['error_tile_item'] = isset($this->error['tile_item']) ? $this->error['tile_item'] : [];

		// Colorbox
		$this->document->addScript('view/javascript/jquery/colorbox/jquery.colorbox-min.js');
		$this->document->addStyle('view/javascript/jquery/colorbox/colorbox.css');

		// Breadcrumbs
		$page_url = array_filter([
			'sort'  => $this->request->get['sort'] ?? 'name',
			'order' => $this->request->get['order'] ?? 'ASC',
			'page'  => $this->request->get['page'] ?? 1
		]);

		$url = $page_url ? '&' . http_build_query($page_url) : '';

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('design/tile', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		];

		if (!isset($this->request->get['tile_id'])) {
			$this->data['action'] = $this->url->link('design/tile/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('design/tile/update', 'token=' . $this->session->data['token'] . '&tile_id=' . $this->request->get['tile_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('design/tile', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['text_info'] = sprintf($this->language->get('text_info'), $this->url->link('design/tile/info', 'token=' . $this->session->data['token'], 'SSL'));

		if (isset($this->request->get['tile_id']) && ($this->request->server['REQUEST_METHOD'] !== 'POST')) {
			$tile_info = $this->model_design_tile->getTile($this->request->get['tile_id']);
		}

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($tile_info)) {
			$this->data['name'] = $tile_info['name'];
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($tile_info)) {
			$this->data['status'] = $tile_info['status'];
		} else {
			$this->data['status'] = true;
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages([]);

		if (isset($this->request->post['tile_item'])) {
			$tile_items = $this->request->post['tile_item'];
		} elseif (isset($this->request->get['tile_id'])) {
			$tile_items = $this->model_design_tile->getTileItems($this->request->get['tile_id']);
		} else {
			$tile_items = [];
		}

		$this->load->model('tool/image');

		$this->data['no_image'] = $this->model_tool_image->resize('no_image.png', 80, 80);

		// Rows are built in the browser from this data, so text is decoded here (Request encodes all input)
		$this->data['tile_items'] = [];

		foreach ($tile_items as $row => $tile_item) {
			if (!empty($tile_item['image']) && file_exists(DIR_IMAGE . html_entity_decode($tile_item['image'], ENT_QUOTES, 'UTF-8'))) {
				$image = html_entity_decode($tile_item['image'], ENT_QUOTES, 'UTF-8');
				$thumb = $this->model_tool_image->resize($image, 80, 80);
			} else {
				$image = '';
				$thumb = $this->data['no_image'];
			}

			$descriptions = [];

			if (isset($tile_item['tile_item_description'])) {
				foreach ($tile_item['tile_item_description'] as $language_id => $description) {
					$descriptions[$language_id] = [
						'title'     => html_entity_decode($description['title'] ?? '', ENT_QUOTES, 'UTF-8'),
						'text'      => html_entity_decode($description['text'] ?? '', ENT_QUOTES, 'UTF-8'),
						'link_text' => html_entity_decode($description['link_text'] ?? '', ENT_QUOTES, 'UTF-8')
					];
				}
			}

			$this->data['tile_items'][] = [
				'row'                   => $row,
				'tile_item_description' => $descriptions,
				'image'                 => $image,
				'thumb'                 => $thumb,
				'link'                  => html_entity_decode($tile_item['link'] ?? '', ENT_QUOTES, 'UTF-8'),
				'external_link'         => (int)($tile_item['external_link'] ?? 0),
				'layout'                => $tile_item['layout'] ?? 'top',
				'align'                 => $tile_item['align'] ?? 'center',
				'valign'                => $tile_item['valign'] ?? 'middle',
				'background'            => ltrim($tile_item['background'] ?? '', '#'),
				'colour'                => ltrim($tile_item['colour'] ?? '', '#'),
				'sort_order'            => (int)($tile_item['sort_order'] ?? 0)
			];
		}

		// NivoCart colours for the colour selects (hex without the leading #)
		$this->load->model('setting/setting');

		$this->data['colour_options'] = [];

		foreach ($this->model_setting_setting->getColors() as $colour) {
			$this->data['colour_options'][] = [
				'hex'   => strtoupper(ltrim($colour['color'], '#')),
				'title' => $colour['title']
			];
		}

		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['text_custom'] = $this->language->get('text_custom');
		$this->data['text_sample'] = $this->language->get('text_sample');
		$this->data['text_sample_text'] = $this->language->get('text_sample_text');

		// Option lists used by the row template
		$this->data['layout_options'] = [
			'top'   => $this->language->get('text_layout_top'),
			'cover' => $this->language->get('text_layout_cover'),
			'left'  => $this->language->get('text_layout_left')
		];

		$this->data['align_options'] = [
			'left'   => $this->language->get('text_align_left'),
			'center' => $this->language->get('text_align_center'),
			'right'  => $this->language->get('text_align_right')
		];

		$this->data['valign_options'] = [
			'top'    => $this->language->get('text_valign_top'),
			'middle' => $this->language->get('text_valign_middle'),
			'bottom' => $this->language->get('text_valign_bottom')
		];

		$this->template = 'design/tile_form.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'design/tile')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((mb_strlen($this->request->post['name'], 'UTF-8') < 3) || (mb_strlen($this->request->post['name'], 'UTF-8') > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		$tile_items = $this->request->post['tile_item'] ?? [];

		if (!$tile_items || (count($tile_items) > 12)) {
			$this->error['items'] = $this->language->get('error_items');
		}

		$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

		foreach ($tile_items as $row => $tile_item) {
			if (isset($tile_item['tile_item_description'])) {
				foreach ($tile_item['tile_item_description'] as $language_id => $description) {
					$title_length = mb_strlen(html_entity_decode($description['title'] ?? '', ENT_QUOTES, 'UTF-8'), 'UTF-8');

					if (($title_length < 1) || ($title_length > 64)) {
						$this->error['tile_item'][$row]['title'][$language_id] = $this->language->get('error_title');
					}

					if (mb_strlen(html_entity_decode($description['text'] ?? '', ENT_QUOTES, 'UTF-8'), 'UTF-8') > 255) {
						$this->error['tile_item'][$row]['text'][$language_id] = $this->language->get('error_text');
					}

					if (mb_strlen(html_entity_decode($description['link_text'] ?? '', ENT_QUOTES, 'UTF-8'), 'UTF-8') > 64) {
						$this->error['tile_item'][$row]['link_text'][$language_id] = $this->language->get('error_link_text');
					}
				}
			}

			if (!empty($tile_item['image'])) {
				$ext = substr(strrchr($tile_item['image'], '.'), 1);

				if (!in_array(strtolower($ext), $allowed, true)) {
					$this->error['tile_item'][$row]['image'] = $this->language->get('error_image_format');
				}
			}

			foreach (['background', 'colour'] as $field) {
				$value = ltrim($tile_item[$field] ?? '', '#');

				if (($value !== '') && !preg_match('/^[0-9A-Fa-f]{6}$/', $value)) {
					$this->error['tile_item'][$row][$field] = $this->language->get('error_colour');
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'design/tile')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return empty($this->error);
	}

	// Colorbox
	public function info() {
		$this->language->load('design/tile');

		$language = $this->language->get('code');
		$direction = $this->language->get('direction');
		$info_title = $this->language->get('info_title');

		$this->load->model('tool/route');

		$routes = $this->model_tool_route->getRoutes([]);

		if ($routes) {
			$output = '<html dir="' . $direction . '" lang="' . $language . '">' . "\n";
			$output .= '<head>' . "\n";
			$output .= '  <title>' . $info_title . '</title>' . "\n";
			$output .= '  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
			$output .= '  <meta name="robots" content="noindex">' . "\n";
			$output .= '</head>' . "\n";
			$output .= '<body>' . "\n";
			$output .= '  <h1>' . $info_title . '</h1>' . "\n";
			$output .= '  <ul style="list-style:square outside none;">';
			foreach ($routes as $route) {
				$output .= '    <li>' . $route['link'] . '  ' . $route['name'] . '</li>' . "\n";
			}
			$output .= '  </ul>';
			$output .= '  </body>' . "\n";
			$output .= '</html>' . "\n";

			$this->response->setOutput($output);
		}
	}
}
