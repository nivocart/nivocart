<?php
/**
 * Class ModelToolMailManager
 *
 * @package NivoCart
 */
class ModelToolMailManager extends Model {
	/**
	 * Add / Edit / Delete / Get
	 */
	public function addEmailTemplate(array $data = []): void {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "email_template` SET
			`language_id` = '" . (int)$data['language_id'] . "',
			`type` = '" . $this->db->escape($data['type']) . "',
			`code` = '" . $this->db->escape($data['code']) . "',
			`name` = '" . $this->db->escape($data['name']) . "',
			`subject` = '" . $this->db->escape($data['subject']) . "',
			`body` = '" . $this->db->escape($data['body']) . "',
			`status` = '" . (int)$data['status'] . "',
			`sort_order` = '" . (int)$data['sort_order'] . "',
			`date_added` = NOW(),
			`date_modified` = NOW()"
		);

		$template_id = $this->db->getLastId();

		// Save and Continue
		$this->session->data['new_template_id'] = $template_id;

		// Save store rows
		$this->saveEmailTemplateStores($template_id, $data['mail_store'] ?? [0]);
	}

	public function editEmailTemplate(int $template_id, array $data = []): void {
		$this->db->query("UPDATE `" . DB_PREFIX . "email_template` SET
			`language_id` = '" . (int)$data['language_id'] . "',
			`type` = '" . $this->db->escape($data['type']) . "',
			`code` = '" . $this->db->escape($data['code']) . "',
			`name` = '" . $this->db->escape($data['name']) . "',
			`subject` = '" . $this->db->escape($data['subject']) . "',
			`body` = '" . $this->db->escape($data['body']) . "',
			`status` = '" . (int)$data['status'] . "',
			`sort_order` = '" . (int)$data['sort_order'] . "',
			`date_modified` = NOW()
			WHERE `template_id` = '" . (int)$template_id . "'"
		);

		// Replace store rows
		$this->saveEmailTemplateStores($template_id, $data['mail_store'] ?? [0]);
	}

	public function deleteEmailTemplate(int $template_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "email_template` WHERE `template_id` = '" . (int)$template_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "email_template_store` WHERE `template_id` = '" . (int)$template_id . "'");
	}

	public function getEmailTemplate(int $template_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "email_template` WHERE `template_id` = '" . (int)$template_id . "'");

		return $query->row;
	}

	// ----------------------------------------------------------------
	// Store — returns array of store_ids for a template
	// ----------------------------------------------------------------

	public function getEmailTemplateStores(int $template_id): array {
		$query = $this->db->query("SELECT `store_id` FROM `" . DB_PREFIX . "email_template_store` WHERE `template_id` = '" . (int)$template_id . "'");

		return array_column($query->rows, 'store_id');
	}

	private function saveEmailTemplateStores(int $template_id, array $store_ids): void {
		// Delete existing pivot rows then re-insert
		$this->db->query("DELETE FROM `" . DB_PREFIX . "email_template_store` WHERE `template_id` = '" . (int)$template_id . "'");

		// Always ensure at least store 0 (default) is saved
		if (empty($store_ids)) {
			$store_ids = [0];
		}

		foreach ($store_ids as $store_id) {
			$this->db->query("INSERT IGNORE INTO `" . DB_PREFIX . "email_template_store` SET `template_id` = '" . (int)$template_id . "', `store_id`    = '" . (int)$store_id . "'");
		}
	}

	// ----------------------------------------------------------------
	// Lookup by code — used by Newsletter AJAX and future dispatch.
	// Matches templates assigned to this store or to store 0 (default).
	// Only returns active (status = 1) templates.
	// ----------------------------------------------------------------

	public function getEmailTemplateByCode(string $code, int $store_id = 0, int $language_id = 1): array {
		$query = $this->db->query("SELECT et.* FROM `" . DB_PREFIX . "email_template` et INNER JOIN `" . DB_PREFIX . "email_template_store` ets ON (ets.template_id = et.template_id AND ets.store_id IN (0, '" . (int)$store_id . "')) WHERE et.`code` = '" . $this->db->escape($code) . "' AND et.`language_id` = '" . (int)$language_id . "' AND et.`status` = 1 ORDER BY ets.`store_id` DESC LIMIT 1");

		return $query->row;
	}

	public function getEmailTemplates(array $data = []): array {
		$sql = "SELECT DISTINCT et.* FROM `" . DB_PREFIX . "email_template` et LEFT JOIN `" . DB_PREFIX . "email_template_store` ets ON (ets.template_id = et.template_id) WHERE 1";

		if (!empty($data['filter_name'])) {
			$sql .= " AND et.`name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_code'])) {
			$sql .= " AND et.`code` LIKE '%" . $this->db->escape($data['filter_code']) . "%'";
		}

		if (!empty($data['filter_type'])) {
			$sql .= " AND et.`type` = '" . $this->db->escape($data['filter_type']) . "'";
		}

		if (!empty($data['filter_language_id'])) {
			$sql .= " AND et.`language_id` = '" . (int)$data['filter_language_id'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND et.`status` = '" . (int)$data['filter_status'] . "'";
		}

		$sort_data = [
			'template_id',
			'name',
			'code',
			'type',
			'sort_order',
			'date_modified'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY et.`" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY et.`type`, et.`sort_order`, et.`name`";
		}

		if (isset($data['order']) && $data['order'] === 'DESC') {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

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

	public function getTotalEmailTemplates(array $data = []): int {
		$sql = "SELECT COUNT(DISTINCT et.template_id) AS `total` FROM `" . DB_PREFIX . "email_template` et LEFT JOIN `" . DB_PREFIX . "email_template_store` ets ON (ets.template_id = et.template_id) WHERE 1";

		if (!empty($data['filter_name'])) {
			$sql .= " AND et.`name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_code'])) {
			$sql .= " AND et.`code` LIKE '%" . $this->db->escape($data['filter_code']) . "%'";
		}

		if (!empty($data['filter_type'])) {
			$sql .= " AND et.`type` = '" . $this->db->escape($data['filter_type']) . "'";
		}

		if (!empty($data['filter_language_id'])) {
			$sql .= " AND et.`language_id` = '" . (int)$data['filter_language_id'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND et.`status` = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}
}
