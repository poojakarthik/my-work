<?php

//----------------------------------------------------------------------------//
// QueryCopyTable
//----------------------------------------------------------------------------//
/**
 * QueryCopyTable
 *
 * COPY TABLE Query
 *
 * Implements a COPY TABLE query using mysqli
 * Id fields WILL be kept
 * Fields from the source table will be inserted into the destination table
 *
 *
 * @prefix		qct
 *
 * @package		framework
 * @class		QueryCopyTable
 */
class QueryCopyTable extends Query
{
 	function __construct($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
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

		// build query 0
		$strQuery = "DROP TABLE IF EXISTS $strTableDestination";
		
		// Trace
		$this->Trace("Query: ".$strQuery);
		
		// run query
		$mixReturn = mysqli_query($this->db->refMysqliConnection, $strQuery);
		
		// check result
		if ($mixReturn === FALSE)
		{
			// query failed
			// Trace
			$this->Trace("Failed: ".$this->Error());
			return FALSE;
		}
		
		// build query 1
		$strQuery = "CREATE TABLE $strTableDestination LIKE $strTableSource";
		
		// Trace
		$this->Trace("Query: ".$strQuery);
		
		// run query
		$mixReturn = mysqli_query($this->db->refMysqliConnection, $strQuery);
		
		// check result
		if ($mixReturn === FALSE)
		{
			// query failed
			// Trace
			$this->Trace("Failed: ".$this->Error());
			return FALSE;
		}

		// build query 2
		$strQuery = "INSERT INTO $strTableDestination SELECT * FROM $strTableSource ";
		
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

		// check result
		if ($mixReturn !== TRUE)
		{
			// query failed
			// Trace
			$this->Trace("Failed: ".$this->Error());
			return FALSE;
		}

		return TRUE;
		//TODO!!!! - return something usefull, rows inserted?
	 }
}


?>
