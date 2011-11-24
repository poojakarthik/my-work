<?php

class Query_Result {
	const LOG_DEBUG	= true;

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
	
	private $_mResult 	= null;
	private $_aFields	= array();
	
	public function __construct($mResult) {
		$this->_mResult = $mResult;
	}
	
	public function __get($sProperty) {
		return $this->_mResult->$sProperty;
	}
	
	public function __set($sProperty, $mValue) {
		$this->_mResult->$sProperty = $mValue;
	}
	
	public function __call($sMethod, $aArgs) {
		$mResult = call_user_func_array(array($this->_mResult, $sMethod), $aArgs);
		switch ($sMethod) {
			case 'fetch_row':
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
				if (!$mResult) {
					return null;
				}
				
				$aResult	= array();
				$i			= 0;
				foreach ($mResult as $sField => $mValue) {
					$oField 			= $this->_getField($i);
					$aResult[$sField] 	= self::_getTypedValue($oField, $mValue);
					$i++;
				}
				return $aResult;
					
			default:
				return $mResult;
		}
	}
	
	private function _getField($iPosition) {
		if (!$this->_aFields[$iPosition]) {
			$this->_aFields[$iPosition] = $this->_mResult->fetch_field_direct($iPosition);
		}
		return $this->_aFields[$iPosition];
	}
	
	private static function _getTypedValue($oField, $sValue) {
		Log::getLog()->logIf(self::LOG_DEBUG, print_r($oField, true));

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