<?php

//----------------------------------------------------------------------------//
// DBList
//----------------------------------------------------------------------------//
/**
 * DBList
 *
 * Database Object List
 *
 * Database Object List
 *
 *
 * @prefix	dbl
 *
 * @package	framework_ui
 * @class	DBList
 * @extends	DBListBase
 */
class DBList extends DBListBase
{
	public $objWhere;
	public $arrDataArray	= Array();
	public $_intLimitStart	= NULL;
	public $_intLimitCount	= NULL;
	public $_intMode 		= DBO_RETURN;
	public $_strIdColumn 	= 'Id';
	public $_arrColumns 	= Array();
	public $_strTable		= '';
	public $_strName		= '';
	public $_arrResult		= Array();
	public $_arrRequest		= Array();
	public $_arrValid		= Array();
	public $_arrProperty	= Array();
	public $_intStatus		= 0;
	public $_arrDefine		= Array();
	public $_db				= NULL;
	
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Database Object List Constructor
	 *
	 * Database Object List Constructor
	 *
	 * @param	string	$strName					Name of the List Template to load
	 * @param	mixed	$strTable		optional	Database table to connect the data object to 
	 * @param	mixed	$mixColumns		optional	Columns to load
	 * @param	string	$strWhere		optional	WHERE Clause
	 * @param	array	$arrWhere		optional	WHERE Data
	 * 
	 * @return	DBList
	 *
	 * @method
	 */
	function __construct($strName, $strTable=NULL, $mixColumns=NULL, $strWhere=NULL, $arrWhere=NULL, $intLimitStart=NULL, $intLimitCount=NULL)
	{
		// Parent Constructor
		parent::__construct();		// !!!!!I'm not sure if it needs this, but the DBObject class has it here
		
		// set name
		$this->_strName = $strName;
		
		// get config
		$this->_arrDefine = Config()->Get('Dbl', $strName);  // !!!!!this 'Dbl' option is not yet implemented in the Config class
		
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
			//TODO!!!! get * column names for tables
		}
		
		// set ID column name
		//TODO!!!! look harder to find this
		if ($this->_arrDefine['IdColumn'])
		{
			$this->_strIdColumn = $this->_arrDefine['IdColumn'];
		}
		
		// set limit
		if (!is_null($intLimitStart))
		{
			$this->_intLimitStart = $intLimitStart;
		}
		else
		{
			$this->_intLimitStart = $this->_arrDefine['LimitStart'];
		}
		
		if (!is_null($intLimitCount))
		{
			$this->_intLimitCount = $intLimitCount;
		}
		else
		{
			$this->_intLimitCount = $this->_arrDefine['LimitCount'];
		}
		
