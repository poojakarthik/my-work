<?php
/**
 * Operation_Prerequisite
 *
 * Models the operation_prerequisite Table
 *
 * @class	Operation_Prerequisite
 */
class Operation_Prerequisite extends ORM_Cached
{
	protected 			$_strTableName			= "operation_prerequisite";
	protected static	$_strStaticTableName	= "operation_prerequisite";
	
	public static function getForOperation($mOperation)
	{
		static	$qQuery;
		$qQuery	= ($qQuery) ? $qQuery : new Query();
		
		$iOperationId	= ($mOperation instanceof Operation) ? $mOperation->id : (int)$mOperation;
		
		$sGetForOperationSQL	= "	SELECT		id
									FROM		operation_prerequisite
									WHERE		operation_id = {$iOperationId}";
		$rGetForOperation	= $qQuery->Execute($sGetForOperationSQL);
		if ($rGetForOperation === false)
		{
			throw new Exception($qQuery->Error());
		}
		
		$aPrerequisites	= array();
		while ($aPrerequisiteId = $rGetForOperation->fetch_assoc())
		{
			$aPrerequisites[]	= self::getForId($aPrerequisiteId['id']);
		}
		
		return $aPrerequisites;
	}
	
	//------------------------------------------------------------------------//
	//				START - CACHE FUNCTIONS
	//------------------------------------------------------------------------//
	
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
		return PHP_INT_MAX;
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "name ASC");
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