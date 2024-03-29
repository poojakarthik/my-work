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
					echo "Running Scheduled Report";
					//Creating the current report Schedule instance
					$oReportScheduleInstance = new Report_Schedule($oReportSchedule->toArray(),TRUE); //bolLoadById set true
					//Create ReportScheduleLog Entry
					$oReportScheduleLogAdd = new Report_Schedule_Log();
					$oReportScheduleLogAdd->report_schedule_id = $oReportSchedule->id;
					$oReportScheduleLogAdd->executed_datetime = date("Y-m-d H:i:s");
					$oReportScheduleLogAdd->is_error = 0; //Initial Setup before completion
					$oReportScheduleLogAdd->download_path = "";
					$oReportScheduleLogAdd->save();

					if ($aResult = $oReportScheduleInstance->generate()) {
						$oReport = Report_New::getForId($oReportSchedule->report_id);
						$oReportCategory = Report_Category::getForId($oReport->report_category_id);
						$oReportDeliveryFormat = Report_Delivery_Format::getForId($oReportSchedule->report_delivery_format_id);
						$oReportDeliveryMethod = Report_Delivery_Method::getForId($oReportSchedule->report_delivery_method_id);
						//Create date specific File Save Path
						$sReportSavePath = FLEX_BASE_PATH.self::REPORT_UPLOAD_PATH.$oReportCategory->name."/";
						//Create required file path folder if it doesn't exist
						if (!is_dir($sReportSavePath)) {
							mkdir($sReportSavePath,'0777',true);
							chmod(FLEX_BASE_PATH.self::REPORT_UPLOAD_PATH.$oReportCategory->name, 0777);
						}

						if ($oReportSchedule->filename == "NULL") {
							$sReportName = str_replace(" ", "_", $oReport->name);
						} else {
							$sReportName = str_replace(" ", "_", $oReportSchedule->filename);
						}

						$sFilename = $sReportSavePath . $oReport->id . "_" . $sReportName . "_" . date('YmdHis') . "." . strtolower($oReportDeliveryFormat->name);
						@unlink($sFilename);

						if ($oReportDeliveryFormat->id === REPORT_DELIVERY_FORMAT_XLS) {
							//Create Workbook
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
							// Set File type for Logic Spreadsheet as Selected Delivery Format Type
							$oSpreadsheet->saveAs($sFilename, ($oReportDeliveryFormat->id === REPORT_DELIVERY_FORMAT_XLS?'Excel2007':$oReportDeliveryFormat->name));
						}
						else {
							$rTmpFileHandle = fopen($sFilename, 'w');

							$iRow = 0;
							while ($aRow = $aResult->fetch_assoc())	{
								$aKeys = array_keys($aRow);
								$aValues = array_values($aRow);

								//Get the Field names if first row and write them to sheet before inserting any data
								if (!$iRow) {
									fputcsv($rTmpFileHandle, $aKeys);
								}

								fputcsv($rTmpFileHandle, $aValues);

								$iRow++;
							}
						}
						chmod($sFilename,0777);
						// Update Download Path for ReportScheduleLog Entry
						$oReportScheduleLogAdd->is_error = 0;
						$oReportScheduleLogAdd->download_path = $sFilename;
						$oReportScheduleLogAdd->save();
						//Use Proper Delivery Method
						if ($oReportDeliveryMethod->id == REPORT_DELIVERY_METHOD_EMAIL) {
							$sAttachmentContent = file_get_contents($sFilename);
							$sCurrentTimestamp = date('d/m/Y H:i:s');

							$aHeaders = array(
									'From' => "reports@billingsite.com.au",
									'Subject' => "{$oReport->name} executed on {$sCurrentTimestamp}"
								);
							$oEmailFlex = new Email_Flex();
							$oEmailFlex->setSubject($aHeaders['Subject']);
							$oEmailFlex->setFrom($aHeaders['From']);
							// Attachment (file to deliver)
							if ($oReportDeliveryFormat->id == REPORT_DELIVERY_FORMAT_XLS) {
								$sMimeType = "application/x-msexcel";
							} else if ($oReportDeliveryFormat->id == REPORT_DELIVERY_FORMAT_CSV) {
								$sMimeType = "text/csv";
							}
							$oEmailFlex->createAttachment(
								$sAttachmentContent,
								$sMimeType,
								Zend_Mime::DISPOSITION_ATTACHMENT,
								Zend_Mime::ENCODING_BASE64,
								$sFilename
							);

							$aReportDeliveryEmployees = Report_Delivery_Employee::getForReportScheduleId($oReportSchedule->id);
							$aReceivers = array();
							foreach ($aReportDeliveryEmployees as $oReportDeliveryEmployee) {
								$oEmployee = Employee::getForId($oReportDeliveryEmployee->employee_id);
								$aEmployee = $oEmployee->toArray();
								$oEmailFlex->addTo($oEmployee->Email);
								$aReceivers[] = $aEmployee['FirstName'];
							}
							// Generate Content
				 			$strContent	= "Dear " . implode("/", $aReceivers) . ",\n\n";
							$strContent .= "Attached is the Scheduled Report ({$oReport->name}) .";
							$strContent .= "\nRegards\nFlex @ Billing Site";
							$oEmailFlex->setBodyText($strContent);

							// Send the email
							try {
								$oEmailFlex->send();
	 						} catch (Zend_Mail_Transport_Exception $oException) {
								// Sending the email failed
								Log::getLog()->log("Failed to send email to " . $aEmployee['FirstName']);
								throw $oException;
							}
						}
					} else {
						Log::getLog()->log("Failed to execute the query");
					}
				}
			}
			return 0;
		} catch (Exception $oException) {
			$this->showUsage($oException->getMessage());
			return 1;
		}
	}

	function _isScheduledToRun($oReportSchedule){
		$iNow = time();
		// Compute lastday of scheduled execution
		$oReportFrequencyType = Report_Frequency_Type::getForId($oReportSchedule->report_frequency_type_id);
		$iFrequencyMultiple = $oReportSchedule->frequency_multiple;
		$sFrequencyType = strtolower($oReportFrequencyType->name) . "s";

		if ($oReportSchedule->schedule_end_datetime == "0000-00-00 00:00:00") {
			$iEndScheduletendDateTimeTimestamp = 0; //Making sure end schedule date time is not affective if NULL
		} else {
			$dFinalSchedulendDateTime = new DateTime($oReportSchedule->schedule_end_datetime);
			$iEndScheduletendDateTimeTimestamp = intval($dFinalSchedulendDateTime->format('U'));
		}
		//Compute the next scheduled datetime
		if ($oReportScheduleLog = Report_Schedule_Log::getLastReportScheduledLogForScheduleId($oReportSchedule->id)) {
			$dLastExecutedDateTime = $oReportScheduleLog->executed_datetime;
			$dNextScheduledDateTime = new DateTime($dLastExecutedDateTime);
			$dNextScheduledDateTime->modify('+' . $oReportSchedule->frequency_multiple.' '.$sFrequencyType);
		} else {
			$dNextScheduledDateTime = new DateTime($oReportSchedule->schedule_datetime);
		}
		if ($iEndScheduletendDateTimeTimestamp != 0) {
			if ($iNow > intval($dNextScheduledDateTime->format('U')) && $iNow < $iEndScheduletendDateTimeTimestamp) {
				return true;
			} else {
				return false;
			}
		} else {
			if ($iNow > intval($dNextScheduledDateTime->format('U'))) {
				return true;
			} else {
				return false;
			}
		}
	}
}