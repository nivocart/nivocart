<?php
class Template {
	/**
	 * @var array<mixed>
	 */
	public array $data = [];

	/**
	 * Constructor
	 *
	 * @param string   $filename
	 */
	public function fetch(string $filename) {
		$file = DIR_TEMPLATE . $filename;

		if (file_exists($file)) {
			extract($this->data);

			ob_start();
			include($file);
			$content = ob_get_clean();

			return $content;

		} else {
			trigger_error('Error: Could not load template ' . $file . '!');
			exit();
		}
	}
}
