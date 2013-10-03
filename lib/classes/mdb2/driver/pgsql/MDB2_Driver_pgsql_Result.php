<?php
class MDB2_Driver_pgsql_Result {

	private $_oPDOStatement;
	private $_oPDOPgSQLDriver;

	function __construct($oStatement, $oPDOPgSQLDriver) {
		$this->_oPDOStatement = $oStatement;
		$this->_oPDOPgSQLDriver = $oPDOPgSQLDriver;
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

    function fetchCol($colnum = 0) {
        $column = array();
        $fetchmode = is_numeric($colnum) ? MDB2_FETCHMODE_ORDERED : MDB2_FETCHMODE_ASSOC;
        $row = $this->fetchRow($fetchmode);
        if (is_array($row)) {
            if (!array_key_exists($colnum, $row)) {
                return MDB2::raiseError(MDB2_ERROR_TRUNCATED, null, null,
                    'column is not defined in the result set: '.$colnum, __FUNCTION__);
            }
            do {
                $column[] = $row[$colnum];
            } while (is_array($row = $this->fetchRow($fetchmode)));
        }
        if (MDB2::isError($row)) {
            return $row;
        }
        return $column;
    }

	public function fetchRow($iFetchMode=MDB2_FETCHMODE_DEFAULT, $iRow=null) {
		$aResult = $this->_oPDOStatement->fetch($this->_oPDOPgSQLDriver->getPDOFetchMode($iFetchMode));
		//return $aResult;
		return ($aResult) ? $this->_applyPortabilityOptions($aResult) : false;
	}

	public function fetchAll($iFetchMode=MDB2_FETCHMODE_DEFAULT, $iRow=null) {
		$aResult = $this->_oPDOStatement->fetchAll($this->_oPDOPgSQLDriver->getPDOFetchMode($iFetchMode));
		foreach ($aResult as $iRowKey=>$aRow) {
			$aResult[$iRowKey] = $this->_applyPortabilityOptions($aRow);
		}
		return $aResult;
	}

	public static function fixAssocFieldNames($sKey) {
		return preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $sKey);
	}

	private function _applyPortabilityOptions($mData) {
		// MDB2_PORTABILITY_RTRIM
		if ($this->_isPortabilityOptionSet(MDB2_PORTABILITY_RTRIM)) {
			$mData = $this->_iterateDataAndApplyFunctionToValues('rtrim', $mData);
		}
		// MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES
		if ($this->_isPortabilityOptionSet(MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES)) {
			$fnApply = array('MDB2_Driver_pgsql_Result', 'fixAssocFieldNames');
			$mData = $this->_iterateDataAndApplyFunctionToKeys(
				/*
				function($sKey) {
					return preg_replace('/^(?:.*\.)?([^.]+)$/', '\\1', $sKey);
				},
				*/
				$fnApply,
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
				} else {
					$mNewData->$sKey = $mValue;
				}
			}
		} elseif(is_array($mData)) {
			$mNewData = array();
			foreach ($mData as $sKey => $mValue) {
				if (is_string($mValue)) {
					$mNewData[$sKey] = call_user_func($fnApply, $mValue);
				} else {
					$mNewData[$sKey] = $mValue;
				}
			}
		} else {
			throw new Exception("Error in method _iterateDataAndApplyFunctionToValues(), unexpected data type: " . gettype($mData));
		}
		return $mNewData;
	}

	private function _isPortabilityOptionSet($iPortabilityConstant) {
		return (isset($this->_oPDOPgSQLDriver->aPortabilityOptions['portability']) && $this->_oPDOPgSQLDriver->aPortabilityOptions['portability'] & $iPortabilityConstant);
	}

}