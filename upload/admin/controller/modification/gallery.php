<?php
/**
 * Class ControllerModificationGallery
 *
 * Optional modification: Gallery (customer photos). Moderation of the submissions, settings and the
 * texts of the gallery pages, and the install / uninstall of its own database table and image folders.
 *
 * Location: admin/controller/modification/gallery.php
 *
 * @package NivoCart
 */
class ControllerModificationGallery extends Controller {
	private array $error = [];
	private string $name = 'gallery';

	public function index(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/gallery');

		// The table is created on install, show the install page if it is missing (e.g. restored database)
		if (!$this->model_modification_gallery->tableExists()) {
			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['heading_title'] = $this->language->get('heading_title');
			$this->data['text_install_message'] = $this->language->get('text_install_message');
			$this->data['text_install_btn'] = $this->language->get('text_install_btn');

			$this->data['install_url'] = $this->url->link('modification/gallery/setup', 'token=' . $this->session->data['token'], 'SSL');

			$this->data['breadcrumbs'] = $this->breadcrumbs();

			$this->template = 'modification/gallery_install.tpl';
			$this->children = [
				'common/header',
				'common/footer'
			];

			$this->response->setOutput($this->render());

			return;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('localisation/language');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			$content = [];

			foreach ((array)($this->request->post['gallery_page_content'] ?? []) as $language_id => $value) {
				$content[(int)$language_id] = [
					'title'            => trim((string)($value['title'] ?? '')),
					'meta_description' => trim((string)($value['meta_description'] ?? '')),
					'meta_keyword'     => trim((string)($value['meta_keyword'] ?? '')),
					'description'      => (string)($value['description'] ?? ''),
					'agreement'        => (string)($value['agreement'] ?? '')
				];
			}

			$this->model_setting_setting->editSetting('gallery_page', [
				'gallery_page_status'     => !empty($this->request->post['gallery_page_status']) ? 1 : 0,
				'gallery_page_limit'      => (int)$this->request->post['gallery_page_limit'],
				'gallery_page_rate_limit' => (int)$this->request->post['gallery_page_rate_limit'],
				'gallery_page_notify'     => !empty($this->request->post['gallery_page_notify']) ? 1 : 0,
				'gallery_page_watermark'  => trim((string)($this->request->post['gallery_page_watermark'] ?? '')),
				'gallery_page_filters'    => $this->postedFilters(),
				'gallery_page_content'    => $content
			]);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('modification/gallery', 'token=' . $this->session->data['token'] . '#tab-settings', 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$texts = [
			'tab_submissions', 'tab_settings', 'text_yes', 'text_no', 'text_all', 'text_pending', 'text_approved', 'text_refused',
			'text_add_filter', 'text_no_results', 'text_info', 'text_confirm', 'text_confirm_delete', 'text_none_selected',
			'column_image', 'column_pet', 'column_type', 'column_submitter', 'column_date_added', 'column_status', 'column_action',
			'entry_status', 'entry_limit', 'entry_rate_limit', 'entry_notify', 'entry_filters', 'help_filters', 'column_filter_name', 'column_sort_order', 'entry_watermark', 'entry_title', 'entry_meta_description',
			'entry_meta_keyword', 'entry_description', 'entry_agreement', 'help_status', 'help_limit', 'help_rate_limit', 'help_notify',
			'help_watermark', 'help_title', 'help_meta_description', 'help_meta_keyword', 'help_description', 'help_agreement',
			'button_approve', 'button_pending', 'button_refuse', 'button_delete', 'button_filter', 'button_edit', 'button_save',
			'button_cancel', 'text_pending_total'
		];

		foreach ($texts as $text) {
			$this->data[$text] = $this->language->get($text);
		}

		$this->data['error_warning'] = $this->error['warning'] ?? '';

		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		}

		$this->data['error_limit'] = $this->error['limit'] ?? '';
		$this->data['error_rate_limit'] = $this->error['rate_limit'] ?? '';
		$this->data['error_watermark'] = $this->error['watermark'] ?? '';

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['breadcrumbs'] = $this->breadcrumbs();

		$this->data['action'] = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] = $this->session->data['token'];

		// Settings (posted values win so a failed save keeps what was typed)
		$defaults = [
			'gallery_page_status'     => 0,
			'gallery_page_limit'      => 12,
			'gallery_page_rate_limit' => 5,
			'gallery_page_notify'     => 1,
			'gallery_page_watermark'  => ''
		];

		foreach ($defaults as $key => $default) {
			if (isset($this->request->post[$key])) {
				$this->data[$key] = $this->request->post[$key];
			} elseif ($this->config->get($key) !== null) {
				$this->data[$key] = $this->config->get($key);
			} else {
				$this->data[$key] = $default;
			}
		}

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->data['gallery_page_content'] = $this->request->post['gallery_page_content'] ?? (array)$this->config->get('gallery_page_content');

		$this->data['gallery_page_filters'] = isset($this->request->post['gallery_page_filters']) ? $this->postedFilters() : $this->getFilters();
		$this->data['error_filters'] = $this->error['filters'] ?? '';

		// Submissions
		$filter_name = $this->request->get['filter_name'] ?? '';
		$filter_status = $this->request->get['filter_status'] ?? '';
		$sort = $this->request->get['sort'] ?? 'date_added';
		$order = $this->request->get['order'] ?? 'DESC';
		$page = max(1, (int)($this->request->get['page'] ?? 1));

		$filter = [
			'filter_name'   => $filter_name,
			'filter_status' => $filter_status,
			'sort'          => $sort,
			'order'         => $order,
			'start'         => ($page - 1) * (int)$this->config->get('config_admin_limit'),
			'limit'         => (int)$this->config->get('config_admin_limit')
		];

		$url = $this->getListUrl($filter_name, $filter_status);

		$totals = $this->model_modification_gallery->getTotalsByStatus();

		$this->data['text_pending_total'] = sprintf($this->language->get('text_pending_total'), $totals[0], $totals[1], $totals[2]);

		$filter_names = $this->getFilterNames();

		$this->data['images'] = [];

		foreach ($this->model_modification_gallery->getImages($filter) as $result) {
			$this->data['images'][] = [
				'gallery_id'    => (int)$result['gallery_id'],
				'thumb'         => $result['image'] ? $this->url->link('modification/gallery/preview', 'token=' . $this->session->data['token'] . '&gallery_id=' . (int)$result['gallery_id'], 'SSL') : '',
				'pet_name'      => $result['pet_name'],
				'type'          => $filter_names[(int)$result['filter_id']] ?? '',
				'customer_name' => $result['customer_name'],
				'email'         => $result['email'],
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'status'        => (int)$result['status'],
				'selected'      => isset($this->request->post['selected']) && in_array($result['gallery_id'], (array)$this->request->post['selected']),
				'edit'          => $this->url->link('modification/gallery/edit', 'token=' . $this->session->data['token'] . '&gallery_id=' . (int)$result['gallery_id'] . $url, 'SSL')
			];
		}

		$image_total = $this->model_modification_gallery->getTotalImages($filter);

		$sort_url = $url . '&order=' . (($order === 'ASC') ? 'DESC' : 'ASC');

		$this->data['sort_pet'] = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'] . '&sort=pet_name' . $sort_url, 'SSL');
		$this->data['sort_type'] = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'] . '&sort=filter_id' . $sort_url, 'SSL');
		$this->data['sort_submitter'] = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'] . '&sort=customer_name' . $sort_url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'] . '&sort=date_added' . $sort_url, 'SSL');
		$this->data['sort_status'] = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'] . '&sort=status' . $sort_url, 'SSL');

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['filter_name'] = $filter_name;
		$this->data['filter_status'] = $filter_status;

		$this->data['approve'] = $this->url->link('modification/gallery/approve', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['pending'] = $this->url->link('modification/gallery/pending', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['refuse'] = $this->url->link('modification/gallery/refuse', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['delete'] = $this->url->link('modification/gallery/delete', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['navigation_hi'] = $this->config->get('config_pagination_hi');
		$this->data['navigation_lo'] = $this->config->get('config_pagination_lo');

		$pagination = new Pagination();
		$pagination->total = $image_total;
		$pagination->page = $page;
		$pagination->limit = (int)$this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'] . $url . '&sort=' . $sort . '&order=' . $order . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->template = 'modification/gallery.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	/**
	 * View and correct one submission.
	 */
	public function edit(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/gallery');

		$gallery_id = (int)($this->request->get['gallery_id'] ?? 0);

		$info = $this->model_modification_gallery->getImage($gallery_id);

		if (!$info) {
			$this->session->data['error'] = $this->language->get('error_not_found');

			$this->redirect($this->url->link('modification/gallery', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$url = $this->getListUrl($this->request->get['filter_name'] ?? '', $this->request->get['filter_status'] ?? '');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateForm()) {
			$this->model_modification_gallery->editImage($gallery_id, [
				'pet_name'    => trim((string)$this->request->post['pet_name']),
				'filter_id'   => (int)$this->request->post['filter_id'],
				'description' => trim((string)($this->request->post['description'] ?? ''))
			]);

			$this->session->data['success'] = $this->language->get('text_success_edit');

			$this->redirect($this->url->link('modification/gallery', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$texts = [
			'text_yes', 'text_no', 'text_pending', 'text_approved', 'text_refused', 'text_confirm',
			'text_confirm_delete', 'text_image_gone', 'entry_pet_name', 'entry_pet_type', 'entry_description', 'entry_reference',
			'entry_submitter', 'entry_email', 'entry_optin', 'entry_ip', 'entry_date_added', 'entry_status', 'entry_page', 'help_description_edit',
			'help_submitter', 'button_approve', 'button_pending', 'button_refuse', 'button_delete', 'button_save', 'button_cancel'
		];

		foreach ($texts as $text) {
			$this->data[$text] = $this->language->get($text);
		}

		$this->data['heading_title'] = $this->language->get('heading_title') . ' #' . $gallery_id;

		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['error_pet_name'] = $this->error['pet_name'] ?? '';
		$this->data['error_filter'] = $this->error['filter'] ?? '';
		$this->data['error_description'] = $this->error['description'] ?? '';

		$this->data['breadcrumbs'] = $this->breadcrumbs();

		$this->data['breadcrumbs'][] = [
			'text'      => '#' . $gallery_id,
			'href'      => $this->url->link('modification/gallery/edit', 'token=' . $this->session->data['token'] . '&gallery_id=' . $gallery_id, 'SSL'),
			'separator' => ' :: '
		];

		$this->data['action'] = $this->url->link('modification/gallery/edit', 'token=' . $this->session->data['token'] . '&gallery_id=' . $gallery_id . $url, 'SSL');
		$this->data['cancel'] = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['approve'] = $this->url->link('modification/gallery/approve', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['pending'] = $this->url->link('modification/gallery/pending', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['refuse'] = $this->url->link('modification/gallery/refuse', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('modification/gallery/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['gallery_id'] = $gallery_id;
		$this->data['status'] = (int)$info['status'];
		$this->data['has_image'] = (bool)$info['image'];
		$this->data['image'] = $info['image'] ? $this->url->link('modification/gallery/preview', 'token=' . $this->session->data['token'] . '&gallery_id=' . $gallery_id . '&v=' . strtotime($info['date_modified']), 'SSL') : '';

		$this->data['pet_name'] = $this->request->post['pet_name'] ?? $info['pet_name'];
		$this->data['filter_id'] = (int)($this->request->post['filter_id'] ?? $info['filter_id']);
		$this->data['filters'] = $this->getFilterNames();
		$this->data['description'] = $this->request->post['description'] ?? $info['description'];

		$this->data['reference'] = $this->reference($gallery_id);
		$this->data['customer_name'] = $info['customer_name'];
		$this->data['email'] = $info['email'];
		$this->data['optin'] = (int)$info['contact_optin'];
		$this->data['ip'] = $info['ip'];
		$this->data['date_added'] = date($this->language->get('date_format_time'), strtotime($info['date_added']));

		$this->data['page'] = '';

		if ((int)$info['status'] === 1) {
			$this->data['page'] = $this->pageUrl($gallery_id);
		}

		$this->template = 'modification/gallery_form.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	public function approve(): void {
		$this->changeStatus(1);
	}

	public function pending(): void {
		$this->changeStatus(0);
	}

	public function refuse(): void {
		$this->changeStatus(2);
	}

	/**
	 * Set the status of the selected submissions: 0 = Pending, 1 = Approved, 2 = Refused (the image is deleted).
	 *
	 * @param int $status
	 */
	private function changeStatus(int $status): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/gallery');

		$redirect = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'], 'SSL');

		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} elseif (empty($this->request->post['selected']) || !is_array($this->request->post['selected'])) {
			$this->session->data['error'] = $this->language->get('text_none_selected');
		} else {
			$done = 0;
			$failed = 0;

			foreach ($this->request->post['selected'] as $gallery_id) {
				$info = $this->model_modification_gallery->getImage((int)$gallery_id);

				if (!$info) {
					continue;
				}

				$was = (int)$info['status'];

				if ($this->model_modification_gallery->setStatus((int)$gallery_id, $status)) {
					$done++;

					if (($status === 1) && ($was !== 1)) {
						$this->notifyApproved($this->model_modification_gallery->getImage((int)$gallery_id));
					}
				} else {
					$failed++;
				}
			}

			if ($failed) {
				$this->session->data['error'] = sprintf($this->language->get('error_status'), $failed);
			}

			if ($done) {
				$this->session->data['success'] = sprintf($this->language->get('text_success_status'), $done);
			}
		}

		$this->redirect($redirect);
	}

	public function delete(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/gallery');

		$redirect = $this->url->link('modification/gallery', 'token=' . $this->session->data['token'], 'SSL');

		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} elseif (empty($this->request->post['selected']) || !is_array($this->request->post['selected'])) {
			$this->session->data['error'] = $this->language->get('text_none_selected');
		} else {
			foreach ($this->request->post['selected'] as $gallery_id) {
				$this->model_modification_gallery->deleteImage((int)$gallery_id);
			}

			$this->session->data['success'] = $this->language->get('text_success_delete');
		}

		$this->redirect($redirect);
	}

	/**
	 * Shows the stored image of a submission, also while it waits for approval (the pending folder is blocked from the web).
	 */
	public function preview(): void {
		$this->load->model('modification/gallery');

		$info = $this->model_modification_gallery->getImage((int)($this->request->get['gallery_id'] ?? 0));

		$path = $info ? $this->model_modification_gallery->getPath((string)$info['image']) : '';

		if (!$path || !is_file($path)) {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			return;
		}

		$this->response->addHeader('Content-Type: image/webp');
		$this->response->addHeader('Cache-Control: private, max-age=0, must-revalidate');
		$this->response->addHeader('X-Content-Type-Options: nosniff');

		$this->response->setOutput((string)file_get_contents($path));
	}

	/**
	 * Called by Extensions > Modifications when the modification is installed.
	 */
	public function install(): void {
		$this->load->model('modification/gallery');

		$this->model_modification_gallery->install();
	}

	/**
	 * Called by Extensions > Modifications when the modification is uninstalled.
	 * The settings (group "gallery") are removed by the Modifications page itself.
	 */
	public function uninstall(): void {
		$this->language->load('modification/' . $this->name);

		$this->load->model('modification/gallery');

		$this->model_modification_gallery->uninstall();

		$this->session->data['success'] = $this->language->get('text_uninstalled');
	}

	/**
	 * Install page button: create the missing table and open the settings.
	 */
	public function setup(): void {
		$this->language->load('modification/' . $this->name);

		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
			$this->session->data['error'] = $this->language->get('error_permission');
		} else {
			$this->install();
		}

		$this->redirect($this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'));
	}

	/**
	 * Tell the submitter the image is published (only when they ticked the contact box).
	 *
	 * @param array<string, mixed> $info
	 *
	 * @return void
	 */
	private function notifyApproved(array $info): void {
		if (!$info || empty($info['contact_optin']) || !emailIsValid($info['email'])) {
			return;
		}

		$remove = HTTPS_CATALOG . 'index.php?route=modification/gallery/remove&gallery_id=' . (int)$info['gallery_id'] . '&token=' . $info['token'];

		$mail = new Mail();
		$mail->setTo($info['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_approved_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8'));
		$mail->setText(html_entity_decode(sprintf(
			$this->language->get('email_approved_text'),
			html_entity_decode($info['customer_name'], ENT_QUOTES, 'UTF-8'),
			html_entity_decode($info['pet_name'], ENT_QUOTES, 'UTF-8'),
			$this->pageUrl((int)$info['gallery_id']),
			$remove,
			html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')
		), ENT_QUOTES, 'UTF-8'));
		$mail->send();
	}

	/**
	 * Public page address of a published image.
	 *
	 * @param int $gallery_id
	 *
	 * @return string
	 */
	private function pageUrl(int $gallery_id): string {
		$keyword = $this->model_modification_gallery->getKeyword($gallery_id);

		if ($keyword && $this->config->get('config_seo_url')) {
			return HTTPS_CATALOG . $keyword;
		}

		return HTTPS_CATALOG . 'index.php?route=modification/gallery/info&gallery_id=' . $gallery_id;
	}

	/**
	 * @param int $gallery_id
	 *
	 * @return string
	 */
	private function reference(int $gallery_id): string {
		return 'GAL-' . str_pad((string)$gallery_id, 5, '0', STR_PAD_LEFT);
	}

	/**
	 * @param string $filter_name
	 * @param string $filter_status
	 *
	 * @return string
	 */
	private function getListUrl(string $filter_name, string $filter_status): string {
		$url = '';

		if ($filter_name !== '') {
			$url .= '&filter_name=' . urlencode(html_entity_decode($filter_name, ENT_QUOTES, 'UTF-8'));
		}

		if ($filter_status !== '') {
			$url .= '&filter_status=' . urlencode($filter_status);
		}

		return $url;
	}

	private function breadcrumbs(): array {
		return [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			],
			[
				'text'      => $this->language->get('text_modification'),
				'href'      => $this->url->link('extension/modification', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			],
			[
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('modification/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			]
		];
	}

	/**
	 * The saved filters, in sort order.
	 *
	 * @return array<int, array{filter_id: int, sort_order: int, name: array<int, string>}>
	 */
	protected function getFilters(): array {
		$filters = $this->config->get('gallery_page_filters');

		if (is_string($filters)) {
			$filters = json_decode($filters, true);
		}

		$result = [];

		foreach ((array)$filters as $filter) {
			if (is_array($filter) && !empty($filter['filter_id'])) {
				$result[] = [
					'filter_id'  => (int)$filter['filter_id'],
					'sort_order' => (int)($filter['sort_order'] ?? 0),
					'name'       => (array)($filter['name'] ?? [])
				];
			}
		}

		usort($result, fn($a, $b) => $a['sort_order'] <=> $b['sort_order'] ?: $a['filter_id'] <=> $b['filter_id']);

		return $result;
	}

	/**
	 * @return array<int, string> Filter names (admin language) by filter number
	 */
	protected function getFilterNames(): array {
		$names = [];

		foreach ($this->getFilters() as $filter) {
			$names[$filter['filter_id']] = (string)($filter['name'][(int)$this->config->get('config_language_id')] ?? reset($filter['name']) ?: '');
		}

		return $names;
	}

	/**
	 * The filters as posted by the settings form. New rows get the next free number.
	 *
	 * @return array<int, array{filter_id: int, sort_order: int, name: array<int, string>}>
	 */
	protected function postedFilters(): array {
		$this->load->model('modification/gallery');

		$next = max($this->model_modification_gallery->getMaxFilter(), 0);

		foreach ((array)($this->request->post['gallery_page_filters'] ?? []) as $row) {
			$next = max($next, (int)($row['filter_id'] ?? 0));
		}

		foreach ($this->getFilters() as $filter) {
			$next = max($next, $filter['filter_id']);
		}

		$filters = [];

		foreach ((array)($this->request->post['gallery_page_filters'] ?? []) as $row) {
			if (!is_array($row)) {
				continue;
			}

			$names = [];

			foreach ((array)($row['name'] ?? []) as $language_id => $name) {
				$names[(int)$language_id] = trim((string)$name);
			}

			$filter_id = (int)($row['filter_id'] ?? 0);

			$filters[] = [
				'filter_id'  => $filter_id ?: ++$next,
				'sort_order' => (int)($row['sort_order'] ?? 0),
				'name'       => $names
			];
		}

		return $filters;
	}

	protected function validateFilters(): void {
		$filters = $this->postedFilters();

		if (!$filters) {
			$this->error['filters'] = $this->language->get('error_filters');

			return;
		}

		$this->load->model('localisation/language');
		$this->load->model('modification/gallery');

		$kept = [];

		foreach ($filters as $filter) {
			$kept[$filter['filter_id']] = true;

			foreach ($this->model_localisation_language->getLanguages() as $language) {
				$length = mb_strlen(html_entity_decode($filter['name'][(int)$language['language_id']] ?? '', ENT_QUOTES, 'UTF-8'), 'UTF-8');

				if (($length < 1) || ($length > 32)) {
					$this->error['filters'] = $this->language->get('error_filters');
				}
			}
		}

		// A filter that is in use cannot be removed
		foreach ($this->getFilters() as $filter) {
			if (!isset($kept[$filter['filter_id']])) {
				$total = $this->model_modification_gallery->getTotalByFilter($filter['filter_id']);

				if ($total) {
					$this->error['filters'] = sprintf($this->language->get('error_filter_in_use'), $this->getFilterNames()[$filter['filter_id']] ?? $filter['filter_id'], $total);
				}
			}
		}
	}

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$limit = $this->request->post['gallery_page_limit'] ?? '';

		if (!ctype_digit((string)$limit) || ((int)$limit < 1) || ((int)$limit > 100)) {
			$this->error['limit'] = $this->language->get('error_limit');
		}

		$rate_limit = $this->request->post['gallery_page_rate_limit'] ?? '';

		if (!ctype_digit((string)$rate_limit) || ((int)$rate_limit < 1) || ((int)$rate_limit > 50)) {
			$this->error['rate_limit'] = $this->language->get('error_rate_limit');
		}

		$this->validateFilters();

		$watermark = trim((string)($this->request->post['gallery_page_watermark'] ?? ''));

		if (mb_strlen($watermark, 'UTF-8') > 60) {
			$this->error['watermark'] = $this->language->get('error_watermark');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}

	protected function validateForm(): bool {
		if (!$this->user->hasPermission('modify', 'modification/' . $this->name)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$length = mb_strlen(html_entity_decode(trim((string)($this->request->post['pet_name'] ?? '')), ENT_QUOTES, 'UTF-8'), 'UTF-8');

		if (($length < 2) || ($length > 32)) {
			$this->error['pet_name'] = $this->language->get('error_pet_name');
		}

		if (!isset($this->getFilterNames()[(int)($this->request->post['filter_id'] ?? 0)])) {
			$this->error['filter'] = $this->language->get('error_filter');
		}

		$length = mb_strlen(html_entity_decode(trim((string)($this->request->post['description'] ?? '')), ENT_QUOTES, 'UTF-8'), 'UTF-8');

		if ($length > 250) {
			$this->error['description'] = $this->language->get('error_description');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return empty($this->error);
	}
}
