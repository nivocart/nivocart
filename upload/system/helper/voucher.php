<?php
/**
 * Gift Voucher helper functions shared by Catalog and Admin.
 *
 * voucherCodeGenerate()   - secure random code, easy to type on a phone (8 characters)
 * voucherCodeNormalise()  - upper case, spaces and hyphens removed (for lookups)
 * voucherCodeFormat()     - display format XXXX-XXXX
 * voucherDesigns()        - list of valid occasion keys
 * voucherDesignIsValid()  - check an occasion key
 * voucherEmailHtml()      - render the voucher email template file
 *
 * @package NivoCart
 */

/**
 * Generate a random voucher code.
 *
 * The alphabet has no 0, O, 1, I or L so the code can not be misread or mistyped.
 * 31 characters over 8 positions gives about 8.5 x 10^11 combinations.
 *
 * @param int $length Number of characters (default 8)
 *
 * @return string
 */
function voucherCodeGenerate(int $length = 8): string {
	$alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

	$max = strlen($alphabet) - 1;

	$code = '';

	for ($i = 0; $i < $length; $i++) {
		$code .= $alphabet[random_int(0, $max)];
	}

	return $code;
}

/**
 * Normalise a code typed by a customer: upper case, no spaces and no hyphens.
 *
 * @param mixed $code
 *
 * @return string
 */
function voucherCodeNormalise($code): string {
	if (!is_string($code)) {
		return '';
	}

	return strtoupper(str_replace([' ', '-'], '', trim($code)));
}

/**
 * Format a code for display, XXXX-XXXX for generated codes, unchanged otherwise.
 *
 * @param string $code
 *
 * @return string
 */
function voucherCodeFormat(string $code): string {
	$normalised = voucherCodeNormalise($code);

	if (preg_match('/^[A-Z0-9]{8}$/', $normalised)) {
		return substr($normalised, 0, 4) . '-' . substr($normalised, 4);
	}

	return $code;
}

/**
 * Valid occasion keys. The display names come from the language files (text_design_<key>).
 *
 * @return array<int, string>
 */
function voucherDesigns(): array {
	return ['general', 'birthday', 'christmas', 'thank_you', 'congratulations'];
}

/**
 * Check an occasion key.
 *
 * @param mixed $design
 *
 * @return bool
 */
function voucherDesignIsValid($design): bool {
	return is_string($design) && in_array($design, voucherDesigns(), true);
}

/**
 * Render a voucher email template file with the given data.
 *
 * Used by Catalog and Admin so both send exactly the same email.
 *
 * @param string               $file Full path of the template file
 * @param array<string, mixed> $data Template variables
 *
 * @return string
 */
function voucherEmailHtml(string $file, array $data): string {
	if (!is_file($file)) {
		throw new \RuntimeException('Error: Could not load template ' . $file . '!');
	}

	extract($data);

	ob_start();
	include($file);

	return (string)ob_get_clean();
}
