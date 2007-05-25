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
			$this->objWhere 	= new aphplix_DbWhere($strWhere, $arrWhere);
		}
		else
		{
			$this->objWhere 	= new aphplix_DbWhere($this->_arrOptions['Where'], $this->_arrOptions['WhereData']);
		}
	}
}
?>