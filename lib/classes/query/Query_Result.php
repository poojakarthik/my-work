<?php

class Query_Result {
	const LOG_DEBUG	= false;

	const DATA_TYPE_TINYINT 	= 1;
	const DATA_TYPE_SMALLINT	= 2;
	const DATA_TYPE_INT 		= 3;
	const DATA_TYPE_FLOAT 		= 4;
	const DATA_TYPE_DOUBLE 		= 5;
	const DATA_TYPE_NULL		= 6;
	const DATA_TYPE_TIMESTAMP	= 7;
	const DATA_TYPE_BIGINT		= 8;
	const DATA_TYPE_MEDIUMINT	= 9;
	const DATA_TYPE_DATE		= 10;
	const DATA_TYPE_TIME		= 11;
	const DATA_TYPE_DATETIME	= 12;
	const DATA_TYPE_YEAR		= 13;
	const DATA_TYPE_BIT			= 16;
	const DATA_TYPE_DECIMAL		= 246;
	const DATA_TYPE_ENUM		= 247;
	const DATA_TYPE_SET			= 248;
	const DATA_TYPE_TINYBLOB	= 249;
	const DATA_TYPE_MEDIUMBLOB	= 250;
	const DATA_TYPE_LONGBLOB	= 251;
	const DATA_TYPE_TEXT		= 252;
	const DATA_TYPE_BLOB		= 252;
	const DATA_TYPE_VARCHAR		= 253;
	const DATA_TYPE_CHAR		= 254;

	private $_sQuery = null;
	private $_mResult = null;
	private $_aFields = array();

	public function __construct($sQuery, $mResult) {
		$this->_sQuery = $sQuery;
		$this->_mResult = $mResult;
	}

	public function getQuery() {
		return $this->_sQuery;
	}

	public function __get($sProperty) {
		return $this->_mResult->$sProperty;
	}

	public function __set($sProperty, $mValue) {
		$this->_mResult->$sProperty = $mValue;
	}

	public function __call($sMethod, $aArgs) {
		switch ($sMethod) {
			// fetch_row()
			case 'fetch_row':
				// We simply want to auto-cast our results
				$mResult = call_user_func_array(array($this->_mResult, 'fetch_row'), $aArgs);
				if (!$mResult) {
					return null;
				}

				$aResult = array();
				foreach ($mResult as $i => $mValue) {
					$oField 		= $this->_getField($i);
					$aResult[$i]	= self::_getTypedValue($oField, $mValue);
				}
				return $aResult;

			case 'fetch_assoc':
				// We want to auto-cast our results
				// Because results can have multiple fields with the same name, return values
				// from fetch_assoc() can have different column counts to fetch_row(),
				// so we will simulate fetch_assoc(), while calling fetch_row() under the hood.
				// As per http://au.php.net/manual/en/mysqli-result.fetch-assoc.php `Return Values`,
				// the last column with a given name takes precedence
				$mResult = call_user_func_array(array($this->_mResult, 'fetch_row'), $aArgs);
				if (!$mResult) {
					return null;
				}

				$aResult	= array();
				foreach ($mResult as $i => $mValue) {
					$oField 				= $this->_getField($i);
					$aResult[$oField->name]	= self::_getTypedValue($oField, $mValue);
				}
				/*// This method doesn't work when a query has been coded to expect multiple columns with the same name
				$i			= 0;
				foreach ($mResult as $sField => $mValue) {
					$oField 			= $this->_getField($i);
					$aResult[$sField] 	= self::_getTypedValue($oField, $mValue);
					$i++;
				}*/
				return $aResult;

			default:
				// Pass through
				return call_user_func_array(array($this->_mResult, $sMethod), $aArgs);
		}
	}

	private function _getField($iPosition) {
		if (!isset($this->_aFields[$iPosition]) || !$this->_aFields[$iPosition]) {
			$this->_aFields[$iPosition] = $this->_mResult->fetch_field_direct($iPosition);
		}
		return $this->_aFields[$iPosition];
	}

	private static function _getTypedValue($oField, $sValue) {
		//Log::getLog()->logIf(self::LOG_DEBUG, print_r($oField, true));

		$mValue 	= $sValue;
		$iFieldType	= $oField->type;
		switch ($iFieldType) {
			case self::DATA_TYPE_TINYINT:
			case self::DATA_TYPE_SMALLINT:
			case self::DATA_TYPE_INT:
			case self::DATA_TYPE_BIGINT:
			case self::DATA_TYPE_MEDIUMINT:
				// Integer
				$mValue = ($sValue === null ? null : (int)$sValue);
				Log::getLog()->logIf(self::LOG_DEBUG, "{$oField->name} = int => {$mValue}");
				break;
			case self::DATA_TYPE_FLOAT:
			case self::DATA_TYPE_DOUBLE:
			//case DATA_TYPE_DECIMAL: -- Removed because DECIMAL values aren't technically floating point numbers
				// Floating point
				$mValue = ($sValue === null ? null : (float)$sValue);
				Log::getLog()->logIf(self::LOG_DEBUG, "{$oField->name} = float => {$mValue}");
				break;
			case self::DATA_TYPE_NULL:
				$mValue = null;
				Log::getLog()->logIf(self::LOG_DEBUG, "{$oField->name} = null");
				break;
			default:
				Log::getLog()->logIf(self::LOG_DEBUG, "{$oField->name} NO CONVERSION = {$oField->type} => {$mValue}");
				break;
		}
		return $mValue;
	}
}

?>