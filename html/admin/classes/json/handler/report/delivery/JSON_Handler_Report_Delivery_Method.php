<?php
class JSON_Handler_Report_Delivery_Method extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getAll() {
		// Check user authorisation and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_REPORT_USER);
		$aReportDeliveryMethod = Report_Delivery_Method::getAll();
		$aResultSet = array();
		foreach ($aReportDeliveryMethod as $iKey=>$oReportDeliveryMethod) {
			array_push($aResultSet, $oReportDeliveryMethod->toArray());
		}
		return array(
			'success' => true,
			'report_delivery_methods' => $aResultSet
		);
	}
}