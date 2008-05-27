<?php


// The ModuleService object models a service for a particular Account
// The service is identified by any of the Ids of the Service records which
// were used to model this service for the account that the records belong to
// The service is also identifiable with an FNN and Account Id


abstract class ModuleService
{
	protected $_strErrorMsg				= NULL;
	
	// You can't change the status unless everything else is saved
	protected $_bolHasUnsavedChanges	= NULL;
	
	protected $_strFNN					= NULL;
	protected $_intServiceType			= NULL;
	protected $_intCostCentre			= NULL;
	protected $_intAccount				= NULL;
	protected $_intAccountGroup			= NULL;
	protected $_bolForceInvoiceRender	= NULL;
	protected $_bolIndial100			= NULL;
	protected $_intCurrentId			= NULL;
	
	protected $_arrServiceRecords		= NULL;
	
	protected $_arrCurrentPlan			= NULL;
	protected $_arrFuturePlan			= NULL;
	protected $_bolPlanDetailsLoaded	= NULL;
	
	protected $_arrRateGroupDetails		= NULL;
	protected $_arrAdjustments			= NULL;
	protected $_arrRecurringAdjustments	= NULL;
	protected $_arrCostCentre			= NULL;
	
/******************************************************************************/
// Constructor
/******************************************************************************/
	// The user must supply a Service Id
	function __construct($intService)
	{
		// Service Id has been supplied
		// Retrieve its details
		if (!$this->_LoadDetails($intService))
		{
			throw new Exception($this->_strErrorMsg);
		}
	}

	// Loads in the service from the database (doesn't load extra details)
	protected function _LoadDetails($intService)
	{
		$strWhere	= "	Account = (SELECT Account FROM Service WHERE Id = <ServiceId>)
						AND
						FNN = (SELECT FNN FROM Service WHERE Id = <ServiceId>)";
		$arrWhere	= Array("ServiceId" => $intService);
		
		$selServices	= new StatementSelect("Service", "*", $strWhere, "Id DESC");
		$mixResult		= $selServices->Execute($arrWhere);
		
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve details for service with Id: $intService";
			return FALSE;
		}
		elseif ($mixResult == 0)
		{
			// No records were returned
			$this->_strErrorMsg = "No Service records could be found in the database relating to Service Id: $intService";
			return FALSE;
		}
		
		// Save details from the most recent Service Record modelling this FNN for this account
		$arrRecord						= $selServices->Fetch();
		$this->_strFNN 					= $arrRecord['FNN'];
		$this->_intServiceType			= $arrRecord['ServiceType'];
		$this->_intCostCentre			= $arrRecord['CostCentre'];
		$this->_intAccount				= $arrRecord['Account'];
		$this->_intAccountGroup			= $arrRecord['AccountGroup'];
		$this->_bolForceInvoiceRender	= (bool)$arrRecord['ForceInvoiceRender'];
		$this->_bolIndial100			= (bool)$arrRecord['Indial100'];
		$this->_intCurrentId			= $arrRecord['Id'];
		
		// Build the ServiceRecords array
		$this->_arrServiceRecords = Array();
		do
		{
			$this->_arrServiceRecords[] = Array(
											"ServiceId"				=> $arrRecord['Id'],
											"CreatedOn"				=> $arrRecord['CreatedOn'],
											"ClosedOn"				=> $arrRecord['ClosedOn'],
											"CreatedBy"				=> $arrRecord['CreatedBy'],
											"ClosedBy"				=> $arrRecord['ClosedBy'],
											"NatureOfCreation"		=> $arrRecord['NatureOfCreation'],
											"NatureOfClosure"		=> $arrRecord['NatureOfClosure'],
											"Status"				=> $arrRecord['Status'],
											"LineStatus"				=> $arrRecord['LineStatus'],
											"LineStatusDate"			=> $arrRecord['LineStatusDate'],
											"PreselectionStatus"		=> $arrRecord['PreselectionStatus'],
											"PreselectionStatusDate"	=> $arrRecord['PreselectionStatusDate'],
											"NextOwner"				=> $arrRecord['NextOwner'],
											"LastOwner"				=> $arrRecord['LastOwner'],
											"CappedCharge"			=> $arrRecord['CappedCharge'],
											"UncappedCharge"		=> $arrRecord['UncappedCharge'],
											"Carrier"				=> $arrRecord['Carrier'],
											"CarrierPreselect"		=> $arrRecord['CarrierPreselect']
										);
			$arrRecord = $selServices->Fetch();
		} while ($arrRecord !== FALSE);
		
