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
				if ($this->_isScheduledToRun($oReportSchedule)) {
					
					//Creating the current report Schedule instance
					
					$oReportScheduleInstance = new Report_Schedule($oReportSchedule->toArray(),TRUE); //bolLoadById set true

					//Create ReportScheduleLog Entry
					$oReportScheduleLogAdd = new Report_Schedule_Log();

					$oReportScheduleLogAdd->report_schedule_id = $oReportSchedule->id;
					$oReportScheduleLogAdd->executed_datetime = new MySQLFunction("NOW()");
					$oReportScheduleLogAdd->is_error = -1; //Initial Setup before completion
					$oReportScheduleLogAdd->download_path = "";
					$oReportScheduleLogAdd->save();

					if ($aResult = $oReportScheduleInstance->generate()) {

						
						
						//Create Workbook
						$oReport = Report_New::getForId($oReportSchedule->report_id);

						$oReportCategory = Report_Category::getForId($oReport->report_category_id);

						$oReportDeliveryFormat = Report_Delivery_Format::getForId($oReportSchedule->report_delivery_format_id);
						$oReportDeliveryMethod = Report_Delivery_Method::getForId($oReportSchedule->report_delivery_method_id);

						//Create date specific File Save Path
						$sReportSavePath = FLEX_BASE_PATH.self::REPORT_UPLOAD_PATH.$oReportCategory->name."/";

						//Create required file path folder if it doesn't exist
						if (!is_dir($sReportSavePath)) {
							mkdir($sReportSavePath,'0777',true);
							chmod(FLEX_BASE_PATH.self::REPORT_UPLOAD_PATH.oReportCategory->name, 0777);
							
						}

						if($oReportSchedule->filename == "NULL") {
							$sReportName = str_replace(" ", "_", $oReport->name);
						}
						else {
							$sReportName = str_replace(" ", "_", $oReportSchedule->filename);
						}

						$sFilename = $sReportSavePath . $oReport->id . "_" . $sReportName . "_" . date('YmdHis') . "." . strtolower($oReportDeliveryFormat->name);
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
						$oSpreadsheet->saveAs($sFilename, ($oReportDeliveryFormat->name == 'XLS'?'Excel2007':$oReportDeliveryFormat->name));
						chmod($sFilename,0777);

						// Update Download Path for ReportScheduleLog Entry
						$oReportScheduleLogAdd->is_error = 0;
						$oReportScheduleLogAdd->download_path = $sFilename;
						$oReportScheduleLogAdd->save();

						//Use Proper Delivery Method
						if(strtolower($oReportDeliveryMethod->delivery_method) == 'email') {
							
							$sAttachmentContent = file_get_contents($sFilename);

							$sCurrentTimestamp = date('d/m/Y h:i:s');
							//TODO Write Code To Send Email Here
							//$arrHeaders = Array('From' => "test@smartbusinesstelecom.com.au", 'Subject' => "Report Attached - " . $oReport->Name);
							$arrHeaders = Array	(
									'From'		=> "reports@yellowbilling.com.au",
									'Subject'	=> "{$oReport->Name} executed on {$sCurrentTimestamp}"
								);


							$oEmailFlex	= new Email_Flex();
							$oEmailFlex->setSubject($arrHeaders['Subject']);

							$aReportDeliveryEmployees = Report_Delivery_Employee::getForReportScheduleId($oReportSchedule->id); 

							
							$aReceivers = array();
							foreach($aReportDeliveryEmployees as $oReportDeliveryEmployee){

								$oEmployee = Employee::getForId($oReportDeliveryEmployee->employee_id);

								$aEmployee = $oEmployee->toArray();

								$oEmailFlex->addTo($oEmployee->Email);

								
								$oEmailFlex->setFrom($arrHeaders['From']);

								$
								// Generate Content
					 			$strContent	=	"Dear {$aEmployee['FirstName']},\n\n";
								
								$strContent .= "Attached is the Scheduled Report ({$oReport->name}) .";
								$strContent 	.= "\n\nPablo\nYellow Billing Mascot";
								
								$oEmailFlex->setBodyText($strContent);
								// Attachment (file to deliver)
								if(strtoupper($oReportDeliveryFormat->name) == "XLS") {
									$sMimeType = "application/x-msexcel";
								}
								else if(strtoupper($oReportDeliveryFormat->name) == "CSV") {
									$sMimeType = "text/csv";
								}
								$oEmailFlex->createAttachment(
									$sAttachmentContent,
									$sMimeType,
									Zend_Mime::DISPOSITION_ATTACHMENT,
									Zend_Mime::ENCODING_BASE64,
									$sFilename
								);
								// Send the email
								try {
									$oEmailFlex->send();
									$aReceivers[] = $aEmployee['FirstName'];
		 						} catch (Zend_Mail_Transport_Exception $oException) {
									// Sending the email failed
									print_r("Failed to Send Email to " . $aEmployee['FirstName'])
								}
							}
						}
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
	function _isScheduledToRun($oReportSchedule){
		$iNow = time();

		// Compute lastday of scheduled execution
		$oReportFrequencyType = Report_Frequency_Type::getForId($oReportSchedule->report_frequency_type_id);

		$iFrequencyMultiple = $oReportSchedule->frequency_multiple;
		$sFrequencyType = strtolower($oReportFrequencyType->name)."s";

		if($oReportSchedule->schedule_end_datetime == "NULL") {
			$iEndScheduletedDateTimeTimestamp = time() + 1000;
		}
		else {
			$dFinalScheduledDateTime = new DateTime($oReportSchedule->schedule_end_datetime);
			$iEndScheduletedDateTimeTimestamp = $dFinalScheduledDateTime->getTimestamp();
		}		
		//$dFinalScheduledDateTime = date_add(new DateTime($oReportSchedule->schedule_datetime), date_interval_create_from_date_string($iFrequencyMultiple.' '.$sFrequencyType));
		
		//Compute the next scheduled datetime
		if ($oReportScheduleLog = Report_Schedule_Log::getLastReportScheduledLogForScheduleId($oReportSchedule->id)) {
			$dLastExecutedDateTime = $oReportScheduleLog->executed_datetime;
			$dNextScheduledDateTime = date_add(new DateTime($dLastExecutedDateTime), date_interval_create_from_date_string($oReportSchedule->frequency_multiple.' '.$sFrequencyType));
		}
		else {
			$dNextScheduledDateTime = new DateTime($oReportSchedule->scheduled_datetime);
		}
		if ($iNow > $dNextScheduledDateTime->getTimestamp() && $iNow < $iEndScheduletedDateTimeTimestamp) {
			return true;
		}
		else {
			return false;
		}
	}
}