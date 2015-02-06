<?php
class Cli_App_Service extends Cli {
	const SWITCH_COMMIT = 'c';
	const SWITCH_FILE = "f";

	function run() {
		$dataAccess = DataAccess::getDataAccess();
		$dataAccess->TransactionStart(false);
		try {
			$log = Log::get();
			$this->_aArgs = $this->getValidatedArguments();
			$data = $this->_getDataForFile();

			$log->log("Processing " . count($data) . " Services:");
			
			foreach ($data as $row) {
				if (strlen(trim($row)) === 0) {
					$log->log("Skipping empty line");
					continue;
				}
				$parsed = array_associate([
					0 => 'accountId',
					1 => 'fnn',
					2 => 'ratePlanId',
					3 => 'serviceTypeId',
					4 => 'createdOn',
					5 => 'serviceStatusId'
				], File_CSV::parseLineRFC4180($row));

				// Validation
				if (count($parsed) < 6) {
					throw new Exception("Cant parse file, invalid row found, row = '{$row}'\n");
				}

				// Account validation.
				$account = Account::getForId($parsed['accountId']);
				if (!$account) {
					throw new Exception("Unable to resolve Account ID: {$parsed['accountId']}");
				}

				// Date validation.
				if (strtotime($parsed['createdOn']) === false) {
					throw new Exception("Invalid createdOn, parsed value = '{$parsed['createdOn']}'");
				}
				if (!DateTime::createFromFormat('Y-m-d H:i:s', $parsed['createdOn'])) {
					throw new Exception("Invalid date format supplied, expecting 'Y-m-d H:i:s', parsed value = '{$parsed['createdOn']}'");
				}
				// Service Type validation.
				$serviceType = Service_Type::getForId($parsed['serviceTypeId']);
				if (!$serviceType) {
					throw new Exception("Unable to resolve Service Type ID: {$parsed['serviceTypeId']}");
				}
				$serviceStatus = Service_Status::getForId($parsed['serviceStatusId']);
				if (!$serviceStatus) {
					throw new Exception("Unable to resolve Service Status ID: {$parsed['serviceStatusId']}");
				}
				// Service Status validation
				if (!in_array(intval($parsed['serviceStatusId']), [SERVICE_PENDING, SERVICE_ACTIVE])) {
					throw new Exception("Service Status: {$serviceStatus->description} ({$serviceStatus->id}) in not one of the accepted values: Pending Activation (" . SERVICE_PENDING . ") or Active (" . SERVICE_ACTIVE . ")");
				}
				// Plan validation.
				$ratePlan = Rate_Plan::getForId($parsed['ratePlanId']);
				if (!$ratePlan) {
					throw new Exception("Unable to resolve Rate Plan ID: {$parsed['ratePlanId']}");
				}
				if ($ratePlan->ServiceType !== $serviceType->id) {
					throw new Exception("Rate Plan Service Type ID: {$ratePlan->ServiceType}, does not match Service Type ID: {$serviceType->id}, for FNN: {$parsed['fnn']}");
				}
				if ($ratePlan->customer_group !== $account->CustomerGroup) {
					throw new Exception("Rate Plan Customer Group ID: {$ratePlan->customer_group}, does not match Account Customer Group ID: {$account->CustomerGroup}, for FNN: {$parsed['fnn']}");
				}
				if ($ratePlan->Archived !== RATE_STATUS_ACTIVE) {
					throw new Exception("Rate Plan Archived: {$ratePlan->Archived}, does not match: 0, for FNN: {$parsed['fnn']}");
				}

				// Existing Service validation.
				$existingServicesForFNN = Service::getFNNInstances($parsed['fnn']);
				foreach ($existingServicesForFNN as $existingService) {
					// Check if Service Type matches
					if ($existingService['ServiceType'] === $serviceType->id) {
						// Check if its active.
						if ($existingService['ClosedOn'] === null) {
							throw new Exception("Active Service with the same Service Type ID: {$serviceType->id}, already exists.");
						}
						if (strtotime($existingService['ClosedOn']) < strtotime($parsed['createdOn'])) {
							throw new Exception("Active Service with the same Service Type ID: {$serviceType->id}, already exists.");
						}
					} else {
						// Different ServiceType, this is ok.
					}
				}

				// Figure out the Status..
				if ($account->Archived == ACCOUNT_STATUS_PENDING_ACTIVATION || $parsed['serviceStatusId'] === SERVICE_PENDING) {
					$status = SERVICE_PENDING;
				} else {
					$status = $parsed['serviceStatusId'];
				}

				$customerGroup = Customer_Group::getForId($account->CustomerGroup);

				$log->log("	* FNN: {$parsed['fnn']}");
				$log->log("		* Account ID: {$account->Id} ({$account->BusinessName})");
				$log->log("		* Customer Group ID: {$customerGroup->Id} ({$customerGroup->description})");
				$log->log("		* Service Type ID: {$serviceType->id} ({$serviceType->description})");
				$log->log("		* Rate Plan ID: {$ratePlan->Id} ({$ratePlan->description})");
				$log->log("		* Created On: {$parsed['createdOn']}");
				$log->log("		* Service Status: {$serviceStatus->id} ({$serviceStatus->description})");

				// Everything seems good, proceed to creating the Service.
				$service = new Service([
					'FNN' => $parsed['fnn'],
					'ServiceType' => $serviceType->id,
					'residential' => 0,
					'Indial100' => 0,
					'AccountGroup' => $account->AccountGroup,
					'Account' => $account->Id,
					'CreatedOn' => $parsed['createdOn'], // Format the date as Y-m-d H:i:s
					'CreatedBy' => USER_ID,
					'NatureOfCreation' => SERVICE_CREATION_NEW,
					'Status' => $status,
					'CappedCharge' => 0,
					'UncappedCharge' => 0,
					'ForceInvoiceRender' => 0,
					'Cost' => 0
				]);
				$service->save();
				$log->log("			 + Saving Service…");

				// Set Service Plan
				$service->setPlanFromStartDatetime($ratePlan, 1, $service->CreatedOn);
				$log->log("			 + Setting Rate Plan…");

				// Currently Supported Service Type Modules
				switch ($serviceType->module) {
					case 'M2_NBN':
						// OK
						break;
					case 'IFTel_Engin_VOIP':
						// OK
						break;
					default:
						// Everything else
						throw new Exception("Unsupported/Unimplemented serviceType.module '{$serviceType->module}'");
						break;
				}
			}

			// Commit
			if ($this->_aArgs[self::SWITCH_COMMIT]) {
				$log->log("Committing changes…");
				$dataAccess->TransactionCommit(false);
			} else {
				$log->log("Not running in 'commit' mode, reverting changes…");
				$dataAccess->TransactionRollback(false);
			}
		} catch (Exception $exception) {
			$log->log($exception . "; reverting changes…");
			$dataAccess->TransactionRollback(false);
			return 1;
		}
	}

	private function _getDataForFile() {
		$dataFileLocation = $this->_aArgs[self::SWITCH_FILE];
		if (!file_exists($dataFileLocation)) {
			throw new Exception("File path not found = '{$dataFileLocation}'");
		} else {
			// Remove any duplicates in the file
			$data = array_unique(file($dataFileLocation));
			return $data;		
		}
	}

	function getCommandLineArguments() {
		return array(
			self::SWITCH_COMMIT => array(
				self::ARG_REQUIRED => false,
				self::ARG_LABEL => "COMMIT",
				self::ARG_DESCRIPTION => "Changes will be made to the database.",
				self::ARG_DEFAULT => false,
				self::ARG_VALIDATION => 'Cli::_validIsSet()'
			),
			self::SWITCH_FILE => array(
				self::ARG_REQUIRED => true,
				self::ARG_LABEL => "FILE",
				self::ARG_DESCRIPTION => "Import File Location",
				self::ARG_VALIDATION => 'Cli::_validString("%1$s")'
			)
		);
	}
}