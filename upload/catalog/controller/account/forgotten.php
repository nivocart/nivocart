<?php
/**
 * Class ControllerAccountForgotten
 *
 * @package NivoCart
 */
class ControllerAccountForgotten extends Controller {
	private $error = [];

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

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->language->load('mail/forgotten');

			$password = substr(md5(mt_rand()), 0, 10);

			$this->model_account_customer->editPassword($this->request->post['email'], $password);

			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

			$message = sprintf($this->language->get('text_greeting'), $this->config->get('config_name')) . "\n\n";
			$message .= $this->language->get('text_password') . "\n\n";
			$message .= $password;

			// HTML Mail
			$template = new Template();

			$template->data['title'] = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
			$template->data['logo'] = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
			$template->data['store_name'] = $this->config->get('config_name');
			$template->data['store_url'] = $this->config->get('config_url');
			$template->data['message'] = nl2br($message);

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

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_forgotten'),
			'href'      => $this->url->link('account/forgotten', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		];

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_your_email'] = $this->language->get('text_your_email');
		$this->data['text_email'] = $this->language->get('text_email');

		$this->data['entry_email'] = $this->language->get('entry_email');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['action'] = $this->url->link('account/forgotten', '', 'SSL');
		$this->data['back'] = $this->url->link('account/login', '', 'SSL');

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/forgotten.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/forgotten.tpl';
		} else {
			$this->template = 'default/template/account/forgotten.tpl';
		}

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

	protected function validate() {
		if (!isset($this->request->post['email']) || (mb_strlen($this->request->post['email'], 'UTF-8') > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}

		// Check email is valid
		if (isset($this->request->post['email'])) {
			// Email exists check
			$this->load->model('account/customer');

			$email_unknown = $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email']) ? false : true;

			if ($email_unknown) {
				$this->error['warning'] = $this->language->get('error_unknown');
			}

			// Email MX Record check
			$this->load->model('tool/email');

			$email_valid = $this->model_tool_email->verifyMail($this->request->post['email']);

			if (!$email_valid) {
				$this->error['email'] = $this->language->get('error_email');
			}
		}

		return empty($this->error);
	}
}
