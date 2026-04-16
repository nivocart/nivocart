<?php
class ModelTotalSubTotal extends Model {

    public function getTotal(array $taxes, float $total): array {
        $this->language->load('total/sub_total');

        $sub_total = (float)$this->cart->getSubTotal();

        foreach ($this->session->data['vouchers'] ?? [] as $voucher) {
            $sub_total += (float)$voucher['amount'];
        }

		// Round to two decimals
		$sub_total = round($sub_total, 2, PHP_ROUND_HALF_UP);

        return [
            'total_data' => [[
                'code'       => 'sub_total',
                'title'      => $this->language->get('text_sub_total'),
                'text'       => $this->currency->format($sub_total, $this->config->get('config_currency')),
                'value'      => $sub_total,
                'sort_order' => $this->config->get('sub_total_sort_order')
            ]],
            'total' => $sub_total,
            'taxes' => [],
        ];
    }
}
