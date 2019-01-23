<?php
class ControllerErrorForbidden extends Controller {

	public function index() {
		if (!$this->user->isLogged()) {
			$ban_page = $this->config->get('config_ban_page');

			if ($ban_page == 'search') {
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
