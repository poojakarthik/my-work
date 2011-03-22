<?php

class DataAccess
 {
	const	PROFILER_LOG_PATH	= 'logs/profiling/data_access/';
	
	const	TRANSACTION_LOGGING	= false;
	
 	private	$_arrSavepoints	= array();
 	
 	//------------------------------------------------------------------------//
	// arrTableDefine
	//------------------------------------------------------------------------//
	/**
	 * arrTableDefine
	 *
	 * Database table Definitions
	 *
	 * Database table Definitions
	 *
	 * @type		array
	 *
	 * @property
	 */
	public $arrTableDefine;
	
	//------------------------------------------------------------------------//
	// refMysqliConnection
	//------------------------------------------------------------------------//
	/**
	 * refMysqliConnection
	 *
	 * Database reference for mysqli
	 *
	 * Database reference for mysqli
	 *
	 * @type		Reference
	 *
	 * @property
	 */
	public $refMysqliConnection;
	
	private $_bProfilingEnabled	= false;
	
	private	$_aProfiling		= array();

	private static $arrDataAccessCache = array();

	public static function getDataAccess($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		if (!array_key_exists($strConnectionType, self::$arrDataAccessCache))
		{
			self::$arrDataAccessCache[$strConnectionType] = new DataAccess($strConnectionType);
		}
		return self::$arrDataAccessCache[$strConnectionType];
	}

	public function getNow($bAsTimestamp=false) {
		// TODO: Caching
		$sDatetime	= array_value(Query::run('SELECT NOW() AS now')->fetch_assoc(), 'now');
		return $bAsTimestamp ? strtotime($sDatetime) : $sDatetime;
	}


	/**
	 * connected()
	 *
	 * Determines whether or not a connection has already been established to a gived
	 * database.
	 *
	 * @param string $strConnectionType A configured database connection
	 * 									(Default is FLEX_DATABASE_CONNECTION_DEFAULT)
	 */
	public static function connected($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		return array_key_exists($strConnectionType, self::$arrDataAccessCache);
	}


 	//------------------------------------------------------------------------//
	// DataAccess() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * DataAccess()
	 *
	 * Constructor for DataAccess
	 *
	 * Constructor for DataAccess
	 * Access instances throught the DataAccess::getDataAccess() factory function.
	 *
	 * @return		void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 * @access private
	 */
	private function __construct($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		// TODO: Remove this once all config files have been ported to the new format
		// This is 'if block' here for backwards compatibility only!
		if ($strConnectionType == FLEX_DATABASE_CONNECTION_DEFAULT &&
			array_key_exists('**arrDatabase', $GLOBALS) &&
			!array_key_exists($strConnectionType, $GLOBALS['**arrDatabase']))
		{
			$GLOBALS['**arrDatabase'][$strConnectionType] = array();
			if (array_key_exists('Type', $GLOBALS['**arrDatabase']))
			{
				$GLOBALS['**arrDatabase'][$strConnectionType]['Type'] = array();
			}
			if (array_key_exists('URL', $GLOBALS['**arrDatabase']))
			{
				$GLOBALS['**arrDatabase'][$strConnectionType]['URL'] = array();
			}
			if (array_key_exists('User', $GLOBALS['**arrDatabase']))
			{
				$GLOBALS['**arrDatabase'][$strConnectionType]['User'] = array();
			}
			if (array_key_exists('Password', $GLOBALS['**arrDatabase']))
			{
				$GLOBALS['**arrDatabase'][$strConnectionType]['Password'] = array();
			}
			if (array_key_exists('Database', $GLOBALS['**arrDatabase']))
			{
				$GLOBALS['**arrDatabase'][$strConnectionType]['Database'] = array();
			}
		}

		// Make sure we have a config
		if ( !array_key_exists('**arrDatabase', $GLOBALS) || !$GLOBALS['**arrDatabase']
		  || !array_key_exists($strConnectionType, $GLOBALS['**arrDatabase']) || !$GLOBALS['**arrDatabase'][$strConnectionType])
		{
			throw new Exception("Database Configuration '$strConnectionType' not found!");
		}

		$arrDBConfig = $GLOBALS['**arrDatabase'][$strConnectionType];

		// Connect to MySQL database
		$this->refMysqliConnection = new mysqli($arrDBConfig['URL'], $arrDBConfig['User'], $arrDBConfig['Password'], $arrDBConfig['Database']);
		
		// Make sure the connection was successful
		if(mysqli_connect_errno())
		{
			// TODO: Make custom DatabaseException();
			throw new Exception();
		}
		
		// Enable AutoCommit
		$this->refMysqliConnection->autocommit(TRUE);
		$this->_bolHasTransaction = FALSE;
		
		// make global database definitions available
		$this->arrTableDefine = new Flex_Data_Model();
	}
	
	//------------------------------------------------------------------------//
	// FetchTableDefine
	//------------------------------------------------------------------------//
	/**
	 * FetchTableDefine()
	 *
	 * return the definition for a table
	 *
	 * return the definition for a table
	 *
	 * @param		string	name of the table
	 * @return		mixed	array table definition or FALSE if table doesn't exist
	 *
	 * @method
	 */
	function FetchTableDefine($strTableName)
	{
		if($this->arrTableDefine->{$strTableName})
		{
			return $this->arrTableDefine->{$strTableName};
		}
		else
		{
			return FALSE;
		}
	}
	
	//------------------------------------------------------------------------//
	// FetchAllTableDefinitions
	//------------------------------------------------------------------------//
	/**
	 * FetchAllTableDefinitions()
	 *
	 * returns an array declaring all table definitions for the database requested
	 *
	 * returns an array declaring all table definitions for the database requested
	 *
	 * @return		mixed	array of table definitions for tables that exist on the database this DataAccess object points to
	 *
	 * @method
	 */
	function FetchAllTableDefinitions()
	{
		$arrAllTables = $this->arrTableDefine->getAll();
		$arrTables = array();
		
		// Check what tables exist in this database
		foreach ($arrAllTables as $strTableName=>$arrTableDefinition)
		{
			$strQuery = "SELECT {$arrTableDefinition['Id']} FROM {$arrTableDefinition['Name']} LIMIT 1";
			if ($this->refMysqliConnection->query($strQuery) !== FALSE)
			{
				// The table exists
				$arrTables[$arrTableDefinition['Name']] = $arrTableDefinition;
			}
		}
		
		return $arrTables;
	}
	
	
	//------------------------------------------------------------------------//
	// FetchClean
	//------------------------------------------------------------------------//
	/**
	 * FetchClean()
	 *
	 * return an empty record from a database table
	 *
	 * return an empty record from a database table
	 * uses the database define to create the record
	 * does not talk to the database at all
	 *
	 * @param		string	name of the table
	 * @return		mixed	array record or FALSE if table doesn't exist
	 *
	 * @method
	 */
	function FetchClean($strTableName)
	{
		if($this->arrTableDefine->{$strTableName})
		{
			foreach($this->arrTableDefine->{$strTableName}['Column'] as $strKey => $strValue)
			{
				$arrClean[$strKey] = '';
			}
			return $arrClean;
		}
		else
		{
			return FALSE;
		}
	}
	
	//------------------------------------------------------------------------//
	// FetchCleanOblib
	//------------------------------------------------------------------------//
	/**
	 * FetchCleanOblib()
	 *
	 * return an empty record from a database table into an oblib object
	 *
	 * return an empty record from a database table into an oblib object
	 * uses the database define to create the record
	 * does not talk to the database at all
	 *
	 * @param		string	$strTableName		name of the table
	 * @param		object	$oblobjPushObject	the pile of crap oblib object to fetch into
	 *
	 * @return		bool
	 *
	 * @method
	 */
	function FetchCleanOblib($strTableName, $oblobjPushObject)
	{
		// return false if we were not passed an oblib object
		if (!is_subclass_of ($oblobjPushObject, 'data') || !method_exists ($oblobjPushObject, 'Push'))
		{
			return FALSE;
		}
		
		// retunr false if table does not exist
		if(!$this->arrTableDefine->{$strTableName})
		{
			return FALSE;
		}
		
		foreach($this->arrTableDefine->{$strTableName}['Column'] as $strKey => $strValue)
		{
			$arrClean[$strKey] = '';
			// Create a new instance of an oblib object using the ObLib parameter of the database definition
			if (isset ($strValue["ObLib"]))
			{
				$oblobjPushObject->Push
				(
					new $strValue["ObLib"]
					(
						$strKey, ''
					)
				);
			}
		}

		// add in an Id
		$oblobjPushObject->Push (new dataInteger ("Id", 0));
	
		// is oblib a bloated pile om monkey puke ?
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// TransactionStart
	//------------------------------------------------------------------------//
	/**
	 * TransactionStart()
	 *
	 * Starts a Transaction
	 *
	 * Starts a Transaction
	 *
	 * @return		boolean					TRUE	: Committed
	 * 										FALSE	: Failed
	 *
	 * @method
	 */
	function TransactionStart($bSilentFail=true)
	{
		if ($this->_bolHasTransaction)
		{
			// Create a Savepoint to simulate Nested Transactions
			$strSavepointUID	= "FLEX_NESTED_".sha1(time() * (rand(1, 100) / 100));
			
			if (self::TRANSACTION_LOGGING)
			{
				Log::getLog()->log("Creating Savepoint '{$strSavepointUID}'...");
			}
			
			if (!$this->refMysqliConnection->query("SAVEPOINT {$strSavepointUID}"))
			{
				if (self::TRANSACTION_LOGGING)
				{
					Log::getLog()->log($this->refMysqliConnection->error);
				}
				
				// Failure
				// TODO: Throw an Exception
				//throw new Exception("Unable to create Savepoint with UID '{$strSavepointUID}'}: ".$this->refMysqliConnection->error);
				if ($bSilentFail !== false) {
					return false;
				}
				throw new Exception_Database_Transaction("Unable to create Savepoint with UID '{$strSavepointUID}'}: ".$this->refMysqliConnection->error);
			}
			
			array_push($this->_arrSavepoints, $strSavepointUID);
			return true;
		}
		else
		{
			if (self::TRANSACTION_LOGGING)
			{
				Log::getLog()->log("Starting transaction...");
			}
			
			// Create a Transaction
			$this->_bolHasTransaction = true;
			
			// Make sure the table doesn't lock if PHP dies
			register_shutdown_function(Array($this, "__shutdown"));
			
			// Disable Auto-Commit
			if (!$this->refMysqliConnection->autocommit(false)) {
				if ($bSilentFail !== false) {
					return false;
				}
				throw new Exception_Database_Transaction("Unable to start a Transaction: ".$this->refMysqliConnection->error);
			}
			return true;
		}
	}
	
	//------------------------------------------------------------------------//
	// TransactionRollback
	//------------------------------------------------------------------------//
	/**
	 * TransactionRollback()
	 *
	 * Rolls back the current Transaction, then re-enables AutoCommit
	 *
	 * Rolls back the current Transaction, then re-enables AutoCommit
	 *
	 * @return		boolean					TRUE	: Rolled back
	 * 										FALSE	: Failed
	 *
	 * @method
	 */
	function TransactionRollback($bSilentFail=true)
	{
		if (!$this->_bolHasTransaction)
		{
			if (self::TRANSACTION_LOGGING)
			{
				Log::getLog()->log("No Transaction to roll back!");
			}
			
			// No transaction to roll back
			if ($bSilentFail !== false) {
				return false;
			}
			throw new Exception_Database_Transaction("No Transaction to rollback");
		}
		elseif (count($this->_arrSavepoints))
		{
			// Roll back to last Savepoint
			$strSavepointUID	= array_pop($this->_arrSavepoints);
			
			if (self::TRANSACTION_LOGGING)
			{
				Log::getLog()->log("Rolling back to Savepoint '{$strSavepointUID}'...");
			}
			
			if (!$this->refMysqliConnection->query("ROLLBACK TO SAVEPOINT {$strSavepointUID}"))
			{
				if (self::TRANSACTION_LOGGING)
				{
					Log::getLog()->log($this->refMysqliConnection->error);
				}
				
				// Failure
				// TODO: Throw an Exception
				if ($bSilentFail !== false) {
					return false;
				}
				throw new Exception_Database_Transaction("Unable to rollback to Savepoint with UID '{$strSavepointUID}'}: ".$this->refMysqliConnection->error);
			}
		}
		else
		{
			if (self::TRANSACTION_LOGGING)
			{
				Log::getLog()->log("Rolling back transaction...");
			}
			
			// Roll back, then disable transactioning
			$this->_bolHasTransaction	= false;
			if (!($this->refMysqliConnection->rollback() && $this->refMysqliConnection->autocommit(true))) {
				if ($bSilentFail !== false) {
					return false;
				}
				throw new Exception_Database_Transaction("Unable to rollback the Transaction: ".$this->refMysqliConnection->error);
			}
			return true;
		}
	}
	
	//------------------------------------------------------------------------//
	// TransactionCommit
	//------------------------------------------------------------------------//
	/**
	 * TransactionCommit()
	 *
	 * Commits the current Transaction, then re-enables AutoCommit
	 *
	 * Commits the current Transaction, then re-enables AutoCommit
	 *
	 * @return		boolean					TRUE	: Started
	 * 										FALSE	: Failed
	 *
	 * @method
	 */
	function TransactionCommit($bSilentFail=true)
	{
		if (!$this->_bolHasTransaction)
		{
			if (self::TRANSACTION_LOGGING)
			{
				Log::getLog()->log("No Transaction to commit!");
			}
			
			// No transaction to commit
			if ($bSilentFail !== false) {
				return false;
			}
			throw new Exception_Database_Transaction("No Transaction to commit");
		}
		elseif (count($this->_arrSavepoints))
		{
			// Roll back to last Savepoint
			$strSavepointUID	= array_pop($this->_arrSavepoints);
			
			if (self::TRANSACTION_LOGGING)
			{
				Log::getLog()->log("Releasing Savepoint '{$strSavepointUID}'...");
			}
			
			if (!$this->refMysqliConnection->query("RELEASE SAVEPOINT {$strSavepointUID}"))
			{
				if (self::TRANSACTION_LOGGING)
				{
					Log::getLog()->log("Unable to release savepoint '{$strSavepointUID}': {$this->refMysqliConnection->error}");
				}
				
				// Failure
				// TODO: Throw an Exception
				if ($bSilentFail !== false) {
					return false;
				}
				throw new Exception_Database_Transaction("Unable to commit/release Savepoint with UID '{$strSavepointUID}'}: ".$this->refMysqliConnection->error);
			}
			else
			{
				return true;
			}
		}
		else
		{
			if (self::TRANSACTION_LOGGING)
			{
				Log::getLog()->log("Committing transaction...");
			}
			
			// Commit, then disable transactioning
			$this->_bolHasTransaction	= false;
			
			$bCommitted		= $this->refMysqliConnection->commit();
			if (!$bCommitted)
			{
				if (self::TRANSACTION_LOGGING)
				{
					Log::getLog()->log("Commit failed: {$this->refMysqliConnection->error}");
				}
			}
			else
			{
				$bAutoCommit	= $this->refMysqliConnection->autocommit(true);
				if (!$bAutoCommit)
				{
					if (self::TRANSACTION_LOGGING)
					{
						Log::getLog()->log("Unable to enable Autocommit Mode: {$this->refMysqliConnection->error}");
					}
				}
			}
			
			if (!$bCommitted || !$bAutoCommit) {
				if ($bSilentFail !== false) {
					return false;
				}
				throw new Exception_Database_Transaction("Unable to commit the Transaction: ".$this->refMysqliConnection->error);
			}
			return true;
		}
	}
	
	public function TransactionCurrent()
	{
		if (!$this->_bolHasTransaction)
		{
			return false;
		}
		elseif (count($this->_arrSavepoints))
		{
			return $this->_arrSavepoints[count($this->_arrSavepoints)];
		}
		else
		{
			return true;
		}
	}
	
	//------------------------------------------------------------------------//
	// __shutdown
	//------------------------------------------------------------------------//
	/**
	 * __shutdown()
	 *
	 * If PHP dies, this will prevent table locking
	 *
	 * If PHP dies, this will prevent table locking
	 *
	 * @method
	 */
	function __shutdown()
	{
		// Roll back all transactions
		while ($this->_bolHasTransaction)
		{
			//$this->refMysqliConnection->rollback();
			$this->TransactionRollback();
		}
	}
	
	function __destruct()
	{
		try
		{
			if ($this->getProfilingEnabled())
			{
				// Write out Profiling Data to log file
				$this->exportProfilingToXML();
			}
		}
		catch (Exception $eException)
		{
			echo $eException->__toString();
		}
	}
	
	function escape($value)
	{
		return $this->refMysqliConnection->real_escape_string($value);
	}
	
	public function addToProfiler(DatabaseAccess $oDatabaseAccess)
	{
		if ($this->getProfilingEnabled())
		{
			$this->_aProfiling[]	= $oDatabaseAccess;
		}
	}
	
	public function exportProfilingToXML($sXMLPath=null)
	{
		if ($sXMLPath)
		{
			$sXMLPath	= trim($sXMLPath);
			$sSavePath	= ($sXMLPath[0] === '/') ? $sXMLPath : FILES_BASE_PATH.self::PROFILER_LOG_PATH.$sXMLPath;
		}
		else
		{
			$sSavePath	= FILES_BASE_PATH.self::PROFILER_LOG_PATH.date("YmdHis+u").'.xml';
		}
		
		@mkdir(dirname($sSavePath), 0777, true);
		
		// Save the XML
		self::_profilingToXML($this->_aProfiling)->save($sSavePath);
	}
	
	static private function _profilingToXML($aProfilingData)
	{
		$domDocument				= new DOMDocument('1.0', 'UTF-8');
		$domDocument->formatOutput	= true;
		
		$eDatabaseAccesses	= new DOMElement('database-accesses');
		$domDocument->appendChild($eDatabaseAccesses);
		
		foreach ($aProfilingData as $oDatabaseAccess)
		{
			$aDatabaseAccessProfile	= $oDatabaseAccess->aProfiling;
			
			$eDatabaseAccess	= new DOMElement('database-access');
			$eDatabaseAccesses->appendChild($eDatabaseAccess);
			
			// Query
			$eQuery	= new DOMElement('query', $aDatabaseAccessProfile['sQuery']);
			$eDatabaseAccess->appendChild($eQuery);
			
			// Prepare
			if (array_key_exists('fPreparationTime', $aDatabaseAccessProfile) || array_key_exists('fPreparationStart', $aDatabaseAccessProfile))
			{
				$ePrepare	= new DOMElement('prepare');
				$eDatabaseAccess->appendChild($ePrepare);
				
				if (array_key_exists('fPreparationStart', $aDatabaseAccessProfile))
				{
					$ePrepareStart	= new DOMElement('start', date("Y-m-d H:i:s.u", $aDatabaseAccessProfile['fPreparationStart']));
					$ePrepare->appendChild($ePrepareStart);
				}
				if (array_key_exists('fPreparationTime', $aDatabaseAccessProfile))
				{
					$ePrepareDuration	= new DOMElement('duration', $aDatabaseAccessProfile['fPreparationTime']);
					$ePrepare->appendChild($ePrepareDuration);
				}
			}
			
			// Executions
			$eExecutions	= new DOMElement('executions');
			$eDatabaseAccess->appendChild($eExecutions);
			foreach ($aDatabaseAccessProfile['aExecutions'] as $aExecution)
			{
				$eExecution	= new DOMElement('execution');
				$eExecutions->appendChild($eExecution);
				
				// Time
				$eExecutionStart	= new DOMElement('start-time', date("Y-m-d H:i:s.u", $aExecution['fStartTime']));
				$eExecution->appendChild($eExecutionStart);
				$eExecutionDuration	= new DOMElement('duration', $aExecution['fDuration']);
				$eExecution->appendChild($eExecutionDuration);
				
				// Results
				if (array_key_exists('iResults', $aExecution))
				{
					$eResults	= new DOMElement('results', $aExecution['iResults']);
					$eExecution->appendChild($eResults);
				}
				if (array_key_exists('iInsertId', $aExecution))
				{
					$eInsertId	= new DOMElement('insert-id', $aExecution['iInsertId']);
					$eExecution->appendChild($eInsertId);
				}
				if (array_key_exists('iRowsAffected', $aExecution))
				{
					$eRowsAffected	= new DOMElement('rows-affected', $aExecution['iRowsAffected']);
					$eExecution->appendChild($eRowsAffected);
				}
				
				// Where
				if (array_key_exists('aWhere', $aExecution))
				{
					$eWhere	= new DOMElement('where');
					$eExecution->appendChild($eWhere);
					
					foreach ($aExecution['aWhere'] as $sAlias=>$mValue)
					{
						$eWhereParameter	= new DOMElement(preg_replace("/\W+/i", '-', $sAlias), $mValue);
						$eWhere->appendChild($eWhereParameter);
					}
				}
				
				// Data
				if (array_key_exists('aData', $aExecution))
				{
					$eData	= new DOMElement('data');
					$eExecution->appendChild($eData);
					
					foreach ($aExecution['aData'] as $sAlias=>$mValue)
					{
						$eDataParameter	= new DOMElement(preg_replace("/\W+/i", '-', $sAlias), $mValue);
						$eData->appendChild($eDataParameter);
					}
				}
			}
		}
		
		return $domDocument;
	}
	
	public function setProfilingEnabled($bEnabled)
	{
		$this->_bProfilingEnabled	= (bool)$bEnabled;
	}
	
	public function getProfilingEnabled()
	{
		return $this->_bProfilingEnabled;
	}
	
	//------------------------------------------------------------------------//
	// Error()
	//------------------------------------------------------------------------//
	/**
	 * Error()
	 *
	 * Return an SQL error message
	 *
	 * Returns the latest SQL error message
	 *
	 * @return		string					SQL Error Message
	 *
	 * @method
	 */
	function Error()
	{
		if (mysqli_error($this->refMysqliConnection))
		{
			$strReturn = mysqli_error($this->refMysqliConnection);
			$strReturn .= "\n Call Stack:\n".Backtrace(debug_backtrace())."\n";
			return $strReturn;
		}
		
		// There was no error
		return FALSE;
	}
}

?>