<?php
/**
 * Class ModelTotalTotal
 *
 * @package NivoCart
 */
class ModelTotalTotal extends Model {
	/**
	 * Functions Get
	 */
    public function getTotal(array $taxes, float $total): array {
        $this->language->load('total/total');

        return [
            'total_data' => [[
                'code'       => 'total',
                'title'      => $this->language->get('text_total'),
                'text'       => $this->currency->format(max(0.0, $total), $this->config->get('config_currency')),
                'value'      => max(0.0, round($total, 2, PHP_ROUND_HALF_UP)),
                'sort_order' => $this->config->get('total_sort_order')
            ]],
            'total' => 0.0,
            'taxes' => [],
        ];
    }
}