		return TRUE;
	}

	// Refresh the Service Object from the database
	function Refresh()
	{
		if ($this->_LoadDetails($this->_intCurrentId) === FALSE)
		{
			return FALSE;
		}
		
		// Refresh the plan details if they have already been loaded
		if ($this->_bolPlanDetailsLoaded)
		{
			if (!$this->LoadPlanDetails())
			{
				return FALSE;
			}
		}
		
		return TRUE;
	}
	
	// Loads in details regarding the current plan and future scheduled plan, if there is one
	function LoadPlanDetails()
	{
		// Get the Id of the most recent Service Record
		$intServiceId = $this->_intCurrentId;
		
		$strTables	= "	Service AS S 
						LEFT JOIN ServiceRatePlan AS SRP1 ON S.Id = SRP1.Service AND SRP1.Id = (SELECT SRP2.Id 
								FROM ServiceRatePlan AS SRP2 
								WHERE SRP2.Service = S.Id AND NOW() BETWEEN SRP2.StartDatetime AND SRP2.EndDatetime
								ORDER BY SRP2.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP1 ON SRP1.RatePlan = RP1.Id
						LEFT JOIN ServiceRatePlan AS SRP3 ON S.Id = SRP3.Service AND SRP3.Id = (SELECT SRP4.Id 
								FROM ServiceRatePlan AS SRP4 
								WHERE SRP4.Service = S.Id AND SRP4.StartDatetime BETWEEN NOW() AND SRP4.EndDatetime
								ORDER BY SRP4.CreatedOn DESC
								LIMIT 1
								)
						LEFT JOIN RatePlan AS RP2 ON SRP3.RatePlan = RP2.Id";
		$arrColumns	= Array("CurrentPlanId" 			=> "RP1.Id",
							"CurrentPlanName"			=> "RP1.Name",
							"FuturePlanId"				=> "RP2.Id",
							"FuturePlanName"			=> "RP2.Name",
							"FuturePlanStartDatetime"	=> "SRP3.StartDatetime");
		$strWhere	= "	S.Id = <ServiceId>";
		$arrWhere	= Array("ServiceId" => $intServiceId);
		$selPlans	= new StatementSelect($strTables, $arrColumns, $strWhere);
		
		$mixResult	= $selPlans->Execute($arrWhere);
		
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve Plan details for service with Id: $intServiceId";
			return FALSE;
		}
		
		$arrRecord = $selPlans->Fetch();
		$this->_arrCurrentPlan = NULL;
		if ($arrRecord['CurrentPlanId'] != NULL)
		{
			// The service has a current plan defined
			$this->_arrCurrentPlan = Array(
											"PlanId"	=> $arrRecord['CurrentPlanId'],
											"Name"		=> $arrRecord['CurrentPlanName']
											);
		}
		
		$this->_arrFuturePlan = NULL;
		if ($arrRecord['FuturePlanId'] != NULL)
		{
			// The service has a plan scheduled to start some time in the future
			$this->_arrFuturePlan = Array(
											"PlanId"		=> $arrRecord['FuturePlanId'],
											"Name"			=> $arrRecord['FuturePlanName'],
											"StartDatetime"	=> $arrRecord['FuturePlanStartDatetime']
											);
		}
		
		$this->_bolPlanDetailsLoaded = TRUE;
		
		return TRUE;
	}
	
	function LoadAdjustmentDetails()
	{
		//TODO!
		$this->_strErrorMsg = "LoadAdjustmentDetails() functionality has not been implemented yet";
		return FALSE;
	}
	
	function LoadProvisioningHistory()
	{
		//TODO!
		$this->_strErrorMsg = "LoadProvisioningHistory() functionality has not been implemented yet";
		return FALSE;
	}
	
/******************************************************************************/
// Error Reporting methods
/******************************************************************************/

	// Returns the last error msg that was set
	function GetErrorMsg()
	{
		return $this->_strErrorMsg;
	}
	
	function IsOK()
	{
		return (bool)($this->_strErrorMsg === NULL);
	}
	
/******************************************************************************/
// Accessor Methods
/******************************************************************************/
	
	// Returns a multi-dimensional array representing the Service and all its currently loaded associated components
	function GetArray()
	{
		//TODO!
		$this->_strErrorMsg = "GetArray() functionality has not been implemented yet";
		return FALSE;
	}
	
	function GetId()
	{
		return $this->_arrHistory[0]['ServiceId'];
	}
	
	function GetFNN()
	{
		return $this->_strFNN;
	}
	
	function GetAccount()
	{
		return $this->_intAccount;
	}

	function GetAccountGroup()
	{
		return $this->_intAccountGroup;
	}

	function GetCostCentre()
	{
		return $this->_intCostCentre;
	}
	
	function GetServiceType()
	{
		return $this->_intServiceType;
	}
	
	
	// Builds an array detailing the history
	/*
	 * $arrHistory[]['ServiceId']			Id of the Service record that this particular Historical event is associated with
	 * 				['IsCreationEvent']		TRUE if the action falls under the SERVICE_CREATION_ group of actions/events
	 * 										FALSE if the action falls under the SERVICE_CLOSURE_ group of actions/events
	 * 				['Event']				References constant from the ServiceCreation ConstantGroup if IsCreationAction == TRUE
	 * 										References constant from the ServiceClosure ConstantGroup if IsCreationAction == FALSE
	 * 				['TimeStamp']			Time at which the event occured
	 * 				['Employee']			Id of the employee who instigated the event
	 * 				['RelatedService']		Id of the other service if the Event is a LESSEE_CHANGE, ACCOUNT_CHANGE, or REVERSAL
	 * The most recent event is first and the oldest event is last 
	 */
	function GetHistory()
	{
		$arrHistory = Array();
		
		// Iterate through the Service records stored in $this->_arrHistory
		foreach ($this->_arrServiceRecords as $intServiceId=>$arrServiceRecord)
		{
			if ($arrServiceRecord['ClosedOn'] != NULL)
			{
				$arrHistoryItem = Array("ServiceId"			=> $intServiceId,
										"IsCreationEvent"	=> FALSE,
										"TimeStamp"			=> $arrServiceRecord['ClosedOn'],
										"Employee"			=> $arrServiceRecord['ClosedBy']
										);
				if ($arrServiceRecord['NatureOfClosure'] != NULL)
				{
					// Nature of Closure is known
					$arrHistoryItem['Event'] = $arrServiceRecord['NatureOfClosure'];
				}
				else
				{
					// Nature of Closure is unknown.  Have a guess
					switch ($arrServiceRecord['Status'])
					{
						case SERVICE_DISCONNECTED:
							$arrHistoryItem['Event'] = SERVICE_CLOSURE_DISCONNECTED;
							break;
						case SERVICE_ARCHIVED:
							$arrHistoryItem['Event'] = SERVICE_CLOSURE_ARCHIVED;
							break;
						default:
							// This should never occur
							$arrHistoryItem['Event'] = NULL;
					}
				}
				
				// If the nature of closure relates to this Service being moved to another account
				// then store a reference to the Service Record which was created by the move
				if ($arrServiceRecord['NatureOfClosure'] == SERVICE_CLOSURE_LESSEE_CHANGED ||
					$arrServiceRecord['NatureOfClosure'] == SERVICE_CLOSURE_ACCOUNT_CHANGED ||
					$arrServiceRecord['NatureOfClosure'] == SERVICE_CLOSURE_LESSEE_CHANGE_REVERSED ||
					$arrServiceRecord['NatureOfClosure'] == SERVICE_CLOSURE_ACCOUNT_CHANGE_REVERSED)
				{
					// The event marks the moving of the service to another account
					// Include a reference to the new service record
					$arrHistoryItem['RelatedServiceId'] = $arrServiceRecord['NextOwner'];
				}
			}
			
			// Add the history item to the history
			$arrHistory[] = $arrHistoryItem;
			
			// If CreatedOn and ClosedOn are the same, then it means the Service Record was only added
			// so that the Status could be updated.  Don't bother storing details as to why this 
			// Service Record was created
			if ($arrServiceRecord['CreatedOn'] == $arrServiceRecord['ClosedOn'])
			{
				continue;
			}
			
			// Store the creation of this service record as a historical event
			$arrHistoryItem = Array("ServiceId"			=> $intServiceId,
									"IsCreationEvent"	=> TRUE,
									"TimeStamp"			=> $arrServiceRecord['CreatedOn'],
									"Employee"			=> $arrServiceRecord['CreatedBy']
									);
			if ($arrServiceRecord['NatureOfCreation'] != NULL)
			{
				// Nature of Creation is known
				$arrHistoryItem['Event'] = $arrServiceRecord['NatureOfCreation'];
			}
			else
			{
				// Nature of Creation is unknown.  Have a guess
				$arrHistoryItem['Event'] = SERVICE_CREATION_ACTIVATED;
			}
			
			// If the nature of creation relates to this Service having been moved from another account
			// then store a reference to the last Service Record which modelled this Physical Service
			if ($arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_LESSEE_CHANGED ||
				$arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_ACCOUNT_CHANGED ||
				$arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_LESSEE_CHANGE_REVERSED ||
				$arrServiceRecord['NatureOfCreation'] == SERVICE_CREATION_ACCOUNT_CHANGE_REVERSED)
			{
				// The event marks the moving of the service to another account
				// Include a reference to the new service record
				$arrHistoryItem['RelatedServiceId'] = $arrServiceRecord['LastOwner'];
			}
			
			// Add the history item to the history
			$arrHistory[] = $arrHistoryItem;
		}
		
		return $arrHistory;
	}

	
