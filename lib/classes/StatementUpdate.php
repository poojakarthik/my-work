<?php

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
	 	
		// account for 'USING INDEX' in table name
		$arrTable = explode(' ', trim($strTable));
		$strTable = $arrTable[0];
		
	 	$this->_strTable = $strTable;
	 	
	 	// Determine if it's a partial or full update
	 	if ($arrColumns)
	 	{
			$this->_arrColumns = $arrColumns;
			
			if (!is_string($arrColumns))
			{
				// remove the index column
				unset($this->_arrColumns[$this->db->arrTableDefine->{$this->_strTable}['Id']]);
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
		 	$arrFullCols	= array_keys($this->db->arrTableDefine->{$this->_strTable}['Column']);
		 	$strQuery		.= implode(' = ?, ', $arrFullCols) . ' = ?\n';
	 	}

	 	// Add the WHERE clause
	 	if ($strWhere)
	 	{
	 		// Find and replace the aliases in $strWhere
	 		$this->_arrWhereAliases = $this->FindAlias($strWhere);
	 		
			$strQuery .= $strWhere . "\n";
	 	}
	 	else
	 	{
	 		// We MUST have a WHERE clause
	 		throw new Exception("No where clause.");
	 	}
	 	
		// Add the LIMIT clause
	 	if ((int)$intLimit)
	 	{
			$strQuery .= " LIMIT ".(int)$intLimit;
		}
		
		$this->_strQuery = $strQuery;
		
		// Trace
		$this->Trace("Query: $strQuery");
		
	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->db->refMysqliConnection->stmt_init();
	 	
	 	if (!$this->_stmtSqlStatment->prepare($strQuery))
	 	{
			Debug($strQuery);
			Debug($arrColumns);
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
					if (!isset ($this->db->arrTableDefine->{$this->_strTable}["Column"][$mixKey]["Type"]))
					{
						throw new Exception ("Could not find data type: " . $this->_strTable . "." . $mixKey);
					}
					
					$strType .= $this->db->arrTableDefine->{$this->_strTable}["Column"][$mixKey]["Type"];
		 			
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
		 	foreach ($this->db->arrTableDefine->{$this->_strTable}["Column"] as $strColumnName=>$arrColumnValue)
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
	 	
	 	// Only do bind_param if we have params to bind
	 	if (count($arrParams))
	 	{
		 	array_unshift($arrParams, $strType);
			if (!call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), $arrParams))
			{
				Debug($arrParams);
				Debug("Total Params: ".count($arrParams)."; Data Params: $intParamCount");
				Debug($this->_strQuery);
			}
	 	}
		

		// Send any blobs that have been defined
	 	$intBlobPos = -1;
	 	while (($intBlobPos = strpos($strType, "b", ++$intBlobPos)) !== FALSE)
	 	{
	 		// The parameter data starts at $arrParams[1]
	 		$blobParam = $arrParams[1 + $intBlobPos];
	 		
	 		// Split into 1MB chunks
	 		$arrChunks = str_split($blobParam, 1048576);
	 		
	 		foreach ($arrChunks as $blobChunk)
	 		{
	 			$this->_stmtSqlStatment->send_long_data($intBlobPos, $blobChunk);
	 		}
	 	}
		
		$mixResult = $this->_stmtSqlStatment->execute();

		$this->Debug($mixResult);
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
