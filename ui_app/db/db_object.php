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
		if (is_array($mixColumns))
		{
			$this->_arrColumns = $mixColumns;
		}
		elseif ($mixColumns)
		{
			//TODO!!!! convert column names into an array
		}
		elseif ($this->_arrDefine['Columns'])
		{
			$this->_arrColumns = $this->_arrDefine['Columns'];
		}
		else
		{
			// get * column names for tables
			//TODO!!!! I think you have to explicitly define the column names
			//$this->_arrColumns = "*";
		}
		
		// set ID column name
		//TODO!!!! look harder to find this
		if ($this->_arrDefine['IdColumn'])
		{
			$this->_strIdColumn = $this->_arrDefine['IdColumn'];
		}
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Calls a private for this object method
	 *
	 * <long description>
	 *
	 * @param	string	$strName	<description>
	 * @param	array	$arrArguments	<description>
	 * @return	<type>
	 *
	 * @method
	 */
	function __call($strName, $arrArguments)
	{
		return PropertyToken()->Method($this, $strName, $arrArguments);
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
	 * @param	integer		$intId		optional The Id of the record we want to load
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
		if ($arrResult = $this->SelectById($this->_strTable, $this->_arrColumns, $intId))
		{
			// load the data into the object
			$bolReturn = $this->$strType($arrResult);
		}
		else
		{
			$bolReturn = FALSE;
		}
		
		// set Id
		$this->_arrProperties[$this->_strIdColumn] = $intId;
		
		return $bolReturn;
	 }
	 
	 // always cleans the object
	 function LoadClean($intId = NULL)
	 {
	 	$bolReturn = $this->Load($intId, $strType='LoadData');
		if (!$bolReturn)
		{
	 		// clean the object
			$this->Clean();
		}
		
		return $bolReturn;
	 }
	 
	//------------------------------------------------------------------------//
	// _LoadMerge
	//------------------------------------------------------------------------//
	/**
	 * _LoadMerge()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	integer	$intId	[optional] <description>
	 * @return	boolean
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function LoadMerge($intId=NULL)
	{		
		return $this->Load($intId, $strType='MergeData');
	}
	
	//------------------------------------------------------------------------//
	// _LoadUpdate
	//------------------------------------------------------------------------//
	/**
	 * _LoadUpdate
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	integer	$intId	[optional] <description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
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
	 * @param	integer		$intId		The Id of the record we want to load
	 *
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
}


?>
