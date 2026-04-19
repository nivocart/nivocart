<?php
class Image {
	/**
	 * @var string
	 */
	public string $imagefile;

	/**
	 * @var mixed
	 */
	public $image;

	/**
	 * @var int
	 */
	private int $width;

	/**
	 * @var int
	 */
	private int $height;

	/**
	 * @var string
	 */
	private string $bits;

	/**
	 * @var string
	 */
	private string $mime;

	protected $watermark;

	/**
	 * Constructor
	 *
	 * @param string $imagefile
	 */
	public function __construct(string $imagefile) {
		if (!extension_loaded('gd')) {
			exit('Error: PHP GD is not installed!');
		}

		if (file_exists($imagefile) && filesize($imagefile) > 0) {
			$this->imagefile = $imagefile;

			$info = getimagesize($imagefile);

			$this->width = $info[0];
			$this->height = $info[1];

			$this->bits = isset($info['bits']) ? $info['bits'] : '';
			$this->mime = isset($info['mime']) ? $info['mime'] : '';

			$this->image = match($this->mime) {
				'image/jpeg', 'image/pjpeg' => imagecreatefromjpeg($imagefile),
				'image/png'                 => imagecreatefrompng($imagefile),
				'image/gif'                 => imagecreatefromgif($imagefile),
				'image/webp'                => imagecreatefromwebp($imagefile),
				default                     => null
			};

			clearstatcache();

		} else {
			exit('Error: Could not load image ' . $imagefile . '!');
		}
	}

	/**
	 * Get File
	 *
	 * @return string
	 */
	public function getFile(): string {
		return $this->imagefile;
	}

	/**
	 * Get Image
	 *
	 * @return mixed
	 */
	public function getImage() {
		return $this->image ?: null;
	}

	/**
	 * Get Width
	 *
	 * @return int
	 */
	public function getWidth(): int {
		return $this->width;
	}

	/**
	 * Get Height
	 *
	 * @return int
	 */
	public function getHeight(): int {
		return $this->height;
	}

	/**
	 * Get Bits
	 *
	 * @return string
	 */
	public function getBits(): string {
		return $this->bits;
	}

	/**
	 * Get Mime
	 *
	 * @return string
	 */
	public function getMime(): string {
		return $this->mime;
	}

	/**
	 * Save
	 *
	 * @param string $imagefile
	 * @param int    $quality
	 *
	 * @return void
	 */
	public function save(string $imagefile, int $quality = 90): void {
		$info = pathinfo($imagefile);

		$extension = strtolower($info['extension']);

		if (is_object($this->image) || is_resource($this->image)) {
			$result = match($extension) {
				'jpg', 'jpeg' => (function() use ($imagefile, $quality) {
					imageinterlace($this->image, true);
					return imagejpeg($this->image, $imagefile, $quality);
				})(),
				'png'  => imagepng($this->image, $imagefile),
				'gif'  => imagegif($this->image, $imagefile),
				'webp' => imagewebp($this->image, $imagefile),
				default => ''
			};

			imagedestroy($this->image);

			if (class_exists('Imagick') && !empty($result)) {
				$img = new Imagick($imagefile);

				if (!empty($img)) {
					$img->stripImage();
					$img->writeImage($imagefile);
					$img->destroy();
				}
			}
		}
	}

	/**
	 * Resize
	 *
	 * @param int    $width
	 * @param int    $height
	 * @param string $default
	 *
	 * @return void
	 */
	public function resize(int $width = 0, int $height = 0, string $default = ''): void {
		if (!$this->width || !$this->height) {
			return;
		}

		$xpos = 0;
		$ypos = 0;
		$scale = 1;

		$scale_w = $width / $this->width;
		$scale_h = $height / $this->height;

		$scale = match($default) {
			'w'     => $scale_w,
			'h'     => $scale_h,
			default => min($scale_w, $scale_h)
		};

		if ($scale === 1 && $scale_h === $scale_w && $this->mime !== 'image/png') {
			return;
		}

		$new_width = (int)($this->width * $scale);
		$new_height = (int)($this->height * $scale);

		$xpos = (int)(($width - $new_width) / 2);
		$ypos = (int)(($height - $new_height) / 2);

		$image_old = $this->image;

		$this->image = imagecreatetruecolor($width, $height);

		if ($this->mime === 'image/png') {
			imagealphablending($this->image, false);
			imagesavealpha($this->image, true);
			$background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);
			imagecolortransparent($this->image, $background);
		} else {
			$background = imagecolorallocate($this->image, 255, 255, 255);
		}

