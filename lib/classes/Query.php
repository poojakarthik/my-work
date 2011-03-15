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
}

?>
