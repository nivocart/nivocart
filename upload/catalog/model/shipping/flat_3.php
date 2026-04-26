<?php
/**
 * Class ModelShippingFlat3
 *
 * @package NivoCart
 */
class ModelShippingFlat3 extends Model {
	/**
	 * Functions Get
	 */
	public function getQuote($address) {
		$this->language->load('shipping/flat');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE geo_zone_id = '" . (int)$this->config->get('flat_3_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('flat_3_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$quote_data = [];

			$quote_data['flat_3'] = [
				'code'         => 'flat_3.flat_3',
				'title'        => $this->language->get('text_description'),
				'cost'         => $this->config->get('flat_3_cost'),
				'tax_class_id' => $this->config->get('flat_3_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($this->config->get('flat_3_cost'), $this->config->get('flat_3_tax_class_id'), $this->config->get('config_tax')), $this->config->get('config_currency'))
			];

			$method_data = [
				'code'       => 'flat_3',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('flat_3_sort_order'),
				'error'      => false
			];
		}

		return $method_data;
	}
}
