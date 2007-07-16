<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// db_access_ui
//----------------------------------------------------------------------------//
/**
 * db_access_ui
 *
 * DB Access Interface for the UIs
 *
 * DB Access Interface for the UIs
 *
 * @file		db_access_ui.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// DataAccessUI
//----------------------------------------------------------------------------//
/**
 * DataAccessUI
 *
 * DB Access Interface for the UIs
 *
 * DB Access Interface for the UIs
 *
 *
 * @prefix	dbi
 *
 * @package	framework_ui
 * @class	DataAccessUI
 * @extends	DatabaseAccess
 */
class DataAccessUI extends DatabaseAccess
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * DataAccessUI Constructor
	 *
	 * DataAccessUI Constructor
	 * 
	 * @return	DataAccessUI
	 *
	 * @method
	 */
	function __construct()
	{
		// parent constructor
		parent::__construct();
	}
	
	
	//------------------------------------------------------------------------//
	// SelectById
	//------------------------------------------------------------------------//
	/**
	 * SelectById()
	 *
	 * Performs a MySQLi SELECT using only the field's Id as a restraint
	 *
	 * Performs a MySQLi SELECT using only the field's Id as a restraint
	 *
	 * @param	array	$strTable					Table name we want to use
	 * @param	array	$arrColumns					Can be either associative or indexed array.
	 * 												Use indexed for normal column referencing.
	 * 												Use associated arrays for either renaming of
	 * 												columns (eg. ["ColumnAlias"] = "ColumnName") and
	 *		 										special SQL funcion calls (eg. ["NowAlias"] = new MySQLFunction("NOW()"))
	 * @param	integer	$intId						Unique Id for this data record
	 * 
	 * @return	mixed								FALSE: Query failed
	 * 												array: Result row as Associative Array
	 *
	 * @method
	 */
	 function SelectById($strTable, $arrColumns, $intId)
	 {
	 	// Make sure we are actually selecting something
		if (!$arrColumns)
		{
			$arrColumns = "*";
		}
	 
	 	// Convert SelectById parameters to StatementSelect equivelants
	 	$intId = (int)$intId;
	 	
		// TODO: Generate WHERE based on Foreign Key Joins.  Just force to only Id for now.
		$strWhere = "Id = <Id>";
		$arrWhere = Array();
		$arrWhere['Id']	= $intId;
	 	
	 	// Statement Construct, Execute, Fetch, Return :D
	 	$selStatement = new StatementSelect($strTable, $arrColumns, $strWhere, NULL, 1);
	 	$selStatement->Execute($arrWhere);
	 	return $selStatement->Fetch();
	 }
	 
	 
	//------------------------------------------------------------------------//
	// Select
	//------------------------------------------------------------------------//
	/**
	 * Select()
	 *
	 * Performs a MySQLi SELECT
	 *
	 * Performs a MySQLi SELECT
	 *
	 * @param	array		$strTable					Name of table to select from
	 * @param	array		$arrColumns					Can be either associative or indexed array.
	 * 													Use indexed for normal column referencing.
	 * 													Use associated arrays for either renaming of
	 * 													columns (eg. ["ColumnAlias"] = "ColumnName") and
	 *		 											special SQL funcion calls (eg. ["NowAlias"] = new MySQLFunction("NOW()"))
	 * @param	VixenWhere	$objWhere					Unique Id for this data record
	 * @param	integer		$intLimitStart	optional	Starting row for Result Set
	 * @param	integer		$intLimitCount	optional	Number of rows in Result Set
	 * @param	string		$strOrderBy		optional	SQL ORDER BY clause
	 * 
	 * @return	mixed									FALSE: Query failed
	 * 													array: Result set as an Indexed Array of Result Rows
	 *
	 * @method
	 */
	function Select($strTable, $arrColumns=NULL, $objWhere=NULL, $intLimitStart=NULL, $intLimitCount=NULL, $strOrderBy=NULL, $strUseIndex=NULL)
	{
		// Create SELECT clause
		if (!$arrColumns)
		{
			// default to select *
			$mixColumns = "*";
		}
		else
		{
			$mixColumns = $arrColumns;
		}
		
		// LIMIT clause
		$strLimit = NULL;
		if ($intLimitStart !== NULL)
		{
			$strLimit = "$intLimitStart";
			if ($intLimitCount)
			{
				$strLimit .= ", $intLimitCount";
			}
		}
		elseif ($intLimitCount)
		{
			$strLimit = "0, $intLimitCount";
		}
		
		// set "USE INDEX" if we were passed an index
		if ($strUseIndex)
		{
			$strTable = "$strTable USE INDEX ($strUseIndex)";
		}

	 	// Statement Construct, Execute, Fetch, Return :D
	 	$selStatement = new StatementSelect($strTable, $mixColumns, $objWhere->GetString(), $strOrderBy, $strLimit);
	 	$selStatement->Execute($objWhere->GetArray());
	 	return $selStatement->FetchAll();
	}
	
	//------------------------------------------------------------------------//
	// UpdateById
	//------------------------------------------------------------------------//
	/**
	 * UpdateById()
	 *
	 * Updates a Database entry by its Unique Id
	 *
	 * Updates a Database entry by its Unique Id
	 *
	 * @param	string		$strTable					Table to update
	 * @param	array		$arrColumns					Columns to update
	 * @param	array		$arrData	 				Data to update with
	 * 
	 * @return	mixed									integer	: Number of rows affected
	 * 													FALSE	: Error
	 *
	 * @method
	 */
	function UpdateById($strTable, $arrColumns, $arrData)
	{
		// run query
		if (!is_array($arrColumns))
		{
			$arrColumns = NULL;
		}
	 	$ubiUpdate = new StatementUpdateById($strTable, $arrColumns);
	 	return $ubiUpdate->Execute($arrData);
	}
	
	//------------------------------------------------------------------------//
	// Insert
	//------------------------------------------------------------------------//
	/**
	 * Insert()
	 *
	 * Inserts a record into the Database
	 *
	 * Inserts a record into the Database
	 *
	 * @param	string	$strTable			Table to insert into
	 * @param	array	$arrColumns			Columns to set values for
	 * @param	array	$arrData			Data to insert
	 * 
	 * @return	mixed						FALSE	: Failed
	 * 										integer	: Id of the new record
	 *
	 * @method
	 */
	function Insert($strTable, $arrColumns, $arrData)
	{	
		if (!is_array($arrColumns))
		{
			$arrColumns = NULL;
		}
		$insInsert = new StatementInsert($strTable, $arrColumns);
		return $insInsert->Execute($arrData);
	}
}



