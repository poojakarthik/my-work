<?php
/**
 * Collection_Severity
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collection_Severity
 */
class Collection_Severity extends ORM_Cached
{
	protected 			$_strTableName			= "collection_severity";
	protected static	$_strStaticTableName	= "collection_severity";


	
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

    public static function getForSystemName($sSystemName)
    {
        $oStatement = self:: _preparedStatement('getForSysName');
        $oStatement->Execute(array('system_name'=>$sSystemName));

        $aResults	= $oStatement->FetchAll();
        return count($aResults)>0 ? new self($aResults[0]) : null;
    }
    
    public function isActive()
	{
		return $this->working_status_id == WORKING_STATUS_ACTIVE;
	}
	
	public function isDraft()
	{
		return $this->working_status_id == WORKING_STATUS_DRAFT;
	}
	
	public static function isSeverityLevelTaken($iSeverityLevel, $iIgnoreIfBySeverityId=null)
	{
		$oSelect 	= self::_preparedStatement('selBySeverityLevel');
		$iNumRows	= $oSelect->Execute(array('severity_level' => $iSeverityLevel));
		if ($iNumRows === false)
		{
			throw new Exception_Database("Failed to check if severity level is taken, level={$iSeverityLevel}. ".$oSelect->Error());
		}
		
		if ($iNumRows > 0)
		{
			if ($iIgnoreIfBySeverityId === null)
			{
				// Have a row using it already
				return true;
			}
			else
			{
				// Return true, if the row returned doesn't match the given 'ignore' id
				$aRow = $oSelect->Fetch();
				return ($aRow['id'] != $iIgnoreIfBySeverityId); 
			}
		}
	}
	
	public function getForWorkingStatus($mWorkingStatusIds)
	{
		$aWorkingStatusIds 	= (is_array($mWorkingStatusIds) ? $mWorkingStatusIds : array($mWorkingStatusIds));
		$oSelect 			= self::_preparedStatement('selByWorkingStatus');
		if ($oSelect->Execute(array('working_status_ids' => implode(',', $aWorkingStatusIds))) === false)
		{
			throw new Exception_Database("Failed to get severities for working statuses '".implode(',', $aWorkingStatusIds)."'. ".$oSelect->Error());
		}
		return ORM::importResult($oSelect->FetchAll(), 'Collection_Severity');
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
                case 'getForSysName':
                    $arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "system_name = <system_name>", NULL, 1);
					break;
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;
				case 'selBySeverityLevel':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "severity_level = <severity_level>", "id ASC");
					break;
				case 'selByWorkingStatus':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "working_status_id IN (<working_status_ids>)", "name ASC");
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