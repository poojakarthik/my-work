<?php
class Correspondence_Run_Dispatch_Delivery_Method extends ORM_Cached
{
	protected 			$_strTableName			= "correspondence_run_dispatch_delivery_method";
	protected static	$_strStaticTableName	= "correspondence_run_dispatch_delivery_method";

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

	public static function getForDispatchId($iDispatchId)
	{
		$oQuery	= new Query();
		$sSql	= "	SELECT 	*
					FROM 	".self::$_strStaticTableName."
					WHERE 	correspondence_run_dispatch_id = {$iDispatchId}";
		$mResult	= $oQuery->Execute($sSql);
		$aResult 	= array();
		if ($mResult)
		{
			while ($aRow = $mResult->fetch_assoc())
			{
				$aResult[] = new self($aRow);
			}
		}

		return $aResult;
	}


	public static function getForDeliveryMethodAndFileExportRecord($iMethod, $iRunFileExportId)
	{
		$oQuery	= new Query();
		$sSql	= "	SELECT 	*
					FROM 	".self::$_strStaticTableName."
					WHERE 	correspondence_run_dispatch_id = {$iRunFileExportId}
					AND 	correspondence_delivery_method_id = {$iMethod}";
		$oQuery		= new Query();
		$mResult	= $oQuery->Execute($sSql);
		$aRow 		= $mResult->fetch_assoc();
		return ($aRow['id'] == null ? null :  new self($aRow));
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
				case 'selByBatchId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "correspondence_run_batch_id =  <correspondence_run_batch_id> ");
					break;
				case 'selByScheduleDateTime':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "scheduled_datetime <= <scheduled_datetime> AND correspondence_run_error_id IS NULL AND delivered_datetime IS NULL");
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