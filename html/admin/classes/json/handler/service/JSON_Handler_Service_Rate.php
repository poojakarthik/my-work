<?php
class JSON_Handler_Service_Rate extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getActiveOrUpcoming($serviceId) {
		$serviceRates = array();
		$serviceRatesResult = DataAccess::get()->query('
			SELECT sr.id,
				r.Id AS rate_id,
				r.Name AS name,
				r.Description AS description,
				r.RecordType AS record_type_id,
				rt.Name AS record_type_name,
				rt.Description AS record_type_description,
				r.Fleet AS is_fleet,
				sr.start_datetime,
				sr.end_datetime
			FROM service_rate sr
				JOIN Rate r ON (r.Id = sr.rate_id)
				JOIN RecordType rt ON (rt.Id = r.RecordType)
			WHERE sr.service_id = <service_id>
				AND COALESCE(<effective_date>, NOW()) <= sr.end_datetime
			ORDER BY (COALESCE(<effective_date>, NOW()) BETWEEN sr.start_datetime AND sr.end_datetime) DESC,
				sr.created_datetime DESC,
				sr.id DESC
		', array(
			'service_id' => $serviceId,
			'effective_date' => null
		));
		while ($serviceRate = $serviceRatesResult->fetch_object()) {
			$serviceRates []= $serviceRate;
		}

		return array(
			'success' => true,
			'serviceRates' => $serviceRates
		);
	}

	const OVERRIDE_STARTS_IMMEDIATELY = 'immediately';
	const OVERRIDE_STARTS_DATE = 'date';
	const OVERRIDE_ENDS_INDEFINITELY = 'indefinite';
	const OVERRIDE_ENDS_DATE = 'date';
	public function saveNew(stdClass $details) {
		// throw new Exception(print_r($details, true));

		if (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN)) {
			throw new Exception('You do not have sufficient permission to add Override Rates.');
		}

		$errors = array();
		$serviceRate = new Service_Rate();

		// Validate & Clean
		// Service
		if (!preg_match('/^\d+$/', $details->service->id)) {
			$errors []= 'No Service specified. Contact Flex support.';
		} else {
			try {
				$serviceRate->service_id = Service::getForId($details->service->id)->Id;
			} catch (Exception_ORM_LoadById $exception) {
				$errors []= sprintf('Service with Id #%d doesnt\'t exist. Contact Flex support.', $details->service->id);
			}
		}

		// Rate
		if (!preg_match('/^\d+$/', $details->rate->id)) {
			$errors []= 'No Rate specified. If you are sure you have selected a Rate to override with, contact Flex support.';
		} else {
			try {
				$serviceRate->rate_id = Rate::getForId($details->rate->id)->Id;
			} catch (Exception_ORM_LoadById $exception) {
				$errors []= sprintf('Rate with Id #%d doesnt\'t exist. Contact Flex support.', $details->service->id);
			}
		}

		// Starts
		if ($details->starts === self::OVERRIDE_STARTS_IMMEDIATELY) {
			$serviceRate->start_datetime = date('Y-m-d', DataAccess::get()->now(true));
		} elseif ($details->starts === self::OVERRIDE_STARTS_DATE) {
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $details->start_date)) {
				$errors []= sprintf('Invalid starting date provided: %s', var_export($details->start_date, true));
			} else {
				$serviceRate->start_datetime = $details->start_date . ' 00:00:00';
			}
		} else {
			$errors []= 'No starting date specified. If you are sure you have selected a starting date for the override, contact Flex support.';
		}

		// Ends
		if ($details->ends === self::OVERRIDE_ENDS_INDEFINITELY) {
			$serviceRate->end_datetime = '9999-12-31 23:59:59';
		} elseif ($details->ends === self::OVERRIDE_ENDS_DATE) {
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $details->end_date)) {
				$errors []= sprintf('Invalid ending date provided: %s', var_export($details->end_date, true));
			} else {
				$serviceRate->end_datetime = $details->end_date . ' 23:59:59';
			}
		} else {
			$errors []= 'No ending date specified. If you are sure you have selected a ending date for the override, contact Flex support.';
		}

		// Enforce: Starts < Ends
		if (isset($serviceRate->start_datetime) && isset($serviceRate->end_datetime) && $serviceRate->start_datetime >= $serviceRate->end_datetime) {
			$errors []= 'The override must end after it starts';
		}

		// Errors? Bail out
		if (count($errors)) {
			return array(
				'success' => false,
				'errors' => $errors
			);
		}

		// Save override
		DataAccess::get()->TransactionStart(false);
		try {
			$serviceRate->created_employee_id = Flex::getUserId();
			$serviceRate->created_datetime = DataAccess::get()->now();
			$serviceRate->save();

			// Mark relevant CDRs to be re-rated
			// $cdrUpdateResult = DataAccess::get()->query('
			// 	UPDATE CDR c
			// 		LEFT JOIN InvoiceRun ir ON (ir.Id = c.invoice_run_id)
			// 	SET c.Status = <cdr_status_rerate>
			// 	WHERE c.Service = <service_id>
			// 		AND c.StartDatetime BETWEEN <start_datetime> AND <end_datetime>
			// 		AND c.RecordType = <record_type_id>
			// 		AND COALESCE(c.DestinationCode, 0) = COALESCE(<destination_code>, 0)
			// 		AND c.Status IN (<reratable_cdr_statuses>)
			// 		AND (
			// 			ir.Id IS NULL
			// 			OR ir.invoice_run_type_id NOT IN (<commitable_invoice_run_type_ids>)
			// 		)
			// ', array(
			// 	'cdr_status_normalised' => CDR_RERATE,
			// 	'start_datetime' => $serviceRate->start_datetime,
			// 	'end_datetime' => $serviceRate->end_datetime,
			// 	'record_type_id' => $rate->RecordType,
			// 	'destination_code' => $rate->Destination,
			// 	'reratable_cdr_statuses' => array(CDR_RATED, CDR_TEMP_INVOICE),
			// 	'commitable_invoice_run_type_ids' => array(INVOICE_RUN_TYPE_LIVE, INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM_FIRST)
			// ));

			// throw new Exception('Test: ' . print_r($serviceRate->toArray(), true));

			// Commit
			DataAccess::get()->TransactionCommit(false);
		} catch (Exception $exception) {
			DataAccess::get()->TransactionRollback(false);
			throw $exception;
		}

		// DataAccess::get()->TransactionRollback(false);
		return array(
			'success' => true,
			'serviceRate' => $serviceRate->toStdClass()
		);
	}
}