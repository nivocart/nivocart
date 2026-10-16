<?php
/**
 * Class ControllerCommonCaptcha
 *
 * Generates a new captcha code, keeps it in the session and returns it as a PNG image.
 * The code itself is never output as text.
 *
 * @package NivoCart
 */
class ControllerCommonCaptcha extends Controller {
	/**
	 * Function index
	 */
	public function index() {
		$this->load->library('captcha');

		$captcha = new Captcha();

		$image = $captcha->getImage();

		if ($image === '') {
			// GD/FreeType or the font is unavailable. No code is stored, so the form cannot be submitted.
			return;
		}

		$this->session->data['captcha'] = $captcha->getCode();

		$this->response->addHeader('Content-Type: image/png');
		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		$this->response->addHeader('Pragma: no-cache');
		$this->response->addHeader('X-Content-Type-Options: nosniff');

		$this->response->setOutput($image);
	}
}
