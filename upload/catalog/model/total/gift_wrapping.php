<?php
/**
 * Class ModelTotalGiftWrapping
 *
 * @package NivoCart
 */
class ModelTotalGiftWrapping extends Model {
	/**
	 * Functions Get
	 */
	public function getTotal(array $taxes, float $total): array {
		if (!isset($this->session->data['wrapping']) || !$this->config->get('gift_wrapping_status') || $this->cart->getSubTotal() <= 0) {
			return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
		}

		$this->language->load('total/gift_wrapping');

		$price = (float)$this->config->get('gift_wrapping_price');
		$new_taxes = [];

		if ($this->config->get('gift_wrapping_tax_class_id')) {
			foreach ($this->tax->getRates($price, $this->config->get('gift_wrapping_tax_class_id')) as $tax_rate) {
				$new_taxes[$tax_rate['tax_rate_id']] = ($new_taxes[$tax_rate['tax_rate_id']] ?? 0) + $tax_rate['amount'];
			}
		}

		return [
			'total_data' => [[
				'code'       => 'gift_wrapping',
				'title'      => $this->language->get('text_gift_wrapping'),
				'text'       => $this->currency->format($price, $this->config->get('config_currency')),
				'value'      => $price,
				'sort_order' => $this->config->get('gift_wrapping_sort_order')
			]],
			'total' => $price,
			'taxes' => $new_taxes,
		];
	}
}