/******************************************************************************/
// Mutator Methods
/******************************************************************************/
	function SetFNN($strFNN)
	{
		$this->_bolHasUnsavedChanges = TRUE;
		$this->_strFNN = $strFNN;
		return TRUE;
	}
	
	function SetCostCentre($intCostCentre)
	{
		$this->_bolHasUnsavedChanges = TRUE;
		$this->_intCostCentre = $intCostCentre;
		return TRUE;
	}

	// Saves the details to the most recent Service Record modelling this Service for this account
	// Note that this will never create a new record
	// It assumes $this->_intCurrentId is the id of the most recent Service Record modelling this service for this account
	function SaveService()
	{
		$arrUpdate = Array(	"FNN"					=> $this->_strFNN,
							"CostCentre"			=> $this->_intCostCentre,
							"ForceInvoiceRender"	=> $this->_bolForceInvoiceRender
							);
		$strWhere	= "Id = <ServiceId>";
		$updService	= new StatementUpdate("Service", $strWhere, $arrUpdate);
		$mixResult	= $updService->Execute($arrUpdate, Array("ServiceId" => $this->_intCurrentId));
		
		if ($mixResult === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to update the Service Record details for service with Id '{$this->_intCurrentId}'";
			return FALSE;
		}
		
		$this->_bolHasUnsavedChanges = FALSE;
		return TRUE;
	}
	
	// This will commit changes to the database
	// This does not create a system note
	// NOTE that this will make the object out of date, and it should be reloaded
	// Change the status of a service
	function SetStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;
		
		if ($this->_bolHasUnsavedChanges)
		{
			// There are unsaved changes.  Save them now
			if ($this->SaveService() === FALSE)
			{
				return FALSE;
			}
		}
		
		if ($intStatus == SERVICE_ACTIVE)
		{
			// The service is being activated
			if ($this->_Activate($strTimeStamp) === FALSE)
			{
				return FALSE;
			}
		}
		else
		{
			// The service is being deactivated (disconnected or archived)
			if ($this->_Deactivate($intStatus, $strTimeStamp) === FALSE)
			{
				return FALSE;
			}
		}
		
		// If the current Service Id has changed then it means a new Service record has been added,
		// and we should copy across the plan details
		if ($this->_intCurrentId > $this->_arrServiceRecords[0]['ServiceId'])
		{
			$intNewServiceId	= $this->_intCurrentId;
			$intOldServiceId	= $this->_arrServiceRecords[0]['ServiceId'];
			
			// Copy all ServiceRatePlan records across from the old service where EndDatetime is in the future and StartDatetime < EndDatetime
			$strCopyServiceRatePlanRecordsToNewService =	"INSERT INTO ServiceRatePlan (Id, Service, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active) ".
															"SELECT NULL, $intNewServiceId, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active ".
															"FROM ServiceRatePlan WHERE Service = $intOldServiceId AND EndDatetime > '$strTimeStamp' AND StartDatetime < EndDatetime";
			$qryInsertServicePlanDetails = new Query();

			if ($qryInsertServicePlanDetails->Execute($strCopyServiceRatePlanRecordsToNewService) === FALSE)
			{
				// Inserting the records into the ServiceRatePlan table failed
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert records into the ServiceRatePlan table";
				return FALSE;
			}

			// Copy all ServiceRateGroup records across from the old service where EndDatetime is in the future and StartDatetime < EndDatetime
			$strCopyServiceRateGroupRecordsToNewService =	"INSERT INTO ServiceRateGroup (Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active) ".
															"SELECT NULL, $intNewServiceId, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active ".
															"FROM ServiceRateGroup WHERE Service = $intOldServiceId AND EndDatetime > '$strTimeStamp' AND StartDatetime < EndDatetime";

			if ($qryInsertServicePlanDetails->Execute($strCopyServiceRateGroupRecordsToNewService) === FALSE)
			{
				// Inserting the records into the ServiceRateGroup table failed
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert records into the ServiceRateGroup table";
				return FALSE;
			}
		}

		return TRUE;
	}
	
	// Activates the service (This will create a new service record, and copy across all details relating to the service,
	// which aren't specific to the service type)
	// It will not copy across all unbilled CDRs, Charges, Recurring Charges
	// It will copy across plan details
	protected function _Activate($strTimeStamp)
	{
		$intService		= $this->_intCurrentId;
		$strFNN			= $this->_strFNN;
		$bolIsIndial	= $this->_bolIndial100;
		
		// The most recent values for CreatedOn and ClosedOn
		$strCreatedOn	= $this->_arrServiceRecords[0]['CreatedOn'];
		$strClosedOn	= $this->_arrServiceRecords[0]['ClosedOn'];
		
		if ($strClosedOn < $strCreatedOn)
		{
			$strCreatedOn	= OutputMask()->LongDateAndTime($strCreatedOn);
			$strClosedOn	= OutputMask()->LongDateAndTime($strClosedOn);
			//TODO! I think this will have to be changed to check into the nature of closure
			$this->_strErrorMsg = "This service cannot be activated as its CreatedOn TimeStamp ($strCreatedOn) is greater than its ClosedOn TimeStamp ($strClosedOn) signifying that it was never actually used by this account";
			return FALSE;
		}
		
		// Check if the FNN is currently in use
		$arrWhere					= Array();
		$arrWhere['FNN']			= ($bolIsIndial) ? substr($strFNN, 0, -2) . "__" : $strFNN; 
		$arrWhere['IndialRange']	= substr($strFNN, 0, -2) . "__";
		$arrWhere['ClosedOn']		= $strClosedOn;
		
		$selFNNInUse = new StatementSelect("Service", "Id", "(FNN LIKE <FNN> OR (FNN LIKE <IndialRange> AND Indial100 = 1)) AND (ClosedOn IS NULL OR (ClosedOn >= CreatedOn AND NOW() <= ClosedOn))");
		if ($selFNNInUse->Execute($arrWhere))
		{
			// At least one record was returned, which means the FNN is currently in use by an active service
			if ($bolIsIndial)
			{
				$this->_strErrorMsg = "Cannot activate this service as at least one of the FNNs in the Indial Range is currently being used by another service.  The other service must be disconnected or archived before this service can be activated.";
			}
			$this->_strErrorMsg = "Cannot activate this service as the FNN: $strFNN is currently being used by another service.  The other service must be disconnected or archived before this service can be activated";
			return FALSE;
		}
		
		// If the Service hasn't closed yet, then just update the ClosedOn and Status properties
		// You do not need to create a new record, or renormalise CDRs
		if ($strClosedOn >= $strTimeStamp)
		{
			// Just update the record
			$arrUpdate = Array	(	"Id"				=> $intService,
									"ClosedOn"			=> NULL,
									"Status"			=> SERVICE_ACTIVE,
									"NatureOfClosure"	=> NULL
								);
			$updService = new StatementUpdateById("Service", $arrUpdate);
			if ($updService->Execute($arrUpdate) === FALSE)
			{
				// There was an error while trying to activate the service
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to activate the Service with Id '{$this->_intCurrentId}'";
				return FALSE;
			}
			
			// Update the corresponding record in the _arrServiceRecords array
			$this->_arrServiceRecords[0]['ClosedOn']		= NULL;
			$this->_arrServiceRecords[0]['Status']			= SERVICE_ACTIVE;
			$this->_arrServiceRecords[0]['NatureOfClosure']	= NULL;
			
			// Service was activated successfully
			return TRUE;
		}
		
		// Activating the service requires the creation of a new service record, based on the old service record
		$intOldServiceId	= $intService;
		$intUserId			= AuthenticatedUser()->_arrUser['Id'];
		
		$arrServiceRecordData = Array(	"FNN"						=> $this->_strFNN,
										"ServiceType"				=> $this->_intServiceType,
										"Indial100"					=> $this->_bolIndial100,
										"AccountGroup"				=> $this->_intAccountGroup,
										"Account"					=> $this->_intAccount,
										"CostCentre"				=> $this->_intCostCentre,
										"CappedCharge"				=> $this->_arrServiceRecords[0]['CappedCharge'],
										"UncappedCharge"			=> $this->_arrServiceRecords[0]['UncappedCharge'],
										"CreatedOn"					=> $strTimeStamp,
										"CreatedBy"					=> $intUserId,
										"NatureOfCreation"			=> SERVICE_CREATION_ACTIVATED,
										"ClosedOn"					=> NULL,
										"ClosedBy"					=> NULL,
										"NatureOfClosure"			=> NULL,
										"Carrier"					=> $this->_arrServiceRecords[0]['Carrier'],
										"CarrierPreselect"			=> $this->_arrServiceRecords[0]['CarrierPreselect'],
										"EarliestCDR"				=> NULL,
										"LatestCDR"					=> NULL,
										"LineStatus"				=> NULL,
										"LineStatusDate"			=> NULL,
										"PreselectionStatus"		=> NULL,
										"PreselectionStatusDate"	=> NULL,
										"ForceInvoiceRender"		=> $this->_bolForceInvoiceRender,
										"LastOwner"					=> $intOldServiceId,
										"NextOwner"					=> NULL,
										"Status"					=> SERVICE_ACTIVE
										);
		
		$insService	= new StatementInsert("Service", $arrServiceRecordData);
		$mixResult	= $insService->Execute($arrServiceRecordData);
		if ($mixResult === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert a new record into the Service table";
			return FALSE;
		}
		
		// Store the Id of the new Service Record, as the Current Id
		$this->_intCurrentId = $mixResult;

		// Update the Service.NextOwner property of the last Service record
		$arrUpdate	= Array("Id" => $intOldServiceId, "NextOwner" => $this->_intCurrentId);
		$updService	= new StatementUpdateById("Service", $arrUpdate);
		if ($updService->Execute($arrUpdate) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to update the NextOwner property of the last Service record";
			return FALSE;
		} 
		
		// Activating the service was successfull
		return TRUE;
	}
	
	// Deactivates the service (Disconnected or Archived)
	// This can create a new Service record, if the status is being changed between Disconnected and Archived
	// It will copy across plan details
	protected function _Deactivate($intStatus, $strTimeStamp)
	{
		$intService		= $this->_intCurrentId;
		
		// The most recent values for CreatedOn and ClosedOn
		$strCreatedOn	= $this->_arrServiceRecords[0]['CreatedOn'];
		$strClosedOn	= $this->_arrServiceRecords[0]['ClosedOn'];

		// Work out the nature of the closure
		$intNatureOfClosure	= ($intStatus == SERVICE_DISCONNECTED)? SERVICE_CLOSURE_DISCONNECTED : SERVICE_CLOSURE_ARCHIVED;
		$intUserId			= AuthenticatedUser()->_arrUser['Id'];

		if ($strClosedOn === NULL)
		{
			// A ClosedOn TimeStamp has not been set for the current Service record
			// Just update the record
			$arrUpdate = Array	(	"Id"				=> $intService,
									"ClosedOn"			=> $strTimeStamp,
									"ClosedBy"			=> $intUserId,
									"Status"			=> $intStatus,
									"NatureOfClosure"	=> $intNatureOfClosure
								);
			$updService = new StatementUpdateById("Service", $arrUpdate);
			if ($updService->Execute($arrUpdate) === FALSE)
			{
				// There was an error while trying to activate the service
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to ". (($intStatus == SERVICE_DISCONNECTED)? "Disconnect" : "Archive") ." the Service with Id '{$this->_intCurrentId}'";
				return FALSE;
			}
			
			// Update the corresponding record in the _arrServiceRecords array
			$this->_arrServiceRecords[0]['ClosedOn']		= $strTimeStamp;
			$this->_arrServiceRecords[0]['ClosedBy']		= $intUserId;
			$this->_arrServiceRecords[0]['Status']			= $intStatus;
			$this->_arrServiceRecords[0]['NatureOfClosure']	= $intNatureOfClosure;
			
			// Service was deactivated successfully
			return TRUE;
		}
		
		if ($strClosedOn < $strCreatedOn)
		{
			$strCreatedOn	= OutputMask()->LongDateAndTime($strCreatedOn);
			$strClosedOn	= OutputMask()->LongDateAndTime($strClosedOn);
			//TODO! I think this will have to be changed to check into the nature of closure
			$this->_strErrorMsg = "This service cannot be ". GetConstantDescription($intStatus, "Service") ." as its CreatedOn TimeStamp ($strCreatedOn) is greater than its ClosedOn TimeStamp ($strClosedOn) signifying that it was never actually used by this account";
			return FALSE;
		}
		
		// In order to "deactivate" this service, a new Service Record must be added
		// In which CreatedOn and ClosedOn == NOW()
		$intOldServiceId = $intService;
		$arrServiceRecordData = Array(	"FNN"						=> $this->_strFNN,
										"ServiceType"				=> $this->_intServiceType,
										"Indial100"					=> $this->_bolIndial100,
										"AccountGroup"				=> $this->_intAccountGroup,
										"Account"					=> $this->_intAccount,
										"CostCentre"				=> $this->_intCostCentre,
										"CappedCharge"				=> $this->_arrServiceRecords[0]['CappedCharge'],
										"UncappedCharge"			=> $this->_arrServiceRecords[0]['UncappedCharge'],
										"CreatedOn"					=> $strTimeStamp,
										"CreatedBy"					=> $intUserId,
										"NatureOfCreation"			=> SERVICE_CREATION_STATUS_CHANGED,
										"ClosedOn"					=> $strTimeStamp,
										"ClosedBy"					=> $intUserId,
										"NatureOfClosure"			=> $intNatureOfClosure,
										"Carrier"					=> $this->_arrServiceRecords[0]['Carrier'],
										"CarrierPreselect"			=> $this->_arrServiceRecords[0]['CarrierPreselect'],
										"EarliestCDR"				=> NULL,
										"LatestCDR"					=> NULL,
										"LineStatus"				=> NULL,
										"LineStatusDate"			=> NULL,
										"PreselectionStatus"		=> NULL,
										"PreselectionStatusDate"	=> NULL,
										"ForceInvoiceRender"		=> $this->_bolForceInvoiceRender,
										"LastOwner"					=> $intOldServiceId,
										"NextOwner"					=> NULL,
										"Status"					=> $intStatus
										);
		
		$insService	= new StatementInsert("Service", $arrServiceRecordData);
		$mixResult	= $insService->Execute($arrServiceRecordData);
		if ($mixResult === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert a new record into the Service table";
			return FALSE;
		}
		
		// Store the Id of the new Service Record, as the Current Id
		$this->_intCurrentId = $mixResult;

		// Update the Service.NextOwner property of the last Service record
		$arrUpdate	= Array("Id" => $intOldServiceId, "NextOwner" => $this->_intCurrentId);
		$updService	= new StatementUpdateById("Service", $arrUpdate);
		if ($updService->Execute($arrUpdate) === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to update the NextOwner property of the last Service record";
			return FALSE;
		} 

		// Deactivating the service was successfull
		return TRUE;
		
	}
	
