<?php
final class Front {
	/**
	 * @var Registry
	 */
	protected $registry;

	/**
	 * @var array<string, string>
	 */
	protected array $pre_action = [];

	/**
	 * @var object
	 */
	protected object $action;

	/**
	 * @var Action $error
	 */
	protected $error;

	/**
	 * Constructor
	 */
	public function __construct($registry) {
		$this->registry = $registry;
	}

	/**
	 * Pre Action
	 * 
	 * @param $pre_action
	 */
	public function addPreAction($pre_action) {
		$this->pre_action[] = $pre_action;
	}

	/**
	 * Dispatch
	 * 
	 * @param $action
	 * @param $error
	 */
	public function dispatch($action, $error) {
		$this->error = $error;

		foreach ($this->pre_action as $pre_action) {
			$result = $this->execute($pre_action);

			if ($result) {
				$action = $result;
				break;
			}
		}

		while ($action) {
			$action = $this->execute($action);
		}
	}

	/**
	 * Execute
	 * 
	 * @param $action
	 */
	private function execute($action) {
		if (file_exists($action->getFile())) {
			require_once($action->getFile());

			$class = $action->getClass();

			$controller = new $class($this->registry);

			if (is_callable(array($controller, $action->getMethod())) && mb_substr($action->getMethod(), 0, 2, 'UTF-8') != '__') {
				$action = call_user_func_array(array($controller, $action->getMethod()), $action->getArgs());
			} else {
				$action = $this->error;
				$this->error = '';
			}

		} else {
			$action = $this->error;
			$this->error = '';
		}

		return $action;
	}
}
