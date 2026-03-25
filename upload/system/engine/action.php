<?php
final class Action {
	/**
	 * @var string
	 */
	protected string $file = '';

	/**
	 * @var string
	 */
	protected string $class = '';

	/**
	 * @var string
	 */
	protected string $method = '';

	/**
	 * @var array<string, string>
	 */
	protected array $args = [];

	/**
	 * Constructor
	 *
	 * @param string $route
	 * @param array $args
	 */
	public function __construct(string $route, array $args = []) {
		$path = '';

		$parts = explode('/', str_replace('../', '', (string)$route));

		foreach ($parts as $part) {
			$path .= $part;

			if (is_dir(DIR_APPLICATION . 'controller/' . $path)) {
				$path .= '/';
				array_shift($parts);
				continue;
			}

			$file = DIR_APPLICATION . 'controller/' . str_replace(array('../', '..\\', '..'), '', (string)$path) . '.php';

			if (is_file($file)) {
				$this->file = $file;
				$this->class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', (string)$path);
				array_shift($parts);
				break;
			}
		}

		if ($args) {
			$this->args = $args;
		}

		$method = array_shift($parts);

		if ($method) {
			$this->method = $method;
		} else {
			$this->method = 'index';
		}
	}

	/**
	 * Get File
	 *
	 * @return string
	 */
	public function getFile(): string {
		return $this->file;
	}

	/**
	 * Get Class
	 *
	 * @return string
	 */
	public function getClass(): string {
		return $this->class;
	}

	/**
	 * Get Method
	 *
	 * @return string
	 */
	public function getMethod(): string {
		return $this->method;
	}

	/**
	 * Get Args
	 *
	 * @return array<string, string>
	 */
	public function getArgs(): array {
		return $this->args;
	}
}
