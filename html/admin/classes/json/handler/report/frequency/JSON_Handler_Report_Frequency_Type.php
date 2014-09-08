<?php
class JSON_Handler_Report_Frequency_Type extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getAll() {
		$aReportFrequencyType = Report_Frequency_Type::getAll();
		$aResultSet = array();
		foreach ($aReportFrequencyType as $iKey=>$oReportFrequencyType) {
			array_push($aResultSet, $oReportFrequencyType->toArray());
		}
		return array(
			'success' => true,
			'report_frequency_types' => $aResultSet
		);
	}
}
