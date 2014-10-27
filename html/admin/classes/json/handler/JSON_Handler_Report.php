<?php
class JSON_Handler_Report extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	const TEMP_REPORT_UPLOAD_PATH = "files/temp/";

	public function save($mData) {
		try {
			// Check user authorisfation and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);
			AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);

			if (property_exists($mData->report, "id")) {
				// Clear existing data
				DataAccess::get()->query("DELETE FROM report_employee WHERE report_id = <report_id>", array('report_id' => $mData->report->id));
				DataAccess::get()->query("DELETE FROM report_constraint WHERE report_id = <report_id>", array('report_id' => $mData->report->id));
				
				// Save existing Report.
				$aRow = (array)$mData->report;
				$oReport = new Report_New($aRow, $bLoad=true);
				$oReport->name = $mData->name;
				$oReport->summary = $mData->summary;
				$oReport->query = $mData->query;
				$oReport->created_employee_id = $aRow['created_employee_id'];
				$oReport->created_datetime = $aRow['created_datetime'];
				$oReport->is_enabled = $aRow['is_enabled'];
				$oReport->report_category_id = $mData->category;
				$oReport->save();

				// Create Report Employee
				$aReportEmployee = $mData->report_employee;
				foreach ($aReportEmployee as $iEmployeeId) {
					$oReportSchedule = new Report_Employee();
					$oReportSchedule->report_id = $oReport->id;
					$oReportSchedule->employee_id = $iEmployeeId;
					$oReportSchedule->created_employee_id = Flex::getUserId();
					$oReportSchedule->created_datetime = date("Y-m-d H:i:s");
					$oReportSchedule->save();
				}

				// Create Constraints
				$aConstraint = $mData->constraint;
				foreach ($aConstraint as $oConstraint) {
					$oReportConstraint = new Report_Constraint();
					$oReportConstraint->report_id = $oReport->id;
					$oReportConstraint->name = $oConstraint->name;
					$oReportConstraint->report_constraint_type_id = (int)$oConstraint->report_constraint_type_id;
					$oReportConstraint->source_query = $oConstraint->source_query;
					$oReportConstraint->validation_regex = $oConstraint->validation_regex;
					$oReportConstraint->placeholder = $oConstraint->placeholder;
					$oReportConstraint->save();
				}

			} else {
				// Save new Report.
				$oReport = new Report_New();
				$oReport->name = $mData->name;
				$oReport->summary = $mData->summary;
				$oReport->query = $mData->query;
				$oReport->created_datetime = date("Y-m-d H:i:s");
				$oReport->created_employee_id = Flex::getUserId();
				$oReport->is_enabled = 1;
				$oReport->report_category_id = $mData->category;
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
			}
			return array(
				'bSuccess' => true
			);
		} catch (JSON_Handler_Account_Run_Exception $oException) {
			return 	array(
						'Success' => false,
						'bSuccess' => false,
						'sMessage' => $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess' => false,
				'sMessage' => $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}

	public function getScheduleForReportId($mData) {
		// Check user authorisation and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);

		return array(
				'success' => true,
				'aReportSchedule' =>$this->_getScheduleForReportId($mData->iReportId)
				);
	}

	public function getEmployees($bReportingUserOnly=false) {
		// Check user authorisation and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);

		$employees = Employee::getAll();
		$filteredEmployees = array();

		foreach ($employees as $key=>$employee) {
			if ($employee->Archived) {
				continue;
			}
			if ($employee->Id == 0) {
				continue;
			}
			if ($bReportingUserOnly && (($employee->Privileges & PERMISSION_REPORT_USER) != PERMISSION_REPORT_USER)) {
				continue;
			}
			array_push($filteredEmployees, $employee->toArray());
		}
		return array(
			'success' => true,
			'employees' => $filteredEmployees,
			'allemployees' => $employees
		);
	}

	private function _getActiveEmployeesForReportId($iReportId) {
		// Check user authorisation and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);

		$rQuery	= DataAccess::get()->query('
			SELECT e.*,	re.report_id
			FROM Employee e
				LEFT JOIN report_employee re ON (
					re.employee_id = e.Id
					AND re.report_id = <report_id>
				)
			WHERE e.Archived = 0', array('report_id' => $iReportId));
		$aDataSet = array();
		while ($aResultSet = $rQuery->fetch_assoc()) {
			if (($aResultSet['Privileges'] & PERMISSION_GOD) == PERMISSION_GOD) {
				continue;
			}
			if (($aResultSet['Privileges'] & PERMISSION_REPORT_USER) != PERMISSION_REPORT_USER) {
				continue;
			}
			$aDataSet[] = $aResultSet;
		}
		return $aDataSet;
	}

	public function _getScheduleForReportId($iReportId) {
		// Check user authorisation and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);
		
		$rQuery	= DataAccess::get()->query("
			SELECT rs.*, rft.name as 'frequency_type'
			FROM report_schedule rs
			LEFT JOIN report_frequency_type rft ON rft.id = rs.report_frequency_type_id
			WHERE report_id = <report_id> AND is_enabled = 1", array('report_id' => $iReportId));
		$aDataSet= array();
		while ($aResultSet = $rQuery->fetch_assoc()) {
			$aDataSet[] = $aResultSet;
		}
		return $aDataSet;
	}

	public function _getConstraintForReportId($iReportId) {
		$rQuery	= DataAccess::get()->query("
			SELECT rc.*, rct.Name AS 'constraint_name'
			FROM report_constraint rc
			LEFT JOIN report_constraint_type rct ON rct.id = rc.report_constraint_type_id
			WHERE report_id = <report_id>", array('report_id' => $iReportId));
		$aDataSet= array();
		while ($aResultSet = $rQuery->fetch_assoc()) {
			$aDataSet[] = $aResultSet;
		}
		return $aDataSet;
	}

	public function _getReportFrequencyTypes() {
		$aReportFrequencyType = Report_Frequency_Type::getAll();
		$aResultSet = array();
		foreach ($aReportFrequencyType as $iKey=>$oReportFrequencyType) {
			array_push($aResultSet, $oReportFrequencyType->toArray());
		}
		return $aResultSet;
	}

	public function getForId($mData) {
		try {
			// Check user authorisation and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);

			$resQuery = Query::run("
				SELECT r.*
				FROM report r
				WHERE id = <report_id>", array('report_id' => $mData->iReportId));
			if ($resQuery === false) {
				throw new Exception($qryQuery->Error());
			}

			$aReport = $resQuery->fetch_assoc();
			$aEmployee = $this->_getActiveEmployeesForReportId($mData->iReportId);
			$aReportConstraint = $this->_getConstraintForReportId($mData->iReportId);
			$aReportSchedule = $this->_getScheduleForReportId($mData->iReportId);
			$aReportFrequencyType = $this->_getReportFrequencyTypes();

			return array(
				'bSuccess' => true,
				"aReport" => $aReport,
				"aEmployee" => $aEmployee,
				"aReportConstraint" => $aReportConstraint,
				"aReportSchedule" => $aReportSchedule,
				"aReportFrequencyType" => $aReportFrequencyType
			);

		} catch (JSON_Handler_Account_Run_Exception $oException) {
			return 	array(
						'Success' => false,
						'bSuccess' => false,
						'sMessage' => $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess' => false,
				'sMessage' => $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
			);
		}
	}

	public function getAll() {
		try {
			// Check user authorisation and permissions
			AuthenticatedUser()->CheckAuth();
			AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);

			//JSON Object from Dataset could not be parsed into an JSON parameter to this function and hence php://input is used as an alternative way to capture the request payload
			$sRequestContent = file_get_contents('php://input');
			$oRequest = json_decode($sRequestContent);
			
			$bCountOnly = $oRequest->bCountOnly;
			$iLimit = $oRequest->iLimit;
			$iOffset = $oRequest->iOffset;
			$oSort = $oRequest->oSort;
			$oFilter = $oRequest->oFilter;
			$sOrderBy = Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			
			if ($sOrderBy != "") {
				$sOrderBy = " ORDER BY " . $sOrderBy;
			}
			
			if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN) && AuthenticatedUser()->UserHasPerm(PERMISSION_REPORT_USER)) {					
				$mResult = Query::run(
					"SELECT	r.*, CONCAT(e.FirstName, ' ', e.LastName) AS created_employee_full_name, rc.name AS report_category
					 FROM report r
					 JOIN Employee e ON
					 	e.Id = r.created_employee_id
					 JOIN report_category rc ON 
					 	rc.id = r.report_category_id
					" . $sOrderBy);
			} else {
				$mResult = Query::run(
					"SELECT		r.*, CONCAT(e.FirstName, ' ', e.LastName) AS created_employee_full_name, rc.name AS report_category
					 FROM		report r
					 JOIN		Employee e ON e.Id = r.created_employee_id
					 JOIN 		report_category rc ON rc.id = r.report_category_id
					 JOIN 		report_employee re ON re.report_id = r.id
					 WHERE 		re.employee_id = " . Flex::getUserId() . "
					" . $sOrderBy);
			}
			if ($bCountOnly) {
				return array('iRecordCount' => $mResult->num_rows);
			}
			
			$iLimit	= ($iLimit === null ? 0 : $iLimit);
			$iOffset = ($iOffset === null ? 0 : $iOffset);
			$i = $iOffset;
			if ($mResult->num_rows>0) {
				while ($aRow = $mResult->fetch_assoc()) {
					$bCanManage = false;

					if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN) && AuthenticatedUser()->UserHasPerm(PERMISSION_REPORT_USER)) {
						$bCanManage = true;
					}
					$aRow['bCanManage'] = $bCanManage; 
					$aReport[] = $aRow;
				}
			} else {
				$aRow['message'] = "There are no reports added for you";
				$bCanManage = false;
				if (AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN) && AuthenticatedUser()->UserHasPerm(PERMISSION_REPORT_USER)) {
					
					$bCanManage = true;
				}
				$aRow['bCanManage'] = $bCanManage; 
				$aReport[] = $aRow;
			}
			

			return array(
				'bSuccess' => true,
				"aRecords" => $aReport,
				'iRecordCount' => $mResult->num_rows,
				'sOrderBy' => $sOrderBy
			);
		} catch (JSON_Handler_Account_Run_Exception $oException) {
			return 	array(
						'Success' => false,
						'bSuccess' => false,
						'sMessage' => $oException->getMessage()
					);
		} catch (Exception $e) {
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
				'bSuccess' => false,
				'sMessage' => $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.',
				'sOrderBy' => $sOrderBy
			);
		}
	}

	public function generate($mData) {
		// Check user authorisation and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);
		$oReport = Report_New::getForId($mData->id);

		$aConstraintResult = Report_Constraint::getConstraintForReportId($mData->id);
		
		$aConstraintValues = array();
		if (count($aConstraintResult)) {
			foreach ($aConstraintResult as $oConstraint) {
				$sConstraintName = $oConstraint->name;

				if (isset($mData->{$sConstraintName})) {
					$aConstraintValues[$sConstraintName] = Query::prepareByPHPType($mData->{$sConstraintName});
				} else {
					return 	array(
						'success' => true,
						'bSuccess' => false,
						'sMessage' => "Constraint Missing:"
					);
				}
			}
		}
		$oResult = Query::run($oReport->query, $aConstraintValues);
		if ($oResult) {
			$iResultCount = $oResult->num_rows;
			
			if ($iResultCount > 0) {
				$oReportDeliveryFormat = Report_Delivery_Format::getForId($mData->delivery_format);
				$oReportDeliveryMethod = Report_Delivery_Method::getForId($mData->delivery_method);
				
				$oSpreadsheet = new Logic_Spreadsheet(array());
			
				$iRow = 0;
				while ($aRow = $oResult->fetch_assoc())	{
					$aKeys = array_keys($aRow);
					$aValues = array_values($aRow);

					//Get the Field names if first row and write them to sheet before inserting any data
					if (!$iRow) {
						$oSpreadsheet->addRecord($aKeys);
					}

					$oSpreadsheet->addRecord($aValues);

					$iRow++;
				}

				$sReportTempPath = FLEX_BASE_PATH.self::TEMP_REPORT_UPLOAD_PATH.date('Y')."/".date('F')."/".date('j')."/";

				//Create required file path folder if it doesn't exist
				while (!is_dir($sReportTempPath)) {
					mkdir($sReportTempPath, '0777',true);
					chmod(FLEX_BASE_PATH.self::TEMP_REPORT_UPLOAD_PATH.date('Y'), 0777);
					chmod(FLEX_BASE_PATH.self::TEMP_REPORT_UPLOAD_PATH.date('Y')."/".date('F'), 0777);
					chmod(FLEX_BASE_PATH.self::TEMP_REPORT_UPLOAD_PATH.date('Y')."/".date('F')."/".date('j'), 0777);
				}

				//Create Workbook
				$sFilename = str_replace(" ", "_", $oReport->name) . "." .strtolower($oReportDeliveryFormat->name);
				$sTmpFilePath = $sReportTempPath . $sFilename;
				@unlink($sFilename);

				// Set File type for Logic Spreadsheet as CSV
				$oSpreadsheet->saveAs($sTmpFilePath, ($oReportDeliveryFormat->name === 'XLS'?'Excel2007':$oReportDeliveryFormat->name));
				chmod($sTmpFilePath, 0777);

				//Use Proper Delivery Method
				if (strtoupper($oReportDeliveryMethod->name) == 'EMAIL') {
					
					$sAttachmentContent = file_get_contents($sTmpFilePath);
					$sCurrentTimestamp = date('d/m/Y H:i:s');
					$aHeaders = array(
							'From' => "reports@yellowbilling.com.au",
							'Subject' => "{$oReport->name} requested on {$sCurrentTimestamp}"
						);


					$oEmailFlex	= new Email_Flex();
					$oEmailFlex->setSubject($aHeaders['Subject']);

					$delivery_employees = explode(",", $mData->selectedDeliveryEmployees);
					$aReceivers = array();
					for ($i=0; $i<count($delivery_employees);$i++) {
						$oEmployee = Employee::getForId($delivery_employees[$i]);
						$aEmployee = $oEmployee->toArray();
						$oEmailFlex->addTo($oEmployee->Email);
						$oEmailFlex->setFrom($aHeaders['From']);
						// Generate Content
			 			$strContent	= "Dear {$aEmployee['FirstName']},\n\n";
						$strContent .= "Attached is the Ad-Hoc Report ({$oReport->name}) you requested on {$sCurrentTimestamp}.";
						$strContent .= "\n\nPablo\nYellow Billing Mascot";
						$oEmailFlex->setBodyText($strContent);
						// Attachment (file to deliver)
						if (strtoupper($oReportDeliveryFormat->name) == "XLS") {
							$sMimeType = "application/x-msexcel";
						} else if (strtoupper($oReportDeliveryFormat->name) == "CSV") {
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
								'success' => true,
								'bSuccess' => false,
								'sMessage' => $oException->getMessage()
							);
						}
					}
					return 	array(
							'success' => true,
							'bSuccess' => true,
							'bIsEmail' => true,
							'sMessage' => "Report emailed successfully to " . implode(", ",$aReceivers)
						);
				} else if (strtoupper($oReportDeliveryMethod->name) == "DOWNLOAD") {
					return 	array(
							'success' => true,
							'bSuccess' => true,
							'bIsEmail' => false,
							'sFilename' => $sFilename
						);
				}
			} else {
				return 	array(
						'success' => true,
						'bSuccess' => false,
						'sMessage' => "No Result Available for Report"
				);
			}
		} else {
			return 	array(
					'success' => true,
					'bSuccess' => false,
					'sMessage' => "Error While Generating report"
				);
		}
	}
}