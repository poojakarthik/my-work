<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// db_object.php
//----------------------------------------------------------------------------//
/**
 * db_object
 *
 * contains the DBObject class which represents a single record of a table in the database
 *
 * contains the DBObject class which represents a single record of a table in the database
 *
 * @file		db_object.php
 * @language	PHP
 * @package		ui_app
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// DBObject
//----------------------------------------------------------------------------//
/**
 * DBObject
 *
 * Database Object - represents a single record of a table in the database
 *
 * Database Object - represents a single record of a table in the database
 *
 *
 * @prefix	dbo
 *
 * @package	ui_app
 * @class	DBObject
 * @extends	DBObjectBase
 */
class DBObject extends DBObjectBase
{
	public $_objWhere;
	public $Where;
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
	public $_intContext		= 0;
	
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
		elseif ($this->_arrDefine !== NULL && array_key_exists('Table', $this->_arrDefine) && $this->_arrDefine['Table'])
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
		if ($this->_arrDefine != NULL && array_key_exists('IdColumn', $this->_arrDefine) && $this->_arrDefine['IdColumn'])
		{
			$this->_strIdColumn = $this->_arrDefine['IdColumn'];
		}
		
		// set up where object
		$this->_objWhere = new DBWhere();
		
		// set up a public ref to the where object
		$this->Where = $this->_objWhere;
		
		
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
	 * @param	string	$strName		name of the method that was called
	 * @param	array	$arrArguments	arguements that were passed
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
	 * @param	string		$strProperty	property's name
	 * 
	 * @return	PropertyToken
	 *
	 * @method
	 */
	function __get($strProperty)
	{
		//Debug("GET $strProperty");
		return PropertyToken()->_Property($this, $strProperty);
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
	 * @param	string		$strProperty	property's name
	 * @param	mix			$mixValue		property's value
	 * 
	 * @return	PropertyToken
	 *
	 * @method
	 */
	function __set($strProperty, $mixValue)
	{
		//Debug("GET $strProperty");
		//$objToken = PropertyToken()->_Property($this, $strProperty);
		return ($this->_arrProperties[$strProperty] = $mixValue);
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
	 * @param	string		$strProperty	new property's name
	 * @param	mix			$mixValue		new property's value
	 * @param	string		$intContext		new property's context which is 
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
	 * @param	string		$intContext		property's context which is 
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
			// Check if the Property is optional.  If a property is optional, 
			// then you only perform the validation rule, if the value doesn't equate to an empty string
			$strValidationRule = trim($strValidationRule);
			
			if (strtolower(substr($strValidationRule, 0, 9)) == "optional:")
			{
				// The property is optional
				if ($this->_arrProperties[$strProperty] === "" || $this->_arrProperties[$strProperty] === NULL)
				{
					// A value for the property has not been suplied therefore it has passed validation
					$this->_arrValid[$strProperty] = TRUE;
					return TRUE;
				}
				
				// A value for the property has been suplied so perform the validation
				$strValidationRule = trim(substr($strValidationRule, 9));
			}
		
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
			// Validate Property
			if (!$this->_arrValid[$strProperty] = $this->ValidateProperty($strProperty, $intContext))
			{
				// Invalid
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
		foreach($this->_arrValid as $bolValid)
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
	// SetToInvalid
	//------------------------------------------------------------------------//
	/**
	 * SetToInvalid()
	 *
	 * Explicitly sets the object to invalid
	 *
	 * Explicitly sets the object to invalid
	 *
	 * @return	void
	 *
	 * @method
	 */
	function SetToInvalid()
	{
		$this->_bolValid = FALSE;
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
		$this->_objWhere->Clean();
		$this->SetColumns();
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
		// get data
		if ($this->_objWhere->GetString())
		{
			// WHERE clause has been declared so use it, but only retrieve one record maximum
			$arrResults = $this->Select($this->_strTable, $this->_arrColumns, $this->_objWhere, 0, 1);
			
			// $this->Select() returns an array of records, even though there will only ever be 1 record at the most
			$arrResult = $arrResults[0];
		}
		else
		{
			// WHERE clause has not been defined so retrieve the record based on the value of the Id property

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

			$arrResult = $this->SelectById($this->_strTable, $this->_arrColumns, $intId);
			
			// set Id
			$this->_arrProperties[$this->_strIdColumn] = $intId;
		}
		
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
	 * @param	integer		$intId		[optional] Id of the record we want to load
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
	 * @param	integer	$intId	[optional] Id of the record we want to load
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
	 * @param	integer	$intId	[optional] Id of the record we want to load
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
	 * @param	array		$arrData	raw data to be loaded into the object
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
		
		foreach ($arrData as $strKey=>$mixValue)
		{
			if (!array_key_exists($strKey, $this->_arrProperties))
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
			$mixReturnValue = $this->UpdateById($this->_strTable, $this->_arrColumns, $this->_arrProperties);
			return ($mixReturnValue !== FALSE);
		}
		else
		{
			// Insert, and set the new Id
			if ($mixResult = $this->Insert($this->_strTable, $this->_arrColumns, $this->_arrProperties))
			{
				$mixReturnValue = ($this->_arrProperties[$this->_strIdColumn] = $mixResult);
				return ($mixReturnValue !== FALSE);
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
		// load the values of each property
		foreach ($this->_arrProperties as $strProperty=>$mixValue)
		{
			$arrReturn['Properties'][$strProperty]['Value'] = $mixValue;
		}

		// load property definition data
		foreach ($this->_arrProperties as $strProperty=>$mixValue)
		{
			// for each context of each property, load the definition data if it exists (this will also load conditional context data)
			if (isset($this->_arrDefine[$strProperty]))
			{
				$arrReturn['Properties'][$strProperty]['Context'] = $this->_arrDefine[$strProperty];
			}
		}

		// load validation information
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
	 * @param	string		$strTabs	[optional]	string containing tab chars '\t'
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
		$strOutput = $this->_ShowInfo($arrInfo, $strTabs);

		if (!$strTabs)
		{
			Debug($strOutput);
		}
		return $strOutput;
	}
	
	//------------------------------------------------------------------------//
	// _ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * _ShowInfo()
	 *
	 * Recursively formats data which may or may not be a multi-dimensional array
	 *
	 * Recursively formats data which may or may not be a multi-dimensional array
	 * This is used by the ShowInfo method
	 *
	 * @param	mix			$mixData				Data to format
	 *												this can be a single value, array
	 *												or multi-dimensional array
	 * @param	string		$strTabs	[optional]	string containing tab chars '\t'
	 *												used to define how far the object's 
	 *												info should be tabbed.
	 * @return	string								returns the object's info as a formatted string.
	 *
	 * @method
	 */
	private function _ShowInfo($mixData, $strTabs='')
	{
		if (is_array($mixData))
		{
			foreach ($mixData as $mixKey=>$mixValue)
			{
				if (!is_array($mixValue))
				{
					// $mixValue is not an array
					$strOutput .= $strTabs . $mixKey . " : " . $mixValue . "\n";
				}
				else
				{
					// $mixValue is an array so output its contents
					$strOutput .= $strTabs . $mixKey . "\n";
					$strOutput .= $this->_ShowInfo($mixValue, $strTabs."\t");
				}
			}
		} 
		else
		{
			$strOutput = $mixData . "\n";
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
	 * @param	string		$strTable	name of the table.  Note that this can be
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
	 *									anything that can go in an SQL "FROM" clause.
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
	 * @param	mix		$mixColumns		optional; Either an associated array of columns and their alias's (Alias=>ColumnName)
	 *									OR 
	 *									an indexed array of column names (Array("Column1", "Column2", etc)
	 *									OR
	 *									a comma separated string of columns ("Column1, Column2, etc")
	 *									OR 
	 *									NULL to retrive columns from the database definition file
	 *
	 * @return	array					returns the data attribute storing the column names ($_arrColumns)
	 *
	 * @method
	 */
	function SetColumns($mixColumns=NULL)
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
		elseif ($this->_arrDefine !== NULL && array_key_exists('Columns', $this->_arrDefine) && $this->_arrDefine['Columns'])
		{
			// currently $this->_arrDefine['Columns'] is not defined so this
			// block of code will never be run
			$this->_arrColumns = $this->_arrDefine['Columns'];
		}
		else
		{
			$this->_arrColumns = NULL;
			//TODO!!!! get * column names for tables
			// This scenario is currently handled by DataAccessUI::Select and DataAccessUI::SelectById
			// If either of these methods are called and $_arrColumns is empty, it selects all
			// columns from the table of the database
		}
		
		if (is_array($this->_arrColumns) && !IsAssociativeArray($this->_arrColumns))
		{
			$arrColumns = Array();
			foreach ($this->_arrColumns as $strColumn)
			{
				$arrColumns[$strColumn] = $strColumn;
			}
			$this->_arrColumns = $arrColumns;
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

	//------------------------------------------------------------------------//
	// SetContext
	//------------------------------------------------------------------------//
	/**
	 * SetContext()
	 *
	 * Set the current context associated with this DBObject
	 *
	 * Set the current context associated with this DBObject
	 * The DBObject's current context is used when retrieving definition data for 
	 * a property using the token's __get method
	 * 
	 * @param	int		$intContext		Context to set the DBObject to
	 *
	 * @return	int						returns the data attribute declaring the current context ($_intContext)
	 *
	 * @method
	 */
	function SetContext($intContext)
	{
		return $this->_intContext = $intContext;
	}
	
	//------------------------------------------------------------------------//
	// GetContext
	//------------------------------------------------------------------------//
	/**
	 * GetContext()
	 *
	 * Get the context currently associated with this DBObject
	 *
	 * Get the context currently associated with this DBObject
	 * 
	 * @return	int						returns the data attribute declaring the current context ($_intContext)
	 * @method
	 */
	function GetContext()
	{
		return $this->_intContext;
	}

	//------------------------------------------------------------------------//
	// AsArray
	//------------------------------------------------------------------------//
	/**
	 * AsArray()
	 *
	 * Returns the record that the DBObject represents, as an associative array of properties and their values
	 *
	 * Returns the record that the DBObject represents, as an associative array of properties and their values
	 * 
	 * @return	array				Associative array of properties and their values
	 * @method
	 */
	function AsArray()
	{
		return $this->_arrProperties;
	}

}


?>
