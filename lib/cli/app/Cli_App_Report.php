<?php
require_once dirname(__FILE__) . '/' . '../../../' . 'flex.require.php';

class Cli_App_Report extends Cli {
	// Set the root report upload path here
	const REPORT_UPLOAD_PATH = "files/scheduled_reports/";

	function run() {
		try	{
			//Get all scheduled reports
			$aReportSchedules = Report_Schedule::getScheduledReports();
			foreach ($aReportSchedules as $oReportSchedule) {
				$iNow = time();

				// Compute lastday of scheduled execution
				$oReportFrequencyType = Report_Frequency_Type::getForId($oReportSchedule->report_frequency_type_id);

				$iFrequencyMultiple = $oReportSchedule->frequency_multiple;
				$sFrequencyType = strtolower($oReportFrequencyType->name)."s";

				
				$dFinalScheduledDateTime = date_add(new DateTime($oReportSchedule->schedule_datetime), date_interval_create_from_date_string($iFrequencyMultiple.' '.$sFrequencyType));
				
				//Compute the next scheduled datetime

				if ($oReportScheduleLog = Report_Schedule_Log::getLastReportScheduledLogForScheduleId($oReportSchedule->id)) {
					$dLastExecutedDateTime = $oReportScheduleLog->executed_datetime;
					$dNextScheduledDateTime = date_add(new DateTime($dLastExecutedDateTime), date_interval_create_from_date_string('1 '.$sFrequencyType));
				}
				else {
					$dNextScheduledDateTime = new DateTime($oReportSchedule->scheduled_datetime);
				}

				//Check if current time has passed next scheduled datetime of the report

				if ($iNow > $dNextScheduledDateTime->getTimestamp() && $iNow < $dFinalScheduledDateTime->getTimestamp()) {
					
					//Creating the current report Schedule instance
					
					$oReportScheduleInstance = new Report_Schedule($oReportSchedule->toArray(),TRUE); //bolLoadById set true

					if ($aResult = $oReportScheduleInstance->generate()) {

						//Create date specific File Save Path
						$sReportSavePath = FLEX_BASE_PATH.self::REPORT_UPLOAD_PATH.date('Y')."/".date('F')."/".date('j')."/";

						//Create required file path folder if it doesn't exist
						if (!is_dir($sReportSavePath)) {
							mkdir($sReportSavePath,'0777',true);
							chmod(FLEX_BASE_PATH.self::REPORT_UPLOAD_PATH.date('Y'), 0777);
							chmod(FLEX_BASE_PATH.self::REPORT_UPLOAD_PATH.date('Y')."/".date('F'), 0777);
							chmod(FLEX_BASE_PATH.self::REPORT_UPLOAD_PATH.date('Y')."/".date('F')."/".date('j'), 0777);
						}
						
						//Create Workbook
						$oReport = Report_New::getForId($oReportSchedule->report_id);
						$sFilename = $sReportSavePath. str_replace(" ", "_", $oReport->name) .".csv";
						@unlink($sFilename);
						$oSpreadsheet = new Logic_Spreadsheet(array());
						
						$iRow = 0;
						while ($aRow = $aResult->fetch_assoc())	{
							$aKeys = array_keys($aRow);
							$aValues = array_values($aRow);

							//Get the Field names if first row and write them to sheet before inserting any data
							if(!$iRow) {
								$oSpreadsheet->addRecord($aKeys);
							}

							$oSpreadsheet->addRecord($aValues);

							$iRow++;
						}
						
						// Set File type for Logic Spreadsheet as CSV
						$oSpreadsheet->saveAs($sFilename, "CSV");
						chmod($sFilename,0777);

						// Save Report Schedule Log as Successfull Run Log
						$aReportScheduleLogValue = Array();
						$aReportScheduleLogValue["report_schedule_id"] = $oReportSchedule->id;
						$aReportScheduleLogValue["executed_datetime"] = new MySQLFunction("NOW()");
						$aReportScheduleLogValue["is_error"] = 0;
						$aReportScheduleLogValue["download_path"] = $sFilename;
						Report_Schedule_Log::insertReportScheduleLog($aReportScheduleLogValue);
					}
					else {
						print_r("Query Compilation Failed");
					}
				}
			}
			return 0;
		}
		catch(Exception $oException) {
			$this->showUsage($oException->getMessage());
			return 1;
		}
	}

	function getCommandLineArguments() {
		return array(
		);
	}

	function _getCompiledQuery($oReportSchedule) {
		//Get the report from reports table
		
		$oReport = Report_New::getForId($oReportSchedule->report_id);
		$aConstraints = Report_Constraint::getConstraintForReportId($oReport->id);

		$sCompiledQuery = $oReport->query;
		
		if (!sizeof($aConstraints)) {
			return $sCompiledQuery;
		}


		/*
			Querys with Constraints should look like following:
			Select * 
			from 
				(
				Select * 
				from tableA
				where aFieldName = <aConstraintName>
				) AS a 
			where bFieldName = <bConstraintName> and cFieldName Like '<cConstraintName>%'
			group by xFieldName 
			order by <dConstraintName>
			having eFieldName > <eConstraintName>
			limit 0, <fConstraintName>

		*/
		
		foreach ($aConstraints as $oConstraint) {
			$sConstraintName = $oConstraint->name;

			$oScheduleConstraintValue = Report_Schedule_ConstraintValue::getConstraintValueForScheduleIdConstraintId($oReportSchedule->id, $oConstraint->id);

			//Replace constraint placeholder in query
			$sCompiledQuery = str_ireplace("<".$sConstraintName.">", $oScheduleConstraintValue->value,	$sCompiledQuery);
		}
		return $sCompiledQuery;
	}
}