<?php

//----------------------------------------------------------------------------//
// Query
//----------------------------------------------------------------------------//
/**
 * Query
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
	 function __construct($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	 {
	 	$this->intSQLMode =SQL_QUERY;
		parent::__construct($strConnectionType);
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
	 	$mixResult = mysqli_query($this->db->refMysqliConnection, $strQuery);
	 	$this->Debug($mixResult);
		return $mixResult;
	 }
	 
	 // Returns the number of records affected by the last INSERT, UPDATE, REPLACE or DELETE query executed 
	 function AffectedRows()
	 {
	 	return mysqli_affected_rows($this->db->refMysqliConnection);
	 }
}

?>
