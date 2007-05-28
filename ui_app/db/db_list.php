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
	public $_arrTables		= Array();
	public $_strName		= '';
	public $_arrResult		= Array();
	public $_arrRequest		= Array();
	public $_arrValid		= Array();
	public $_arrProperty	= Array();
	public $_intStatus		= 0;
	public $_arrOptions		= Array();
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
	 * @param	mixed	$mixTable		optional	Tables to load from
	 * @param	mixed	$mixColumns		optional	Columns to load
	 * @param	string	$strWhere		optional	WHERE Clause
	 * @param	array	$arrWhere		optional	WHERE Data
	 * 
	 * @return	DBList
	 *
	 * @method
	 */
	function __construct($strName, $mixTable=NULL, $mixColumns=NULL, $strWhere=NULL, $arrWhere=NULL, $intLimitStart=NULL, $intLimitCount=NULL)
	{
		// set name
		$this->_strName = $strName;
		
		// get config
		$this->_arrOptions = Config()->Get('Dbl', $strName);
		
		// set table
		if (is_array($mixTable))
		{
			$this->_arrTables = $mixTable;
		}
		elseif ($this->_arrOptions['Tables'])
		{
			$this->_arrTables = $this->_arrOptions['Tables'];
		}
		elseif($mixTable)
		{
			//TODO!!!! convert table names into an array
		}
		else
		{
			// as a last resort use the dbl name as the table name
			$this->_arrTables[$strName]['Table'] = $strName;
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
		
		// set limit
		if (!is_null($intLimitStart))
		{
			$this->_intLimitStart = $intLimitStart;
		}
		else
		{
			$this->_intLimitStart = $this->_arrOptions['LimitStart'];
		}
		
		if (!is_null($intLimitCount))
		{
			$this->_intLimitCount = $intLimitCount;
		}
		else
		{
			$this->_intLimitCount = $this->_arrOptions['LimitCount'];
		}
		
		// set up where object
		if (!is_null($strWhere))
		{
			$this->objWhere 	= new DbWhere($strWhere, $arrWhere);
		}
		else
		{
			$this->objWhere 	= new DbWhere($this->_arrOptions['Where'], $this->_arrOptions['WhereData']);
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
		if ($arrResult = $this->_db->Select($this->_arrTables, $this->_arrColumns, $this->objWhere, $this->intLimitStart, $this->intLimitCount))
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
			$this->arrDataArray[$this->_intCount] = new DBObject($this->_strName, $this->_arrTables, $this->arrColumns);

			$this->arrDataArray[$this->_intCount]->_arrResult	= $arrRecord;
			$this->arrDataArray[$this->_intCount]->LoadProperties($arrRecord);
			
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