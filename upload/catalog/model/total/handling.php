<?php
class ModelTotalHandling extends Model {

	public function getTotal(array $taxes, float $total): array {
        $sub_total = (float)$this->cart->getSubTotal();

        if ($sub_total <= 0 || $sub_total >= (float)$this->config->get('handling_total')) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $this->language->load('total/handling');

        $fee = (float)$this->config->get('handling_fee');
        $new_taxes = [];

        if ($this->config->get('handling_tax_class_id')) {
            foreach ($this->tax->getRates($fee, $this->config->get('handling_tax_class_id')) as $tax_rate) {
                $new_taxes[$tax_rate['tax_rate_id']] = ($new_taxes[$tax_rate['tax_rate_id']] ?? 0) + $tax_rate['amount'];
            }
        }

        return [
            'total_data' => [[
                'code'       => 'handling',
                'title'      => $this->language->get('text_handling'),
                'text'       => $this->currency->format($fee, $this->config->get('config_currency')),
                'value'      => $fee,
                'sort_order' => $this->config->get('handling_sort_order')
            ]],
            'total' => $fee,
            'taxes' => $new_taxes,
        ];
    }
}
