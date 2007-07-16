<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// db_list.php
//----------------------------------------------------------------------------//
/**
 * db_list
 *
 * contains the DBList class which can represent multiple records of a table in the database
 *
 * contains the DBList class which can represent multiple records of a table in the database
 *
 * @file		db_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

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
 * @package	ui_app
 * @class	DBList
 * @extends	DBListBase
 */
class DBList extends DBListBase
{
	public $_objWhere;
	public $Where;
	public $_intLimitStart	= NULL;
	public $_intLimitCount	= NULL;
	public $_intMode 		= DBO_RETURN;
	public $_strIdColumn 	= 'Id';
	public $_strUseIndex 	= NULL;
	public $_strOrderBy 	= NULL;
	public $_arrColumns 	= Array();
	public $_strTable		= '';
	public $_strName		= '';
	public $_arrValid		= Array();
	public $_intStatus		= 0;
	public $_arrDefine		= Array();
	public $_db				= NULL;
	
	private $_intCount		= 0;
	
	
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
	 * @param	mixed	$strTable		[optional]	Database table to connect the data object to 
	 * @param	mixed	$mixColumns		[optional]	Columns to load
	 * @param	string	$strWhere		[optional]	WHERE Clause
	 * @param	array	$arrWhere		[optional]	WHERE Data
	 * 
	 * @return	DBList
	 *
	 * @method
	 */
	function __construct($strName, $strTable=NULL, $mixColumns=NULL, $strWhere=NULL, $arrWhere=NULL, $intLimitStart=NULL, $intLimitCount=NULL)
	{
		// Parent Constructor
		parent::__construct();
		
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
		$this->SetColumns($mixColumns);
		
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
		elseif ($this->_arrDefine['LimitStart'])
		{
			$this->_intLimitStart = $this->_arrDefine['LimitStart'];
		}
		else
		{
			$this->_intLimitStart = NULL;
		}
		
		if (!is_null($intLimitCount))
		{
			$this->_intLimitCount = $intLimitCount;
		}
		elseif ($this->_arrDefine['LimitCount'])
		{
			$this->_intLimitCount = $this->_arrDefine['LimitCount'];
		}
		else
		{
			$this->_intLimitCount = NULL;
		}
		
		// set USE INDEX
		$this->_strUseIndex = $this->_arrDefine['UseIndex'];
		
		// set ORDER BY
		$this->_strOrderBy = $this->_arrDefine['OrderBy'];
		
		// set up where object
		if (!is_null($strWhere))
		{
			$this->_objWhere 	= new DbWhere($strWhere, $arrWhere);
		}
		else
		{
			$this->_objWhere 	= new DbWhere($this->_arrDefine['Where'], $this->_arrDefine['WhereData']);
		}
		
		// set up a public ref to the where object
		$this->Where = $this->_objWhere;
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
	function Load($strWhere=NULL, $arrWhere=NULL, $intLimitCount=NULL, $intLimitStart=NULL)
	{
		// if WHERE parameters were passed then use them
		if ($strWhere || $arrWhere)
		{
			$this->_objWhere->Set($strWhere, $arrWhere);
		} 
		elseif (!$this->_objWhere->GetString())
		{
			// WHERE parameters have not been passed and a WHERE clause has not been predefined for this list so use the passed parameters
			
			//FIXME! The below line will currently not do anything because to get to this stage, both $strWhere and $arrWhere are equal to NULL
			//and DbWhere->Set does not do anything with a parameter if it is null
			$this->_objWhere->Set($strWhere, $arrWhere);
		}
		
		
		// setup limit, if one has been specified
		if ($intLimitCount || $intLimitStart)
		{
			$this->SetLimit($intLimitCount, $intLimitStart);
		}
		
		// empty records
		$this->EmptyRecords();
		
		if ($arrResult = $this->Select())
		{
			// load results into data objects
			foreach($arrResult AS $arrRow)
			{
				$this->AddRecord($arrRow);
			}
			return TRUE;
		}
		else
		{
			return FALSE;
		}
		
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
		if ($arrResult = parent::Select($this->_strTable, $this->_arrColumns, $this->_objWhere, $this->_intLimitStart, $this->_intLimitCount, $this->_strOrderBy, $this->_strUseIndex))
		{
			return $arrResult;
		}
		else
		{
			return FALSE;
		}
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
			$this->_arrDataArray[$this->_intCount] = new DBObject($this->_strName, $this->_strTable, $this->_arrColumns);

			// load data into object
			$this->_arrDataArray[$this->_intCount]->LoadData($arrRecord);
				
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
		$this->_arrDataArray = Array();
		
		// reset counter
		$this->_intCount = 0;
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// UseIndex
	//------------------------------------------------------------------------//
	/**
	 * UseIndex()
	 *
	 * sets the USE INDEX clause for the select statement
	 *
	 * sets the USE INDEX clause for the select statement
	 *
	 * @param	string	$strProperty	property to be used as the index
	 * @return	void
	 *
	 * @method
	 */
	function UseIndex($strProperty)
	{
		$this->_strUseIndex = $strProperty;
	}
	
	//------------------------------------------------------------------------//
	// OrderBy
	//------------------------------------------------------------------------//
	/**
	 * OrderBy()
	 *
	 * sets the ORDER BY clause for the select statement
	 *
	 * sets the ORDER BY clause for the select statement
	 *
	 * @param	string	$strProperty	property to be used for ordering
	 *									You should be able to use a list of comma
	 *									separated properties
	 * @return	void
	 *
	 * @method
	 */
	function OrderBy($strProperty)
	{
		$this->_strOrderBy = $strProperty;
	}
	
	//------------------------------------------------------------------------//
	// __set
	//------------------------------------------------------------------------//
	/**
	 * __set()
	 *
	 * Sets a value in the where array
	 *
	 * Sets a value in the where array
	 *
	 * @param	string		$strProperty	property name
	 * @param	mixed		$mixValue		property value
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __set($strProperty, $mixValue)
	{
		return ($this->_objWhere->$strProperty = $mixValue);
	}
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Gets a value from the where array
	 *
	 * Gets a value from the where array
	 *
	 * @param	string		$strProperty	property name
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	function __get($strProperty)
	{
		return $this->_objWhere->$strProperty;

	}
	
	//------------------------------------------------------------------------//
	// Info
	//------------------------------------------------------------------------//
	/**
	 * Info()
	 *
	 * return info about the DB object list
	 *
	 * return info about the DB object list
	 * 
	 * @return	array		associative array (ObjectUniqueId=>ObjectInfo)
	 *
	 * @method
	 */
	function Info()
	{
		$arrReturn = Array();
		foreach ($this->_arrDataArray as $objDBObject)
		{
			// the index of $arrReturn is the value of the DB object's unique Id column
			// or should it just be a linear array?
			$arrReturn[$objDBObject->_arrProperties[$objDBObject->_strIdColumn]] = $objDBObject->Info();
		}
		
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// ShowInfo
	//------------------------------------------------------------------------//
	/**
	 * ShowInfo()
	 *
	 * Formats info about the DB object list so that it can be displayed
	 *
	 * Formats info about the DB object list so that it can be displayed
	 * 
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
	 *												used to define how far the object list's 
	 *												info should be tabbed.
	 * @return	string								returns the object list's info as a formatted string.
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
	 * @param	string		$strTabs	[optional]	a string containing tab chars '\t'
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
	
	//------------------------------------------------------------------------//
	// SetTable
	//------------------------------------------------------------------------//
	/**
	 * SetTable()
	 *
	 * Set the table associated with this DBList
	 *
	 * Set the table associated with this DBList
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
	// RecordCount
	//------------------------------------------------------------------------//
	/**
	 * RecordCount()
	 *
	 * Returns the number of records in the DBList
	 *
	 * Returns the number of records in the DBList
	 * 
	 * @return	int					number of records in the DBList
	 *
	 * @method
	 */
	function RecordCount()
	{
		return $this->_intCount;
	}


}
?>
