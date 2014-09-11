<?php

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
		
		// check that table defs exists
		if (is_array($this->db->arrTableDefine->{$strTableDestination}) && is_array($this->db->arrTableDefine->{$strTableSource}))
		{
			// empty column list
			$arrColumns = Array();
			
			// for each destination column
			foreach ($this->db->arrTableDefine->{$strTableDestination}['Column'] as $strColumnKey=>$arrColumn)
			{
				// check if there is a matching source column
				if (isset($this->db->arrTableDefine->{$strTableSource}['Column'][$strColumnKey]))
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
			$this->Debug($mixReturn);
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

?>
