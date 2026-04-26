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

		$points_rate = (float)$this->config->get('config_reward_rate');
		$customer_points = (float)$this->customer->getRewardPoints();
		$requested = (float)$this->session->data['reward'];

		if ($requested > $customer_points) {
			return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
		}

		$points = $requested;

		$products = $this->cart->getProducts();

		$points_total = array_sum(array_column(array_filter($products, fn ($p) => $p['points']), 'points'));

		$sub_total = (float)$this->cart->getSubTotal();
		$max_points = min($points / $points_rate, $points_total);
		$reward_value = ($points && $max_points > $sub_total) ? $sub_total : $max_points;

		$discount_total = 0.0;
		$tax_adjustments = [];

		foreach ($products as $product) {
			if (!$product['points']) {
				continue;
			}

			if ($product['tax_class_id']) {
				foreach ($this->tax->getRates($reward_value, $product['tax_class_id']) as $tax_rate) {
					if ($tax_rate['type'] === 'P') {
						$tax_adjustments[$tax_rate['tax_rate_id']] = ($tax_adjustments[$tax_rate['tax_rate_id']] ?? 0) - $tax_rate['amount'];
					}
				}
			}

			$discount_total += $reward_value;
		}

		return [
			'total_data' => [[
				'code'       => 'reward',
				'title'      => sprintf($this->language->get('text_reward'), $reward_value * $points_rate),
				'text'       => $this->currency->format(-$discount_total, $this->config->get('config_currency')),
				'value'      => -$discount_total,
				'sort_order' => $this->config->get('reward_sort_order')
			]],
			'total' => -$discount_total,
			'taxes' => $tax_adjustments,
		];
	}

	public function confirm(array $order_info, array $order_total): void {
		$this->language->load('total/reward');

		$points = $this->extractPointsFromTitle($order_total['title']);

		if (!$points) {
			return;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_reward` SET customer_id = '" . (int)$order_info['customer_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', points = '" . (float)-$points . "', date_added = NOW()");
	}

	private function extractPointsFromTitle(string $title): float {
		$start = strpos($title, '(');
		$end = strrpos($title, ')');

		if ($start !== false && $end !== false && $end > $start) {
			return (float)substr($title, $start + 1, $end - $start - 1);
		}

		return 0.0;
	}
}