/******************************************************************************/
// Change Of Lessee related Methods
/******************************************************************************/
	
	function HasServiceMoveScheduled()
	{
		//TODO!
		$this->_strErrorMsg = "HasServiceMoveScheduled() functionality has not been implemented yet";
		return FALSE;
	}
	
/******************************************************************************/
// Static Methods
/******************************************************************************/
	
	// Returns a service object modelling a new service
	// (factory)
	static function CreateNewService($strFNN, $intAccount, $strEffectiveFromDatetime, $bolIsIndial=NULL)
	{
		// TODO! Validate the FNN and work out what ServiceType it is for
		// TODO! Check that the FNN is available from $strEffectiveFromDatetime
		
		// Insert the Service Record and then build a ServiceObject of the correct derived type, passing
		// an array of defined details to it
		
		// Return an object of the appropriate derived class (ModuleLandLine, ModuleMobile, etc)
	}
	
	// Performs a change of lessee, or Service Move
	// This should probably be done at the Account Object Level, but I don't think it's too ilogical for a service
	// to manage the history of who owns it
	static function MoveToAccount($intServiceId, $intAccountId, $bolIsLesseeChange)
	{
		//TODO!
		throw new Exception("MoveToAccount() functionality has not been implemented yet");
	}
	
}

//----------------------------------------------------------------------------//
// ModuleLandLine
//----------------------------------------------------------------------------//
/**
 * ModuleLandLine
 *
 * Models a LandLine service that is currently defined in the database
 *
 * Models a LandLine service that is currently defined in the database
 *
 * @package	ui_app
 * @class	ModuleLandLine
 * @extends	ModuleService
 */
