<?php
class JSON_Handler_Report_Delivery_Format extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getAll() {
		$aReportDeliveryFormat = Report_Delivery_Format::getAll();
		$aResultSet = array();
		foreach ($aReportDeliveryFormat as $iKey=>$oReportDeliveryFormat) {
			array_push($aResultSet, $oReportDeliveryFormat->toArray());
		}
		return array(
			'success' => true,
			'report_delivery_formats' => $aResultSet
		);
	}
}