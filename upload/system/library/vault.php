<?php
/**
 * Library Class Vault
 *
 * Passphrase-protected encryption for the API Key Manager.
 *
 * - A random 32-byte data key (DEK) encrypts every value (XChaCha20-Poly1305, random nonce,
 *   the row ID bound in as associated data so a ciphertext cannot be moved to another row).
 * - The DEK is wrapped by a key derived from the passphrase (Argon2id) and stored in
 *   the vault table. The passphrase and the unwrapped DEK are never stored in the database.
 * - While unlocked, the DEK is kept in the PHP session only, bound to the current Admin
 *   token, and discarded after an idle timeout, on lock, and on logout.
 *
 * @package NivoCart
 */
final class Vault {
	/**
	 * Prefix of every encrypted value (also tells legacy plaintext rows apart)
	 */
	public const PREFIX = 'nvv1:';

	/**
	 * Minimum passphrase length in characters
	 */
	public const MIN_PASSPHRASE_LENGTH = 12;

	/**
	 * Idle time in seconds before the vault locks itself
	 */
	public const IDLE_TIMEOUT = 900;

	/**
	 * Failed unlock attempts allowed before a lockout, and lockout length in seconds
	 */
	public const MAX_ATTEMPTS = 5;
	public const LOCKOUT_TIME = 900;

	private const AD_KEY = 'nivocart-vault-key-v1';
	private const AD_VALUE = 'nivocart-api-key-v1:';
	private const PAD_BLOCK = 64;

	/**
	 * @var Registry
	 */
	private Registry $registry;

	/**
	 * Constructor
	 *
	 * @param Registry $registry
	 */
	public function __construct(Registry $registry) {
		$this->registry = $registry;
	}

	/**
	 * Is Set Up
	 *
	 * @return bool
	 */
	public function isSetUp(): bool {
		return (bool)$this->getRow();
	}

