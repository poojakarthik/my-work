<?php
/**
 * Data_Report_Operation_Profile
 *
 * This class represents a data_report_operation_profile table record
 *
 * @class	Data_Report_Operation_Profile
 */
class Data_Report_Operation_Profile extends ORM_Cached
{
	protected 			$_strTableName			= "data_report_operation_profile";
	protected static	$_strStaticTableName	= "data_report_operation_profile";
	
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
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function getForDataReportId($iDataReportId)
	{
		$aResult	= array();
		$oSelect	= self::_preparedStatement('selByDataReportId');
		$oSelect->Execute(array('data_report_id' => $iDataReportId));
		
		while($aSelf = $oSelect->Fetch())
		{
			$aResult[$aSelf['operation_profile_id']]	= Operation_Profile::getForId($aSelf['operation_profile_id']);
		}
		
		return $aResult;
	}

	public static function removeForDataReportId($iDataReportId)
	{
		$sTable		= self::$_strStaticTableName;
		$oQuery		= new Query();
		$sQuery 	= "	DELETE FROM	{$sTable}" .
					"	WHERE		data_report_id = {$iDataReportId};";
		$oResult	= $oQuery->Execute($sQuery);
		
		if ($oResult === false)
		{
			throw new Exception("Error deleting from $sTable: {$sQuery}");
		}
		
		return $oResult;
	}

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement	Name of the statement
	 * 
	 * @return	Statement					The requested Statement
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				case 'selByDataReportId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "data_report_id = <data_report_id>");
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