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
 * @package    PHPExcel_Calculation
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    v1.8.1, released: 01-05-2015
 */

/*
PARTLY BASED ON:
	Copyright (c) 2007 E. W. Bachtal, Inc.

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software
	and associated documentation files (the "Software"), to deal in the Software without restriction,
	including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
	subject to the following conditions:

	  The above copyright notice and this permission notice shall be included in all copies or substantial
	  portions of the Software.

	The software is provided "as is", without warranty of any kind, express or implied, including but not
	limited to the warranties of merchantability, fitness for a particular purpose and noninfringement. In
	no event shall the authors or copyright holders be liable for any claim, damages or other liability,
	whether in an action of contract, tort or otherwise, arising from, out of or in connection with the
	software or the use or other dealings in the software.

	http://ewbi.blogs.com/develops/2007/03/excel_formula_p.html
	http://ewbi.blogs.com/develops/2004/12/excel_formula_p.html
*/

/**
 * PHPExcel_Calculation_FormulaToken
 *
 * @category   PHPExcel
 * @package    PHPExcel_Calculation
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_Calculation_FormulaToken {
	/* Token types */
	public const TOKEN_TYPE_NOOP = 'Noop';
	public const TOKEN_TYPE_OPERAND = 'Operand';
	public const TOKEN_TYPE_FUNCTION = 'Function';
	public const TOKEN_TYPE_SUBEXPRESSION = 'Subexpression';
	public const TOKEN_TYPE_ARGUMENT = 'Argument';
	public const TOKEN_TYPE_OPERATORPREFIX = 'OperatorPrefix';
	public const TOKEN_TYPE_OPERATORINFIX = 'OperatorInfix';
	public const TOKEN_TYPE_OPERATORPOSTFIX = 'OperatorPostfix';
	public const TOKEN_TYPE_WHITESPACE = 'Whitespace';
	public const TOKEN_TYPE_UNKNOWN = 'Unknown';

	/* Token subtypes */
	public const TOKEN_SUBTYPE_NOTHING = 'Nothing';
	public const TOKEN_SUBTYPE_START = 'Start';
	public const TOKEN_SUBTYPE_STOP = 'Stop';
	public const TOKEN_SUBTYPE_TEXT = 'Text';
	public const TOKEN_SUBTYPE_NUMBER = 'Number';
	public const TOKEN_SUBTYPE_LOGICAL = 'Logical';
	public const TOKEN_SUBTYPE_ERROR = 'Error';
	public const TOKEN_SUBTYPE_RANGE = 'Range';
	public const TOKEN_SUBTYPE_MATH = 'Math';
	public const TOKEN_SUBTYPE_CONCATENATION = 'Concatenation';
	public const TOKEN_SUBTYPE_INTERSECTION = 'Intersection';
	public const TOKEN_SUBTYPE_UNION = 'Union';

	/**
	 * Create a new PHPExcel_Calculation_FormulaToken
	 *
	 * @param string $_value
	 * @param string $_tokenType Token type (represented by TOKEN_TYPE_*)
	 * @param string $_tokenSubType Token Subtype (represented by TOKEN_SUBTYPE_*)
	 */
	public function __construct(
		/**
		 * Value
		 */
		private $_value,
		/**
		 * Token Type (represented by TOKEN_TYPE_*)
		 */
		private $_tokenType = PHPExcel_Calculation_FormulaToken::TOKEN_TYPE_UNKNOWN,
		/**
		 * Token SubType (represented by TOKEN_SUBTYPE_*)
		 */
		private $_tokenSubType = PHPExcel_Calculation_FormulaToken::TOKEN_SUBTYPE_NOTHING
	) {
	}

	/**
	 * Get Value
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->_value;
	}

	/**
	 * Set Value
	 *
	 * @param string	$value
	 */
	public function setValue($value): void {
		$this->_value = $value;
	}

	/**
	 * Get Token Type (represented by TOKEN_TYPE_*)
	 *
	 * @return string
	 */
	public function getTokenType() {
		return $this->_tokenType;
	}

	/**
	 * Set Token Type
	 *
	 * @param string	$value
	 */
	public function setTokenType($value = PHPExcel_Calculation_FormulaToken::TOKEN_TYPE_UNKNOWN): void {
		$this->_tokenType = $value;
	}

	/**
	 * Get Token SubType (represented by TOKEN_SUBTYPE_*)
	 *
	 * @return string
	 */
	public function getTokenSubType() {
		return $this->_tokenSubType;
	}

	/**
	 * Set Token SubType
	 *
	 * @param string	$value
	 */
	public function setTokenSubType($value = PHPExcel_Calculation_FormulaToken::TOKEN_SUBTYPE_NOTHING): void {
		$this->_tokenSubType = $value;
	}
}
