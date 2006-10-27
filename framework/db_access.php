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
	// arrTableDefine
	//------------------------------------------------------------------------//
	/**
	 * arrTableDefine
	 *
	 * Database table Definitions
	 *
	 * Database table Definitions
	 *
	 * @type		array
	 *
	 * @property
	 */
	public $arrTableDefine;
	
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
		$this->refMysqliConnection = new mysqli(DATABASE_URL, DATABASE_USER, DATABASE_PWORD, DATABASE_NAME);
		
		// Make sure the connection was successful
		if(mysqli_connect_errno())
		{
			// TODO: Make custom DatabaseException();
			throw new Exception();
		}
		
		// make global database definitions available
		$this->arrTableDefine = &$_GLOBALS['arrDatabaseTableDefine'];
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
// Query
//----------------------------------------------------------------------------//
/**
 * Statement
 *
 * Query Class
 *
 * Query Class
 *
 *
 * @prefix		qry
 *
 * @package		framework
 * @class		Query
 */
 class Query extends DatabaseAccess
 {
 	//------------------------------------------------------------------------//
	// Query() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * Query()
	 *
	 * Constructor for Query
	 *
	 * Constructor for Query Class
	 *
	 * @return		void
	 *
	 * @method
	 */ 
	 function __construct()
	 {
		parent::__construct();
	 }
 }

