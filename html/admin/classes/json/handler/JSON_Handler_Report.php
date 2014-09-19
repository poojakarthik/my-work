<?php
class JSON_Handler_Report extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	const TEMP_REPORT_UPLOAD_PATH = "files/temp/";

	public function save($mData) {
		try {
			// Check user authorization and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

			if(property_exists($mData->report, "id")) {
				// Clear existing data

				$oQuery = new Query();
				$oQuery->Execute("DELETE FROM report_employee WHERE report_id = {$mData->report->id}");
				$oQuery->Execute("DELETE FROM report_constraint WHERE report_id = {$mData->report->id}");
				//$oQuery->Execute("UPDATE report_schedule SET is_enabled = 0 WHERE report_id = {$mData->report->id}");

				// Save existing Report.
				$aRow = (array)$mData->report;
				$oReport = new Report_New($aRow, $bLoad=true);
				$oReport->name = $mData->name;
				$oReport->summary = $mData->summary;
				$oReport->query = $mData->query;
				$oReport->created_employee_id = $aRow['created_employee_id'];
				$oReport->created_datetime = $aRow['created_datetime'];
				$oReport->is_enabled = $aRow['is_enabled'];
				$oReport->report_category_id =$mData->category;
				$oReport->save();

				// Create Report Employee
				$aReportEmployee = $mData->report_employee;
				foreach($aReportEmployee as $iEmployeeId) {
					$oReportSchedule = new Report_Employee();
					$oReportSchedule->report_id = $oReport->id;
					$oReportSchedule->employee_id = $iEmployeeId;
					$oReportSchedule->created_employee_id = Flex::getUserId();
					$oReportSchedule->created_datetime = date("Y-m-d H:i:s");
					$oReportSchedule->save();
				}

				// Create Constraints
				$aConstraint = $mData->constraint;
				foreach($aConstraint as $oConstraint) {
					var_dump($oConstraint);
					$oReportConstraint = new Report_Constraint();
					$oReportConstraint->report_id = $oReport->id;
					$oReportConstraint->name = $oConstraint->name;
					$oReportConstraint->report_constraint_type_id = (int)$oConstraint->report_constraint_type_id;
					$oReportConstraint->source_query = $oConstraint->source_query;
					$oReportConstraint->validation_regex = $oConstraint->validation_regex;
					$oReportConstraint->placeholder = $oConstraint->placeholder;
					$oReportConstraint->save();
				}
				/*
				// Create Schedules
				$aSchedule = $mData->schedule;
				foreach($aSchedule as $oSchedule) {
					$oReportSchedule = new Report_Schedule();
					$oReportSchedule->report_id = $oReport->id;
					$oReportSchedule->report_frequency_type_id = (int)$oSchedule->report_frequency_type_id;
					$oReportSchedule->frequency_multiple = (int)$oSchedule->frequency_multiple;
					$oReportSchedule->schedule_datetime = $oSchedule->schedule_datetime;
					$oReportSchedule->is_enabled = 1;
					$oReportSchedule->compiled_query = '';
					$oReportSchedule->scheduled_employee_id = Flex::getUserId();
					$oReportSchedule->scheduled_datetime = date("Y-m-d H:i:s");
					$oReportSchedule->save();
				}
				*/

			} else {
				// Save new Report.
				$oReport = new Report_New();
				$oReport->name = $mData->name;
				$oReport->summary = $mData->summary;
				$oReport->query = $mData->query;
				$oReport->created_datetime = date("Y-m-d H:i:s");
				$oReport->created_employee_id = Flex::getUserId();
				$oReport->is_enabled = 1;
				$oReport->report_category_id =$mData->category;
				$oReport->save();

				// Create Report Employee
				$aReportEmployee = $mData->report_employee;
				foreach($aReportEmployee as $iEmployeeId) {
					$oReportSchedule = new Report_Employee();
					$oReportSchedule->report_id = $oReport->id;
					$oReportSchedule->employee_id = $iEmployeeId;
					$oReportSchedule->created_employee_id = Flex::getUserId();
					$oReportSchedule->created_datetime = date("Y-m-d H:i:s");
					$oReportSchedule->save();
				}

				// Create Constraints
				$aConstraint = $mData->constraint;
				foreach($aConstraint as $oConstraint) {
					$oReportConstraint = new Report_Constraint();
					$oReportConstraint->report_id = $oReport->id;
					$oReportConstraint->name = $oConstraint->name;
					$oReportConstraint->report_constraint_type_id = (int)$oConstraint->report_constraint_type_id;
					$oReportConstraint->source_query = $oConstraint->source_query;
					$oReportConstraint->validation_regex = $oConstraint->validation_regex;
					$oReportConstraint->placeholder = $oConstraint->placeholder;
					$oReportConstraint->save();
				}
				// Create Schedules
				/*
				$aSchedule = $mData->schedule;
				foreach($aSchedule as $oSchedule) {
					$oReportSchedule = new Report_Schedule();
					$oReportSchedule->report_id = $oReport->id;
					$oReportSchedule->report_frequency_type_id = (int)$oSchedule->report_frequency_type_id;
					$oReportSchedule->frequency_multiple = (int)$oSchedule->frequency_multiple;
					$oReportSchedule->schedule_datetime = $oSchedule->schedule_datetime;
					$oReportSchedule->is_enabled = 1;
					$oReportSchedule->compiled_query = '';
					$oReportSchedule->scheduled_employee_id = Flex::getUserId();
					$oReportSchedule->scheduled_datetime = date("Y-m-d H:i:s");
					$oReportSchedule->save();
				}
				*/
			}
			return array(
				'bSuccess'	=> true
			);
		} catch (JSON_Handler_Account_Run_Exception $oException) {
			return 	array(
						'Success'	=> false,
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}

	public function getScheduleForReportId($mData) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		return array(
				'success' => true,
				'aReportSchedule' =>$this->_getScheduleForReportId($mData->iReportId)
				);
	}
	public function getEmployees() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		$employees = Employee::getAll();
		Log::get()->log(print_r($employees, true));
		$filteredEmployees = array();
		foreach ($employees as $key=>$employee) {
			if ($employee->Archived) {
				continue;
			}
			/*if(($employee->Permission & PERMISSION_ADMIN)!=PERMISSION_ADMIN && ($employee->Permission & PERMISSION_ACCOUNTS)!=PERMISSION_ACCOUNTS){
				continue;
			}*/
			array_push($filteredEmployees, $employee->toArray());
		}
		return array(
			'success' => true,
			'employees' => $filteredEmployees,
			'allemployees' => $employees
		);
	}

	private function _getActiveEmployeesForReportId($iReportId) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		$oQuery = new Query();

		$sSQL	= "
			SELECT		e.*,
						re.report_id
			FROM		Employee e
			LEFT JOIN	report_employee re ON (re.employee_id = e.Id AND re.report_id = {$iReportId})
			WHERE		e.Archived = 0";

		$rQuery	= $oQuery->Execute($sSQL);
		$aDataSet= array();
		while($aResultSet = $rQuery->fetch_assoc()) {
			$aDataSet[] = $aResultSet;
		}
		return $aDataSet;
	}

	public function _getScheduleForReportId($iReportId) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		$oQuery = new Query();

		$sSQL	= "
			SELECT		rs.*
			FROM		report_schedule rs
			WHERE		report_id = {$iReportId} AND is_enabled = 1";

		$rQuery	= $oQuery->Execute($sSQL);
		$aDataSet= array();
		while($aResultSet = $rQuery->fetch_assoc()) {
			$aDataSet[] = $aResultSet;
		}
		return $aDataSet;
	}
	public function _getConstraintForReportId($iReportId) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		$oQuery = new Query();

		$sSQL	= "
			SELECT		rc.*
			FROM		report_constraint rc
			WHERE		report_id = {$iReportId}";

		$rQuery	= $oQuery->Execute($sSQL);
		$aDataSet= array();
		while($aResultSet = $rQuery->fetch_assoc()) {
			$aDataSet[] = $aResultSet;
		}
		return $aDataSet;
	}

	public function _getReportFrequencyTypes($iReportId) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		$aReportFrequencyType = Report_Frequency_Type::getAll();
		$aResultSet = array();
		foreach ($aReportFrequencyType as $iKey=>$oReportFrequencyType) {
			array_push($aResultSet, $oReportFrequencyType->toArray());
		}
		return $aResultSet;
	}

	public function getForId($mData) {
		try {
			// Check user authorization and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

			$qryQuery = new Query();
			$strSQL	= "
				SELECT		r.*
				FROM		report r
				WHERE		id = {$mData->iReportId}";

			$resQuery		= $qryQuery->Execute($strSQL);
			if ($resQuery === false) {
				throw new Exception($qryQuery->Error());
			}

			$aReport				= $resQuery->fetch_assoc();
			$aEmployee				= $this->_getActiveEmployeesForReportId($mData->iReportId);
			$aReportConstraint		= $this->_getConstraintForReportId($mData->iReportId);
			$aReportSchedule		= $this->_getScheduleForReportId($mData->iReportId);
			$aReportFrequencyType	= $this->_getReportFrequencyTypes();

			return array(
				'bSuccess'	=> true,
				"aReport" => $aReport,
				"aEmployee" => $aEmployee,
				"aReportConstraint" => $aReportConstraint,
				"aReportSchedule" => $aReportSchedule,
				"aReportFrequencyType" => $aReportFrequencyType
			);

		} catch (JSON_Handler_Account_Run_Exception $oException) {
			return 	array(
						'Success'	=> false,
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}

	public function getAll() {
		try {
			$sRequestContent = file_get_contents('php://input');
			$oRequest = json_decode($sRequestContent);
			
			$bCountOnly = $oRequest->bCountOnly;
			$iLimit = $oRequest->iLimit;
			$iOffset = $oRequest->iOffset;
			$oSort = $oRequest->oSort;
			$oFilter = $oRequest->oFilter;
			// Check user authorization and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

			$sOrderBy = Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			
			if($sOrderBy != "") {
				$sOrderBy = " Order By " . $sOrderBy;
			}
			$sLimit		= Statement::generateLimit($iLimit, $iOffset);
			// TODO send customer group id as part of request.
			$mResult = Query::run(
				"SELECT		r.*, CONCAT(e.FirstName, ' ', e.LastName) AS created_employee_full_name, rc.name AS report_category
				 FROM		report r
				 JOIN		Employee e ON e.Id = r.created_employee_id
				 JOIN 		report_category rc ON rc.id = r.report_category_id
				" . $sOrderBy . " LIMIT " .$sLimit);
			if ($bCountOnly) {
				return array('iRecordCount' => $mResult->num_rows);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$i			= $iOffset;
			if ($mResult) {
				while ($aRow = $mResult->fetch_assoc()) {
					$aReport[] = $aRow;
				}
			}
			return array(
				'bSuccess'	=> true,
				"aRecords" => $aReport,
				'iRecordCount'	=> $mResult->num_rows,
				'sOrderBy'	=> $sOrderBy
			);
		} catch (JSON_Handler_Account_Run_Exception $oException) {
			return 	array(
						'Success'	=> false,
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess'	=> false,
				'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.',
				'sOrderBy'	=> $sOrderBy
			);
		}
	}
	
	public function generate($mData) {
		$oReport = Report_New::getForId($mData->id);

		$aConstraintResult = Report_Constraint::getConstraintForReportId($mData->id);
		
		$aConstraintValues = array();
		if (sizeof($aConstraintResult)) {
			foreach ($aConstraintResult as $oConstraint) {
				$sConstraintName = $oConstraint->name;

				if(isset($mData->{$sConstraintName})) {
					$aConstraintValues[$sConstraintName] = $mData->{$sConstraintName};
				}
				else {
					return 	array(
						'success'	=> true,
						'bSuccess'	=> false,
						'sMessage'	=> "Constraint Missing:"
					);
				}
			}
		}
		$oResult = Query::run($oReport->query, $aConstraintValues);
		if ($oResult){
			
			$iResultCount = $oResult->num_rows;

			
			if($iResultCount > 0) {
				$oSpreadsheet = new Logic_Spreadsheet(array());
			
				$iRow = 0;
				while ($aRow = $oResult->fetch_assoc())	{
					$aKeys = array_keys($aRow);
					$aValues = array_values($aRow);

					//Get the Field names if first row and write them to sheet before inserting any data
					if(!$iRow) {
						$oSpreadsheet->addRecord($aKeys);
					}

					$oSpreadsheet->addRecord($aValues);

					$iRow++;
				}

				$sReportTempPath = FLEX_BASE_PATH.self::TEMP_REPORT_UPLOAD_PATH.date('Y')."/".date('F')."/".date('j')."/";

				//Create required file path folder if it doesn't exist
				while (!is_dir($sReportTempPath)) {
					mkdir($sReportTempPath,'0777',true);
					chmod(FLEX_BASE_PATH.self::TEMP_REPORT_UPLOAD_PATH.date('Y'), 0777);
					chmod(FLEX_BASE_PATH.self::TEMP_REPORT_UPLOAD_PATH.date('Y')."/".date('F'), 0777);
					chmod(FLEX_BASE_PATH.self::TEMP_REPORT_UPLOAD_PATH.date('Y')."/".date('F')."/".date('j'), 0777);
				}
				
				//Create Workbook
				$sFilename = str_replace(" ", "_", $oReport->name) . "." .strtolower($mData->delivery_format);
				$sTmpFilePath = $sReportTempPath . $sFilename;
				@unlink($sFilename);

				// Set File type for Logic Spreadsheet as CSV
				$oSpreadsheet->saveAs($sTmpFilePath, ($mData->delivery_format == 'XLS'?'Excel2007':$mData->delivery_format));
				chmod($sTmpFilePath,0777);


				//Use Proper Delivery Method
				if($mData->delivery_method == 'EMAIL') {
					
					$sAttachmentContent = file_get_contents($sTmpFilePath);

					$sCurrentTimestamp = date('d/m/Y h:i:s');
					//TODO Write Code To Send Email Here
					//$arrHeaders = Array('From' => "test@smartbusinesstelecom.com.au", 'Subject' => "Report Attached - " . $oReport->Name);
					$arrHeaders = Array	(
							'From'		=> "reports@yellowbilling.com.au",
							'Subject'	=> "{$oReport->Name} requested on {$sCurrentTimestamp}"
						);


					$oEmailFlex	= new Email_Flex();
					$oEmailFlex->setSubject($arrHeaders['Subject']);

					$delivery_employees = explode(",",$mData->selectedDeliveryEmployees);

					
					$aReceivers = array();
					for($i=0; $i<sizeof($delivery_employees);$i++) {

						$oEmployee = Employee::getForId($delivery_employees[$i]);

						$aEmployee = $oEmployee->toArray();

						$oEmailFlex->addTo($oEmployee->Email);

						
						$oEmailFlex->setFrom($arrHeaders['From']);

						$
						// Generate Content
			 			$strContent	=	"Dear {$aEmployee['FirstName']},\n\n";
						
						$strContent .= "Attached is the Ad-Hoc Report ({$oReport->name}) you requested on {$sCurrentTimestamp}.";
						$strContent 	.= "\n\nPablo\nYellow Billing Mascot";
						
						$oEmailFlex->setBodyText($strContent);
						// Attachment (file to deliver)
						if($mData->delivery_format == "XLS") {
							$sMimeType = "application/x-msexcel";
						}
						else if($mData->delivery_format == "CSV") {
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
							return 	array(
								'success'	=> true,
								'bSuccess'	=> false,
								'sMessage'	=> $oException->getMessage()
							);
						}
					}
					return 	array(
							'success'	=> true,
							'bSuccess'	=> true,
							'bIsEmail'	=> true,
							'sMessage'	=> "Report emailed successfully to " . implode(", ",$aReceivers)
						);
				}
				else if($mData->delivery_method == "DOWNLOAD") {
					return 	array(
							'success'	=> true,
							'bSuccess'	=> true,
							'bIsEmail'	=> false,
							'sFilename'	=> $sFilename
						);
				}
			}
			else {
				return 	array(
						'success'	=> true,
						'bSuccess'	=> false,
						'sMessage'	=> "No Result Available for Report"
				);
			}
		}
		else {
			return 	array(
					'success'	=> true,
					'bSuccess'	=> false,
					'sMessage'	=> "Error While Generating report"
				);
		}
	}
}
