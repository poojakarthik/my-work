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

// TODO: Add Functionality for MySQL Function in INSERT + SELECT
 
 
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
	
	//------------------------------------------------------------------------//
	// FetchTableDefine
	//------------------------------------------------------------------------//
	/**
	 * FetchTableDefine()
	 *
	 * return the definition for a table
	 *
	 * return the definition for a table
	 *
	 * @param		string	name of the table
	 * @return		mixed	array table definition or FALSE if table doesn't exist
	 *
	 * @method
	 */ 
	function FetchTableDefine($strTableName)
	{
		if($this->arrTableDefine[$strTableName])
		{
			return $this->arrTableDefine[$strTableName];
		}
		else
		{
			return FALSE;
		}
	}
	
	//------------------------------------------------------------------------//
	// FetchClean
	//------------------------------------------------------------------------//
	/**
	 * FetchClean()
	 *
	 * return an empty record from a database table
	 *
	 * return an empty record from a database table
	 * uses the database define to create the record
	 * does not talk to the database at all
	 *
	 * @param		string	name of the table
	 * @return		mixed	array record or FALSE if table doesn't exist
	 *
	 * @method
	 */ 
	function FetchClean($strTableName)
	{
		if($this->arrTableDefine[$strTableName])
		{
			foreach($this->arrTableDefine[$strTableName]['Column'] as $strKey => $strValue)
			{
				$arrClean[$strKey] = '';
			}
			return $arrClean;
		}
		else
		{
			return FALSE;
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
	public $intSQLMode;
	
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
		if (!isset ($GLOBALS['dbaDatabase']) || !$GLOBALS['dbaDatabase'] || !($GLOBALS['dbaDatabase'] instanceOf DataAccess))
		{
			$GLOBALS['dbaDatabase'] = new DataAccess();
		}
		
		// make global database object available
		$this->db = &$GLOBALS['dbaDatabase'];
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
		return (is_array($arrArray) && !is_numeric(implode(array_keys($arrArray))));
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
		return mysqli_error($this->db->refMysqliConnection);
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
	 	$this->intSQLMode = SQL_STATEMENT;
		parent::__construct();
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
		// Special case for mysql functions
		
		
		//print_r($mixData);
		if ($mixData instanceOf MySQLFunction)
		{
			return "i";
		}
		elseif (is_int($mixData))
 		{
 			// It's an integer
 			return "i";
 		}
 		elseif (is_float($mixData))
 		{
 			// It's a float/double
 			return "d";
 		}
		/*
		 * this was commented on nov. 2 2006 because of conflicts with string
 		elseif (!is_scalar($mixData))
 		{
 			// It's a binary object
 			return "b";
 		}
		*/
 		
 		// Else, it's a string
 		return "s";
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
	 	$this->intSQLMode =SQL_QUERY;
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
	 * @param		string	strQuery		string containing a full SQL query
	 * 
	 * @return		bool
	 * @method
	 */ 
	 function Execute($strQuery)
	 {
	 	$this->Trace($strQuery);
	 	
	 	// run query
		return mysqli_query($this->db->refMysqliConnection, $strQuery);
	 }
 }


//----------------------------------------------------------------------------//
// MySQLFunction
//----------------------------------------------------------------------------//
/**
 * MySQLFunction
 *
 * For Functions in MySQL
 *
 * Allows the usage of MySQL Functions in Queries
 *
 *
 * @prefix		fnc
			("MySQL Function")
 *
 * @package		framework
 * @class		MySQLFunction
 */
class MySQLFunction
{

 	//------------------------------------------------------------------------//
	// strFunction
	//------------------------------------------------------------------------//
	/**
	 * strFunction
	 *
	 * The function we wish to pass to MySQL
	 *
	 * The function we wish to pass to MySQL
	 *
	 * @type	<type>
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
	private $_strFunction;
	private $_arrParams;
	private $_arrOrderedParams;
	
	//------------------------------------------------------------------------//
	// MySQLFunction() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * MySQLFunction()
	 *
	 * Constructor for MySQLFunction object
	 *
	 * Constructor for MySQLFunction object
	 *
	 * @param		string	strFunction		The function we are passing, represented as a string
	 *
	 * @return		void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	
	function __construct ($strFunction, $arrParams=null)
	{
		$this->_strFunction = $strFunction;
		$this->_arrParams = $arrParams;
	}
	
	//------------------------------------------------------------------------//
	// getFunction()
	//------------------------------------------------------------------------//
	/**
	 * getFunction()
	 *
	 * Gets the value of the function
	 *
	 * Gets the value of the function
	 *
	 * @return		string							The value of the MySQL Function
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 

	public function getFunction ()
	{
		return $this->_strFunction;
	}
	
	public function getParameters ()
	{
		return $this->_arrParams;
	}
	
	public function setParameters ($arrParams)
	{
		$this->_arrParams = $arrParams;
	}
	
	public function Prepare ()
	{
		$strFunction = $this->_strFunction;
		$this->_arrOrderedParams = Statement::FindAlias ($strFunction);
		
		return $strFunction;
	}
	
	public function Execute (&$strType, &$arrParams, $arrData)
	{
		foreach ($this->_arrOrderedParams as $mixColumn)
		{
			$strType .= Statement::GetDBInputType ($arrData [$mixColumn]);
			$arrParams [] = $arrData [$mixColumn];
		}
	}
}

//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
// QUERY CLASSES
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//


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
		// Trace
		$this->Trace("Input: $mixTable");
	 	
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
                
                // Trace
                $this->Trace("Query: ".$strQuery);
				
				// run query
				$mixReturn = mysqli_query($this->db->refMysqliConnection, $strQuery);
				//echo (mysqli_error($this->db->refMysqliConnection));
				// check result
				if ($mixReturn !== TRUE)
				{
					// we will return false
					// Trace
					$this->Trace("Failed: ".$this->Error());
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
// QuerySelectInto
//----------------------------------------------------------------------------//
/**
 * QuerySelectInto
 *
 * SELECT INTO Query
 *
 * Implements a SELECT INTO query using mysqli
 * Id fields will not be kept
 * Fields from the source table will be inserted into the destination table if
 * a matching field name exists. non-matching fields will be ignored.
 *
 *
 * @prefix		siq
 *
 * @package		framework
 * @class		QuerySelectInto
 */
 class QuerySelectInto extends Query
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
	 * @param		string	strTableDestination		name of the table to insert into
	 * @param		string	strTableSource			name of the table to select from
	 * @param		string	strWhere				optional A full SQL WHERE clause, minus the keyword.
	 * @param		string	strLimit				optional A full SQL LIMIT Clause, minus the keyword.
	 * 
	 * @return		bool
	 * @method
	 */ 
	 function Execute($strTableDestination, $strTableSource, $strWhere = NULL, $strLimit = NULL)
	 {
		// Trace
		$this->Trace("Input: $strTableDestination, $strTableSource, $strWhere, $strLimit");
		
		// check that table defs exists
		if (is_array($this->db->arrTableDefine[$strTableDestination]) && is_array($this->db->arrTableDefine[$strTableSource]))
		{
			// empty column list
			$arrColumns = Array();
			
			// for each destination column
			foreach ($this->db->arrTableDefine[$strTableDestination]['Column'] as $strColumnKey=>$arrColumn)
			{
				// check if there is a matching source column
				if (isset($this->db->arrTableDefine[$strTableSource]['Column'][$strColumnKey]))
				{
					// add column to the query
					$arrColumns[] = $strColumnKey;
				}
			}
			
			// check if we have matching columns
			if (empty($arrColumns))
			{
				return FALSE;
			}
			
			// build columns string
			$strColumns = implode(', ', $arrColumns);
			
			// build query
			$strQuery = "INSERT INTO $strTableDestination ($strColumns) SELECT $strColumns FROM $strTableSource ";
			
			// add where clause
			if ($strWhere)
			{
				$strQuery .= "WHERE " . $strWhere . "\n";
			}
			
			// add limit clause
			if ($strLimit)
			{
				$strQuery .= "LIMIT " . $strLimit . "\n";
			}
			
			// Trace
			$this->Trace("Query: ".$strQuery);
			
			// run query
			$mixReturn = mysqli_query($this->db->refMysqliConnection, $strQuery);
			//echo (mysqli_error($this->db->refMysqliConnection));
			// check result
			if ($mixReturn !== TRUE)
			{
				// query failed
				// Trace
				$this->Trace("Failed: ".$this->Error());
				return FALSE;
			}
		}
		else
		{
			// missing table def(s) or bad table names
			return FALSE;
		}
		
		return TRUE;
		//TODO!!!! - return something usefull, rows inserted?
	 }
 }

//----------------------------------------------------------------------------//
// QueryTruncate
//----------------------------------------------------------------------------//
/**
 * QueryTruncate
 *
 * TRUNCATE Query
 *
 * Implements a TRUNCATE query using mysqli
 *
 *
 * @prefix		trq
 *
 * @package		framework
 * @class		QueryTruncate
 */
 class QueryTruncate extends Query
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
	 * @param		string	strTable		string containing name of the table to truncate
	 * 
	 * @return		bool
	 * @method
	 */ 
	 function Execute($strTable)
	 {
		// Trace
		$this->Trace("Input: $strTable");

	 	// check what we were given
		if (!is_string($strTable))
		{
			return FALSE;
		}
		
		// by default we return TRUE
		$bolReturn = TRUE;

		// create query
		$strQuery = "TRUNCATE TABLE ".$strTable;
		
		// Trace
		$this->Trace("Query: $strQuery");
		
		// run query
		$mixReturn = mysqli_query($this->db->refMysqliConnection, $strQuery);
		// check result
		if ($mixReturn !== TRUE)
		{
			// we will return false
			// Trace
			$this->Trace("Failed: ".$this->Error());
			$bolReturn = FALSE;
		}
		
		return $bolReturn;
	 }
 }


