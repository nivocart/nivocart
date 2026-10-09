<?php
/**
 * Email helper functions shared by Catalog and Admin.
 *
 * emailIsValid()           - format check only (no network access)
 * emailDomain()            - returns the ASCII (punycode) domain part
 * emailDomainCanReceive()  - DNS check: can the domain receive mail (RFC 5321)
 *
 * @package NivoCart
 */

/**
 * Check the format of an email address.
 *
 * @param mixed $email Address to check
 * @param int   $max_length Maximum allowed length (database field is 96)
 *
 * @return bool
 */
function emailIsValid($email, int $max_length = 96): bool {
	if (!is_string($email)) {
		return false;
	}

	if ($email === '' || mb_strlen($email, 'UTF-8') > $max_length) {
		return false;
	}

	// Convert an internationalised domain to punycode so FILTER_VALIDATE_EMAIL can check it
	$pos = strrpos($email, '@');

	if ($pos === false) {
		return false;
	}

	$local = substr($email, 0, $pos);
	$domain = substr($email, $pos + 1);

	if (!preg_match('//u', $domain)) {
		return false;
	}

	if (function_exists('idn_to_ascii') && preg_match('/[^\x20-\x7e]/', $domain)) {
		$ascii = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

		if ($ascii === false) {
			return false;
		}

		$domain = $ascii;
	}

	return (bool)filter_var($local . '@' . $domain, FILTER_VALIDATE_EMAIL);
}

/**
 * Get the ASCII (punycode) domain of an email address.
 *
 * @param string $email
 *
 * @return string Empty string if there is no domain
 */
function emailDomain(string $email): string {
	$pos = strrpos(trim($email), '@');

	if ($pos === false) {
		return '';
	}

	$domain = strtolower(substr(trim($email), $pos + 1));

	if (function_exists('idn_to_ascii') && preg_match('/[^\x20-\x7e]/', $domain)) {
		$ascii = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

		if ($ascii === false) {
			return '';
		}

		$domain = $ascii;
	}

	return rtrim($domain, '.');
}

/**
 * Check whether the domain of an email address can receive mail.
 *
 * Follows RFC 5321 section 5.1: use MX records, and if there are none
 * fall back to the domain's A / AAAA record (implicit MX). A "null MX"
 * (RFC 7505, a single MX with target ".") means the domain accepts no mail.
 *
 * @param string $email
 *
 * @return bool
 */
function emailDomainCanReceive(string $email): bool {
	$domain = emailDomain($email);

	if ($domain === '') {
		return false;
	}

	$hosts = [];
	$weights = [];

	if (@getmxrr($domain, $hosts, $weights) && $hosts) {
		foreach ($hosts as $host) {
			if (trim($host, '.') !== '') {
				return true;
			}
		}

		// Only a null MX was published
		return false;
	}

	return @checkdnsrr($domain . '.', 'A') || @checkdnsrr($domain . '.', 'AAAA');
}
