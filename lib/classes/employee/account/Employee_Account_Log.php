<?php
/**
 * Employee_Account_Log
 *
 * Models a record of the employee_account_log table
 *
 * Models a record of the employee_account_log table
 *
 * @class	Employee_Account_Log
 */
class Employee_Account_Log extends ORM_Cached
{
	const SEVERITY_WARNINGS_NOT_APPLICABLE	= null;
	const SEVERITY_WARNINGS_DECLINED		= 0;
	const SEVERITY_WARNINGS_ACCEPTED		= 1;
	
	protected 			$_strTableName			= "employee_account_log";
	protected static	$_strStaticTableName	= "employee_account_log";
	
	public function acceptSeverityWarnings()
	{
		if ($this->accepted_severity_warnings === self::SEVERITY_WARNINGS_NOT_APPLICABLE)
		{
			// Update the severity warnings flag
			$this->accepted_severity_warnings = self::SEVERITY_WARNINGS_ACCEPTED;
			$this->save();
		}
		else if ($this->accepted_severity_warnings === self::SEVERITY_WARNINGS_DECLINED)
		{
			// Create a new record (clone) and set the severity warnings flag
			$oClone 							= new self();
			$oClone->employee_id				= $this->employee_id;
			$oClone->account_id					= $this->account_id;
			$oClone->contact_id					= $this->contact_id;
			$oClose->viewed_on					= date('Y-m-d H:i:s');
			$oClone->accepted_severity_warnings = self::SEVERITY_WARNINGS_ACCEPTED;
			$oClone->save();
		}
		else if ($this->accepted_severity_warnings === self::SEVERITY_WARNINGS_ACCEPTED)
		{
			// Warnings are already accepted
		}
	}
	
	public function declineSeverityWarnings()
	{
		if ($this->accepted_severity_warnings === self::SEVERITY_WARNINGS_NOT_APPLICABLE)
		{
			// Update the severity warnings flag
			$this->accepted_severity_warnings = self::SEVERITY_WARNINGS_DECLINED;
			$this->save();
		}
		else if ($this->accepted_severity_warnings === self::SEVERITY_WARNINGS_DECLINED)
		{
			// Already declined
		}
		else if ($this->accepted_severity_warnings === self::SEVERITY_WARNINGS_ACCEPTED)
		{
			throw new Exception("Severity Warnings have already been accepted, they cannot be declined.");
		}
	}
	
	public static function createIfNotExistsForToday($iEmployeeId, $iAccountId=null, $iContactId=null)
	{
		if ($iAccountId === NULL && $iContactId === NULL)
		{
			throw new Exception("Cannot log create employee_account_log when both account_id and contact_id are NULL");
		}
		
		// Make sure there is a contact id or an account
		if ($iContactId !== NULL && $iAccountId === NULL)
		{
			// We have a contact, but don't have an account, use the contact's default account
			if (($oContact = Contact::getForId($iContactId)) === NULL)
			{
				// The contact cannot be found
				throw new Exception("Contact with id: $iContactId could not be found");
			}
			
			$iAccountId = $oContact->Account;
		}
		
		$oLatest 		= self::getLatestForEmployeeAndAccount($iEmployeeId, $iAccountId);
		$iStartToday	= strtotime(date('Y-m-d 00:00:00'));
		if (($oLatest === null) || (strtotime($oLatest->viewed_on) < $iStartToday))
		{
			// None or was before today, create a new record
			$oNew 				= new self();
			$oNew->employee_id 	= $iEmployeeId;
			$oNew->account_id	= $iAccountId;
			$oNew->viewed_on	= date('Y-m-d H:i:s');
			$oNew->save();
		}
		else
		{
			// Use latest
			return $oLatest;
		}
	}
	
	public static function getLatestForEmployeeAndAccount($iEmployeeId, $iAccountId)
	{
		$oSelect = self::_preparedStatement('selLatestByEmployeeAndAccount');
		if ($oSelect->Execute(array('employee_id' => $iEmployeeId, 'account_id' => $iAccountId)) === false)
		{
			throw new Exception_Database("Failed to get latest for Employee ({$iEmployeeId}) and Account ({$iAccountId}). ".$oSelect->Error());
		}
		
		$aRow = $oSelect->Fetch();
		if ($aRow)
		{
			return new self($aRow);
		}
		
		return null;
	}
	
	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mObjects)
	{
		parent::addToCache($mObjects, __CLASS__);
	}

	public static function getForId($iId, $bSilentFail=false)
	{
		return parent::getForId($iId, $bSilentFail, __CLASS__);
	}
	
	public static function getAll($bForceReload=false)
	{
		return parent::getAll($bForceReload, __CLASS__);
	}
		
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($sStatement)
	{
		static	$arrPreparedStatements	= Array();
		
		if (isset($arrPreparedStatements[$sStatement]))
		{
			return $arrPreparedStatements[$sStatement];
		}
		else
		{
			switch ($sStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "Id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "Name");
					break;
				case 'selLatestByEmployeeAndAccount':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "account_id = <account_id> AND employee_id = <employee_id>", "viewed_on DESC", 1);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$sStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$sStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$sStatement} does not exist!");
			}
			return $arrPreparedStatements[$sStatement];
		}
	}
}
?>