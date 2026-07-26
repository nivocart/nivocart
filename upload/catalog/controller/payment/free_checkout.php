<?php
/**
 * Class ControllerPaymentFreeCheckout
 *
 * @package NivoCart
 */
class ControllerPaymentFreeCheckout extends Controller {
	/** Error array Placeholder */

	protected function index() {
		$this->language->load('payment/free_checkout');

		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_instruction'] = $this->language->get('text_instruction');

		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->data['continue'] = $this->url->link('checkout/success', '', 'SSL');

		// Theme
		$this->data['template'] = $this->config->get('config_template');

		$this->resolveTemplate('payment/free_checkout');

		$this->render();
	}

	public function confirm() {
		// Prevent free checkout direct access exploits
		if (strtolower($this->session->data['payment_method']['code']) !== 'free_checkout') {
			return;
		}

		if ($this->session->data['payment_method']['code'] === 'free_checkout') {
			$this->language->load('payment/free_checkout');

			$this->load->model('checkout/order');

			$comment = $this->language->get('text_instruction') . "\n\n";

			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('free_checkout_order_status_id'), $comment, true);
		}
	}
}