class ModuleLandLine extends ModuleService
{
	// Stores the address associated with this service
	private $_arrAddress		= NULL;
	
	// Used to keep track of whether or not the Service's address has been loaded
	private $_bolAddressLoaded	= NULL;
	
	// TRUE if the LandLine service has Extension Level Billing turned on
	// FALSE if it is not turned on
	private	$_bolELB			= NULL;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the ModuleLandLine class 
	 * 
	 * Constructor for the ModuleLandLine class
	 * 
	 * @param	int		$intService		Id of the LandLine service that this object will model
	 * 									PRE: Id must reference a valid landline service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @throws	Exception on error or if $intService doesn't reference a LandLine service in the database
	 *
	 * @return	void
	 * @method
	 */
	function __construct($intService)
	{
		parent::__construct($intService);
		
		if ($this->_intServiceType != SERVICE_TYPE_LAND_LINE)
		{
			throw new Exception("Service with Id '$intService' is not a landline service");
		}
	}
	
	//------------------------------------------------------------------------//
	// _LoadDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadDetails()
	 *
	 * Loads the details for the LandLine 
	 * 
	 * Loads the details for the LandLine
	 * Loads the basic Service Record details, and works out if ELB is turned on if the service is an Indial100
	 * It does not load the Service's address details
	 * 
	 * @param	int		$intService		Id of the LandLine service that this object will model
	 * 									PRE: Id must reference a valid landline service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	protected function _LoadDetails($intService)
	{
		if (parent::_LoadDetails($intService) === FALSE)
		{
			return FALSE;
		}
		
		// Check if Extension Level Billing is turned on or off
		// ELB is considered to be turned on if the Service is an Indial 100 and has records in the ServiceExtension table
		if ($this->_bolIndial100)
		{
			$selELB		= new StatementSelect("ServiceExtension", "Id", "Service = <ServiceId>", "Id", "1");
			$mixResult	= $selELB->Execute(Array("ServiceId" => $this->_intCurrentId));
			
			if ($mixResult === FALSE)
			{
				// Database error occured
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to work out if the service '{$this->_intCurrentId}' has Extension Level Billing";
				return FALSE;
			}
			$this->_bolELB = ($mixResult == 1)? TRUE : FALSE;
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// SetStatus
	//------------------------------------------------------------------------//
	/**
	 * SetStatus()
	 *
	 * Changes the status of the Landline (active/disconnected/archived) 
	 * 
	 * Changes the status of the Landline (active/disconnected/archived)
	 * Before processing the StatusChange it will run SaveService() if there are 
	 * any currently unsaved changes made to the service.
	 * If a new Service record is required to model the change of status, then a new ServiceAddress record
	 * will also be made (if the service currently has a ServiceAddress) and new records will be added to the
	 * ServiceExtension table if ELB is turned on.  All these new details will be reloaded in the object, if it is
	 * necessary
	 * 
	 * @param	int		$intStatus		The new Service Status to set the service to (SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	optional, TimeStamp at which the Status Change will be recorded as having been made
	 * 									This should not be in the past.  Defaults to NOW() 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function SetStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;
		
		if (parent::SetStatus($intStatus, $strTimeStamp) === FALSE)
		{
			return FALSE;
		}
		
		// Check if a new Service Record was made
		if ($this->_intServiceId != $this->_arrServiceRecords[0]['ServiceId'])
		{
			// Make a new ServiceAddress record
			$intNewServiceId	= $this->_intCurrentId;
			$intOldServiceId	= $this->_arrServiceRecords[0]['ServiceId'];
			
			// Retrieve the old ServiceAddress record, if one exists
			$selAddress	= new StatementSelect("ServiceAddress", "*", "Service = <OldServiceId>", "Id DESC", "1");
			$mixResult	= $selAddress->Execute(Array("OldServiceId"=>$intOldServiceId));
			if ($mixResult === FALSE)
			{
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve a record from the ServiceAddress table";
				return FALSE;
			}
			if ($mixResult == 1)
			{
				// A ServiceAddress record exists, insert it as a new record referencing the new Service Id
				$arrServiceAddress				= $selAddress->Fetch();
				$arrServiceAddress['Service']	= $intNewServiceId;
				$arrServiceAddress['Id']		= NULL;
				
				$insAddress = new StatementInsert("ServiceAddress", $arrServiceAddress);
				if ($insAddress->Execute($arrServiceAddress) === FALSE)
				{
					$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert a record into the ServiceAddress table";
					return FALSE;
				}
			}
			
			// Make new ServiceExtension records if ELB is turned on
			if ($this->_bolELB)
			{
				if (!EnableELB($intNewServiceId))
				{
					$this->_strErrorMsg = "Updating Extension Level Billing details failed";
					return FALSE;
				}
			}
			
			// Refresh the object			
			return $this->Refresh();
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Refresh
	//------------------------------------------------------------------------//
	/**
	 * Refresh()
	 *
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested) 
	 * 
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested)
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function Refresh()
	{
		if (parent::Refresh() === FALSE)
		{
			return FALSE;
		}
		
		// Reload the ServiceAddress record, if it has previously been loaded
		if ($this->_bolAddressLoaded)
		{
			$mixResult = GetAddress(TRUE);
			return (bool)($mixResult !== FALSE);
		}
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// GetAddress
	//------------------------------------------------------------------------//
	/**
	 * GetAddress()
	 *
	 * Retrieves the LandLine's current ServiceAddress record, if there is one 
	 * 
	 * Retrieves the LandLine's current ServiceAddress record, if there is one
	 *
	 * @param	bool	$bolForceRefresh	optional, defaults to FALSE.  If TRUE then the ServiceAddress
	 * 										record will be retrieved from the database.  If FALSE then
	 * 										the method will only retrieve the Address from the database, if it
	 * 										isn't already being stored in the object, from a previous call to this method
	 *
	 * @return	mixed						FALSE	: Error occurred
	 * 										NULL	: The service doesn't have an associated ServiceAddress record
	 * 										Array	: The ServiceAddress record
	 * @method
	 */
	function GetAddress($bolForceRefresh=FALSE)
	{
		if ($this->_bolAddressLoaded && !$bolForceRefresh)
		{
			// The address has already been loaded, and we are not forcing a refresh
			return $this->_arrAddress;
		}
		
		$selAddress	= new StatementSelect("ServiceAddress", "*", "Service = <ServiceId>", "Id DESC", "1");
		$mixResult	= $selAddress->Execute(Array("ServiceId" => $this->_intCurrentId));
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve the ServiceAddress record";
			return FALSE;
		}
		if ($mixResult == 0)
		{
			// The service doesn't have a ServiceAddress record
			return NULL;
		}
		
		// Save the address record
		$this->_arrAddress			= $selAddress->Fetch();
		$this->_bolAddressLoaded	= TRUE;
		return $this->_arrAddress;
	}
	
	
	//------------------------------------------------------------------------//
	// IsIndial100
	//------------------------------------------------------------------------//
	/**
	 * IsIndial100()
	 *
	 * Returns TRUE if the LandLine is an Indial100, ELSE FALSE 
	 * 
	 * Returns TRUE if the LandLine is an Indial100, ELSE FALSE
	 *
	 *
	 * @return	bool	TRUE if the LandLine is an Indial100, ELSE FALSE
	 * @method
	 */
	function IsIndial100()
	{
		return (bool)$this->_bolIndial100;
	}
}

//----------------------------------------------------------------------------//
// ModuleMobile
//----------------------------------------------------------------------------//
/**
 * ModuleMobile
 *
 * Models a Mobile service that is currently defined in the database
 *
 * Models a Mobile service that is currently defined in the database
 *
 * @package	ui_app
 * @class	ModuleMobile
 * @extends	ModuleService
 */
class ModuleMobile extends ModuleService
{
	// While there is a ModuleService->_bolHasUnsavedChanges property, I should work out when I need 
	// to save this details, and when I don't
	// If this is NULL and $_bolExtraDetailsLoaded == TRUE, then one can conclude that the Service doesn't even have
	// a ServiceMobileDetail record associated with it
	private $_arrExtraDetails		= NULL;
	
