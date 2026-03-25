<?php
class Config {
	/**
	 * @var array<string, string>
	 */
	private array $data = [];

	/**
	 * Get
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get(string $key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}

	/**
	 * Set
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function set(string $key, $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * Has
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has(string $key): bool {
		return isset($this->data[$key]);
	}

	/**
	 * Load
	 *
	 * @param string $filename
	 *
	 * @return array<string, string>
	 */
	public function load(string $filename): array {
		$file = DIR_CONFIG . $filename . '.php';

		if (is_file($file)) {
			$_ = [];

			require($file);

			$this->data = array_merge($this->data, $_);

			return $this->data;
		} else {
			trigger_error('Error: Could not load config ' . $filename . '!');
			return [];
		}
	}
}
