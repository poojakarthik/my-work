<?php
class DBList extends DBListBase {
	public $_objWhere;
	public $Where;
	public $_intLimitStart = null;
	public $_intLimitCount = null;
	//public $_intMode = DBO_RETURN;
	public $_strIdColumn = 'Id';
	public $_strUseIndex = null;
	public $_strOrderBy = null;
	public $_arrColumns = array();
	public $_strTable = '';
	public $_strName = '';
	public $_arrValid = array();
	public $_intStatus = 0;
	public $_arrDefine = array();
	public $_db = null;
	
	private $_intCount = 0;
	
	function __construct($strName, $strTable=null, $mixColumns=null, $strWhere=null, $arrWhere=null, $intLimitStart=null, $intLimitCount=null) {
		// Parent Constructor
		parent::__construct();
		
		// set name
		$this->_strName = $strName;
		
		// get config
		$this->_arrDefine = Config()->Get('Dbl', $strName);  // !!!!!this 'Dbl' option is not yet implemented in the Config class
		
		// set table
		if ($strTable) {
			// use the table from parameters
			$this->_strTable = $strTable;
		} elseif ($this->_arrDefine['Table']) {
			// use the table from the definition
			$this->_strTable = $this->_arrDefine['Table'];
		} else {
			// as a last resort use the dbo name as the table name
			$this->_strTable = $strName;
		}
		
		// set columns
		$this->SetColumns($mixColumns);
		
		// set ID column name
		//TODO!!!! look harder to find this
		if ($this->_arrDefine['IdColumn']) {
			$this->_strIdColumn = $this->_arrDefine['IdColumn'];
		}
		
		// set limit
		if (!is_null($intLimitStart)) {
			$this->_intLimitStart = $intLimitStart;
		} elseif ($this->_arrDefine['LimitStart']) {
			$this->_intLimitStart = $this->_arrDefine['LimitStart'];
		} else {
			$this->_intLimitStart = null;
		}
		
		if (!is_null($intLimitCount)) {
			$this->_intLimitCount = $intLimitCount;
		} elseif ($this->_arrDefine['LimitCount']) {
			$this->_intLimitCount = $this->_arrDefine['LimitCount'];
		} else {
			$this->_intLimitCount = null;
		}
		
		// set USE INDEX
		$this->_strUseIndex = $this->_arrDefine['UseIndex'];
		
		// set ORDER BY
		$this->_strOrderBy = $this->_arrDefine['OrderBy'];
		
		// set up where object
		if (!is_null($strWhere)) {
			$this->_objWhere = new DBWhere($strWhere, $arrWhere);
		} else {
			$this->_objWhere = new DBWhere($this->_arrDefine['Where'], $this->_arrDefine['WhereData']);
		}
		
		// set up a public ref to the where object
		$this->Where = $this->_objWhere;
	}
	
	function Load($strWhere=null, $arrWhere=null, $intLimitCount=null, $intLimitStart=null) {
		// if WHERE parameters were passed then use them
		if ($strWhere || $arrWhere) {
			$this->_objWhere->Set($strWhere, $arrWhere);
		} elseif (!$this->_objWhere->GetString()) {
			// WHERE parameters have not been passed and a WHERE clause has not been predefined for this list so use the passed parameters
			
			//FIXME! The below line will currently not do anything because to get to this stage, both $strWhere and $arrWhere are equal to null
			//and DBWhere->Set does not do anything with a parameter if it is null
			$this->_objWhere->Set($strWhere, $arrWhere);
		}
		
		
		// setup limit, if one has been specified
		if ($intLimitCount || $intLimitStart) {
			$this->SetLimit($intLimitCount, $intLimitStart);
		}
		
		// empty records
		$this->EmptyRecords();
		
		if ($arrResult = $this->Select()) {
			// load results into data objects
			foreach($arrResult AS $arrRow) {
				$this->AddRecord($arrRow);
			}
			return true;
		} else {
			return false;
		}
		
	}
	
