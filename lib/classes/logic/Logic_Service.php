<?php

/**
 * Description of Account_Logic
 *
 * @author JanVanDerBreggen
 */
abstract class Logic_Service implements DataLogic
{
    protected $oDO;
   

    
   public function __construct($mDefinition)
    {
        $this->oDO = is_numeric($mDefinition) ? Service::getForId($mDefinition) : (get_class($mDefinition) == 'Service' ? $mDefinition : null);
    }
    
     //------------------------------------------------------------------------//
	// ChangeStatus
	//------------------------------------------------------------------------//
	/**
	 * ChangeStatus()
	 *
	 * The logic of this method is modelled on ModuleService::ChangeStatus()
         * It is up to the caller of this method to make sure that the correct Service record for the service is used to perform this operation on
	 * @method
	 */
	function changeStatus($intStatus, $strTimeStamp=NULL)
	{
		$strTimeStamp = ($strTimeStamp == NULL)? GetCurrentISODateTime() : $strTimeStamp;



		// Check that the Service has come into effect
		if ($this->CreatedOn > $strTimeStamp)
		{
			// The service hasn't even come into effect yet
			$this->_strErrorMsg = "The service hasn't even come into effect on this account yet.  Its status cannot be changed";
			return FALSE;
		}

		// Check that the Account is of an appropriate Status
		$selAccount = new StatementSelect("Account", "Archived", "Id = <AccountId>");
		if (!$selAccount->Execute(array("AccountId" => $this->Account)))
		{
			// Could not find the account
			$this->_strErrorMsg = "Unexpected database error occurred when trying to retrieve details of the account that this service belongs to";
			return FALSE;
		}
		$arrAccount = $selAccount->Fetch();
		if ($arrAccount['Archived'] == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			// The service's Account is pending activation, which means you can't change the status of any of the services belonging to it
			$this->_strErrorMsg = "The status cannot be changed while the Account is pending activation";
			return FALSE;
		}

		if ($intStatus == SERVICE_ACTIVE)
		{
                    throw new Exception("Service Activation is Currently not yet implemented here. Please refer to the old ModuleService classes for this");
//			// The service is being activated
//			if ($this->_Activate($strTimeStamp) === FALSE)
//			{
//				return FALSE;
//			}
		}
		elseif ($intStatus == SERVICE_DISCONNECTED || $intStatus == SERVICE_ARCHIVED)
		{
			// The service is being deactivated (disconnected or archived)
                        $mResult = $this->_Deactivate($intStatus, $strTimeStamp);
			if ($mResult === FALSE)
			{
				return FALSE;
			}
		}
		else
		{
			// Invalid Status to change to
			$this->_strErrorMsg = "Can not change the status of the service to ". GetConstantDescription($intStatus, "service_status");
			return FALSE;
		}

		// If a new Service record was made, then we have to make a copy the Plan details which references it
		// and we also have to make a copy of the ServiceType specific details which references it
		if (is_numeric($mResult) && $this->Id != $mResult )
		{
			// Copy the Plan Details
			if ($this->_CopyPlanDetails($mResult, $strTimeStamp) === FALSE)
			{
				return FALSE;
			}

			// Copy the ServiceType specific details
			if ($this->_CopySupplementaryDetails($mResult, $this->Account, $this->AccountGroup) === FALSE)
			{
				return FALSE;
			}

			
		}

		return $mResult;
	}

