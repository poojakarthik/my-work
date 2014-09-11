<?php

//----------------------------------------------------------------------------//
// DBWhere
//----------------------------------------------------------------------------//
/**
 * DBWhere
 *
 * Container for a Prepared Statement WHERE
 *
 * Container for a Prepared Statement WHERE.  Keeps the original string with placeholders
 * and the array linking placeholders to values in one place/object.
 *
 * @prefix	dbw
 *
 * @package	framework_ui
 * @class	DBWhere
 */
class DBWhere
{
	private $_strWhere	= '';
	private $_arrWhere	= Array();
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the DBWhere class
	 *
	 * Constructor for the DBWhere class
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
	 * Sets the WHERE array for the DBWhere object 
	 *
	 * Sets the WHERE array for the DBWhere object 
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
	 * Sets the WHERE clause string of the DBWhere object
	 *
	 * Sets the WHERE clause string of the DBWhere object
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
	 * 					If the object is currently storing an array of placeholders and values but not storing a WHERE clause string
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
				$arrWhere[] = "$strKey = <$strKey>";
			}
			$strWhere = trim(implode(" AND ", $arrWhere));
			return $strWhere;
		}

		return $this->_strWhere;
	}
	
	//------------------------------------------------------------------------//
	// Clean
	//------------------------------------------------------------------------//
	/**
	 * Clean()
	 *
	 * Empties the contents of the where clause
	 *
	 * Empties the contents of the where clause
	 *
	 * @return	void
	 * @method
	 */
	function Clean()
	{
		$this->_strWhere = '';
		$this->_arrWhere = Array();
	}
	
}

?>
