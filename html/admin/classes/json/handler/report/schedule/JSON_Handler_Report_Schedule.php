<?php
class JSON_Handler_Report_Schedule extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function saveSchedule($mData) {
		// Check user authorisation and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);

		$oReportSchedule = new Report_Schedule();
		$oReportSchedule->report_id = $mData->id;
		$oReportSchedule->report_frequency_type_id = (int)$mData->report_frequency_type_id;
		$oReportSchedule->frequency_multiple = (int)$mData->frequency_multiple;
		$oReportSchedule->schedule_datetime = $mData->schedule_datetime;
		$oReportSchedule->schedule_end_datetime = $mData->schedule_end_datetime;
		$oReportSchedule->is_enabled = 1;
		$oReportSchedule->compiled_query = '';
		$oReportSchedule->scheduled_employee_id = Flex::getUserId();
		$oReportSchedule->scheduled_datetime = date("Y-m-d H:i:s");
		$oReportSchedule->report_delivery_format_id = $mData->delivery_format;
		$oReportSchedule->report_delivery_method_id = $mData->delivery_method;
		$oReportSchedule->filename = $mData->filename;
		$oReportSchedule->save();

		if (trim($mData->selectedDeliveryEmployees) != "") {
			$aReportDeliveryEmployee = explode(",",$mData->selectedDeliveryEmployees);
			foreach($aReportDeliveryEmployee as $iReportDeliveryEmployeeId) {
				//Create ORM Object First and Save it to Delivery Employee Object
				$oReportDeliveryEmployee = new Report_Delivery_Employee();
				$oReportDeliveryEmployee->report_schedule_id = $oReportSchedule->id;
				$oReportDeliveryEmployee->employee_id = $iReportDeliveryEmployeeId;
				$oReportDeliveryEmployee->created_employee_id = Flex::getUserId();
				$oReportDeliveryEmployee->created_datetime = date("Y-m-d H:i:s");
				$oReportDeliveryEmployee->save();
			}
		}		

		$aConstraintResult = Report_Constraint::getConstraintForReportId($mData->id);
		$aConstraintValues = array();
		if (count($aConstraintResult)) {
			foreach ($aConstraintResult as $oConstraint) {
				$sConstraintName = $oConstraint->name;

				if (isset($mData->{$sConstraintName})) {
					$oReportScheduleConstraintValue = new Report_Schedule_Constraint_Value();
					$oReportScheduleConstraintValue->report_constraint_id = $oConstraint->id;
					$oReportScheduleConstraintValue->report_schedule_id = $oReportSchedule->id;
					$oReportScheduleConstraintValue->value = Query::prepareByPHPType($mData->{$sConstraintName});

					$oReportScheduleConstraintValue->save();
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
		return 	array(
			'success'	=> true,
			'bSuccess'	=> true,
			'sMessage'	=> "Report scheduled successfully"
		);
	}

	public function archiveSchedule($mData) {
		// Check user authorisation and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);

		$oReportSchedule = Report_Schedule::getForId($mData->iReportScheduleId);
		$oReportSchedule->is_enabled = 0;
		$oReportSchedule->save();

		return array(
				'success' => true
			);
	}
}