//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
// STATEMENT CLASSES
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//
//----------------------------------------------------------------------------//


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
	// datMetaData
	//------------------------------------------------------------------------//
	/**
	 * datMetaData
	 *
	 * Stores the meta data for the response
	 *
	 * Stores the meta data for the response, so we can use it for oblib later
	 *
	 * @type	array
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
 	private $_datMetaData = Array();	
	
	//------------------------------------------------------------------------//
	// _bolObLib
	//------------------------------------------------------------------------//
	/**
	 * _bolObLib
	 *
	 * Evaluates whether or not we want oblib objects or an array returned
	 *
	 * Evaluates whether or not we want oblib objects returned or if we would
	 * prefer to use an array. In order to set this value as "TRUE", you need
	 * to call useObLib (TRUE). By default, we do not want to use ObLib
	 *
	 * @type	boolean
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
 	private $_bolObLib = FALSE;

 
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
	 * 										columns (eg. ["ColumnAlias"] = "ColumnName") and
	 * 										special SQL funcion calls (eg. ["NowAlias"] = "NOW()")
	 * @param		mixed	mixWhere		optional A full SQL WHERE clause, minus the keyword.
	 * 										Paramaters should be aliased in a meaningful
	 * 										fashion enclosed in <>'s
	 * 										(eg. "FooBar = <FooBar>")
	 *										Can also be passed as an associative array (eg. the same
	 *										array as passed to the execute method), to produce a
	 *										WHERE clause like "Foo = <Foo> AND Bar = <Bar>" using
	 *										the array keys.
	 * @param		string	strOrder		optional A full SQL ORDER BY clause, minus the keywords
	 * 										(eg. "ColumnName ASC, Column2Name")
	 * @param		string	strLimit		optional SQL LIMIT clause, minus the keyword
	 * 										(eg. "5") - Return first 5 rows
	 * 										(eg. "5,10") - Return rows 6-15
	 * @param		string	strGroupBy		optional a full SQL GROUP BY clause, minus the keywords
	 * 										(eg. "")
	 * @return		void
	 *
	 * @method
	 */ 
	function __construct($strTables, $mixColumns, $mixWhere = "", $strOrder = "", $strLimit = "", $strGroupBy = "")
	{
		parent::__construct();
		
		// Trace
		$this->Trace("Input: $strTables, $mixColumns, $mixWhere, $strOrder, $strLimit, $strGroupBy");
		
		// prepare the WHERE clause
		$strWhere = $this->PrepareWhere($mixWhere);
		
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
		 	while (current($mixColumns) != null)
		 	{
		 		$strQuery .= current($mixColumns);
		 		
		 		// If this column has an AS alias
		 		if (key($mixColumns) != "")
		 		{
		 			$strQuery .= " AS ";
		 			$strQuery .= key($mixColumns);
		 		}
		 		$strQuery .= ", ";
				next($mixColumns);
		 	}
			$strQuery = substr($strQuery, 0, -2);
 		}
 		elseif (is_array($mixColumns))
 		{
 			// If it's an indexed array
			reset($mixColumns);
			
		 	// Add the columns 	
		 	while (key($mixColumns) != null)
		 	{
		 		$strQuery .= current($mixColumns).", ";
				next($mixColumns);
		 	}
			$strQuery = substr($strQuery, 0, -2);
 		}
 		else
 		{
 			// We have an invalid type, so throw an exception
 			//throw new InvalidTypeException();
 			echo("Invalid Type!  Line 765\n");
 		}

	 	// Add the FROM line
	 	$strQuery .= " FROM " . $strTables . "\n";

	 	// Add the WHERE clause
	 	if ($strWhere != "")
	 	{
	 		// Find and replace the aliases in $strWhere
	 		$this->_arrWhereAliases = $this->FindAlias($strWhere);
	 		Debug($this->_arrWhereAliases);
			
			$strQuery .= $strWhere . "\n";
	 	}
	 	
	 	// Add the GROUP BY clause
	 	if ($strGroupBy != "")
	 	{
			$strQuery .= "GROUP BY " . $strGroupBy . "\n";	
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
	 	
		// Trace
		$this->Trace("Query: $strQuery");
		
		// DEBUG
		$this->_strQuery = $strQuery;
	 	
	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->db->refMysqliConnection->stmt_init();

	 	if (!$this->_stmtSqlStatment->prepare($strQuery))
	 	{
	 		// There was problem preparing the statment
	 		//throw new Exception("Could not prepare statement : $strQuery\n");
			// Trace
			$this->Trace("Error: ".$this->Error());
			//throw new Exception($this->Error());
	 	}
	}
	
	//------------------------------------------------------------------------//
	// useObLib()
	//------------------------------------------------------------------------//
	/**
	 * useObLib()
	 *
	 * Changes our ObLib status
	 *
	 * Changes our flag to specify whether or not we are using ObLib
	 *
	 * @param		boolean	bolObLib		TRUE: We are using ObLib
	 *										FALSE: We are not using ObLib
	 * 
	 * @return		void
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 public function useObLib ($bolObLib)
	 {
	 	$this->_bolObLib = ($bolObLib === TRUE);
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
	 * @return		int		number of rows
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function Execute($arrWhere = Array())
	 {
	 	// Trace
//		$this->Trace("Execute($strQuery)");
	 	
	 	$strType = "";
	 	
	 	// Bind the WHERE data to our mysqli_stmt
	 	if (isset($this->_arrWhereAliases))
	 	{
	 		$i = 0;
		 	// Bind the WHERE data to our mysqli_stmt
		 	foreach ($this->_arrWhereAliases as $strAlias)
		 	{
				if (is_array($arrWhere[$strAlias]))
				{
					$strParam = $arrWhere[$strAlias]['Value'];
				}
				else
				{
					$strParam = $arrWhere[$strAlias];
				}
		 		$strType .= $this->GetDBInputType($strParam);
		 		$arrParams[] = $strParam;
	 			$i++;
		 	}
		 	/*
		 	if (count($this->_arrWhereAliases) != count($arrParams))
		 	{
		 		Debug("Number of Aliases doesn't match variables");
		 		Debug($this->_arrWhereAliases);
		 		Debug($arrParams);
		 		DebugBacktrace();
		 	}*/
		 	
		 	Debug("Aliases: ".count($this->_arrWhereAliases)."; Params: ".count($arrParams). "; Query: ".$this->_strQuery);
		 	DebugBacktrace();
		 	
			if (is_array($arrParams))
			{
		 		array_unshift($arrParams, $strType);
				call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), $arrParams);
			}
	 	}
		
	 	// Free any previous results
	 	$this->_stmtSqlStatment->free_result();
	 	
	 	// Run the Statement
	 	if(!$this->_stmtSqlStatment->execute())
	 	{
			// Trace
			$this->Trace("Failed: ".$this->Error());
			// Die in the rectal sphincter
			new Exception($this->Error());
	 	}
	 	
		
		
	 	// Store the results (required for result_metadata())
	 	$this->_stmtSqlStatment->store_result();
	 	
	 	// Retrieve the metatdata from the resultset
	 	$this->_datMetaData = $this->_stmtSqlStatment->result_metadata();
		
		// Create a parameter list for bind_result()
	 	while ($fldField = $this->_datMetaData->fetch_field())
	 	{
	 		// Each parameter is a reference to an index in the result array (key is the Field name)
	 		$arrFields[] = &$this->_arrBoundResults[$fldField->name];
	 	}
		
		call_user_func_array(Array($this->_stmtSqlStatment,"bind_result"), $arrFields);
		
		return $this->_stmtSqlStatment->num_rows;
	}
	
	//------------------------------------------------------------------------//
	// Count()
	//------------------------------------------------------------------------//
	/**
	 * Count()
	 *
	 * Counts how many rows were returned by the last execution
	 *
	 * Counts how many rows were returned by the last execution
	 *
	 * @return		integer							Returns a number (0..*) with the number of 
	 *										rows returned by this query
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function Count()
	{
		return $this->_stmtSqlStatment->num_rows;
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
	 * @param		data	&oblobjPushObject	An object that is inherited from `data` which
	 *											contains the method "Push". This will be used
	 *											to insert information into the object
	 *
	 * @return		mixed					Associative array of columns and values
	 * 										Key is the ColumnName, value is its value
	 * 										or FALSE if there was no next row
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	function Fetch (&$oblobjPushObject=null)
	{
		// Firstly, if we have a row to pull ... pull it (and put it into $this->_arrBoundResults)
		if($this->_stmtSqlStatment->fetch())
		{
			// If we are using ObLib, we want to turn everything into an oblib object
			if ($this->_bolObLib === TRUE)
			{
				// We're up to here which means that we're using ObLib ...
				
				// Because we're using ObLib, we need the first parameter to be called.
				// In this instance, this means we need $oblobjPushObject to be an
				// inheritance of `data` and contain the method "Push"
				if (!is_subclass_of ($oblobjPushObject, 'data') || !method_exists ($oblobjPushObject, 'Push'))
				{
					throw new Exception (
						'You are using Oblib ... Therefore you must have ' .
						'1 parameter ([inherited of] data $oblobjPushObject) ' .
						'with a method named `Push` where the information will be returned'
					);
				}
				
				// Start at 0 and loop through all the fields in the meta data
				$i=0;
				while ($fldField = @$this->_datMetaData->fetch_field_direct ($i++))
				{
					// Because Id is not defined in our database definitions, we
					// can have to specify that Id fields are Integers
					if ($fldField->name == "Id")
					{
						$oblobjPushObject->Push (new dataInteger ("Id", $this->_arrBoundResults ["Id"]));
					}
					else
					{
						// Create a new instance of an oblib object using the ObLib parameter of the database definition
						$oblobjPushObject->Push
						(
							new $this->db->arrTableDefine[$fldField->table]["Column"][$fldField->name]["ObLib"]
							(
								$fldField->name, $this->_arrBoundResults [$fldField->name]
							)
						);
					}
				}
				
				return true;
			}
			else
			{
				// If we're not using oblib, return an associative array
 				return $this->_arrBoundResults;
			}
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
 * @class		StatementInsert
 */
 class StatementInsert extends Statement
 {
 	
 	//------------------------------------------------------------------------//
	// intInsertId
	//------------------------------------------------------------------------//
	/**
	 * intInsertId
	 *
	 * Keeps the Id of the last execution made on the statement
	 *
	 * Keeps the Id of the last execution made on the statement
	 *
	 * @type	<type>
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
	private $intInsertId;
	
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
	 */ 
	 function __construct($strTable, $arrColumns = NULL)
	 {
		parent::__construct();
		
		// Trace
		$this->Trace("Input: $strTable, $arrColumns");
			 	
	 	$this->_strTable = $strTable;
		// Compile the query from our passed info
	 	$strQuery = "INSERT INTO " . $strTable . " (";
	 	
	 	reset($this->db->arrTableDefine[$strTable]["Column"]);
	 	for ($i = 0; $i < (count($this->db->arrTableDefine[$strTable]["Column"]) - 1); $i++)
	 	{
	 		$strQuery .= key($this->db->arrTableDefine[$strTable]["Column"]) . ", ";
	 		next($this->db->arrTableDefine[$strTable]["Column"]);
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
	 	
		// Trace
		$this->Trace("Query: $strQuery");
		
	 	if (!$this->_stmtSqlStatment->prepare($strQuery))
	 	{
			//echo($strQuery);
			//echo Mysqli_error($this->db->refMysqliConnection);
	 		// There was problem preparing the statment
			// Trace
			$this->Trace("Failed: ".$this->Error());
	 		throw new Exception(
	 			"An error occurred : " . Mysqli_error($this->db->refMysqliConnection) . "\n" . $strQuery . "\n\n"
	 		);
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
	 * 										If a field is not set, it is assumed to be null
	 * 
	 * @return		mixed					int	: Insert Id
	 * 										false	: Insert failed
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function Execute($arrData)
	 {
		// Trace
		$this->Trace("Execute($arrData)");
	 	
	 	$strType = "";
	 	
	 	// Bind the VALUES data to our mysqli_stmt
	 	foreach ($this->db->arrTableDefine[$this->_strTable]["Column"] as $strColumnName=>$arrColumnValue)
	 	{
			if (isset ($arrData[$strColumnName]))
			{
				$strType .= $arrColumnValue['Type'];
				$arrParams[] = $arrData[$strColumnName];
			}
			else
			{
				// Assumes that missing fields are supposed to be null
				// We say the type is an integer, so that the word NULL
				// does not get preescaped
				$strType .= "i";
				$arrParams[] = NULL;
			}
	 	}
		array_unshift($arrParams, $strType);
		call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), $arrParams);
	 	
	 	// Run the Statement
	 	if ($this->_stmtSqlStatment->execute())
		{
			// If the execution worked, we want to get the insert id for this statement
			$this->intInsertId = $this->db->refMysqliConnection->insert_id;	
			return $this->intInsertId;
		}
		else
		{
			// Trace
			$this->Trace("Failed: ".$this->Error());
			
			// If the execution failed, return a "false" boolean
			$this->intInsertId = false;
			return false;
		}
		
	 }
 }


//----------------------------------------------------------------------------//
// StatementUpdateById
//----------------------------------------------------------------------------//
/**
 * StatementUpdateById
 *
 * UPDATE by Id Query
 *
 * Implements an UPDATE by Id query using mysqli statements
 *
 *
 * @prefix		ubi
 *
 * @package		framework
 * @class		UpdateByIdStatement
 */
 class StatementUpdateById extends StatementUpdate
 {
 	//------------------------------------------------------------------------//
	// StatementUpdateById() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * StatementUpdateById()
	 *
	 * Constructor for StatementUpdateById object
	 *
	 * Constructor for StatementUpdateById object
	 *
	 * @param		string	strTable		Name of the table to update
	 * @param		array	arrColumns		optional Associative array of the columns 
	 * 										you want to update, where the keys are the column names.
	 * 										If you want to update everything, ignore
	 * 										this parameter
	 *
	 * @return		void
	 *
	 * @method
	 */ 
 	function __construct($strTable, $arrColumns = null)
	{
		// make global database object available
		$this->db = &$GLOBALS['dbaDatabase'];
		$strId = $this->db->arrTableDefine[$strTable]['Id'];
		if (!$strId)
		{
			throw new Exception("Missing Table Id for : $strTable");
		}
		
		$strWhere = "$strId = <$strId>";
		parent::__construct($strTable, $strWhere, $arrColumns);
	}
	
	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Executes the StatementUpdateById, with a new set of values
	 *
	 * Executes the StatementUpdateById, with a new set of values
	 *
	 * @param		array	arrData			Associative array of data to be entered.  If this is
	 * 										for a partial update, make sure that it is the exact same
	 * 										array passed to the constructor (the elements must be in the same order)
	 * 
	 * @return		mixed					int			: number of Affected Rows
	 * 										bool FALSE	: Update failed
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function Execute($arrData)
	 {
	 	$strId = $this->db->arrTableDefine[$this->_strTable]['Id'];
		$intId = $arrData[$strId];
		$arrWhere = Array($strId => $intId);
	 	return parent::Execute($arrData, $arrWhere);
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
	// bolIsPartialUpdate
	//------------------------------------------------------------------------//
	/**
	 * bolIsPartialUpdate
	 *
	 * Determines whether the UPDATE is partial or complete
	 *
	 * Determines whether the UPDATE is partial or complete		true	: Partial
	 * 															false	: Full
	 *
	 * @type	<type>
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
	private $_bolIsPartialUpdate = false;
	
 	//------------------------------------------------------------------------//
	// intAffectedRows
	//------------------------------------------------------------------------//
	/**
	 * intAffectedRows
	 *
	 * Keeps a count of the number of affected rows in the version query 
	 *
	 * Keeps a count of the number of affected rows in the version query 
	 *
	 * @type	<type>
	 *
	 * @property
	 */
	private $intAffectedRows = false;
	
	private $_arrColumns;
	
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
	 * @param		mixed	mixWhere		A full SQL WHERE clause, minus the keyword.
	 * 										Paramaters should be aliased in a meaningful
	 * 										fashion enclosed in <>'s
	 * 										(eg. "FooBar = <FooBar>")
	 *										Can also be passed as an associative array (eg. the same
	 *										array as passed to the execute method), to produce a
	 *										WHERE clause like "Foo = <Foo> AND Bar = <Bar>" using
	 *										the array keys.
	 * @param		array	arrColumns		optional Associative array of the columns 
	 * 										you want to update, where the keys are the column names.
	 * 										If you want to update everything, ignore
	 * 										this parameter
	 * @param		int		intLimit		optional LIMIT
	 *
	 * @return		void
	 *
	 * @method
	 */ 
	 function __construct($strTable, $mixWhere, $arrColumns = null, $intLimit = null)
	 {
		parent::__construct();
		
		// Trace
		$this->Trace("Input: $strTable, $mixWhere, $arrColumns, $intLimit");
		
		// prepare the WHERE clause
		$strWhere = $this->PrepareWhere($mixWhere);
		
		// Compile the query from our passed infos
	 	$strQuery = "UPDATE " . $strTable . "\n" . "SET ";
	 			
	 	$this->_strTable = $strTable;
	 	
	 	// Determine if it's a partial or full update
	 	if ($arrColumns)
	 	{
			$this->_arrColumns = $arrColumns;
			
			if (!is_string($arrColumns))
			{
				// remove the index column
				unset($this->_arrColumns[$this->db->arrTableDefine[$this->_strTable]['Id']]);
			}
			else
			{
				// For some reason arrColumns is a string
				Debug($arrColumns);
				DebugBacktrace();
				Die();	// Die in the ass
			}
			
	 		// Partial Update, so use $arrColumns
	 		$this->_bolIsPartialUpdate = true;
	 		
			foreach ($this->_arrColumns as $mixKey => $mixColumn)
			{
				if ($mixColumn instanceOf MySQLFunction)
				{
					$strQuery .= $mixKey . " = " . $mixColumn->Prepare () . ", ";
				}
				else
				{
			 		$strQuery .= $mixKey . " = ?, ";
				}
	 		}
			
			$strQuery = substr ($strQuery, 0, -2) . " ";
	 	}
	 	else
	 	{
		 	// Full Update, so retrieve columns from the Table definition arrays
		 	reset($this->db->arrTableDefine[$this->_strTable]["Column"]);
		 	for ($i = 0; $i < (count($this->db->arrTableDefine[$this->_strTable]["Column"]) - 1); $i++)
		 	{
		 		$strQuery .= key($this->db->arrTableDefine[$this->_strTable]["Column"]) . " = ?, ";
		 		next($this->db->arrTableDefine[$this->_strTable]["Column"]);
		 	}
		 	// Last column is different
		 	$strQuery .= key($this->db->arrTableDefine[$this->_strTable]["Column"]) . " = ?\n";
	 	}

	 	// Add the WHERE clause
	 	if ($strWhere != "")
	 	{
	 		// Find and replace the aliases in $strWhere
	 		$this->_arrWhereAliases = $this->FindAlias($strWhere);
	 		
			$strQuery .= $strWhere . "\n";
	 	}
	 	else
	 	{
	 		// We MUST have a WHERE clause
	 		throw new Exception();
	 	}
	 	
		// Add the LIMIT clause
	 	if ((int)$intLimit)
	 	{
			$strQuery .= " LIMIT ".(int)$intLimit;
		}
		
		// Trace
		$this->Trace("Query: $strQuery");
		
	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->db->refMysqliConnection->stmt_init();
	 	
	 	if (!$this->_stmtSqlStatment->prepare($strQuery))
	 	{
			// Trace
			$this->Trace("Failed: ".$this->Error());
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
	 * @param		array	arrData			Associative array of data to be entered.  If this is
	 * 										for a partial update, make sure that it is the exact same
	 * 										array passed to the constructor (the elements must be in the same order)
	 * @param		array	arrWhere		Associative array of parameters for the WHERE clause.
	 * 										MUST use the same aliases as used when the object was
	 * 										created.  Key string is the alias (ignoring the <>'s)
	 * 										, and the Value is the value to be inserted.
	 * 
	 * @return		mixed					int			: number of Affected Rows
	 * 										bool FALSE	: Update failed
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */ 
	 function Execute($arrData, $arrWhere)
	 {
		// Trace
		$this->Trace("Execute($arrData. $arrWhere)");
	 	
	 	$arrBoundVariables = Array();
	 	$strType = "";
	 	
		$arrParams = array();
		
	 	if ($this->_bolIsPartialUpdate)
	 	{
			foreach ($this->_arrColumns as $mixKey => $mixColumn)
			{
				if ($mixColumn instanceOf MySQLFunction)
				{
					if (!($arrData [$mixKey] instanceOf MySQLFunction))
					{
						throw new Exception ("Dead :-("); // TODO: Fix that ...
					}
					
					$mixColumn->Execute ($strType, $arrParams, $arrData [$mixKey]->getParameters ());
				}
				else
				{
					if (!isset ($this->db->arrTableDefine[$this->_strTable]["Column"][$mixKey]["Type"]))
					{
						throw new Exception ("Could not find data type: " . $this->_strTable . "." . $mixKey);
					}
					
					$strType .= $this->db->arrTableDefine[$this->_strTable]["Column"][$mixKey]["Type"];
		 			
					// account for table.column key names
					if (isset($arrData [$mixKey]))
					{
						$arrParams[] = $arrData [$mixKey];
					}
					else
					{
						$arrParams[] = $arrData [$this->_strTable.".".$mixKey];
					}
				}
	 		}
	 	}
	 	else
	 	{
		 	// Bind the VALUES data to our mysqli_stmt
		 	foreach ($this->db->arrTableDefine[$this->_strTable]["Column"] as $strColumnName=>$arrColumnValue)
		 	{
				$strType .= $arrColumnValue["Type"];
				$arrParams[] = $arrData[$strColumnName];
		 	}		
	 	}
		
	 	// Bind the WHERE data to our mysqli_stmt
	 	foreach ($this->_arrWhereAliases as $strAlias)
	 	{
	 		$strType .= $this->GetDBInputType($arrWhere[$strAlias]);
			
			$strParam = "";
			
			if ($arrWhere[$strAlias] instanceOf MySQLFunction)
			{
				$strParam = $arrWhere[$strAlias]->getFunction ();
			}
			else
			{
				$strParam = $arrWhere[$strAlias];
			}
			
	 		$arrParams[] = $strParam;
	 	}
	 	
	 	array_unshift($arrParams, $strType);
		call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), $arrParams);
		
		$mixResult = $this->_stmtSqlStatment->execute();
		
	 	// Run the Statement
	 	if ($mixResult)
		{
			// If it was successful, we want to store the number of affected rows
			$this->intAffectedRows = $this->db->refMysqliConnection->affected_rows;
			return $this->intAffectedRows;
		}
		else
		{
			if ($mixResult === FALSE)
			{
				// Trace
				$this->Trace("Failed: ".$this->Error());
			}
			
			$this->intAffectedRows = false;
			return false;
		}
	 }
 }



?>
