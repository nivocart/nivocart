<?php

/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace FontLib\Table;

use FontLib\TrueType\File;
use FontLib\Font;
use FontLib\BinaryStream;

/**
 * Generic Font table directory entry.
 *
 * @package php-font-lib
 */
class DirectoryEntry extends BinaryStream {
	/**
	 * @var File
	 */
	protected $font;

	/**
	 * @var Table
	 */
	protected $font_table;

	public $entryLength = 4;

	public $tag;
	public $checksum;
	public $offset;
	public $length;

	protected $origF;

	public static function computeChecksum($data) {
		$len = strlen($data);
		$mod = $len % 4;

		if ($mod) {
			$data = str_pad($data, $len + (4 - $mod), "\0");
		}

		$len = strlen($data);

		$hi = 0x0000;
		$lo = 0x0000;

		for ($i = 0; $i < $len; $i += 4) {
			$hi += (ord($data[$i]) << 8) + ord($data[$i + 1]);
			$lo += (ord($data[$i + 2]) << 8) + ord($data[$i + 3]);

			$hi += $lo >> 16;

			$lo = $lo & 0xFFFF;
			$hi = $hi & 0xFFFF;
		}

		return ($hi << 8) + $lo;
	}

	public function __construct(File $font) {
		$this->font = $font;
		$this->f = $font->f;
	}

	public function parse(): void {
		$this->tag = $this->font->read(4);
	}

	public function open($filename, $mode = self::modeRead): void {
	}

	public function setTable(Table $font_table): void {
		$this->font_table = $font_table;
	}

	public function encode($entry_offset): void {
		Font::d("\n==== $this->tag ====");

		$data = $this->font_table;
		$font = $this->font;

		$table_offset = $font->pos();
		$this->offset = $table_offset;
		$table_length = $data->encode();

		$font->seek($table_offset);
		$table_data = $font->read($table_length);

		$font->seek($entry_offset);

		$font->write($this->tag, 4);
		$font->writeUInt32(self::computeChecksum($table_data));
		$font->writeUInt32($table_offset);
		$font->writeUInt32($table_length);

		Font::d("Bytes written = $table_length");

		$font->seek($table_offset + $table_length);
	}

	/**
	 * @return File
	 */
	public function getFont() {
		return $this->font;
	}

	public function startRead(): void {
		$this->font->seek($this->offset);
	}

	public function endRead(): void {
	}

	public function startWrite(): void {
		$this->font->seek($this->offset);
	}

	public function endWrite(): void {
	}
}
