<?php
/**
 * Class ModelToolImage
 *
 * @package NivoCart
 */
class ModelToolImage extends Model {
	/**
	 * Function Resize
	 */
    public function resize(string $filename, int $width, int $height): ?string {
        $filename = html_entity_decode($filename, ENT_QUOTES, 'UTF-8');

        // Security & existence check
        $real = realpath(DIR_IMAGE . $filename);

        if (!$real || !is_file($real) || substr(str_replace('\\', '/', $real), 0, mb_strlen(DIR_IMAGE, 'UTF-8')) !== DIR_IMAGE) {
            return null;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $old_image = $filename;
        $new_image = 'cache/' . mb_substr($filename, 0, strrpos($filename, '.'), 'UTF-8') . '-' . $width . 'x' . $height . '.' . $extension;

        // Create cache directory if needed
        $cache_dir = DIR_IMAGE . dirname($new_image);

        if (!is_dir($cache_dir) && !mkdir($cache_dir, 0755, true)) {
            return null;
        }

        // Generate cached image if missing or stale
        if (!is_file(DIR_IMAGE . $new_image) || filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image)) {
            [$width_orig, $height_orig] = getimagesize(DIR_IMAGE . $old_image);

            if ($width_orig !== $width || $height_orig !== $height) {
                $image = new Image(DIR_IMAGE . $old_image);
                $image->resize($width, $height);
                $image->save(DIR_IMAGE . $new_image);
            } else {
                copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
            }
        }

        // Build and return the full URL
        $encoded_image = implode('/', array_map('rawurlencode', explode('/', $new_image)));

        $is_https = (!empty($this->request->server['HTTPS']) && in_array($this->request->server['HTTPS'], ['on', '1'], true)) ||
            (isset($this->request->server['SERVER_PORT']) && $this->request->server['SERVER_PORT'] === '443') ||
            (!empty($this->request->server['HTTP_X_FORWARDED_PROTO']) && $this->request->server['HTTP_X_FORWARDED_PROTO'] === 'https');

        $base_url = $is_https ? HTTPS_CATALOG : HTTP_CATALOG;

        return $base_url . 'image/' . $encoded_image;
    }
}
