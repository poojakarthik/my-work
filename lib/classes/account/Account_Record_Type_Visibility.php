<?php

class Account_Record_Type_Visibility {

	protected static $cache = array();

	private	$_arrTidyNames	= array();
	private	$_arrProperties	= array();

	public function __construct($arrProperties=NULL, $bolLoadById=FALSE) {
		// Get list of columns from Data Model
		$arrTableDefine	= DataAccess::getDataAccess()->FetchTableDefine('account_record_type_visibility');
		foreach ($arrTableDefine['Column'] as $strName=>$arrColumn) {
			$this->_arrProperties[$strName]					= NULL;
			$this->_arrTidyNames[self::tidyName($strName)]	= $strName;
		}
		$this->_arrProperties[$arrTableDefine['Id']]				= NULL;
		$this->_arrTidyNames[self::tidyName($arrTableDefine['Id'])]	= $arrTableDefine['Id'];

		// Automatically load the Invoice using the passed Id
		$intId	= ($arrProperties['Id']) ? $arrProperties['Id'] : (($arrProperties['id']) ? $arrProperties['id'] : NULL);
		if ($bolLoadById && $intId) {
			$selById	= $this->_preparedStatement('selById');
			if ($selById->Execute(Array('Id' => $intId))) {
				$arrProperties	= $selById->Fetch();
			} elseif ($selById->Error()) {
				throw new Exception_Database("DB ERROR: ".$selById->Error());
			} else {
				throw new Exception(__CLASS__." with Id {$intId} does not exist!");
			}
		}

		// Set Properties
		if (is_array($arrProperties)) {
			foreach ($arrProperties as $strName=>$mixValue) {
				// Load from the Database
				$this->{$strName}	= $mixValue;
			}
		}
	}

	private static function getFor($where, $arrWhere, $bolAsArray=FALSE) {
		$selUsers = new StatementSelect(
			"account_record_type_visibility",
			self::getColumns(),
			$where);
		if (($outcome = $selUsers->Execute($arrWhere)) === FALSE) {
			throw new Exception_Database("Failed to check for existing account_record_type_visibility: " . $selUsers->Error());
		}
		if (!$outcome && !$bolAsArray) {
			return NULL;
		}

		$records = array();
		while ($props = $selUsers->Fetch()) {
			if (!array_key_exists($props['Id'], self::$cache)) {
				self::$cache[$props['Id']] = new Account($props);
			}
			$records[] = self::$cache[$props['Id']];
			if (!$bolAsArray) {
				return $records[0];
			}
		}
		return $records;
	}

	public static function getForId($id) {
		if (array_key_exists($id, self::$cache)) {
			return self::$cache[$id];
		}
		$oAccountRecordTypeVisibility = self::getFor("id = <Id>", array("Id" => $id));
		return $oAccountRecordTypeVisibility;
	}

	public static function getForAccountIdAndRecordTypeId($account_id, $record_type_id) {
		$oSelect	= self::_preparedStatement('selByAccountIdAndRecordTypeId');
		$oSelect->Execute(array("RecordTypeId" => $record_type_id, "AccountId" => $account_id));
		if ($aResult = $oSelect->Fetch()) {
			return new self($aResult);
		} else {
			return false;
		}
	}

	protected function getValuesToSave() {
		$arrColumns = self::getColumns();
		$arrValues = array();
		foreach($arrColumns as $strColumn) {
			if ($strColumn == 'id') {
				continue;
			}
			$arrValues[$strColumn] = $this->{$strColumn};
		}
		return $arrValues;
	}

	// Empties the cache
	public static function emptyCache() {
		self::$cache = array();
	}

	protected static function getColumns() {
		return array(
			'id',
			'account_id',
			'record_type_id',
			'is_visible'
		);
	}

	public function __get($strName) {
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		return (array_key_exists($strName, $this->_arrProperties)) ? $this->_arrProperties[$strName] : NULL;
	}

	public function __set($strName, $mxdValue) {
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;

		if (array_key_exists($strName, $this->_arrProperties)) {
			$mixOldValue					= $this->_arrProperties[$strName];
			$this->_arrProperties[$strName]	= $mxdValue;

			if ($mixOldValue !== $mxdValue) {
				$this->_saved = FALSE;
			}
		} else {
			$this->{$strName} = $mxdValue;
		}
	}

	private function tidyName($name) {
		if (preg_match("/^[A-Z]+$/", $name)) $name = strtolower($name);
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}

	//------------------------------------------------------------------------//
	// toArray()
	//------------------------------------------------------------------------//
	/**
	 * toArray()
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * @return	array										DB Record
	 *
	 * @method
	 */
	public function toArray() {
		return $this->_arrProperties;
	}

	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 *
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	private static function _preparedStatement($strStatement) {
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement])) {
			return $arrPreparedStatements[$strStatement];
		} else {
			switch ($strStatement) {
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("account_record_type_visibility", "*", "id = <Id>", NULL, 1);
					break;
				case 'selByAccountIdAndRecordTypeId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("account_record_type_visibility", "*", "record_type_id = <RecordTypeId> AND account_id = <AccountId>");
					break;

				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("account_record_type_visibility");
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("account_record_type_visibility");
					break;

				// UPDATES
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
