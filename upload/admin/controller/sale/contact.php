<?php
/**
 * Class ControllerSaleContact
 *
 * @package NivoCart
 */
class ControllerSaleContact extends Controller {
	/** Error array Placeholder */

	public function index() {
		$this->language->load('sale/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['text_newsletter'] = $this->language->get('text_newsletter');
		$this->data['text_customer_all'] = $this->language->get('text_customer_all');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_customer_group'] = $this->language->get('text_customer_group');
		$this->data['text_affiliate_all'] = $this->language->get('text_affiliate_all');
		$this->data['text_affiliate'] = $this->language->get('text_affiliate');
		$this->data['text_product'] = $this->language->get('text_product');

		$this->data['entry_store'] = $this->language->get('entry_store');
		$this->data['entry_to'] = $this->language->get('entry_to');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_customer'] = $this->language->get('entry_customer');
		$this->data['entry_affiliate'] = $this->language->get('entry_affiliate');
		$this->data['entry_product'] = $this->language->get('entry_product');
		$this->data['entry_subject'] = $this->language->get('entry_subject');
		$this->data['entry_message'] = $this->language->get('entry_message');

		$this->data['button_send'] = $this->language->get('button_send');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['token'] = $this->session->data['token'];

		$this->data['breadcrumbs'] = [];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		];

		$this->data['breadcrumbs'][] = [
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/contact', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		];

		$this->data['cancel'] = $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('sale/customer_group');

		$group_customers = [];

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups($group_customers);

		$this->template = 'sale/contact.tpl';
		$this->children = [
			'common/header',
			'common/footer'
		];

		$this->response->setOutput($this->render());
	}

	public function send() {
		$this->language->load('sale/contact');

		$json = [];

		if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}

		if (!$this->user->hasPermission('modify', 'sale/contact')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['subject']) {
			$json['error']['subject'] = $this->language->get('error_subject');
		}

		if (!$this->request->post['message']) {
			$json['error']['message'] = $this->language->get('error_message');
		}

		if ($json) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$this->load->model('setting/store');
		$this->load->model('sale/customer');
		$this->load->model('sale/customer_group');
		$this->load->model('sale/affiliate');
		$this->load->model('sale/order');

		$store_info = $this->model_setting_store->getStore($this->request->post['store_id']);
		$store_name = $store_info ? $store_info['name'] : $this->config->get('config_name');

		$page = (int)($this->request->get['page'] ?? 1);

		$email_total = 0;

		$emails = [];

		[$emails, $email_total] = match ($this->request->post['to']) {
			'newsletter' => $this->getCustomerEmails(['filter_newsletter' => 1], $page),

			'customer_all' => $this->getCustomerEmails([], $page),

			'customer_group' => $this->getCustomerEmails(['filter_customer_group_id' => $this->request->post['customer_group_id']], $page),

			'customer' => [
				$this->resolveIndividualEmails(
					$this->request->post['customer'] ?? [],
					fn ($id) => $this->model_sale_customer->getCustomer($id)
				),
				0
			],

			'affiliate_all' => $this->getAffiliateEmails([], $page),

			'affiliate' => [
				$this->resolveIndividualEmails(
					$this->request->post['affiliate'] ?? [],
					fn ($id) => $this->model_sale_affiliate->getAffiliate($id)
				),
				0
			],

			'product' => $this->getProductEmails($this->request->post['product'] ?? [], $page),

			default => [[], 0]
		};

		if ($emails) {
			$start = ($page - 1) * 10;
			$end = $start + 10;

			$json['success'] = ($end < $email_total) ? sprintf($this->language->get('text_sent'), $start, $email_total) : $this->language->get('text_success');

			$json['next'] = ($end < $email_total) ? str_replace('&amp;', '&', $this->url->link('sale/contact/send', 'token=' . $this->session->data['token'] . '&page=' . ($page + 1), 'SSL')) : '';

			$message = '<html dir="ltr" lang="en">' . "\n";
			$message .= '<head>' . "\n";
			$message .= '<title>' . $this->request->post['subject'] . '</title>' . "\n";
			$message .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
			$message .= '</head>' . "\n";
			$message .= '<body>' . html_entity_decode($this->request->post['message'], ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
			$message .= '</html>' . "\n";

			foreach ($emails as $email) {
				if (preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					$mail = new Mail();
					$mail->setTo($email);
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
					$mail->setSubject(html_entity_decode($this->request->post['subject'], ENT_QUOTES, 'UTF-8'));
					$mail->setHtml($message);
					$mail->send();
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	// Email Send helper functions
	private function getCustomerEmails(array $filters, int $page): array {
		$data = array_merge($filters, [
			'start' => ($page - 1) * 10,
			'limit' => 10
		]);

		$total = $this->model_sale_customer->getTotalCustomers($data);
		$results = $this->model_sale_customer->getCustomers($data);

		return [array_column($results, 'email'), $total];
	}

	private function getAffiliateEmails(array $filters, int $page): array {
		$data = array_merge($filters, [
			'start' => ($page - 1) * 10,
			'limit' => 10
		]);

		$total = $this->model_sale_affiliate->getTotalAffiliates($data);
		$results = $this->model_sale_affiliate->getAffiliates($data);

		return [array_column($results, 'email'), $total];
	}

	private function getProductEmails(array $products, int $page): array {
		$total = $this->model_sale_order->getTotalEmailsByProductsOrdered($products);
		$results = $this->model_sale_order->getEmailsByProductsOrdered($products, ($page - 1) * 10, 10);

		return [array_column($results, 'email'), $total];
	}

	private function resolveIndividualEmails(array $ids, callable $fetcher): array {
		$emails = [];

		foreach ($ids as $id) {
			$info = $fetcher($id);

			if ($info) {
				$emails[] = $info['email'];
			}
		}

		return $emails;
	}
}
