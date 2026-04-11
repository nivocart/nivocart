<?php
class ModelTotalCoupon extends Model {

    public function getTotal(array $taxes, float $total): array {
        if (!isset($this->session->data['coupon'])) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $this->language->load('total/coupon');

        $this->load->model('checkout/coupon');
        $this->load->model('catalog/product');

        $coupon_info = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);

        if (!$coupon_info) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        [$discount_total, $tax_adjustments] = $this->calculateDiscount($coupon_info, $total);

        return [
            'total_data' => [[
                'code'       => 'coupon',
                'title'      => sprintf($this->language->get('text_coupon'), $this->session->data['coupon']),
                'text'       => $this->currency->format(-$discount_total, $this->config->get('config_currency')),
                'value'      => -$discount_total,
                'sort_order' => $this->config->get('coupon_sort_order')
            ]],
            'total' => -$discount_total,
            'taxes' => $tax_adjustments,
        ];
    }

    private function calculateDiscount(array $coupon_info, float $total): array {
        $coupon_special = $this->config->get('config_coupon_special');
        $discount_total = 0.0;
        $tax_adjustments = [];

        $sub_total = $this->resolveSubTotal($coupon_info);

        if ($coupon_info['type'] === 'F') {
            $coupon_info['discount'] = min((float)$coupon_info['discount'], $sub_total);
        }

        foreach ($this->cart->getProducts() as $product) {
            if (!$this->productIsEligible($product, $coupon_info, $coupon_special)) {
                continue;
            }

            $discount = $this->calculateProductDiscount($product, $coupon_info, $sub_total);

            if ($product['tax_class_id'] && $discount > 0) {
                foreach ($this->tax->getRates($discount, $product['tax_class_id']) as $tax_rate) {
                    if ($tax_rate['type'] === 'P') {
                        $tax_adjustments[$tax_rate['tax_rate_id']] = ($tax_adjustments[$tax_rate['tax_rate_id']] ?? 0) - $tax_rate['amount'];
                    }
                }
            }

            $discount_total += $discount;
        }

        // Shipping discount
        if ($coupon_info['shipping'] && isset($this->session->data['shipping_method'])) {
            $shipping = $this->session->data['shipping_method'];

            if (!empty($shipping['tax_class_id'])) {
                foreach ($this->tax->getRates($shipping['cost'], $shipping['tax_class_id']) as $tax_rate) {
                    if ($tax_rate['type'] === 'P') {
                        $tax_adjustments[$tax_rate['tax_rate_id']] = ($tax_adjustments[$tax_rate['tax_rate_id']] ?? 0) - $tax_rate['amount'];
                    }
                }
            }

            $discount_total += (float)$shipping['cost'];
        }

        // Discount can never exceed the running total
        $discount_total = min($discount_total, $total);

        return [$discount_total, $tax_adjustments];
    }

    private function resolveSubTotal(array $coupon_info): float {
        if (!$coupon_info['product']) {
            return (float)$this->cart->getSubTotal();
        }

        $sub_total = 0.0;

        foreach ($this->cart->getProducts() as $product) {
            if (in_array($product['product_id'], $coupon_info['product'], strict: true)) {
                $sub_total += (float)$product['total'];
            }
        }

        return $sub_total;
    }

	// Function might need rework
    private function productIsEligible(array $product, array $coupon_info, bool $coupon_special): bool {
        if (!$coupon_info['product'] && !in_array($product['product_id'], $coupon_info['product'], strict: true)) {
            return false;
        }

        if (!$coupon_special) {
            $result = $this->model_catalog_product->getProduct($product['product_id']);
            if (!empty($result['special'])) {
                return false;
            }
        }

        return true;
    }

    private function calculateProductDiscount(array $product, array $coupon_info, float $sub_total): float {
        return match($coupon_info['type']) {
            'F' => (float)$coupon_info['discount'] * ((float)$product['total'] / $sub_total),
            'P' => (float)$product['total'] / 100 * (float)$coupon_info['discount'],
            default => 0.0,
        };
    }

    public function confirm(array $order_info, array $order_total): void {
        $code = $this->extractCodeFromTitle($order_total['title']);

        $this->load->model('checkout/coupon');

        $coupon_info = $this->model_checkout_coupon->getCoupon($code, false, false);

        if ($coupon_info) {
            $this->model_checkout_coupon->redeem(
                $coupon_info['coupon_id'],
                $order_info['order_id'],
                $order_info['customer_id'],
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
