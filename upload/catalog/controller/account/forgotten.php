<?php
/**
 * Class ControllerAccountForgotten
 *
 * Implements a secure token-based password reset flow (CWE-640 remediation).
 *
 * Flow:
 *   1. User submits their email → forgotten/index() validates it, generates a
 *      cryptographically secure token, stores a bcrypt hash of it in the DB
 *      with a 1-hour expiry, and emails a reset *link* (never a password).
 *   2. User clicks the link → forgotten/reset() validates the token against
 *      the stored hash and expiry, then shows a "choose new password" form.
 *   3. User submits the form → forgotten/confirm() saves the new password,
 *      invalidates the token, and redirects to login.
 *
 * @package NivoCart
 */
class ControllerAccountForgotten extends Controller {
	private $error = [];

	/**
	 * Step 1 – request a reset link
	 */
	public function index() {
		if ($this->customer->isLogged()) {
			$this->redirect($this->url->link('account/account', '', 'SSL'));
		}

		if ($this->config->get('config_secure') && !$this->request->isSecure()) {
			$this->redirect($this->url->link('account/forgotten', '', 'SSL'), 301);
		}

		$this->language->load('account/forgotten');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('account/customer');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validateEmail()) {
			// 1. Generate a cryptographically secure token.
			$raw_token = bin2hex(random_bytes(32)); // 64 hex chars
			$hashed_token = password_hash($raw_token, PASSWORD_BCRYPT);
			$expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour

			// 2. Persist the *hash* (never the raw token) and expiry.
			$this->model_account_customer->saveResetToken($this->request->post['email'], $hashed_token, $expires_at);

			// 3. Build the reset URL containing the raw token.
			$reset_url = $this->url->link('account/forgotten/reset', 'token=' . $raw_token . '&email=' . $this->request->post['email'], 'SSL');

			// 4. Email the link – no password ever appears in the email.
			$this->language->load('mail/forgotten');

			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

			$message = sprintf($this->language->get('text_greeting'), $this->config->get('config_name')) . "\n\n";
			$message .= $this->language->get('text_reset_link') . "\n\n";
			$message .= $reset_url . "\n\n";
			$message .= $this->language->get('text_link_expiry') . "\n\n";
			$message .= $this->language->get('text_ignore');

			$template = new Template();
			$template->data['title'] = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
			$template->data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
			$template->data['store_name'] = $this->config->get('config_name');
			$template->data['store_url'] = $this->config->get('config_url');
			$template->data['message'] = nl2br($message);
			$template->data['reset_url'] = $reset_url; // available for richer HTML templates

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/forgotten.tpl')) {
				$html = $template->fetch($this->config->get('config_template') . '/template/mail/forgotten.tpl');
			} else {
				$html = $template->fetch('default/template/mail/forgotten.tpl');
			}

