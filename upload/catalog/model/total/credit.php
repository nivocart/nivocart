<?php
/**
 * Class ModelTotalCredit
 *
 * @package NivoCart
 */
class ModelTotalCredit extends Model {
	/**
	 * Functions Get, Confirm
	 */
    public function getTotal(array $taxes, float $total): array {
        $current_credit = abs((float)($this->session->data['current_credit'] ?? 0));

        if (!$this->config->get('credit_status') && !$current_credit) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $this->language->load('total/credit');

        $balance = $current_credit + (float)$this->customer->getBalance();

        if (!$balance) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        $credit = min($balance, $total);

        if ($credit <= 0) {
            return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
        }

        return [
            'total_data' => [[
                'code'       => 'credit',
                'title'      => $this->language->get('text_credit'),
                'text'       => $this->currency->format(-$credit, $this->config->get('config_currency')),
                'value'      => -$credit,
                'sort_order' => $this->config->get('credit_sort_order')
            ]],
            'total' => -$credit,
            'taxes' => [],
        ];
    }

    public function confirm(array $order_info, array $order_total): void {
        $this->language->load('total/credit');

        if (!$order_info['customer_id']) {
            return;
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_transaction` SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', amount = '" . (float)$order_total['value'] . "', date_added  = NOW()");
    }
}
