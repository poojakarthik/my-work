<?php
class Report_Schedule_Log extends ORM_Cached {
	protected $_strTableName = "report_schedule_log";
	protected static $_strStaticTableName = "report_schedule_log";
	
	/**
	 * getLastReportScheduledLogForScheduleId
	 * 
	 * Returns an object of Report Schedule Log objects for a Report Schedule ID which was last run
	 * This method will add results to the Cache, however it will not read from the Cache
	 * 
	 * returns	Last Report Schedule Log Object 
	 */
	public static function getLastReportScheduledLogForScheduleId($iReportScheduleId) {
		$aReportScheduleLogs	= array();
		
		$oSelectReportScheduleLogs	= self::_preparedStatement('selByReportScheduleId');
		$iResult = $oSelectReportScheduleLogs->Execute(array( 'report_schedule_id' => $iReportScheduleId));
		if ($iResult === false)	{
			throw new Exception_Database($oSelectReportScheduleLogs->Error());
		}
		if ($oSelectReportScheduleLogs->Count()) {
			$aReportScheduleLog = $oSelectReportScheduleLogs->Fetch();
		
			// Create new Report Schedule Log object and manually add to the Cache
			$oReportScheduleLog	= new self($aReportScheduleLog);
			self::addToCache($oReportScheduleLog);
			
			return $oReportScheduleLog;
		}
		else {
			return false;
		}
	}

	/**
	 * insertReportScheduleLog()
	 * 
	 * Inserts a new report schedule log
	 * 
	 * @return	array
	 */
	public static function insertReportScheduleLog($aReportScheduleLog)	{
		$sReportScheduleLogInsertStatement = new StatementInsert(self::$_strStaticTableName,$aReportScheduleLog);
		if (($outcome = $sReportScheduleLogInsertStatement->Execute($aReportScheduleLog)) === FALSE) {
			throw new Exception_Database('Failed to save ' . (str_replace('_', ' ', self::$_strStaticTableName)) . ' details: ' . $sReportScheduleLogInsertStatement->Error());	
		}
	}

	/**
	* updateReportScheduleLog()
	* Updated the Report Schedule Log Object with updated values
	*/
	public static function updateReportScheduleLog($aValues) {
		$sCompliedQueryUpdateStatement = new StatementUpdateById(self::$_strStaticTableName,$aValues);
		if (($outcome = $sCompliedQueryUpdateStatement->Execute($aValues)) === FALSE) {
			throw new Exception_Database('Failed to save ' . (str_replace('_', ' ', self::$_strStaticTableName)) . ' details: ' . $sCompliedQueryUpdateStatement->Error());
		}
		
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

				case 'selByReportScheduleId':
					$arrPreparedStatements[$strStatement] = new StatementSelect(self::$_strStaticTableName,"*", "report_schedule_id= <report_schedule_id>","executed_datetime DESC", 1);
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