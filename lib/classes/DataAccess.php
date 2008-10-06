<?php

class DataAccess
 {
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


	private static $arrDataAccessCache = array();

	public static function getDataAccess($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	{
		if (!array_key_exists($strConnectionType, self::$arrDataAccessCache))
		{
			self::$arrDataAccessCache[$strConnectionType] = new DataAccess($strConnectionType);
		}
		return self::$arrDataAccessCache[$strConnectionType];
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
	function TransactionStart()
	{
		if ($this->_bolHasTransaction)
		{
			// Can't start a new transaction if we already have one
			return FALSE;
		}
		$this->_bolHasTransaction = TRUE;
		
		// Make sure the table doesn't lock if PHP dies
		register_shutdown_function(Array($this, "__shutdown"));
		
		// Disable Auto-Commit
		return $this->refMysqliConnection->autocommit(FALSE);
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
	function TransactionRollback()
	{
		if (!$this->_bolHasTransaction)
		{
			// No transaction to roll back
			return FALSE;
		}
		
		// Roll back, then disable transactioning
		return ($this->refMysqliConnection->rollback() && $this->refMysqliConnection->autocommit(TRUE));
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
	function TransactionCommit()
	{
		if (!$this->_bolHasTransaction)
		{
			// No transaction to commit
			return FALSE;
		}
		
		// Commit, then disable transactioning
		return ($this->refMysqliConnection->commit() && $this->refMysqliConnection->autocommit(TRUE));
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
		if ($this->_bolHasTransaction)
		{
			// No transaction to roll back
			$this->refMysqliConnection->rollback();
		}
	}
	
	function escape($value)
	{
		return $this->refMysqliConnection->real_escape_string($value);
	}
}

?>
