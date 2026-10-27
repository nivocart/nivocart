<?php
/**
 * Class ModelToolApiKeyManager
 *
 * Every Key Code is encrypted by the Vault library before it reaches the database,
 * so queries, exceptions and backups only ever contain ciphertext. The vault must be
 * registered as $this->vault by the controller before these methods are used.
 *
 * @package NivoCart
 */
class ModelToolApiKeyManager extends Model {
	/**
	 * Functions Add, Edit, Delete, Get, Migrate, Log
	 */
	public function addApiKey(array $data = []): int {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "api_key` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `code` = '', `status` = '" . (int)$data['status'] . "'");

		$api_key_id = $this->db->getLastId();

		try {
			$code = $this->vault->encrypt((string)$data['code'], $api_key_id);

			$this->db->query("UPDATE `" . DB_PREFIX . "api_key` SET `code` = '" . $this->db->escape($code) . "' WHERE `api_key_id` = '" . (int)$api_key_id . "'");

			$this->verifyStoredCode($api_key_id, $code);
		} catch (\Throwable $e) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "api_key` WHERE `api_key_id` = '" . (int)$api_key_id . "'");

			throw $e;
		}

		return $api_key_id;
	}

	public function editApiKey(int $api_key_id, array $data = []): bool {
		if (!$this->getApiKey($api_key_id)) {
			return false;
		}

		$previous = $this->getApiKey($api_key_id);

		$code = $this->vault->encrypt((string)$data['code'], $api_key_id);

		$this->db->query("UPDATE `" . DB_PREFIX . "api_key` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `code` = '" . $this->db->escape($code) . "', `status` = '" . (int)$data['status'] . "' WHERE `api_key_id` = '" . (int)$api_key_id . "'");

		try {
			$this->verifyStoredCode($api_key_id, $code);
		} catch (\Throwable $e) {
			$this->db->query("UPDATE `" . DB_PREFIX . "api_key` SET `name` = '" . $this->db->escape((string)$previous['name']) . "', `code` = '" . $this->db->escape((string)$previous['code']) . "', `status` = '" . (int)$previous['status'] . "' WHERE `api_key_id` = '" . (int)$api_key_id . "'");

			throw $e;
		}

		return true;
	}

	public function deleteApiKey(int $api_key_id): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "api_key` WHERE `api_key_id` = '" . (int)$api_key_id . "'");
	}

	/**
	 * Get a row as stored (the code is still encrypted)
	 */
	public function getApiKey(int $api_key_id): array {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api_key` WHERE `api_key_id` = '" . (int)$api_key_id . "'");

		return $query->row;
	}

	/**
	 * Get a row with the code decrypted (the vault must be unlocked)
	 */
	public function getApiKeyDecrypted(int $api_key_id): array {
		$result = $this->getApiKey($api_key_id);

		if ($result) {
			$result['code'] = $this->decryptCode($result);
		}

		return $result;
	}

	/**
	 * Get the decrypted code of one row, or null if the row does not exist
	 */
	public function revealApiKey(int $api_key_id): ?string {
		$result = $this->getApiKey($api_key_id);

		if (!$result) {
			return null;
		}

		return $this->decryptCode($result);
	}

	public function getApiKeys(array $data = []): array {
		$sql = "SELECT `api_key_id`, `name`, `code`, `status` FROM `" . DB_PREFIX . "api_key`";

		$sort_data = [
			'api_key_id',
			'name'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data, true)) {
			$sql .= " ORDER BY `" . $data['sort'] . "`";
		} else {
			$sql .= " ORDER BY `name`";
		}

		if (isset($data['order']) && ($data['order'] === 'DESC')) {
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

	public function getTotalApiKeys(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "api_key`");

		return (int)$query->row['total'];
	}

	/**
	 * Count rows whose code is not encrypted yet (new install, restored old backup)
	 */
	public function getTotalLegacyApiKeys(): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "api_key` WHERE `code` NOT LIKE '" . $this->db->escape(Vault::PREFIX) . "%'");

		return (int)$query->row['total'];
	}

	/**
	 * Encrypt every row that still holds a plaintext code (the vault must be unlocked).
	 *
	 * The old tool stored values HTML-encoded (Request::clean), so name and code are decoded
	 * first. Each value is decrypted again and compared before it is saved.
	 *
	 * @return int Number of rows migrated
	 */
	public function migrateLegacyApiKeys(): int {
		$query = $this->db->query("SELECT `api_key_id`, `name`, `code` FROM `" . DB_PREFIX . "api_key` WHERE `code` NOT LIKE '" . $this->db->escape(Vault::PREFIX) . "%'");

		$migrated = 0;

		foreach ($query->rows as $result) {
			$api_key_id = (int)$result['api_key_id'];

			$name = html_entity_decode((string)$result['name'], ENT_QUOTES, 'UTF-8');
			$plain = html_entity_decode((string)$result['code'], ENT_QUOTES, 'UTF-8');

			$code = $this->vault->encrypt($plain, $api_key_id);

			if (!hash_equals($plain, $this->vault->decrypt($code, $api_key_id))) {
				throw new \RuntimeException('Error: Migration check failed for key ' . $api_key_id . '!');
			}

			$this->db->query("UPDATE `" . DB_PREFIX . "api_key` SET `name` = '" . $this->db->escape($name) . "', `code` = '" . $this->db->escape($code) . "' WHERE `api_key_id` = '" . $api_key_id . "'");

			try {
				$this->verifyStoredCode($api_key_id, $code);
			} catch (\Throwable $e) {
				$this->db->query("UPDATE `" . DB_PREFIX . "api_key` SET `name` = '" . $this->db->escape((string)$result['name']) . "', `code` = '" . $this->db->escape((string)$result['code']) . "' WHERE `api_key_id` = '" . $api_key_id . "'");

				throw $e;
			}

			$migrated++;
		}

		return $migrated;
	}

	/**
	 * Record a vault event in the Admin User Log (never contains keys or passphrases)
	 */
	public function logAction(string $action, bool $allowed = true): void {
		$url = (string)preg_replace('/(token=)[^&]*/', '$1', (string)($this->request->server['REQUEST_URI'] ?? ''));
		$ip = (string)($this->request->server['REMOTE_ADDR'] ?? '');

		$this->db->query("INSERT INTO `" . DB_PREFIX . "user_log` SET `user_id` = '" . (int)$this->user->getId() . "', `username` = '" . $this->db->escape(mb_substr((string)$this->user->getUserName(), 0, 20, 'UTF-8')) . "', `action` = '" . $this->db->escape(mb_substr($action, 0, 50, 'UTF-8')) . "', `allowed` = '" . (int)$allowed . "', `url` = '" . $this->db->escape(mb_substr($url, 0, 200, 'UTF-8')) . "', `ip` = '" . $this->db->escape(mb_substr($ip, 0, 40, 'UTF-8')) . "', `date` = NOW()");
	}

	/**
	 * Read the row back and confirm the database kept the whole ciphertext (a column that is
	 * too small would cut it short without an error)
	 */
	private function verifyStoredCode(int $api_key_id, string $code): void {
		$stored = $this->getApiKey($api_key_id);

		if (!$stored || !hash_equals($code, (string)$stored['code'])) {
			throw new \RuntimeException('Error: The stored value for key ' . $api_key_id . ' did not match what was saved!');
		}
	}

	/**
	 * Decrypt the code of a stored row. A legacy plaintext row is returned decoded.
	 */
	private function decryptCode(array $result): string {
		if ($this->vault->isEncrypted((string)$result['code'])) {
			return $this->vault->decrypt((string)$result['code'], (int)$result['api_key_id']);
		}

		return html_entity_decode((string)$result['code'], ENT_QUOTES, 'UTF-8');
	}
}