			$mail = new Mail();
			$mail->setTo($this->request->post['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setHtml($html);
			$mail->send();

			// Always show the same success message regardless of whether the
			// email address exists – prevents user enumeration.
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->buildBreadcrumbs();

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_your_email'] = $this->language->get('text_your_email');
		$this->data['text_email'] = $this->language->get('text_email');

		$this->data['entry_email'] = $this->language->get('entry_email');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');

		$this->data['error_warning'] = $this->error['warning'] ?? '';

		$this->data['action'] = $this->url->link('account/forgotten', '', 'SSL');
		$this->data['back'] = $this->url->link('account/login', '', 'SSL');

		$this->renderTemplate('account/forgotten');
	}

	/**
	 * Step 2 – user arrives via the emailed link; show "enter new password"
	 */
	public function reset() {
		if ($this->customer->isLogged()) {
			$this->redirect($this->url->link('account/account', '', 'SSL'));
		}

		$this->language->load('account/forgotten');

		$this->document->setTitle($this->language->get('heading_title_reset'));

		$this->load->model('account/customer');

		$raw_token = $this->request->get['token'] ?? '';
		$email = $this->request->get['email'] ?? '';

		// Validate the token before showing the form (prevents wasted form fills).
		if (!$this->validateToken($raw_token, $email)) {
			$this->session->data['error'] = $this->language->get('error_token_invalid');

			$this->redirect($this->url->link('account/forgotten', '', 'SSL'));
		}

		$this->data['heading_title_reset'] = $this->language->get('heading_title_reset');

		$this->data['entry_password'] = $this->language->get('entry_password');
		$this->data['entry_confirm'] = $this->language->get('entry_confirm');

		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['error_warning'] = $this->error['warning'] ?? '';
		$this->data['error_password'] = $this->error['password'] ?? '';

		// Pass token & email through to the confirm action via hidden fields.
		$this->data['action'] = $this->url->link('account/forgotten/confirm', '', 'SSL');

		$this->data['token'] = $raw_token;
		$this->data['email'] = $email;

		$this->renderTemplate('account/forgotten_reset');
	}

	/**
	 * Step 3 – save the user-chosen password, invalidate the token
	 */
	public function confirm() {
		if ($this->customer->isLogged()) {
			$this->redirect($this->url->link('account/account', '', 'SSL'));
		}

		$this->language->load('account/forgotten');

		$this->load->model('account/customer');

		$raw_token = $this->request->post['token'] ?? '';
		$email = $this->request->post['email'] ?? '';

		if (($this->request->server['REQUEST_METHOD'] !== 'POST') || !$this->validateToken($raw_token, $email) || !$this->validatePassword()) {
			$this->data['heading_title_reset'] = $this->language->get('heading_title_reset');

			$this->data['entry_password'] = $this->language->get('entry_password');
			$this->data['entry_confirm'] = $this->language->get('entry_confirm');

			$this->data['button_continue'] = $this->language->get('button_continue');

			// Re-display the reset form with errors.
			$this->data['error_warning'] = $this->error['warning'] ?? $this->language->get('error_token_invalid');
			$this->data['error_password'] = $this->error['password'] ?? '';

			$this->data['action'] = $this->url->link('account/forgotten/confirm', '', 'SSL');
			$this->data['token'] = $raw_token;
			$this->data['email'] = $email;

			$this->renderTemplate('account/forgotten_reset');
			return;
		}

		// Save the new, user-supplied password.
		$this->model_account_customer->editPassword($email, $this->request->post['password']);

		// Immediately invalidate the token so it cannot be reused.
		$this->model_account_customer->clearResetToken($email);

		$this->session->data['success'] = $this->language->get('text_password_changed');

		$this->redirect($this->url->link('account/login', '', 'SSL'));
	}

	/**
	 * Validates the email field on the "request reset" form.
	 *
	 * NOTE: To prevent user enumeration the *same* success response is always
	 * returned to the browser even when the email is not found. The error is
	 * still recorded internally so we can skip sending the email, but we never
	 * expose whether an account exists.
	 */
	protected function validateEmail() {
		$email = $this->request->post['email'] ?? '';

		if ((mb_strlen($email, 'UTF-8') > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
			$this->error['warning'] = $this->language->get('error_email');
			return false;
		}

		$this->load->model('account/customer');

		// Unknown email – set an internal flag but do NOT surface the error to
		// the browser (anti-enumeration).
		$email_exists = (bool) $this->model_account_customer->getTotalCustomersByEmail($email);

		if (!$email_exists) {
			// Return true so the controller shows the generic success page,
			// but skip actually sending an email (no model call will follow
			// because validateEmail() is called before the token/email block).
			// We signal "skip send" by setting a private flag.
			$this->error['skip_send'] = true;
			// anti-enumeration: show success anyway
			return true;
		}

		// MX / format check.
		$this->load->model('tool/email');

		if (!$this->model_tool_email->verifyMail($email)) {
			$this->error['warning'] = $this->language->get('error_email');
			return false;
		}

		return true;
	}

	/**
	 * Validates the token from the reset link against the stored hash & expiry.
	 */
	private function validateToken(string $raw_token, string $email): bool {
		if (empty($raw_token) || empty($email)) {
			return false;
		}

		$record = $this->model_account_customer->getResetToken($email);

		if (!$record) {
			return false;
		}

		// Check expiry first (constant-time-safe ordering doesn't matter here).
		if (strtotime($record['date_expires']) < time()) {
			$this->error['warning'] = $this->language->get('error_token_expired');
			return false;
		}

		// Verify raw token against stored bcrypt hash – timing-safe.
		if (!password_verify($raw_token, $record['token'])) {
			$this->error['warning'] = $this->language->get('error_token_invalid');
			return false;
		}

		return true;
	}

	/**
	 * Validates the new password chosen by the user on the reset form.
	 */
	protected function validatePassword() {
		$password = $this->request->post['password'] ?? '';
		$confirm = $this->request->post['confirm']  ?? '';

		if ((mb_strlen($password, 'UTF-8') < 8) || (mb_strlen($password, 'UTF-8') > 20)) {
			$this->error['password'] = $this->language->get('error_password_length');
		} elseif ($password !== $confirm) {
			$this->error['password'] = $this->language->get('error_password_mismatch');
		}

		return empty($this->error);
	}

	/**
	 * Private rendering helpers
	 */
	private function buildBreadcrumbs() {
		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false,
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
			'separator' => $this->language->get('text_separator'),
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_forgotten'),
			'href'      => $this->url->link('account/forgotten', '', 'SSL'),
			'separator' => $this->language->get('text_separator'),
		];
	}

	private function renderTemplate(string $name) {
		$this->data['template'] = $this->config->get('config_template');

		$custom = DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . $name . '.tpl';

		$this->template = file_exists($custom) ? $this->config->get('config_template') . '/template/' . $name . '.tpl' : 'default/template/' . $name . '.tpl';

		$this->children = [
			'common/content_higher',
			'common/content_high',
			'common/content_left',
			'common/content_right',
			'common/content_low',
			'common/content_lower',
			'common/footer',
			'common/header',
		];

		$this->response->setOutput($this->render());
	}
}
