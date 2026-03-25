<?php
final class Encryption {
	/**
	 * @var string
	 */
	private string $key = '';

	/**
	 * Constructor
	 *
	 * @param string $key
	 */
	public function __construct(string $key) {
		$this->key = hash('sha256', $key, true);
	}

	/**
	 * encrypt
	 *
	 * @return string
	 */
	public function encrypt(string $value): string {
		$method = 'AES-128-CBC';

		$iv_length = openssl_cipher_iv_length($method); // 16

		$iv = openssl_random_pseudo_bytes($iv_length);

		$encrypted = openssl_encrypt($value, $method, hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv);

		$encoded = base64_encode($encrypted) . '|' . base64_encode($iv);

		return strtr($encoded, '+/=', '-_,');
	}

	/**
	 * decrypt
	 *
	 * @return string
	 */
	public function decrypt(string $value): string {
		$value = explode('|', strtr($value, '-_,', '+/=') . '|');

		$decoded = base64_decode($value[0]);

		$iv = base64_decode($value[1]);

		$method = 'AES-128-CBC';

		$decrypted = trim(openssl_decrypt($decoded, $method, hash('sha256', $this->key, true), OPENSSL_RAW_DATA, $iv));

		return $decrypted;
	}
}
