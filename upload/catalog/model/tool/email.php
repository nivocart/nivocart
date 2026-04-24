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
	public function verifyMail($email): bool {
		$valid = false;

		if ($this->url->isLocal()) {
			$valid = true;
		} else {
			if ($email && filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
				$domain = substr(strrchr($email, '@'), 1);

				if (checkdnsrr($domain, 'MX')) {
					$valid = true;
				}
			}
		}

		return $valid;
	}
}
