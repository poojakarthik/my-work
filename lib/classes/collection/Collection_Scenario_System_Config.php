<?php
/**
 * Collection_Scenario
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collection_Scenario
 */
class Collection_Scenario_System_Config extends ORM_Cached
{
	protected 			$_strTableName			= "collection_scenario_system_config";
	protected static	$_strStaticTableName	= "collection_scenario_system_config";
	
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
        
        public static function getForSystemScenario($iSystemScenarioId, $bActiveOnly = true)
        {
            if (!$bActiveOnly)
            {
                return  self::getFor(array('collection_scenario_system_id'=>$iSystemScenarioId, 'start_datetime'=>oObject));
            }
            else
            {
                $oQuery =  new Query();
                $sSql = "select * from collection_scenario_system_config c where collection_scenario_system_id = $iSystemScenarioId and start_datetime<=now() and end_datetime>now()";
                 $mResult =$oQuery->Execute($sSql);

                $aResult = array();
                if ($mResult)
                {
                        while ($aRow = $mResult->fetch_assoc())
                        {
                                $aResult[]= new self($aRow);
                        }
                }
                return $aResult[0];
            }
           
        }

         public static function getFor($aCriteria)
        {
            $aWhere	= StatementSelect::generateWhere(null, $aCriteria);
            $oQuery	= new StatementSelect(self::$_strStaticTableName, "*", $aWhere['sClause']);
            $mixResult			= $oQuery->Execute($aWhere['aValues']);
            $arrRecordSet	= $oQuery->FetchAll();
            $aResult = array();
            foreach($arrRecordSet as $aRecord)
            {
                $aResult[] = new self($aRecord);
            }
            return $aResult;
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