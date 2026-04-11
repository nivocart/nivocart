<?php
class ModelTotalVoucher extends Model {

    public function getTotal(array $taxes, float $total): array {
        if (!isset($this->session->data['voucher'])) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $this->language->load('total/voucher');

        $this->load->model('checkout/voucher');

        $voucher_info = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);

        $amount = 0.0;

        if (!empty($voucher_info['amount'])) {
            $amount += (float)$voucher_info['amount'];
        }

        if (!empty($this->session->data['current_voucher_value'])) {
            $amount += (float)$this->session->data['current_voucher_value'];
        }

        if (!$amount) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        // Voucher can never exceed the running total
        $amount = min($amount, $total);

        return [
            'total_data' => [[
                'code'       => 'voucher',
                'title'      => sprintf($this->language->get('text_voucher'), $this->session->data['voucher']),
                'text'       => $this->currency->format(-$amount, $this->config->get('config_currency')),
                'value'      => -$amount,
                'sort_order' => $this->config->get('voucher_sort_order')
            ]],
            'total' => -$amount,
            'taxes' => [],
        ];
    }

    public function confirm(array $order_info, array $order_total): void {
        $code = $this->extractCodeFromTitle($order_total['title']);

        $this->load->model('checkout/voucher');

        $voucher_info = $this->model_checkout_voucher->getVoucher($code);

        if ($voucher_info) {
            $this->model_checkout_voucher->redeem(
                $voucher_info['voucher_id'],
                $order_info['order_id'],
                $order_total['value']
            );
        }
    }

    private function extractCodeFromTitle(string $title): string {
        $start = strpos($title, '(');
        $end = strrpos($title, ')');

        if ($start !== false && $end !== false && $end > $start) {
            return substr($title, $start + 1, $end - $start - 1);
        }

        return '';
    }
}