	// Used to keep track of whether or not the Mobile's extra details have been loaded from the database
	private $_bolExtraDetailsLoaded	= NULL;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the ModuleMobile class 
	 * 
	 * Constructor for the ModuleMobile class
	 * 
	 * @param	int		$intService		Id of the Mobile service that this object will model
	 * 									PRE: Id must reference a valid Mobile service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @throws	Exception on error or if $intService doesn't reference a Mobile service in the database
	 *
	 * @return	void
	 * @method
	 */
	function __construct($intService)
	{
		parent::__construct($intService);
		
		if ($this->_intServiceType != SERVICE_TYPE_MOBILE)
		{
			throw new Exception("Service with Id '$intService' is not a mobile service");
		}
	}
	
	//------------------------------------------------------------------------//
	// _LoadDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadDetails()
	 *
	 * Loads the details for the Mobile 
	 * 
	 * Loads the details for the Mobile
	 * Loads the basic Service Record details
	 * It does not load the Service's extra details (ServiceMobileDetail record)
	 * 
	 * @param	int		$intService		Id of the Mobile service that this object will model
	 * 									PRE: Id must reference a valid mobile service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	protected function _LoadDetails($intService)
	{
		return parent::_LoadDetails($intService);
	}
	
	// TODO! implement this properly
	function SaveService()
	{
		parent::SaveService();
		
		// Save the Mobile Extra details (this could involve updating an existing record, or inserting a new one)
		//TODO!
		
		$this->_bolHasUnsavedChanges = TRUE;
	}
	
