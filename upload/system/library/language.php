<?php
class Language {
	/**
	 * @var string
	 */
	private string $standard = 'english';

	/**
	 * @var string
	 */
	private string $directory;

	/**
	 * @var array<string, string>
	 */
	private array $data = [];

	/**
	 * Constructor
	 *
	 * @param string $directory
	 */
	public function __construct(string $directory = '') {
		$this->directory = $directory;
	}

	/**
	 * Set
	 *
	 * Set language text string
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return void
	 */
	public function set(string $key, string $value): void {
		$this->data[$key] = $value;
	}

	/**
	 * Get
	 *
	 * Get language text string
	 *
	 * @param string $key
	 */
	public function get(string $key): string {
		return $this->data[$key] ?? $key;
	}

	/**
	 * Load
	 *
	 * @param string $filename
	 *
	 * @return array<string, string>
	 */
	public function load(string $filename): array {
		$_ = array();

		$file = DIR_LANGUAGE . $this->standard . '/' . $filename . '.php';

		if (file_exists($file)) {
			require($file);
		}

		$file = DIR_LANGUAGE . $this->directory . '/' . $filename . '.php';

		if (file_exists($file)) {
			require($file);
		}

		$this->data = array_merge($this->data, $_);

		return $this->data;
	}
}
