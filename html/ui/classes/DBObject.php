<?php
class DBObject extends DBObjectBase {
	public $_objWhere;
	public $Where;
	public $_strIdColumn = 'Id';
	public $_arrColumns = array();
	public $_strTable = '';
	public $_strName = '';
	public $_arrResult = array();
	public $_arrValid = array();
	public $_bolValid = null;
	public $_arrProperties = array();
	public $_intStatus = 0;
	public $_arrDefine = array();
	public $_intContext = 0;
	
	function __construct($strName, $strTable=null, $mixColumns=null) {
		//_arrDefine does not currently contain 'Table' or 'Columns' or 'IdColumn'

		// Parent Constructor
		parent::__construct();
		
		// set name
		$this->_strName = $strName;
		
		// get config
		$this->_arrDefine = Config()->Get('Dbo', $strName);
		
		// set table
		if ($strTable) {
			// use the table from parameters
			$this->_strTable = $strTable;
		} elseif ($this->_arrDefine !== null && array_key_exists('Table', $this->_arrDefine) && $this->_arrDefine['Table']) {
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
		if ($this->_arrDefine != null && array_key_exists('IdColumn', $this->_arrDefine) && $this->_arrDefine['IdColumn']) {
			$this->_strIdColumn = $this->_arrDefine['IdColumn'];
		}
		
		// set up where object
		$this->_objWhere = new DBWhere();
		
		// set up a public ref to the where object
		$this->Where = $this->_objWhere;
		
		// validate the object (considering nothing has been actually loaded into the object
		// this should just set $_bolValid to true
		$this->Validate();
	}
	
	function __call($strName, $arrArguments) {
		return true;
	}
	
	function __get($strProperty) {
		return PropertyToken()->_Property($this, $strProperty);
	}

	function __set($strProperty, $mixValue) {
		//$objToken = PropertyToken()->_Property($this, $strProperty);
		return ($this->_arrProperties[$strProperty] = $mixValue);
	}

	function AddProperty($strProperty, $mixValue, $intContext=CONTEXT_DEFAULT) {
		// store property
		$this->_arrProperties[$strProperty] = $mixValue;
		
		// validate property
		$this->ValidateProperty($strProperty, $intContext);
	}
	
	function ValidateProperty($strProperty, $intContext=CONTEXT_DEFAULT) {
		// find validation rule
		$strValidationRule = $this->_arrDefine[$strProperty][$intContext]['ValidationRule'];
		
		// do validation
		if ($strValidationRule) { 
			// Check if the Property is optional.  If a property is optional, 
			// then you only perform the validation rule, if the value doesn't equate to an empty string
			$strValidationRule = trim($strValidationRule);
			
			if (strtolower(substr($strValidationRule, 0, 9)) == "optional:") {
				// The property is optional
				if ($this->_arrProperties[$strProperty] === "" || $this->_arrProperties[$strProperty] === null) {
					// A value for the property has not been suplied therefore it has passed validation
					$this->_arrValid[$strProperty] = true;
					return true;
				}
				
				// A value for the property has been suplied so perform the validation
				$strValidationRule = trim(substr($strValidationRule, 9));
			}
		
			if (!$this->_arrValid[$strProperty] = Validate($strValidationRule, $this->_arrProperties[$strProperty])) {
				$this->_bolValid = false;
			}
			return $this->_arrValid[$strProperty];
		} else {
			// no validation rule
			return true;
		}
	}

	function Validate($intContext=CONTEXT_DEFAULT) {
		$this->_bolValid = true;
		$this->_arrValid = array();
		foreach ($this->_arrProperties AS $strProperty=>$mixValue) {
			// Validate Property
			if (!$this->_arrValid[$strProperty] = $this->ValidateProperty($strProperty, $intContext)) {
				// Invalid
				$this->_bolValid = false;
			}
		}
		return $this->_bolValid;
	}
	
	function IsValid() {
		foreach($this->_arrValid as $bolValid) {
			if ($bolValid === false) {
				return $this->_bolValid = false;
			}
		}
		return $this->_bolValid;
	}
	
	function IsInvalid() {
		if ($this->IsValid() === false) {
			return true;
		}
		return false;
	}
	
	// checks validation on the entire object and also sets the object to valid
	function SetValid() {
		$this->_bolValid = true;
		return $this->IsValid();
	}
	
	function SetToInvalid() {
		$this->_bolValid = false;
	}
	
	function Clean() {
		$this->_objWhere->Clean();
		$this->SetColumns();
		$this->_arrProperties = array();
		$this->_arrResult = array();
		$this->_arrValid = array();
		$this->_bolValid = null;
		$this->_intStatus = STATUS_CLEANED;
	}
	
	function Load($intId = null, $strType='LoadData') {
		// get data
		if ($this->_objWhere->GetString()) {
			// WHERE clause has been declared so use it, but only retrieve one record maximum
			$arrResults = $this->Select($this->_strTable, $this->_arrColumns, $this->_objWhere, 0, 1);
			
			// $this->Select() returns an array of records, even though there will only ever be 1 record at the most
			$arrResult = $arrResults[0];
		} else {
			// WHERE clause has not been defined so retrieve the record based on the value of the Id property

			// Set Id
			$intId = (int)$intId;
			if (!$intId) {
				$intId = (int)$this->_arrProperties[$this->_strIdColumn];
			}
	
			// Make sure we have an Id
			if (!$intId) {
				return false;
			}

			$arrResult = $this->SelectById($this->_strTable, $this->_arrColumns, $intId);
			
			// set Id
			$this->_arrProperties[$this->_strIdColumn] = $intId;
		}
		
		if (!empty($arrResult)) {
			// load the data into the object
			$bolReturn = $this->$strType($arrResult);
		} else {
			$this->_arrValid[$this->_strIdColumn] = false;
			$this->_bolValid = false;
			$bolReturn = false;
		}
		
		return $bolReturn;
	}
	
	function LoadClean($intId=null) {
		//TODO! we need to make a descision as to what to do when no data is retrieved
		
		$bolReturn = $this->Load($intId, $strType='LoadData');
		if (!$bolReturn) {
			// clean the object
			$this->Clean();
		}
		
		return $bolReturn;
	}
	
	function LoadMerge($intId=null) { 
		return $this->Load($intId, $strType='MergeData');
	}
	
	function LoadUpdate($intId=null) {
		return $this->Load($intId, $strType='UpdateData');
	}
	
	function LoadData($arrData) {
		// clean the object
		$this->Clean();
	
		// store the raw result
		$this->_arrResult = $arrData;
			
		// store the data in the property list
		$this->_arrProperties = $arrData;
		
		$this->_intStatus = STATUS_LOADED;
		
		return true;
	}
	
	function MergeData($arrData) {
		// store the raw result
		$this->_arrResult = $arrData;
		
		foreach ($arrData as $strKey=>$mixValue) {
			if (!array_key_exists($strKey, $this->_arrProperties)) {
				$this->_arrProperties[$strKey] = $mixValue;
			}
		}
		
		$this->_intStatus = STATUS_MERGED;
		
		return true;
	}
	
	function UpdateData($arrData) {
		// store the raw result
		$this->_arrResult = $arrData;
		
		foreach ($arrData as $strKey=>$mixValue) {
			$this->_arrProperties[$strKey] = $mixValue;
		}
		
		$this->_intStatus = STATUS_UPDATED;
		
		return true;
	}
	
	function Save() {
		// Is this a new record?
		if ($this->_arrProperties[$this->_strIdColumn] > 0) {
			// Update by Id
			$mixReturnValue = $this->UpdateById($this->_strTable, $this->_arrColumns, $this->_arrProperties);
			return ($mixReturnValue !== false);
		} else {
			// Insert, and set the new Id
			if ($mixResult = $this->Insert($this->_strTable, $this->_arrColumns, $this->_arrProperties)) {
				$mixReturnValue = ($this->_arrProperties[$this->_strIdColumn] = $mixResult);
				return ($mixReturnValue !== false);
			} else {
				return false;
			}
		}
	}
	
	function Info() {
		// load the values of each property
		foreach ($this->_arrProperties as $strProperty=>$mixValue) {
			$arrReturn['Properties'][$strProperty]['Value'] = $mixValue;
		}

		// load property definition data
		foreach ($this->_arrProperties as $strProperty=>$mixValue) {
			// for each context of each property, load the definition data if it exists (this will also load conditional context data)
			if (isset($this->_arrDefine[$strProperty])) {
				$arrReturn['Properties'][$strProperty]['Context'] = $this->_arrDefine[$strProperty];
			}
		}

		// load validation information
		if (!empty($this->_arrValid)) {
			$arrReturn['Valid'] = $this->_arrValid;
		}

		return $arrReturn;
	}

	function ShowInfo($strTabs='') {
		$arrInfo = $this->Info();
		$strOutput = $this->_ShowInfo($arrInfo, $strTabs);

		if (!$strTabs)
		{
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
	
	function SetTable($strTable) {
		return $this->_strTable = $strTable;
	}
	
	function GetTable() {
		return $this->_strTable;
	}
	
	function SetColumns($mixColumns=null) {
		if (is_array($mixColumns)) {
			$this->_arrColumns = $mixColumns;
		} elseif ($mixColumns) {
			// convert column names into an array
			$mixColumns = str_replace(" ", "", $mixColumns);
			$arrColumns = explode(",", $mixColumns);
			$this->_arrColumns = $arrColumns;
		} elseif ($this->_arrDefine !== null && array_key_exists('Columns', $this->_arrDefine) && $this->_arrDefine['Columns']) {
			// currently $this->_arrDefine['Columns'] is not defined so this
			// block of code will never be run
			$this->_arrColumns = $this->_arrDefine['Columns'];
		} else {
			$this->_arrColumns = null;
			//TODO!!!! get * column names for tables
			// This scenario is currently handled by DataAccessUI::Select and DataAccessUI::SelectById
			// If either of these methods are called and $_arrColumns is empty, it selects all
			// columns from the table of the database
		}
		
		if (is_array($this->_arrColumns) && !IsAssociativearray($this->_arrColumns)) {
			$arrColumns = array();
			foreach ($this->_arrColumns as $strColumn) {
				$arrColumns[$strColumn] = $strColumn;
			}
			$this->_arrColumns = $arrColumns;
		}
		
		return $this->_arrColumns;
	}
	
	function GetColumns() {
		return $this->_arrColumns;
	}

	function SetContext($intContext) {
		return $this->_intContext = $intContext;
	}
	
	function GetContext() {
		return $this->_intContext;
	}

	function Asarray() {
		return $this->_arrProperties;
	}
}
