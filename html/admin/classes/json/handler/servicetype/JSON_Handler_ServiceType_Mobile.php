<?php
class JSON_Handler_ServiceType_Mobile extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getStates() {
		$statesResult = DataAccess::get()->query('SELECT * FROM state');

		$states = array();
		while ($state = $statesResult->fetch_assoc()) {
			$states[$state['code']] = $serviceState['name'];
		}
		return array(
			'states' => $states
		);
	}
}