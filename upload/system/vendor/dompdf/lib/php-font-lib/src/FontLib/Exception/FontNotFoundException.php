<?php

namespace FontLib\Exception;

class FontNotFoundException extends \Exception {
	/**
	 * @construct
	 */
    public function __construct($fontPath) {
        $this->message = 'Font not found in: ' . $fontPath;
    }
}
