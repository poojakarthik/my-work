<?php
/**
 * Account_TIO_Complaint
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Account_TIO_Complaint
 */
class Account_TIO_Complaint extends ORM_Cached
{
	protected 			$_strTableName			= "account_tio_complaint";
	protected static	$_strStaticTableName	= "account_tio_complaint";
	
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
	
	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}
	
	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}
	
	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public function getCurrentForAccountId($iAccountId)
	{
		// Get result
		$oSelect = self::_preparedStatement('selByAccountAndCurrent');
		if ($oSelect->Execute(array('account_id' => $iAccountId)) === false)
		{
			throw new Exception_Database("Failed to get current account tio complaint for account {$iAccountId}. ".$oSelect->Error());
		}
		
		// Return orm, if result found
		$aRow = $oSelect->Fetch();
		return ($aRow ? new self($aRow) : null);
	}

	public static function getForCollectionSuspensionId($iSuspensionId)
	{
		$aRow = Query::run("SELECT	*
							FROM	account_tio_complaint
							WHERE	collection_suspension_id = {$iSuspensionId}")->fetch_assoc();
		return ($aRow ? new self($aRow) : null);
	}

	public function end($iEndReasonId)
	{
		// End Suspension
		Collection_Suspension::getForId($this->collection_suspension_id)->end($iEndReasonId);
		
		// Remove tio ref from account record
		$oAccount 						= Account::getForId($this->account_id);
		$oAccount->tio_reference_number	= null;
		$oAccount->save();
	}

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 *
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selByAccountAndCurrent':
					$arrPreparedStatements[$strStatement]	=	new StatementSelect(
																	"			account_tio_complaint atc
																		JOIN	collection_suspension cs ON (
																					cs.id = atc.collection_suspension_id
																					AND cs.account_id = atc.account_id
																					AND cs.effective_end_datetime IS NULL
																				)",
																	"atc.*", 
																	"atc.account_id = <account_id>", 
																	null, 
																	1
																);
					break;
				
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>