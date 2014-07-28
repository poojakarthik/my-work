<?php
class JSON_Handler_Service extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	const RATE_SEARCH_LIMIT = 15;
	public function searchAvailableRates($serviceId, stdClass $constraints) {
		$db = DataAccess::get();
		$oldCharset = $db->refMysqliConnection->character_set_name();
		$db->refMysqliConnection->set_charset('utf8');

		$constraints = object_merge((object)array(
			'terms' => array(),
			'record_type_id' => null
		), $constraints);

		Log::get()->formatLog('Constraints: %s', print_r($constraints, true));

		// Prepare search terms
		// A single "record" has to match all terms, but it can match on any property of the record
		$likeTerms = array();
		foreach ($constraints->terms as $term) {
			$likeTerms []= new Query_Placeholder_LikeExpressionSet(
				array('r.Name', 'r.Description', 'rt.Name', 'rt.Description', 'd.Description'),
				$term,
				Query_Placeholder_LikeExpressionSet::OPERATOR_OR
			);
		}

		$ratesResult = $db->query("
			SELECT r.*,
				d.Description AS destination_description,
				rt.Name AS record_type_name,
				rt.Description AS record_type_description,
				rt.DisplayType AS record_type_unit_type
			FROM Service s
				JOIN Rate r ON (
					r.ServiceType = s.ServiceType
					AND r.Archived = <rate_status_id>
				)
				JOIN RecordType rt ON (
					rt.ServiceType = s.ServiceType
					AND rt.Id = r.RecordType
				)
				LEFT JOIN Destination d ON (
					d.Code = r.Destination
				)
			WHERE s.Id = <service_id>
				AND (
					(ISNULL(<record_type_id>) OR rt.Id = <record_type_id>)
					AND <LIKE-terms>
				)
			ORDER BY r.Id DESC
			LIMIT <rate_search_limit>;
		", array(
			'service_id' => $serviceId,
			'record_type_id' => $constraints->record_type_id,
			'LIKE-terms' => new Query_Placeholder_QueryPlaceholderSet($likeTerms, Query_Placeholder_QueryPlaceholderSet::OPERATOR_AND),
			'rate_status_id' => RATE_STATUS_ACTIVE,
			'rate_search_limit' => self::RATE_SEARCH_LIMIT
		));

		$aRates = array();
		while ($oRate = $ratesResult->fetch_object()) {
			$aRates[] = $oRate;
		}

		$db->refMysqliConnection->set_charset($oldCharset);
		return array(
			'rates' => $aRates
		);
	}

	public function saveNew($iAccountId, $oServicesData) {
		$aServices = array();
		foreach ((array)$oServicesData as $sIndex=>$oServiceData) {
			// Clean
			$oCleanedServiceData = self::_cleanServiceData($oServiceData);

			// Validate


			// Save Service
			// TODO

			// Save Service Properties
			// TODO
		}

		return array(
			'phpData' => var_export($oServices, true)
		);
	}

	private static function _cleanServiceData($oServiceData) {
		$oCleanedServiceData = (object)array(
			'rate_plan_id' => null
		);

		// Service Type
		$oCleanedServiceData->service_type_id = (int)$oServiceData->service_type_id;

		// Rate Plan
		if (preg_match('/^\d+$/', $oServiceData->rate_plan_id)) {
			$oCleanedServiceData->rate_plan_id = (int)$oServiceData->rate_plan_id;
		}

		return $oCleanedServiceData;
	}

	private static function _validateCleanedServiceData($oCleanedServiceData) {
		$aErrors = array();

		// Service Type
		$oServiceType = Service_Type::getForId($oCleanedServiceData->service_type_id, true);
		if ($oServiceType === false) {
			$aErrors[] = new Exception_Validation('Couldn\'t find Service Type with Id: ' . var_export($oCleanedServiceData->service_type_id, true));
		}

		// Rate Plan
		if ($oServiceType !== false) {
			$oRatePlan = Rate_Plan::getForId($oCleanedServiceData->rate_plan_id, true);
			if ($oRatePlan === false) {
				$aErrors[] = new Exception_Validation('Couldn\'t find Rate Plan with Id: ' . var_export($oCleanedServiceData->rate_plan_id, true));
			} elseif ($oRatePlan->ServiceType !== $oServiceType->id) {
				$aErrors[] = new Exception_Validation('Rate Plan #' . var_export($oRatePlan->Id, true) . ' is not configured for use with Service Type #' . var_export($oServiceType->id, true));
			}
		}
	}
}