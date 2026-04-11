<?php
class ModelTotalTax extends Model {

    public function getTotal(array $taxes, float $total): array {
        $total_data = [];
        $total_added = 0.0;

        foreach ($taxes as $key => $value) {
            $value = (float)$value;

			// Check if rate name is currently set. Default to "Tax" otherwise
			$tax_name = $this->tax->getRateName($key) ? $this->tax->getRateName($key) : 'Tax';

            if ($value > 0) {
                $total_data[] = [
                    'code'       => 'tax',
                    'title'      => $tax_name,
                    'text'       => $this->currency->format($value, $this->config->get('config_currency')),
                    'value'      => round($value, 2, PHP_ROUND_HALF_UP),
                    'sort_order' => $this->config->get('tax_sort_order')
                ];

                $total_added += $value;
            }
        }

        return [
            'total_data' => $total_data,
            'total'      => $total_added,
            'taxes'      => [],
        ];
    }
}