	public static function canServiceBeAutomaticallyBarred($iServiceId, $iBarringLevelId, $sEffectiveDateTime=null)
	{
		$sEffectiveDate	= ($sEffectiveDate === null ? DataAccess::getDataAccess()->getNow() : $sEffectiveDate);
		
		$oQuery	= new Query();
		$sQuery = "	SELECT      CASE
					                WHEN 	{$iBarringLevelId} = ".BARRING_LEVEL_UNRESTRICTED."
					                THEN	(
							                    SELECT  status_id
							                    FROM    carrier_provisioning_support
							                    WHERE   carrier_id = c_pre.Id
							                    AND     provisioning_type_id = ".PROVISIONING_TYPE_UNBAR."
							                )
					                WHEN 	{$iBarringLevelId} = ".BARRING_LEVEL_BARRED."
					                THEN 	(
							                    SELECT  status_id
							                    FROM    carrier_provisioning_support
							                    WHERE   carrier_id = c_pre.Id
							                    AND     provisioning_type_id = ".PROVISIONING_TYPE_BAR."
							                )
					                WHEN 	{$iBarringLevelId} = ".BARRING_LEVEL_TEMPORARY_DISCONNECTION."
									THEN	(
							                    SELECT  status_id
							                    FROM    carrier_provisioning_support
							                    WHERE   carrier_id = c_full.Id
							                    AND     provisioning_type_id = ".PROVISIONING_TYPE_DISCONNECT_TEMPORARY."
							                )
					            END AS active_status_id
					FROM        Service s
					JOIN   	    service_type st ON (st.id = s.ServiceType)
					JOIN   	    ServiceRatePlan srp ON (
					                srp.Service = s.Id
					                AND NOW() BETWEEN srp.StartDatetime AND srp.EndDatetime
					            )
					JOIN   	    RatePlan rp ON (rp.Id = srp.RatePlan)
					JOIN   	    Carrier c_pre ON (c_pre.Id = rp.CarrierPreselection)
					JOIN   	    Carrier c_full ON (c_full.Id = rp.CarrierFullService)
					WHERE		s.Id = {$iServiceId}";
		$mResult = $oQuery->Execute($sQuery);
		if ($mResult === false)
		{
			throw new Exception_Database("Failed to get active_status for carrier_provisioning_support for service {$iServiceId}. ".$oQuery->Error());
		}
		
		$aRow = $mResult->fetch_assoc();
		return ($aRow['active_status_id'] == ACTIVE_STATUS_ACTIVE);
	}
	
	public static function createProvisioningRequest($iServiceId, $iProvisioningTypeId, $sAuthorisationDate=null, $iEmployeeId=null)
	{
		$oQuery	= new Query();
		$sQuery = "	SELECT      if(
					                st.const_name = 'SERVICE_TYPE_LAND_LINE',
					                if(
					                    {$iProvisioningTypeId} = ".PROVISIONING_TYPE_BAR.",
					                    c_pre.Id,
					                    c_full.Id
					                ),
					                c_full.Id
					            ) AS service_carrier_id,
								a.Id AS account_id,
								a.AccountGroup AS account_group_id,
								a.CustomerGroup AS account_customer_group_id,
								s.FNN AS service_fnn
					FROM        Service s
					JOIN		Account a ON (a.Id = s.Account)
					JOIN   	    service_type st ON (st.id = s.ServiceType)
					JOIN   	    ServiceRatePlan srp ON (
					                srp.Service = s.Id
					                AND NOW() BETWEEN srp.StartDatetime AND srp.EndDatetime
					            )
					JOIN   	    RatePlan rp ON (rp.Id = srp.RatePlan)
					JOIN   	    Carrier c_pre ON (c_pre.Id = rp.CarrierPreselection)
					JOIN   	    Carrier c_full ON (c_full.Id = rp.CarrierFullService)
					WHERE		s.Id = {$iServiceId}";
		$mResult = $oQuery->Execute($sQuery);
		if ($mResult === false)
		{
			throw new Exception_Database("Failed to get service details for service {$iServiceId}. ".$oQuery->Error());
		}
		
		$aDetailsRow = $mResult->fetch_assoc();
		
