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
	 * 
	 * @return	mixed									FALSE: Query failed
	 * 													array: Result set as an Indexed Array of Result Rows
	 *
	 * @method
	 */
	function Select($strTable, $arrColumns=NULL, $objWhere=NULL, $intLimitStart=NULL, $strLimitCount=NULL)
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
		if ($intLimitStart)
		{
			$strLimit = "$intLimitStart";
			if ($strLimitCount)
			{
				$strLimit .= ", $strLimitCount";
			}
		}
		
	 	// Statement Construct, Execute, Fetch, Return :D
	 	$selStatement = new StatementSelect($strTable, $mixColumns, $objWhere->strWhere, NULL, $strLimit);
	 	$selStatement->Execute($objWhere->arrWhere);
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
	// __Construct
	//------------------------------------------------------------------------//
	/**
	 * __Construct()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	string	$strWhere	[optional] <description>
	 * @param	array	$arrWhere	[optional] <description>
	 * @return	void
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
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
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	string	$strWhere	[optional] <description>
	 * @param	array	$arrWhere	[optional] <description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
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
	
	function SetArray($arrWhere, $strValue=NULL)
	{
	
	}
	
	function SetString($strWhere)
	{
		$this->_strWhere = $strWhere;
	}
	
	function GetArray()
	{
		if (empty($this->_arrWhere))
		{
		
		}
		else
		{
			return $this->_arrWhere;
		}
	}
	
	function GetString()
	{
		return $this->_strWhere;
	}
}

?>
