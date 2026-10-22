<?php
/**
 * Class ControllerAccountReview
 *
 * Customer account product reviews: list of purchased products, review form (modal or page) and save.
 *
 * @package NivoCart
 */
class ControllerAccountReview extends Controller {
	/** Products per page in the list */
	private const LIST_LIMIT = 10;

	/** Review text length limits (characters) */
	private const TEXT_MIN = 25;
	private const TEXT_MAX = 1000;

	/** Maximum new reviews a customer can submit per hour */
	private const RATE_LIMIT = 5;

	public function index() {
		if (!$this->hasAccess()) {
			$this->redirectToLogin('account/review');

			return;
		}

		$this->language->load('account/review');

		if (!$this->config->get('config_review_status')) {
			$this->redirect($this->url->link('account/account', '', 'SSL'));

			return;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = $this->getBreadcrumbs();

		$this->load->model('account/review');

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		foreach (['heading_title', 'text_intro', 'text_empty', 'column_product', 'column_orders', 'column_review', 'column_approval', 'column_action', 'button_continue', 'button_close', 'error_load'] as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		$page = isset($this->request->get['page']) ? max(1, (int)$this->request->get['page']) : 1;

		$data = [
			'start' => ($page - 1) * self::LIST_LIMIT,
			'limit' => self::LIST_LIMIT
		];

		$product_total = $this->model_account_review->getTotalPurchasedProducts();

		$results = $this->model_account_review->getPurchasedProducts($data);

		$this->data['products'] = [];

		foreach ($results as $result) {
			$orders = [];

			foreach (explode(',', (string)$result['order_ids']) as $order_id) {
				$orders[] = [
					'order_id' => (int)$order_id,
					'href'     => $this->url->link('account/order/info', 'order_id=' . (int)$order_id, 'SSL')
				];
			}

			if (!$result['review_id']) {
				$review_text = $this->language->get('text_review_none');
				$approval_text = $this->language->get('text_approval_none');
				$approval_class = 'none';
				$action = 'write';

			} else {
				$review_text = $this->language->get('text_review_submitted');
				$action = 'locked';

				switch ((int)$result['review_status']) {
					case ModelAccountReview::STATUS_APPROVED:
						$approval_text = $this->language->get('text_approval_approved');
						$approval_class = 'approved';

						break;
					case ModelAccountReview::STATUS_REFUSED:
						$approval_text = $this->language->get('text_approval_refused');
						$approval_class = 'refused';

						break;
					default:
						$approval_text = $this->language->get('text_approval_pending');
						$approval_class = 'pending';
						$action = 'edit';

						break;
				}
			}

			$this->data['products'][] = [
				'product_id'     => (int)$result['product_id'],
				'name'           => $result['product_name'],
				'variant'        => $result['variant'] ? htmlspecialchars((string)$result['variant'], ENT_QUOTES, 'UTF-8', false) : '',
				'href'           => $this->url->link('product/product', 'product_id=' . (int)$result['product_id'], 'SSL'),
				'orders'         => $orders,
				'review_text'    => $review_text,
				'approval_text'  => $approval_text,
				'approval_class' => $approval_class,
				'action'         => $action,
				'button_text'    => ($action === 'edit') ? $this->language->get('button_edit') : $this->language->get('button_write'),
				'form'           => $this->url->link('account/review/form', 'product_id=' . (int)$result['product_id'], 'SSL')
			];
		}

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = self::LIST_LIMIT;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/review', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('account/review_list');

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

		$this->response->setOutput($this->render());
	}

	/**
	 * Review form. Renders a fragment for the modal (modal=1) or a full page otherwise.
	 */
	public function form() {
		$is_modal = !empty($this->request->get['modal']);

		$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		$this->language->load('account/review');

		$error = '';
		$back = $this->url->link('account/review', '', 'SSL');
		$button_back = $this->language->get('button_back');
		$back_close = true;

		if (!$this->hasAccess()) {
			if (!$is_modal) {
				$this->redirectToLogin('account/review/form', 'product_id=' . $product_id);

				return;
			}

			$error = $this->language->get('error_login');
			$back = $this->url->link('account/login', '', 'SSL');
			$button_back = $this->language->get('button_login');
			$back_close = false;
		}

		$product = [];
		$review = [];

		$this->load->model('account/review');

		if (!$error) {
			if (!$this->config->get('config_review_status')) {
				$error = $this->language->get('error_disabled');
			} else {
				$product = $this->model_account_review->getPurchasedProduct($product_id);

				if (!$product) {
					$error = $this->language->get('error_purchase');
				} else {
					$review = $this->model_account_review->getCustomerReview($product_id);

					if ($review && (int)$review['status'] !== ModelAccountReview::STATUS_PENDING) {
						$error = $this->language->get('error_locked');
					}
				}
			}
		}

		$edit = (bool)$review;

		$this->data['heading_title'] = $this->language->get($edit ? 'text_form_edit' : 'text_form_write');

		$this->document->setTitle($this->data['heading_title']);

		foreach (['entry_product', 'entry_orders', 'entry_author', 'entry_date', 'entry_rating', 'entry_text', 'help_author', 'help_text', 'text_wait', 'button_cancel', 'button_submit', 'error_general'] as $key) {
			$this->data[$key] = $this->language->get($key);
		}

		$this->data['text_max'] = self::TEXT_MAX;

		$this->data['is_modal'] = $is_modal;
		$this->data['form_error'] = $error;
		$this->data['back'] = $back;
		$this->data['button_back'] = $button_back;
		$this->data['back_close'] = $back_close;
		$this->data['cancel'] = $this->url->link('account/review', '', 'SSL');

		$this->data['product_id'] = $product_id;
		$this->data['product_name'] = $product ? $product['product_name'] : '';
		$this->data['variant'] = ($product && $product['variant']) ? htmlspecialchars((string)$product['variant'], ENT_QUOTES, 'UTF-8', false) : '';
		$this->data['orders'] = '';

		if ($product) {
			$order_ids = [];

			foreach (explode(',', (string)$product['order_ids']) as $order_id) {
				$order_ids[] = '#' . (int)$order_id;
			}

			$this->data['orders'] = implode(', ', $order_ids);
		}

		$this->data['author'] = $this->hasAccess() ? $this->getAuthorName() : '';
		$this->data['date'] = date($this->language->get('date_format_short'));

		$this->data['rating'] = $review ? (int)$review['rating'] : 0;
		$this->data['text'] = $review ? $review['text'] : '';

		$this->data['ratings'] = [];

		foreach ([5, 4, 3, 2, 1] as $value) {
			$this->data['ratings'][] = [
				'value' => $value,
				'title' => sprintf($this->language->get('text_stars'), $value)
			];
		}

		$this->data['token'] = '';

		if (!$error) {
			$this->session->data['review_token'] = bin2hex(random_bytes(16));

			$this->data['token'] = $this->session->data['review_token'];
		}

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('account/review_form');

		if (!$is_modal) {
			$this->data['breadcrumbs'] = $this->getBreadcrumbs();

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

		$this->response->setOutput($this->render());
	}

	/**
	 * Save a new review, or update a pending one (JSON).
	 */
	public function save() {
		$this->language->load('account/review');

		$json = [];

		if ($this->request->server['REQUEST_METHOD'] !== 'POST' || !$this->config->get('config_review_status')) {
			$json['error']['warning'] = $this->language->get('error_disabled');

			$this->sendJson($json);

			return;
		}

		if (!$this->hasAccess()) {
			$json['error']['warning'] = $this->language->get('error_login');
			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('account/login', '', 'SSL'));

			$this->sendJson($json);

			return;
		}

		// Form token (CSRF) and honeypot
		$session_token = isset($this->session->data['review_token']) ? (string)$this->session->data['review_token'] : '';
		$posted_token = isset($this->request->post['token']) && is_string($this->request->post['token']) ? $this->request->post['token'] : '';

		if ($session_token === '' || !hash_equals($session_token, $posted_token) || !empty($this->request->post['website'])) {
			$json['error']['warning'] = $this->language->get('error_token');

			$this->sendJson($json);

			return;
		}

		$this->load->model('account/review');

		// Server side eligibility: completed order, and not already processed
		$product_id = isset($this->request->post['product_id']) ? (int)$this->request->post['product_id'] : 0;

		if (!$this->model_account_review->hasPurchased($product_id)) {
			$json['error']['warning'] = $this->language->get('error_purchase');

			$this->sendJson($json);

			return;
		}

		$review = $this->model_account_review->getCustomerReview($product_id);

		if ($review && (int)$review['status'] !== ModelAccountReview::STATUS_PENDING) {
			$json['error']['warning'] = $this->language->get('error_locked');

			$this->sendJson($json);

			return;
		}

		if (!$review && $this->model_account_review->getTotalReviewsSince(60) >= self::RATE_LIMIT) {
			$json['error']['warning'] = $this->language->get('error_rate');

			$this->sendJson($json);

			return;
		}

		// Field validation
		$rating = isset($this->request->post['rating']) ? (int)$this->request->post['rating'] : 0;

		if ($rating < 1 || $rating > 5) {
			$json['error']['rating'] = $this->language->get('error_rating');
		}

		$text = '';

		if (isset($this->request->post['text']) && is_string($this->request->post['text'])) {
			$text = trim(str_replace(["\r\n", "\r"], "\n", $this->request->post['text']));
		}

		$length = mb_strlen(html_entity_decode($text, ENT_QUOTES, 'UTF-8'), 'UTF-8');

		if ($length < self::TEXT_MIN || $length > self::TEXT_MAX) {
			$json['error']['text'] = $this->language->get('error_text');
		}

		if (isset($json['error'])) {
			$json['error']['warning'] = $this->language->get('error_general');

			$this->sendJson($json);

			return;
		}

		$data = [
			'text'   => $text,
			'rating' => $rating
		];

		if ($review) {
			$this->model_account_review->editReview((int)$review['review_id'], $data);

			$message = $this->language->get('text_success_edit');

		} else {
			$data['author'] = $this->getAuthorName();

			if (!$this->model_account_review->addReview($product_id, $data)) {
				// Another request created the review first
				$json['error']['warning'] = $this->language->get('error_locked');

				$this->sendJson($json);

				return;
			}

			$message = $this->language->get('text_success');
		}

		unset($this->session->data['review_token']);

		$this->session->data['success'] = $message;

		$json['success'] = $message;
		$json['redirect'] = str_replace('&amp;', '&', $this->url->link('account/review', '', 'SSL'));

		$this->sendJson($json);
	}

	/**
	 * Logged in, secure session and login not expired
	 */
	private function hasAccess(): bool {
		return $this->customer->isLogged() && $this->customer->isSecure() && !$this->customer->loginExpired();
	}

	private function redirectToLogin(string $route, string $args = ''): void {
		if ($this->customer->isLogged()) {
			$this->customer->logout();
		}

		$this->session->data['redirect'] = $this->url->link($route, $args, 'SSL');

		$this->redirect($this->url->link('account/login', '', 'SSL'));
	}

	private function sendJson(array $json): void {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private function getBreadcrumbs(): array {
		return [
			[
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			],
			[
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			],
			[
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/review', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			]
		];
	}

	/**
	 * Public author name: full firstname and the initial of the surname ("John D").
	 */
	private function getAuthorName(): string {
		$firstname = trim(html_entity_decode($this->customer->getFirstName(), ENT_QUOTES, 'UTF-8'));
		$lastname = trim(html_entity_decode($this->customer->getLastName(), ENT_QUOTES, 'UTF-8'));

		$name = $firstname;

		if ($lastname !== '') {
			$name .= ' ' . mb_strtoupper(mb_substr($lastname, 0, 1, 'UTF-8'), 'UTF-8');
		}

		$name = trim($name);

		if ($name === '') {
			$name = $this->language->get('text_customer');
		}

		return htmlspecialchars(mb_substr($name, 0, 64, 'UTF-8'), ENT_QUOTES, 'UTF-8');
	}
}
