<?php
class ControllerBlogInstall extends Controller {

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

	public function validateTable() {
		$table_name = $this->db->escape('blog_article');

		$table = DB_PREFIX . $table_name;

		$query = $this->db->query("SHOW TABLES LIKE '{$table}'");

		if ($query->num_rows) {
			return true;
		}

		return false;
	}
}
