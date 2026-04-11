<?php
class ModelTotalGiftWrapping extends Model {

    public function getTotal(array $taxes, float $total): array {
        if (!isset($this->session->data['wrapping']) || !$this->config->get('gift_wrapping_status') || $this->cart->getSubTotal() <= 0) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $this->language->load('total/gift_wrapping');

        $price = (float)$this->config->get('gift_wrapping_price');

        return [
            'total_data' => [[
                'code'       => 'gift_wrapping',
                'title'      => $this->language->get('text_gift_wrapping'),
                'text'       => $this->currency->format($price, $this->config->get('config_currency')),
                'value'      => $price,
                'sort_order' => $this->config->get('gift_wrapping_sort_order')
            ]],
            'total' => $price,
            'taxes' => [],
        ];
    }
}
