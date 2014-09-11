<?php

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
		$this->Debug($mixReturn);
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

?>
