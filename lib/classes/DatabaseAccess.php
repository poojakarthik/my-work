<?php

//----------------------------------------------------------------------------//
// DatabaseAccess
//----------------------------------------------------------------------------//
/**
 * DatabaseAccess
 *
 * Database Access Abstract Base Class
 *
 * Database Access Abstract Base Class
 * All database access classes (querys and statements) are based on
 * this class
 *
 *
 * @prefix		db
 *
 * @package		framework
 * @class		DatabaseAccess 
 */
 abstract class DatabaseAccess
 {
 	//------------------------------------------------------------------------//
	// intSQLMode	
	//------------------------------------------------------------------------//
	/**
	 * intSQLMode
	 *
	 * Stores the SQL mode
	 *
	 * Stores the SQL mode, SQL_QUERY or SQL_STATEMENT
	 *
	 * @type		int
	 *
	 * @property
	 */
	protected $intSQLMode;
	
 	//------------------------------------------------------------------------//
	// db	
	//------------------------------------------------------------------------//
	/**
	 * db
	 *
	 * reference to the global database connection object
	 *
	 * reference to the global database connection object
	 *
	 * @type		reference
	 *
	 * @property
	 */
	protected $db;
	
	public	$aProfiling	=	array
							(
								'aExecutions'		=> array(),
								'fPreparationStart'	=> null,
								'fPreparationTime'	=> null
							);

	//------------------------------------------------------------------------//
	// DatabaseAccess() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * DatabaseAccess()
	 *
	 * Constructor for DatabaseAccess
	 *
	 * Constructor for DatabaseAccess

	 * @return		void
	 *
	 * @method
	 */ 
	function __construct($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		// make global database object available
		$this->db = DataAccess::getDataAccess($strConnectionType);
		
		$this->db->addToProfiler($this);
	}
	
	//------------------------------------------------------------------------//
	// DatabaseAccess() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * DatabaseAccess()
	 *
	 * Constructor for DatabaseAccess
	 *
	 * Constructor for DatabaseAccess

	 * @return		void
	 *
	 * @method
	 */ 
	function Trace($strString)
	{
		return Trace("(".get_class($this).")\n".$strString, 'MySQL');
	}
	
	//------------------------------------------------------------------------//
	// IsColumnName()
	//------------------------------------------------------------------------//
	/**
	 * IsColumnName()
	 *
	 * Checks validity of ColumnName
	 *
	 * Checks validity of ColumnName.  Used to tell if string is a standard
	 * column name or something else (eg. SQL function or alias)
	 *
	 * @param		string	$strColumn		ColumnName to be checked
	 * 
	 * @return		boolean					true	: string is a ColumnName
	 * 										false	: string is not a ColumnName
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function IsColumnName($strColumn)
	{
		return (eregi("A-Z0-9", $strColumn) == strlen($strColumn)) ? true : false;
	}
	

	//------------------------------------------------------------------------//
	// FindAlias()
	//------------------------------------------------------------------------//
	/**
	 * FindAlias()
	 *
	 * Finds aliases in a string
	 *
	 * Searches a string, and pulls out an array of aliases bounded by <>'s.
	 * Replaces the string with a ?
	 *
	 * @param		string	$strColumn		ColumnName to be checked
	 * 
	 * @return		array					Indexed Array of aliases.
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function FindAlias(&$strString)
	{
		$arrAliases = array();
		$arrMatches = array();

		// Find Aliases
		preg_match_all ("/<([\d\w]+)>/misU", $strString, $arrAliases, PREG_SET_ORDER);
		// String replace all aliases with ?'s
		$strString = preg_replace("/<([\d\w]+)>/misU", "?", $strString);
		
		// Remove <>'s from alias names
		$i = 0;
		foreach ($arrAliases as $arrAlias)
		{
			$arrMatches[$i++] = $arrAlias [1];
		}
		
		return $arrMatches;
	}
	
	//------------------------------------------------------------------------//
	// StripTable()
	//------------------------------------------------------------------------//
	/**
	 * StripTable()
	 *
	 * Strips the table from a string in "TableName.ColumnName" format
	 *
	 * Strips the table from a string in "TableName.ColumnName" format
	 *
	 * @param		string	$strText		String to parse
	 * 
	 * @return		string					Stripped table name
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function StripTable($strText) 
	{
		$strText = substr($strText, 0, (strpos($strText, ".") + 1));
		if ($strText == "")
		{
			// There was no table name
			return false;
		}
		return $strText;
	}
	
	//------------------------------------------------------------------------//
	// IsAssociativeArray()
	//------------------------------------------------------------------------//
	/**
	 * IsAssociativeArray()
	 *
	 * Determines if a passed array is associative or not
	 *
	 * Determines if a passed array is associative or not
	 *
	 * @param		array	$arrArray		Array to be checked
	 * 
	 * @return		boolean					true	: Associative array
	 * 										false	: Indexed array
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function IsAssociativeArray($arrArray) 
	{
		return IsAssociativeArray($arrArray);
	}
	
	//------------------------------------------------------------------------//
	// Error()
	//------------------------------------------------------------------------//
	/**
	 * Error()
	 *
	 * Return an SQL error message
	 *
	 * Returns the latest SQL error message
	 *
	 * @return		string					SQL Error Message
	 *
	 * @method
	 */ 
	function Error()
	{
		if (mysqli_error($this->db->refMysqliConnection))
		{
			$strReturn = mysqli_error($this->db->refMysqliConnection);
			$strReturn .= "\n Call Stack:\n".Backtrace(debug_backtrace())."\n";
			return $strReturn;
		}
		
		// There was no error
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// Debug()
	//------------------------------------------------------------------------//
	/**
	 * Debug()
	 *
	 * Outputs the lastest SQL error message
	 *
	 * Outputs the lastest SQL error message on backend applications
	 *
	 * @param		mixed					Data returned from the MySQLi Execute function
	 *
	 * @method
	 */ 
	function Debug()
	{
		if (defined("USER_NAME") && (USER_NAME != "Intranet_app"))
		{
			Debug($this->Error(), 'console');
		}
	}
	
	//------------------------------------------------------------------------//
	// PrepareWhere()
	//------------------------------------------------------------------------//
	/**
	 * PrepareWhere()
	 *
	 * Prepare an SQL WHERE clause
	 *
	 * Prepare an SQL WHERE clause
	 *
	 * @param		mixed	$mixWhere		string, array or object containing
	 *										details of the where clause
	 *
	 * @param		string	$strJoiner		optional joiner 'AND' or 'OR'. defaults to 'AND'
	 *										If $mixWhere is an array, $strJoiner
	 *										is used to join the array elements					 
	 *
	 * @param		string	$strOperator	optional operator '=', '<', '>', 'LIKE' etc. defaults to '='
	 *										If $mixWhere is an array, $strOperator
	 *										is used as an operator with the array elements					 
	 *
	 * @return		string					SQL WHERE clause (including the WHERE keyword)
	 *
	 * @method
	 */ 
	function PrepareWhere($mixWhere, $strJoiner = 'AND', $strOperator = '=')
	{
		// set default operator
		$strDefaultOperator = $strOperator;
		
		// make a string
		if (is_string($mixWhere))
		{
			// input is a string
			// trim the WHERE keyword
			$strWhere = trim($mixWhere);
			if (strtolower(substr($strWhere, 0, 5)) == "where")
			{
				$strWhere = substr($strWhere, 6);
			}
		}
		elseif( is_array($mixWhere) || is_object($mixWhere))
		{
			// input is an array or object
			if (is_object($mixWhere))
			{
				// we can only use objects with an 'Iterator' interface
				$arrImplements = class_implements($mixWhere);
				if (!$arrImplements['Iterator'])
				{
					// return nothing
					return "";
				}
			}
			
			// add each element
			foreach($mixWhere as $strKey=>$strValue)
			{
				// set column to key
				$strColumn = $strKey;
				
				// set orerator to default value
				$strOperator		= $strDefaultOperator;
				
				// check & modify value
				if (is_array($strValue))
				{
					// we may have been passed an array as value
					if ($strValue['Operator'])
					{
						// get the operator if available
						$strOperator	= $strValue['Operator'];
					}
					if ($strValue['Column'])
					{
						// get the key if available
						$strColumn	= $strValue['Column'];
					}
					
					$strValue		= $strValue['Value'];
				}
				if ($this->intSQLMode == SQL_STATEMENT)
				{
					// prepared statement constructors don't use the value
					$strValue		= "<$strKey>";
				}
			
				// add element
				$arrWhere[] = "$strColumn $strOperator $strValue";
			}
			
			// join elements
			if (is_array($arrWhere))
			{
				$strWhere = implode(" $strJoiner ", $arrWhere);
			}
		}
		else
		{
			// return an empty string if we have nothing
			return "";
		}
		
		// trim the WHERE clause string
		$strWhere = trim($strWhere);
		
		// return an empty string if we have nothing
		if (!$strWhere)
		{
			return "";
		}
		
		// add the WHERE keyword
		$strWhere = " WHERE $strWhere ";
		
		// return the WHERE clause
		return $strWhere;
	}
	
	//------------------------------------------------------------------------//
	// ImplodeTables
	//------------------------------------------------------------------------//
	/**
	 * ImplodeTables()
	 *
	 * Implodes an array of tables to a FROM string
	 *
	 * Implodes an array of tables to a FROM string
	 *
	 * @param	array	$arrTables		Associative array of tables to implode
	 * @return	string					FROM clause (without the FROM keyword)
	 *
	 * @method
	 */
	function ImplodeTables($arrTables)
	{
		$arrReturn = Array();
		
		if (!is_array($arrTables))
		{
			return FALSE;
		}
		
		foreach ($arrTables AS $strAlias=>$arrTable)
		{
			// Name & Alias
			if ($strAlias != $arrTable['Table'])
			{
				$strTable = "{$arrTable['Table']} {$this->_strAs} {$strAlias}";
			}
			else
			{
				$strTable = $arrTable['Table'];
			}
			
			// Index
			if ($arrTable['Index'])
			{
				$strTable .= " {$this->_UseIndex} ({$arrTable['Index']})";
			}
			
			// add to return array
			$arrReturn[] = $strTable;
		}
		
		return implode(', ', $arrReturn);
	}

	public function FetchCleanOblib($strTableName, $oblobjPushObject)
	{
		$this->db->FetchCleanOblib($strTableName, $oblobjPushObject);
	}

	function EscapeString($strString)
	{
		return $this->db->refMysqliConnection->real_escape_string($strString);
	}
}

?>
