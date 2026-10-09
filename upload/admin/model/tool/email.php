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
	public function verifyMail(string $email): bool {
		if (!emailIsValid($email)) {
			return false;
		}

		if ($this->url->isLocal()) {
			return true;
		}

		return emailDomainCanReceive($email);
	}
}
