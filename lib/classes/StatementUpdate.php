<?php
class StatementUpdate extends Statement {
	private $_bolIsPartialUpdate = false;
	private $intAffectedRows = false;
	private $_arrColumns;
	
	function __construct($strTable, $mixWhere, $arrColumns=null, $mLimit=null) {
		parent::__construct();
		
		// Trace
		$this->Trace("Input: {$strTable}, {$mixWhere}, {$arrColumns}, {$mLimit}");
		
		// prepare the WHERE clause
		$strWhere = $this->PrepareWhere($mixWhere);
		
		// Compile the query from our passed infos
		$strQuery = "UPDATE {$strTable}\nSET ";
		
		// account for 'USING INDEX' in table name
		$arrTable = explode(' ', trim($strTable));
		$strTable = $arrTable[0];
		
		$this->_strTable = $strTable;
		
		// Determine if it's a partial or full update
		if ($arrColumns) {
			$this->_arrColumns = $arrColumns;
			
			if (!is_string($arrColumns)) {
				// remove the index column
				unset($this->_arrColumns[$this->db->arrTableDefine->{$this->_strTable}['Id']]);
			} else {
				// For some reason arrColumns is a string
				Debug($arrColumns);
				DebugBacktrace();
				die; // Die in the ass
			}
			
			// Partial Update, so use $arrColumns
			$this->_bolIsPartialUpdate = true;
			
			foreach ($this->_arrColumns as $mixKey => $mixColumn) {
				if ($mixColumn instanceOf MySQLFunction) {
					$strQuery .= "{$mixKey} = " . $mixColumn->Prepare() . ", ";
				} else {
					$strQuery .= "{$mixKey} = ?, ";
				}
			}
			
			$strQuery = substr ($strQuery, 0, -2) . " ";
		} else {
			// Full Update, so retrieve columns from the Table definition arrays
			$arrFullCols = array_keys($this->db->arrTableDefine->{$this->_strTable}['Column']);
			$strQuery .= implode(' = ?, ', $arrFullCols) . " = ?\n";
		}

		// Add the WHERE clause
		if ($strWhere) {
			// Find and replace the aliases in $strWhere
			$this->_arrPlaceholders = $this->FindAlias($strWhere);
			
			$strQuery .= $strWhere . "\n";
		} else {
			// We MUST have a WHERE clause
			throw new Exception("No where clause.");
		}
		
		// Add the LIMIT clause
		$iLimit = (int)$mLimit;
		if ($iLimit) {
			$strQuery .= " LIMIT {$iLimit}";
		}
		
		// Prepare the Statement
		$this->_prepare($strQuery);
	}
	
	function Execute($arrData, $arrWhere) {
		$aExecutionProfile = array();
		$aExecutionProfile['fStartTime'] = microtime(true);
		$aExecutionProfile['aWhere'] = array();
		
		// Trace
		$this->Trace("Execute({$arrData}. {$arrWhere})");
		
		$arrBoundVariables = array();
		$strType = "";
		
		$arrParams = array();
		
		if ($this->_bolIsPartialUpdate) {
			foreach ($this->_arrColumns as $mixKey => $mixColumn) {
				if ($mixColumn instanceOf MySQLFunction) {
					if (!($arrData [$mixKey] instanceOf MySQLFunction)) {
						throw new Exception("Dead :-("); // TODO: Fix that ...
					}
					
					$mixColumn->Execute($strType, $arrParams, $arrData [$mixKey]->getParameters());
				} else {
					if (!isset($this->db->arrTableDefine->{$this->_strTable}["Column"][$mixKey]["Type"])) {
						throw new Exception("Could not find data type: {$this->_strTable}.{$mixKey}");
					}
					
					$strType .= $this->db->arrTableDefine->{$this->_strTable}["Column"][$mixKey]["Type"];
					
					// account for table.column key names
					if (isset($arrData[$mixKey])) {
						$arrParams[] = $arrData[$mixKey];
					} elseif (isset($arrData[$this->_strTable.".".$mixKey])) {
						$arrParams[] = $arrData[$this->_strTable.".".$mixKey];
					} else {
						$arrParams[] = null;
					}
				}
			}
		} else {
			// Bind the VALUES data to our mysqli_stmt
			foreach ($this->db->arrTableDefine->{$this->_strTable}["Column"] as $strColumnName=>$arrColumnValue) {
				$strType .= $arrColumnValue["Type"];
				$arrParams[] = $arrData[$strColumnName];
			}
		}
		
		// Bind the WHERE data to our mysqli_stmt
		foreach ($this->_arrPlaceholders as $strAlias) {
			$mWhereValue = isset($arrWhere[$strAlias]) ? $arrWhere[$strAlias] : null;
			$strType .= $this->GetDBInputType($mWhereValue);
			
			$strParam = "";
			
			if ($mWhereValue instanceOf MySQLFunction) {
				$strParam = $mWhereValue->getFunction();
			} else {
				$strParam = $mWhereValue;
			}
			$aExecutionProfile['aWhere'][$strAlias] = $strParam;
			
			$arrParams[] = $strParam;
		}
		
		// Only do bind_param if we have params to bind
		if (count($arrParams)) {
			// `bind_param` expects the bound parameter values to be passed by reference, which requires calling the following hack
			// function `referencialiseArrayValues` in order to be called using `call_user_func_array`:
			$aReferencialised = $arrParams;
			array_unshift($aReferencialised, $strType);
			if (!call_user_func_array(array($this->_stmtSqlStatment,"bind_param"), referencialiseArrayValues($aReferencialised))) {
				Debug($aReferencialised);
				Debug("Total Params: ".count($aReferencialised)."; Data Params: {$intParamCount}");
				Debug($this->_strQuery);
			}
		}
		

		// Send any blobs that have been defined
		$intBlobPos = -1;
		while (($intBlobPos = strpos($strType, "b", ++$intBlobPos)) !== FALSE) {
			// The parameter data starts at $aReferencialised[1]
			$blobParam = $aReferencialised[1 + $intBlobPos];
			
			// Split into 1MB chunks
			$arrChunks = str_split($blobParam, 1048576);
			
			foreach ($arrChunks as $blobChunk) {
				$this->_stmtSqlStatment->send_long_data($intBlobPos, $blobChunk);
			}
		}
		
		$mixResult = $this->_execute();

		$this->Debug($mixResult);
		// Run the Statement
		if ($mixResult) {
			// If it was successful, we want to store the number of affected rows
			$this->intAffectedRows = $this->db->refMysqliConnection->affected_rows;
			
			// Update profiling info
			$aExecutionProfile['fDuration'] = microtime(true) - $aExecutionProfile['fStartTime'];
			$aExecutionProfile['iAffectedRows'] = $this->intAffectedRows;
			if ($this->db->getProfilingEnabled()) {
				$this->aProfiling['aExecutions'][] = $aExecutionProfile;
			}
			
			return $this->intAffectedRows;
		} else {
			if ($mixResult === FALSE) {
				// Trace
				$this->Trace("Failed: ".$this->Error());
			}
			
			$this->intAffectedRows = false;
			return false;
		}
	}
}