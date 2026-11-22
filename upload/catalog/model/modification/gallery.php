<?php
/**
 * Class ModelModificationGallery (Catalog)
 *
 * Reads the approved gallery images, stores new submissions and turns an uploaded photo into the
 * safe, square, watermarked WebP image that is kept on the server.
 *
 * Location: catalog/model/modification/gallery.php
 *
 * @package NivoCart
 */
class ModelModificationGallery extends Model {
	/** Stored image size (pixels). The pages show it at 600 px, the rest is for high density screens. */
	public const IMAGE_SIZE = 900;

	/** Smallest accepted image side (pixels) and the largest picture (pixels in total) we agree to decode. */
	public const IMAGE_MIN = 300;
	public const IMAGE_MAX_PIXELS = 16000000;

	/** Largest accepted upload in bytes (the browser normally sends a much smaller, already cropped image). */
	public const UPLOAD_MAX = 8388608;

	public const FOLDER_PENDING = 'data/gallery/pending/';

	/**
	 * Does the gallery table exist (is the modification installed)?
	 *
	 * @return bool
	 */
	public function isInstalled(): bool {
		$result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "gallery'");

		return (bool)$result->num_rows;
	}

	/**
	 * Approved images, latest submission first. Only what may be shown in public: no name, no email.
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getImages(array $data = []): array {
		$where = "`status` = '1'";

		if (!empty($data['filter_id'])) {
			$where .= " AND `filter_id` = '" . (int)$data['filter_id'] . "'";
		}

		$start = max(0, (int)($data['start'] ?? 0));
		$limit = max(1, (int)($data['limit'] ?? 12));

		$query = $this->db->query("SELECT `gallery_id`, `pet_name`, `filter_id`, `description`, `image`, `date_added` FROM `" . DB_PREFIX . "gallery` WHERE " . $where . " ORDER BY " . ((isset($data['sort']) && ($data['sort'] === 'random')) ? 'RAND()' : '`date_added` DESC, `gallery_id` DESC') . " LIMIT " . $start . "," . $limit);

		return $query->rows;
	}

	/**
	 * @param array<string, mixed> $data
	 *
	 * @return int
	 */
	public function getTotalImages(array $data = []): int {
		$where = "`status` = '1'";

		if (!empty($data['filter_id'])) {
			$where .= " AND `filter_id` = '" . (int)$data['filter_id'] . "'";
		}

		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "gallery` WHERE " . $where);

		return (int)$query->row['total'];
	}

	/**
	 * One approved image (public data only).
	 *
	 * @param int $gallery_id
	 *
	 * @return array<string, mixed>
	 */
	public function getImage(int $gallery_id): array {
		$query = $this->db->query("SELECT `gallery_id`, `pet_name`, `filter_id`, `description`, `image`, `date_added`, `date_modified` FROM `" . DB_PREFIX . "gallery` WHERE `gallery_id` = '" . (int)$gallery_id . "' AND `status` = '1'");

		return $query->row;
	}

	/**
	 * The previous (older) and next (newer) approved image, for the links on the image page.
	 *
	 * @param int $gallery_id
	 *
	 * @return array{previous: int, next: int}
	 */
	public function getNeighbours(int $gallery_id): array {
		$previous = $this->db->query("SELECT `gallery_id` FROM `" . DB_PREFIX . "gallery` WHERE `status` = '1' AND `gallery_id` < '" . (int)$gallery_id . "' ORDER BY `gallery_id` DESC LIMIT 1");
		$next = $this->db->query("SELECT `gallery_id` FROM `" . DB_PREFIX . "gallery` WHERE `status` = '1' AND `gallery_id` > '" . (int)$gallery_id . "' ORDER BY `gallery_id` ASC LIMIT 1");

		return [
			'previous' => $previous->num_rows ? (int)$previous->row['gallery_id'] : 0,
			'next'     => $next->num_rows ? (int)$next->row['gallery_id'] : 0
		];
	}

	/**
	 * Submissions sent from this IP address in the last 24 hours (for the rate limit).
	 *
	 * @param string $ip
	 *
	 * @return int
	 */
	public function getTotalByIp(string $ip): int {
		$query = $this->db->query("SELECT COUNT(*) AS `total` FROM `" . DB_PREFIX . "gallery` WHERE `ip` = '" . $this->db->escape($ip) . "' AND `date_added` > DATE_SUB(NOW(), INTERVAL 24 HOUR)");

		return (int)$query->row['total'];
	}

	/**
	 * Store a new submission (always pending).
	 *
	 * @param array<string, mixed> $data
	 *
	 * @return array{gallery_id: int, token: string}
	 */
	public function addImage(array $data): array {
		$token = bin2hex(random_bytes(16));

		$this->db->query("INSERT INTO `" . DB_PREFIX . "gallery` SET `pet_name` = '" . $this->db->escape($data['pet_name']) . "', `filter_id` = '" . $this->db->escape($data['filter_id']) . "', `description` = '" . $this->db->escape($data['description']) . "', `customer_name` = '" . $this->db->escape($data['customer_name']) . "', `email` = '" . $this->db->escape($data['email']) . "', `contact_optin` = '" . (int)$data['contact_optin'] . "', `image` = '" . $this->db->escape($data['image']) . "', `status` = '0', `token` = '" . $this->db->escape($token) . "', `ip` = '" . $this->db->escape($data['ip']) . "', `date_added` = NOW(), `date_modified` = NOW()");

		return [
			'gallery_id' => (int)$this->db->getLastId(),
			'token'      => $token
		];
	}

	/**
	 * The submission that belongs to a deletion link (any status), or an empty array.
	 *
	 * @param int    $gallery_id
	 * @param string $token
	 *
	 * @return array<string, mixed>
	 */
	public function getImageByToken(int $gallery_id, string $token): array {
		if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
			return [];
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "gallery` WHERE `gallery_id` = '" . (int)$gallery_id . "' AND `token` = '" . $this->db->escape($token) . "'");

		return $query->row;
	}

	/**
	 * Delete a submission completely: record, image files and page address.
	 *
	 * @param int $gallery_id
	 *
	 * @return void
	 */
	public function deleteImage(int $gallery_id): void {
		$query = $this->db->query("SELECT `image` FROM `" . DB_PREFIX . "gallery` WHERE `gallery_id` = '" . (int)$gallery_id . "'");

		if ($query->num_rows && preg_match('#^data/gallery/(pending/)?[a-z0-9\-]+\.webp$#', (string)$query->row['image'])) {
			$image = (string)$query->row['image'];

			if (is_file(DIR_IMAGE . $image)) {
				unlink(DIR_IMAGE . $image);
			}

			foreach (glob(DIR_IMAGE . 'cache/data/gallery/' . pathinfo($image, PATHINFO_FILENAME) . '-*.webp') ?: [] as $file) {
				unlink($file);
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE `query` = 'gallery_id=" . (int)$gallery_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "gallery` WHERE `gallery_id` = '" . (int)$gallery_id . "'");
	}

	/**
	 * Turn an uploaded photo into the stored image: it is decoded and drawn again (so nothing hidden in the
	 * file and no EXIF data such as GPS survives), turned upright, cropped to a square, scaled down to 900 px,
	 * watermarked and saved as WebP in the private "pending" folder.
	 *
	 * @param array<string, mixed> $file      The $_FILES entry
	 * @param string               $watermark Text printed in the bottom right corner, empty for none
	 *
	 * @return array{image: string, error: string} The stored path (relative to the image folder) or an error key
	 */
	public function saveUpload(array $file, string $watermark): array {
		$result = ['image' => '', 'error' => ''];

		if (!isset($file['error'], $file['tmp_name'], $file['size']) || ((int)$file['error'] !== UPLOAD_ERR_OK) || !is_uploaded_file((string)$file['tmp_name'])) {
			$result['error'] = 'error_upload';

			return $result;
		}

		if (((int)$file['size'] < 1) || ((int)$file['size'] > self::UPLOAD_MAX)) {
			$result['error'] = 'error_size';

			return $result;
		}

		$tmp = (string)$file['tmp_name'];

		// The real type comes from the file contents, never from the name or the browser
		$info = @getimagesize($tmp);

		if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
			$result['error'] = 'error_type';

			return $result;
		}

		$finfo = class_exists('finfo') ? new finfo(FILEINFO_MIME_TYPE) : null;

		if (!$finfo || !in_array($finfo->file($tmp), ['image/jpeg', 'image/png'], true)) {
			$result['error'] = 'error_type';

			return $result;
		}

		if (($info[0] < self::IMAGE_MIN) || ($info[1] < self::IMAGE_MIN)) {
			$result['error'] = 'error_dimensions';

			return $result;
		}

		if (($info[0] * $info[1]) > self::IMAGE_MAX_PIXELS) {
			$result['error'] = 'error_too_large';

			return $result;
		}

		if (!function_exists('imagewebp')) {
			$result['error'] = 'error_server';

			return $result;
		}

		$source = ($info[2] === IMAGETYPE_JPEG) ? @imagecreatefromjpeg($tmp) : @imagecreatefrompng($tmp);

		if (!$source) {
			$result['error'] = 'error_type';

			return $result;
		}

		// Phone photos are stored sideways with a flag, GD ignores the flag, so turn the picture upright
		if (($info[2] === IMAGETYPE_JPEG) && function_exists('exif_read_data')) {
			$exif = @exif_read_data($tmp);

			$orientation = isset($exif['Orientation']) ? (int)$exif['Orientation'] : 1;

			if (in_array($orientation, [2, 4, 5, 7], true)) {
				imageflip($source, IMG_FLIP_HORIZONTAL);
			}

			$angle = match($orientation) {
				3, 4    => 180,
				6, 7    => 270,
				5, 8    => 90,
				default => 0
			};

			if ($angle) {
				$rotated = imagerotate($source, $angle, 0);

				if ($rotated) {
					$source = $rotated;
				}
			}
		}

		$width = imagesx($source);
		$height = imagesy($source);

		// Cover crop: the largest centred square
		$side = min($width, $height);

		$size = min($side, self::IMAGE_SIZE);

		if ($size < self::IMAGE_MIN) {
			$result['error'] = 'error_dimensions';

			return $result;
		}

		$image = imagecreatetruecolor($size, $size);

		// Transparent PNG pictures get a white background
		imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));

		imagecopyresampled($image, $source, 0, 0, (int)(($width - $side) / 2), (int)(($height - $side) / 2), $size, $size, $side, $side);

		if ($watermark !== '') {
			$this->drawWatermark($image, $size, $watermark);
		}

		$directory = DIR_IMAGE . self::FOLDER_PENDING;

		if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
			$result['error'] = 'error_server';

			return $result;
		}

		$name = self::FOLDER_PENDING . bin2hex(random_bytes(16)) . '.webp';

		$quality = (int)($this->config->get('config_image_quality') ?: 85);

		if (!imagewebp($image, DIR_IMAGE . $name, max(70, min(95, $quality))) || !is_file(DIR_IMAGE . $name) || (filesize(DIR_IMAGE . $name) < 1)) {
			$result['error'] = 'error_server';

			return $result;
		}

		$result['image'] = $name;

		return $result;
	}

	/**
	 * Print the text in the bottom right corner, white with a soft dark shadow so it reads on any picture.
	 *
	 * @param \GdImage $image
	 * @param int      $size
	 * @param string   $text
	 *
	 * @return void
	 */
	private function drawWatermark(\GdImage $image, int $size, string $text): void {
		$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

		$margin = max(8, (int)round($size / 60));

		$font = DIR_SYSTEM . 'fonts/Recaptcha.ttf';

		imagealphablending($image, true);

		$shadow = imagecolorallocatealpha($image, 0, 0, 0, 70);
		$light = imagecolorallocatealpha($image, 255, 255, 255, 25);

		if (function_exists('imagettftext') && is_file($font)) {
			$points = max(9, (int)round($size / 50));

			$box = imagettfbbox($points, 0, $font, $text);

			if ($box) {
				$text_width = abs($box[2] - $box[0]);

				$x = $size - $text_width - $margin;
				$y = $size - $margin;

				imagettftext($image, $points, 0, $x + 1, $y + 1, $shadow, $font, $text);
				imagettftext($image, $points, 0, $x, $y, $light, $font, $text);

				return;
			}
		}

		// No FreeType: the plain built-in font
		$x = $size - (imagefontwidth(3) * strlen($text)) - $margin;
		$y = $size - imagefontheight(3) - $margin;

		imagestring($image, 3, $x + 1, $y + 1, $text, $shadow);
		imagestring($image, 3, $x, $y, $text, $light);
	}
}
