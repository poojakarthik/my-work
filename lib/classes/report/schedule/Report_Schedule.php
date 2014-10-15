<?php
class Report_Schedule extends ORM_Cached {
	protected $_strTableName = "report_schedule";
	protected static $_strStaticTableName = "report_schedule";

	/*
		generate()
		Report Generate function
		Compiles the query as per the constraints allocated to the query and executes the compiled query to return the result set of the required report
	*/
	public function generate() {
		$sCompiledQuery = $this->getCompiledQuery();
		$aReportSchedule = $this->toArray();
		//Update Compiled Query into Report Schedule Object if compiled query is not yet set
		if ($aReportSchedule['compiled_query'] == ""){
			$aReportSchedule['compiled_query'] = $sCompiledQuery;
			$oReportSchedule = new self($aReportSchedule);
			$oReportSchedule->save();
		}
		try {
			$oResult = Query::run($sCompiledQuery);
			
			return $oResult;
		}
		catch (Exception $e) {
			// Update  ReportScheduleLog Entry
			$oReportScheduleLog = Report_Schedule_Log::getLastReportScheduledLogForScheduleId($this->id);
			$oReportScheduleLog->is_error = 1; //Set the error flag
			$oReportScheduleLog->save();
			return false;
		}
	}

	/*
		getCompiledQuery()
		Compile the Query for the Schedule as per the constraint  value
	*/
	private function getCompiledQuery() {
		//Get the report from reports table
		
		$oReport = Report_New::getForId($this->report_id);
		$aConstraints = Report_Constraint::getConstraintForReportId($oReport->id);

		$sCompiledQuery = $oReport->query;
		
		if (!sizeof($aConstraints)) {
			return $sCompiledQuery;
		}

		foreach ($aConstraints as $oConstraint) {
			$sConstraintName = $oConstraint->name;

			$oScheduleConstraintValue = Report_Schedule_Constraint_Value::getConstraintValueForScheduleIdConstraintId($this->id, $oConstraint->id);

			//Replace constraint placeholder in query
			$sCompiledQuery = str_ireplace("<".$sConstraintName.">", $oScheduleConstraintValue,	$sCompiledQuery);
		}
		return $sCompiledQuery;
	}

	public static function getScheduledReports() {
		$aReportSchedules = array();
		$aResult = Query::run("
			SELECT *
			FROM report_schedule
			WHERE frequency_multiple > 0 and is_enabled = 1
		");
		
		while ($aReportSchedule = $aResult->fetch_assoc())	{
			$oReportSchedule	= new self($aReportSchedule);
			$aReportSchedules[$oReportSchedule->id]	= $oReportSchedule;
		}
		
		return $aReportSchedules;
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