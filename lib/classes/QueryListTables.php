<?php

//----------------------------------------------------------------------------//
// QueryListTables
//----------------------------------------------------------------------------//
/**
 * QueryListTables
 *
 * LIST TABLES Query
 *
 * Implements a LIST TABLES query using mysqli
 *
 *
 * @prefix		qlt
 *
 * @package		framework
 * @class		QueryListTables
 */
 class QueryListTables extends Query
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
	 * @param		string	strDatabase		optianal name of the database to show tables for
	 * 
	 * @return		bool
	 * @method
	 */ 
	 function Execute($strDatabase=NULL)
	 {
		// Trace
		$this->Trace("Input: $strDatabase");

	 	// check what we were given
		if (is_string($strDatabase))
		{
			$strDatabase = " FROM $strDatabase ";
		}
		else
		{
			$strDatabase = "";
		}

		// create query
		$strQuery = "SHOW FULL TABLES $strDatabase WHERE Table_Type != 'VIEW'";
		
		// Trace
		$this->Trace("Query: $strQuery");
		
		// run query
		$mixReturn = mysqli_query($this->db->refMysqliConnection, $strQuery);
		
		// check result
		if (!$mixReturn)
		{
			// we will return false
			// Trace
			$this->Trace("Failed: ".$this->Error());
			$bolReturn = FALSE;
		}
		
		$arrReturn = Array();
		
		while($arrTable = mysqli_fetch_array($mixReturn, MYSQLI_NUM))
		{
			$arrReturn[$arrTable[0]] = $arrTable[0];
		}
		
		return $arrReturn;
	 }
}

?>
