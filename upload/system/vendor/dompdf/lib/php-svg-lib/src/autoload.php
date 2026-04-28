<?php

/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

spl_autoload_register(function ($class): void {
	if (str_starts_with($class, "Svg")) {
		$file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
		$file = realpath(__DIR__ . DIRECTORY_SEPARATOR . $file . '.php');

		if (file_exists($file)) {
			include_once $file;
		}
	}
});
