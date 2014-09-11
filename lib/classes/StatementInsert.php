<?php
class StatementInsert extends Statement {
	public $intInsertId;

	function __construct($strTable, $arrColumns=null, $bolWithId=null, $strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT) {
		parent::__construct($strConnectionType);

		// Trace
		$this->Trace("Input: {$strTable}, {$arrColumns}");

		$this->_strTable = $strTable;

		$this->_bolWithId = $bolWithId;

		// Determine if it's a partial or full insert
		if ($arrColumns) {
			$this->_arrColumns = $arrColumns;

			if (!is_string($arrColumns)) {
				// remove the index column
				if ($bolWithId !== true) {
					unset($this->_arrColumns[$this->db->arrTableDefine->{$this->_strTable}['Id']]);
				}
			} else {
				// For some reason arrColumns is a string
				Debug($arrColumns);
				DebugBacktrace();
				die;	// Die in the ass
			}

			// Partial Update, so use $arrColumns
			$this->_bolIsPartialInsert = true;
		} else {
			// Full Insert, so retrieve columns from the Table definition array
			$this->_arrColumns = $this->db->arrTableDefine->{$strTable}["Column"];

			// add the Id
			if ($bolWithId === true) {
				$this->_arrColumns[$this->db->arrTableDefine->{$this->_strTable}['Id']]['Type'] = "i";
			}
		}

		// Work out the keys and values
		$arrInsertValues = array();
		$strInsertKeys = implode(',', array_keys($this->_arrColumns));
		foreach ($this->_arrColumns as $mixKey=>$mixColumn) {
			if ($mixColumn instanceof MySQLFunction) {
				$arrInsertValues[] = $mixColumn->Prepare();
			} else {
				$arrInsertValues[] = '?';
			}
		}
		$strInsertValues = implode(',', $arrInsertValues);

		// Compile the query
		$strQuery = "INSERT INTO {$strTable} ({$strInsertKeys}) VALUES ({$strInsertValues})";

		// Prepare the Statement
		$this->_prepare($strQuery);
	}

	function Execute($arrData) {
		$aExecutionProfile = array();
		$aExecutionProfile['fStartTime'] = microtime(true);

		// Trace
		$this->Trace("Execute({$arrData})");

		// Bind the VALUES data to our mysqli_stmt
		$strType = "";
		$arrParams = array();
		if (isset($this->_bolIsPartialInsert) && $this->_bolIsPartialInsert) {
			// partial insert
			foreach ($this->_arrColumns as $mixKey=>$mixColumn) {
				if ($mixColumn instanceOf MySQLFunction) {
					if (!($arrData[$mixKey] instanceof MySQLFunction)) {
						throw new Exception ("Dead :-("); // TODO: Fix that ...
					}

					$mixColumn->Execute($strType, $arrParams, $arrData[$mixKey]->getParameters());
				} else {
					if (!isset($this->db->arrTableDefine->{$this->_strTable}["Column"][$mixKey]["Type"])) {
						if (!$mixKey == $this->db->arrTableDefine->{$this->_strTable}['Id']) {
							throw new Exception("Could not find data type: {$this->_strTable}.{$mixKey}");
						}
					}

					if ($mixKey == $this->db->arrTableDefine->{$this->_strTable}['Id']) {
						// add Id
						$strType .= 'd';
					} else {
						// add normal value
						$strType .= $this->db->arrTableDefine->{$this->_strTable}["Column"][$mixKey]["Type"];
					}

					// account for table.column key names
					if (isset($arrData[$mixKey])) {
						$arrParams[] = $arrData[$mixKey];
					} elseif (isset($arrData["{$this->_strTable}.{$mixKey}"])) {
						$arrParams[] = $arrData["{$this->_strTable}.{$mixKey}"];
					} else {
						$arrParams[] = null;
					}
				}
			}
		} else {
			// full insert
			foreach ($this->db->arrTableDefine->{$this->_strTable}["Column"] as $strColumnName=>$arrColumnValue) {
				if (isset($arrData[$strColumnName])) {
					$strType .= $arrColumnValue['Type'];
					$arrParams[] = $arrData[$strColumnName];
				} else {
					// Assumes that missing fields are supposed to be null
					// We say the type is an integer, so that the word NULL
					// does not get preescaped
					$strType .= "i";
					$arrParams[] = null;
				}
			}
			if ($this->_bolWithId === true) {
				// add in the Id if needed
				$strType .= "d";
				$arrParams[] = $arrData[$this->db->arrTableDefine->{$this->_strTable}['Id']];
			}
		}

		// `bind_param` expects the bound parameter values to be passed by reference, which requires calling the following hack
		// function `referencialiseArrayValues` in order to be called using `call_user_func_array`:
		$aReferencialised = $arrParams;
		array_unshift($aReferencialised, $strType);
		call_user_func_array(Array($this->_stmtSqlStatment,"bind_param"), referencialiseArrayValues($aReferencialised));

		// Send any blobs that have been defined
		$intBlobPos = -1;
		while (($intBlobPos = strpos($strType, "b", ++$intBlobPos)) !== false) {
			// The parameter data starts at $aReferencialised[1]
			$blobParam = $aReferencialised[1 + $intBlobPos];

			// Split into 1MB chunks
			$arrChunks = str_split($blobParam, 1048576);

			foreach ($arrChunks as $blobChunk) {
				$this->_stmtSqlStatment->send_long_data($intBlobPos, $blobChunk);
			}
		}

		// Run the Statement
		$mixResult = $this->_execute();

		$this->Debug($mixResult);
		if ($mixResult) {
			// If the execution worked, we want to get the insert id for this statement
			$this->intInsertId = $this->db->refMysqliConnection->insert_id;
			if ((int)$this->db->refMysqliConnection->insert_id < 1) {
				//Debug("WTF! Last Insert Id is apparently '{$this->db->refMysqliConnection->insert_id}'");
			}

			// Update profiling info
			$aExecutionProfile['fDuration'] = microtime(true) - $aExecutionProfile['fStartTime'];
			$aExecutionProfile['iInsertId'] = $this->intInsertId;
			$this->aProfiling['aExecutions'][] = $aExecutionProfile;

			return $this->intInsertId;
		} else {
			// Trace
			$this->Trace("Failed: ".$this->Error());

			// If the execution failed, return a "false" boolean
			$this->intInsertId = false;
			return false;
		}
	}
}
?>