<?php
class ModelTotalShipping extends Model {

    public function getTotal(&$total_data, &$total, &$taxes) {
        if (!$this->cart->hasShipping() || !isset($this->session->data['shipping_method'])) {
            return;
        }

        $method = $this->session->data['shipping_method'];
        $cost   = (float)$method['cost'];

        $total_data[] = [
            'code'       => 'shipping',
            'title'      => $method['title'],
            'text'       => $this->currency->format($cost, $this->config->get('config_currency')),
            'value'      => $cost,
            'sort_order' => $this->config->get('shipping_sort_order')
        ];

        // Accumulate shipping taxes if applicable
        if (!empty($method['tax_class_id'])) {
            $tax_rates = $this->tax->getRates($cost, $method['tax_class_id']);

            foreach ($tax_rates as $tax_rate) {
                $taxes[$tax_rate['tax_rate_id']] = 
                    ($taxes[$tax_rate['tax_rate_id']] ?? 0) + $tax_rate['amount'];
            }
        }

        $total += $cost;
    }
}