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
	public $_arrTables		= Array();
	public $_strName		= '';
	public $_arrResult		= Array();
	public $_arrRequest		= Array();
	public $_arrValid		= Array();
	public $_arrProperty	= Array();
	public $_intStatus		= 0;
	public $_arrOptions		= Array();
	
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
	 * @param	mixed	$mixTable		optional	Database table to connect the data object to 
	 * @param	mixed	$mixColumns		optional	Columns to include in the data object
	 * 
	 * @return	DBObject
	 *
	 * @method
	 */
	function __construct($strName, $mixTable=NULL, $mixColumns=NULL)
	{
		// Parent Constructor
		parent::__construct();
		
		// set name
		$this->_strName = $strName;
		
		// get config
		$this->_arrOptions = Config()->Get('Dbo', $strName);
		
		// set table
		if (is_array($mixTable))
		{
			$this->_arrTables = $mixTable;
		}
		elseif ($this->_arrOptions['Table'])
		{
			$this->_arrTables = $this->_arrOptions['Table'];
		}
		elseif($mixTable)
		{
			$arrTables = explode(',', $mixTable);
			foreach ($arrTables as $strTable)
			{
				$this->_arrTables[] = trim($strTable);
			}
		}
		else
		{
			// as a last resort use the dbo name as the table name
			$this->_arrTables[$strName]['Name'] = $strName;
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
		elseif ($this->_arrOptions['Columns'])
		{
			$this->_arrColumns = $this->_arrOptions['Columns'];
		}
		else
		{
			//TODO!!!! get * column names for tables
		}
		
		// set ID column name
		//TODO!!!! look harder to find this
		if ($this->_arrOptions['IdColumn'])
		{
			$this->_strIdColumn = $this->_arrOptions['IdColumn'];
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
		return PropertyToken()->Property($this, $strProperty);
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
		$this->dboObject->_arrProperty 	= Array();
		$this->dboObject->_arrRequest	= Array();
		$this->dboObject->_arrResult	= Array();
		$this->dboObject->_arrValid 	= Array();
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
	 *
	 * @param	integer		$intId		The Id of the record we want to load
	 *
	 * @return	bool
	 *
	 * @method
	 */
	 function Load($intId = NULL)
	 {
		// Set Id if we need to
		$this->_arrProperties['Id'] = ($intId == (int)$intId) ? $intId : $this->_arrProperties['Id'];
		
		// Make sure we have an Id
		if ($this->_arrProperties['Id'])
		{
	 		return $this->LoadData($this->SelectById($this->_arrTables, $this->_arrColumns, $intId));
		}
		else
		{
			return FALSE;
		}
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
	 	// Assign data
	 	$this->_arrProperty = array_merge($this->_arrProperty, $arrData);
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
		if ($this->_arrProperties['Id'] > 0)
		{
			// Update by Id
			return (bool)$this->UpdateById($this->_arrTables, $this->_arrColumns, $this->_arrProperties);
		}
		else
		{
			// Insert, and set the new Id
			if ($mixResult = $this->Insert($this->_arrTables, $this->_arrColumns, $this->_arrProperties))
			{
				return (bool)($this->_arrProperties['Id'] = $mixResult);
			}
			else
			{
				return FALSE;
			}
		}
	 }
}


?>