	function SetLimit($intLimitCount=null, $intLimitStart=null) {
		if (!is_null($intLimitStart)) {
			$this->_intLimitStart = $intLimitStart;
		}
		
		if (!is_null($intLimitCount)) {
			$this->_intLimitCount = $intLimitCount;
		}
	}
	
	function Select() {
		// select the record
		if ($arrResult = parent::Select($this->_strTable, $this->_arrColumns, $this->_objWhere, $this->_intLimitStart, $this->_intLimitCount, $this->_strOrderBy, $this->_strUseIndex)) {
			return $arrResult;
		} else {
			return false;
		}
	}
	
	function AddRecord($arrRecord) {
		if (is_array($arrRecord)) {
			// count++
			$this->_intCount++;
			
			// create object with count key
			$this->_arrDataArray[$this->_intCount] = new DBObject($this->_strName, $this->_strTable, $this->_arrColumns);

			// load data into object
			$this->_arrDataArray[$this->_intCount]->LoadData($arrRecord);
				
			// return key
			return $this->_intCount;
		} else {
			return false;
		}
	}

	function EmptyRecords() {
		// empty data array
		$this->_arrDataArray = array();
		
		// reset counter
		$this->_intCount = 0;
		
		return true;
	}
	
	function UseIndex($strProperty) {
		$this->_strUseIndex = $strProperty;
	}
	
	function OrderBy($strProperty) {
		$this->_strOrderBy = $strProperty;
	}
	
	function __set($strProperty, $mixValue) {
		return ($this->_objWhere->$strProperty = $mixValue);
	}
	
	function __get($strProperty) {
		return $this->_objWhere->$strProperty;

	}
	
	function Info() {
		$arrReturn = array();
		foreach ($this->_arrDataArray as $objDBObject) {
			// the index of $arrReturn is the value of the DB object's unique Id column
			// or should it just be a linear array?
			$arrReturn[$objDBObject->_arrProperties[$objDBObject->_strIdColumn]] = $objDBObject->Info();
		}
		
		return $arrReturn;
	}
	
	function ShowInfo($strTabs='') {
		$arrInfo = $this->Info();
		
		$strOutput = $this->_ShowInfo($arrInfo, $strTabs);

		if (!$strTabs) {
			Debug($strOutput);
		}
		return $strOutput;
	}
	
	private function _ShowInfo($mixData, $strTabs='') {
		if (is_array($mixData)) {
			foreach ($mixData as $mixKey=>$mixValue) {
				if (!is_array($mixValue)) {
					// $mixValue is not an array
					$strOutput .= $strTabs . $mixKey . " : " . $mixValue . "\n";
				} else {
					// $mixValue is an array so output its contents
					$strOutput .= $strTabs . $mixKey . "\n";
					$strOutput .= $this->_ShowInfo($mixValue, $strTabs."\t");
				}
			}
		} else {
			$strOutput = $mixData . "\n";
		}
		return $strOutput;
	}
	
	function SetColumns($mixColumns) {
		if (is_array($mixColumns)) {
			$this->_arrColumns = $mixColumns;
		} elseif ($mixColumns) {
			// convert column names into an array
			$mixColumns = str_replace(" ", "", $mixColumns);
			$arrColumns = explode(",", $mixColumns);
			$this->_arrColumns = $arrColumns;
		} elseif ($this->_arrDefine['Columns']) {
			$this->_arrColumns = $this->_arrDefine['Columns'];
		} else {
			//TODO!!!! get * column names for tables
			// This scenario is currently handled by DataAccessUI::Select and DataAccessUI::SelectById
			// If either of these methods are called and $_arrColumns is empty, it selects all
			// columns from the table of the database
		}
		return $this->_arrColumns;
	}
	
	function GetColumns() {
		return $this->_arrColumns;
	}
	
	function SetTable($strTable) {
		return $this->_strTable = $strTable;
	}

	function RecordCount() {
		return $this->_intCount;
	}
}
