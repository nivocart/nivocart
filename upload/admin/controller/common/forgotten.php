<?php
/**
 * Class ControllerCommonForgotten
 *
 * @package NivoCart
 */
class ControllerCommonForgotten extends Controller {
	private const MAX_REQUESTS_IP = 5;
	private const MAX_REQUESTS_EMAIL = 3;

	private $error = [];

	public function index() {
		if (!$this->config->get('config_password')) {
			$this->redirect($this->url->link('common/login', '', 'SSL'));
		}

		$this->language->load('common/forgotten');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('user/user');

		if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {
			// Only send an email when the address belongs to an administrator, but always
			// show the same confirmation so that valid addresses cannot be discovered
			if ($this->model_user_user->getTotalUsersByEmail($this->request->post['email'])) {
				$this->language->load('mail/forgotten');

				$code = bin2hex(random_bytes(32));

				$this->model_user_user->editCode($this->request->post['email'], $code);

				$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

				$message = sprintf($this->language->get('text_greeting'), $this->config->get('config_name')) . "\n\n";
				$message .= sprintf($this->language->get('text_change'), $this->config->get('config_name')) . "\n\n";
				$message .= $this->url->link('common/reset', 'code=' . $code, 'SSL') . "\n\n";
				$message .= sprintf($this->language->get('text_expiry'), ModelUserUser::CODE_LIFETIME) . "\n\n";
				$message .= sprintf($this->language->get('text_ip'), $this->request->server['REMOTE_ADDR']) . "\n\n";

				$mail = new Mail();
				$mail->setTo($this->request->post['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($this->config->get('config_name'));
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('common/login', '', 'SSL'));
		}

		if ($this->user->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] === $this->session->data['token'])) {
			$this->data['logged'] = true;
		} else {
			$this->data['logged'] = false;
		}

		// Breadcrumbs
		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_forgotten'),
			'href'      => $this->url->link('common/forgotten', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_your_email'] = $this->language->get('text_your_email');
		$this->data['text_email'] = $this->language->get('text_email');

		$this->data['entry_email'] = $this->language->get('entry_email');

		$this->data['button_reset'] = $this->language->get('button_reset');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['action'] = $this->url->link('common/forgotten', '', 'SSL');

		$this->data['cancel'] = $this->url->link('common/login', '', 'SSL');

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} else {
			$this->data['email'] = '';
		}

		$this->template = 'common/forgotten.tpl';
		$this->children = [
			'common/header_login',
			'common/footer_login'
		];

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		$ip = $this->request->server['REMOTE_ADDR'] ?? '';
		$email = (isset($this->request->post['email']) && is_string($this->request->post['email'])) ? mb_strtolower(mb_substr($this->request->post['email'], 0, 96, 'UTF-8'), 'UTF-8') : '';

		// Rate limiting: per IP address and per email address
		if (($this->model_user_user->getTotalRecoveryAttempts('request', 'ip', $ip) >= self::MAX_REQUESTS_IP) || (($email !== '') && ($this->model_user_user->getTotalRecoveryAttempts('request', 'email', $email) >= self::MAX_REQUESTS_EMAIL))) {
			$this->error['warning'] = $this->language->get('error_attempts');

			return false;
		}

		$this->model_user_user->addRecoveryAttempt('request', $email, $ip);

		if (!isset($this->request->post['email']) || !is_string($this->request->post['email']) || (!emailIsValid($this->request->post['email']))) {
			$this->error['warning'] = $this->language->get('error_email');
		}

		return empty($this->error);
	}
}
