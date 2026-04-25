<?php

/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Shared
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    ##VERSION##, ##DATE##
 */

/**
 * PHPExcel_Shared_CodePage
 *
 * @category   PHPExcel
 * @package    PHPExcel_Shared
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_Shared_CodePage {
	/**
	 * Convert Microsoft Code Page Identifier to Code Page Name which iconv
	 * and mbstring understands
	 *
	 * @param integer $codePage Microsoft Code Page Indentifier
	 * @return string Code Page Name
	 * @throws PHPExcel_Exception
	 */
	public static function NumberToName($codePage = 1252) {
		return match ($codePage) {
			367 => 'ASCII',
			437 => 'CP437',
			720 => throw new PHPExcel_Exception('Code page 720 not supported.'),
			737 => 'CP737',
			775 => 'CP775',
			850 => 'CP850',
			852 => 'CP852',
			855 => 'CP855',
			857 => 'CP857',
			858 => 'CP858',
			860 => 'CP860',
			861 => 'CP861',
			862 => 'CP862',
			863 => 'CP863',
			864 => 'CP864',
			865 => 'CP865',
			866 => 'CP866',
			869 => 'CP869',
			874 => 'CP874',
			932 => 'CP932',
			936 => 'CP936',
			949 => 'CP949',
			950 => 'CP950',
			1200 => 'UTF-16LE',
			1250 => 'CP1250',
			1251 => 'CP1251',
			0, 1252 => 'CP1252',
			1253 => 'CP1253',
			1254 => 'CP1254',
			1255 => 'CP1255',
			1256 => 'CP1256',
			1257 => 'CP1257',
			1258 => 'CP1258',
			1361 => 'CP1361',
			10000 => 'MAC',
			10001 => 'CP932',
			10002 => 'CP950',
			10003 => 'CP1361',
			10006 => 'MACGREEK',
			10007 => 'MACCYRILLIC',
			10008 => 'CP936',
			10029 => 'MACCENTRALEUROPE',
			10079 => 'MACICELAND',
			10081 => 'MACTURKISH',
			21010 => 'UTF-16LE',
			32768 => 'MAC',
			32769 => throw new PHPExcel_Exception('Code page 32769 not supported.'),
			65000 => 'UTF-7',
			65001 => 'UTF-8',
			default => throw new PHPExcel_Exception('Unknown codepage: ' . $codePage),
		};
	}
}
