<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// DB_ACCESS
//----------------------------------------------------------------------------//
/**
 * DB_ACCESS
 *
 * Handles DB interaction
 *
 * Handles DB interaction.  Currently limited to MySQL
 *
 * @file		db_access.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// DataAccess
//----------------------------------------------------------------------------//
/**
 * DataAccess
 *
 * Provides connection to the MySQL server
 *
 * Provides connection to the MySQL server
 *
 *
 * @prefix		dba
 *
 * @package		framework
 * @class		DataAccess
 */
 class DataAccess
 {
 	//------------------------------------------------------------------------//
	// refMysqliConnection	
	//------------------------------------------------------------------------//
	/**
	 * refMysqliConnection
	 *
	 * Database reference for mysqli
	 *
	 * Database reference for mysqli
	 *
	 * @type		Reference
	 *
	 * @property
	 * @see			<MethodName()||typePropertyName>
	 */
	public $refMysqliConnection;
	
 	//------------------------------------------------------------------------//
	// DataAccess() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * DataAccess()
	 *
	 * Constructor for DataAccess
	 *
	 * Constructor for DataAccess

	 * @return		void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function __construct()
	{
		// Connect to MySQL database
		$refMysqliConnection = new mysqli(DATABASE_URL, DATABASE_USER, DATABASE_PWORD, DATABASE_NAME);
		
		// Make sure the connection was successful
		if(mysqli_connect_errno())
		{
			// TODO: Make custom DatabaseException();
			throw new Exception();
		}
	}
 }
 
