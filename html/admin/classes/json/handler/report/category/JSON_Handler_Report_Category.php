<?php
class JSON_Handler_Report_Category extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getAll() {
		// Check user authorisation and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_PROPER_ADMIN);
		$aReportCategory = Report_Category::getAll();
		$aResultSet = array();
		foreach ($aReportCategory as $iKey=>$oReportCategory) {
			array_push($aResultSet, $oReportCategory->toArray());
		}
		return array(
			'success' => true,
			'report_categories' => $aResultSet
		);
	}
}