		// set up where object
		if (!is_null($strWhere))
		{
			$this->objWhere 	= new DbWhere($strWhere, $arrWhere);
		}
		else
		{
			$this->objWhere 	= new DbWhere($this->_arrDefine['Where'], $this->_arrDefine['WhereData']);
		}
	}
	
	//------------------------------------------------------------------------//
	// Load
	//------------------------------------------------------------------------//
	/**
	 * Load()
	 *
	 * Loads the Database Object List from the Database
	 *
	 * Loads the Database Object List from the Database
	 *
	 * @param	string	$strWhere		[optional]	WHERE Clause with <> placeholders
	 * @param	array	$arrWhere		[optional]	WHERE parameter data
	 * @param	integer	$intLimitCount	[optional]	Number of items to load
	 * @param	integer	$intLimitStart	[optional]	Skip this many results
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function Load($arrWhere=NULL, $strWhere=NULL, $intLimitCount=NULL, $intLimitStart=NULL)
	{
		// setup where object
		$this->objWhere->Load($strWhere, $arrWhere);
		
		// setup limit
		$this->SetLimit($intLimitCount, $intLimitStart);
		
		if ($this->Select())
		{
			// load results into data objects
			$this->LoadResults();
			return TRUE;
		}
		else
		{
			return FALSE;
		}
		
	}
	
	
	//------------------------------------------------------------------------//
	// LoadResults
	//------------------------------------------------------------------------//
	/**
	 * LoadResults()
	 *
	 * Populate the Database Object List with latest Load() results
	 *
	 * Populate the Database Object List with latest Load() results
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function LoadResults()
	{
		if (is_array($this->_arrResult))
		{
			// empty records
			$this->EmptyRecords();
			
			// load records
			foreach($this->_arrResult AS $intKey=>$arrResult)
			{
				$this->AddRecord($arrResult);
			}
		}
		else
		{
			return FALSE;
		}
		return TRUE;
	}
	
	
	//------------------------------------------------------------------------//
	// SetLimit
	//------------------------------------------------------------------------//
	/**
	 * SetLimit()
	 *
	 * Limits the number of Database Objects in the List
	 *
	 * Limits the number of Database Objects in the List
	 *
	 * @param	integer	$intLimitCount	[optional]	Number of items to load
	 * @param	integer	$intLimitStart	[optional]	Skip this many results
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function SetLimit($intLimitCount=NULL, $intLimitStart=NULL)
	{
		if (!is_null($intLimitStart))
		{
			$this->_intLimitStart = $intLimitStart;
		}
		
		if (!is_null($intLimitCount))
		{
			$this->_intLimitCount = $intLimitCount;
		}
	}
	
	
	//------------------------------------------------------------------------//
	// Select
	//------------------------------------------------------------------------//
	/**
	 * Select()
	 *
	 * Retrieves relevant Object Data from the Database
	 *
	 * Retrieves relevant Object Data from the Database
	 *
	 * @return	bool
	 *
	 * @method
	 */
	function Select()
	{
		// select the record
		if ($arrResult = $this->_db->Select($this->_strTable, $this->_arrColumns, $this->objWhere, $this->intLimitStart, $this->intLimitCount))
		{
			$this->_arrResult = $arrResult;
		}
		else
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// LoadRecord
	//------------------------------------------------------------------------//
	/**
	 * LoadRecord()
	 *
	 * Alias for AddRecord
	 *
	 * Alias for AddRecord
	 *
	 * @param	array	$arrRecord	 Record to add to the list
	 * 
	 * @return	mixed				FALSE	: Failed
	 * 								integer	: Number of records loaded so far
	 *
	 * @method
	 */
	function LoadRecord($arrRecord)
	{
		return $this->AddRecord($arrRecord);
	}
	
	
	//------------------------------------------------------------------------//
	// AddRecord
	//------------------------------------------------------------------------//
	/**
	 * AddRecord()
	 *
	 * Adds a Database Object to the list generated from the data passed in
	 *
	 * Adds a Database Object to the list generated from the data passed in
	 *
	 * @param	array	$arrRecord	 Record to add to the list
	 * 
	 * @return	mixed				FALSE	: Failed
	 * 								integer	: Number of records loaded so far
	 *
	 * @method
	 */
	function AddRecord($arrRecord)
	{
		if (is_array($arrRecord))
		{
			// count++
			$this->_intCount++;
			
			// create object with count key
			$this->arrDataArray[$this->_intCount] = new DBObject($this->_strName, $this->_strTable, $this->arrColumns);

			$this->arrDataArray[$this->_intCount]->_arrResult	= $arrRecord;
			$this->arrDataArray[$this->_intCount]->LoadProperties($arrRecord);  //this method doesn't exist at the moment
			
			// for each index
				// create index entry for object
				//TODO!!!!
				
			// return key
			return $this->_intCount;
		}
		else
		{
			return FALSE;
		}
	}

	//------------------------------------------------------------------------//
	// EmptyRecords
	//------------------------------------------------------------------------//
	/**
	 * EmptyRecords()
	 *
	 * Cleans the Database Object List
	 *
	 * Cleans the Database Object List
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	function EmptyRecords()
	{
		// empty data array
		$this->arrDataArray = Array();
		
		// reset counter
		$this->_intCount = 0;
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Index
	//------------------------------------------------------------------------//
	/**
	 * Index()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	string	$strProperty	<description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function Index($strProperty)
	{
		// create new index
	}
	
	//------------------------------------------------------------------------//
	// UseIndex
	//------------------------------------------------------------------------//
	/**
	 * UseIndex()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	string	$strProperty	<description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function UseIndex($strProperty)
	{
		// use the index
	}
	
	//------------------------------------------------------------------------//
	// OrderBy
	//------------------------------------------------------------------------//
	/**
	 * OrderBy()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	string	$strProperty	<description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function OrderBy($strProperty)
	{
	
	}
	
	//------------------------------------------------------------------------//
	// Where
	//------------------------------------------------------------------------//
	/**
	 * Where()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	string	$strWhere	<description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function Where($strWhere)
	{
	
	}
}
?>
