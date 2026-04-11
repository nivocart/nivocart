<?php
class ModelTotalPayPalFee extends Model {

    public function getTotal(array $taxes, float $total): array {
        $cart_total = (float)$this->cart->getTotal();

        $paypal_fee_max = $this->config->get('paypal_fee_total');

        if (!$cart_total || (!empty($paypal_fee_max) && $cart_total >= (float)$paypal_fee_max)) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        if (!$this->isPayPalPaymentMethod()) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $this->language->load('total/paypal_fee');

        $fee = $this->calculateFee($cart_total);

        $new_taxes = [];

        if ($this->config->get('paypal_fee_tax_class_id')) {
            foreach ($this->tax->getRates($fee, $this->config->get('paypal_fee_tax_class_id')) as $tax_rate) {
                $new_taxes[$tax_rate['tax_rate_id']] = ($new_taxes[$tax_rate['tax_rate_id']] ?? 0) + $tax_rate['amount'];
            }
        }

        return [
            'total_data' => [[
                'code'       => 'paypal_fee',
                'title'      => $this->language->get('text_paypal_fee'),
                'text'       => $this->currency->format($fee, $this->config->get('config_currency')),
                'value'      => $fee,
                'sort_order' => $this->config->get('paypal_fee_sort_order')
            ]],
            'total' => $fee,
            'taxes' => $new_taxes,
        ];
    }

    private function isPayPalPaymentMethod(): bool {
        $session_code = $this->session->data['payment_method']['code'] ?? '';

        $post_code = $this->request->post['payment'] ?? '';

        foreach ([$session_code, $post_code] as $code) {
            if (str_starts_with($code, 'pp_') || $code === 'paypal_email') {
                return true;
            }
        }

        return false;
    }

    private function calculateFee(float $cart_total): float {
        if ($this->config->get('paypal_fee_fee_type') === 'F') {
            return (float)$this->config->get('paypal_fee_fee');
        }

        $fee = $cart_total * (float)$this->config->get('paypal_fee_fee') / 100;

        $min = (float)$this->config->get('paypal_fee_fee_min');
        $max = (float)$this->config->get('paypal_fee_fee_max');

        if ($min > 0 && $fee < $min) $fee = $min;
        if ($max > 0 && $fee > $max) $fee = $max;

        return $fee;
    }
}
