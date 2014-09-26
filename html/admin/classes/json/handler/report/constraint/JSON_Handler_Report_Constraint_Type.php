<?php
class JSON_Handler_Report_Constraint_Type extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getAll() {
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		$aReportConstraintType = Report_Constraint_Type::getAll();
		$aResultSet = array();
		foreach ($aReportConstraintType as $iKey=>$oReportConstraintType) {
			array_push($aResultSet, $oReportConstraintType->toArray());
		}
		return array(
			'success' => true,
			'report_constraint_types' => $aResultSet
		);
	}
}
