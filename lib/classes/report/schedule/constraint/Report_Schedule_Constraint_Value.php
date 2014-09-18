<?php
class Report_Schedule_Constraint_Value extends ORM_Cached {
	protected $_strTableName = "report_schedule_constraint_value";
	protected static $_strStaticTableName = "report_schedule_constraint_value";

	/**
	 * getConstraintValueForScheduleIdConstraintId
	 * 
	 * Returns an array of Report_Constraint objects associated with a given Report.
	 * This method will add results to the Cache, however it will not read from the Cache
	 * 
	 * returns Report_Schedule_Constraint_Value Object
	 */
	public static function getConstraintValueForScheduleIdConstraintId($iReportScheduleId,$iReportConstraintId)	{
		
		$oSelectReportScheduleConstraintValue = self::_preparedStatement('selByReportScheduleIdConstraintId');
		$iResult = $oSelectReportScheduleConstraintValue->Execute(array('report_schedule_id'=>$iReportScheduleId, 'report_constraint_id'=>$iReportConstraintId));
		if ($iResult === false) {
			throw new Exception_Database($oSelectReportScheduleConstraintValue->Error());
		}
		$aReportScheduleConstraintValue = $oSelectReportScheduleConstraintValue->Fetch();
		// Create new Report Constraint Value object and manually add to the Cache
		$oReportScheduleConstraintValue	= new self($aReportScheduleConstraintValue);
		self::addToCache($oReportScheduleConstraintValue);
			
		return $oReportScheduleConstraintValue;
	}

	protected static function getCacheName() {
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName)) {
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}

	protected static function getMaxCacheSize() {
		return 100;
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache() {
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects() {
		return parent::getCachedObjects(__CLASS__);
	}

	protected static function addToCache($mixObjects) {
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false) {
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}

	public static function getAll($bolForceReload=false) {
		return parent::getAll($bolForceReload, __CLASS__);
	}

	public static function importResult($aResultSet) {
		return parent::importResult($aResultSet, __CLASS__);
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	protected static function _preparedStatement($strStatement) {
		static $arrPreparedStatements = array();
		if (isset($arrPreparedStatements[$strStatement])) {
			return $arrPreparedStatements[$strStatement];
		} else {
			switch ($strStatement) {
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;

				case 'selAll':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;

				case 'selByReportScheduleIdConstraintId':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName, "*", "report_schedule_id = <report_schedule_id> and report_constraint_id = <report_constraint_id>" );
					break;

				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement] = new StatementInsert(self::$_strStaticTableName);
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement] = new StatementUpdateById(self::$_strStaticTableName);
					break;

				// UPDATES

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}