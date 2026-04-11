<?php
class ModelTotalLowOrderFee extends Model {

    public function getTotal(array $taxes, float $total): array {
        $sub_total = (float)$this->cart->getSubTotal();

        if ($sub_total <= 0 || $sub_total >= (float)$this->config->get('low_order_fee_total')) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $this->language->load('total/low_order_fee');

        $fee = (float)$this->config->get('low_order_fee_fee');
        $new_taxes = [];

        if ($this->config->get('low_order_fee_tax_class_id')) {
            foreach ($this->tax->getRates($fee, $this->config->get('low_order_fee_tax_class_id')) as $tax_rate) {
                $new_taxes[$tax_rate['tax_rate_id']] = ($new_taxes[$tax_rate['tax_rate_id']] ?? 0) + $tax_rate['amount'];
            }
        }

        return [
            'total_data' => [[
                'code'       => 'low_order_fee',
                'title'      => $this->language->get('text_low_order_fee'),
                'text'       => $this->currency->format($fee, $this->config->get('config_currency')),
                'value'      => $fee,
                'sort_order' => $this->config->get('low_order_fee_sort_order')
            ]],
            'total' => $fee,
            'taxes' => $new_taxes,
        ];
    }
}