		// Create the provisioning request record
		$oProvisioningRequest 						= new Provisioning_Request();
		$oProvisioningRequest->AccountGroup 		= $aDetailsRow['account_group_id'];
		$oProvisioningRequest->Account 				= $aDetailsRow['account_id'];
		$oProvisioningRequest->Service				= $iServiceId;
		$oProvisioningRequest->FNN 					= $aDetailsRow['service_fnn'];
		$oProvisioningRequest->Employee 			= ($iEmployeeId === null ? Employee::SYSTEM_EMPLOYEE_ID : $iEmployeeId);
		$oProvisioningRequest->Carrier 				= $aDetailsRow['service_carrier_id'];
		$oProvisioningRequest->Type 				= $iProvisioningTypeId;
		$oProvisioningRequest->RequestedOn 			= DataAccess::getDataAccess()->getNow();
		$oProvisioningRequest->AuthorisationDate	= ($sAuthorisationDate === null ? date('Y-m-d', DataAccess::getDataAccess()->getNow(true)) : $sAuthorisationDate);
		$oProvisioningRequest->scheduled_datetime	= $oProvisioningRequest->RequestedOn;
		$oProvisioningRequest->Status 				= REQUEST_STATUS_WAITING;
		$oProvisioningRequest->customer_group_id 	= $aDetailsRow['account_customer_group_id'];
		$oProvisioningRequest->save();
	}

    //------------------------------------------------------------------------//
	// _Deactivate
	//------------------------------------------------------------------------//
	/**
	 * _Deactivate()
	 *
	 * Deactivate the service (Sets status to DISCONNECTED or ARCHIVED)
	 *
	 * Deactivate the service (Sets status to DISCONNECTED or ARCHIVED)
	 * This will create a new Service record, if the status is being changed between Disconnected and Archived
	 * It will not copy across unbilled CDRs, Charges, recurring charges or the plan details
	 *
	 * @param	int		$intStatus		Status to change the service to (SERVICE_DISCONNECTED or SERVICE_ARCHIVED)
	 * @param	string	$strTimeStamp	TimeStamp at which the Activation will be recorded as having been made.
	 * 									This should not be in the past
	 *
	 * @return	bool					TRUE on success, FALSE on failure
	 * @method
	 */
	private function _Deactivate($intStatus, $strTimeStamp)
	{
		$intService		= $this->Id;

		// The most recent values for CreatedOn and ClosedOn
		$strCreatedOn	= $this->CreatedOn;
		$strClosedOn	= $this->ClosedOn;

		if ($this->Status == SERVICE_PENDING)
		{
			// You can only deactivate a service that is pending activation, if the service relates to a cancelled sale
			$objFlexSaleItem = FlexSaleItem::getForServiceId($intService, TRUE);
			if (Data_Source::dsnExists(FLEX_DATABASE_CONNECTION_SALES) && $objFlexSaleItem !== NULL)
			{
				// The service originated from a sale in the Sales Portal
				try
				{
					$doSaleItem	= $objFlexSaleItem->getExternalReferenceObject();

					if ($doSaleItem->saleItemStatusId != DO_Sales_SaleItemStatus::CANCELLED)
					{
						// The corresponding sale item has not been cancelled
						$this->_strErrorMsg = "Cannot deactivate a service that is pending activation, until the corresponding sale item is cancelled";
						return FALSE;
					}
					else
					{
						// The sale item is cancelled, so deactivating the service is ok
					}
				}
				catch (Exception $e)
				{
					$this->_strErrorMsg = "Failed to retrieve sale information relating to this service - ". $e->getMessage();
					return FALSE;
				}
			}
			else
			{
				// You cannot deactivate a service that is pending activation, unless it directly relates to a sale_item that has been cancelled
				$this->_strErrorMsg = "Cannot deactivate a service that is pending activation";
				return FALSE;
			}
		}

		// Work out the nature of the closure
		$intNatureOfClosure	= ($intStatus == SERVICE_DISCONNECTED)? SERVICE_CLOSURE_DISCONNECTED : SERVICE_CLOSURE_ARCHIVED;
		$intUserId			= AuthenticatedUser()->_arrUser['Id'];

		if ($strClosedOn === NULL)
		{	// Update the corresponding record in the _arrServiceRecords array
			$this->ClosedOn		= $strTimeStamp;
			$this->ClosedBy		= $intUserId;
			$this->Status			= $intStatus;
			$this->NatureOfClosure	= $intNatureOfClosure;
                        $this->save();

			// Service was deactivated successfully
			return $this->Id;
		}

		if ($strClosedOn < $strCreatedOn)
		{
			$strCreatedOn	= OutputMask()->LongDateAndTime($strCreatedOn);
			$strClosedOn	= OutputMask()->LongDateAndTime($strClosedOn);
			//TODO! I think this will have to be changed to check into the nature of closure
			$this->_strErrorMsg = "This service cannot be ". GetConstantDescription($intStatus, "service_status") ." as its CreatedOn TimeStamp ($strCreatedOn) is greater than its ClosedOn TimeStamp ($strClosedOn) signifying that it was never actually used by this account";
			return FALSE;
		}

		if ($strClosedOn > $strTimeStamp)
		{
			// The is a closure scheduled for a future date, don't let them change the status
			$this->_strErrorMsg = "This service cannot be ". GetConstantDescription($intStatus, "service_status") . " as it is scheduled to close at a later date";
			return FALSE;
		}

		// In order to "deactivate" this service, a new Service Record must be added
		// In which CreatedOn and ClosedOn == NOW()
		$intOldServiceId = $intService;
                $arrServiceRecordData = Array(	"FNN"						=> $this->FNN,
                                                "ServiceType"				=> $this->ServiceType,
                                                "residential"				=> $this->residential,
                                                "Indial100"					=> $this->Indial100,
                                                "AccountGroup"				=> $this->AccountGroup,
                                                "Account"					=> $this->Account,
                                                "CostCentre"				=> $this->CostCentre,
                                                "CreatedOn"					=> $strTimeStamp,
                                                "CreatedBy"					=> $intUserId,
                                                "NatureOfCreation"			=> SERVICE_CREATION_STATUS_CHANGED,
                                                "ClosedOn"					=> $strTimeStamp,
                                                "ClosedBy"					=> $intUserId,
                                                "NatureOfClosure"			=> $intNatureOfClosure,
                                                "Carrier"					=> $this->Carrier,
                                                "CarrierPreselect"			=> $this->CarrierPreselect,
                                                "EarliestCDR"				=> $this->EarliestCDR,
                                                "LatestCDR"					=> $this->LatestCDR,
                                                "LineStatus"				=> $this->LineStatus,
                                                "LineStatusDate"			=> $this->LineStatusDate,
                                                "PreselectionStatus"		=> $this->PreselectionStatus,
                                                "PreselectionStatusDate"	=> $this->PreselectionStatusDate,
                                                "ForceInvoiceRender"		=> $this->ForceInvoiceRender,
                                                "LastOwner"					=> $this->LastOwner,
                                                "NextOwner"					=> $this->NextOwner,
                                                "Status"					=> $intStatus
                                                );

		$insService	= new StatementInsert("Service", $arrServiceRecordData);
		$mixResult	= $insService->Execute($arrServiceRecordData);
		if ($mixResult === FALSE)
		{
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert a new record into the Service table";
			return FALSE;
		}

		// Store the Id of the new Service Record
		$this->_intNewId = $mixResult;

		// Deactivating the service was successfull
		return $mixResult;

	}

        	//------------------------------------------------------------------------//
	// _CopyPlanDetails
	//------------------------------------------------------------------------//
	/**
	 * _CopyPlanDetails()
	 *
	 * Makes a Copy of the plan details associated with the current Service Id, and associates them with $intDestServiceId
	 *
	 * Makes a Copy of the plan details associated with the current Service Id, and associates them with $intDestServiceId
	 * TODO! Currently this retains the State of the LastChargedOn property.  There might be situations where you don't want this to be the case.  Such as ChangeOfLessee's and AccountChanges
	 *
	 * @param	int		$intDestServiceId					Id of the Destination Service
	 * @param	string	$strEarliestAllowableEndDateTime	optional, Defaults to NULL.  If NULL then
	 * 														All ServiceRatePlan and ServiceRateGroup records will be copied
	 * 														across.  If set to a DateTime, then only those records where
	 * 														EndDateTime > $strEarliestAllowableEndDateTime will be copied across
	 *
	 * @return	bool										TRUE on success, FALSE on Failure
	 * @method
	 */
	protected function _CopyPlanDetails($intDestServiceId, $strEarliestAllowableEndDateTime=NULL)
	{
		$intNewServiceId			= $intDestServiceId;
		$intOldServiceId			= $this->Id;
		$strEndDateTimeCondition	= "";
		if ($strEarliestAllowableEndDateTime !== NULL)
		{
			$strEndDateTimeCondition = "AND EndDatetime > '$strEarliestAllowableEndDateTime'";
		}

		// Copy all valid ServiceRatePlan records across from the old service
		$strCopyServiceRatePlanRecordsToNewService =	"INSERT INTO ServiceRatePlan (Id, Service, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active, contract_scheduled_end_datetime, ".
														"contract_effective_end_datetime, contract_status_id, contract_breach_reason_id, contract_breach_reason_description, contract_payout_percentage, ".
														"contract_payout_charge_id, exit_fee_charge_id, contract_breach_fees_charged_on, contract_breach_fees_employee_id, contract_breach_fees_reason) ".
														"SELECT NULL, $intNewServiceId, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active, contract_scheduled_end_datetime, ".
														"contract_effective_end_datetime, contract_status_id, contract_breach_reason_id, contract_breach_reason_description, contract_payout_percentage, ".
														"contract_payout_charge_id, exit_fee_charge_id, contract_breach_fees_charged_on, contract_breach_fees_employee_id, contract_breach_fees_reason ".
														"FROM ServiceRatePlan WHERE Service = $intOldServiceId $strEndDateTimeCondition AND StartDatetime < EndDatetime";
		$qryInsertServicePlanDetails = new Query();

		if ($qryInsertServicePlanDetails->Execute($strCopyServiceRatePlanRecordsToNewService) === FALSE)
		{
			// Inserting the records into the ServiceRatePlan table failed
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert records into the ServiceRatePlan table";
			return FALSE;
		}

		// Copy all valid ServiceRateGroup records across from the old service
		$strCopyServiceRateGroupRecordsToNewService =	"INSERT INTO ServiceRateGroup (Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active) ".
														"SELECT NULL, $intNewServiceId, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active ".
														"FROM ServiceRateGroup WHERE Service = $intOldServiceId $strEndDateTimeCondition AND StartDatetime < EndDatetime";

		if ($qryInsertServicePlanDetails->Execute($strCopyServiceRateGroupRecordsToNewService) === FALSE)
		{
			// Inserting the records into the ServiceRateGroup table failed
			$this->_strErrorMsg = "Unexpected Database error occurred while trying to insert records into the ServiceRateGroup table";
			return FALSE;
		}
		return TRUE;
	}

   
    abstract protected function _CopySupplementaryDetails($intDestServiceId, $intDestAccountId, $intDestAccountGroup);
    
    public function __get($sField) 
    {
    	if ($sField == 'id')
            $sField = 'Id';
        return $this->oDO->{$sField};
    }



    public function __call($function, $args)
    {
        return call_user_func_array(array($this->oDO, $function),$args);
    }

   

    public function __set($sField, $mValue) 
    {
		$this->oDO->{$sField} = $mValue;
    }

    public function save() 
    {
		return $this->oDO->save();
    }

    public function toArray() 
    {
		return $this->oDO->toArray();
    }

    //------------------------------------------------------------------------//
	// GetServiceById
	//------------------------------------------------------------------------//
	/**
	 * GetServiceById()
	 *
	 * Creates and returns the ServiceType specific ModuleService derived object modelling the service specified
	 *
	 * Creates and returns the ServiceType specific ModuleService derived object modelling the service specified
	 * (Factory method for creating Service objects specific to the service's ServiceType)
	 *
	 * @param	int		$intServiceId		Id of the service to create an object of.  Must reference a valid service
	 * 										record in the database
	 * @param	int		$intServiceType		optional, the proper ServiceType for the service.  If not supplied, then
	 * 										it is calculated, but this requires an extra interaction with the database
	 *
	 * @return	mixed						FALSE	: Error has occurred
	 * 										NULL	: The Service could not be found in the database
	 * 										object	: one of either ModuleLandLine, ModuleMobile, ModuleADSL or ModuleInbound
	 * 													modelling the service
	 * @method
	 * @static
	 */
	static function getForId($intServiceId)
	{
		static $selServiceType;
		if ($intServiceType === NULL)
		{
			// The ServiceType is not known, find out what it is
			if (!isset($selServiceType))
			{
				// Create the prepared statement
				$selServiceType = new StatementSelect("Service", "ServiceType", "Id = <ServiceId>");
			}
			$mixResult = $selServiceType->Execute(Array("ServiceId"=>$intServiceId));
			if ($mixResult === FALSE)
			{
				// An error occurred
				return FALSE;
			}
			if ($mixResult == 0)
			{
				// The service could not be found
				return NULL;
			}
			$arrRecord = $selServiceType->Fetch();
			$intServiceType = $arrRecord['ServiceType'];
		}

		// Instanciate the object
		try
		{
			switch ($intServiceType)
			{
				case SERVICE_TYPE_LAND_LINE:
					$objService = new Logic_Service_Landline($intServiceId);
					break;

				case SERVICE_TYPE_MOBILE:
					$objService = new Logic_Service_Mobile($intServiceId);
					break;

				case SERVICE_TYPE_ADSL:
					$objService = new Logic_Service_ADSL($intServiceId);
					break;

				case SERVICE_TYPE_INBOUND:
					$objService = new Logic_Service_Inbound($intServiceId);
					break;

				default;
					return FALSE;
			}
		}
		catch (Exception $e)
		{
			return FALSE;
		}


		return $objService;
	}

   
}
?>