		imagefilledrectangle($this->image, 0, 0, $width, $height, $background);

		imagecopyresampled($this->image, $image_old, $xpos, $ypos, 0, 0, $new_width, $new_height, $this->width, $this->height);

		imagedestroy($image_old);

		$this->width = $width;
		$this->height = $height;
	}

	/**
	 * Watermark
	 *
	 * @param self   $watermark
	 * @param string $position
	 *
	 * @return void
	 */
	public function watermark(self $watermark, string $position = 'bottomright'): void {
		[$watermark_pos_x, $watermark_pos_y] = match($position) {
			'topleft'      => [0, 0],
			'topcenter'    => [intval(($this->width - $watermark->getWidth()) / 2), 0],
			'topright'     => [$this->width - $watermark->getWidth(), 0],
			'middleleft'   => [0, intval(($this->height - $watermark->getHeight()) / 2)],
			'middlecenter' => [intval(($this->width - $watermark->getWidth()) / 2), intval(($this->height - $watermark->getHeight()) / 2)],
			'middleright'  => [$this->width - $watermark->getWidth(), intval(($this->height - $watermark->getHeight()) / 2)],
			'bottomleft'   => [0, $this->height - $watermark->getHeight()],
			'bottomcenter' => [intval(($this->width - $watermark->getWidth()) / 2), $this->height - $watermark->getHeight()],
			'bottomright'  => [$this->width - $watermark->getWidth(), $this->height - $watermark->getHeight()],
		};

		imagealphablending($this->image, true);
		imagesavealpha($this->image, true);
		imagecopy($this->image, $watermark->getImage(), $watermark_pos_x, $watermark_pos_y, 0, 0, $watermark->getWidth(), $watermark->getHeight());

		imagedestroy($watermark->getImage());
	}

	/**
	 * Crop
	 *
	 * @param int $top_x
	 * @param int $top_y
	 * @param int $bottom_x
	 * @param int $bottom_y
	 *
	 * @return void
	 */
	public function crop(int $top_x, int $top_y, int $bottom_x, int $bottom_y): void {
		$image_old = $this->image;
		$this->image = imagecreatetruecolor($bottom_x - $top_x, $bottom_y - $top_y);

		imagecopy($this->image, $image_old, 0, 0, $top_x, $top_y, $this->width, $this->height);
		imagedestroy($image_old);

		$this->width = $bottom_x - $top_x;
		$this->height = $bottom_y - $top_y;
	}

	/**
	 * Rotate
	 *
	 * @param int    $degree
	 * @param string $color
	 *
	 * @return void
	 */
	public function rotate(int $degree, string $color = 'FFFFFF'): void {
		$rgb = $this->html2rgb($color);

		$this->image = imagerotate($this->image, $degree, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));

		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}

	/**
	 * Filter
	 *
	 * @return void
	 */
	private function filter(): void {
		$args = func_get_args();

		imagefilter(...$args);
	}

	/**
	 * Text
	 *
	 * @param string $text
	 * @param int    $x
	 * @param int    $y
	 * @param int    $size
	 * @param string $color
	 *
	 * @return void
	 */
	private function text(string $text, int $x = 0, int $y = 0, int $size = 5, string $color = '000000'): void {
		$rgb = $this->html2rgb($color);

		imagestring($this->image, $size, $x, $y, $text, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));
	}

	/**
	 * Merge
	 *
	 * @param self $merge
	 * @param int  $x
	 * @param int  $y
	 * @param int  $opacity
	 *
	 * @return void
	 */
	private function merge(self $merge, int $x = 0, int $y = 0, int $opacity = 100): void {
		imagecopymerge($this->image, $merge->getImage(), $x, $y, 0, 0, $merge->getWidth(), $merge->getHeight(), $opacity);
	}

	/**
	 * HTML2RGB
	 *
	 * @param string $color
	 *
	 * @return array<int, int>
	 */
	private function html2rgb(string $color): array {
		if ($color[0] === '#') {
			$color = substr($color, 1);
		}

		if (strlen($color) === 6) {
			[
				$r,
				$g,
				$b
			] = [
				$color[0] . $color[1],
				$color[2] . $color[3],
				$color[4] . $color[5]
			];
		} elseif (strlen($color) === 3) {
			[
				$r,
				$g,
				$b
			] = [
				$color[0] . $color[0],
				$color[1] . $color[1],
				$color[2] . $color[2]
			];
		} else {
			return [];
		}

		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);

		return [
			$r,
			$g,
			$b
		];
	}
}