//----------------------------------------------------------------------------//
// DatabaseAccess
//----------------------------------------------------------------------------//
/**
 * DatabaseAccess
 *
 * Database Access Abstract Base Class
 *
 * Database Access Abstract Base Class
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
	function __construct()
	{
		// connect to database if not already connected
		if (!$_GLOBALS['dbaDatabase'] || !is_a($_GLOBALS['dbaDatabase'], "DataAccess"))
		{
			$_GLOBALS['dbaDatabase'] = "hi world";
			//$_GLOBALS['dbaDatabase'] = new DataAccess();
		}
		
		// make global database object available
		$this->db = &$_GLOBALS['dbaDatabase'];
	}
 }
 
//----------------------------------------------------------------------------//
// Statement
//----------------------------------------------------------------------------//
/**
 * Statement
 *
 * Statement Abstract Base Class
 *
 * Statement Abstract Base Class
 *
 *
 * @prefix		bst
 *
 * @package		framework
 * @class		Statement
 */
 abstract class Statement extends DatabaseAccess
 {
  	//------------------------------------------------------------------------//
	// stmtSqlStatement	
	//------------------------------------------------------------------------//
	/**
	 * stmtSqlStatement
	 *
	 * Stores our statement
	 *
	 * Stores our statement
	 *
	 * @type		mysql_stmt
	 *
	 * @property
	 * @see			<MethodName()||typePropertyName>
	 */
	private $_stmtSqlStatment;

 	//------------------------------------------------------------------------//
	// Statement() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * Statement()
	 *
	 * Constructor for Statement
	 *
	 * Constructor for Statement Abstract Base Class - This should never run!

	 * @return		void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function __construct()
	 {
	 	// TODO: Make custom AbstractException()
	 	//throw new Exception();
		parent::__construct();
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
	 * Replaces the string
	 *
	 * @param		string	$strColumn		ColumnName to be checked
	 * 
	 * @return		array					Indexed Array of aliases.
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function FindAlias($strString)
	{
		$arrAliases = array();

		// Find Aliases
		ereg("<[0-9a-zA-Z]*>", $strString, $arrAliases);

		// String replace all aliases with ?'s
		ereg_replace("<[0-9a-zA-Z]*>", "?", $strString);
		
		return $arrAliases;
	}
	
	
	function IsAssociativeArray($arrArray) 
	{
	   return (is_array($arrArray) && !is_numeric(implode(array_keys($arrArray))));
	}
 }


//----------------------------------------------------------------------------//
// StatementSelect
//----------------------------------------------------------------------------//
/**
 * StatementSelect
 *
 * SELECT Query
 *
 * Implements a SELECT query using mysqli statements
 *
 *
 * @prefix		sel
 *
 * @package		framework
 * @class		StatementSelect
 */
 class StatementSelect extends Statement
 {
 
	//------------------------------------------------------------------------//
	// StatementSelect() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * StatementSelect()
	 *
	 * Constructor for StatementSelect object
	 *
	 * Constructor for StatementSelect object
	 *
	 * @param		array	arrTables		Indexed array of tables to select from.
	 * 										Format of each of the strings is
	 * 										"TableName [JOIN Table2Name ON... etc.]"
	 * @param		array	arrColumns		Can be either associative or indexed array.
	 * 										Use indexed for normal column referencing.
	 * 										Use associated arrays for either renaming of
	 * 										columns (eg. ["ColumnName"] = "ColumnAlias") and
	 * 										special SQL funcion calls (eg. ["NOW()"] = "NowAlias")
	 * @param		string	strWhere		A full SQL WHERE clause, minus the keyword.
	 * 										Paramaters should be aliased in a meaningful
	 * 										fashion enclosed in <>'s
	 * 										(eg. "FooBar = <FooBar>")
	 * @param		string	strOrder		A full SQL ORDER BY clause, minus the keywords
	 * 										(eg. "ColumnName ASC, Column2Name")
	 * @param		string	strLimit		SQL LIMIT clause, minus the keyword
	 * 										(eg. "5") - Return first 5 rows
	 * 										(eg. "5,10") - Return rows 6-15
	 * @return		void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function __construct($arrTables, $mixColumns, $strWhere = "", $strOrder = "", $strLimit = "")
	{
		parent::__construct();
		// Compile the query from our passed info
	 	$strQuery = "SELECT ";
	 	
	 	if (is_string($mixColumns))
	 	{
	 		// $mixColumns is just a string, therefore only one column selected
	 		$strQuery .= $mixColumns . "\n";
	 	}
 		elseif ($this->IsAssociativeArray($mixColumns))

 		{
			// If arrColumns is associative, then add keys and values with "AS" between them
			reset($mixColumns);
			
		 	// Add the columns 	
		 	while (key($mixColumns) != null)
		 	{
		 		$strQuery .= key($mixColumns);
		 		
		 		// If this column has an AS alias
		 		if (current($mixColumns) != "")
		 		{
		 			$strQuery .= " AS " . current($mixColumns);
		 		}
		 				 		
		 		next($mixColumns);
		 		
		 		// If this isn't the last item in the array, add a comma
		 		if (key($mixColumns) != null)
		 		{
		 			$strQuery .= ", ";
		 		}
		 		
		 		$strQuery .= "\n";
		 	}
 		}
 		elseif (is_array($mixColumns))
 		{
 			// If it's an indexed array
 			reset($mixColumns);
 			
		 	// Add the columns 	
		 	while (key($mixColumns) != null)
		 	{
		 		$strQuery .= current($mixColumns);
	 		
		 		next($mixColumns);
		 		
		 		// If this isn't the last item in the array, add a comma
		 		if (key($mixColumns) != null)
		 		{
		 			$strQuery .= ", ";
		 		}
		 	}
		 	
		 	$strQuery .= "\n";
 		}
 		else
 		{
 			// We have an invalid type, so throw an exception
 			throw new Exception();
 		}



	 	// Add the FROM line
	 	$strQuery .= "FROM ";
	 	reset($arrTables);
	 	// Add the tables into the query
	 	while (key($arrTables) != null)
	 	{
	 		$strQuery .= current($arrTables);
 		
	 		next($arrTables);
	 		
	 		// If this isn't the last item in the array, add a comma
	 		if (key($arrTables) != null)
	 		{
	 			$strQuery .= ", ";
	 		}
	 	}
	 	
	 	$strQuery .= "\n";
	 	
	 	
	 	
	 	// Add the WHERE line
	 	if ($strWhere != "")
	 	{
	 		//
	 		if (!strcasecmp(substr($strWhere, 0, 5)))
	 		{
	 			
	 		}
	 	}
	 	
	}

	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Executes the StatementSelect, with a new set of values
	 *
	 * Executes the StatementSelect, with a new set of values
	 *
	 * @param		array	arrWhere		Associative array of parameters for the WHERE clause.
	 * 										MUST use the same aliases as used when the object was
	 * 										created.  Key string is the alias (ignoring the <>'s)
	 * 										, and the Value is the value to be inserted.
	 * 
	 * @return		void
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function Execute($arrData)
	 {
	 	// TODO
	 }

	//------------------------------------------------------------------------//
	// Fetch()
	//------------------------------------------------------------------------//
	/**
	 * Fetch()
	 *
	 * Fetches the next row of data
	 *
	 * Fetches the next row of data from the resultset as an Associative Array
	 *
	 * @return		array					Associative array of columns and values
	 * 										Key is the ColumnName, value is its value
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function Fetch()
	{
	 	// TODO
	}
	
	
	//------------------------------------------------------------------------//
	// FetchAll()
	//------------------------------------------------------------------------//
	/**
	 * FetchAll()
	 *
	 * Fetches the resultset
	 *
	 * Fetches the entire resultset as an Indexed array of Associated arrays
	 *
	 * @return		array					Indexed array of Associated arrays
	 * 										Associative arrays of columns and values
	 * 										Key is the ColumnName, value is its value
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function FetchAll()
	{
	 	// TODO
	}
 }
 
//----------------------------------------------------------------------------//
// StatementInsert
//----------------------------------------------------------------------------//
/**
 * StatementInsert
 *
 * INSERT Query
 *
 * Implements an INSERT query using mysqli statements
 *
 *
 * @prefix		ins
 *
 * @package		framework
 * @class		InsertStatement
 */
 class StatementInsert extends Statement
 {
 
	//------------------------------------------------------------------------//
	// StatementInsert() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * StatementInsert()
	 *
	 * Constructor for StatementInsert object
	 *
	 * Constructor for StatementInsert object
	 *
	 * @param		string	strTable		Name of the table to insert into
	 *
	 * @return		void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function __construct($strTable)
	 {
	 	// TODO
	 }
	 
	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Executes the StatementInsert, with a new set of values
	 *
	 * Executes the StatementInsert, with a new set of values
	 *
	 * @param		array	arrData			Indexed array of the data to be inserted
	 * 										Assumed that this data is in the correct order
	 * 
	 * @return		boolean					true	: Insert successful
	 * 										false	: Insert failed
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function Execute($arrData)
	 {
	 	// TODO
	 }
 }


//----------------------------------------------------------------------------//
// StatementUpdate
//----------------------------------------------------------------------------//
/**
 * StatementUpdate
 *
 * UPDATE Query
 *
 * Implements an UPDATE query using mysqli statements
 *
 *
 * @prefix		upd
 *
 * @package		framework
 * @class		StatementUpdate
 */
 class StatementUpdate extends Statement
 {
 
	//------------------------------------------------------------------------//
	// StatementUpdate() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * StatementUpdate()
	 *
	 * Constructor for StatementUpdate object
	 *
	 * Constructor for StatementUpdate object
	 *
	 * @param		string	strTable		Name of the table to update
	 * @param		string	strWhere		A full SQL WHERE clause, minus the keyword.
	 * 										Paramaters should be aliased in a meaningful
	 * 										fashion enclosed in <>'s
	 * 										(eg. "FooBar = <FooBar>")
	 *
	 * @return		void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function __construct($strTable)
	 {
	 	// TODO
	 }
	 
	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Executes the StatementUpdate, with a new set of values
	 *
	 * Executes the StatementUpdate, with a new set of values
	 *
	 * @param		array	arrData			Associative array of data to be entered.
	 * 										Key is the Column name, value is the value
	 * 										to update with
	 * @param		array	arrWhere		Associative array of parameters for the WHERE clause.
	 * 										MUST use the same aliases as used when the object was
	 * 										created.  Key string is the alias (ignoring the <>'s)
	 * 										, and the Value is the value to be inserted.
	 * 
	 * @return		boolean					true	: Insert successful
	 * 										false	: Insert failed
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function Execute($arrWhere)
	 {
	 	// TODO
	 }
 }
?>