//----------------------------------------------------------------------------//
// DbWhere
//----------------------------------------------------------------------------//
/**
 * DbWhere
 *
 * Container for a Prepared Statement WHERE
 *
 * Container for a Prepared Statement WHERE.  Keeps the original string with placeholders
 * and the array linking placeholders to values in one place/object.
 *
 * @prefix	dbw
 *
 * @package	framework_ui
 * @class	DbWhere
 */
class DbWhere
{
	private $_strWhere	= '';
	private $_arrWhere	= Array();
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the DbWhere class
	 *
	 * Constructor for the DbWhere class
	 *
	 * @param	string	$strWhere	[optional] SQL WHERE clause with named placeholders for values
	 * @param	array	$arrWhere	[optional] WHERE clause placeholder associative array (placeholder => value)
	 * @return	void
	 *
	 * @method
	 */
	function __construct($strWhere=NULL, $arrWhere=NULL)
	{
		if ($strWhere)
		{
			$this->_strWhere = $strWhere;
		}
		
		if (is_array($arrWhere) || is_object($arrWhere))
		{
			$this->_arrWhere = $arrWhere;
		}
	}
	
	//------------------------------------------------------------------------//
	// Set
	//------------------------------------------------------------------------//
	/**
	 * Set()
	 *
	 * Sets the WHERE string and WHERE array for the object
	 *
	 * Sets the WHERE string and WHERE array for the object
	 *
	 * @param	string	$strWhere	[optional] SQL WHERE clause with named placeholders for values
	 * @param	array	$arrWhere	[optional] WHERE clause placeholder associative array (placeholder => value)
	 * @return	void
	 *
	 * @method
	 */
	function Set($strWhere=NULL, $arrWhere=NULL)
	{
		if (!is_null($strWhere))
		{
			$this->_strWhere = $strWhere;
		}
		
		if (is_array($arrWhere) || is_object($arrWhere))
		{
			$this->_arrWhere = $arrWhere;
		}
	}
	
