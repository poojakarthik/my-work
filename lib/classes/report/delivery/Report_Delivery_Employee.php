<?php
class Report_Delivery_Employee extends ORM_Cached {
	protected $_strTableName = "report_delivery_employee";
	protected static $_strStaticTableName = "report_delivery_employee";

	/**
	 * getForReportScheduleId
	 * 
	 * Returns an array of Report_Delivery_Employee objects associated with a given Report Schedule.
	 * This method will add results to the Cache, however it will not read from the Cache
	 * 
	 * returns Report_Delivery_Employee Array
	 */
	public static function getForReportScheduleId($iReportScheduleId)	{
		$aReportDeliveryEmployees	= array();

		$oSelectReportDeliveryEmployee = self::_preparedStatement('selByReportScheduleId');
		$iResult = $oSelectReportDeliveryEmployee->Execute(array('report_schedule_id'=>$iReportScheduleId));
		if ($iResult === false) {
			throw new Exception_Database($oSelectReportDeliveryEmployee->Error());
		}
		while ($aReportDeliveryEmployee = $oSelectReportDeliveryEmployee->Fetch()) {
			// Create new Report Constraint Value object and manually add to the Cache
			$oReportDeliveryEmployee	= new self($aReportDeliveryEmployee);
			self::addToCache($oReportDeliveryEmployee);
			$aReportDeliveryEmployees[$oReportDeliveryEmployee->id]	= $oReportDeliveryEmployee;
		}
		return $aReportDeliveryEmployees;
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
