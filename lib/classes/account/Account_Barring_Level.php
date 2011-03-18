<?php
/**
 * Account_Barring_Level
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Account_Barring_Level
 */
class Account_Barring_Level extends ORM_Cached
{
	protected 			$_strTableName			= "account_barring_level";
	protected static	$_strStaticTableName	= "account_barring_level";
	
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

        public static function getMostRecentForAccount($iAccountId)
        {
            $oQuery = new Query();
            $sSql = "SELECT *
                        FROM account_barring_level
                        WHERE account_id = $iAccountId
                        AND id = (SELECT MAX(id) FROM account_barring_level WHERE account_id = $iAccountId)";
            $mResult = $oQuery->Execute($sSql);
            if ($mResult)
            {
                $aRecord = $mResult->fetch_assoc();
                return $aRecord != null ? new self($aRecord) : null;
            }
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

	public function getScheduledCountOnDayForBarringLevel($sDate=null, $iBarringLevelId)
	{
		if ($sDate === null)
		{
			$sDate = date('Y-m-d');
		}
		
		$oSelect 	= self::_preparedStatement('selScheduleOnDateForBarringLevel');
		$mResult	= $oSelect->Execute(array('effective_date' => $sDate, 'barring_level_id' => $iBarringLevelId));
		if ($mResult === false)
		{
			throw new Exception("Failed to get count of scheduled barrings on '{$sDate}', for barring level '{$iBarringLevel}'.".$oSelect->Error());
		}
		
		return $oSelect->Count();
	}

	public function authorise()
	{
		$this->authorised_datetime 		= date('Y-m-d H:i:s');
		$this->authorised_employee_id	= Flex::getUserId();
		$this->save();
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
				case 'selScheduleOnDateForBarringLevel':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "(<effective_date> BETWEEN authorised_datetime AND actioned_datetime) AND barring_level_id = <barring_level_id>", "id ASC");
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