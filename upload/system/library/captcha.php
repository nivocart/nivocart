<?php
/**
 * Library Class Captcha
 *
 * @package NivoCart
 */
class Captcha {
	/**
	 * @var string
	 */
	public string $code = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$last = strlen($characters) - 1;

		for ($i = 0; $i < 4; $i++) {
			$this->code .= $characters[random_int(0, $last)];
		}
	}

	/**
	 * Get Code
	 *
	 * The code must only ever be stored in the session. It must never be sent to the client as text.
	 *
	 * @return string
	 */
	public function getCode(): string {
		return $this->code;
	}

	/**
	 * Get Image
	 *
	 * Builds the captcha as a PNG image and returns the raw image data. Nothing is sent to the browser here,
	 * so no stray output can corrupt the image.
	 *
	 * @param string $font
	 *
	 * @return string Raw PNG data or an empty string if GD/FreeType is unavailable.
	 */
	public function getImage(string $font = ''): string {
		if (!function_exists('imagecreatetruecolor') || !function_exists('imagettftext')) {
			return '';
		}

		$file = DIR_SYSTEM . 'fonts/' . ($font ? basename($font) : 'Recaptcha') . '.ttf';

		if (!is_file($file)) {
			return '';
		}

		$width = 186;
		$height = 42;

		$image = imagecreatetruecolor($width, $height);

		$background = imagecolorallocate($image, 250, 250, 250);
		$text_color = imagecolorallocate($image, 10, 10, 10);
		$text_shadow = imagecolorallocate($image, 128, 128, 128);
		$noise_color = imagecolorallocate($image, 150, 150, 150);

		imagefilledrectangle($image, 0, 0, $width, $height, $background);

		// Background noise: dots and lines
		for ($i = 0; $i < 60; $i++) {
			imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $noise_color);
		}

		for ($i = 0; $i < 4; $i++) {
			imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $noise_color);
		}

		// Draw each character with a slight random angle and vertical offset
		$length = strlen($this->code);
		$step = (int)floor(($width - 20) / $length);

		for ($i = 0; $i < $length; $i++) {
			$x = 10 + ($i * $step) + random_int(0, 6);
			$y = 29 + random_int(-3, 3);
			$angle = random_int(-12, 12);

			imagettftext($image, 22, $angle, $x + 1, $y + 1, $text_shadow, $file, $this->code[$i]);
			imagettftext($image, 22, $angle, $x, $y, $text_color, $file, $this->code[$i]);
		}

		ob_start();

		imagepng($image, null, 9);

		$data = (string)ob_get_clean();

		unset($image);

		return $data;
	}
}
