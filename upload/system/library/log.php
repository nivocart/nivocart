<?php
class Log {
	/**
	 * @var string
	 */
	private string $filename;

	/**
	 * Constructor
	 *
	 * @param string $filename
	 */
	public function __construct(string $filename) {
		$this->filename = $filename;
	}

	/**
	 * Write
	 *
	 * @param mixed $message
	 *
	 * @return void
	 */
	public function write($message): void {
		$file = DIR_LOGS . $this->filename;

		$handle = fopen($file, 'a');

		fwrite($handle, date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");

		fclose($handle);
	}
}
