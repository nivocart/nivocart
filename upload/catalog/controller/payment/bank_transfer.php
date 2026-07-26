<?php
/**
 * Class ControllerPaymentBankTransfer
 *
 * @package NivoCart
 */
class ControllerPaymentBankTransfer extends Controller {
	/** Error array Placeholder */

	protected function index() {
		$this->language->load('payment/bank_transfer');

		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_instruction'] = $this->language->get('text_instruction');
		$this->data['text_description'] = $this->language->get('text_description');
		$this->data['text_payment'] = $this->language->get('text_payment');

		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->data['bank'] = nl2br($this->config->get('bank_transfer_bank_' . $this->config->get('config_language_id')));

		$this->data['continue'] = $this->url->link('checkout/success', '', 'SSL');

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('payment/bank_transfer');

		$this->render();
	}

	public function confirm() {
		// Prevent bank transfer direct access exploits
		if (strtolower($this->session->data['payment_method']['code']) !== 'bank_transfer') {
			return;
		}

		if ($this->session->data['payment_method']['code'] === 'bank_transfer') {
			$this->language->load('payment/bank_transfer');

			$this->load->model('checkout/order');

			$comment = $this->language->get('text_instruction') . "\n\n";
			$comment .= $this->config->get('bank_transfer_bank_' . $this->config->get('config_language_id')) . "\n\n";
			$comment .= $this->language->get('text_payment');

			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('bank_transfer_order_status_id'), $comment, true);
		}
	}
}
