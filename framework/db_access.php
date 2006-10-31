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
		$this->arrTableDefine = &$GLOBALS['arrDatabaseTableDefine'];
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
		if (!$GLOBALS['dbaDatabase'] || !is_a($GLOBALS['dbaDatabase'], "DataAccess"))
		{
			$GLOBALS['dbaDatabase'] = new DataAccess();
		}
		
		// make global database object available
		$this->db = &$GLOBALS['dbaDatabase'];
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
			$arrTables = Array($mixTable);
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
			//echo($strTableName);
			// check that table def exists
			if (is_array($this->db->arrTableDefine[$strTableName]))
			{
				$arrTableDefine = $this->db->arrTableDefine[$strTableName];
				
				/* CREATE TABLE `{$define['Name']}` (
				 *		`{$define['Id']}`	bigint	NOT NULL	auto_increment,
				 *		`{$column['name']}` {$column['type']} {$column['attributes']} {$column['null']} DEFAULT '{$column['default']}',
				 *		...
				 *
				 * INDEX	(`{$index[n]}`, `{$index[n++]}`),
				 * UNIQUE	(`{$unique[n]}`, `{$unique[n++]}`),
				 * PRIMARY KEY	(`{$define['Id']}`)
				 * ) TYPE = {$define['Type']}
		 		 */
				 /*
				 	$define['Name']		= "";			// table name
				 	$define['Type']		= "MYISAM";		// defaults to	'MYISAM'
					$define['Id']		= "Id";			// defaults to	'Id'
					
					$define['Index'][] 		= "";
					$define['Unique'][] 	= "";
					
					$define['Column'][$strName]['Type'] 		= "";			// Validation type: s, i etc
					$define['Column'][$strName]['SqlType'] 		= "";			// Sql Type: Varchar(5), Int etc
					$define['Column'][$strName]['Null'] 		= TRUE|FALSE;	// optional, defaults to FALSE (NOT NULL)
					$define['Column'][$strName]['Default'] 		= "";			// optional default value
					$define['Column'][$strName]['Attributes'] 	= "";			// optional attributes
				 
				 */
				 
				// clean reused variables 
				unset($strIndex);
				unset($strUnique);

				// set defaults primary index
                if (empty($arrTableDefine['Id']))
                {
                    $arrTableDefine['Id'] = 'Id';
                }
                
                // set default table type
                if (empty($structure['Type']))
                {
                    $arrTableDefine['Type'] = 'MYISAM';
                }
                 
                // build index string
                if (is_array($arrTableDefine['Index']))
                {
                    foreach($arrTableDefine['Index'] as $strIndexValue)
                    {
                        $strIndex .= "$strIndexValue,";
                    }
                    $strIndex = substr($strIndex, 0, -1);
                }
                 
                // build unique string
                if (is_array($arrTableDefine['Unique']))
                {
                    foreach($arrTableDefine['Unique'] as $strUniqueValue)
                    {
                        $strUnique .= "$strUniqueValue,";
                    }
                    $strUnique = substr($strUnique, 0, -1);
                }
                
                // build the CREATE query
                $strQuery  = "CREATE TABLE $strTableName (\n";
                
				// autoindex (Id column)
				$strQuery .= "    {$arrTableDefine['Id']} bigint NOT NULL auto_increment,\n";
				
                // columns
                foreach($arrTableDefine['Column'] as $strColumnKey=>$arrColumn)
                {
                    // use the key if we don't have a column name
                    if (empty($arrColumn['Name']))
                    {
                        $arrColumn['Name'] = $strColumnKey;
                    }
                    
                    // null, defaults to not null
                    if ($arrColumn['Null'] === TRUE)
                    {
                        $strNull = '';
                    }
                    else
                    {
                        $strNull = 'NOT NULL';
                    }
                    
                    // default
                    if($arrColumn['Default'])
                    {
                        $arrColumn['Default'] = "DEFAULT '{$arrColumn['Default']}'";
                    }
                    
                    $strQuery .= "    {$arrColumn['Name']} {$arrColumn['SqlType']} {$arrColumn['Attributes']} $strNull {$arrColumn['Default']},\n";
                }
                 
                // index
                if ($strIndex)
                {
                    $strQuery .= "    INDEX    ($strIndex),\n";
                }
                // unique
                if ($strUnique)
                {
                    $strQuery .= "    UNIQUE    ($strUnique),\n";
                }
                // primary key & table type
                $strQuery .= "    PRIMARY KEY    ({$arrTableDefine['Id']})\n";
                $strQuery .= ") TYPE = {$arrTableDefine['Type']}\n";
				
				// run query
				$mixReturn = mysqli_query($this->db->refMysqliConnection, $strQuery);
				//echo (mysqli_error($this->db->refMysqliConnection));
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
		$strString = ereg_replace("<[0-9a-zA-Z]*>", "?", $strString);
		
		// Remove <>'s from alias names
		$i = 0;
		foreach ($arrAliases as $strAlias)
		{
			$arrAliases[$i] = substr($strAlias, 1, -1);
			$i++;
		}
		
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
		//print_r($mixData);
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
	// arrBoundResults
	//------------------------------------------------------------------------//
	/**
	 * arrBoundResults
	 *
	 * Stores the temporary bound results
	 *
	 * Stores the temporary bound results, so we only have to call bind_restults once
	 *
	 * @type	array
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
 	private $_arrBoundResults = Array();	

 
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
	 * @param		string	strTables		String of tables, like in an SQL FROM clause,
	 * 										ignoring the  FROM keyword.
	 * 										(eg. "TableName[, Table2Name JOIN Table3Name...]")
	 * @param		mixed	mixColumns		Can be either associative or indexed array.
	 * 										Use indexed for normal column referencing.
	 * 										Use associated arrays for either renaming of
	 * 										columns (eg. ["ColumnName"] = "ColumnAlias") and
	 * 										special SQL funcion calls (eg. ["NOW()"] = "NowAlias")
	 * @param		string	strWhere		optional A full SQL WHERE clause, minus the keyword.
	 * 										Paramaters should be aliased in a meaningful
	 * 										fashion enclosed in <>'s
	 * 										(eg. "FooBar = <FooBar>")
	 * @param		string	strOrder		optional A full SQL ORDER BY clause, minus the keywords
	 * 										(eg. "ColumnName ASC, Column2Name")
	 * @param		string	strLimit		optional SQL LIMIT clause, minus the keyword
	 * 										(eg. "5") - Return first 5 rows
	 * 										(eg. "5,10") - Return rows 6-15
	 * @return		void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function __construct($strTables, $mixColumns, $strWhere = "", $strOrder = "", $strLimit = "")
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
		 			$strQuery .= " AS ";
		 			$strQuery .= current($mixColumns);
		 		}
		 		
				next($mixColumns);
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
		 	}
 		}
 		else
 		{
 			// We have an invalid type, so throw an exception
 			//throw new InvalidTypeException();
 			echo("Invalid Type!  Line 765\n");
 		}

	 	// Add the FROM line
	 	$strQuery .= "FROM " . $strTables . "\n";

	 	// Add the WHERE clause
	 	if ($strWhere != "")
	 	{
	 		// Find and replace the aliases in $strWhere
	 		$this->_arrWhereAliases = $this->FindAlias($strWhere);
	 		
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
	 	$this->_stmtSqlStatment = $this->db->refMysqliConnection->stmt_init();

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
	 function Execute($arrWhere = Array())
	 {
	 	// Bind the WHERE data to our mysqli_stmt
	 	if (isset($this->_arrWhereAliases))
	 	{
	 		$i = 0;
		 	// Bind the WHERE data to our mysqli_stmt
		 	foreach ($this->_arrWhereAliases as $strAlias)
		 	{
		 		$strType .= $this->GetDBInputType($arrWhere[$i]);
		 		$arrParams[] = $arrWhere[$strAlias];
	 			$i++;
		 	}
	 	}

	 	array_unshift($arrParams, $strType);
		call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), $arrParams);
	 	
	 	// Free any previous results
	 	$this->_stmtSqlStatment->free_result();
	 	
	 	// Run the Statement
	 	$this->_stmtSqlStatment->execute();
	 	
	 	// Store the results (required for result_metadata())
	 	$this->_stmtSqlStatment->store_result();
	 	
	 	// Retrieve the metatdata from the resultset
	 	$datMetaData = $this->_stmtSqlStatment->result_metadata();
	 	
		// Create a parameter list for bind_result()
	 	while ($fldField = $datMetaData->fetch_field())
	 	{
	 		// Each parameter is a reference to an index in the result array (key is the Field name)
	 		$arrFields[] = &$this->_arrBoundResults[$fldField->name];
	 	}
	 	
 		call_user_func_array(Array($this->_stmtSqlStatment,"bind_result"), $arrFields);
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
	 * @return		mixed					Associative array of columns and values
	 * 										Key is the ColumnName, value is its value
	 * 										or FALSE if there was no next row
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function Fetch()
	{
		if($this->_stmtSqlStatment->fetch())
		{
 			return $this->_arrBoundResults;
		}
		
		// If there was no result, then return false
		return false;
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

		// Add the results to our huge array of results
	 	while($this->_stmtSqlStatment->fetch())
	 	{
			unset($arrTemp);
			foreach($this->_arrBoundResults as $strKey=>$mixValue)
			{
				$arrTemp[$strKey] = $mixValue;
			}
			$arrResults[] = $arrTemp;
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
			 	
	 	$this->_strTable = $strTable;
		// Compile the query from our passed info
	 	$strQuery = "INSERT INTO " . $strTable . " (";
	 	
	 	reset($this->db->arrTableDefine[$strTable]["Column"]);
	 	for ($i = 0; $i < (count($this->db->arrTableDefine[$strTable]["Column"]) - 1); $i++)
	 	{
	 		$strQuery .= key($this->db->arrTableDefine[$strTable]["Column"]) . ", ";
	 		next();
	 	}
	 	// Last column is different
	 	$strQuery .= key($this->db->arrTableDefine[$strTable]["Column"]) . ")\n";
	 	
	 	$strQuery .= "VALUES(";

		// Create a ? placeholder for every column
	 	for ($i = 0; $i < (count($this->db->arrTableDefine[$strTable]["Column"]) - 1); $i++)
	 	{
	 		$strQuery .= "?, ";
	 	}
	 	// Last ? is different
	 	$strQuery .= "?)";

	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->db->refMysqliConnection->stmt_init();
		
	 	if (!$this->_stmtSqlStatment->prepare($strQuery))
	 	{
			//echo($strQuery);
			//echo Mysqli_error($this->db->refMysqliConnection);
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
	 * @param		array	arrData			Associative array of the data to be inserted
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
	 	foreach ($this->db->arrTableDefine[$this->_strTable]["Column"] as $strColumnName=>$arrColumnValue)
	 	{
			$strType .= $arrColumnValue['Type'];
			$arrParams[] = $arrData[$strColumnName];
	 	}
		array_unshift($arrParams, $strType);
		call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), $arrParams);
	 	
	 	// Run the Statement
	 	return $this->_stmtSqlStatment->execute();
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
	 				
	 	$this->_strTable = $strTable;
	 	
	 	// Retrieve columns from the Table definition arrays
	 	reset($this->db->arrTableDefine[$this->_strTable]["Column"]);
	 	for ($i = 0; $i < (count($this->db->arrTableDefine[$this->_strTable]["Column"]) - 1); $i++)
	 	{
	 		$strQuery .= key($this->db->arrTableDefine[$this->_strTable]["Column"]) . " = ?, ";
	 		next();
	 	}
	 	// Last column is different
	 	$strQuery .= key($this->db->arrTableDefine[$this->_strTable]["Column"]) . " = ?\n";

	 	// Add the WHERE clause
	 	if ($strWhere != "")
	 	{
	 		// Find and replace the aliases in $strWhere
	 		$this->_arrWhereAliases = $this->FindAlias($strWhere);
	 		
			$strQuery .= "WHERE " . $strWhere . "\n";
	 	}
	 	else
	 	{
	 		// We MUST have a WHERE clause
	 		throw new Exception();
	 	}
	 	
	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->db->refMysqliConnection->stmt_init();
	 	
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
	 	$arrBoundVariables = Array();
	 	
	 	$i = 0;
	 	// Bind the VALUES data to our mysqli_stmt
	 	foreach ($this->db->arrTableDefine[$this->_strTable]["Column"] as $strColumnName=>$arrColumnValue)
	 	{
			$strType .= $arrColumnValue["Type"];
			$arrParams[] = $arrData[$strColumnName];
			$i++;
	 	}
 		
 		$i = 0;
	 	// Bind the WHERE data to our mysqli_stmt
	 	foreach ($this->_arrWhereAliases as $strAlias)
	 	{
	 		$strType .= $this->GetDBInputType($arrWhere[$i]);
	 		$arrParams[] = $arrWhere[$strAlias];
 			$i++;
	 	}
	 	
	 	array_unshift($arrParams, $strType);
		call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), $arrParams);
			
	 	// Run the Statement
	 	return $this->_stmtSqlStatment->execute();
	 }
 }
?>
