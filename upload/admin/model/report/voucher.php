<?php
/**
 * Class ModelReportVoucher
 *
 * @package NivoCart
 */
class ModelReportVoucher extends Model {
	/**
	 * Functions Get
	 */
	public function getVouchers(array $data = []): array {
		$sql = "SELECT v.voucher_id, v.order_id, v.code, v.to_name, v.amount, v.date_added, v.date_sent, v.date_expire, (v.amount + COALESCE((SELECT SUM(vh.amount) FROM `" . DB_PREFIX . "voucher_history` vh WHERE vh.voucher_id = v.voucher_id), 0)) AS `balance`, (SELECT MAX(vh2.date_added) FROM `" . DB_PREFIX . "voucher_history` vh2 WHERE vh2.voucher_id = v.voucher_id AND vh2.amount < 0) AS `date_redeemed`, " . $this->getStateSql() . " AS `state` FROM `" . DB_PREFIX . "voucher` v";

		$sql .= $this->getFilterSql($data);

		$sql .= " ORDER BY v.date_added DESC, v.voucher_id DESC";

		if (isset($data['start']) && isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalVouchers(array $data = []): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "voucher` v" . $this->getFilterSql($data));

		return (int)$query->row['total'];
	}

	private function getStateSql(): string {
		return "CASE
			WHEN v.status = '0' THEN 'disabled'
			WHEN (v.order_id > 0 AND NOT EXISTS (SELECT 1 FROM `" . DB_PREFIX . "order` o WHERE o.order_id = v.order_id AND o.order_status_id = '" . (int)$this->config->get('config_complete_status_id') . "')) THEN 'pending'
			WHEN (v.amount + COALESCE((SELECT SUM(vh3.amount) FROM `" . DB_PREFIX . "voucher_history` vh3 WHERE vh3.voucher_id = v.voucher_id), 0)) <= 0 THEN 'used'
			WHEN (v.date_expire IS NOT NULL AND v.date_expire <= NOW()) THEN 'expired'
			ELSE 'active'
		END";
	}

	private function getFilterSql(array $data): string {
		$implode = [];

		if (!empty($data['filter_date_start'])) {
			$implode[] = "DATE(v.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end'])) {
			$implode[] = "DATE(v.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		if (!empty($data['filter_state']) && in_array($data['filter_state'], ['active', 'used', 'expired', 'pending', 'disabled'], true)) {
			$implode[] = $this->getStateSql() . " = '" . $this->db->escape($data['filter_state']) . "'";
		}

		return $implode ? " WHERE " . implode(" AND ", $implode) : "";
	}
}
