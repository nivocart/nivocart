<?php
/**
 * Class ModelToolEmail
 *
 * @package NivoCart
 */
class ModelToolEmail extends Model {
	/**
	 * Check if email string is valid
	 *
	 * @return bool
	 */
	public function verifyMail(string $email): bool {
		if ($this->url->isLocal()) {
			return true;
		}

		$email = trim($email);

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return false;
		}

		$domain = substr(strrchr($email, '@'), 1);

		return checkdnsrr($domain, 'MX');
	}
}
