<?php
/**
 * Adjustment_Type_System_Config
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Adjustment_Type_System_Config
 */
class Adjustment_Type_System_Config extends ORM_Cached
{
	protected 			$_strTableName			= "adjustment_type_system_config";
	protected static	$_strStaticTableName	= "adjustment_type_system_config";
	
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

	public static function getAdjustmentTypeForSystemAdjustmentType($iSystemAdjustmentType, $mEffectiveDatetime=null)
	{
		if ($mEffectiveDatetime === null)
		{
			$sEffectiveDatetime	= date('Y-m-d H:i:s');
		}
		else if (is_numeric($mEffectiveDatetime))
		{
			$sEffectiveDatetime	= date('Y-m-d H:i:s', $mEffectiveDatetime);
		}
		else
		{
			$sEffectiveDatetime	= $mEffectiveDatetime;
		}
		
		$oStmt	= self::_preparedStatement('selForSystemAdjustmentTypeAndEffectiveDatetime');
		if ($oStmt->Execute(array('adjustment_type_system_id' => $iSystemAdjustmentType, 'effective_datetime' => $sEffectiveDatetime)) === false)
		{
			throw new Exception_Database("Failed to find System Adjustment Type config. ".$oStmt->Error());
		}
		
		$aResult	= $oStmt->Fetch();
		if (!$aResult || !isset($aResult['adjustment_type_id']))
		{
			throw new Exception_Database("Cannot find config for given System Adjustment Type, effective '{$sEffectiveDatetime}'. ".$oStmt->Error());
		}
		
		return Adjustment_Type::getForId($aResult['adjustment_type_id']);
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
				case 'selForSystemAdjustmentTypeAndEffectiveDatetime':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "adjustment_type_system_id = <adjustment_type_system_id> AND <effective_datetime> BETWEEN start_datetime AND end_datetime", "id DESC", 1);
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