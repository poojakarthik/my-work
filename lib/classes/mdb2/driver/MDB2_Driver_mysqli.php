<?php
class MDB2_Driver_mysqli extends MDB2_Driver {

	private $_oPDO;
	public $aPortabilityOptions;

	function __construct($aDSN, $aOptions=false) {
		$this->_oPDO = new PDO("mysql:dbname={$aDSN['database']};host={$aDSN['hostspec']}", $aDSN['username'], $aDSN['password']);
		$this->_oPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->setFetchMode(MDB2_FETCHMODE_DEFAULT);
		$this->aPortabilityOptions = $aOptions;

		$aValidationErrors = $this->_validatePortabilityOptions();
		if (!empty($aValidationErrors)) {
			throw new Exception("Error in method __construct(), unsupported portability option/s: " . implode(", ", $aValidationErrors));
		}
	}

	// TOOD
	/*
	MDB2: http://pear.php.net/package/MDB2/docs/latest/MDB2/MDB2_Driver_Common.html#methodquote
	
	Convert a text value into a DBMS specific format that is suitable to compose query statements.

	Return: text string that represents the given argument value in a DBMS specific format.
	Access: public

	Parameters:
	string  	$value  	—	  text string value that is intended to be converted.
	string  	$type  	—	  type to which the value should be converted to
	bool  	$quote  	—	  quote
	bool  	$escape_wildcards  	—	  escape wildcards
	*/
	public function quote($sValue, $sType=null, $bQuote=true, $bEscapeWildcards=false) {
		throw new Exception("Error in method quote(), not implemented.");
		// PDO's quote does not work the same...
		//return $this->_oPDO->quote($sValue, $iParameterType=PDO::PARAM_STR);
	}

