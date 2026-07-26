<?php
/**
 * Abstract Class Controller
 *
 * @package NivoCart
 */
abstract class Controller {
	/**
	 * @var Registry
	 */
	protected $registry;

	/**
	 * @var int
	 */
	protected int $id = 0;

	/**
	 * @var string
	 */
	protected string $layout = '';

	/**
	 * @var string
	 */
	protected string $template = '';

	/**
	 * @var array<string, string>
	 */
	protected array $children = [];

	/**
	 * @var array<string, string>
	 */
	protected array $data = [];

	/**
	 * @var string
	 */
	protected string $output = '';

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
		if ($this->registry->has($key)) {
			return $this->registry->get($key);
		} else {
			throw new \Exception('Error: Could not call registry key ' . $key . '!');
		}
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
	 * Forward
	 *
	 * @param string $route
	 * @param array $args
	 */
	protected function forward(string $route, array $args = []) {
		return new Action($route, $args);
	}

	/**
	 * Redirect
	 *
	 * @param string $url
	 * @param int $status
	 */
	protected function redirect(string $url, int $status = 302) {
		header('Location: ' . str_replace(['&amp;', "\n", "\r"], ['&', '', ''], $url), true, $status);
	}

	/**
	 * getChild
	 *
	 * @param string $child
	 * @param array $args
	 */
	protected function getChild(string $child, array $args = []) {
		$action = new Action($child, $args);

		if (file_exists($action->getFile())) {
			require_once $action->getFile();

			$class = $action->getClass();
			$controller = new $class($this->registry);
			$controller->{$action->getMethod()}($action->getArgs());

			return $controller->output;

		} else {
			throw new \Exception('Error: Could not load controller ' . $child . '!');
		}
	}

	/**
	 * hasAction
	 *
	 * @param string $child
	 * @param array $args
	 *
	 * @return bool
	 */
	protected function hasAction(string $child, array $args = []): bool {
		$action = new Action($child, $args);

		if (file_exists($action->getFile())) {
			require_once $action->getFile();

			$class = $action->getClass();
			$controller = new $class($this->registry);

			if (method_exists($controller, $action->getMethod())) {
				return true;
			} else {
				return false;
			}

		} else {
			return false;
		}
	}

	/**
	 * Resolve Template
	 *
	 * Resolves the correct catalog template path for a given template name,
	 * falling back to the default theme if the active theme does not provide it.
	 * Sets $this->template ready for render().
	 *
	 * Usage (catalog controllers only):
	 *   $this->resolveTemplate('common/content_high');
	 *   $this->resolveTemplate('product/product');
	 *
	 * @param string $name  Template name relative to /template/, without .tpl extension
	 *
	 * @return void
	 */
	protected function resolveTemplate(string $name): void {
		$theme = (string)$this->config->get('config_template');

		$themed = $theme . '/template/' . $name . '.tpl';

		$this->template = file_exists(DIR_TEMPLATE . $themed) ? $themed : 'default/template/' . $name . '.tpl';
	}

	/**
	 * Render
	 *
	 * @return string
	 */
	protected function render(): string {
		foreach ($this->children as $child) {
			$this->data[basename($child)] = $this->getChild($child);
		}

		if (file_exists(DIR_TEMPLATE . $this->template)) {
			extract($this->data);

			ob_start();

			require DIR_TEMPLATE . $this->template;

			$this->output = ob_get_contents();

			ob_end_clean();

			return $this->output;

		} else {
			throw new \Exception('Error: Could not load template ' . DIR_TEMPLATE . $this->template . '!');
		}
	}
}
