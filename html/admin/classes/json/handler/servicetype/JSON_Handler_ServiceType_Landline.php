<?php
class JSON_Handler_ServiceType_Landline extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getPostalAddressTypes() {
		$postalAddressTypes = array();
		foreach ($GLOBALS['*arrConstant']['PostalAddrType'] as $code=>$postalAddressType) {
			$postalAddressTypes[$code] = $postalAddressType['Description'];
		}
		return array(
			'postalAddressTypes' => $postalAddressTypes
		);
	}

	public function getStandardAddressTypes() {
		$standardAddressTypes = array();
		foreach ($GLOBALS['*arrConstant']['ServiceAddrType'] as $code=>$standardAddressType) {
			if (!isset($GLOBALS['*arrConstant']['PostalAddrType'][$code]) && $code !== 'LOT') {
				$standardAddressTypes[$code] = $standardAddressType['Description'];
			}
		}
		return array(
			'standardAddressTypes' => $standardAddressTypes
		);
	}

	public function getStreetTypes() {
		$streetTypes = array();
		foreach ($GLOBALS['*arrConstant']['ServiceStreetType'] as $code=>$serviceStreetType) {
			$streetTypes[$code] = $serviceStreetType['Description'];
		}
		return array(
			'streetTypes' => $streetTypes
		);
	}

	public function getStreetTypeSuffixes() {
		$streetTypeSuffixes = array();
		foreach ($GLOBALS['*arrConstant']['ServiceStreetSuffixType'] as $code=>$serviceStreetTypeSuffix) {
			$streetTypeSuffixes[$code] = $serviceStreetTypeSuffix['Description'];
		}
		return array(
			'streetTypeSuffixes' => $streetTypeSuffixes
		);
	}

	public function getStates() {
		$states = array();
		foreach ($GLOBALS['*arrConstant']['ServiceStateType'] as $code=>$serviceState) {
			$states[$code] = $serviceState['Description'];
		}
		return array(
			'states' => $states
		);
	}
}