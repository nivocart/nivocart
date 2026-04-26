<?php
/**
 * Class ModelTotalShipping
 *
 * @package NivoCart
 */
class ModelTotalShipping extends Model {
	/**
	 * Functions Get
	 */
    public function getTotal(array $taxes, float $total): array {
        if (!$this->cart->hasShipping() || !isset($this->session->data['shipping_method'])) {
            return $this->emptyResult();
        }

        $method = $this->session->data['shipping_method'];

		$title = (string)$method['title'];
        $cost = (float)$method['cost'];

        $new_taxes = [];

        if (!empty($method['tax_class_id'])) {
            foreach ($this->tax->getRates($cost, $method['tax_class_id']) as $tax_rate) {
                $new_taxes[$tax_rate['tax_rate_id']] = ($new_taxes[$tax_rate['tax_rate_id']] ?? 0) + $tax_rate['amount'];
            }
        }

        return [
            'total_data' => [[
                'code'       => 'shipping',
                'title'      => $title,
                'text'       => $this->currency->format($cost, $this->config->get('config_currency')),
                'value'      => $cost,
                'sort_order' => $this->config->get('shipping_sort_order')
            ]],
            'total' => $cost,
            'taxes' => $new_taxes,
        ];
    }

    private function emptyResult(): array {
        return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
    }
}