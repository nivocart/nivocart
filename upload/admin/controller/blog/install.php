<?php
/**
 * Class ControllerBlogInstall
 *
 * @package NivoCart
 */
class ControllerBlogInstall extends Controller {
	private $error = [];

	public function index() {
		$this->language->load('blog/install');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} elseif (isset($this->session->data['warning']) ) {
			$this->data['error_warning'] = $this->session->data['warning'];

			unset($this->session->data['warning']);
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['database_found'] = $this->validateTable();

		$this->data['error_database'] = $this->language->get('error_database');

		$this->template = 'blog/install.tpl';

		$this->response->setOutput($this->render());
	}

	public function validateTable(): bool {
		$table_name = $this->db->escape('blog_article');

		$table = DB_PREFIX . $table_name;

		$query = $this->db->query("SHOW TABLES LIKE '{$table}'");

		return ($query->num_rows) ? true : false;
	}
}
