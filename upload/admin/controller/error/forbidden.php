<?php
/**
 * Class ControllerErrorForbidden
 *
 * @package NivoCart
 */
class ControllerErrorForbidden extends Controller {
	/** Error array Placeholder */

	public function index() {
		if (!$this->user->isLogged()) {
			$ban_page = $this->config->get('config_ban_page');

			if ($ban_page === 'search') {
				$end_page = 'search';
			} else {
				$end_page = 'firewall';
			}

			$this->session->destroy();

			header('Location: ../security/' . $end_page . '.html');
			exit();
		}
	}
}
