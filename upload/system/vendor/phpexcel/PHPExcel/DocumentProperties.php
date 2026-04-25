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
 * @category    PHPExcel
 * @package    PHPExcel
 * @copyright    Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    v1.8.1, released: 01-05-2015
 */

/**
 * PHPExcel_DocumentProperties
 *
 * @category    PHPExcel
 * @package        PHPExcel
 * @copyright    Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_DocumentProperties {
	/** constants */
	public const PROPERTY_TYPE_BOOLEAN = 'b';
	public const PROPERTY_TYPE_INTEGER = 'i';
	public const PROPERTY_TYPE_FLOAT = 'f';
	public const PROPERTY_TYPE_DATE = 'd';
	public const PROPERTY_TYPE_STRING = 's';
	public const PROPERTY_TYPE_UNKNOWN = 'u';

	/**
	 * Creator
	 *
	 * @var string
	 */
	private $_creator = 'Unknown Creator';

	/**
	 * LastModifiedBy
	 *
	 * @var string
	 */
	private $_lastModifiedBy;

	/**
	 * Created
	 *
	 * @var datetime
	 */
	private $_created;

	/**
	 * Modified
	 *
	 * @var datetime
	 */
	private $_modified;

	/**
	 * Title
	 *
	 * @var string
	 */
	private $_title = 'Untitled Spreadsheet';

	/**
	 * Description
	 *
	 * @var string
	 */
	private $_description = '';

	/**
	 * Subject
	 *
	 * @var string
	 */
	private $_subject = '';

	/**
	 * Keywords
	 *
	 * @var string
	 */
	private $_keywords = '';

	/**
	 * Category
	 *
	 * @var string
	 */
	private $_category = '';

	/**
	 * Manager
	 *
	 * @var string
	 */
	private $_manager = '';

	/**
	 * Company
	 *
	 * @var string
	 */
	private $_company = 'Microsoft Corporation';

	/**
	 * Custom Properties
	 *
	 * @var string
	 */
	private $_customProperties = [];

	/**
	 * Create a new PHPExcel_DocumentProperties
	 */
	public function __construct() {
		// Initialise values
		$this->_lastModifiedBy = $this->_creator;

		$this->_created = time();
		$this->_modified = time();
	}

	/**
	 * Get Creator
	 *
	 * @return string
	 */
	public function getCreator() {
		return $this->_creator;
	}

	/**
	 * Set Creator
	 *
	 * @param string $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setCreator($pValue = '') {
		$this->_creator = $pValue;

		return $this;
	}

	/**
	 * Get Last Modified By
	 *
	 * @return string
	 */
	public function getLastModifiedBy() {
		return $this->_lastModifiedBy;
	}

	/**
	 * Set Last Modified By
	 *
	 * @param string $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setLastModifiedBy($pValue = '') {
		$this->_lastModifiedBy = $pValue;

		return $this;
	}

	/**
	 * Get Created
	 *
	 * @return datetime
	 */
	public function getCreated() {
		return $this->_created;
	}

	/**
	 * Set Created
	 *
	 * @param datetime $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setCreated($pValue = null) {
		if ($pValue === null) {
			$pValue = time();
		} elseif (is_string($pValue)) {
			if (is_numeric($pValue)) {
				$pValue = intval($pValue);
			} else {
				$pValue = strtotime($pValue);
			}
		}

		$this->_created = $pValue;

		return $this;
	}

	/**
	 * Get Modified
	 *
	 * @return datetime
	 */
	public function getModified() {
		return $this->_modified;
	}

	/**
	 * Set Modified
	 *
	 * @param datetime $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setModified($pValue = null) {
		if ($pValue === null) {
			$pValue = time();
		} elseif (is_string($pValue)) {
			if (is_numeric($pValue)) {
				$pValue = intval($pValue);
			} else {
				$pValue = strtotime($pValue);
			}
		}

		$this->_modified = $pValue;

		return $this;
	}

	/**
	 * Get Title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->_title;
	}

	/**
	 * Set Title
	 *
	 * @param string $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setTitle($pValue = '') {
		$this->_title = $pValue;

		return $this;
	}

	/**
	 * Get Description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->_description;
	}

	/**
	 * Set Description
	 *
	 * @param string $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setDescription($pValue = '') {
		$this->_description = $pValue;

		return $this;
	}

	/**
	 * Get Subject
	 *
	 * @return string
	 */
	public function getSubject() {
		return $this->_subject;
	}

	/**
	 * Set Subject
	 *
	 * @param string $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setSubject($pValue = '') {
		$this->_subject = $pValue;

		return $this;
	}

	/**
	 * Get Keywords
	 *
	 * @return string
	 */
	public function getKeywords() {
		return $this->_keywords;
	}

	/**
	 * Set Keywords
	 *
	 * @param string $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setKeywords($pValue = '') {
		$this->_keywords = $pValue;

		return $this;
	}

	/**
	 * Get Category
	 *
	 * @return string
	 */
	public function getCategory() {
		return $this->_category;
	}

	/**
	 * Set Category
	 *
	 * @param string $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setCategory($pValue = '') {
		$this->_category = $pValue;

		return $this;
	}

	/**
	 * Get Company
	 *
	 * @return string
	 */
	public function getCompany() {
		return $this->_company;
	}

	/**
	 * Set Company
	 *
	 * @param string $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setCompany($pValue = '') {
		$this->_company = $pValue;

		return $this;
	}

	/**
	 * Get Manager
	 *
	 * @return string
	 */
	public function getManager() {
		return $this->_manager;
	}

	/**
	 * Set Manager
	 *
	 * @param string $pValue
	 * @return PHPExcel_DocumentProperties
	 */
	public function setManager($pValue = '') {
		$this->_manager = $pValue;

		return $this;
	}

	/**
	 * Get a List of Custom Property Names
	 *
	 * @return array of string
	 */
	public function getCustomProperties() {
		return array_keys($this->_customProperties);
	}

	/**
	 * Check if a Custom Property is defined
	 *
	 * @param string $propertyName
	 * @return boolean
	 */
	public function isCustomPropertySet($propertyName) {
		return isset($this->_customProperties[$propertyName]);
	}

	/**
	 * Get a Custom Property Value
	 *
	 * @param string $propertyName
	 * @return string
	 */
	public function getCustomPropertyValue($propertyName) {
		if (isset($this->_customProperties[$propertyName])) {
			return $this->_customProperties[$propertyName]['value'];
		}
	}

	/**
	 * Get a Custom Property Type
	 *
	 * @param string $propertyName
	 * @return string
	 */
	public function getCustomPropertyType($propertyName) {
		if (isset($this->_customProperties[$propertyName])) {
			return $this->_customProperties[$propertyName]['type'];
		}
	}

	/**
	 * Set a Custom Property
	 *
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @param string $propertyType
	 *		'i' : Integer
	 *		'f' : Floating Point
	 *		's' : String
	 *		'd' : Date/Time
	 *		'b' : Boolean
	 * @return PHPExcel_DocumentProperties
	 */
	public function setCustomProperty($propertyName, $propertyValue = '', $propertyType = null) {
		if (($propertyType === null) || (!in_array($propertyType, [self::PROPERTY_TYPE_INTEGER, self::PROPERTY_TYPE_FLOAT, self::PROPERTY_TYPE_STRING, self::PROPERTY_TYPE_DATE, self::PROPERTY_TYPE_BOOLEAN]))) {
			if ($propertyValue === null) {
				$propertyType = self::PROPERTY_TYPE_STRING;
			} elseif (is_float($propertyValue)) {
				$propertyType = self::PROPERTY_TYPE_FLOAT;
			} elseif (is_int($propertyValue)) {
				$propertyType = self::PROPERTY_TYPE_INTEGER;
			} elseif (is_bool($propertyValue)) {
				$propertyType = self::PROPERTY_TYPE_BOOLEAN;
			} else {
				$propertyType = self::PROPERTY_TYPE_STRING;
			}
		}

		$this->_customProperties[$propertyName] = ['value' => $propertyValue, 'type' => $propertyType];

		return $this;
	}

	/**
	 * Implement PHP __clone to create a deep clone, not just a shallow copy.
	 */
	public function __clone() {
		$vars = get_object_vars($this);

		foreach ($vars as $key => $value) {
			if (is_object($value)) {
				$this->$key = clone $value;
			} else {
				$this->$key = $value;
			}
		}
	}

	public static function convertProperty($propertyValue, $propertyType) {
		return match ($propertyType) {
			//    Empty
			'empty' => '',
			//    Null
			'null' => null,
			//    Integer
			'i1', 'i2', 'i4', 'i8', 'int' => (int)$propertyValue,
			//    Unsigned Integer
			'ui1', 'ui2', 'ui4', 'ui8', 'uint' => abs((int)$propertyValue),
			//    Decimal
			'r4', 'r8', 'decimal' => (float) $propertyValue,
			//    Basic String
			'lpstr', 'lpwstr', 'bstr' => $propertyValue,
			//    File Time
			'date', 'filetime' => strtotime((string) $propertyValue),
			//    Boolean
			'bool' => ($propertyValue == 'true') ? true : false,
			//    Clipboard Data
			'cy', 'error', 'vector', 'array', 'blob', 'oblob', 'stream', 'ostream', 'storage', 'ostorage', 'vstream', 'clsid', 'cf' => $propertyValue,
			default => $propertyValue,
		};
	}

	public static function convertPropertyType($propertyType) {
		return match ($propertyType) {
			//    Unsigned Integer
			'i1', 'i2', 'i4', 'i8', 'int', 'ui1', 'ui2', 'ui4', 'ui8', 'uint' => self::PROPERTY_TYPE_INTEGER,
			//    Decimal
			'r4', 'r8', 'decimal' => self::PROPERTY_TYPE_FLOAT,
			//    Basic String
			'empty', 'null', 'lpstr', 'lpwstr', 'bstr' => self::PROPERTY_TYPE_STRING,
			//    File Time
			'date', 'filetime' => self::PROPERTY_TYPE_DATE,
			//    Boolean
			'bool' => self::PROPERTY_TYPE_BOOLEAN,
			//    Clipboard Data
			'cy', 'error', 'vector', 'array', 'blob', 'oblob', 'stream', 'ostream', 'storage', 'ostorage', 'vstream', 'clsid', 'cf' => self::PROPERTY_TYPE_UNKNOWN,
			default => self::PROPERTY_TYPE_UNKNOWN,
		};
	}
}
