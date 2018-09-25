<?php
final class Encryption {
	private $key;

	public function __construct($key) {
		$this->key = hash('sha256', $key, true);
	}

	public function encrypt($value) {
		$method = 'AES-128-CBC';

		$iv_length = openssl_cipher_iv_length($method); // 16

		$iv = openssl_random_pseudo_bytes($iv_length);

		$encrypted = openssl_encrypt($value, $method, hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv);

		$encoded = base64_encode($encrypted) . '|' . base64_encode($iv);

		return strtr($encoded, '+/=', '-_,');
	}

	public function decrypt($value) {
		$value = explode('|', strtr($value, '-_,', '+/=') . '|');

		$decoded = base64_decode($value[0]);

		$iv = base64_decode($value[1]);

		$method = 'AES-128-CBC';

		$decrypted = trim(openssl_decrypt($decoded, $method, hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv));

		return $decrypted;
	}
}
