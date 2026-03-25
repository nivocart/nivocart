<?php
final class Registry {
	/**
	 * @var array<string, string>
	 */
	private array $data = [];

	/**
	 * get
	 *
	 * @param string $key
	 */
	public function get(string $key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}

	/**
	 * set
	 *
	 * @param string $key
	 * @param $value
	 *
	 * @return void
	 */
	public function set(string $key, $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * has
	 *
	 * @param string $key
	 */
	public function has(string $key) {
		return isset($this->data[$key]);
	}
}
