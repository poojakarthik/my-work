<?php
/**
 * Collection_Severity_Restriction
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collection_Severity_Restriction
 */
class Collection_Severity_Restriction extends ORM_Cached
{
	protected 			$_strTableName			= "collection_severity_restriction";
	protected static	$_strStaticTableName	= "collection_severity_restriction";
	
	public function getRestriction()
	{
		return Collection_Restriction::getForId($this->collection_restriction_id);
	}
	
	/**
     * return Collection_Restriction ORMs for the collection_severity id passed in
     *
     */
    public static function getForSeverityId($iSeverityId)
    {
	   	$oStatement = self:: _preparedStatement('selBySeverityId');
	    if ($oStatement->Execute(array('collection_severity_id'=>$iSeverityId)) === false)
	    {
	    	throw new Exception_Database("Failed to get for severity id {$iSeverityId}. ".$oSelect->Error());
	    }		
	    return ORM::importResult($oStatement->FetchAll(), 'Collection_Severity_Warning');
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

	public static function removeForSeverityId($iSeverityId)
	{
		$oQuery = new Query();
		
		// Turn fk's off
		$mResult = $oQuery->Execute("SET foreign_key_checks = 0;");
		if ($mResult === false)
		{
			throw new Exception_Database("Failed to turn off fk's prior to removing restrictions for severity. ".$oQuery->Error());
		}
		
		// Delete the records
		$mResult = $oQuery->Execute("	DELETE FROM collection_severity_restriction
										WHERE		collection_severity_id = {$iSeverityId};");
		if ($mResult === false)
		{
			// Turn fk's back on
			$mResult	= $oQuery->Execute("SET foreign_key_checks = 1;");
			if ($mResult === false)
			{
				throw new Exception_Database("Failed to turn ON fk's after exception thrown. ".$oQuery->Error());
			}
			
			throw new Exception_Database("Failed to remove collection_severity_restriction records. ".$oQuery->Error());
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
                case 'selBySeverityId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "collection_severity_id = <collection_severity_id>");
					break;
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