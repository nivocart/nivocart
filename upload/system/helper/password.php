<?php
/**
 * Helper Password
 *
 * Password hashing for users, customers and affiliates.
 *
 * New passwords are stored with password_hash() (bcrypt, PASSWORD_DEFAULT).
 * Older accounts may still hold a salted SHA1 hash (40 chars, with a salt) or a
 * plain MD5 hash (32 chars). passwordVerify() accepts all three formats and
 * passwordNeedsRehash() tells the login code when to upgrade the stored hash.
 *
 * @package NivoCart
 */

/**
 * Prepare a password for bcrypt.
 *
 * bcrypt only uses the first 72 bytes and rejects NUL bytes, so a password
 * longer than 72 bytes (or with a NUL byte) is reduced to a fixed-length
 * digest first. Normal passwords are left untouched.
 */
function passwordPrepare(string $password): string {
	if (strlen($password) > 72 || str_contains($password, "\0")) {
		return base64_encode(hash('sha256', $password, true));
	}

	return $password;
}

/**
 * Create a hash for storage.
 */
function passwordHash(string $password): string {
	return password_hash(passwordPrepare($password), PASSWORD_DEFAULT);
}

/**
 * Check a password against a stored hash (bcrypt, legacy salted SHA1 or legacy MD5).
 */
function passwordVerify(string $password, string $hash, string $salt = ''): bool {
	if ($hash === '') {
		return false;
	}

	if ($hash[0] === '$') {
		return password_verify(passwordPrepare($password), $hash);
	}

	if (strlen($hash) === 40 && $salt !== '') {
		return hash_equals(strtolower($hash), sha1($salt . sha1($salt . sha1($password))));
	}

	if (strlen($hash) === 32) {
		return hash_equals(strtolower($hash), md5($password));
	}

	return false;
}

/**
 * True when the stored hash is a legacy one or uses an outdated algorithm or cost.
 */
function passwordNeedsRehash(string $hash): bool {
	return ($hash === '' || $hash[0] !== '$' || password_needs_rehash($hash, PASSWORD_DEFAULT));
}

/**
 * True when a value looks like a stored hash (bcrypt/argon2, salted SHA1 or MD5).
 */
function passwordIsHash(string $value): bool {
	return (bool)preg_match('/^(\$2[aby]\$|\$argon2)|^[a-f0-9]{32}$|^[a-f0-9]{40}$/i', $value);
}
