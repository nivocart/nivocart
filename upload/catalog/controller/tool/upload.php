<?php
/**
 * Class ControllerToolUpload
 *
 * @package NivoCart
 */
class ControllerToolUpload extends Controller {
	/** Error array Placeholder */

	public function index() {
		$this->language->load('tool/upload');

		$json = [];

		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

			// Validate the filename length
			if ((mb_strlen($filename, 'UTF-8') < 3) || (mb_strlen($filename, 'UTF-8') > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}

			// Validate the extension, the real MIME type (detected server-side) and the contents
			$this->load->library('fileupload');

			$fileupload = new FileUpload();

			if (!$fileupload->validate($this->request->files['file']['tmp_name'], $filename, (string)$this->config->get('config_file_extension_allowed'), (string)$this->config->get('config_file_mime_allowed'))) {
				$json['error'] = $this->language->get('error_filetype');
			}

			// Return any upload error
			if ($this->request->files['file']['error'] !== UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}

		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		clearstatcache();

		if (!$json && is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
			$file = basename($filename) . '.' . bin2hex(random_bytes(8));

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

			// Hide the uploaded file name so people can not link to it directly.
			$this->load->model('tool/upload');

			$json['code'] = $this->model_tool_upload->addUpload($filename, $file);

			$json['success'] = $this->language->get('text_upload');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
