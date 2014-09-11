<?php

//----------------------------------------------------------------------------//
// StatementSelectReport
//----------------------------------------------------------------------------//
/**
 * StatementSelectReport
 *
 * SELECT REPORT Query
 *
 * Implements a SELECT REPORT query using mysqli statements
 *
 * USAGE :
 *
 * // instanciate like a normal select object
 * $slrReport = New StatementSelectReport($strTables, $mixColumns, $mixWhere, $strOrder, $strLimit, $strGroupBy);
 *
 * // Prepare the report
 * $slrReport->Prepare($arrDefine);
 *
 * // Execute like a normal select statement
 * $slrReport->Execute($arrWhere);
 *
 * // Format the report as a CSV
 * $slrReport->FormatCSV();
 *
 * // Do something with the report
 * echo $slrReport->Report;
 *
 *
 * @prefix		slr
 *
 * @package		framework
 * @class		StatementSelectReport
 */
 class StatementSelectReport extends StatementSelect
 {
 	/**
	 * StatementSelectReport()
	 *
	 * Constructor for StatementSelectReport object
	 *
	 * Constructor for StatementSelectReport object
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
 	function __construct($strTables, $mixColumns, $mixWhere = "", $strOrder = "", $strLimit = "", $strGroupBy = "", $strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		parent::__construct($strTables, $mixColumns, $mixWhere, $strOrder, $strLimit, $strGroupBy, $strConnectionType);
	}
	
	//------------------------------------------------------------------------//
	// Prepare()
	//------------------------------------------------------------------------//
	/**
	 * Prepare()
	 *
	 * Prepares the StatementSelectReport
	 *
	 * Prepares the StatementSelectReport
	 * 
	 * Definitian Array Format ;
	 * 	$arrDefine['AliasName']['Input'] 		= ??
	 * 	$arrDefine['AliasName']['Command'] 		= String	PHP Command
	 *
	 * @param		array	arrDefine		Associative array that defines the report
	 * 
	 * @return		VOID
	 *
	 * @method
	 */ 
	 function Prepare($arrDefine)
	 {
	 	// set the internal data array
		$this->_arrReportData = Array();
		if (is_array($arrDefine))
		{
			foreach($arrDefine AS $strKey=>$arrValue)
			{
				if ($arrValue['Command'])
				{
					$this->_arrReportData[$strKey]['Value'] = EvalReturn($arrValue['Command']);
				}
			}	
		}
	 }
	 
	//------------------------------------------------------------------------//
	// Execute()
	//------------------------------------------------------------------------//
	/**
	 * Execute()
	 *
	 * Executes the StatementSelectReport, with a new set of values
	 *
	 * Executes the StatementSelectReport, with a new set of values
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
	 	// add report data to the statement
		if (is_array($this->_arrReportData))
		{
			foreach($this->_arrReportData AS $strKey=>$arrValue)
			{
				if (!$arrWhere[$strKey])
				{
					$arrWhere[$strKey] = $arrValue['Value'];
				}
			}
		}
	 	
	 	// execute the statement
	 	return parent::Execute($arrWhere);
	 }
	 
	//------------------------------------------------------------------------//
	// FormatCSV()
	//------------------------------------------------------------------------//
	/**
	 * FormatCSV()
	 *
	 * Format the results of StatementSelectReport into a CSV
	 *
	 * Format the results of StatementSelectReport into a CSV
	 * CSV will then be available as $this->Report
	 * 
	 * @param		str		strSeperator	optional field seperator, defaults to ;
	 * @param		str		strTerminator	optional line terminator, defaults to \n
	 *
	 * @return		VOID
	 * @method
	 */ 
	 function FormatCSV($strSeperator = ';', $strTerminator = "\n")
	 {
	 	// clear report
		$this->Report = "";
		
	 	// add header row & first row
		$arrRow = $this->_stmtSqlStatment->fetch();
		if (is_array($arrRow))
		{
			// add header to CSV
			$this->Report .= implode($strSeperator, array_keys($arrRow)).$strTerminator;
			// add line to CSV
			$this->Report .= implode($strSeperator, $arrRow).$strTerminator;
			
			// add all rows
			// Add the results to our huge array of results
			while($this->_stmtSqlStatment->fetch())
			{
				$arrTemp = array();
				foreach($this->_arrBoundResults as $strKey=>$mixValue)
				{
					$arrTemp[$strKey] = '"'.$this->_stmtSqlStatment->real_escape_string($mixValue).'"';
				}
				// add line to CSV
				$this->Report .= implode($strSeperator, $arrTemp).$strTerminator;
			}
		}
	 }
}

?>