//----------------------------------------------------------------------------//
// QueryCreate
//----------------------------------------------------------------------------//
/**
 * QueryCreate
 *
 * CREATE Query
 *
 * Implements a CREATE query using mysqli
 *
 *
 * @prefix		crq
 *
 * @package		framework
 * @class		QueryCreate
 */
 class QueryCreate extends Query
 {
 	function __construct()
	{
		parent::__construct();
	}
		
 	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Executes the Query
	 *
	 * Executes the Query
	 *
	 * @param		mixed	mixTable		string containing name of the table to create
	 * 										or an array of table names to create.
	 * 
	 * @return		bool
	 * @method
	 */ 
	 function Execute($mixTable)
	 {
	 	// check what we were given
		if (!$mixTable)
		{
			return FALSE;
		}
		elseif (is_string($mixTable))
		{
			// convert string to array
			$arrTables = Array(mixTable);
		}
		elseif (is_array($mixTable))
		{
			$arrTables = $mixTable;
		}
		else
		{
			return FALSE;
		}
		
		// by default we return TRUE
		$bolReturn = TRUE;
		
		// create tables
		foreach($arrTables as $strTableName)
		{
			// check that table def exists
			if (is_array($this->db->arrTableDefine[$strTableName]))
			{
				// build query
				$strQuery = "";
				//TODO!!!!
				
				/* CREATE TABLE `{$structure['name']}` (
				 *		`{$structure['serial']}`	bigint	NOT NULL	auto_increment,
				 *		`{$colmn['name']}` {$colmn['type']} {$colmn['attributes']} {$colmn['null']} DEFAULT '{$colmn['default']}',
				 *		...
				 *
				 * INDEX	(`{$index[n]}`, `{$index[n++]}`),
				 * UNIQUE	(`{$unique[n]}`, `{$unique[n++]}`),
				 * PRIMARY KEY	(`{$structure['serial']}`)
				 * ) TYPE = {$structure['type']}
				 *
				 * $structure['id']		defaults to	'id'
				 * $structure['type']		defaults to	'MYISAM'
				 *
				 * $structure['name'] 				= 'table_name';
				 * $structure['colmn'][n]['name'] 	= 'colmn1';
				 * $structure['colmn'][n]['type'] 	= 'varchar(10)';
				 * $structure['colmn'][n]['null'] 	= TRUE;
				 * $structure['sql'][]	 			= SQL QUERY;
				 * $structure['data']				= standard data array to be inserted
		 */
				
				
				
				
				// run query
				$mixReturn = mysqli_query($this->db->refMysqliConnection, $strQuery);
				
				// check result
				if ($mixReturn !== TRUE)
				{
					// we will return false
					$bolReturn = FALSE;
				}
			}
			else
			{
				// we will return false
				$bolReturn = FALSE;
			}
		}
		
		return $bolReturn;
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
	// arrWhereAliases	
	//------------------------------------------------------------------------//
	/**
	 * arrWhereAliases
	 *
	 * Stores the WHERE aliases
	 *
	 * Stores the WHERE aliases
	 *
	 * @type		array
	 *
	 * @property
	 * @see			<MethodName()||typePropertyName>
	 */
	private $_arrWhereAliases;
	
	//------------------------------------------------------------------------//
	// strTable	
	//------------------------------------------------------------------------//
	/**
	 * strTable
	 *
	 * Name of the table we're working with (if UPDATE or INSERT)
	 *
	 * Name of the table we're working with (if UPDATE or INSERT)
	 *
	 * @type		string
	 *
	 * @property
	 * @see			<MethodName()||typePropertyName>
	 */
	private $_strTable;

 	//------------------------------------------------------------------------//
	// Statement() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * Statement()
	 *
	 * Constructor for Statement
	 *
	 * Constructor for Statement Abstract Base Class
	 *
	 * @return		void
	 *
	 * @method
	 */ 
	 function __construct()
	 {
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

		// Find Aliases
		ereg("<[0-9a-zA-Z]*>", $strString, $arrAliases);

		// String replace all aliases with ?'s
		ereg_replace("<[0-9a-zA-Z]*>", "?", $strString);
		
		return $arrAliases;
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
		return (is_array($arrArray) && !is_numeric(implode(array_keys($arrArray))));
	}
	
	
	//------------------------------------------------------------------------//
	// GetDBInputType()
	//------------------------------------------------------------------------//
	/**
	 * GetDBInputType()
	 *
	 * Determines the type of a passed variable
	 *
	 * Determines the type of a passed variable.
	 * Returns:		"s" - String
	 * 				"i" - Integer
	 * 				"d" - Float/Double
	 * 				"b" - Binary
	 *
	 * @param		mixed	$mixData		Data to be checked
	 * 
	 * @return		string					"s" : String
	 * 										"i" : Integer
	 * 										"d" : Float/Double
	 * 										"b" : Binary
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function GetDBInputType($mixData) 
	{
		if (is_int($mixData))
 		{
 			// It's an integer
 			return "i";
 		}
 		elseif (is_float($mixData))
 		{
 			// It's a float/double
 			return "d";
 		}
 		elseif (is_scalar($mixData))
 		{
 			// It's a binary object
 			return "b";
 		}
 		
 		// Else, it's a string
 		return "s";
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
	function __construct($arrTables, $mixColumns, $strWhere, $strOrder, $strLimit)
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
		 			$strQuery .= "";
		 		}
		 		
				next($mixColumns);
		 	}
 		}
 		elseif (is_array($mixColumns))
 		{
 			// If it's an indexed array
 		}
 		else
 		{
 			// We have an invalid type, so throw an exception
 			//throw new InvalidTypeException();
 		}

	 	// Add the FROM line
	 	$strQuery .= "FROM ";
	 	// Add the tables into the query
	 	for ($i = 0; $i < (count($arrTables) - 1); $i++)
	 	{
	 		$strQuery .= $arrTables[$i] . ", ";
	 	}
	 	// Add the last table name (is different from the rest)
	 	$strQuery .= $arrTables[count($arrTables)] . "\n";
	 	
	 	$strQuery .= "\n";
	 	
	 	// Add the WHERE clause
	 	if ($strWhere != "")
	 	{
	 		// Find and replace the aliases in $strWhere
	 		$this->_arrWhereAliases = FindAlias($strWhere);
	 		
			$strQuery .= "WHERE " . $strWhere . "\n";
	 	}
	 	
	 	// Add the ORDER BY clause
	 	if ($strOrder != "")
	 	{
			$strQuery .= "ORDER BY " . $strOrder . "\n";	
	 	}
	 	
	 	// Add the LIMIT clause
	 	if ($strLimit != "")
	 	{
			$strQuery .= "LIMIT " . $strLimit . "\n";	
	 	}
	 	
	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->refMysqliConnection->stmt_init();
	 	
	 	if (!$this->_stmtSqlStatment->prepare($strQuery))
	 	{
	 		// There was problem preparing the statment
	 		throw new Exception();
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
	 	// Bind the WHERE data to our mysqli_stmt
	 	reset($this->_arrWhereAliases);
	 	
	 	while (key($this->_arrWhereAliases) != null)
	 	{
	 		$this->_stmtSqlStatment->bind_param($this->GetDBInputType($arrData[current($this->_arrWhereAliases)]), $arrData[current($this->_arrWhereAliases)]);
 			next($this->_arrWhereAliases);
	 	}
	 	
	 	// Run the Statement
	 	$this->_stmtSqlStatment->execute();
	 	
	 	// Store the results (required for num_rows())
	 	$this->_stmtSqlStatment->store_result();
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
	 	// Retrieve the next row of data from the resultset
	 	$arrResults = Array();
	 	$datMetaData = $this->_stmtSqlStatment->result_metadata();
	 	
	 	// First parameter for bind_result is the statment
	 	$arrFields[0] = &$this->_stmtSqlStatment;
	 	
	 	// Create a parameter list for bind_result()
	 	$i = 1;
	 	while ($fldField = $this->_stmtSqlStatment->fetch_field())
	 	{
	 		// Each parameter is a reference to an index in the result array (key is the Field name)
	 		$arrFields[$i] = &$arrResults[$fldField->name];
	 		$i++;
	 	}
	 		
 		call_user_func_array($this->_stmtSqlStatment->bind_result, $arrFields);
 		
 		return $arrResults;
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
	 * NOTE: 	If you have previously called Fetch(), then FetchAll() will only
	 * 			return the remaining rows in the resultset, not all.
	 *
	 * @return		array					Indexed array of Associated arrays
	 * 										Associative arrays of columns and values
	 * 										Key is the ColumnName, value is its value
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function FetchAll()
	{
	 	// Retrieve the remaining rows of data from the resultset
	 	$arrResults = Array();
	 	$arrRow = Array();
	 	$datMetaData = $this->_stmtSqlStatment->result_metadata();
	 	
		// First parameter for bind_result is the statment
		$arrFields[0] = &$this->_stmtSqlStatment;

	 	for ($i = 0; $i < $this->_stmtSqlStatment->num_rows(); $i++)
	 	{
		 	// Create a parameter list for bind_result()
		 	$i = 1;
		 	while ($fldField = $this->_stmtSqlStatment->fetch_field())
		 	{
		 		// Each parameter is a reference to an index in the result array (key is the Field name)
		 		$arrFields[$i] = &$arrRow[$fldField->name];
		 		$i++;
		 	}

	 		call_user_func_array($this->_stmtSqlStatment->bind_result, $arrFields);
	 		$arrResults[] = $arrRow;
	 	}
	 	
 		return $arrResults;
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
		parent::__construct();
		// Compile the query from our passed info
	 	$strQuery = "INSERT INTO " . $strTable . "\n" .
	 				"VALUES (";
	 				
	 	$this->_strTable = $strTable;
	 				
		// Create a ? placeholder for every column
	 	for ($i = 0; $i < (count(this->arrTableDefine[$strTable]["Column"]) - 1); $i++)
	 	{
	 		$strQuery .= "?, ";
	 	}
	 	// Last ? is different
	 	$strQuery .= "?)";

	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->refMysqliConnection->stmt_init();
	 	
	 	if (!$this->_stmtSqlStatment->prepare($strQuery))
	 	{
	 		// There was problem preparing the statment
	 		throw new Exception();
	 	}
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
	 	// Bind the VALUES data to our mysqli_stmt
	 	
	 	for ($i = 0; $i < count(this->arrTableDefine[$this->_strTable]["Column"]); $i++)
	 	{
	 		// FIXME: Use the Database definition to find type when Flame is done with it
	 		$this->_stmtSqlStatment->bind_param($this->GetDBInputType($arrData[$i]), $arrData[$i]);
	 	}
	 	
	 	// Run the Statement
	 	$this->_stmtSqlStatment->execute();
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
	 function __construct($strTable, $strWhere)
	 {
		parent::__construct();
		// Compile the query from our passed infos
	 	$strQuery = "UPDATE " . $strTable . "\n" .
	 				"SET ";
	 				
	 	$this->strTable = $strTable;
	 	
	 	// Retrieve columns from the Table definition arrays
	 	reset(this->arrTableDefine[$this->_strTable]["Column"]);
	 	for ($i = 0; $i < (count(this->arrTableDefine[$this->_strTable]["Column"]) - 1); $i++)
	 	{
	 		$strQuery .= key(this->arrTableDefine[$this->_strTable]["Column"]) . " = ?, ";
	 		next();
	 	}
	 	// Last column is different
	 	$strQuery .= key(this->arrTableDefine[$this->_strTable]["Column"]) . " = ?)\n";

	 	// Add the WHERE clause
	 	if ($strWhere != "")
	 	{
	 		// Find and replace the aliases in $strWhere
	 		$this->_arrWhereAliases = FindAlias($strWhere);
	 		
			$strQuery .= "WHERE " . $strWhere . "\n";
	 	}
	 	else
	 	{
	 		// We MUST have a WHERE clause
	 		throw new Exception();
	 	}
	 	
	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->refMysqliConnection->stmt_init();
	 	
	 	if (!$this->_stmtSqlStatment->prepare($strQuery))
	 	{
	 		// There was problem preparing the statment
	 		throw new Exception();
	 	}
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
	 function Execute($arrData, $arrWhere)
	 {
	 	// Bind the VALUES data to our mysqli_stmt
	 	for ($i = 0; $i < count(this->arrTableDefine[$this->_strTable]["Column"]); $i++)
	 	{
	 		$this->_stmtSqlStatment->bind_param($this->GetDBInputType($arrData[$i]), $arrData[$i]);
	 	}
	 	
	 	// Bind the WHERE data to our mysqli_stmt
	 	reset($this->_arrWhereAliases);
	 	while (key($this->_arrWhereAliases) != null)
	 	{
	 		$this->_stmtSqlStatment->bind_param($this->GetDBInputType($arrData[current($this->_arrWhereAliases)]), $arrData[current($this->_arrWhereAliases)]);
 			next($this->_arrWhereAliases);
	 	}
	 	
	 	// Run the Statement
	 	$this->_stmtSqlStatment->execute();
	 }
 }
?>
