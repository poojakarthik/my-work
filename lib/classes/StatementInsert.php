<?php

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
	public $intInsertId;
	
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
	 * @param		array	arrColumns		optional Associative array of the columns 
	 * 										you want to insert, where the keys are the column names.
	 * 										If you want to insert everything, ignore
	 * 										this parameter
	 * @param		bool	bolWithId		Set TRUE to force the Id field to be inserted 
	 *
	 * @return		void
	 *
	 * @method
	 */ 
	 function __construct($strTable, $arrColumns = NULL, $bolWithId = NULL, $strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	 {
		parent::__construct($strConnectionType);
		
		// Trace
		$this->Trace("Input: $strTable, $arrColumns");
			 	
	 	$this->_strTable = $strTable;
	 	
		$this->_bolWithId = $bolWithId;
		
		
		// Determine if it's a partial or full insert
	 	if ($arrColumns)
	 	{
			$this->_arrColumns = $arrColumns;
			
			if (!is_string($arrColumns))
			{
				// remove the index column
				if ($bolWithId !== TRUE)
				{
					unset($this->_arrColumns[$this->db->arrTableDefine->{$this->_strTable}['Id']]);
				}
			}
			else
			{
				// For some reason arrColumns is a string
				Debug($arrColumns);
				DebugBacktrace();
				Die();	// Die in the ass
			}
			
	 		// Partial Update, so use $arrColumns
	 		$this->_bolIsPartialInsert = TRUE;
		}
		else
		{
			// Full Insert, so retrieve columns from the Table definition array
			$this->_arrColumns = $this->db->arrTableDefine->{$strTable}["Column"];
			
			// add the Id
			if ($bolWithId === TRUE)
			{
				$this->_arrColumns[$this->db->arrTableDefine->{$this->_strTable}['Id']]['Type'] = "i";
			}
		}
		
		// Work out the keys and values
		$arrInsertValues	= Array();
		$strInsertKeys 		= implode(',', array_keys($this->_arrColumns));
		foreach ($this->_arrColumns as $mixKey => $mixColumn)
		{
			if ($mixColumn instanceOf MySQLFunction)
			{
				$arrInsertValues[]	= $mixColumn->Prepare();
			}
			else
			{
				$arrInsertValues[]	= '?';
			}
		}	
		$strInsertValues 	= implode(',', $arrInsertValues);
		
		// Compile the query
		$strQuery 			= "INSERT INTO " . $strTable . " ($strInsertKeys) VALUES($strInsertValues)";
		
	 	// Init and Prepare the mysqli_stmt
	 	$this->_stmtSqlStatment = $this->db->refMysqliConnection->stmt_init();
	 	
		// Trace
		$this->Trace("Query: $strQuery");
		$this->_strQuery = $strQuery;
		
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
	 	
		// Bind the VALUES data to our mysqli_stmt
	 	$strType = "";
		$arrParams = array();
	 	if (isset ($this->_bolIsPartialInsert) && $this->_bolIsPartialInsert)
	 	{
			// partial insert
			foreach ($this->_arrColumns as $mixKey => $mixColumn)
			{
				if ($mixColumn instanceOf MySQLFunction)
				{
					if (!($arrData [$mixKey] instanceOf MySQLFunction))
					{
						throw new Exception ("Dead :-("); // TODO: Fix that ...
					}
					
					$mixColumn->Execute ($strType, $arrParams, $arrData[$mixKey]->getParameters());
				}
				else
				{
					if (!isset ($this->db->arrTableDefine->{$this->_strTable}["Column"][$mixKey]["Type"]))
					{
						if (!$mixKey == $this->db->arrTableDefine->{$this->_strTable}['Id'])
						{
							throw new Exception ("Could not find data type: " . $this->_strTable . "." . $mixKey);
						}
					}
					
					if ($mixKey == $this->db->arrTableDefine->{$this->_strTable}['Id'])
					{
						// add Id
						$strType .= 'd';
					}
					else
					{
						// add normal value
						$strType .= $this->db->arrTableDefine->{$this->_strTable}["Column"][$mixKey]["Type"];
		 			}
					
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
			// full insert
			foreach ($this->db->arrTableDefine->{$this->_strTable}["Column"] as $strColumnName=>$arrColumnValue)
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
			if ($this->_bolWithId === TRUE)
			{
				// add in the Id if needed
				$strType .= "d";
				$arrParams[] = $arrData[$this->db->arrTableDefine->{$this->_strTable}['Id']];
			}
		}
		
		array_unshift($arrParams, $strType);
		call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), $arrParams);
	 	
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

	 	// Run the Statement
	 	$mixResult = $this->_stmtSqlStatment->execute();
	 	$this->Debug($mixResult);
	 	if ($mixResult)
		{
			// If the execution worked, we want to get the insert id for this statement
			$this->intInsertId = $this->db->refMysqliConnection->insert_id;	
			if ((int)$this->db->refMysqliConnection->insert_id < 1)
			{
				//Debug("WTF! Last Insert Id is apparently '{$this->db->refMysqliConnection->insert_id}'");
			}
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

?>