	public function listTables() {
		try {
			$oStatement = $this->_oPDO->query("SHOW TABLES");
			$aTables = $oStatement->fetchAll();
			$aResult = array();
			for ($i=0; $i<count($aTables); $i++) {
				$aResult[] = array_shift(array_slice($aTables[$i], 0, 1));
			}
			return $aResult;
		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	public function listTableFields($sTable) {
		try {
			$oStatement = $this->_oPDO->query("SHOW COLUMNS FROM {$sTable}");
			$aTableFields = $oStatement->fetchAll();			
			$aResult = array();
			foreach($aTableFields as $aTable) {
				$aResult[] = $aTable['Field'];
			}
			return $aResult;
		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	public function getTableFieldDefinition($sTable, $sFieldName) {
		try {
			$oStatement = $this->_oPDO->query("DESCRIBE {$sTable} {$sFieldName}");
			$aDefinition = $oStatement->fetchAll(MDB2_FETCHMODE_ASSOC);
			preg_match("/\((\d+)\)/", $aDefinition[0]['Type'], $aMatches);
			$iLength = (isset($aMatches[1])) ? (int)$aMatches[1] : null;
			return array(array(
				'notnull' => ($aDefinition[0]['Null'] === 'NO') ? true : false,
				'nativetype' => self::_getNativeDataType($aDefinition[0]['Type']),
				'default' => $aDefinition[0]['Default'],
				'type' => $aDefinition[0]['Type'],
				'mdb2type' => self::_getMDB2DataType($aDefinition[0]['Type']),
				'length' => $iLength
			));
		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	public function beginTransaction($sSavepoint=null) {
		if($sSavepoint) {
			throw new Exception("Error in method beginTransaction(), unimplemented property: \$sSavepoint");
		} else {
			if($this->inTransaction()) {
				return MDB2_OK;
			} else {
				if($this->_oPDO->beginTransaction()) {
					return MDB2_OK;
				} else {
					return new MDB2_Error();
				}
			}
		}
	}

	public function inTransaction($bIgnoreNested=false) {
		if($bIgnoreNested) {
			throw new Exception("Error in method inTransaction(), unimplemented property: \$bIgnoreNested");
		}
		if($this->_oPDO->inTransaction()) {
			return true;
		}
		return false;
	}

	public function commit($sSavepoint=null) {
		if($sSavepoint) {
			throw new Exception("Error in method commit(), unimplemented property: \$sSavepoint");
		} else {
			if($this->inTransaction()) {
				if($this->_oPDO->commit()) {
					return MDB2_OK;
				} else {
					return new MDB2_Error();
				}
			} else {
				return new MDB2_Error();
			}
		}
	}

	public function rollback($sSavepoint=null) {
		if($sSavepoint) {
			throw new Exception("Error in method rollback(), unimplemented property: \$sSavepoint");
		} else {
			if($this->_oPDO->rollBack()) {
				return MDB2_OK;
			} else {
				return new MDB2_Error();
			}
		}
	}

	public function setFetchMode($iFetchMode, $sObjectClass='stdClass') {
		if ($sObjectClass !== 'stdClass') {
			throw new Exception("Error in method setFetchMode(), setting the object class is not supported: " . var_export($sObjectClass, true));
		}
		if (!is_numeric($iFetchMode)) {
			throw new Exception("Error in method setFetchMode(), unsupported Fetch Mode requested: " . var_export($iFetchMode, true));
		}
		$this->setPDOFetchMode($iFetchMode);
		$this->_oPDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->getPDOFetchMode($iFetchMode));
	}

	public function exec($sQuery) {
		try {
			return $this->_oPDO->exec($sQuery);
		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	public function query($sQuery, $mTypes=null, $mResultClass=true, $mResultWrapClass=true) {
		try {
			if ($mResultClass !== true) {
				throw new Exception('Error in method query(), unimplemented/unknown result class: ' . var_export($mResultClass, true));
			}
			if ($mResultWrapClass !== true) {
				throw new Exception('Error in method query(), unimplemented/unknown class to wrap results: ' . var_export($mResultWrapClass, true));
			}
			if ($mTypes !== null) {
				throw new Exception('Error in method query(), unimplemented/unknown column types: ' . var_export($mTypes, true));
			}
			return new MDB2_Driver_mysqli_Result($this->_oPDO->query($sQuery), $this);

		} catch (PDOException $oException) {
			return MDB2_Error::fromPDOException($oException);
		}
	}

	public function numRows() {
		if($this->_oPDO->rowCount()) {
			return $this->_oPDO->rowCount();
		} else {
			return new MDB2_Error();
		}
	}

	private function _validatePortabilityOptions() {
		$aErrors = array();
		// Not allowed options.
		if ($this->_isPortabilityOptionSet(MDB2_PORTABILITY_FIX_CASE)) {
			$aErrors[] = 'MDB2_PORTABILITY_FIX_CASE';
		}
		if ($this->_isPortabilityOptionSet(MDB2_PORTABILITY_EMPTY_TO_NULL)) {
			$aErrors[] = 'MDB2_PORTABILITY_EMPTY_TO_NULL';
		}
		return $aErrors;
	}

	private function _isPortabilityOptionSet($iPortabilityConstant) {
		return (isset($this->aPortabilityOptions['portability']) && $this->aPortabilityOptions['portability'] & $iPortabilityConstant);
	}
	// Method inspired by MDB2.
	private static function _getNativeDataType($sDatatype) {
		return preg_replace('/^([a-z]+)[^a-z].*/i', '\\1', $sDatatype);
	}

	// Method derived from MDB2.
	private static function _getMDB2DataType($sDatatype) {		
		$sDatatype = strtolower($sDatatype);
		$sDatatype = strtok($sDatatype, '(), ');
		if ($sDatatype == 'national') {
			$sDatatype = strtok('(), ');
		}

		switch ($sDatatype) {
			case 'tinyint':
				$sMDB2Datatype = 'integer';
				break;
			case 'smallint':
				$sMDB2Datatype = 'integer';
				break;
			case 'mediumint':
				$sMDB2Datatype = 'integer';
				break;
			case 'int':
			case 'integer':
				$sMDB2Datatype = 'integer';
				break;
			case 'bigint':
				$sMDB2Datatype = 'integer';
				break;
			case 'tinytext':
			case 'mediumtext':
			case 'longtext':
			case 'text':
			case 'varchar':
			case 'string':
			case 'char':
				$sMDB2Datatype = 'text';
				break;
			case 'enum':
				$sMDB2Datatype = 'text';
			case 'set':
				$sMDB2Datatype = 'text';
				break;
			case 'date':
				$sMDB2Datatype = 'date';
				break;
			case 'datetime':
			case 'timestamp':
				$sMDB2Datatype = 'timestamp';
				break;
			case 'time':
				$sMDB2Datatype = 'time';
				break;
			case 'float':
			case 'double':
			case 'real':
				$sMDB2Datatype = 'float';
				break;
			case 'unknown':
			case 'decimal':
			case 'numeric':
				$sMDB2Datatype = 'decimal';
				break;
			case 'tinyblob':
			case 'mediumblob':
			case 'longblob':
			case 'blob':
				$sMDB2Datatype = 'blob';
				break;
			case 'binary':
			case 'varbinary':
				$sMDB2Datatype = 'blob';
				break;
			case 'year':
				$sMDB2Datatype = 'integer';
				$sMDB2Datatype = 'date';
				break;
			default:
				throw new Exception("Error in method _getMDB2DataType(), unknown database attribute type: " . var_export($sDatatype, true));
		}

		return $sMDB2Datatype;
	}

}