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
 * @category	PHPExcel
 * @package		PHPExcel_Chart
 * @copyright	Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license		http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version		##VERSION##, ##DATE##
 */

/**
 * PHPExcel_Chart_Title
 *
 * @category	PHPExcel
 * @package		PHPExcel_Chart
 * @copyright	Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_Chart_Title {
	/**
	 * Create a new PHPExcel_Chart_Title
	 * @param string $caption
	 */
	public function __construct(
		/**
		 * Title Caption
		 */
		private $_caption = null,
		/**
		 * Title Layout
		 */
		private readonly ?\PHPExcel_Chart_Layout $_layout = null
	) {
	}

	/**
	 * Get caption
	 *
	 * @return string
	 */
	public function getCaption() {
		return $this->_caption;
	}

	/**
	 * Set caption
	 *
	 * @param string $caption
	 * @return PHPExcel_Chart_Title
	 */
	public function setCaption($caption = null) {
		$this->_caption = $caption;

		return $this;
	}

	/**
	 * Get Layout
	 *
	 * @return PHPExcel_Chart_Layout
	 */
	public function getLayout() {
		return $this->_layout;
	}
}
