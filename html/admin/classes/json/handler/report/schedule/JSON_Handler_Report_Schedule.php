<?php
class JSON_Handler_Report_Schedule extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function saveSchedule($mData) {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		$oReportSchedule = new Report_Schedule();
		$oReportSchedule->report_id = $mData->id;
		$oReportSchedule->report_frequency_type_id = (int)$mData->report_frequency_type_id;
		$oReportSchedule->frequency_multiple = (int)$mData->frequency_multiple;
		$oReportSchedule->schedule_datetime = $mData->schedule_datetime;
		$oReportSchedule->is_enabled = 1;
		$oReportSchedule->compiled_query = '';
		$oReportSchedule->scheduled_employee_id = Flex::getUserId();
		$oReportSchedule->scheduled_datetime = date("Y-m-d H:i:s");
		$oReportSchedule->save();

		$aConstraintResult = Report_Constraint::getConstraintForReportId($mData->id);
		
		$aConstraintValues = array();
		if (sizeof($aConstraintResult)) {
			foreach ($aConstraintResult as $oConstraint) {
				$sConstraintName = $oConstraint->name;

				if(isset($mData->{$sConstraintName})) {
					$oReportScheduleConstraintValue = new Report_Schedule_Constraint_Value();
					$oReportScheduleConstraintValue->report_constraint_id = $oConstraint->id;
					$oReportScheduleConstraintValue->report_schedule_id = $oReportSchedule->id;
					$oReportScheduleConstraintValue->value = $mData->{$sConstraintName};

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
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);

		$oReportSchedule = Report_Schedule::getForId($mData->iReportScheduleId);
		$oReportSchedule->is_enabled = 0;
		$oReportSchedule->save();

		return array(
				'success' => true
			);
	}
}