	//------------------------------------------------------------------------//
	// __set
	//------------------------------------------------------------------------//
	/**
	 * __set()
	 *
	 * Modifier for the value of a specified placeholder
	 *
	 * Modifier for the value of a specified placeholder
	 *
	 * @param	string	$strProperty	the placeholder to modify the value of
	 * @param	array	$strValue		the new value to associate with the placeholder
	 * @return	void
	 *
	 * @method
	 */
	function __set($strProperty, $strValue)
	{
		$this->_arrWhere[$strProperty] = $strValue;
	}
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Accessor for the value of a specified placeholder
	 *
	 * Accessor for the value of a specified placeholder
	 *
	 * @param	string	$strProperty	the placeholder to access the value of
	 * @return	string					the value associated with the placeholder
	 *
	 * @method
	 */
	function __get($strProperty)
	{
		return $this->_arrWhere[$strProperty];
	}
	
	//------------------------------------------------------------------------//
	// SetArray
	//------------------------------------------------------------------------//
	/**
	 * SetArray()
	 *
	 * Sets the WHERE array for the DbWhere object 
	 *
	 * Sets the WHERE array for the DbWhere object 
	 *
	 * @param	array	$arrWhere	WHERE clause placeholder associative array (placeholder => value)
	 * @return	void
	 *
	 * @method
	 */
	function SetArray($arrWhere)
	{
		$this->_arrWhere = $arrWhere;
	}
	
	//------------------------------------------------------------------------//
	// SetString
	//------------------------------------------------------------------------//
	/**
	 * SetString()
	 *
	 * Sets the WHERE clause string of the DbWhere object
	 *
	 * Sets the WHERE clause string of the DbWhere object
	 *
	 * @param	string	$strWhere	SQL WHERE clause with named placeholders for values
	 * @return	void
	 *
	 * @method
	 */
	function SetString($strWhere)
	{
		$this->_strWhere = $strWhere;
	}
	
	//------------------------------------------------------------------------//
	// GetArray
	//------------------------------------------------------------------------//
	/**
	 * GetArray()
	 *
	 * Retrieves the WHERE clause placeholder associative array
	 *
	 * Retrieves the WHERE clause placeholder associative array
	 *
	 * @return	array	WHERE clause placeholder associative array (placeholder => value)
	 *
	 * @method
	 */
	function GetArray()
	{
		return $this->_arrWhere;
	}
	
	//------------------------------------------------------------------------//
	// GetString
	//------------------------------------------------------------------------//
	/**
	 * GetString()
	 *
	 * Retrieves the SQL WHERE clause with named placeholders for values 
	 *
	 * Retrieves the SQL WHERE clause with named placeholders for values
	 *
	 * @return	string	Compiled SQL WHERE clause with named placeholders for values.
	 * 					If the object is currently storing an array of placeholders and values
	 *					then this return string is compiled from that, with each individual condition being ANDed together.  
	 *					Else it returns the stored SQL WHERE clause string.
	 * @method
	 */
	function GetString()
	{
		if (!trim($this->_strWhere))
		{
			$arrWhere = Array();
			foreach($this->_arrWhere AS $strKey=>$strValue)
			{
				$arrWhere[] = "$strKey = $strValue"; 
			}
			$strWhere = trim(implode(" AND ", $arrWhere));
			return $strWhere;
		}

		return $this->_strWhere;
	}
}

?>
