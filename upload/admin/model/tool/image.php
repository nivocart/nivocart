<?php
/**
 * Class ModelToolImage
 *
 * @package NivoCart
 */
class ModelToolImage extends Model {
	private string $extension;
	private string $old_image;
	private string $new_image;

	public function resize(string $filename, int $width, int $height) {
		$filename = html_entity_decode($filename, ENT_QUOTES, 'UTF-8');

		if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, mb_strlen(DIR_IMAGE, 'UTF-8')) !== DIR_IMAGE) {
			return;
		}

		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		$old_image = $filename;
		$new_image = 'cache/' . mb_substr($filename, 0, strrpos($filename, '.', 0), 'UTF-8') . '-' . (int)$width . 'x' . (int)$height . '.' . $extension;

		if (!is_file(DIR_IMAGE . $new_image) || (filectime(DIR_IMAGE . $old_image) > filectime(DIR_IMAGE . $new_image))) {
			$path = '';

			$directories = explode('/', dirname($new_image));

			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;

				if (!is_dir(DIR_IMAGE . $path)) {
					@mkdir(DIR_IMAGE . $path, 0777);
				}
			}

			list ($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);

			if ($width_orig !== $width || $height_orig !== $height) {
				$image = new Image(DIR_IMAGE . $old_image);
				$image->resize($width, $height);
				$image->save(DIR_IMAGE . $new_image);
			} else {
				copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
			}
		}

		$new_image = str_replace(' ', '%20', $new_image);

		// Resolve server base URL
		if ((isset($this->request->server['HTTPS']) && in_array($this->request->server['HTTPS'], ['on', '1'], true)) ||
			(isset($this->request->server['SERVER_PORT']) && $this->request->server['SERVER_PORT'] === '443') ||
			(isset($this->request->server['HTTP_X_FORWARDED_PROTO']) && $this->request->server['HTTP_X_FORWARDED_PROTO'] === 'https')) {
			return HTTPS_CATALOG . 'image/' . $new_image;
		} else {
			return HTTP_CATALOG . 'image/' . $new_image;
		}
	}
}
