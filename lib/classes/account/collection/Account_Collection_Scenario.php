<?php
/**
 * Account_Collection_Scenario
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Account_Collection_Scenario
 */
class Account_Collection_Scenario extends ORM_Cached
{
	protected 			$_strTableName			= "account_collection_scenario";
	protected static	$_strStaticTableName	= "account_collection_scenario";
	
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

    public static function getForAccountId($iAccountId, $bActiveOnly = true)
    {

	$oQuery = new Query();
	$sSQL =	"   SELECT *
		    FROM account_collection_scenario
		    WHERE account_id = $iAccountId ";
	$sOrderBy = " ORDER BY start_datetime ASC";
	 $mResult;
	 $aResult= array();

	if ($bActiveOnly)
	{
	    $mResult = $oQuery->Execute($sSQL." AND  start_datetime <= NOW() AND end_datetime > NOW() ".$sOrderBy);
	}
	else
	{
	    $mResult = $oQuery->Execute($sSQL.$sOrderBy);
	}

	if ($mResult)
	{
	    while ($aRecord = $mResult->fetch_assoc())
	    {
		$aResult[] = new self($aRecord);
	    }
	}
	else
	{
	    throw new Exception_Database($oQuery->Error());
	}

	return $aResult;

    }

    public static function getFor($aCriteria)
    {
        $aWhere	= StatementSelect::generateWhere(null, $aCriteria);
        $oQuery	= new StatementSelect(self::$_strStaticTableName, "*", $aWhere['sClause'], "created_datetime ASC");
        $mixResult			= $oQuery->Execute($aWhere['aValues']);
        $arrRecordSet	= $oQuery->FetchAll();
        $aResult = array();
        foreach($arrRecordSet as $aRecord)
        {
            $aResult[] = new self($aRecord);
        }
        return $aResult;
    }
	
	public static function factory($iAccountId, $iScenarioId, $sStartDatetime=null)
	{
		$oRecord							= new self();
		$oRecord->account_id				= $iAccountId;
		$oRecord->collection_scenario_id	= $iScenarioId;
		$oRecord->created_datetime			= date('Y-m-d H:i:s');
		$oRecord->start_datetime			= ($sStartDatetime === null ? date('Y-m-d H:i:s') : $sStartDatetime);
		$oRecord->end_datetime				= Data_Source_Time::END_OF_TIME;
		$oRecord->save();
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