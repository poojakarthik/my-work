<?php
class MDB2_Driver_mysqli_Result {

	private $_oPDOStatement;
	private $_oPDOMySQLDriver;

	function __construct($oStatement, $oPDOMySQLDriver) {
		$this->_oPDOStatement = $oStatement;
		$this->_oPDOMySQLDriver = $oPDOMySQLDriver;
	}

	public function fetchOne($mColnum=0, $iRownum=null) {
		if ($mColnum !== 0) {
			throw new Exception("Error in method fetchOne(), unsupported/unimplemented parameter mColnum={$mColnum}");
		}
		if (isset($iRownum)) {
			throw new Exception("Error in method fetchOne(), unsupported/unimplemented parameter iRownum={$iRownum}");
		}
		return $this->_oPDOStatement->fetchColumn();
	}

	public function fetchRow($iFetchMode=MDB2_FETCHMODE_DEFAULT, $iRow=null) {
		$aResult = $this->_oPDOStatement->fetch($this->_oPDOMySQLDriver->getPDOFetchMode($iFetchMode));
		return $this->_applyPortabilityOptions($aResult);
	}

	public function fetchAll($iFetchMode=MDB2_FETCHMODE_DEFAULT, $iRow=null) {
		$aResult = $this->_oPDOStatement->fetchAll($this->_oPDOMySQLDriver->getPDOFetchMode($iFetchMode));
		foreach ($aResult as $iRowKey=>$aRow) {
			$aResult[$iRowKey] = $this->_applyPortabilityOptions($aRow);
		}
		return $aResult;
	}

	private function _applyPortabilityOptions($mData) {
		// MDB2_PORTABILITY_RTRIM
		if ($this->_isPortabilityOptionSet(MDB2_PORTABILITY_RTRIM)) {
			$mData = $this->_iterateDataAndApplyFunctionToValues('rtrim', $mData);
		}
		// MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES
		if ($this->_isPortabilityOptionSet(MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES)) {
			$mData = $this->_iterateDataAndApplyFunctionToKeys(
				function($sKey) {
					return preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $sKey);
				}, 
				$mData
			);
		}
		return $mData;
	}

	private function _iterateDataAndApplyFunctionToKeys($fnApply, $mData) {
		if (is_object($mData)) {
			$mNewData = new StdClass();
			foreach (get_object_vars($mData) as $sKey => $mValue) {
				$sNewKey = call_user_func($fnApply, $sKey);
				$mNewData->$sNewKey = $mValue;
			}
		} elseif(is_array($mData)) {
			$mNewData = array();
			foreach ($mData as $sKey => $mValue) {
				$sNewKey = call_user_func($fnApply, $sKey);
				$mNewData[$sNewKey] = $mValue;
			}
		} else {
			throw new Exception("Error in method _iterateDataAndApplyFunctionToKeys(), unexpected data type: " . gettype($mData));
		}
		return $mNewData;
	}

	private function _iterateDataAndApplyFunctionToValues($fnApply, $mData) {
		if (is_object($mData)) {
			$mNewData = new StdClass();
			foreach (get_object_vars($mData) as $sKey => $mValue) {
				if (is_string($mValue)) {
					$mNewData->$sKey = call_user_func($fnApply, $mValue);
				}
			}
		} elseif(is_array($mData)) {
			$mNewData = array();
			foreach ($mData as $sKey => $mValue) {
				if (is_string($mValue)) {
					$mNewData[$sKey] = call_user_func($fnApply, $mValue);
				}
			}
		} else {
			throw new Exception("Error in method _iterateDataAndApplyFunctionToValues(), unexpected data type: " . gettype($mData));
		}
		return $mNewData;
	}

	private function _isPortabilityOptionSet($iPortabilityConstant) {
		return (isset($this->_oPDOMySQLDriver->aPortabilityOptions['portability']) && $this->_oPDOMySQLDriver->aPortabilityOptions['portability'] & $iPortabilityConstant);
	}

}