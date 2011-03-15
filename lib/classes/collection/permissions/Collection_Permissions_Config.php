<?php
/**
 * Collection_Permissions_Config
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collection_Permissions_Config
 */
class Collection_Permissions_Config extends ORM_Cached
{
	protected 			$_strTableName			= "collection_permissions_config";
	protected static	$_strStaticTableName	= "collection_permissions_config";
	
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

	public static function getOptimalConfigValuesForEmployee($iEmployeeId)
	{
		return self::getOptimalConfigValuesForPermissions(Employee::getForId($iEmployeeId)->Privileges);
	}
	
	public static function getOptimalConfigValuesForPermissions($iPermissions)
	{
		$oSelect = self::_preparedStatement('selOptimalValuesForPermissions');
		if ($oSelect->Execute(array('permissions' => $iPermissions)) === false)
		{
			throw new Exception_Databases("Failed to get final collection_permissions_config values for permission {$iPermissions}. ".$oSelect->Error());
		}
		return $oSelect->Fetch();
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
				case 'selOptimalValuesForPermissions':
					$arrPreparedStatements[$strStatement]	=	new StatementSelect(
																	self::$_strStaticTableName, 
																	"	MAX(suspension_maximum_days) 								AS suspension_maximum_days,
																		MAX(promise_start_delay_maximum_days)	 					AS promise_start_delay_maximum_days,
																		MAX(promise_maximum_days_between_due_and_end) 				AS promise_maximum_days_between_due_and_end,
																		MAX(promise_instalment_maximum_interval_days) 				AS promise_instalment_maximum_interval_days,
																		MIN(promise_instalment_minimum_promised_percentage)			AS promise_instalment_minimum_promised_percentage,
																		MAX(promise_can_replace) 									AS promise_can_replace,
																		MAX(promise_create_maximum_severity_level) 					AS promise_create_maximum_severity_level,
																		MAX(promise_amount_maximum) 								AS promise_amount_maximum,
																		MAX(suspension_maximum_suspensions_per_collections_period)	AS suspension_maximum_suspensions_per_collections_period", 
																	"permissions & <permissions> = <permissions>"
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