<?php
/**
 * Class ModelTotalReward
 *
 * @package NivoCart
 */
class ModelTotalReward extends Model {
	/**
	 * Functions Get, Confirm
	 */
    public function getTotal(array $taxes, float $total): array {
        if (!isset($this->session->data['reward'])) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $this->language->load('total/reward');

        $points_rate = (float)$this->config->get('config_reward_rate') ?: 1.0;
        $customer_points = (float)$this->customer->getRewardPoints();
        $requested = (float)$this->session->data['reward'];

        if ($requested > $customer_points) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $points = $requested;
        $products = $this->cart->getProducts();

        $points_total = array_sum(array_column(array_filter($products, fn($p) => $p['points']), 'points'));

        $sub_total = (float)$this->cart->getSubTotal();
        $max_points = min($points / $points_rate, $points_total);
        $reward_value = ($points && $max_points > $sub_total) ? $sub_total : $max_points;

        $eligible_total = 0.0;

        foreach ($products as $product) {
            if ($product['points']) {
                $eligible_total += (float)$product['total'];
            }
        }

        if ($reward_value <= 0 || $eligible_total <= 0) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $tax_adjustments = [];

        // The reward value is shared between the eligible products in proportion to their totals
        foreach ($products as $product) {
            if (!$product['points']) {
                continue;
            }

            $product_discount = $reward_value * ((float)$product['total'] / $eligible_total);

            if ($product['tax_class_id']) {
                foreach ($this->tax->getRates($product_discount, $product['tax_class_id']) as $tax_rate) {
                    if ($tax_rate['type'] === 'P') {
                        $tax_adjustments[$tax_rate['tax_rate_id']] = ($tax_adjustments[$tax_rate['tax_rate_id']] ?? 0) - $tax_rate['amount'];
                    }
                }
            }
        }

        return [
            'total_data' => [[
                'code'       => 'reward',
                'title'      => sprintf($this->language->get('text_reward'), round($reward_value * $points_rate)),
                'text'       => $this->currency->format(-$reward_value, $this->config->get('config_currency')),
                'value'      => -$reward_value,
                'sort_order' => $this->config->get('reward_sort_order')
            ]],
            'total' => -$reward_value,
            'taxes' => $tax_adjustments,
        ];
    }

    public function confirm(array $order_info, array $order_total): void {
        $this->language->load('total/reward');

        if (!$order_info['customer_id']) {
            return;
        }

        // The order total value is the discount in the default currency, so the points used are value x rate
        $points_rate = (float)$this->config->get('config_reward_rate') ?: 1.0;
        $points = (int)round(abs((float)$order_total['value']) * $points_rate);

        if (!$points) {
            return;
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_reward` SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', points = '" . (int)-$points . "', date_added = NOW()");
    }
}
