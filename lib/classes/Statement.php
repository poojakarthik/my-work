<?php

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
	protected $_stmtSqlStatment;
	
	//------------------------------------------------------------------------//
	// arrPlaceholders	
	//------------------------------------------------------------------------//
	/**
	 * arrPlaceholders
	 *
	 * Indexed array of placeholders used in the prepared statement, stored in the order in which they are found in the statement
	 *
	 * Indexed array of placeholders used in the prepared statement, stored in the order in which they are found in the statement
	 *
	 * @type		array
	 *
	 * @property
	 * @see			<MethodName()||typePropertyName>
	 */
	private $_arrPlaceholders;
	
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
	 function __construct($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	 {
	 	$this->aProfiling['fPreparationStart']	= microtime(true);
	 	
	 	$this->intSQLMode = SQL_STATEMENT;
		parent::__construct($strConnectionType);
	 }
	 
	/**
	 * _prepare()
	 *
	 * Prepares the Statement
	 *
	 * @return		void
	 * 
	 * @param		string	$sQuery
	 *
	 * @method
	 */ 
	 protected function _prepare($sQuery)
	 {
		$this->Trace("Query: {$sQuery}");
		
		$this->_strQuery		= $sQuery;
	 	$this->_stmtSqlStatment	= $this->db->refMysqliConnection->stmt_init();
		
	 	if (!$this->_stmtSqlStatment->prepare($sQuery))
	 	{
	 		// There was problem preparing the statment
	 		//throw new Exception("Could not prepare statement : $strQuery\n");
			// Trace
			
			$this->Trace("Error: ".$this->Error());
			Debug($this->Error());
			
			//throw new Exception($this->Error());
	 	}
		
		$this->aProfiling['fPreparationTime']	= microtime(true) - $this->aProfiling['fPreparationStart'];
		$this->aProfiling['sQuery']				= $sQuery;
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
 			return "d";
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

?>
