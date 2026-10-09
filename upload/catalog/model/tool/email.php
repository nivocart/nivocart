<?php
/**
 * Class ModelToolEmail
 *
 * @package NivoCart
 */
class ModelToolEmail extends Model {
	/**
	 * Check that an email address has a valid format and that its domain can receive mail.
	 * On a local server the DNS lookup is skipped (format is still checked).
	 *
	 * @param string $email
	 *
	 * @return bool
	 */
	public function verifyMail($email): bool {
		if (!emailIsValid($email)) {
			return false;
		}

		if ($this->url->isLocal()) {
			return true;
		}

		return emailDomainCanReceive($email);
	}

	/**
	 * Look up an active Mail Manager template by code.
	 * Automatically uses the current store and language.
	 * Prefers a store-specific row over the global (store_id = 0) row.
	 * Returns an empty array if no active template is found (caller falls back to .tpl).
	 *
	 * @param  string $code  e.g. 'customer_register', 'order_confirm'
	 * @return array         Keys: template_id, code, name, subject, body — or [] if not found
	 */
	public function getTemplateByCode(string $code): array {
		$store_id = (int)$this->config->get('config_store_id');
		$language_id = (int)$this->config->get('config_language_id');

		$query = $this->db->query("SELECT et.* FROM `" . DB_PREFIX . "email_template` et INNER JOIN `" . DB_PREFIX . "email_template_store` ets ON (ets.template_id = et.template_id AND ets.store_id IN (0, '" . (int)$store_id . "')) WHERE et.`code` = '" . $this->db->escape($code) . "' AND et.`language_id` = '" . (int)$language_id . "' AND et.`status` = 1 ORDER BY ets.`store_id` DESC LIMIT 1");

		return $query->row;
	}

	/**
	 * Replace placeholder tokens in a subject or body string.
	 * Tokens are simple {key} style — e.g. {firstname}, {order_id}.
	 * Any token not present in $tokens is left as-is.
	 *
	 * @param  string $text    The subject or body string from the DB template
	 * @param  array  $tokens  Associative array: ['{firstname}' => 'John', ...]
	 * @return string
	 */
	public function replaceTokens(string $text, array $tokens): string {
		return str_replace(array_keys($tokens), array_values($tokens), $text);
	}
}
