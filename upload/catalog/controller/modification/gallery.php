<?php
/**
 * Class ControllerModificationGallery (Catalog)
 *
 * Gallery of customer photos: list, image page, submission form, publishing agreement and the
 * "remove my photo" page reached from the email link. The submitter's details are never shown.
 *
 * Location: catalog/controller/modification/gallery.php
 *
 * @package NivoCart
 */
class ControllerModificationGallery extends Controller {
	private const DESCRIPTION_MAX = 250;

	/**
	 * Gallery list: grid or list view, latest submission first, with the filters.
	 */
	public function index() {
		if (!$this->isActive()) {
			return $this->notFound();
		}

		$this->language->load('modification/gallery');

		$this->load->model('modification/gallery');
		$this->load->model('tool/image');

		$content = $this->getContent();

		$title = ($content['title'] !== '') ? $content['title'] : $this->language->get('heading_title');

		$filters = $this->getFilters();

		$type = (isset($this->request->get['type']) && isset($filters[(int)$this->request->get['type']])) ? (int)$this->request->get['type'] : 0;

		$default_limit = max(1, (int)$this->config->get('gallery_page_limit') ?: 12);

		$limits = array_unique([$default_limit, 24, 48, 96]);

		sort($limits);

		$limit = (isset($this->request->get['limit']) && in_array((int)$this->request->get['limit'], $limits, true)) ? (int)$this->request->get['limit'] : $default_limit;

		$page = max(1, (int)($this->request->get['page'] ?? 1));

		$this->document->setTitle($title);
		$this->document->setDescription($content['meta_description']);
		$this->document->setKeywords($content['meta_keyword']);

		$this->addAssets();

		$url = '';

		if ($type) {
			$url .= '&type=' . $type;
		}

		$limit_url = ($limit !== $default_limit) ? '&limit=' . $limit : '';

		$filter = [
			'filter_id'   => $type,
			'start'       => ($page - 1) * $limit,
			'limit'       => $limit
		];

		$total = $this->model_modification_gallery->getTotalImages($filter);

		$this->data['images'] = [];

		foreach ($this->model_modification_gallery->getImages($filter) as $result) {
			$thumb = $this->model_tool_image->resize($result['image'], 300, 300);

			if (!$thumb) {
				continue;
			}

			$this->data['images'][] = [
				'name'        => $result['pet_name'],
				'type'        => $filters[(int)$result['filter_id']] ?? '',
				'description' => $result['description'],
				'thumb'       => $thumb,
				'thumb_2x'    => $this->model_tool_image->resize($result['image'], 600, 600),
				'alt'         => sprintf($this->language->get('text_alt'), $result['pet_name'], $filters[(int)$result['filter_id']] ?? ''),
				'href'        => $this->url->link('modification/gallery/info', 'gallery_id=' . $result['gallery_id'], 'SSL')
			];
		}

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([['text' => $title, 'href' => $this->url->link('modification/gallery', '', 'SSL')]]);

		$this->data['heading_title'] = $title;
		$this->data['description'] = html_entity_decode($content['description'], ENT_QUOTES, 'UTF-8');

		$this->data['text_empty'] = $this->language->get('text_empty');
		$this->data['text_grid'] = $this->language->get('text_grid');
		$this->data['text_list'] = $this->language->get('text_list');
		$this->data['text_limit'] = $this->language->get('text_limit');
		$this->data['text_submit'] = $this->language->get('text_submit');

		$this->data['submit'] = $this->url->link('modification/gallery/submit', '', 'SSL');

		$this->data['types'] = [[
			'text'   => $this->language->get('text_all'),
			'href'   => $this->url->link('modification/gallery', ltrim($limit_url, '&'), 'SSL'),
			'active' => !$type
		]];

		foreach ($filters as $filter_id => $name) {
			$this->data['types'][] = [
				'text'   => $name,
				'href'   => $this->url->link('modification/gallery', 'type=' . $filter_id . $limit_url, 'SSL'),
				'active' => ($filter_id === $type)
			];
		}

		$this->data['limits'] = [];

		foreach ($limits as $value) {
			$this->data['limits'][] = [
				'text'     => $value,
				'href'     => $this->url->link('modification/gallery', ltrim($url . (($value !== $default_limit) ? '&limit=' . $value : ''), '&'), 'SSL'),
				'selected' => ($value === $limit)
			];
		}

		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('modification/gallery', ltrim($url . $limit_url, '&') . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		// Canonical and previous / next page for the search engines
		$page_last = (int)ceil($total / $limit);

		$base = ltrim($url . $limit_url, '&');

		$this->document->addLink($this->url->link('modification/gallery', $base . (($page > 1) ? '&page=' . $page : ''), 'SSL'), 'canonical');

		if ($page > 1) {
			$this->document->addLink($this->url->link('modification/gallery', $base . (($page > 2) ? '&page=' . ($page - 1) : ''), 'SSL'), 'prev');
		}

		if ($page < $page_last) {
			$this->document->addLink($this->url->link('modification/gallery', $base . '&page=' . ($page + 1), 'SSL'), 'next');
		}

		$this->renderPage('modification/gallery_list');
	}

	/**
	 * One image on its own page, with its own address, title, description and structured data.
	 */
	public function info() {
		if (!$this->isActive()) {
			return $this->notFound();
		}

		$this->language->load('modification/gallery');

		$this->load->model('modification/gallery');
		$this->load->model('tool/image');

		$gallery_id = (int)($this->request->get['gallery_id'] ?? 0);

		$info = $this->model_modification_gallery->getImage($gallery_id);

		$large = $info ? $this->model_tool_image->resize($info['image'], 600, 600) : null;

		if (!$info || !$large) {
			return $this->notFound();
		}

		$content = $this->getContent();

		$gallery_title = ($content['title'] !== '') ? $content['title'] : $this->language->get('heading_title');

		$type = $this->getFilters()[(int)$info['filter_id']] ?? '';

		$name = html_entity_decode($info['pet_name'], ENT_QUOTES, 'UTF-8');
		$description = html_entity_decode($info['description'], ENT_QUOTES, 'UTF-8');

		$heading = sprintf($this->language->get('text_info_title'), $info['pet_name'], $type);

		$store = html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$this->document->setTitle($heading . ' - ' . $gallery_title);

		if ($description !== '') {
			$this->document->setDescription(htmlspecialchars(mb_strimwidth($description, 0, 155, '...', 'UTF-8'), ENT_QUOTES, 'UTF-8'));
		} else {
			$this->document->setDescription(htmlspecialchars(sprintf($this->language->get('text_info_meta'), $name, $type, $store), ENT_QUOTES, 'UTF-8'));
		}

		$this->document->addLink($this->url->link('modification/gallery/info', 'gallery_id=' . $gallery_id, 'SSL'), 'canonical');

		$this->addAssets();

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([
			['text' => $gallery_title, 'href' => $this->url->link('modification/gallery', '', 'SSL')],
			['text' => $info['pet_name'], 'href' => $this->url->link('modification/gallery/info', 'gallery_id=' . $gallery_id, 'SSL')]
		]);

		$this->data['heading_title'] = $heading;
		$this->data['description'] = $info['description'];

		$this->data['image'] = $large;
		$this->data['image_2x'] = $this->imageUrl($info['image']);
		$this->data['alt'] = sprintf($this->language->get('text_alt'), $info['pet_name'], $type);

		$this->data['text_credit'] = $this->language->get('text_credit');
		$this->data['text_back'] = $this->language->get('text_back');
		$this->data['text_previous'] = $this->language->get('text_previous');
		$this->data['text_next'] = $this->language->get('text_next');
		$this->data['text_submit'] = $this->language->get('text_submit');

		$this->data['back'] = $this->url->link('modification/gallery', '', 'SSL');
		$this->data['submit'] = $this->url->link('modification/gallery/submit', '', 'SSL');
		$this->data['agreement'] = $this->url->link('modification/gallery/agreement', '', 'SSL');
		$this->data['text_agreement'] = $this->language->get('heading_agreement');

		$neighbours = $this->model_modification_gallery->getNeighbours($gallery_id);

		// The older image is the "previous" one, the newer image is the "next" one
		$this->data['previous'] = $neighbours['previous'] ? $this->url->link('modification/gallery/info', 'gallery_id=' . $neighbours['previous'], 'SSL') : '';
		$this->data['next'] = $neighbours['next'] ? $this->url->link('modification/gallery/info', 'gallery_id=' . $neighbours['next'], 'SSL') : '';

		// Structured data for the image
		$size = @getimagesize(DIR_IMAGE . $info['image']);

		$schema = [
			'@context'             => 'https://schema.org',
			'@type'                => 'ImageObject',
			'contentUrl'           => $this->imageUrl($info['image']),
			'thumbnailUrl'         => (string)$this->model_tool_image->resize($info['image'], 300, 300),
			'name'                 => sprintf($this->language->get('text_info_title'), $name, $type),
			'description'          => ($description !== '') ? $description : sprintf($this->language->get('text_info_meta'), $name, $type, $store),
			'uploadDate'           => date('c', strtotime($info['date_added'])),
			'creditText'           => $this->language->get('text_credit'),
			'acquireLicensePage'   => str_replace('&amp;', '&', $this->url->link('modification/gallery/agreement', '', 'SSL')),
			'representativeOfPage' => true
		];

		if ($size) {
			$schema['width'] = (int)$size[0];
			$schema['height'] = (int)$size[1];
		}

		$this->data['json_ld'] = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

		$this->renderPage('modification/gallery_info');
	}

	/**
	 * The submission form with the crop tool.
	 */
	public function submit() {
		if (!$this->isActive()) {
			return $this->notFound();
		}

		$this->language->load('modification/gallery');

		$heading = $this->language->get('heading_submit');

		$this->document->setTitle($heading);

		$this->document->addLink($this->url->link('modification/gallery/submit', '', 'SSL'), 'canonical');

		$this->document->addStyle('catalog/view/javascript/jquery/cropper/cropper.min.css');
		$this->document->addScript('catalog/view/javascript/jquery/cropper/cropper.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/sfi/css/jquery.simplefileinput.min.css');
		$this->document->addScript('catalog/view/javascript/jquery/sfi/js/jquery.simplefileinput.min.js');

		$this->addAssets();

		$content = $this->getContent();

		$gallery_title = ($content['title'] !== '') ? $content['title'] : $this->language->get('heading_title');

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([
			['text' => $gallery_title, 'href' => $this->url->link('modification/gallery', '', 'SSL')],
			['text' => $heading, 'href' => $this->url->link('modification/gallery/submit', '', 'SSL')]
		]);

		$this->data['heading_title'] = $heading;

		$keys = [
			'text_form_intro', 'text_pet', 'text_photo', 'text_owner', 'text_owner_help', 'text_choose', 'text_photo_help', 'text_crop_hint',
			'text_wait', 'text_back', 'text_select', 'text_no_file', 'text_watermark_note', 'text_optin', 'text_agree_age', 'entry_pet_name', 'entry_filter',
			'entry_description', 'entry_fullname', 'entry_email', 'entry_captcha', 'help_description', 'help_pet_name', 'button_send',
			'error_general', 'error_js_type', 'error_js_small', 'error_no_photo'
		];

		foreach ($keys as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		$agreement = $this->url->link('modification/gallery/agreement', '', 'SSL');

		$this->data['text_agree_publish'] = sprintf($this->language->get('text_agree_publish'), $agreement);

		$this->data['filters'] = $this->getFilters();
		$this->data['description_max'] = self::DESCRIPTION_MAX;
		$this->data['action'] = $this->url->link('modification/gallery/save', '', 'SSL');
		$this->data['cancel'] = $this->url->link('modification/gallery', '', 'SSL');

		// Form token (CSRF) for this visit
		$this->session->data['gallery_token'] = bin2hex(random_bytes(16));

		$this->data['token'] = $this->session->data['gallery_token'];

		// A logged in customer does not have to type the name and email address again
		$this->data['fullname'] = '';
		$this->data['email'] = '';

		if ($this->customer->isLogged() && $this->customer->isSecure()) {
			$this->data['fullname'] = trim($this->customer->getFirstName() . ' ' . $this->customer->getLastName());
			$this->data['email'] = $this->customer->getEmail();
		}

		$this->data['captcha_image'] = $this->url->link('common/captcha', '', 'SSL');

		$this->renderPage('modification/gallery_form');
	}

	/**
	 * Receive a submission (JSON). The photo is checked, redrawn as a square WebP and kept private until approved.
	 */
	public function save() {
		$this->language->load('modification/gallery');

		$json = [];

		if (($this->request->server['REQUEST_METHOD'] !== 'POST') || !$this->isActive()) {
			$json['error']['warning'] = $this->language->get('error_disabled');

			return $this->sendJson($json);
		}

		// Form token (CSRF) and honeypot
		$session_token = isset($this->session->data['gallery_token']) ? (string)$this->session->data['gallery_token'] : '';
		$posted_token = (isset($this->request->post['token']) && is_string($this->request->post['token'])) ? $this->request->post['token'] : '';

		if (($session_token === '') || !hash_equals($session_token, $posted_token) || !empty($this->request->post['website'])) {
			$json['error']['warning'] = $this->language->get('error_token');

			return $this->sendJson($json);
		}

		$this->load->model('modification/gallery');

		$ip = (string)($this->request->server['REMOTE_ADDR'] ?? '');

		$rate_limit = max(1, (int)$this->config->get('gallery_page_rate_limit') ?: 5);

		if ($this->model_modification_gallery->getTotalByIp($ip) >= $rate_limit) {
			$json['error']['warning'] = $this->language->get('error_rate');

			return $this->sendJson($json);
		}

		// Fields
		$pet_name = $this->clean($this->request->post['pet_name'] ?? '');
		$filter_id = (int)($this->request->post['filter_id'] ?? 0);
		$description = $this->clean($this->request->post['description'] ?? '');
		$fullname = $this->clean($this->request->post['fullname'] ?? '');
		$email = $this->clean($this->request->post['email'] ?? '');

		$length = mb_strlen(html_entity_decode($pet_name, ENT_QUOTES, 'UTF-8'), 'UTF-8');

		if (($length < 2) || ($length > 32)) {
			$json['error']['pet_name'] = $this->language->get('error_pet_name');
		}

		if (!isset($this->getFilters()[$filter_id])) {
			$json['error']['filter_id'] = $this->language->get('error_filter');
		}

		if (mb_strlen(html_entity_decode($description, ENT_QUOTES, 'UTF-8'), 'UTF-8') > self::DESCRIPTION_MAX) {
			$json['error']['description'] = $this->language->get('error_description');
		}

		$length = mb_strlen(html_entity_decode($fullname, ENT_QUOTES, 'UTF-8'), 'UTF-8');

		if (($length < 2) || ($length > 64)) {
			$json['error']['fullname'] = $this->language->get('error_fullname');
		}

		if (!emailIsValid($email) || (mb_strlen($email, 'UTF-8') > 96)) {
			$json['error']['email'] = $this->language->get('error_email');
		} else {
			// Email MX Record check
			$this->load->model('tool/email');

			if (!$this->model_tool_email->verifyMail($email)) {
				$json['error']['email'] = $this->language->get('error_email');
			}
		}

		if (empty($this->request->post['agree_age'])) {
			$json['error']['agree_age'] = $this->language->get('error_agree_age');
		}

		if (empty($this->request->post['agree_publish'])) {
			$json['error']['agree_publish'] = $this->language->get('error_agree_publish');
		}

		if (!isset($this->request->post['captcha']) || empty($this->session->data['captcha']) || (strtoupper((string)$this->session->data['captcha']) !== strtoupper((string)$this->request->post['captcha']))) {
			// A failed attempt invalidates the code, so it cannot be guessed repeatedly
			unset($this->session->data['captcha']);

			$json['error']['captcha'] = $this->language->get('error_captcha');
		}

		if (empty($this->request->files['image']['name'])) {
			$json['error']['image'] = $this->language->get('error_no_photo');
		}

		if (isset($json['error'])) {
			$json['error']['warning'] = $this->language->get('error_general');

			return $this->sendJson($json);
		}

		// The photo (checked, redrawn and watermarked, saved in the private folder)
		$stored = $this->model_modification_gallery->saveUpload($this->request->files['image'], (string)$this->config->get('gallery_page_watermark'));

		if ($stored['error'] !== '') {
			$json['error']['image'] = $this->language->get($stored['error']);
			$json['error']['warning'] = $this->language->get('error_general');

			return $this->sendJson($json);
		}

		$added = $this->model_modification_gallery->addImage([
			'pet_name'      => $pet_name,
			'filter_id'     => $filter_id,
			'description'   => $description,
			'customer_name' => $fullname,
			'email'         => $email,
			'contact_optin' => !empty($this->request->post['contact_optin']) ? 1 : 0,
			'image'         => $stored['image'],
			'ip'            => $ip
		]);

		unset($this->session->data['captcha']);

		$reference = $this->reference($added['gallery_id']);

		$this->session->data['gallery_reference'] = $reference;

		$this->sendSubmitted($added, $reference, $pet_name, $fullname, $email);

		if ($this->config->get('gallery_page_notify') && $this->config->get('config_email')) {
			$this->sendAdminNotice($reference, $pet_name, $this->getFilters()[$filter_id]);
		}

		// The form token is used once
		unset($this->session->data['gallery_token']);

		$json['success'] = true;
		$json['redirect'] = str_replace('&amp;', '&', $this->url->link('modification/gallery/success', '', 'SSL'));

		return $this->sendJson($json);
	}

	/**
	 * Thank you page, shown after a successful submission.
	 */
	public function success() {
		if (!$this->isActive()) {
			return $this->notFound();
		}

		$this->language->load('modification/gallery');

		$this->document->setTitle($this->language->get('heading_submit'));

		$reference = isset($this->session->data['gallery_reference']) ? (string)$this->session->data['gallery_reference'] : '';

		if ($reference === '') {
			$this->redirect($this->url->link('modification/gallery', '', 'SSL'));
		}

		unset($this->session->data['gallery_reference']);

		$content = $this->getContent();

		$gallery_title = ($content['title'] !== '') ? $content['title'] : $this->language->get('heading_title');

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([
			['text' => $gallery_title, 'href' => $this->url->link('modification/gallery', '', 'SSL')],
			['text' => $this->language->get('heading_submit'), 'href' => $this->url->link('modification/gallery/submit', '', 'SSL')]
		]);

		$this->data['heading_title'] = $this->language->get('heading_submit');
		$this->data['text_message'] = sprintf($this->language->get('text_success'), htmlspecialchars($reference, ENT_QUOTES, 'UTF-8'));
		$this->data['button_continue'] = $this->language->get('text_back');
		$this->data['continue'] = $this->url->link('modification/gallery', '', 'SSL');

		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('common/success');

		$this->addChildren();

		$this->response->setOutput($this->render());
	}

	/**
	 * The Publishing Agreement and Privacy Policy of the Gallery.
	 */
	public function agreement() {
		$this->load->model('modification/gallery');

		if (!$this->model_modification_gallery->isInstalled()) {
			return $this->notFound();
		}

		$this->language->load('modification/gallery');

		$heading = $this->language->get('heading_agreement');

		$this->document->setTitle($heading);

		$this->document->addLink($this->url->link('modification/gallery/agreement', '', 'SSL'), 'canonical');

		$this->addAssets();

		$content = $this->getContent();

		$gallery_title = ($content['title'] !== '') ? $content['title'] : $this->language->get('heading_title');

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([
			['text' => $gallery_title, 'href' => $this->url->link('modification/gallery', '', 'SSL')],
			['text' => $heading, 'href' => $this->url->link('modification/gallery/agreement', '', 'SSL')]
		]);

		$this->data['heading_title'] = $heading;

		if (trim(strip_tags(html_entity_decode($content['agreement'], ENT_QUOTES, 'UTF-8'))) !== '') {
			$this->data['text'] = html_entity_decode($content['agreement'], ENT_QUOTES, 'UTF-8');
		} else {
			$this->data['text'] = sprintf($this->language->get('text_agreement_default'), htmlspecialchars(html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'), htmlspecialchars((string)$this->config->get('config_email'), ENT_QUOTES, 'UTF-8'));
		}

		$this->data['text_back'] = $this->language->get('text_back');
		$this->data['back'] = $this->url->link('modification/gallery', '', 'SSL');

		$this->renderPage('modification/gallery_agreement');
	}

	/**
	 * "Remove my photo": the link in the email opens a confirmation page, nothing is deleted until the
	 * button is pressed (email programs open links on their own to scan them).
	 */
	public function remove() {
		$this->load->model('modification/gallery');

		if (!$this->model_modification_gallery->isInstalled()) {
			return $this->notFound();
		}

		$this->language->load('modification/gallery');

		$heading = $this->language->get('heading_remove');

		$this->document->setTitle($heading);

		$this->response->addHeader('X-Robots-Tag: noindex, nofollow');

		$gallery_id = (int)($this->request->get['gallery_id'] ?? 0);
		$token = (isset($this->request->get['token']) && is_string($this->request->get['token'])) ? $this->request->get['token'] : '';

		$info = $this->model_modification_gallery->getImageByToken($gallery_id, $token);

		$this->data['heading_title'] = $heading;
		$this->data['text_back'] = $this->language->get('text_back');
		$this->data['back'] = $this->url->link('modification/gallery', '', 'SSL');
		$this->data['button_remove'] = $this->language->get('button_remove');
		$this->data['action'] = '';
		$this->data['state'] = 'invalid';
		$this->data['text_message'] = $this->language->get('text_remove_invalid');

		if ($info) {
			if ($this->request->server['REQUEST_METHOD'] === 'POST') {
				$this->model_modification_gallery->deleteImage($gallery_id);

				$this->sendRemoved($info);

				$this->data['state'] = 'deleted';
				$this->data['text_message'] = $this->language->get('text_removed');
			} else {
				$this->data['state'] = 'confirm';
				$this->data['text_message'] = sprintf($this->language->get('text_remove_confirm'), $info['pet_name']);
				$this->data['action'] = $this->url->link('modification/gallery/remove', 'gallery_id=' . $gallery_id . '&token=' . $token, 'SSL');
			}
		}

		$this->data['breadcrumbs'] = $this->getBreadcrumbs([['text' => $heading, 'href' => $this->url->link('modification/gallery/remove', '', 'SSL')]]);

		$this->renderPage('modification/gallery_remove');
	}

	/**
	 * Is the gallery installed and switched on?
	 *
	 * @return bool
	 */
	private function isActive(): bool {
		if (!$this->config->get('gallery_page_status')) {
			return false;
		}

		$this->load->model('modification/gallery');

		return $this->model_modification_gallery->isInstalled();
	}

	/**
	 * @return mixed
	 */
	private function notFound() {
		return $this->forward('error/not_found');
	}

	/**
	 * Gallery texts of the current language (page title, meta tags, description and agreement).
	 *
	 * @return array<string, string>
	 */
	private function getContent(): array {
		$content = $this->config->get('gallery_page_content');

		if (is_string($content)) {
			$content = json_decode($content, true);
		}

		$row = is_array($content) ? ($content[(int)$this->config->get('config_language_id')] ?? []) : [];

		return [
			'title'            => (string)($row['title'] ?? ''),
			'meta_description' => (string)($row['meta_description'] ?? ''),
			'meta_keyword'     => (string)($row['meta_keyword'] ?? ''),
			'description'      => (string)($row['description'] ?? ''),
			'agreement'        => (string)($row['agreement'] ?? '')
		];
	}

	/**
	 * The filters (set in the administration) with their names in the current language, in sort order.
	 *
	 * @return array<int, string>
	 */
	private function getFilters(): array {
		$filters = $this->config->get('gallery_page_filters');

		if (is_string($filters)) {
			$filters = json_decode($filters, true);
		}

		$list = [];

		foreach ((array)$filters as $filter) {
			if (is_array($filter) && !empty($filter['filter_id'])) {
				$names = (array)($filter['name'] ?? []);

				$name = (string)($names[(int)$this->config->get('config_language_id')] ?? (reset($names) ?: ''));

				if ($name !== '') {
					$list[] = ['filter_id' => (int)$filter['filter_id'], 'sort_order' => (int)($filter['sort_order'] ?? 0), 'name' => $name];
				}
			}
		}

		usort($list, fn($a, $b) => $a['sort_order'] <=> $b['sort_order'] ?: $a['filter_id'] <=> $b['filter_id']);

		$result = [];

		foreach ($list as $filter) {
			$result[$filter['filter_id']] = $filter['name'];
		}

		return $result;
	}

	/**
	 * Add the stylesheet of the Gallery (the theme's own gallery.css, else the default theme's).
	 *
	 * @return void
	 */
	private function addAssets(): void {
		$theme = (string)$this->config->get('config_template');

		$folder = file_exists(DIR_TEMPLATE . $theme . '/stylesheet/gallery.css') ? $theme : 'default';

		// The file date in the address makes browsers and caches fetch the stylesheet again after it changed
		$this->document->addStyle('catalog/view/theme/' . $folder . '/stylesheet/gallery.css?v=' . (int)filemtime(DIR_TEMPLATE . $folder . '/stylesheet/gallery.css'));
	}

	/**
	 * @param array<int, array<string, string>> $crumbs
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function getBreadcrumbs(array $crumbs): array {
		$breadcrumbs = [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			]
		];

		foreach ($crumbs as $crumb) {
			$breadcrumbs[] = [
				'text'      => $crumb['text'],
				'href'      => $crumb['href'],
				'separator' => $this->language->get('text_separator')
			];
		}

		return $breadcrumbs;
	}

	/**
	 * @param string $template
	 *
	 * @return void
	 */
	private function renderPage(string $template): void {
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate($template);

		$this->addChildren();

		$this->response->setOutput($this->render());
	}

	private function addChildren(): void {
		$this->children = [
			'common/content_higher',
			'common/content_high',
			'common/content_left',
			'common/content_right',
			'common/content_low',
			'common/content_lower',
			'common/footer',
			'common/header'
		];
	}

	/**
	 * @param array<string, mixed> $json
	 *
	 * @return void
	 */
	private function sendJson(array $json): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Text field from the form: one line, trimmed. (The request class has already made it HTML safe.)
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	private function clean($value): string {
		if (!is_string($value)) {
			return '';
		}

		return trim((string)preg_replace('/\s+/u', ' ', $value));
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
	 * Address of the stored image (900 px).
	 *
	 * @param string $image
	 *
	 * @return string
	 */
	private function imageUrl(string $image): string {
		$is_https = !empty($this->request->server['HTTPS']) && in_array($this->request->server['HTTPS'], ['on', '1'], true);

		$base = $is_https ? (string)$this->config->get('config_ssl') : (string)$this->config->get('config_url');

		return $base . 'image/' . implode('/', array_map('rawurlencode', explode('/', $image)));
	}

	/**
	 * Confirmation for the submitter, with the link to remove the photo.
	 *
	 * @param array<string, mixed> $added
	 * @param string               $reference
	 * @param string               $pet_name
	 * @param string               $fullname
	 * @param string               $email
	 *
	 * @return void
	 */
	private function sendSubmitted(array $added, string $reference, string $pet_name, string $fullname, string $email): void {
		$store = html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$remove = str_replace('&amp;', '&', $this->url->link('modification/gallery/remove', 'gallery_id=' . $added['gallery_id'] . '&token=' . $added['token'], 'SSL'));

		$mail = new Mail();
		$mail->setTo($email);
		$mail->setFrom((string)$this->config->get('config_email'));
		$mail->setSender($store);
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_submitted_subject'), $reference), ENT_QUOTES, 'UTF-8'));
		$mail->setText(html_entity_decode(sprintf(
			$this->language->get('email_submitted_text'),
			html_entity_decode($fullname, ENT_QUOTES, 'UTF-8'),
			html_entity_decode($pet_name, ENT_QUOTES, 'UTF-8'),
			$reference,
			$remove,
			$store
		), ENT_QUOTES, 'UTF-8'));
		$mail->send();
	}

	/**
	 * Confirmation that the photo and the details are deleted.
	 *
	 * @param array<string, mixed> $info
	 *
	 * @return void
	 */
	private function sendRemoved(array $info): void {
		if (!emailIsValid($info['email'])) {
			return;
		}

		$store = html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$reference = $this->reference((int)$info['gallery_id']);

		$mail = new Mail();
		$mail->setTo($info['email']);
		$mail->setFrom((string)$this->config->get('config_email'));
		$mail->setSender($store);
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_removed_subject'), $reference), ENT_QUOTES, 'UTF-8'));
		$mail->setText(html_entity_decode(sprintf(
			$this->language->get('email_removed_text'),
			html_entity_decode($info['customer_name'], ENT_QUOTES, 'UTF-8'),
			html_entity_decode($info['pet_name'], ENT_QUOTES, 'UTF-8'),
			$reference,
			$store
		), ENT_QUOTES, 'UTF-8'));
		$mail->send();
	}

	/**
	 * Tell the store owner that a new photo waits for approval.
	 *
	 * @param string $reference
	 * @param string $pet_name
	 * @param string $filter_name
	 *
	 * @return void
	 */
	private function sendAdminNotice(string $reference, string $pet_name, string $filter_name): void {
		$store = html_entity_decode((string)$this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

		$mail = new Mail();
		$mail->setTo((string)$this->config->get('config_email'));
		$mail->setFrom((string)$this->config->get('config_email'));
		$mail->setSender($store);
		$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_admin_subject'), $reference), ENT_QUOTES, 'UTF-8'));
		$mail->setText(html_entity_decode(sprintf(
			$this->language->get('email_admin_text'),
			$reference,
			html_entity_decode($pet_name, ENT_QUOTES, 'UTF-8'),
			html_entity_decode($filter_name, ENT_QUOTES, 'UTF-8')
		), ENT_QUOTES, 'UTF-8'));
		$mail->send();
	}
}
