<?php
/**
 * Collection_Scenario_Collection_Event
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collection_Scenario_Collection_Event
 */
class Collection_Scenario_Collection_Event extends ORM_Cached
{
	protected 			$_strTableName			= "collection_scenario_collection_event";
	protected static	$_strStaticTableName	= "collection_scenario_collection_event";

 
	
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

        	public static function getForScenarioId($iScenarioId)
	{
		return self::getFor(array('collection_scenario_id' => $iScenarioId));
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

	public static function removeForScenarioId($iCollectionScenarioId)
	{
		$oQuery = new Query();
		
		// Turn fk's off
		$mResult = $oQuery->Execute("SET foreign_key_checks = 0;");
		if ($mResult === false)
		{
			throw new Exception_Database("Failed to turn off fk's prior to removing records for scenario. ".$oQuery->Error());
		}
		
		// Delete the records
		$mResult = $oQuery->Execute("	DELETE FROM collection_scenario_collection_event
										WHERE		collection_scenario_id = {$iCollectionScenarioId};");
		if ($mResult === false)
		{
			// Turn fk's back on
			$mResult	= $oQuery->Execute("SET foreign_key_checks = 1;");
			if ($mResult === false)
			{
				throw new Exception_Database("Failed to turn ON fk's after exception thrown. ".$oQuery->Error());
			}
			
			throw new Exception_Database("Failed to remove collection_scenario_collection_event records. ".$oQuery->Error());
		}
		
		// Turn fk's back on
		$mResult = $oQuery->Execute("SET foreign_key_checks = 1;");
		if ($mResult === false)
		{
			throw new Exception_Database("Failed to turn ON fk's after successful delete. ".$oQuery->Error());
		}
		
		return true;
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