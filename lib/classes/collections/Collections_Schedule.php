<?php
/**
 * Collections_Schedule
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collections_Schedule
 */
class Collections_Schedule extends ORM_Cached
{
	protected 			$_strTableName			= "collections_schedule";
	protected static	$_strStaticTableName	= "collections_schedule";
	
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
	
	public function isActive()
	{
		return $this->status_id == STATUS_ACTIVE;
	}
	
	public static function getEligibility($iForCollectionEventId=null, $bForDirectDebit=false)
	{
		// Get todays values
		$iDay		= (int)date('d');
		$iMonth		= (int)date('m');
		$iYear		= (int)date('Y');
		$iDayOfWeek	= (int)date('N');
		
		$sForCollectionEventId	= ($iForCollectionEventId === null ? 'NULL' : $iForCollectionEventId);
		$sForDirectDebit 		= ($bForDirectDebit === true ? '1' : '0');
		
		// Get rules for today
		$sQuery = "	SELECT	eligibility, precedence
					FROM	collections_schedule
					WHERE	(day IS NULL OR day = {$iDay})
					AND		(month IS NULL OR month = {$iMonth})
					AND		(year IS NULL OR year = {$iYear})
					AND		(
								(monday <> 1 OR {$iDayOfWeek} = 1)
								OR
								(tuesday <> 1 OR {$iDayOfWeek} = 2)
								OR
								(wednesday <> 1 OR {$iDayOfWeek} = 3)
								OR
								(thursday <> 1 OR {$iDayOfWeek} = 4)
								OR
								(friday <> 1 OR {$iDayOfWeek} = 5)
								OR
								(saturday <> 1 OR {$iDayOfWeek} = 6)
								OR
								(sunday <> 1 OR {$iDayOfWeek} = 7)
							)
					AND		status_id = ".STATUS_ACTIVE."
					AND		(
								(collection_event_id IS NULL)
								OR
								(
									{$sForCollectionEventId} IS NOT NULL
									AND collection_event_id = {$sForCollectionEventId}
									AND (is_direct_debit IS NULL OR is_direct_debit = 0)
								)
							)
					AND		(
								(is_direct_debit IS NULL OR is_direct_debit = 0)
								OR
								(
									{$sForDirectDebit} = 1
									AND is_direct_debit = 1
									AND (collection_event_id IS NULL)
								)
							);";
		$mResult = Query::run($sQuery);
		
		// Determine one with the maximum precedence
		$iEligibility 	= 0;
		$iMaxPrecedence	= null;
		while ($aRow = $mResult->fetch_assoc())
		{
			if (($iMaxPrecedence === null) || ($aRow['precedence'] > $iMaxPrecedence))
			{
				$iEligibility = $aRow['eligibility'];
			}
		}
		
		return ($iEligibility == 1);
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