<?php
final class Loader {
	/**
	 * @var Registry
	 */
	protected $registry;

	/**
	 * Constructor
	 */
	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * __get
	 *
	 * @param string $key
	 *
	 * @return object
	 */
	public function __get(string $key): object {
		return $this->registry->get($key);
	}

	/**
	 * __set
	 *
	 * @param string $key
	 * @param object $value
	 *
	 * @return void
	 */
	public function __set(string $key, object $value): void {
		$this->registry->set($key, $value);
	}

	/**
	 * Model
	 *
	 * @param string $route
	 *
	 * @return void
	 */
	public function model(string $route): void {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		$file = DIR_APPLICATION . 'model/' . $route . '.php';

		$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', (string)$route);

		if (is_file($file)) {
			include_once($file);
			$this->registry->set('model_' . str_replace(['/', '-', '.'], ['_', '', ''], (string)$route), new $class($this->registry));
		} else {
			throw new \Exception('Error: Could not load model ' . $route . '!');
		}
	}

	/**
	 * Library
	 *
	 * @param string $route
	 *
	 * @return void
	 */
	public function library(string $route): void {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		$file = DIR_SYSTEM . 'library/' . $route . '.php';

		if (is_file($file)) {
			include_once($file);
		} else {
			throw new \Exception('Error: Could not load library ' . $route . '!');
		}
	}

	/**
	 * Helper
	 *
	 * @param string $route
	 *
	 * @return void
	 */
	public function helper(string $route): void {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		$file = DIR_SYSTEM . 'helper/' . $route . '.php';

		if (is_file($file)) {
			include_once($file);
		} else {
			throw new \Exception('Error: Could not load helper ' . $route . '!');
		}
	}

	/**
	 * Helper
	 *
	 * @param string $driver
	 * @param string $hostname
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @param int $port
	 */
	public function database(string $driver, string $hostname, string $username, string $password, string $database, int $port = 0) {
		// Sanitize the call
		$driver = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$driver);

		$file = DIR_SYSTEM . 'database/' . $driver . '.php';

		$class = 'Database' . $driver;

		if (is_file($file)) {
			include_once($file);
			$this->registry->set(str_replace('/', '_', $driver), new $class($hostname, $username, $password, $database, $port));
		} else {
			throw new \Exception('Error: Could not load database ' . $driver . '!');
		}
	}

	/**
	 * Config
	 *
	 * @param string $route
	 */
	public function config(string $route) {
		$this->config->load($route);
	}

	/**
	 * Language
	 *
	 * @param string $route
	 */
	public function language(string $route) {
		return $this->language->load($route);
	}
}
