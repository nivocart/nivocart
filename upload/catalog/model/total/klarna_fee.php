<?php
/**
 * Class ModelTotalKlarnaFee
 *
 * @package NivoCart
 */
class ModelTotalKlarnaFee extends Model {
	/**
	 * Functions Get, Resolve
	 */
	public function getTotal(array $taxes, float $total): array {
		$this->language->load('total/klarna_fee');

		$address = $this->resolveAddress();
		$klarna_fee = $this->config->get('klarna_fee');

		if (!$this->isFeeApplicable($address, $klarna_fee)) {
			return ['total_data' => [], 'total' => 0.0, 'taxes' => []];
		}

		$iso = $address['iso_code_3'];
		$fee = (float)$klarna_fee[$iso]['fee'];
		$new_taxes = [];

		foreach ($this->tax->getRates($fee, $klarna_fee[$iso]['tax_class_id']) as $tax_rate) {
			$new_taxes[$tax_rate['tax_rate_id']] = ($new_taxes[$tax_rate['tax_rate_id']] ?? 0) + $tax_rate['amount'];
		}

		return [
			'total_data' => [[
				'code'       => 'klarna_fee',
				'title'      => $this->language->get('text_klarna_fee'),
				'text'       => $this->currency->format($fee, $this->config->get('config_currency')),
				'value'      => $fee,
				'sort_order' => $klarna_fee[$iso]['sort_order']
			]],
			'total' => $fee,
			'taxes' => $new_taxes,
		];
	}

	private function resolveAddress(): ?array {
		if (isset($this->session->data['payment_address_id'])) {
			$this->load->model('account/address');

			return $this->model_account_address->getAddress(
				$this->session->data['payment_address_id']
			);
		}

		return $this->session->data['guest']['payment'] ?? null;
	}

	private function isFeeApplicable(?array $address, mixed $klarna_fee): bool {
		if ($address === null) {
			return false;
		}

		$iso = $address['iso_code_3'] ?? null;
		$payment_code = $this->session->data['payment_method']['code'] ?? null;
		$sub_total = (float)$this->cart->getSubTotal();

		return $iso !== null
			&& $payment_code === 'klarna_invoice'
			&& isset($klarna_fee[$iso])
			&& (bool)$klarna_fee[$iso]['status']
			&& $sub_total < (float)$klarna_fee[$iso]['total'];
	}
}
