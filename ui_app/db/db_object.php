<?php

//----------------------------------------------------------------------------//
// DBObject
//----------------------------------------------------------------------------//
/**
 * DBObject
 *
 * Database Object
 *
 * Database Object
 *
 *
 * @prefix	dbo
 *
 * @package	framework_ui
 * @class	DBObject
 * @extends	DBObjectBase
 */
class DBObject extends DBObjectBase
{
	public $_strIdColumn 	= 'Id';
	public $_arrColumns 	= Array();
	public $_strTable		= '';
	public $_strName		= '';
	public $_arrResult		= Array();
	public $_arrValid		= Array();
	public $_bolValid		= NULL;
	public $_arrProperties	= Array();
	public $_intStatus		= 0;
	public $_arrDefine		= Array();
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Construct a new Database Object
	 *
	 * construct a new Database Object
	 *
	 * @param	string	$strName					Name of the object to create
	 * @param	string	$strTable		optional	Database table to connect the data object to 
	 * @param	mixed	$mixColumns		optional	Columns to include in the data object
	 * 
	 * @return	DBObject
	 *
	 * @method
	 */
	function __construct($strName, $strTable=NULL, $mixColumns=NULL)
	{
		//_arrDefine does not currently contain 'Table' or 'Columns' or 'IdColumn'

		// Parent Constructor
		parent::__construct();
		
		// set name
		$this->_strName = $strName;
		
		// get config
		$this->_arrDefine = Config()->Get('Dbo', $strName);
		
		// set table
		if ($strTable)
		{
			// use the table from parameters
			$this->_strTable = $strTable;
		}
		elseif ($this->_arrDefine['Table'])
		{
			// use the table from the definition
			$this->_strTable = $this->_arrDefine['Table'];
		}
		else
		{
			// as a last resort use the dbo name as the table name
			$this->_strTable = $strName;
		}

		// set columns
		$this->SetColumns($mixColumns);
		
		// set ID column name
		//TODO!!!! look harder to find this
		if ($this->_arrDefine['IdColumn'])
		{
			$this->_strIdColumn = $this->_arrDefine['IdColumn'];
		}
		
		// validate the object (considering nothing has been actually loaded into the object
		// this should just set $_bolValid to TRUE
		$this->Validate();
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Calls a private for this object method
	 *
	 * Calls a private for this object method
	 *
	 * @param	string	$strName		The name of the method that was called
	 * @param	array	$arrArguments	the arguements that were passed
	 * @return	bool
	 *
	 * @method
	 */
	function __call($strName, $arrArguments)
	{
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Generic Property GET function
	 *
	 * Generic Property GET function
	 *
	 * @param	string		$strProperty	The property's name
	 * 
	 * @return	PropertyToken
	 *
	 * @method
	 */
	function __get($strProperty)
	{
		//Debug("GET $strProperty");
		return PropertyToken()->Property($this, $strProperty);
	}
	
	//------------------------------------------------------------------------//
	// __set
	//------------------------------------------------------------------------//
	/**
	 * __set()
	 *
	 * Sets the value to this property
	 *
	 * Sets the value to this property
	 *
	 * @param	string		$strProperty	The property's name
	 * 
	 * @return	PropertyToken
	 *
	 * @method
	 */
	function __set($strProperty, $mixValue)
	{
		//Debug("GET $strProperty");
		$objToken = PropertyToken()->Property($this, $strProperty);
		return ($this->$strProperty->value = $mixValue);
	}
	
	//------------------------------------------------------------------------//
	// AddProperty
	//------------------------------------------------------------------------//
	/**
	 * AddProperty()
	 *
	 * Adds a property to the object and validates it
	 *
	 * Adds a property to the object and validates it
	 *
	 * @param	string		$strProperty	The new property's name
	 * @param	mix			$mixValue		The new property's value
	 * @param	string		$intContext		The new property's context which is 
	 *										used to select the specific validation rule
	 * 
	 * @return	void
	 *
	 * @method
	 */
	function AddProperty($strProperty, $mixValue, $intContext=CONTEXT_DEFAULT)
	{
		// store property
		$this->_arrProperties[$strProperty] = $mixValue;
		
		// validate property
		$this->ValidateProperty($strProperty, $intContext);
	}
	
	//------------------------------------------------------------------------//
	// ValidateProperty
	//------------------------------------------------------------------------//
	/**
	 * ValidateProperty()
	 *
	 * Validates a property
	 *
	 * Validates a property
	 * If the property is found to be invalid then the object is set to invalid
	 * as well as the individual property
	 *
	 * @param	string		$strProperty	name of property to validate
	 * @param	string		$intContext		the property's context which is 
	 *										used to select the specific validation rule
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function ValidateProperty($strProperty, $intContext=CONTEXT_DEFAULT)
	{
		// find validation rule
		$strValidationRule = $this->_arrDefine[$strProperty][$intContext]['ValidationRule'];
		
		// do validation
		if ($strValidationRule)
		{
			if (!$this->_arrValid[$strProperty] = Validate($strValidationRule, $this->_arrProperties[$strProperty]))
			{
				$this->_bolValid = FALSE;
			}
			return $this->_arrValid[$strProperty];
		}
		else
		{
			// no validation rule
			return TRUE;
		}
	}
	
	//------------------------------------------------------------------------//
	// Validate
	//------------------------------------------------------------------------//
	/**
	 * Validate()
	 *
	 * Validates the entire object
	 *
	 * Validates the entire object
	 * If any property is found to be invalid then the object is set to invalid
	 * as well as the individual property
	 *
	 * @param	string		$intContext		context which is used to select the 
	 *										specific validation rule for each property
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function Validate($intContext=CONTEXT_DEFAULT)
	{
		$this->_bolValid = TRUE;
		$this->_arrValid = Array();
		foreach ($this->_arrProperties AS $strProperty=>$mixValue)
		{
			// validate property
			if (!$this->_arrValid[$strProperty] = $this->ValidateProperty($strProperty, $mixValue, $intContext))
			{
				// invalid
				$this->_bolValid = FALSE;
			}
		}
		return $this->_bolValid;
	}
	
	//------------------------------------------------------------------------//
	// IsValid
	//------------------------------------------------------------------------//
	/**
	 * IsValid()
	 *
	 * Checks that each validated property of the object, is valid.
	 *
	 * Checks that each validated property of the object, is valid.
	 * If a validated property is invalid then the object is set to invalid
	 * Else it returns the current state of the object's validation
	 *
	 * @return	bool
	 *
	 * @method
	 */
	function IsValid()
	{
		foreach($this->_arrValid AS $bolValid)
		{
			if ($bolValid === FALSE)
			{
				return $this->_bolValid = FALSE;
			}
		}
		return $this->_bolValid;
	}
	
	//------------------------------------------------------------------------//
	// IsInvalid
	//------------------------------------------------------------------------//
	/**
	 * IsInvalid()
	 *
	 * Checks if the DBObject has been explicitly set to invalid
	 *
	 * Checks if the DBObject has been explicitly set to invalid or
	 * any of the properties of the object have been set to invalid.
	 * IsValid can return true, false, or null
	 * this function tells you specificly if it has been marked as invalid
	 *
	 * @return	bool		If any of the object's properties have been flagged as invalid
	 *						or if the DBObject itself has been flagged as invalid then
	 *						the method returns TRUE. 
	 *						Else the method returns FALSE, meaning the object is either valid
	 *						or has not had its validity checked yet.
	 *
	 * @method
	 */
	function IsInvalid()
	{
		if ($this->IsValid() === FALSE)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// SetValid
	//------------------------------------------------------------------------//
	/**
	 * SetValid()
	 *
	 * Checks that each validated property of the object, is valid
	 *
	 * Checks that each validated property of the object, is valid
	 * If a validated property is invalid then the object is set to invalid
	 * Else it sets the object to valid
	 *
	 * @return	bool
	 *
	 * @method
	 */
	// checks validation on the entire object and also sets the object to valid
	function SetValid()
	{
		$this->_bolValid = TRUE;
		return $this->IsValid();
	}
	
	//------------------------------------------------------------------------//
	// Clean
	//------------------------------------------------------------------------//
	/**
	 * Clean()
	 *
	 * Cleans the object
	 *
	 * Cleans the object
	 *
	 * @return	void
	 *
	 * @method
	 */
	function Clean()
	{
		$this->_arrProperties 	= Array();
		$this->_arrResult		= Array();
		$this->_arrValid 		= Array();
		$this->_bolValid		= NULL;
		$this->_intStatus		= STATUS_CLEANED;
	}
	
	//------------------------------------------------------------------------//
	// Load
	//------------------------------------------------------------------------//
	/**
	 * Load()
	 *
	 * Loads the object from the Database
	 *
	 * Loads the object from the Database
	 * Cleans the object if load is sucessful
	 *
	 * @param	integer		$intId		[optional] The Id of the record we want to load
	 *
	 * @return	bool
	 *
	 * @method
	 */
	 function Load($intId = NULL, $strType='LoadData')
	 {
		// Set Id
		$intId = (int)$intId;
		if (!$intId)
		{
			$intId = (int)$this->_arrProperties[$this->_strIdColumn];
		}

		// Make sure we have an Id
		if (!$intId)
		{
			return FALSE;
		}
		
		// get data
		$arrResult = $this->SelectById($this->_strTable, $this->_arrColumns, $intId);
		if (!empty($arrResult))
		{
			// load the data into the object
			$bolReturn = $this->$strType($arrResult);
		}
		else
		{
			$this->_arrValid[$this->_strIdColumn] = FALSE;
			$this->_bolValid = FALSE;
			$bolReturn = FALSE;
		}
		
		// set Id
		$this->_arrProperties[$this->_strIdColumn] = $intId;
		
		return $bolReturn;
	 }
	 
	//------------------------------------------------------------------------//
	// LoadClean
	//------------------------------------------------------------------------//
	/**
	 * LoadClean()
	 *
	 * Peforms a clean load of the object from the Database
	 *
	 * Peforms a clean load of the object from the Database
	 * This always cleans the object regardless of whether or not anything could
	 * be loaded from the database.
	 *
	 * @param	integer		$intId		[optional] The Id of the record we want to load
	 *
	 * @return	bool
	 *
	 * @method
	 */
	 function LoadClean($intId = NULL)
	 {
	 	//TODO! we need to make a descision as to what to do when no data is retrieved
		
	 	$bolReturn = $this->Load($intId, $strType='LoadData');
		if (!$bolReturn)
		{
	 		// clean the object
			$this->Clean();
		}
		
		return $bolReturn;
	 }
	 
	//------------------------------------------------------------------------//
	// LoadMerge
	//------------------------------------------------------------------------//
	/**
	 * LoadMerge()
	 *
	 * Merges data from the database into the existing property data
	 *
	 * Merges data from the database into the existing property data
	 * Each individual property is only loaded from the database if it is 
	 * currently undefined in the object
	 *
	 * @param	integer	$intId	[optional] The Id of the record we want to load
	 * @return	bool
	 *
	 * @method
	 */
	function LoadMerge($intId=NULL)
	{		
		return $this->Load($intId, $strType='MergeData');
	}
	
	//------------------------------------------------------------------------//
	// LoadUpdate
	//------------------------------------------------------------------------//
	/**
	 * LoadUpdate()
	 *
	 * Updates the existing property data with data taken from the database
	 *
	 * Updates the existing property data with data taken from the database
	 * This only updates the properties specified in $_arrColumns
	 * All other properties are left unchanged
	 *
	 * @param	integer	$intId	[optional] The Id of the record we want to load
	 * @return	bool
	 *
	 * @method
	 */
	function LoadUpdate($intId=NULL)
	{
		return $this->Load($intId, $strType='UpdateData');
	}
	
	//------------------------------------------------------------------------//
	// LoadData
	//------------------------------------------------------------------------//
	/**
	 * LoadData()
	 *
	 * Loads a MySQL result associative array as property data
	 *
	 * Loads a MySQL result associative array as property data
	 *
	 * @param	array		$arrData	the raw data to be loaded into the object
	 *									(MySQL result associative array)
	 * @return	bool
	 *
	 * @method
	 */
	 function LoadData($arrData)
	 {
	 	// clean the object
		$this->Clean();
		
	 	// store the raw result
		$this->_arrResult = $arrData;
			
		// store the data in the property list
		$this->_arrProperties = $arrData;
	 	
		$this->_intStatus		= STATUS_LOADED;
		
		return TRUE;
	 }
	 
	//------------------------------------------------------------------------//
	// MergeData
	//------------------------------------------------------------------------//
	/**
	 * MergeData()
	 *
	 * Merges a MySQL result associative array into the existing property data
	 *
	 * Merges a MySQL result associative array into the existing property data
	 * Each individual property is only loaded from $arrData, if it is currently
	 * undefined in the object
	 *
	 * @param	array		$arrData	the raw data to be merged into the object
	 *									(MySQL result associative array)
	 * @return	bool
	 *
	 * @method
	 */
	 function MergeData($arrData)
	 {
	 	// store the raw result
		$this->_arrResult = $arrData;
		
		foreach ($arrData AS $strKey=>$mixValue)
		{
			if (!isset($this->_arrProperties[$strKey]))
			{
				$this->_arrProperties[$strKey] = $mixValue;
			}
		}
		
		$this->_intStatus		= STATUS_MERGED;
		
		return TRUE;
	 }
	 
	//------------------------------------------------------------------------//
	// UpdateData
	//------------------------------------------------------------------------//
	/**
	 * UpdateData()
	 *
	 * Partially updates the object's property data from a MySQL result associative array
	 *
	 * Updates the object's property data from a MySQL result associative array
	 * This is used to partially update the object's property data as it will 
	 * only update the properties that are specified in $arrData
	 *
	 * @param	array		$arrData	the raw data used to update the object
	 *									(MySQL result associative array)
	 * @return	bool
	 *
	 * @method
	 */
	 function UpdateData($arrData)
	 {
	 	// store the raw result
		$this->_arrResult = $arrData;
		
		foreach ($arrData AS $strKey=>$mixValue)
		{
			$this->_arrProperties[$strKey] = $mixValue;
		}
		
		$this->_intStatus		= STATUS_UPDATED;
		
		return TRUE;
	 }
	 
	//------------------------------------------------------------------------//
	// Save
	//------------------------------------------------------------------------//
	/**
	 * Save()
	 *
	 * Saves current object data to the Database
	 *
	 * Saves current object data to the Database
	 *
	 * @return	bool
	 *
	 * @method
	 */
	 function Save()
	 {				
		// Is this a new record?
		if ($this->_arrProperties[$this->_strIdColumn] > 0)
		{
			// Update by Id
			return (bool)$this->UpdateById($this->_strTable, $this->_arrColumns, $this->_arrProperties);
		}
		else
		{
			// Insert, and set the new Id
			if ($mixResult = $this->Insert($this->_strTable, $this->_arrColumns, $this->_arrProperties))
			{
				return (bool)($this->_arrProperties[$this->_strIdColumn] = $mixResult);
			}
			else
			{
				return FALSE;
			}
		}
	 }
	 
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * return info about the DBO object
	 *
	 * return info about the DBO object
	 * 
	 * @return	array		stores all properties ['Properties'] and all valid properties ['Valid']
	 *
	 * @method
	 */
	function Info()
	{
		$arrReturn['Properties'] = $this->_arrProperties;
		if (!empty($this->_arrValid))
		{
			$arrReturn['Valid'] = $this->_arrValid;
		}
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats info about the DBO object so that it can be displayed
	 *
	 * Formats info about the DBO object so that it can be displayed
	 * 
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the object's 
	 *												info should be tabbed.
	 * @return	string								returns the object's info as a formatted string.
	 *												If strTabs is not given then this string is
	 *												also output using Debug()
	 *
	 * @method
	 */
	function ShowInfo($strTabs='')
	{
		$arrInfo = $this->Info();
		foreach ($arrInfo AS $strKey=>$arrValue)
		{
			$strOutput .= $strTabs."$strKey\n";
			foreach ($arrValue AS $strProperty=>$mixValue)
			{
				$strOutput .= $strTabs."\t$strProperty : $mixValue\n";
			}
		}
		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}
	
	//------------------------------------------------------------------------//
	// SetTable
	//------------------------------------------------------------------------//
	/**
	 * SetTable()
	 *
	 * Set the table associated with this DBObject
	 *
	 * Set the table associated with this DBObject
	 * 
	 * @param	string		$strTable	The name of the table.  Note that this can be
	 *									anything that can go in a SQL "FROM" clause.
	 *									tables can be joined so long as you specify how
	 *
	 * @return	string					returns the data attribute storing the table name ($_strTable)
	 *
	 * @method
	 */
	function SetTable($strTable)
	{
		return $this->_strTable = $strTable;
	}
	
	//------------------------------------------------------------------------//
	// GetTable
	//------------------------------------------------------------------------//
	/**
	 * GetTable()
	 *
	 * Get the table name associated with this DBObject
	 *
	 * Get the table name associated with this DBObject
	 * 
	 * @return	string					The name of the table.  Note that this can be
	 *									anything that can go in a SQL "FROM" clause.
	 *									tables can be joined.
	 * @method
	 */
	function GetTable()
	{
		return $this->_strTable;
	}
	
		//------------------------------------------------------------------------//
	// SetColumns
	//------------------------------------------------------------------------//
	/**
	 * SetColumns()
	 *
	 * Set the columns to retrieve
	 *
	 * Set the columns to retrieve
	 * 
	 * @param	mix		$mixColumns		Either an associated array of columns and their alias's (Alias=>ColumnName)
	 *									OR 
	 *									an indexed array of column names (Array("Column1", "Column2", etc)
	 *									OR
	 *									a comma separated string of columns ("Column1, Column2, etc")
	 *
	 * @return	array					returns the data attribute storing the column names ($_arrColumns)
	 *
	 * @method
	 */
	function SetColumns($mixColumns)
	{
		if (is_array($mixColumns))
		{
			$this->_arrColumns = $mixColumns;
		}
		elseif ($mixColumns)
		{
			// convert column names into an array
			$mixColumns = str_replace(" ", "", $mixColumns);
			$arrColumns = explode(",", $mixColumns);
			$this->_arrColumns = $arrColumns;
		}
		elseif ($this->_arrDefine['Columns'])
		{
			$this->_arrColumns = $this->_arrDefine['Columns'];
		}
		else
		{
			//TODO!!!! get * column names for tables
			// This scenario is currently handled by DataAccessUI::Select and DataAccessUI::SelectById
			// If either of these methods are called and $_arrColumns is empty, it selects all
			// columns from the table of the database
		}
		return $this->_arrColumns;
	}
	
	//------------------------------------------------------------------------//
	// GetColumns
	//------------------------------------------------------------------------//
	/**
	 * GetColumns()
	 *
	 * Accessor for the list of columns which this object retrieves from the database
	 *
	 * Accessor for the list of columns which this object retrieves from the database
	 * 
	 * @return	array					returns the data attribute storing the column names ($_arrColumns)
	 * @method
	 */
	function GetColumns()
	{
		return $this->_arrColumns;
	}

}


?>