	//------------------------------------------------------------------------//
	// SetStatus
	//------------------------------------------------------------------------//
	/**
	 * SetStatus()
	 *
	 * Changes the status of the Mobile (active/disconnected/archived) 
	 * 
	 * Changes the status of the Mobile (active/disconnected/archived)
	 * Before processing the StatusChange it will run SaveService() if there are 
	 * any currently unsaved changes made to the service.
	 * If a new Service record is required to model the change of status, then a new ServiceMobileDetail record
	 * will also be made.  These new details will be reloaded in the object, if it is necessary
	 * The plan details will also be copied across
	 * 
	 * @param	int		$intStatus		The new Service Status to set the service to (SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	optional, TimeStamp at which the Status Change will be recorded as having been made
	 * 									This should not be in the past.  Defaults to NOW() 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function SetStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;
		
		if (parent::SetStatus($intStatus, $strTimeStamp) === FALSE)
		{
			return FALSE;
		}
		
		// Check if a new Service Record was made
		if ($this->_intServiceId != $this->_arrServiceRecords[0]['ServiceId'])
		{
			// Make a new ServiceMobileDetail record
			$intNewServiceId	= $this->_intCurrentId;
			$intOldServiceId	= $this->_arrServiceRecords[0]['ServiceId'];
			
			// Retrieve the old ServiceMobileDetail record, if one exists
			$selExtraDetail	= new StatementSelect("ServiceMobileDetail", "*", "Service = <OldServiceId>", "Id DESC", "1");
			$mixResult		= $selExtraDetail->Execute(Array("OldServiceId"=>$intOldServiceId));
			if ($mixResult === FALSE)
			{
				$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve a record from the ServiceMobileDetail table";
				return FALSE;
			}
			if ($mixResult == 1)
			{
				// A ServiceMobileDetail record exists, insert it as a new record referencing the new Service Id
				$arrExtraDetail				= $selExtraDetail->Fetch();
				$arrExtraDetail['Service']	= $intNewServiceId;
				$arrExtraDetail['Id']		= NULL;
				
				$insExtraDetail = new StatementInsert("ServiceMobileDetail", $arrExtraDetail);
				if ($insExtraDetail->Execute($arrExtraDetail) === FALSE)
				{
					$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert a record into the ServiceMobileDetail table";
					return FALSE;
				}
			}
			
			// Refresh the object			
			return $this->Refresh();
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Refresh
	//------------------------------------------------------------------------//
	/**
	 * Refresh()
	 *
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested) 
	 * 
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested)
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function Refresh()
	{
		if (parent::Refresh() === FALSE)
		{
			return FALSE;
		}
		
		// Reload the ServiceMobileDetail record, if it has previously been loaded
		if ($this->_bolExtraDetailsLoaded)
		{
			$mixResult = GetMobileSpecificDetails(TRUE);
			return (bool)($mixResult !== FALSE);
		}
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// GetMobileSpecificDetails
	//------------------------------------------------------------------------//
	/**
	 * GetMobileSpecificDetails()
	 *
	 * Retrieves the Mobile's current ServiceMobileDetail record, if there is one 
	 * 
	 * Retrieves the Mobile's current ServiceMobileDetail record, if there is one
	 *
	 * @param	bool	$bolForceRefresh	optional, defaults to FALSE.  If TRUE then the ServiceMobileDetail
	 * 										record will be retrieved from the database.  If FALSE then
	 * 										the method will only access the database, if it
	 * 										isn't already being stored in the object, from a previous call to this method
	 *
	 * @return	mixed						FALSE	: Error occurred
	 * 										NULL	: The service doesn't have an associated ServiceMobileDetail record
	 * 										Array	: The ServiceMobileDetail record
	 * @method
	 */
	function GetMobileSpecificDetails($bolForceRefresh=FALSE)
	{
		if ($this->_bolExtraDetailsLoaded && !$bolForceRefresh)
		{
			// The details have already been loaded, and we are not forcing a refresh
			return $this->_arrExtraDetails;
		}
		
		$selExtraDetails	= new StatementSelect("ServiceMobileDetail", "*", "Service = <ServiceId>", "Id DESC", "1");
		$mixResult			= $selExtraDetails->Execute(Array("ServiceId" => $this->_intCurrentId));
		if ($mixResult === FALSE)
		{
			// Database error occured
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to retrieve the ServiceMobileDetail record";
			return FALSE;
		}
		if ($mixResult == 0)
		{
			// The service doesn't have a ServiceMobileDetail record
			return NULL;
		}
		
		// Save the extra details
		$this->_arrExtraDetails			= $selExtraDetails->Fetch();
		$this->_bolExtraDetailsLoaded	= TRUE;
		return $this->_arrExtraDetails;
	}
}

//----------------------------------------------------------------------------//
// ModuleADSL
//----------------------------------------------------------------------------//
/**
 * ModuleADSL
 *
 * Models an ADSL service that is currently defined in the database
 *
 * Models an ADSL service that is currently defined in the database
 *
 * @package	ui_app
 * @class	ModuleADSL
 * @extends	ModuleService
 */
class ModuleADSL extends ModuleService
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the ModuleADSL class 
	 * 
	 * Constructor for the ModuleADSL class
	 * 
	 * @param	int		$intService		Id of the ADSL service that this object will model
	 * 									PRE: Id must reference a valid ADSL service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @throws	Exception on error or if $intService doesn't reference an ADSL service in the database
	 *
	 * @return	void
	 * @method
	 */
	function __construct($intService)
	{
		parent::__construct($intService);
		
		if ($this->_intServiceType != SERVICE_TYPE_ADSL)
		{
			throw new Exception("Service with Id '$intService' is not an ADSL service");
		}
	}
	
