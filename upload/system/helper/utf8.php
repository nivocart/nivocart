<?php
/**
 * @function mbstring helper
 */
mb_internal_encoding('UTF-8');

/**
 * @param string $string
 *
 * @return int
 */
function utf8_strlen(string $string): int {
	return mb_strlen($string);
}

/**
 * @param string $string
 * @param string $needle
 * @param int    $offset
 *
 * @return false|int
 */
function utf8_strpos(string $string, string $needle, int $offset = 0) {
	return mb_strpos($string, $needle, $offset);
}

/**
 * @param string $string
 * @param string $needle
 * @param int    $offset
 *
 * @return false|int
 */
function utf8_strrpos(string $string, string $needle, int $offset = 0) {
	return mb_strrpos($string, $needle, $offset);
}

/**
 * @param string $string
 * @param int 	 $offset
 * @param ?int   $length
 *
 * @return string
 */
function utf8_substr(string $string, int $offset, ?int $length = null): string {
	if ($length === null) {
		return mb_substr($string, $offset, mb_strlen($string));
	} else {
		return mb_substr($string, $offset, $length);
	}
}

/**
 * @param string $string
 *
 * @return string
 */
function utf8_strtoupper(string $string): string {
	return mb_strtoupper($string);
}

/**
 * @param string $string
 *
 * @return string
 */
function utf8_strtolower(string $string): string {
	return mb_strtolower($string);
}
