<?php
class ModelTotalTax extends Model {

	public function getTotal(&$total_data, &$total, &$taxes) {
		foreach ($taxes as $key => $value) {
			if ($value > 0) {
				$total_data[] = array(
					'code'       => 'tax',
					'title'      => $this->tax->getRateName($key),
					'text'       => $this->currency->format($value, $this->config->get('config_currency')),
					'value'      => round($value, 2, PHP_ROUND_HALF_UP),
					'sort_order' => $this->config->get('tax_sort_order')
				);

				$total += $value;
			}
		}
	}
}
