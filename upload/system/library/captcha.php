<?php
class Captcha {
	/**
	 * @var string
	 */
	public string $code = '';

	/**
	 * @var string
	 */
	public string $font;

	/**
	 * @var string
	 */
	public string $file;

	/**
	 * Constructor
	 */
	public function __construct() {
		$captchaText = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4);

		$this->code = $captchaText;
	}

	public function getCode(): string {
		return $this->code;
	}

	/**
	 * Image Function
	 *
	 * Currently not displaying. Not being used.
	 */
	public function showImage(string $font) {
		if ($font) {
			$file = DIR_SYSTEM . 'fonts/' . (string)$font . '.ttf';
		} else {
			$file = DIR_SYSTEM . 'fonts/Recaptcha.ttf';
		}

		$this->image = imagecreatetruecolor(186, 42);

		$background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);

		$text_color = imagecolorallocate($this->image, 10, 10, 10);
		$text_shadow = imagecolorallocate($this->image, 128, 128, 128);

		imagefilledrectangle($this->image, 0, 0, 292, 42, $background);

		imagettftext($this->image, 22, 0, 6, 28, $text_color, $file, $this->code);
		imagettftext($this->image, 22, 0, 7, 29, $text_shadow, $file, $this->code);

		// PNG image post-processing functions
		imagealphablending($this->image, false);
		imagesavealpha($this->image, true);
		imagecolortransparent($this->image, $background);

		header('Content-type: image/png');

		imagepng($this->image, null, 9);

		imagedestroy($this->image);
	}
}