	//------------------------------------------------------------------------//
	// _LoadDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadDetails()
	 *
	 * Loads the details for the ADSL Service 
	 * 
	 * Loads the details for the ADSL Service
	 * Loads the basic Service Record details
	 * 
	 * @param	int		$intService		Id of the ADSL service that this object will model
	 * 									PRE: Id must reference a valid ASDSL service in the Service table of the database.
	 * 									It does not have to reference the most recently added Service record modelling this
	 * 									FNN on this Account, but the object will logically model the use of this service
	 * 									record's FNN on this service record's Account
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	protected function _LoadDetails($intService)
	{
		return parent::_LoadDetails($intService);
	}
	
	//------------------------------------------------------------------------//
	// SetStatus
	//------------------------------------------------------------------------//
	/**
	 * SetStatus()
	 *
	 * Changes the status of the ADSL (active/disconnected/archived) 
	 * 
	 * Changes the status of the ADSL (active/disconnected/archived)
	 * Before processing the StatusChange it will run SaveService() if there are 
	 * any currently unsaved changes made to the service.
	 * If a new Service record is required to model the change of status, then plan details will also be copied across
	 * 
	 * @param	int		$intStatus		The new Service Status to set the service to (SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	optional, TimeStamp at which the Status Change will be recorded as having been made
	 * 									This should not be in the past.  Defaults to NOW() 
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function SetStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;
		
		if (parent::SetStatus($intStatus, $strTimeStamp) === FALSE)
		{
			return FALSE;
		}
		
		// Check if a new Service Record was made
		if ($this->_intServiceId != $this->_arrServiceRecords[0]['ServiceId'])
		{
			// Refresh the object
			return $this->Refresh();
		}
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Refresh
	//------------------------------------------------------------------------//
	/**
	 * Refresh()
	 *
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested) 
	 * 
	 * Refreshes the loaded details of the service (effectively reloads the service, and any extra details if they have previously been requested)
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	function Refresh()
	{
		if (parent::Refresh() === FALSE)
		{
			return FALSE;
		}
		
		// There are no extra details to load for ADSL services
		
		return TRUE;
	}
}

class ModuleInbound extends ModuleService
{
}

?>