	/**
	 * Set Up
	 *
	 * Creates the vault with a new passphrase and leaves it unlocked.
	 *
	 * @param string $passphrase
	 *
	 * @return void
	 */
	public function setUp(string $passphrase): void {
		$this->requireSodium();

		if ($this->isSetUp()) {
			throw new \RuntimeException('Error: The vault is already set up!');
		}

		$this->checkPassphrase($passphrase);

		$dek = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES);
		$salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);

		[$kek, $ops, $mem] = $this->deriveNewKey($passphrase, $salt);

		$wrapped = $this->wrap($dek, $kek);

		$this->registry->get('db')->query("INSERT INTO `" . DB_PREFIX . "api_key_vault` SET `vault_id` = '1', `salt` = '" . $this->esc(base64_encode($salt)) . "', `opslimit` = '" . (int)$ops . "', `memlimit` = '" . (int)$mem . "', `wrapped_key` = '" . $this->esc($wrapped) . "', `failed_attempts` = '0', `locked_until` = '0', `date_added` = NOW(), `date_modified` = NOW()");

		$this->openSession($dek);

		sodium_memzero($kek);
		sodium_memzero($dek);
	}

	/**
	 * Unlock
	 *
	 * @param string $passphrase
	 *
	 * @return bool False on a wrong passphrase or while locked out
	 */
	public function unlock(string $passphrase): bool {
		$this->requireSodium();

		$row = $this->getRow();

		if (!$row) {
			throw new \RuntimeException('Error: The vault is not set up!');
		}

		if ($this->getLockoutRemaining() > 0) {
			return false;
		}

		$dek = $this->unwrapWithPassphrase($row, $passphrase);

		if ($dek === null) {
			$this->registerFailure($row);

			return false;
		}

		$this->clearFailures();
		$this->openSession($dek);

		sodium_memzero($dek);

		return true;
	}

	/**
	 * Lock
	 *
	 * @return void
	 */
	public function lock(): void {
		unset($this->registry->get('session')->data['api_key_vault']);
	}

	/**
	 * Is Unlocked
	 *
	 * @return bool
	 */
	public function isUnlocked(): bool {
		return $this->getKey() !== null;
	}

	/**
	 * Get Lockout Remaining
	 *
	 * @return int Seconds left of a lockout (0 when not locked out)
	 */
	public function getLockoutRemaining(): int {
		$row = $this->getRow();

		if (!$row) {
			return 0;
		}

		return max(0, (int)$row['locked_until'] - time());
	}

	/**
	 * Is Encrypted
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	public function isEncrypted(string $value): bool {
		return strpos($value, self::PREFIX) === 0;
	}

	/**
	 * Encrypt
	 *
	 * @param string $plain
	 * @param int    $id    Row ID, bound to the ciphertext
	 *
	 * @return string
	 */
	public function encrypt(string $plain, int $id): string {
		$key = $this->requireKey();

		$nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

		$padded = sodium_pad($plain, self::PAD_BLOCK);

		$cipher = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($padded, self::AD_VALUE . $id, $nonce, $key);

		sodium_memzero($key);
		sodium_memzero($padded);

		return self::PREFIX . base64_encode($nonce . $cipher);
	}

	/**
	 * Decrypt
	 *
	 * @param string $value
	 * @param int    $id    Row ID the value was encrypted for
	 *
	 * @return string
	 */
	public function decrypt(string $value, int $id): string {
		if (!$this->isEncrypted($value)) {
			throw new \InvalidArgumentException('Error: The value is not encrypted!');
		}

		$key = $this->requireKey();

		$raw = base64_decode(substr($value, strlen(self::PREFIX)), true);

		$nonce_length = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

		if ($raw === false || strlen($raw) < ($nonce_length + SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES)) {
			sodium_memzero($key);

			throw new \RuntimeException('Error: The encrypted value is damaged!');
		}

		$padded = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(substr($raw, $nonce_length), self::AD_VALUE . $id, substr($raw, 0, $nonce_length), $key);

		sodium_memzero($key);

		if ($padded === false) {
			throw new \RuntimeException('Error: The encrypted value could not be verified!');
		}

		try {
			$plain = sodium_unpad($padded, self::PAD_BLOCK);
		} catch (\SodiumException $e) {
			throw new \RuntimeException('Error: The encrypted value is damaged!');
		}

		sodium_memzero($padded);

		return $plain;
	}

	/**
	 * Change Passphrase
	 *
	 * Re-wraps the data key. Stored values are not re-encrypted.
	 *
	 * @param string $old
	 * @param string $new
	 *
	 * @return bool False on a wrong current passphrase or while locked out
	 */
	public function changePassphrase(string $old, string $new): bool {
		$this->requireSodium();

		$row = $this->getRow();

		if (!$row) {
			throw new \RuntimeException('Error: The vault is not set up!');
		}

		$this->checkPassphrase($new);

		if ($this->getLockoutRemaining() > 0) {
			return false;
		}

		$dek = $this->unwrapWithPassphrase($row, $old);

		if ($dek === null) {
			$this->registerFailure($row);

			return false;
		}

		$salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);

		[$kek, $ops, $mem] = $this->deriveNewKey($new, $salt);

		$wrapped = $this->wrap($dek, $kek);

		$this->registry->get('db')->query("UPDATE `" . DB_PREFIX . "api_key_vault` SET `salt` = '" . $this->esc(base64_encode($salt)) . "', `opslimit` = '" . (int)$ops . "', `memlimit` = '" . (int)$mem . "', `wrapped_key` = '" . $this->esc($wrapped) . "', `failed_attempts` = '0', `locked_until` = '0', `date_modified` = NOW() WHERE `vault_id` = '1'");

		$this->openSession($dek);

		sodium_memzero($kek);
		sodium_memzero($dek);

		return true;
	}

	/**
	 * Check Passphrase
	 *
	 * @param string $passphrase
	 *
	 * @return void
	 */
	private function checkPassphrase(string $passphrase): void {
		if (mb_strlen($passphrase, 'UTF-8') < self::MIN_PASSPHRASE_LENGTH) {
			throw new \InvalidArgumentException('Error: The passphrase is too short!');
		}
	}

	/**
	 * Require Sodium
	 *
	 * @return void
	 */
	private function requireSodium(): void {
		if (!function_exists('sodium_crypto_pwhash') || !function_exists('sodium_crypto_aead_xchacha20poly1305_ietf_encrypt') || !defined('SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13')) {
			throw new \RuntimeException('Error: The PHP sodium extension with Argon2id is required!');
		}
	}

	/**
	 * Get Row
	 *
	 * @return array<string, string>
	 */
	private function getRow(): array {
		$query = $this->registry->get('db')->query("SELECT * FROM `" . DB_PREFIX . "api_key_vault` WHERE `vault_id` = '1'");

		return $query->row;
	}

	/**
	 * Esc
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private function esc(string $value): string {
		return $this->registry->get('db')->escape($value);
	}

	/**
	 * Derive New Key
	 *
	 * Uses the MODERATE Argon2id preset and falls back to INTERACTIVE if the server
	 * cannot allocate the memory. The limits used are stored with the vault.
	 *
	 * @param string $passphrase
	 * @param string $salt
	 *
	 * @return array{0: string, 1: int, 2: int}
	 */
	private function deriveNewKey(string $passphrase, string $salt): array {
		$presets = [
			[SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_MODERATE],
			[SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE]
		];

		foreach ($presets as $preset) {
			try {
				$kek = sodium_crypto_pwhash(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES, $passphrase, $salt, $preset[0], $preset[1], SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13);

				return [$kek, $preset[0], $preset[1]];
			} catch (\SodiumException $e) {
				continue;
			}
		}

		throw new \RuntimeException('Error: The server could not derive the vault key!');
	}

	/**
	 * Unwrap With Passphrase
	 *
	 * @param array<string, string> $row
	 * @param string                $passphrase
	 *
	 * @return string|null The data key, or null on a wrong passphrase
	 */
	private function unwrapWithPassphrase(array $row, string $passphrase): ?string {
		$salt = base64_decode($row['salt'], true);

		if ($salt === false || strlen($salt) !== SODIUM_CRYPTO_PWHASH_SALTBYTES) {
			throw new \RuntimeException('Error: The vault record is damaged!');
		}

		$kek = sodium_crypto_pwhash(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES, $passphrase, $salt, (int)$row['opslimit'], (int)$row['memlimit'], SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13);

		$dek = $this->unwrap($row['wrapped_key'], $kek);

		sodium_memzero($kek);

		return $dek;
	}

	/**
	 * Wrap
	 *
	 * @param string $dek
	 * @param string $kek
	 *
	 * @return string
	 */
	private function wrap(string $dek, string $kek): string {
		$nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

		return base64_encode($nonce . sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($dek, self::AD_KEY, $nonce, $kek));
	}

	/**
	 * Unwrap
	 *
	 * @param string $wrapped
	 * @param string $kek
	 *
	 * @return string|null
	 */
	private function unwrap(string $wrapped, string $kek): ?string {
		$raw = base64_decode($wrapped, true);

		$nonce_length = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

		if ($raw === false || strlen($raw) < ($nonce_length + SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES)) {
			return null;
		}

		$dek = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(substr($raw, $nonce_length), self::AD_KEY, substr($raw, 0, $nonce_length), $kek);

		return ($dek === false) ? null : $dek;
	}

	/**
	 * Register Failure
	 *
	 * @param array<string, string> $row
	 *
	 * @return void
	 */
	private function registerFailure(array $row): void {
		$attempts = (int)$row['failed_attempts'] + 1;
		$locked_until = 0;

		if ($attempts >= self::MAX_ATTEMPTS) {
			$attempts = 0;
			$locked_until = time() + self::LOCKOUT_TIME;
		}

		$this->registry->get('db')->query("UPDATE `" . DB_PREFIX . "api_key_vault` SET `failed_attempts` = '" . (int)$attempts . "', `locked_until` = '" . (int)$locked_until . "' WHERE `vault_id` = '1'");
	}

	/**
	 * Clear Failures
	 *
	 * @return void
	 */
	private function clearFailures(): void {
		$this->registry->get('db')->query("UPDATE `" . DB_PREFIX . "api_key_vault` SET `failed_attempts` = '0', `locked_until` = '0' WHERE `vault_id` = '1'");
	}

	/**
	 * Open Session
	 *
	 * @param string $dek
	 *
	 * @return void
	 */
	private function openSession(string $dek): void {
		$session = $this->registry->get('session');

		$session->data['api_key_vault'] = [
			'key'     => base64_encode($dek),
			'token'   => (string)($session->data['token'] ?? ''),
			'expires' => time() + self::IDLE_TIMEOUT
		];
	}

	/**
	 * Get Key
	 *
	 * Returns the data key while the session is unlocked, bound to the current
	 * Admin token and not idle for too long. Each successful call extends the idle time.
	 *
	 * @return string|null
	 */
	private function getKey(): ?string {
		$session = $this->registry->get('session');

		$state = $session->data['api_key_vault'] ?? null;

		if (!is_array($state) || !isset($state['key'], $state['token'], $state['expires'])) {
			return null;
		}

		$token = (string)($session->data['token'] ?? '');

		if ($token === '' || !hash_equals((string)$state['token'], $token) || (int)$state['expires'] < time()) {
			$this->lock();

			return null;
		}

		$key = base64_decode((string)$state['key'], true);

		if ($key === false || strlen($key) !== SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES) {
			$this->lock();

			return null;
		}

		$session->data['api_key_vault']['expires'] = time() + self::IDLE_TIMEOUT;

		return $key;
	}

	/**
	 * Require Key
	 *
	 * @return string
	 */
	private function requireKey(): string {
		$key = $this->getKey();

		if ($key === null) {
			throw new \RuntimeException('Error: The vault is locked!');
		}

		return $key;
	}
}
