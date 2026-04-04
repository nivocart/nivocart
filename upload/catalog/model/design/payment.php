<?php
class ModelDesignPayment extends Model {

	public function getPaymentImages(array $data = []): array {
		$payment_image_data = array();

		$payment_images_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "payment_image` ORDER BY payment");

		foreach ($payment_images_query->rows as $payment_image) {
			$payment_image_data[] = array(
				'payment' => $payment_image['payment'],
				'image'   => $payment_image['image'],
				'status'  => $payment_image['status']
			);
		}

		return $payment_image_data;
	}

	public function getMethodImage(string $code) {
		$image_query = $this->db->query("SELECT DISTINCT `image` FROM `" . DB_PREFIX . "payment_image` WHERE payment = '" . mb_strtolower($code, 'UTF-8') . "' AND status = '1'");

		return $image_query->row;
	}
}
