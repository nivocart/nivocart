<?php
/**
 * Class ModelPaymentSagepay
 *
 * NOTE: Class name changed from ModelPaymentSagePay to ModelPaymentSagepay
 * to match the dynamic property naming pattern used by checkout.php:
 *
 *     $this->{'model_payment_' . $result['code']}
 *
 * For code 'sagepay' this resolves to property 'model_payment_sagepay',
 * which the framework's class-name builder maps to 'ModelPaymentSagepay'.
 *
 * @package NivoCart
 */
class ModelPaymentSagepay extends Model {
	/** Error array Placeholder */

	public function getMethod($address, $total) {
		$this->language->load('payment/sagepay');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('sagepay_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('sagepay_total') > 0 && $this->config->get('sagepay_total') > $total) {
			$status = false;
		} elseif ($this->config->has('sagepay_total_max') && $this->config->get('sagepay_total_max') > 0 && $total > $this->config->get('sagepay_total_max')) {
			$status = false;
		} elseif (!$this->config->get('sagepay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'sagepay',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('sagepay_sort_order')
			];
		}

		return $method_data;
	}
}
