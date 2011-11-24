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
 	const	PREPARE_CALLBACK_NESTING_MAX	= 5000;

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
	 	
	 	$aProfiling				= array();
		$aProfiling['sQuery']	= $strQuery;
	 	
	 	// run query
	 	$mixResult = mysqli_query($this->db->refMysqliConnection, $strQuery);
	 	
	 	// Handle Locking issues
	 	if ($mixResult === false)
	 	{
			// Execute
			if ($this->db->refMysqliConnection->errno == DatabaseAccess::ER_LOCK_DEADLOCK)
			{
				// Failure -- Deadlock
				throw new Exception_Database_Deadlock($this->Error());
			}
			elseif ($this->db->refMysqliConnection->errno == DatabaseAccess::ER_LOCK_WAIT_TIMEOUT)
			{
				// Failure -- Lock wait timeout
				throw new Exception_Database_LockTimeout($this->Error());
			}
	 	}
	 	
	 	// Profiling
	 	if ($mixResult instanceof MySQLi_Result)
	 	{
	 		// Accessor
	 		$aExecutionProfile['iResults']	= $mixResult->num_rows;
	 	}
	 	else
	 	{
	 		// Modifier
	 		$aExecutionProfile['iAffectedRows']	= $this->db->refMysqliConnection->affected_rows;
	 		
	 		if ($this->db->refMysqliConnection->insert_id)
	 		{
	 			$aExecutionProfile['iInsertId']		= $this->db->refMysqliConnection->insert_id;
	 		}
	 	}
	 	
	 	$aExecutionProfile['fDuration']	= microtime(true) - $aExecutionProfile['fStartTime'];
	 	$aExecutionProfile['iResults']	= $this->_stmtSqlStatment->num_rows;
		if ($this->db->getProfilingEnabled()) {
			$aProfiling['aExecutions'][]	= $aExecutionProfile;
		}
	 	
	 	$this->Debug($mixResult);
		return $mixResult;
	 }
	 
	 // Returns the number of records affected by the last INSERT, UPDATE, REPLACE or DELETE query executed
	 function AffectedRows()
	 {
	 	return mysqli_affected_rows($this->db->refMysqliConnection);
	 }

	 public static function run($sQuery, array $aData=null, $sConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT) {
		//Log::getLog()->log("Running Query: {\n{$sQuery}\n}");

		$oQuery	= new Query($sConnectionType);

		// Prepare Query with data
		//Log::getLog()->log("Performing variable replacement on ".count($aData)." possible aliases");
		$aData			= is_array($aData) ? $aData : array();
		$aReplaceData	= array();
		foreach ($aData as $sKey=>$mValue) {
			$sKey	= (string)$sKey;
			if (preg_match("/^[\w\-]+$/i", $sKey)) {
				$mValue						= self::prepareByPHPType($mValue, $sConnectionType);
				$sRegexKey					= "/\<{$sKey}\>/";
				$aReplaceData[$sRegexKey]	= (is_string($mValue)) ? addcslashes($mValue, '$\\') : $mValue;
				//Log::getLog()->log("Match pair '{$sRegexKey}':{$mValue} registered");
			}
		}
		$sQuery	= preg_replace(array_keys($aReplaceData), array_values($aReplaceData), (string)$sQuery);
		//Log::getLog()->log("Final Query form: {\n{$sQuery}\n}");

		// Run Query
		//Log::getLog()->log("Executing Query");
		if (false === ($mResult = $oQuery->Execute($sQuery)) && !$bSilentFail) {
			throw new Exception_Database($oQuery->Error());
		}
		return ($mResult instanceof mysqli_result) ? new Query_Result($mResult) : $mResult;
	 }

	 public static function prepareByPHPType($mValue, $sConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT) {
		//Log::getLog()->log("Preparing ".gettype($mValue)." '{$mValue}'");

		// If the value is a callback, then use its result
		$iNesting	= 0;
		while (is_object($mValue) && $mValue instanceof Callback) {
			$iNesting++;
			//Log::getLog()->log("Invoking Callback at nesting {$iNesting}...");
			if ($iNesting > self::PREPARE_CALLBACK_NESTING_MAX) {
				throw new Exception_Database("Exceeded Query::prepareByPHPType() maximum nesting depth of ".self::PREPARE_CALLBACK_NESTING_MAX);
			}
			$mValue	= $mValue->invoke();
		}

		// Escape/Cast/Convert value
		if (is_int($mValue)) {
			// Integers are fine as they are
			//Log::getLog()->log("Integer {$mValue} returned unmodified");
			return $mValue;
		} elseif (is_float($mValue)) {
			// Floats are fine as they are
			//Log::getLog()->log("Float {$mValue} returned unmodified");
			return $mValue;
		} elseif (is_bool($mValue)) {
			// Booleans need to be integerified
			$iValue	= (int)$mValue;
			//Log::getLog()->log("Boolean {$mValue} converted to {$iValue}");
			return $iValue;
		} elseif (is_null($mValue)) {
			// Nulls become the unescaped string NULL
			//Log::getLog()->log("Null returned as unescaped string NULL");
			return 'NULL';
		} else {
			// Assume everything else is a string (or can be toString()'d), enclosed by single-quotes
			$sValue	= "'".DataAccess::getDataAccess($sConnectionType)->refMysqliConnection->escape_string((string)$mValue)."'";
			//Log::getLog()->log("String/other {$mValue} returned as {$sValue}");
			return $sValue;
		}
	 }
}

?>
