<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// service
//----------------------------------------------------------------------------//
/**
 * service
 *
 * contains all ApplicationTemplate extended classes relating to service functionality
 *
 * contains all ApplicationTemplate extended classes relating to service functionality
 *
 * @file		service.php
 * @language	PHP
 * @package		framework
 * @author		Sean, Jared 'flame' Herbohn
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateService
//----------------------------------------------------------------------------//
/**
 * AppTemplateService
 *
 * The AppTemplateService class
 *
 * The AppTemplateService class.  This incorporates all logic for all pages
 * relating to services
 *
 *
 * @package	ui_app
 * @class	AppTemplateService
 * @extends	ApplicationTemplate
 */
class AppTemplateService extends ApplicationTemplate
{
	// If Service.Id is passed and isn't the most recent record refencing the Service, then change it to the most recent one
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Initialises the ApplicationTemplate object
	 * 
	 * Initialises the ApplicationTemplate object
	 *
	 * @return		void
	 * @method
	 */
	function __construct()
	{
		parent::__construct();
		
		// If a service's Id has been passed by GET, POST or ajax request, make sure it references
		// the most recent Service record which belongs to the Account and models the physical Service
		if (DBO()->Service->Id->IsSet)
		{
			DBO()->ActualRequestedService->Id = DBO()->Service->Id->Value;			
			$intNewestServiceId = $this->GetMostRecentServiceRecordId(DBO()->Service->Id->Value);
			if ($intNewestServiceId != FALSE)
			{
				DBO()->Service->Id = $intNewestServiceId;
			}
		}
	}
	
	//------------------------------------------------------------------------//
	// View
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Performs the logic for viewing a service
	 * 
	 * Performs the logic for viewing a service
	 *
	 * @return		void
	 * @method		View
	 *
	 */
	function View()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		$bolUserHasOperatorPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// Setup all DBO and DBL objects required for the page
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->value ." you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		DBO()->Account->Id = DBO()->Service->Account->Value;
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "Can not find Account: ". DBO()->Service->Account->Value . " associated with this service";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Check that ClosedOn >= CreatedOn if ClosedOn IS NOT NULL
		if (DBO()->Service->ClosedOn->Value != NULL && DBO()->Service->ClosedOn->Value < DBO()->Service->CreatedOn->Value)
		{
			// This service record is invalid
			// Try to find the current owner of this Service
			$intOwner		= ModuleService::GetNewestOwner(DBO()->Service->FNN->Value);
			$intAccount		= DBO()->Service->Account->Value;
			$strAccountLink	= Href()->AccountOverview($intAccount);
			$strErrorMsg	= "This is an invalid service record.  It belonged to account <a href='$strAccountLink' title='Account Overview'>$intAccount</a>";
			
			if ($intOwner == FALSE)
			{
				$strErrorMsg .= "<br />The current owning account cannot be established.";
			}
			else
			{
				$strOwnerLink = Href()->AccountOverview($intOwner);
				$strErrorMsg .= "<br />The current owning account is <a href='$strOwnerLink' title='Account Overview'>$intOwner</a>";
			}
			DBO()->Error->Message = $strErrorMsg;
			$this->LoadPage('error');
			return FALSE;
		}
		
		if (DBO()->Service->Indial100->Value)
		{
			DBL()->ServiceExtension->Service = DBO()->Service->Id->Value;
			DBL()->ServiceExtension->Archived = 0;
			DBL()->ServiceExtension->Load();
			DBO()->Service->ELB = (bool)DBL()->ServiceExtension->RecordCount();
		}
		
		// If the Service has a CostCentre then retrieve the name of it
		if (DBO()->Service->CostCentre->Value)
		{
			DBO()->CostCentre->Id = DBO()->Service->CostCentre->Value;
			DBO()->CostCentre->Load();
			DBO()->Service->CostCentre = DBO()->CostCentre->Name->Value;
		}
		
		
		// Get the details of the current plan for the service
		DBO()->CurrentRatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		if (DBO()->CurrentRatePlan->Id->Value)
		{
			DBO()->CurrentRatePlan->SetTable("RatePlan");
			DBO()->CurrentRatePlan->Load();
		}
		
		DBO()->FutureRatePlan->Id = GetPlanScheduledForNextBillingPeriod(DBO()->Service->Id->Value);
		if (DBO()->FutureRatePlan->Id->Value)
		{
			DBO()->FutureRatePlan->SetTable("RatePlan");
			DBO()->FutureRatePlan->Load();
		}
		
		// Calculate unbilled charges (this includes all unbilled Adjustments(charges) and CDRs for the service)
		$fltUnbilledAdjustments					= UnbilledServiceChargeTotal(DBO()->Service->Id->Value);
		$fltUnbilledCDRs						= UnbilledServiceCDRTotal(DBO()->Service->Id->Value);
		DBO()->Service->TotalUnbilledCharges 	= AddGST($fltUnbilledAdjustments + $fltUnbilledCDRs);
		
		// Load the service notes
		LoadNotes(NULL, DBO()->Service->Id->Value);
		
		// Retrieve the Provisioning History for the Service if it is a Land Line
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
		{
			DBO()->History->CategoryFilter	= PROVISIONING_HISTORY_CATEGORY_BOTH;
			DBO()->History->TypeFilter		= PROVISIONING_HISTORY_FILTER_ALL;
			DBO()->History->MaxItems		= 10;
			$appProvisioning = new AppTemplateProvisioning();
			DBO()->History->Records = $appProvisioning->GetHistory(DBO()->History->CategoryFilter->Value, DBO()->History->TypeFilter->Value, DBO()->Account->Id->Value, DBO()->Service->Id->Value, DBO()->History->MaxItems->Value);
		}
		
		// Context menu
		ContextMenu()->Account->Account_Overview(DBO()->Account->Id->Value);
		ContextMenu()->Account->Invoices_and_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Account->Services->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account->Contacts->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account->Services->Add_Services(DBO()->Account->Id->Value);
			ContextMenu()->Account->Contacts->Add_Contact(DBO()->Account->Id->Value);
			ContextMenu()->Account->Payments->Make_Payment(DBO()->Account->Id->Value);
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
			{
				ContextMenu()->Account->Provisioning->Provisioning(NULL, DBO()->Account->Id->Value);
				ContextMenu()->Account->Provisioning->ViewProvisioningHistory(NULL, DBO()->Account->Id->Value);
			}
			ContextMenu()->Account->Payments->Change_Payment_Method(DBO()->Account->Id->Value);
			ContextMenu()->Account->Add_Associated_Account(DBO()->Account->Id->Value);
			ContextMenu()->Account->Notes->Add_Account_Note(DBO()->Account->Id->Value);
		}
		ContextMenu()->Account->Notes->View_Account_Notes(DBO()->Account->Id->Value);

		ContextMenu()->Service->View_Unbilled_Charges(DBO()->Service->Id->Value);	
		ContextMenu()->Service->View_Service_History(DBO()->Service->Id->Value);
		ContextMenu()->Service->Plan->View_Service_Rate_Plan(DBO()->Service->Id->Value);	
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Service->Edit_Service(DBO()->Service->Id->Value);
			ContextMenu()->Service->Plan->Change_Plan(DBO()->Service->Id->Value);	
			ContextMenu()->Service->Move_Service(DBO()->Service->Id->Value);	
			ContextMenu()->Service->Adjustments->Add_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			ContextMenu()->Service->Adjustments->Add_Recurring_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			// Only Landlines can have provisioning
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
			{
				ContextMenu()->Service->Provisioning->Provisioning(DBO()->Service->Id->Value);
				ContextMenu()->Service->Provisioning->ViewProvisioningHistory(DBO()->Service->Id->Value);
			}
			ContextMenu()->Service->Notes->Add_Service_Note(DBO()->Service->Id->Value);
		}
		ContextMenu()->Service->Notes->View_Service_Notes(DBO()->Service->Id->Value);
		
		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview(DBO()->Service->Account->Value, TRUE);
		BreadCrumb()->SetCurrentPage("Service");

		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('service_view');
		return TRUE;
	}

	function ViewHistory()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);

		$intService = DBO()->Service->Id->Value;
		$objService = ModuleService::GetServiceById($intService);
		
		if ($objService === FALSE)
		{
			// Instantiating the Service object failed
			Ajax()->AddCommand("Alert", "ERROR: Retrieving the service (Id: $intService) failed unexpectedly");
			return TRUE;
		}
		elseif ($objService === NULL)
		{
			// Could not find the service
			Ajax()->AddCommand("Alert", "ERROR: Could not find the service with Service Id: $intService");
			return TRUE;
		}
		
		// The Service object was successfully created
		DBO()->Service->AsObject = $objService;

		// Use the generic popup page template
		$this->LoadPage('generic_popup');
		$this->Page->SetName('Service History - '. $objService->GetFNN());
		$this->Page->AddObject('ServiceHistory', COLUMN_ONE, HTML_CONTEXT_POPUP);
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// GetService
	//------------------------------------------------------------------------//
	/**
	 * GetService()
	 *
	 * Builds an array structure defining a service, the service's ServiceType specific extra details, a history of its status, and its plan details
	 * 
	 * Builds an array structure defining a service, the service's ServiceType specific extra details, a history of its status, and its plan details
	 * The history details when the service was activated(or created) and Closed(disconnected or archived)
	 * It will always have at least one record
	 * On Success the returned array will be of the format:
	 * $arrService	['Id']								Id of the most recently added Service record which models the same Service as $intService
	 * 				['FNN']
	 * 				['ServiceType']
	 * 				['CurrentPlan']	['Id']
	 * 								['Name']
	 * 				['FuturePlan']	['Id']
	 * 								['Name']
	 * 								['StartDatetime']
	 * 				['History'][]	['ServiceId']		These will be ordered from Latest to Earliest Service records modelling this Service for this account
	 * 								['CreatedOn']
	 * 								['ClosedOn']
	 * 								['CreatedBy']
	 * 								['ClosedBy']
	 * 								['Status']
	 * 								['LineStatus']
	 * 								['LineStatusDate']
	 * 				['ExtraDetail']	[]					Stores the ServiceType specific details of the service
	 * 
	 * @param	int		$intService		Id of any of the service records used to model this service
	 *
	 * @return	mixed					FALSE:	On database error
	 * 									Array:  $arrService
	 * 	
	 * @method
	 */
	static function GetService($intService)
	{
		//TODO! This currently does not retrieve the ServiceType specific Extra Details
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
		$arrColumns	= Array("Id" 						=> "S.Id",
							"FNN"						=> "S.FNN",
							"ServiceType"				=> "S.ServiceType", 
							"Status"		 			=> "S.Status",
							"LineStatus"				=> "S.LineStatus",
							"LineStatusDate"			=> "S.LineStatusDate",
							"CreatedOn"					=> "S.CreatedOn", 
							"ClosedOn"					=> "S.ClosedOn",
							"CreatedBy"					=> "S.CreatedBy", 
							"ClosedBy"					=> "S.ClosedBy",
							"NatureOfCreation"			=> "S.NatureOfCreation",
							"NatureOfClosure"			=> "S.NatureOfClosure",
							"LastOwner"					=> "S.LastOwner",
							"NextOwner"					=> "S.NextOwner",
							"Account"					=> "S.Account",
							"CurrentPlanId" 			=> "RP1.Id",
							"CurrentPlanName"			=> "RP1.Name",
							"FuturePlanId"				=> "RP2.Id",
							"FuturePlanName"			=> "RP2.Name",
							"FuturePlanStartDatetime"	=> "SRP3.StartDatetime");
		$strWhere	= "	S.Account = (SELECT Account FROM Service WHERE Id = <ServiceId>)
						AND
						S.FNN = (SELECT FNN FROM Service WHERE Id = <ServiceId>)";
		$arrWhere	= Array("ServiceId" => $intService);
		$strOrderBy	= ("S.Id DESC");
		
		$selServices = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy);
		if ($selServices->Execute($arrWhere) == FALSE)
		{
			// An error occurred, or no records could be returned
			return FALSE;
		}
		
		$arrRecord	= $selServices->Fetch();
		$arrService = Array (
								"Id"			=> $arrRecord['Id'],
								"FNN"			=> $arrRecord['FNN'],
								"ServiceType"	=> $arrRecord['ServiceType'],
								"Account"		=> $arrRecord['Account']
							);

		// Add details about the Service's current plan, if it has one
		if ($arrRecord['CurrentPlanId'] != NULL)
		{
			$arrService['CurrentPlan'] = Array	(
													"Id"	=> $arrRecord['CurrentPlanId'],
													"Name"	=> $arrRecord['CurrentPlanName']
												);
		}
		else
		{
			$arrService['CurrentPlan'] = NULL;
		}
		
		// Add details about the Service's Future scheduled plan, if it has one
		if ($arrRecord['FuturePlanId'] != NULL)
		{
			$arrService['FuturePlan'] = Array	(
													"Id"	=> $arrRecord['FuturePlanId'],
													"Name"	=> $arrRecord['FuturePlanName'],
													"StartDatetime"	=> $arrRecord['FuturePlanStartDatetime']
												);
		}
		else
		{
			$arrService['FuturePlan'] = NULL;
		}
		
		// Add this record's details to the history array
		$arrService['History']		= Array();
		$arrService['History'][]	= Array	(
												"ServiceId"			=> $arrRecord['Id'],
												"CreatedOn"			=> $arrRecord['CreatedOn'],
												"ClosedOn"			=> $arrRecord['ClosedOn'],
												"CreatedBy"			=> $arrRecord['CreatedBy'],
												"ClosedBy"			=> $arrRecord['ClosedBy'],
												"NatureOfCreation"	=> $arrRecord['NatureOfCreation'],
												"NatureOfClosure"	=> $arrRecord['NatureOfClosure'],
												"LastOwner"			=> $arrRecord['LastOwner'],
												"NextOwner"			=> $arrRecord['NextOwner'],
												"Status"			=> $arrRecord['Status'],
												"LineStatus"		=> $arrRecord['LineStatus'],
												"LineStatusDate"	=> $arrRecord['LineStatusDate'],
											);
		 
		
		// If multiple Service records relate to the one actual service then they will be consecutive in the RecordSet
		// Find each one and add it to the Status history
		while (($arrRecord = $selServices->Fetch()) !== FALSE)
		{
			// This record relates to the same Service
			$arrService['History'][]	= Array	(
													"ServiceId"	=> $arrRecord['Id'],
													"CreatedOn"	=> $arrRecord['CreatedOn'],
													"ClosedOn"	=> $arrRecord['ClosedOn'],
													"CreatedBy"	=> $arrRecord['CreatedBy'],
													"ClosedBy"	=> $arrRecord['ClosedBy'],
													"Status"	=> $arrRecord['Status'],
													"LineStatus"		=> $arrService['LineStatus'],
													"LineStatusDate"	=> $arrService['LineStatusDate'],
												);
		}
		
		return $arrService;
	}

	//------------------------------------------------------------------------//
	// ViewAddress
	//------------------------------------------------------------------------//
	/**
	 * ViewAddress()
	 *
	 * Performs the logic for viewing the service's address details
	 * 
	 * Performs the logic for viewing the service's address details
	 * It assumes the following data has been declared
	 * 	DBO()->Service->Id		Id of the service to view the details of
	 *
	 * @return		void
	 * @method		ViewAddress
	 */
	function ViewAddress()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);

		if (!DBO()->Service->Load())
		{
			// Could not load the service reocord
			Ajax()->AddCommand("Alert", "ERROR: Could not find service with Id = ". DBO()->Service->Id->Value);
			return TRUE;
		}
		
		DBO()->ServiceAddress->Where->Service = DBO()->Service->Id->Value;
		
		if (!DBO()->ServiceAddress->Load())
		{
			// The service doesn't have address details, load up the Add Service popup
			$this->EditAddress();
			return TRUE;
		}

		// Store the Physical Address Description
		DBO()->ServiceAddress->PhysicalAddressDescription = $this->BuildPhysicalAddressDescription(DBO()->ServiceAddress->_arrProperties, "<br />");
		
		$this->LoadPage('service_address_view');
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// EditAddress
	//------------------------------------------------------------------------//
	/**
	 * EditAddress()
	 *
	 * Performs the logic for editting the service's address details
	 * 
	 * Performs the logic for editting the service's address details
	 * It assumes the following data has been declared
	 * 	DBO()->Service->Id		Id of the service to view the details of
	 *
	 * @return		void
	 * @method		EditAddress
	 */
	function EditAddress()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		if (!DBO()->Service->Load())
		{
			// Could not load the service record
			Ajax()->AddCommand("Alert", "ERROR: Could not find service with Id = ". DBO()->Service->Id->Value);
			return TRUE;
		}
		
		DBO()->Account->Id = DBO()->Service->Account->Value;
		DBO()->Account->Load();
		
		// Retrieve the address details of this service
		DBO()->ServiceAddress->Where->Service = DBO()->Service->Id->Value;
		DBO()->ServiceAddress->Load();
		
		if (!DBO()->ServiceAddress->Id->Value)
		{
			// The Service does not currently have a ServiceAddress record
			// Define default values, from the Account and Service records
			DBO()->ServiceAddress->Service = DBO()->Service->Id->Value;
			
			$this->_SetDefaultValuesForServiceAddress(DBO()->ServiceAddress, DBO()->Account);
		}
		
		// Retrieve the address details of each service belonging to this account
		DBO()->Account->AllAddresses = $this->_GetAllServiceAddresses(DBO()->Account->Id->Value);

		$this->LoadPage('service_address_edit');
		return TRUE;
	}

	//------------------------------------------------------------------------//
	// SaveAddress
	//------------------------------------------------------------------------//
	/**
	 * SaveAddress()
	 *
	 * Saves a ServiceAddress record
	 * 
	 * Saves a ServiceAddress record
	 * It assumes the following data has been declared
	 * 	DBO()->Service->Id		Id of the service to view the details of
	 * 	etc
	 * 
	 * On success it will fire the "OnServiceUpdate" event and the "OnNewNote" event
	 *
	 * @return		void
	 * @method		SaveAddress
	 */
	function SaveAddress()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		if (!DBO()->Service->Load())
		{
			// Could not load the service reocord
			Ajax()->AddCommand("Alert", "ERROR: Could not find service with Id = ". DBO()->Service->Id->Value);
			return TRUE;
		}
		
		DBO()->Account->Id = DBO()->Service->Account->Value;
		DBO()->Account->Load();
		
		// Retrieve the current address details of this service
		DBO()->CurrentServiceAddress->SetTable("ServiceAddress");
		DBO()->CurrentServiceAddress->Where->Service = DBO()->Service->Id->Value;
		DBO()->CurrentServiceAddress->Load();
		
		$dboServiceAddress	= DBO()->ServiceAddress;
		$arrProblems 		= Array();
		$bolIsValid			= $this->ValidateAndCleanServiceAddress($dboServiceAddress, $arrProblems);
		
		if (!$bolIsValid)
		{
			// The Service Address record is invalid
			$strProblems = implode("<br />", $arrProblems);
			Ajax()->AddCommand("Alert", "ERROR: The following problems were found with the submitted data:<br />$strProblems");
			return TRUE;
		}
		
		// The Service Address is valid, save it
		DBO()->ServiceAddress->Id = (DBO()->CurrentServiceAddress->Id->Value)? DBO()->CurrentServiceAddress->Id->Value : 0;
		
		DBO()->ServiceAddress->AccountGroup	= DBO()->Service->AccountGroup->Value;
		DBO()->ServiceAddress->Account		= DBO()->Service->Account->Value;
		DBO()->ServiceAddress->Service		= DBO()->Service->Id->Value;

		if (!DBO()->ServiceAddress->Save())
		{
			// Saving the record failed
			Ajax()->AddCommand("Alert", "ERROR: Saving the address details failed, unexpectedly");
			return TRUE;
		}
		
		// Create a system note
		if (DBO()->CurrentServiceAddress->Id->Value)
		{
			$strSystemNote = "Address details have been modified";
		}
		else
		{
			$strSystemNote = "Address details have been defined";
		}
		
		SaveSystemNote($strSystemNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
		Ajax()->FireOnNewNoteEvent(DBO()->Service->Account->Value, DBO()->Service->Id->Value);
		
		// Fire the OnServiceUpdate event
		$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
		Ajax()->FireEvent(EVENT_ON_SERVICE_UPDATE, $arrEvent);
		
		// View the Address
		Ajax()->AddCommand("ClosePopup", DBO()->Popup->Id->Value);
		Ajax()->AddCommand("Alert", "Address successfully saved");
		return TRUE;
	}

	// Returns TRUE if valid, else false
	// Any problems encountered will be recorded in the $arrProblems array
	// The $dboServiceAddress will also be cleaned of values that shouldn't be set
	function ValidateAndCleanServiceAddress(&$dboServiceAddress, &$arrProblems)
	{
		// Trim whitespace from all properties
		foreach ($dboServiceAddress as $strName=>$objProperty)
		{
			$objProperty->Trim();
			
			// Nullify any properties that equate to empty strings
			if ($objProperty->Value === "")
			{
				// Setting $objProperty = NULL doesn't work, so directly reference $dboServiceAddress
				$dboServiceAddress->{$strName} = NULL;
			}
		}

		// Convert to upper case, those values that should be in upper case
		$dboServiceAddress->ServiceAddressTypeSuffix	= ($dboServiceAddress->ServiceAddressTypeSuffix->Value) ? strtoupper($dboServiceAddress->ServiceAddressTypeSuffix->Value) : NULL;
		$dboServiceAddress->ServiceStreetNumberSuffix	= ($dboServiceAddress->ServiceStreetNumberSuffix->Value) ? strtoupper($dboServiceAddress->ServiceStreetNumberSuffix->Value) : NULL;
		
		// Run the UiAppDocumentation defined validation rules on the record
		$dboServiceAddress->Validate();
		
		// Handle the user details
		if ($dboServiceAddress->Residential->Value)
		{
			// It's a residential service
			if (!$dboServiceAddress->EndUserTitle->Valid)
			{
				$arrProblems[] = "Title must be declared";
			}
			if (!$dboServiceAddress->EndUserGivenName->Valid)
			{
				$arrProblems[] = "Given Name must be declared";
			}
			if (!$dboServiceAddress->EndUserFamilyName->Valid)
			{
				$arrProblems[] = "Family Name must be declared";
			}
			if (!$dboServiceAddress->DateOfBirth->Valid)
			{
				$arrProblems[] = "Date of birth must be in the format of DD/MM/YYYY";
			}
			else
			{
				// The date is in a valid format, now convert it so it can be stored as YYYYMMDD
				$arrDate = explode("/", $dboServiceAddress->DateOfBirth->Value);
				$dboServiceAddress->DateOfBirth = "{$arrDate[2]}{$arrDate[1]}{$arrDate[0]}";
			}
			
			// Clear the "business" specific fields
			$dboServiceAddress->ABN					= NULL;
			$dboServiceAddress->EndUserCompanyName	= NULL;
			$dboServiceAddress->TradingName			= NULL;
		}
		else
		{
			// It's a business service
			// Remove all spaces from the ABN
			$dboServiceAddress->ABN = str_replace(" ", "", $dboServiceAddress->ABN->Value);  
			$dboServiceAddress->ABN->Validate();
			
			if (!$dboServiceAddress->ABN->Valid)
			{
				$arrProblems[] = "A valid ABN must be declared";
			}
			if (!$dboServiceAddress->EndUserCompanyName->Valid)
			{
				$arrProblems[] = "Company Name must be declared";
			}
			
			// Clear the "residential" specific fields
			$dboServiceAddress->EndUserTitle		= NULL;
			$dboServiceAddress->EndUserGivenName	= NULL;
			$dboServiceAddress->EndUserFamilyName	= NULL;
			$dboServiceAddress->DateOfBirth			= NULL;
			$dboServiceAddress->Employer			= NULL;
			$dboServiceAddress->Occupation			= NULL;
		}
		
		// Check the Billing Address fields
		if (!$dboServiceAddress->BillName->Valid)
		{
			$arrProblems[] = "Bill Name must be declared";
		}
		if (!$dboServiceAddress->BillAddress1->Valid)
		{
			$arrProblems[] = "Billing Address must be declared";
		}
		if (!$dboServiceAddress->BillLocality->Valid)
		{
			$arrProblems[] = "Billing Address Locality must be declared";
		}
		if (!$dboServiceAddress->BillPostcode->Valid)
		{
			$arrProblems[] = "Billing Address Postcode must be declared";
		}
		
		// Validate the service's physical address
		$strAddressType = $dboServiceAddress->ServiceAddressType->Value;
		if (array_key_exists($strAddressType, $GLOBALS['*arrConstant']['ServiceAddrType']))
		{
			// An Address Type has been specified
			if (!$dboServiceAddress->ServiceAddressTypeNumber->Valid)
			{
				$arrProblems[] = "Address Type Number must be declared";
			}
			if (!$dboServiceAddress->ServiceAddressTypeSuffix->Valid)
			{
				$arrProblems[] = "Address Type Suffix must consist only of letters";
			}
		}
		else
		{
			// No address type has been specified
			$dboServiceAddress->ServiceAddressType			= NULL;
			$dboServiceAddress->ServiceAddressTypeNumber	= NULL;
			$dboServiceAddress->ServiceAddressTypeSuffix	= NULL;
		}

		if (array_key_exists($strAddressType, $GLOBALS['*arrConstant']['PostalAddrType']))
		{
			// ServiceAddressType is a postal address
			// NULL the fields that aren't used for postal addresses
			$dboServiceAddress->ServiceStreetNumberStart	= NULL;
			$dboServiceAddress->ServiceStreetNumberEnd		= NULL;
			$dboServiceAddress->ServiceStreetNumberSuffix	= NULL;
			$dboServiceAddress->ServiceStreetName			= NULL;
			$dboServiceAddress->ServiceStreetType			= NULL;
			$dboServiceAddress->ServiceStreetTypeSuffix		= NULL;
			$dboServiceAddress->ServicePropertyName			= NULL;
		}
		else
		{
			// ServiceAddressType is not a postal address type, and can therefore have street details
			if ($strAddressType == SERVICE_ADDR_TYPE_LOT)
			{
				// LOTs do not have Street numbers
				$dboServiceAddress->ServiceStreetNumberStart	= NULL;
				$dboServiceAddress->ServiceStreetNumberEnd		= NULL;
				$dboServiceAddress->ServiceStreetNumberSuffix	= NULL;
			}
			else
			{
				// Validate the Street Number
				$this->ValidateStreetNumber($dboServiceAddress, $arrProblems);				
			}
			
			if ($dboServiceAddress->ServiceStreetName->Value != NULL)
			{
				// A street name has been declared
				// You don't need to test the ServiceStreetType as it is always valid
				if ($dboServiceAddress->ServiceStreetType->Value == SERVICE_STREET_TYPE_NOT_REQUIRED)
				{
					// Suffix is not required
					$dboServiceAddress->ServiceStreetTypeSuffix = NULL;
				}
			}
			else
			{
				// A street name has not been declared
				$dboServiceAddress->ServiceStreetType		= NULL;
				$dboServiceAddress->ServiceStreetTypeSuffix	= NULL;
				
				$dboServiceAddress->ServiceStreetNumberStart	= NULL;
				$dboServiceAddress->ServiceStreetNumberEnd		= NULL;
				$dboServiceAddress->ServiceStreetNumberSuffix	= NULL;
				
				// Check that a Property Name has been declared
				if ($dboServiceAddress->ServicePropertyName->Value == NULL)
				{
					$arrProblems[] = "At least one of the fields 'Street Name' or 'Property Name' must be specified";
				}
			}
		}
		
		if (!$dboServiceAddress->ServiceLocality->Valid)
		{
			$arrProblems[] = "Physical Address Locality must be declared";
		}
		if (!$dboServiceAddress->ServiceState->Valid)
		{
			$arrProblems[] = "Physical Address State must be declared";
		}
		if (!$dboServiceAddress->ServicePostcode->Valid)
		{
			$arrProblems[] = "Physical Address Postcode must be declared";
		}
		
		return (count($arrProblems)) ? FALSE : TRUE;
	}
	
	function ValidateStreetNumber(&$dboServiceAddress, &$arrProblems)
	{
		if ($dboServiceAddress->ServiceStreetNumberStart->Value == NULL)
		{
			// Street Number Start has not been specified
			// Reset the Number End and Suffix
			$dboServiceAddress->ServiceStreetNumberEnd		= NULL;
			$dboServiceAddress->ServiceStreetNumberSuffix	= NULL;
			
			if ($dboServiceAddress->ServiceStreetName->Value !== NULL)
			{
				$arrProblems[] = "Street Number Start must be declared";
			}
			return;
		}
		
		if (!$dboServiceAddress->ServiceStreetNumberStart->Valid)
		{
			$arrProblems[] = "Street Number Start must be declared";
		}
		
		if ($dboServiceAddress->ServiceStreetNumberEnd->Value !== NULL)
		{
			// An end number has been declared
			if (!$dboServiceAddress->ServiceStreetNumberEnd->Valid)
			{
				$arrProblems[] = "Street Number End is invalid";
			}
			elseif ($dboServiceAddress->ServiceStreetNumberEnd->Value <= $dboServiceAddress->ServiceStreetNumberStart->Value)
			{
				// The end number is less than or equal to the start number
				$arrProblems[] = "Street Number End must be greater than Street Number Start";
			}
		}
		
		if ($dboServiceAddress->ServiceStreetNumberSuffix->Value !== NULL && (!$dboServiceAddress->ServiceStreetNumberSuffix->Valid))
		{
			// A suffix has been specified but is invalid
			$arrProblems[] = "Street Number Suffix must consist only of letters";
		}
	}
	
	// Converts a physical service address into its descriptive format, as you would see on an envelope
	function BuildPhysicalAddressDescription($arrAddress, $strLineSeperator="\n")
	{
		$strPropertyName	= trim($arrAddress['ServicePropertyName']);
		$strLocality		= trim($arrAddress['ServiceLocality']);
		$strState			= trim($arrAddress['ServiceState']);
		$strPostCode		= $arrAddress['ServicePostcode'];
		$strAddressTypeLine = "";
		$strStreetLine		= "";
		
		if ($arrAddress['ServiceAddressType'] == SERVICE_ADDR_TYPE_LOT)
		{
			// The service address is a "LOT"
			$strAddressTypeLine		= trim("Allotment {$arrAddress['ServiceAddressTypeNumber']} {$arrAddress['ServiceAddressTypeSuffix']}");
			$strStreetType			= ($arrAddress['ServiceStreetType'] == SERVICE_STREET_TYPE_NOT_REQUIRED)? "" : GetConstantDescription($arrAddress['ServiceStreetType'], "ServiceStreetType");
			$strStreetLine			= trim($arrAddress['ServiceStreetName'] ." $strStreetType ". GetConstantDescription($arrAddress['ServiceStreetTypeSuffix'], "ServiceStreetSuffixType"));
		}
		else if (isset($GLOBALS['*arrConstant']['PostalAddrType'][$arrAddress['ServiceAddressType']]))
		{
			// The service address is a postal service address
			$strAddressTypeLine = trim(GetConstantDescription($arrAddress['ServiceAddressType'], "ServiceAddrType") ." {$arrAddress['ServiceAddressTypeNumber']} {$arrAddress['ServiceAddressTypeSuffix']}");
		}
		else
		{
			// The service address is a standard address, and may or may not have an Address Type
			$strAddressTypeLine = trim(GetConstantDescription($arrAddress['ServiceAddressType'], "ServiceAddrType") ." {$arrAddress['ServiceAddressTypeNumber']} {$arrAddress['ServiceAddressTypeSuffix']}");
			
			$strStreetNumber = "";
			if ($arrAddress['ServiceStreetNumberStart'] != "")
			{
				$strStreetNumber = $arrAddress['ServiceStreetNumberStart'];
			}
			if ($arrAddress['ServiceStreetNumberEnd'] != "")
			{
				$strStreetNumber .= " - ". $arrAddress['ServiceStreetNumberEnd'];
			}
			$strStreetNumber = trim($strStreetNumber ." ". $arrAddress['ServiceStreetNumberSuffix']);
			
			$strStreetType = ($arrAddress['ServiceStreetType'] == SERVICE_STREET_TYPE_NOT_REQUIRED)? "" : GetConstantDescription($arrAddress['ServiceStreetType'], "ServiceStreetType");
			$strStreetTypeSuffix = GetConstantDescription($arrAddress['ServiceStreetTypeSuffix'], "StreetTypeSuffix");
			$strStreetLine = trim("$strStreetNumber {$arrAddress['ServiceStreetName']} $strStreetType ". GetConstantDescription($arrAddress['ServiceStreetTypeSuffix'], "ServiceStreetSuffixType"));
		}
		
		$strAddress = "";
		if ($strPropertyName != "")
		{
			$strAddress .= $strPropertyName . $strLineSeperator;
		}
		if ($strAddressTypeLine != "")
		{
			$strAddress .= $strAddressTypeLine . $strLineSeperator;
		}
		if ($strStreetLine != "")
		{
			$strAddress .= $strStreetLine . $strLineSeperator;
		}
		$strAddress .= "$strLocality{$strLineSeperator}$strState $strPostCode";
		
		return ucwords($strAddress);
	}
	
	//------------------------------------------------------------------------//
	// BulkAdd
	//------------------------------------------------------------------------//
	/**
	 * BulkAdd()
	 *
	 * Performs the logic for building the "bulk add service" page
	 * 
	 * Performs the logic for building the "bulk add service" page
	 * It assumes DBO()->Account->Id has been set
	 *
	 * @return		void
	 * @method		BulkAdd
	 */
	function BulkAdd()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "Can not find Account with Id: ". DBO()->Account->Id->Value;
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Load the ServiceAddress details for all Services currently belonging to the account
		DBO()->Account->AllAddresses = $this->_GetAllServiceAddresses(DBO()->Account->Id->Value);
		
		// Retrieve all usable Cost Centers
		$strWhere		= "Account IN (0, ". DBO()->Account->Id->Value .")";
		$selCostCenters	= new StatementSelect("CostCentre", "Id, Name", $strWhere, "Account DESC, Name ASC");
		$mixResult		= $selCostCenters->Execute();
		$arrCostCenters	= Array();
		if ($mixResult)
		{
			$arrRecordSet = $selCostCenters->FetchAll();
			foreach ($arrRecordSet as $arrRecord)
			{
				$arrCostCenters[$arrRecord['Id']] = $arrRecord['Name'];
			}
		}
		DBO()->Account->AllCostCenters = $arrCostCenters;
		
		// Retrieve all usable RatePlans
		$strWhere		= "Archived = <RatePlanActive> AND customer_group = <CustomerGroup>";
		$arrWhere		= array(
								"RatePlanActive"	=> RATE_STATUS_ACTIVE,
								"CustomerGroup"		=> DBO()->Account->CustomerGroup->Value
								);
		$selRatePlans	= new StatementSelect("RatePlan", "Id, ServiceType, Name", $strWhere, "Name ASC");
		$mixResult		= $selRatePlans->Execute($arrWhere);
		$arrRatePlans	= Array();
		if ($mixResult)
		{
			$arrRecordSet = $selRatePlans->FetchAll();
			foreach ($arrRecordSet as $arrRecord)
			{
				$arrRatePlans[$arrRecord['ServiceType']][$arrRecord['Id']] = $arrRecord['Name'];
			}
		}
		DBO()->Account->AllRatePlans = $arrRatePlans;
		
		// Retrieve all Dealer details
		$arrColumns = array("id", "first_name", "last_name", "title");
		$strWhere	= "termination_date > NOW()";
		$strOrderBy	= "title ASC, first_name ASC, last_name ASC, id ASC";
		$selDealers	= new StatementSelect("dealer", $arrColumns, $strWhere, $strOrderBy);
		$arrDealers = array();
		if ($selDealers->Execute())
		{
			$arrRecordSet = $selDealers->FetchAll();
			foreach ($arrRecordSet as $arrRecord)
			{
				$strName = trim(trim($arrRecord['title']) ." ". trim($arrRecord['first_name']) ." ". trim($arrRecord['last_name']));
				$arrDealers[] = array(	"Id"	=> $arrRecord['id'], 
										"Name"	=> $strName);
			}
		}
		
		DBO()->Dealers->AsArray = $arrDealers;
		
		// Context menu
		ContextMenu()->Account->Account_Overview(DBO()->Account->Id->Value);
		ContextMenu()->Account->Invoices_and_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Account->Services->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account->Contacts->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account->Contacts->Add_Contact(DBO()->Account->Id->Value);
		ContextMenu()->Account->Add_Associated_Account(DBO()->Account->Id->Value);
		ContextMenu()->Account->Notes->Add_Account_Note(DBO()->Account->Id->Value);
		ContextMenu()->Account->Notes->View_Account_Notes(DBO()->Account->Id->Value);
		
		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview(DBO()->Account->Id->Value, TRUE);
		BreadCrumb()->SetCurrentPage("Add Services");

		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('service_bulk_add');
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// BulkValidateServices
	//------------------------------------------------------------------------//
	/**
	 * BulkValidateServices()
	 *
	 * Performs preliminary Validation of services defined using the "Bulk Add Services" webpage
	 * 
	 * Performs preliminary Validation of services defined using the "Bulk Add Services" webpage
	 * It currently Validates the FNNs and checks that a plan has been specified 
	 * 
	 *
	 * @return		void
	 * @method		BulkValidateServices
	 */
	function BulkValidateServices()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not find account with Id: ". DBO()->Account->Id->Value);
			return TRUE;
		}
		
		// Retrieve the array of new services
		// Note that this is an array of objects (structs), not an array of associative arrays
		$arrServices = DBO()->Services->Data->Value;
		
		$JsCode = $this->_BulkValidateServices($arrServices);
		if ($JsCode !== NULL)
		{
			// At least one of the FNNs was invalid
			Ajax()->AddCommand("ExecuteJavascript", $JsCode);
			return TRUE;
		}
		
		// To have gotten this far, the FNNs must all be valid
		Ajax()->AddCommand("ExecuteJavascript", "Vixen.ServiceBulkAdd.ValidateServicesReturnHandler(true);");
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// BulkSave
	//------------------------------------------------------------------------//
	/**
	 * BulkSave()
	 *
	 * Performs final Validation of services defined using the "Bulk Add Services" webpage, and Saves the services
	 * 
	 * Performs final Validation of services defined using the "Bulk Add Services" webpage, and Saves the services
	 * 
	 *
	 * @return		void
	 * @method		BulkSave
	 */
	function BulkSave()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not find account with Id: ". DBO()->Account->Id->Value .". Action aborted.");
			return TRUE;
		}
		
		// Retrieve the array of new services
		// Note that this is an array of objects (structs), not an array of associative arrays
		$arrServices = DBO()->Services->Data->Value;
		
		$JsCode = $this->_BulkValidateServices($arrServices);
		if ($JsCode !== NULL)
		{
			// At least one of the FNNs was invalid
			Ajax()->AddCommand("ExecuteJavascript", $JsCode);
			return TRUE;
		}
		
		// Retrieve a list of all RatePlans required, and their Carrier details
		$arrRatePlanIds = array();
		foreach ($arrServices as $objService)
		{
			$arrRatePlanIds[] = $objService->intPlanId;
		}
		
		$arrRatePlanIds = array_unique($arrRatePlanIds);
		
		$strWhere		= "Id IN (". implode(", ", $arrRatePlanIds) .")";
		$selRatePlans	= new StatementSelect("RatePlan", "Id, ServiceType, CarrierFullService, CarrierPreselection, Archived", $strWhere, "Id");
		$mixResult		= $selRatePlans->Execute();
		if ($mixResult === FALSE || count($arrRatePlanIds) != $mixResult)
		{
			// At least one of the Plans declared for the Services could not be retrieved
			Ajax()->AddCommand("Alert", "ERROR: Could not retrieve all the Plans required.  Bulk service creation aborted.  Please report this to the system administrators");
			return TRUE;
		}
		
		// Convert the retrieved RatePlan records into an Array where the Id of the RatePlan is the key
		$arrRecordSet = $selRatePlans->FetchAll();
		$arrRatePlans = Array();
		foreach ($arrRecordSet as $arrRecord)
		{
			$arrRatePlans[$arrRecord['Id']] = $arrRecord;
		}
		
		// Declare variables that are common amoungst the services being added
		$strNowDateTime		= GetCurrentISODateTime();
		$strNowDate			= substr($strNowDateTime, 0, 10);
		$intEmployeeId		= AuthenticatedUser()->_arrUser['Id'];
		$intAccountGroup	= DBO()->Account->AccountGroup->Value;
		$intAccount			= DBO()->Account->Id->Value;
		
		// Validate And Prepare the details of each service
		$arrServicesDetails = Array();
		foreach ($arrServices as $intIndex=>$objService)
		{
			// If the Account is pending activation, then the status has to be SERVICE_PENDING
			$intServiceStatus = (DBO()->Account->Archived->Value != ACCOUNT_STATUS_PENDING_ACTIVATION && $objService->bolActive)? SERVICE_ACTIVE : SERVICE_PENDING;
			
			$arrServiceRec = Array("FNN"				=> $objService->strFNN,
									"ServiceType"		=> $objService->intServiceType,
									"Indial100"			=> 0,
									"AccountGroup"		=> $intAccountGroup,
									"Account"			=> $intAccount,
									"CostCentre"		=> ($objService->intCostCentre)? $objService->intCostCentre : NULL,
									"CreatedOn"			=> $strNowDateTime,
									"CreatedBy"			=> $intEmployeeId,
									"NatureOfCreation"	=> SERVICE_CREATION_NEW,
									"Carrier"			=> $arrRatePlans[$objService->intPlanId]['CarrierFullService'],
									"CarrierPreselect"	=> $arrRatePlans[$objService->intPlanId]['CarrierPreselection'],
									"Status"			=> $intServiceStatus,
									"Dealer"			=> ($objService->intDealer)? $objService->intDealer : NULL,
									"Cost"				=> $objService->fltCost
								);
								
			switch ($objService->intServiceType)
			{
				case SERVICE_TYPE_MOBILE:
					// Validate Mobile Details
					if ($objService->strDOB != "")
					{
						if (!Validate("ShortDate", $objService->strDOB))
						{
							// DOB has been supplied but is not a valid date in the past
							Ajax()->AddCommand("Alert", "ERROR: Service: {$objService->strFNN}, has DOB incorrectly specified.  Bulk service creation aborted.");
							return TRUE;
						}
						// Convert the user's date into the MySql date format
						$strDOB = substr($objService->strDOB, 6, 4) ."-". substr($objService->strDOB, 3, 2) ."-". substr($objService->strDOB, 0, 2);
					}
					else
					{
						$strDOB = "";
					}
					
					// Prepare Mobile Details
					$arrExtraDetailsRec = Array("Account"		=> $intAccount,
												"AccountGroup"	=> $intAccountGroup,
												"SimPUK"		=> $objService->strSimPUK,
												"SimESN"		=> $objService->strSimESN,
												"SimState"		=> $objService->strSimState,
												"DOB"			=> $strDOB,
												"Comments"		=> $objService->strComments
											);
					break;
					
				case SERVICE_TYPE_INBOUND:
					// No validation required
					$arrExtraDetailsRec = Array("AnswerPoint"	=> $objService->strAnswerPoint,
												"Configuration"	=> $objService->strConfiguration
											);
					break;
					
				case SERVICE_TYPE_ADSL:
					// No validation required
					$arrExtraDetailsRec = NULL;
					break;
				
				case SERVICE_TYPE_LAND_LINE:
					$arrServiceRec['Indial100']	= ($objService->bolIndial100) ? 1 : 0;
					$arrServiceRec['ELB']		= ($objService->bolIndial100 && $objService->bolELB)? TRUE : FALSE;
					
					
					// Check that the AuthorisationDate date is not in the future, and is no more than 30 days in the past
					$intNowDate		= strtotime($strNowDate);
					$intAuthDate	= strtotime(ConvertUserDateToMySqlDate($objService->strAuthorisationDate));
					if ((!Validate("ShortDate", $objService->strAuthorisationDate)) || ($intAuthDate > $intNowDate || $intAuthDate <= strtotime("-30 days", $intNowDate))) 
					{
						// Authorisation Date is incorrect
						Ajax()->AddCommand("Alert", "ERROR: Service: {$objService->strFNN}, has invalid Authorisation Date.  Authorisation Date must be in the format dd/mm/yyyy and must be within the last 30 days.  Bulk service creation aborted.");
						return TRUE;
					}
					
					$arrServiceRec['AuthorisationDate'] = ConvertUserDateToMySqlDate($objService->strAuthorisationDate);
					
					// Validate the AddressDetails
					// Build a DBObject for the ServiceAddress record so that we can use
					$dboServiceAddress = new DBObject("ServiceAddress");
					$objService->objAddressDetails->Residential = (int)$objService->objAddressDetails->Residential;
					foreach ($objService->objAddressDetails as $strProperty=>$mixValue)
					{
						$dboServiceAddress->{$strProperty} = $mixValue;
					}
					$arrProblems = Array();
					$bolAddressValid = $this->ValidateAndCleanServiceAddress($dboServiceAddress, $arrProblems);
					
					if (!$bolAddressValid)
					{
						// The Service Address record is invalid
						$strProblems = implode("<br />", $arrProblems);
						Ajax()->AddCommand("Alert", "ERROR: Bulk service creation aborted.  The following problems were found with the address details for service: {$objService->strFNN}<br />$strProblems");
						return TRUE;
					}
					
					$arrExtraDetailsRec = Array("Account" => $intAccount, "AccountGroup" => $intAccountGroup);
					foreach ($dboServiceAddress as $strProperty=>$objProperty)
					{
						$arrExtraDetailsRec[$strProperty] = $objProperty->Value;
					}
					break;
				
				default:
					// This should never happen
					Ajax()->AddCommand("Alert", "ERROR: Could not handle service {$objService->strFNN} as it is of unknown ServiceType: {$objService->intServiceType}.  Bulk service creation aborted");
					return TRUE;
					break;
			}
			
			// Add all the details of this service to the array
			$arrServicesDetails[] = Array("ServiceRec" => $arrServiceRec,
											"ExtraDetailsRec" => $arrExtraDetailsRec,
											"PlanId" => $objService->intPlanId
											);
		}
		
		// Now add each Service to the database
		TransactionStart();
		
		$arrColumns = Array("FNN"				=> NULL,
							"ServiceType"		=> NULL,
							"Indial100"			=> NULL,
							"AccountGroup"		=> NULL,
							"Account"			=> NULL,
							"CostCentre"		=> NULL,
							"CreatedOn"			=> NULL,
							"CreatedBy"			=> NULL,
							"NatureOfCreation"	=> NULL,
							"Carrier"			=> NULL,
							"CarrierPreselect"	=> NULL,
							"Status"			=> NULL,
							"Dealer"			=> NULL,
							"Cost"				=> 0
							);
		
		$insService = new StatementInsert("Service", $arrColumns);
		
		$arrServicesToProvision = Array();
		$strNote = "New services created:";
		foreach ($arrServicesDetails as $arrServiceDetails)
		{
			$arrServiceRec		= $arrServiceDetails['ServiceRec'];
			$arrExtraDetails	= $arrServiceDetails['ExtraDetailsRec'];
			$intPlanId			= $arrServiceDetails['PlanId'];
			$strStatus			= GetConstantDescription($arrServiceRec['Status'], "service_status");
			$strNote .= "\n". GetConstantDescription($arrServiceRec['ServiceType'], "service_type") ." - {$arrServiceRec['FNN']} ($strStatus)";
			
			$mixResult = $insService->Execute($arrServiceRec);
			if ($mixResult === FALSE)
			{
				// Inserting the Service Record failed
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Adding Service: {$arrServiceRec['FNN']}, failed unexpectedly.  Process Aborted. (Error adding record to the Service table)");
				return TRUE;
			}
			$intServiceId = $mixResult;
			
			// The ExtraDetails record will require the Service Id
			$arrExtraDetails['Service'] = $intServiceId;
			
			$bolOk = TRUE;
			switch ($arrServiceRec['ServiceType'])
			{
				case SERVICE_TYPE_MOBILE:
					if (!isset($insMobile))
					{
						// Declare the StatementInsert object, as it has not been declared yet
						$insMobile = new StatementInsert("ServiceMobileDetail", $arrExtraDetails);
					}
					$bolOk = $insMobile->Execute($arrExtraDetails);
					if ($bolOk === FALSE)
					{
						$strErrorMsg = "ERROR: Adding the Mobile specific details for Service: {$arrServiceRec['FNN']}, failed unexpectedly.  Process Aborted. (Error adding record to the ServiceMobileDetail table)";
					}
					break;
					
				case SERVICE_TYPE_INBOUND:
					if (!isset($insInbound))
					{
						// Declare the StatementInsert object, as it has not been declared yet
						$insInbound = new StatementInsert("ServiceInboundDetail", $arrExtraDetails);
					}
					$bolOk = $insInbound->Execute($arrExtraDetails);
					if ($bolOk === FALSE)
					{
						$strErrorMsg = "ERROR: Adding the Inbound specific details for Service: {$arrServiceRec['FNN']}, failed unexpectedly.  Process Aborted. (Error adding record to the ServiceInboundDetail table)";
					}
					break;
					
				case SERVICE_TYPE_LAND_LINE:
					// Add this service to the list of those to provision, but
					// only if the Service will be immediately activated
					if ($arrServiceRec['Status'] == SERVICE_ACTIVE)
					{
						$arrServicesToProvision[] = Array(	"Id"				=> $intServiceId,
															"FNN"				=> $arrServiceRec['FNN'],
															"Carrier"			=> $arrServiceRec['Carrier'],
															"CarrierPreselect"	=> $arrServiceRec['CarrierPreselect'],
															"AuthorisationDate"	=> $arrServiceRec['AuthorisationDate']
														);
					}
					if (!isset($insLandLine))
					{
						// Declare the StatementInsert object, as it has not been declared yet
						$insLandLine = new StatementInsert("ServiceAddress", $arrExtraDetails);
					}
					$bolOk = $insLandLine->Execute($arrExtraDetails);
					if ($bolOk === FALSE)
					{
						$strErrorMsg = "ERROR: Adding the Land Line specific details for Service: {$arrServiceRec['FNN']}, failed unexpectedly.  Process Aborted. (Error adding record to the ServiceAddress table)";
						break;
					}
					if ($arrServiceRec['Indial100'] && $arrServiceRec['ELB'])
					{
						// Enable ELB
						if (!($this->Framework->EnableELB($intServiceId)))
						{
							// EnableELB failed
							$bolOk = FALSE;
							$strErrorMsg = "ERROR: Adding the Land Line specific details for Service: {$arrServiceRec['FNN']}, failed unexpectedly.  Process Aborted. (Error enabling ELB)";
						}
					}
					break;
					
				case SERVICE_TYPE_ADSL:
					// Don't have to do anything
					break;
			}
			
			if ($bolOk === FALSE)
			{
				// An error has occurred
				TransactionRollback();
				Ajax()->AddCommand("Alert", $strErrorMsg);
				return TRUE;
			}
			
			// Add the plan details
			$bolOk = $this->_SetPlan($intPlanId, $intServiceId, $strNowDateTime, $strNowDateTime);
			if ($bolOk === FALSE)
			{
				// The plan could not be added
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Declaring plan for Service: {$arrServiceRec['FNN']}, failed unexpectedly.  Process Aborted.");
				return TRUE;
			}
		}
		
		// Commit the transaction
		TransactionCommit();
		
		// Perform all Automatic provisioning
		if (count($arrServicesToProvision) > 0)
		{
			
			// Prepare the object to add the provisioning requests
			$arrInsertValues = Array(	"AccountGroup"		=> DBO()->Account->AccountGroup->Value,
										"Account"			=> DBO()->Account->Id->Value,
										"Service"			=> NULL,
										"FNN"				=> NULL,
										"Employee"			=> AuthenticatedUser()->_arrUser['Id'],
										"Carrier"			=> NULL,
										"Type"				=> NULL,
										"RequestedOn"		=> $strNowDateTime,
										"AuthorisationDate"	=> NULL, 
										"Status"			=> REQUEST_STATUS_WAITING
									);
			$insRequest = new StatementInsert("ProvisioningRequest", $arrInsertValues);
			
			TransactionStart();
			$bolSuccess = TRUE;
			foreach ($arrServicesToProvision as $arrService)
			{
				$arrInsertValues['Service']				= $arrService['Id'];
				$arrInsertValues['FNN']					= $arrService['FNN'];
				$arrInsertValues['AuthorisationDate']	= $arrService['AuthorisationDate'];
				
				// Full Service Request
				$arrInsertValues['Carrier']	= $arrService['Carrier'];
				$arrInsertValues['Type']	= PROVISIONING_TYPE_FULL_SERVICE;
				
				if ($insRequest->Execute($arrInsertValues) === FALSE)
				{
					$bolSuccess = FALSE;
					break;
				}
				
				// Preselection Request				
				$arrInsertValues['Carrier'] = $arrService['CarrierPreselect'];
				$arrInsertValues['Type']	= PROVISIONING_TYPE_PRESELECTION;
				
				if ($insRequest->Execute($arrInsertValues) === FALSE)
				{
					$bolSuccess = FALSE;
					break;
				}
			}
			
			if (!$bolSuccess)
			{
				// An Error Occured
				TransactionRollback();
				Ajax()->AddCommand("Alert", "Service creation was successful, however automatic provisioning failed.  None of the services have had provisioning requests made.  Please notify your system administrator.");
				return TRUE;
			}
			TransactionCommit();
		}
		
		// If any of this fails, notify the user
		// TODO! We should also fire off an Email to our Tech Support email account
		
		// Add a system note
		SaveSystemNote($strNote, DBO()->Account->AccountGroup->Value, DBO()->Account->Id->Value);
		
		// Success
		Ajax()->AddCommand("Alert", "Service creation was successful");
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _SetPlan
	//------------------------------------------------------------------------//
	/**
	 * _SetPlan()
	 *
	 * Declares a new plan for a service (handles all database interactions)
	 * 
	 * Declares a new plan for a service (handles all database interactions)
	 * Note the this MUST ALWAYS be used within a Database Transaction, and if this function
	 * returns FALSE (signifying failure) the transaction should be rolled back.
	 * This function does not currently handle automatic note generation or automatic provisioning
	 * 
	 * @param	int		$intPlan			Id of the plan to assign to the service
	 * @param	int		$intService			Id of the Service
	 * @param	string	$strCreatedOn		optional, DateTime string representing when the plan
	 * 										was assigned to the service.  If set to NULL (default)
	 * 										It will use the current time of the Database Server
	 * @param	string	$strStartDatetime	optional, DateTime string representing when the plan
	 * 										comes into effect.  If set to NULL (default)
	 * 										It will use $strCreatedOn
	 * 
	 * @param	bool	$bolUpdateServiceCarrierDetails		optional, if set to TRUE then the Carriers details stored in the Service
	 * 														record will be updated with those of the RatePlan record
	 * @param	bool	$bolRerateCDRs		optional, if set to TRUE then all CDRs belonging to the Service that have
	 * 										Status = CDR_RATED will be updated to CDR_NORMALISED so that they will get
	 * 										rerated.  If the StartDatetime is in the past, then it is recommended that
	 * 										this variable be set to TRUE
	 * @param	bool	$bolActive			optional, defaults to TRUE.  This value will be used for the "Active" property
	 * 										of the ServiceRateGroup and ServiceRatePlan tables.  It is recommended that it 
	 * 										only be set to FALSE if the Plan is supposed to come into effect at a future date
	 *
	 * @return	bool						Returns FALSE if any of the database interactions fail
	 * 										Returns TRUE on success
	 * @method		_SetPlan
	 */
	private function _SetPlan($intPlan, $intService, $strCreatedOn=NULL, $strStartDatetime=NULL, $bolUpdateServiceCarrierDetails=NULL, $bolRerateCDRs=NULL, $bolActive=TRUE)
	{
		static $insServiceRatePlan;
		static $insServiceRateGroup;
		static $updCDR;
		static $updService;
		static $updServiceRatePlan;
		static $updServiceRateGroup;
		static $selRateGroup;
		static $selRatePlan;

		$strCreatedOn		= ($strCreatedOn === NULL)? GetCurrentDateAndTimeForMySQL() : $strCreatedOn;
		$strStartDatetime	= ($strStartDatetime === NULL)? $strCreatedOn : $strStartDatetime;
		$strEndDatetime		= END_OF_TIME;
		$intEmployeeId		= AuthenticatedUser()->_arrUser['Id'];

		// Retrieve the Ids of all the RateGroups belonging to the RatePlan
		if (!isset($selRateGroup))
		{
			$selRateGroup = new StatementSelect("RatePlanRateGroup", "RateGroup", "RatePlan = <RatePlan>", "RateGroup");
		}

		if ($selRateGroup->Execute(Array("RatePlan" => $intPlan)) === FALSE)
		{
			return FALSE;
		}
		$arrRateGroups = $selRateGroup->FetchAll(); 
		
		// Retrieve the RatePlan record
		if (!isset($selRatePlan))
		{
			$selRatePlan = new StatementSelect("RatePlan", "*", "Id = <RatePlan>");
		}

		if ($selRatePlan->Execute(Array("RatePlan" => $intPlan)) != 1)
		{
			return FALSE;
		}
		$arrRatePlan = $selRatePlan->Fetch();


		// Update all the records currently in the ServiceRateGroup table so that any that end after the new one begins, now end 1 second before the new one begins
		$arrColumns	= Array("EndDatetime"=> new MySQLFunction("SUBTIME('$strStartDatetime', SEC_TO_TIME(1))"));
		$strWhere	= "Service = <ServiceId> AND EndDatetime >= <StartDatetime>";
		$arrWhere	= Array("ServiceId" => $intService, "StartDatetime" => $strStartDatetime);
		if (!isset($updServiceRateGroup))
		{
			$updServiceRateGroup = new StatementUpdate("ServiceRateGroup", $strWhere, $arrColumns);
		}
		
		if ($updServiceRateGroup->Execute($arrColumns, $arrWhere) === FALSE)
		{
			return FALSE;
		}
		
		// Update all the records currently in the ServiceRatePlan table so that any that end after the new one begins, now end 1 second before the new one begins
		if (!isset($updServiceRatePlan))
		{
			$updServiceRatePlan = new StatementUpdate("ServiceRatePlan", $strWhere, $arrColumns);
		}

		if ($updServiceRatePlan->Execute($arrColumns, $arrWhere) === FALSE)
		{
			return FALSE;
		}

		// Insert the new record into the ServiceRatePlan table
		$arrColumns = Array(	"Service" => $intService,
								"RatePlan" => $intPlan,
								"CreatedBy" => $intEmployeeId,
								"CreatedOn" => $strCreatedOn,
								"StartDatetime" => $strStartDatetime,
								"EndDatetime" => $strEndDatetime,
								"Active" => ($bolActive)? 1 : 0
							);
		if (!isset($insServiceRatePlan))
		{
			$insServiceRatePlan = new StatementInsert("ServiceRatePlan", $arrColumns);
		}
		
		if ($insServiceRatePlan->Execute($arrColumns) === FALSE)
		{
			return FALSE;
		}
		
		// Insert the new records into the ServiceRateGroup table
		unset($arrColumns['RatePlan']);
		$arrColumns['RateGroup'] = NULL;
		if (!isset($insServiceRateGroup))
		{
			$insServiceRateGroup = new StatementInsert("ServiceRateGroup", $arrColumns);
		}
		foreach ($arrRateGroups as $arrRateGroup)
		{
			$arrColumns['RateGroup'] = $arrRateGroup['RateGroup'];

			if ($insServiceRateGroup->Execute($arrColumns) === FALSE)
			{
				return FALSE;
			}
		}
		
		// Update the Carrier details in the Service table (if specified to do so)
		if ($bolUpdateServiceCarrierDetails)
		{
			$arrColumns = Array(	"Id" => $intService, 
									"Carrier" => $arrRatePlan['CarrierFullService'], 
									"CarrierPreselect" => $arrRatePlan['CarrierPreselection']
								);
			if (!isset($updService))
			{
				$updService = new StatementUpdateById("Service", $arrColumns);
			}
			
			if ($updService->Execute($arrColumns) === FALSE)
			{
				return FALSE;
			}
		}
		
		// Update the CDRs in the CDR table (if specified to do so)
		if ($bolRerateCDRs)
		{
			$arrColumns	= Array("Status" => CDR_NORMALISED);
			$strWhere	= "Service = <ServiceId> AND Status = ". CDR_RATED;
			$arrWhere	= Array("ServiceId" => $intService);
			if (!isset($updCDR))
			{
				$updCDR = new StatementUpdate("CDR", $strWhere, $arrColumns);
			}
			
			if ($updCDR->Execute($arrColumns, $arrWhere) === FALSE)
			{
				return FALSE;
			}
		}

		// I think that's it
		return TRUE;
	}
	
	
	// Checks the array of objects, $arrServices, for duplicates within the array, and then in the database
	// returns javascript to execute if there are duplicates, else returns NULL if they are all valid
	// It is assumed that all the FNNs are already valid Australian FNNs
	function _BulkValidateServices($arrServices)
	{
		// Check that there are not duplicate numbers in the list, or FNNs in the 
		// Indial100 range of any newly specified Indial100 landlines
		// This has already been done in javascript, but I want to check again
		$arrInvalidServiceIndexes = array();
		for ($i = 0; $i < count($arrServices); $i++)
		{
			$bolIndial100 = FALSE;
			if (isset($arrServices[$i]->bolIndial100) && $arrServices[$i]->bolIndial100 == TRUE)
			{
				// The service is an Indial100
				$bolIndial100 = TRUE;
				$strFNNIndial = substr($arrServices[$i]->strFNN, 0, -2);
			}
			
			for ($j = 0; $j < count($arrServices); $j++)
			{
				if ($j == $i)
				{
					continue;
				}
				
				if (($arrServices[$i]->strFNN == $arrServices[$j]->strFNN) || ($bolIndial100 && $strFNNIndial == substr($arrServices[$j]->strFNN, 0, -2)))
				{
					// Add these services to the list of invalid ones
					$arrInvalidServiceIndexes[] = $arrServices[$i]->intArrayIndex; 
					$arrInvalidServiceIndexes[] = $arrServices[$j]->intArrayIndex;
				}
			}
		}
		// Remove any duplicates from the array
		$arrInvalidServiceIndexes = array_unique($arrInvalidServiceIndexes);
		
		if (count($arrInvalidServiceIndexes) > 0)
		{
			// At least 2 of the new services have the same FNN
			$jsonInvalidServiceIndexes = Json()->encode($arrInvalidServiceIndexes);
			$strJs = "Vixen.ServiceBulkAdd.ValidateServicesReturnHandler(false, $jsonInvalidServiceIndexes, 'ERROR: Duplicate services are highlighted');";
			return $strJs;
		}
		
		// Check that none of these new numbers are already in the database
		$strNow		= GetCurrentDateForMySQL();
		$arrFNNs	= Array();
		foreach ($arrServices as $objService)
		{
			if (IsFNNInUse($objService->strFNN, (isset($objService->bolIndial100) && $objService->bolIndial100 == TRUE), $strNow))
			{
				// The FNN is currently being used
				$arrInvalidServiceIndexes[] = $objService->intArrayIndex;
			}
		}
		if (count($arrInvalidServiceIndexes) > 0)
		{
			$jsonInvalidServiceIndexes = Json()->encode($arrInvalidServiceIndexes);
			$strJs = "Vixen.ServiceBulkAdd.ValidateServicesReturnHandler(false, $jsonInvalidServiceIndexes, 'ERROR: highlighted FNNs are currently active in the database and can not be used for new services');";
			return $strJs;
		}
		
		// Check that each Service has a plan declared
		foreach ($arrServices as $objService)
		{
			if ($objService->intPlanId == NULL)
			{
				// The service doesn't have a plan specified
				$arrInvalidServiceIndexes[] = $objService->intArrayIndex;
			}
		}
		if (count($arrInvalidServiceIndexes) > 0)
		{
			$jsonInvalidServiceIndexes = Json()->encode($arrInvalidServiceIndexes);
			$strJs = "Vixen.ServiceBulkAdd.ValidateServicesReturnHandler(false, $jsonInvalidServiceIndexes, 'ERROR: Plans must be specified');";
			return $strJs;
		}
		
		// All FNNs are valid
		return NULL;
	}
	
	//------------------------------------------------------------------------//
	// LoadExtraDetailsPopup
	//------------------------------------------------------------------------//
	/**
	 * LoadExtraDetailsPopup()
	 *
	 * Loads the ExtraDetailsPopup specific to the ServiceType of the Service passed to this function
	 * 
	 * Loads the ExtraDetailsPopup specific to the ServiceType of the Service passed to this function
	 * This is used by the BulkAddServices functionality to declare all the properties that are specific
	 * to the ServiceType of the service
	 * 
	 *
	 * @return		void
	 * @method		LoadExtraDetailsPopup
	 */
	function LoadExtraDetailsPopup()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// Retrieve the array of new services
		// Note that this is an array of objects (structs), not an array of associative arrays
		//$objService = DBO()->Service->Data->Value;

		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
		{
			// Accomodate the extra LaneLine details 
			DBO()->Account->Load();
			
			// Retrieve the address details of each service belonging to this account
			DBO()->Account->AllAddresses = $this->_GetAllServiceAddresses(DBO()->Account->Id->Value);
			
			if (DBO()->Service->AddressDetails->IsSet)
			{
				// The service has already had details defined, load them
				$arrAddressDetails = DBO()->Service->AddressDetails->Value;
				
				foreach ($arrAddressDetails as $strProperty=>$mixValue)
				{
					DBO()->ServiceAddress->{$strProperty} = $mixValue;
				}
				
			}
			else
			{
				// Set Default values for things
				// Don't forget this form has to include controls for the Indial100 checkbox and enabling ELB
				$this->_SetDefaultValuesForServiceAddress(DBO()->ServiceAddress, DBO()->Account);
				DBO()->Service->Indial100 = FALSE;
				DBO()->Service->ELB = FALSE;
				DBO()->Service->AuthorisationDate = date("d/m/Y");
			}
		}
		elseif (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			DBO()->ServiceMobileDetail->SimPUK		= DBO()->Service->SimPUK->Value;
			DBO()->ServiceMobileDetail->SimESN		= DBO()->Service->SimESN->Value;
			DBO()->ServiceMobileDetail->SimState	= DBO()->Service->SimState->Value;
			DBO()->ServiceMobileDetail->DOB			= DBO()->Service->DOB->Value;
			DBO()->ServiceMobileDetail->Comments	= DBO()->Service->Comments->Value;
			
			// Set values that have been passed through
		}
		elseif (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			// Set values that have been passed through
			DBO()->ServiceInboundDetail->AnswerPoint	= DBO()->Service->AnswerPoint->Value;
			DBO()->ServiceInboundDetail->Configuration	= DBO()->Service->Configuration->Value;
		}
		else
		{
			// This shouldn't ever get called
			Ajax()->AddCommand("Alert", "ERROR: AppTemplateService->LoadExtraDetailsPopup: ServiceType '". $objService->intServiceType ."' does not require any extra details defined");
			return TRUE;
		}
		
		$this->LoadPage('service_bulk_add_extra_details');
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Edit
	//------------------------------------------------------------------------//
	/**
	 * Edit()
	 *
	 * Performs the logic for th "Edit Service" popup
	 * 
	 * Performs the logic for th "Edit Service" popup
	 * If the service is successfully updated then it will fire an EVENT_ON_SERVICE_UPDATE event
	 * passing the following object:
	 *		objObject.Service.Id	= id of the service which has been updated
	 *		objObject.NewService.Id	= id of the new service, if the service being updated, 
	 *		was activated, and a new service had to be made.  See KB article KB00005
	 *
	 * @return		void
	 * @method		Edit
	 *
	 */
	function Edit()
	{
		// Check user authorization
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// Services can not be editted while an invoice run is processing
		if (IsInvoicing())
		{
			Ajax()->AddCommand("Alert", "Billing is in progress.  Services cannot be modified while this is happening.  Please try again in a couple of hours.  If this problem persists, please notify your system administrator");
			return TRUE;
		}

		if (SubmittedForm("EditService","Apply Changes"))
		{
			if (DBO()->Service->IsInvalid())
			{
				// The form has not passed initial validation
				Ajax()->AddCommand("Alert", "ERROR: Could not save the service.  Invalid fields are highlighted");
				Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
				return TRUE;
			}
			
			// Retrieve properties of the Service record that arent already set
			DBO()->Service->LoadMerge();
			
			DBO()->Service->FNN = trim(DBO()->Service->FNN->Value);
			DBO()->Service->FNNConfirm = trim(DBO()->Service->FNNConfirm->Value);
			
			// If these have been updated record the details
			if (DBO()->Service->Indial100->Value != DBO()->Service->CurrentIndial100->Value)
			{
				// You can't actually change this property.  It can only be declared when the service is created
				$strChangesNote .= "Indial100" . ((DBO()->Service->Indial100->Value == 1) ? " is set" : " is not set") . "\n";
			}
			if (DBO()->Service->ELB->Value != DBO()->Service->CurrentELB->Value)
			{
				$strChangesNote .= "ELB" . ((DBO()->Service->ELB->Value == 1) ? " is set" : " is not set") . "\n";
			}
			if (DBO()->Service->ForceInvoiceRender->Value != DBO()->Service->CurrentForceInvoiceRender->Value)
			{
				$strChangesNote .= "'Always shown on invoice' has been set to ".  ((DBO()->Service->ForceInvoiceRender->Value)? "'Yes'":"'No'") . "\n";
			}
			
			if (DBO()->Service->FNN->Value != DBO()->Service->CurrentFNN->Value)
			{
				// The user wants to change the FNN
				if (DBO()->Service->FNN->Value != DBO()->Service->FNNConfirm->Value)
				{
					// The FNN was re-entered incorrectly
					DBO()->Service->FNN->SetToInvalid();
					DBO()->Service->FNNConfirm->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: Could not save the service.  Service # and Confirm Service # must be the same");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
				
				// Check that the FFN is valid
				if (!isValidFNN(DBO()->Service->FNN->Value))
				{
					// The FNN is invalid
					DBO()->Service->FNN->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: The FNN is not a valid Australian Full National Number");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
				
				// Make sure the new FNN is valid for the service type
				$intServiceType = ServiceType(DBO()->Service->FNN->Value);
				if ($intServiceType != DBO()->Service->ServiceType->Value)
				{
					// The FNN is invalid for the service's ServiceType.
					DBO()->Service->FNN->SetToInvalid();
					Ajax()->AddCommand("Alert", "ERROR: The FNN is invalid for the service type");
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
				
				// Check that the FNN is not currently being used
				$bolIsIndial = (DBO()->Service->Indial100->Value == TRUE);
				if ($bolIsIndial && (substr(DBO()->Service->FNN->Value, 0, -2) == substr(DBO()->Service->CurrentFNN->Value, 0, -2)))
				{
					// While the FNN has changed, it is still within the original Indial100 range and is therefore considered safe
					$strChangesNote .= "FNN was changed from ". DBO()->Service->CurrentFNN->Value ." to " . DBO()->Service->FNN->Value . "\n";
				}
				elseif (IsFNNInUse(DBO()->Service->FNN->Value, $bolIsIndial, GetCurrentDateForMySQL()))
				{
					// The FNN is in use
					DBO()->Service->FNN->SetToInvalid();
					if ($bolIsIndial)
					{
						$strErrorMsg = "ERROR: At Least 1 of the numbers in this Indial100 range is being used by another service";
					}
					else
					{
						$strErrorMsg = "ERROR: This FNN is currently being used by another service";
					}
					Ajax()->AddCommand("Alert", $strErrorMsg);
					Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
					return TRUE;
				}
				else
				{
					$strChangesNote .= "FNN was changed from ". DBO()->Service->CurrentFNN->Value ." to " . DBO()->Service->FNN->Value . "\n";
					$strWarningForFNNChange = "WARNING: The FNN has been changed.  Any provisioning requests, that have not been sent yet, should be cancelled.";
				}
			}
			
			// Check if the CostCentre property has been updated
			if (DBO()->Service->CostCentre->Value !== NULL)
			{
				// If CostCentre == 0 then set it to NULL
				if (DBO()->Service->CostCentre->Value == 0)
				{
					DBO()->Service->CostCentre = NULL;
				}
				
				// Check if the value of the Service's cost centre property has been changed
				if (DBO()->Service->CostCentre->Value != DBO()->Service->CurrentCostCentre->Value)
				{
					// Work out what to stick in the System Note
					if (DBO()->Service->CostCentre->Value == NULL)
					{
						// Now there is no CostCentre associated with the service
						$strChangesNote .= "Now there is no CostCentre associated with this service\n";
					}
					else
					{
						// Retrieve the name of the CostCentre
						DBO()->CostCentre->Id = DBO()->Service->CostCentre->Value;
						DBO()->CostCentre->Load();
						
						// Retrieve the name of the last CostCentre
						if (DBO()->Service->CurrentCostCentre->Value == NULL)
						{
							$strChangesNote .= "CostCentre has been set to '". DBO()->CostCentre->Name->Value ."'\n";
						}
						else
						{
							DBO()->CurrentCostCentre->SetTable("CostCentre");
							DBO()->CurrentCostCentre->Id = DBO()->Service->CurrentCostCentre->Value;
							DBO()->CurrentCostCentre->Load();
							$strChangesNote .= "CostCentre has changed from '". DBO()->CurrentCostCentre->Name->Value ."' to '". DBO()->CostCentre->Name->Value ."'\n";
						}
					}
				}
			}

			// Declare the transaction
			TransactionStart();

			// Check if the Extension Level Billing property has been updated
			if (DBO()->Service->ELB->Value !== NULL)
			{
				if (DBO()->Service->ELB->Value)
				{
					// Enable ELB
					if (!($this->Framework->EnableELB(DBO()->Service->Id->Value)))
					{
						// EnableELB failed
						TransactionRollback();
						Ajax()->AddCommand("Alert", "ERROR: Enabling ELB failed, unexpectedly.  All modifications to the service have been aborted");
						return TRUE;
					}
				}
				else
				{
					// Disable ELB
					if (!($this->Framework->DisableELB(DBO()->Service->Id->Value)))
					{
						// DisableELB failed
						TransactionRollback();
						Ajax()->AddCommand("Alert", "ERROR: Disabling ELB failed, unexpectedly.  All modifications to the service have been aborted");
						return TRUE;
					}
				}
			}
			
			// If the FNN is changing and the service has ELB, then you will have to update the the FNNs in the ServiceExtension table
			// relating to this service
			if ((DBO()->Service->FNN->Value != DBO()->Service->CurrentFNN->Value) && (DBO()->Service->ELB->Value !== NULL))
			{
				// Update the records in the ServiceExtension table that relate to this Service
				$strFirstFNN = substr(DBO()->Service->FNN->Value, 0, 8) . "00";
				$strQuery = "UPDATE ServiceExtension SET Name = LPAD('$strFirstFNN' + RangeStart, 10, '0') WHERE Service = ". DBO()->Service->Id->Value;

				$qryUpdateServiceExtension = new Query();
				if ($qryUpdateServiceExtension->Execute($strQuery) == FALSE)
				{
					// Updating the ServiceExtension table failed, unexpectedly
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Updating the FNNs in the ServiceExtension table failed, unexpectedly.  All modifications to the service have been aborted");
					return TRUE;
				}
			}

			// Save the changes to the Service Table, if any changes were made
			if (!DBO()->Service->Save())
			{
				// The service did not save
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Updating the service details failed, unexpectedly.  All modifications to the service have been aborted");
				return TRUE;
			}

			// Handle mobile phone details			
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
			{
				// Load the current ServiceMobileDetail record
				DBO()->CurrentServiceMobileDetail->Where->Service = DBO()->Service->Id->Value;
				DBO()->CurrentServiceMobileDetail->SetTable("ServiceMobileDetail");
				$bolRecordFound = DBO()->CurrentServiceMobileDetail->Load();
				
				if ($bolRecordFound)
				{
					// The service already has a ServiceMobileDetail record associated with it
					// Check if anything has changed
					if (DBO()->ServiceMobileDetail->SimPUK->Value != DBO()->CurrentServiceMobileDetail->SimPUK->Value)
					{
						$strChangesNote .= "SimPUK was changed from '". DBO()->CurrentServiceMobileDetail->SimPUK->Value ."' to '" . DBO()->ServiceMobileDetail->SimPUK->Value ."'\n";
					}
					if (DBO()->ServiceMobileDetail->SimESN->Value != DBO()->CurrentServiceMobileDetail->SimESN->Value)
					{
						$strChangesNote .= "SimESN was changed from '". DBO()->CurrentServiceMobileDetail->SimESN->Value ."' to '" . DBO()->ServiceMobileDetail->SimESN->Value ."'\n";				
					}
					if (DBO()->ServiceMobileDetail->SimState->Value != DBO()->CurrentServiceMobileDetail->SimState->Value)
					{
						$strChangesNote .= "SimState was changed from '". 
											GetConstantDescription(DBO()->CurrentServiceMobileDetail->SimState->Value, 'ServiceStateType') . 
											"' to '" . GetConstantDescription(DBO()->ServiceMobileDetail->SimState->Value, 'ServiceStateType') . "'\n";
					}
					if (DBO()->ServiceMobileDetail->Comments->Value != DBO()->CurrentServiceMobileDetail->Comments->Value)
					{
						$strChangesNote .= "Comments were changed from '". DBO()->CurrentServiceMobileDetail->Comments->Value . 
											"' to '" . DBO()->ServiceMobileDetail->Comments->Value ."'\n";
					}
				}
				else
				{
					// The service does not already have a ServiceMobileDetail record associated with it
					// Only add details to the note, if something has actually been defined
					if (DBO()->ServiceMobileDetail->SimPUK->Value)
					{
						$strChangesNote .= "SimPUK has been defined\n";
					}
					if (DBO()->ServiceMobileDetail->SimESN->Value)
					{
						$strChangesNote .= "SimESN has been defined\n";				
					}
					if (DBO()->ServiceMobileDetail->SimState->Value)
					{
						$strChangesNote .= "SimState has been defined\n";
					}
					if (DBO()->ServiceMobileDetail->Comments->Value)
					{
						$strChangesNote .= "Mobile Comments have been defined\n";
					}
				}
			
				// Validate entered Birth Date
				if (trim(DBO()->ServiceMobileDetail->DOB->Value) != "")
				{
					// A date of birth has been specified
					if (!Validate("ShortDate", DBO()->ServiceMobileDetail->DOB->Value))
					{
						TransactionRollback();
						DBO()->ServiceMobileDetail->DOB->SetToInvalid();
						Ajax()->AddCommand("Alert", "ERROR: This is not a valid Date, please use DD/MM/YYYY");
						Ajax()->RenderHtmlTemplate("ServiceEdit", HTML_CONTEXT_DEFAULT, $this->_objAjax->strContainerDivId, $this->_objAjax);
						return TRUE;
					}
					
					// Set DOB to MySql date format
					DBO()->ServiceMobileDetail->DOB = ConvertUserDateToMySqlDate(DBO()->ServiceMobileDetail->DOB->Value);
					if ($bolRecordFound)
					{
						if (DBO()->ServiceMobileDetail->DOB->Value != DBO()->CurrentServiceMobileDetail->DOB->Value)
						{
							$strChangesNote .= "DOB was changed from '". OutputMask()->ShortDate(DBO()->CurrentServiceMobileDetail->DOB->Value) ."' to '" . DBO()->ServiceMobileDetail->DOB->FormattedValue() ."'\n";			
						}
					}
					else
					{
						$strChangesNote .= "DOB was changed to '". DBO()->ServiceMobileDetail->DOB->FormattedValue() ."'\n";
					}
				}
				else
				{
					// A date of Birth has not been specified
					DBO()->ServiceMobileDetail->DOB = NULL;
				}

				// If Id is passed set the columns to Update
				if ($bolRecordFound)
				{
					DBO()->ServiceMobileDetail->Id = DBO()->CurrentServiceMobileDetail->Id->Value;
					
					// Update the existing MobileServiceDetail Record
					DBO()->ServiceMobileDetail->SetColumns("SimPUK, SimESN, SimState, DOB, Comments");
				}
				else
				{
					// Create a new MobileServiceDetail Record
					DBO()->ServiceMobileDetail->SetColumns("Id, AccountGroup, Account, Service, SimPUK, SimESN, SimState, DOB, Comments");
					DBO()->ServiceMobileDetail->Id = 0;
					DBO()->ServiceMobileDetail->AccountGroup = DBO()->Service->AccountGroup->Value;
					DBO()->ServiceMobileDetail->Account = DBO()->Service->Account->Value;
					DBO()->ServiceMobileDetail->Service = DBO()->Service->Id->Value;					
				}

				if (DBO()->ServiceMobileDetail->SimPUK->Value == NULL)
				{
					DBO()->ServiceMobileDetail->SimPUK  = "";
				}
				if (DBO()->ServiceMobileDetail->SimESN->Value == NULL)
				{
					DBO()->ServiceMobileDetail->SimESN  = "";
				}
				if (DBO()->ServiceMobileDetail->SimState->Value == NULL)
				{
					DBO()->ServiceMobileDetail->SimState  = "";
				}
				if (DBO()->ServiceMobileDetail->DOB->Value == NULL)
				{
					DBO()->ServiceMobileDetail->DOB = "0000-00-00";
				}
				if (DBO()->ServiceMobileDetail->Comments->Value == NULL)
				{
					DBO()->ServiceMobileDetail->Comments  = "";
				}
				
				if (!DBO()->ServiceMobileDetail->Save())
				{
					// The ServiceMobileDetail did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Updating the mobile details failed, unexpectedly.  All modifications to the service have been aborted");
					return TRUE;
				}
				// The mobile details saved successfully
			}
			
			// Handle inbound call details
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
			{
				// Has anything been changed in the ServiceInboundDetails if so append this information onto $strChangesNote
				if (DBO()->ServiceInboundDetail->AnswerPoint->Value != DBO()->ServiceInboundDetail->CurrentAnswerPoint->Value)
				{
					$strChangesNote = "AnswerPoint was changed to: " . DBO()->ServiceInboundDetail->AnswerPoint->Value ."\n";
				}
				if (DBO()->ServiceInboundDetail->Configuration->Value != DBO()->ServiceInboundDetail->CurrentConfiguration->Value)
				{
					$strChangesNote = "Configuration was changed to: " . DBO()->ServiceInboundDetail->Configuration->Value ."\n";					
				}
			
				// If Id is passed set the columns to Update
				if (DBO()->ServiceInboundDetail->Id->Value)
				{
					// Update the existing ServiceInboundDetail Record
					DBO()->ServiceInboundDetail->SetColumns("AnswerPoint, Configuration");
				}
				else
				{
					// Create a new ServiceInboundDetail Record
					DBO()->ServiceInboundDetail->SetColumns("Id, Service, AnswerPoint, Complex, Configuration");
					DBO()->ServiceInboundDetail->Id = 0;
					DBO()->ServiceInboundDetail->Service = DBO()->Service->Id->Value;
				}

				if (DBO()->ServiceInboundDetail->AnswerPoint->Value == NULL)
				{
					DBO()->ServiceInboundDetail->AnswerPoint  = "";
				}
				if (DBO()->ServiceInboundDetail->Complex->Value == NULL)
				{
					DBO()->ServiceInboundDetail->Complex  = 0;
				}
				if (DBO()->ServiceInboundDetail->Configuration->Value == NULL)
				{
					DBO()->ServiceInboundDetail->Configuration  = "";
				}

				if (!DBO()->ServiceInboundDetail->Save())
				{
					// The InboundDetail did not save
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Updating the inbound details failed, unexpectedly.  All modifications to the service have been aborted");
					return TRUE;
				}
				// The inbound details saved successfully
			}
			
			// All details regarding the service have been successfully updated

			// Handle updating the Service Status
			// First check that the Service Status has actually changed
			if (DBO()->Service->NewStatus->Value != DBO()->Service->Status->Value)
			{
				$objService = ModuleService::GetServiceById(DBO()->Service->Id->Value, DBO()->Service->ServiceType->Value);
				if ($objService === FALSE)
				{
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Could not create the Service object required to handle the Status change.  All modifications to the service have been aborted");
					return TRUE;
				}
				if ($objService === NULL)
				{
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Could not create the Service object required to handle the Status change, because a service record with Id '". DBO()->Service->Id->Value ."' could not be found in the database.  All modifications to the service have been aborted");
					return TRUE;
				}
				if (!is_object($objService))
				{
					TransactionRollback();
					Ajax()->AddCommand("Alert", "<pre>ERROR:\n". print_r($objService, TRUE) ."</pre>");
					return TRUE;
				}
				
				if ($objService->ChangeStatus(DBO()->Service->NewStatus->Value) === FALSE)
				{
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Changing the Status of the Service failed.<br />". $objService->GetErrorMsg() ."<br />All modifications to the service have been aborted");
					return TRUE;
				}
				
				// The status was successfully changed
				// Check if a new record was created
				if ($objService->GetId() > DBO()->Service->Id->Value)
				{
					// A new record was made
					DBO()->NewService->Id = $objService->GetId();
				}
				
				$intService				= $objService->GetId();
				$strProvisioningNote	= "";
				if (DBO()->Service->NewStatus->Value == SERVICE_ACTIVE && DBO()->Service->Status->Value == SERVICE_PENDING)
				{
					// The service has been activated for the first time
					// Do FullService and Preselection provisioning requests
					if ($objService->CanBeProvisioned())
					{
						if (!$objService->MakeFullServiceProvisioningRequest())
						{
							// Failed to make the FullService provisioning Request
							TransactionRollback();
							Ajax()->AddCommand("Alert", "ERROR: Failed to make the Full Service provisioning request.<br />". $objService->GetErrorMsg() ."<br />All modifications to the service have been aborted");
							return TRUE;
						}
						if (!$objService->MakePreselectionProvisioningRequest())
						{
							// Failed to make the Preselection provisioning Request
							TransactionRollback();
							Ajax()->AddCommand("Alert", "ERROR: Failed to make the Preselection provisioning request.<br />". $objService->GetErrorMsg() ."<br />All modifications to the service have been aborted");
							return TRUE;
						}
						
						$strProvisioningNote = "  FullService and Preselection provisioning requests have been made.";
					}
				}
				
				// Build the note part detailing the Status change
				$strOldStatus	= GetConstantDescription(DBO()->Service->Status->Value, "service_status");
				$strNewStatus	= GetConstantDescription(DBO()->Service->NewStatus->Value, "service_status");
				$strChangesNote = "Status changed from $strOldStatus to $strNewStatus.{$strProvisioningNote}\n". $strChangesNote;
			}

			if ($strChangesNote != "")
			{
				SaveSystemNote("Service modified.\n$strChangesNote", DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, (DBO()->NewService->Id->IsSet)? DBO()->NewService->Id->Value : DBO()->Service->Id->Value);
			}

			// Commit the transaction
			TransactionCommit();
			
			// Close the popup
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			
			// Check that something was actually changed
			if ($strChangesNote != "")
			{
				$strAlert = "The service was successfully updated";
				if ($strProvisioningNote != "")
				{
					$strAlert .= "<br />$strProvisioningNote";
				}
				if (isset($strWarningForFNNChange))
				{
					$strAlert .= "<br />$strWarningForFNNChange";
				}
				
				Ajax()->AddCommand("Alert", $strAlert);

				// Build event object
				// The contents of this object should be declared in the doc block of this method
				$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
				if (DBO()->NewService->Id->Value)
				{
					$arrEvent['NewService']['Id'] = DBO()->NewService->Id->Value;
				}
				Ajax()->FireEvent(EVENT_ON_SERVICE_UPDATE, $arrEvent);
				
				// Fire the OnNewNote Event
				Ajax()->FireOnNewNoteEvent(DBO()->Service->Account->Value, (DBO()->NewService->Id->IsSet)? DBO()->NewService->Id->Value : DBO()->Service->Id->Value);
			}
			return TRUE;
		}
		
		// Load the service record
		if (!DBO()->Service->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: The service with id: ". DBO()->Service->Id->Value ." could not be found");
			return FALSE;
		}
		
		// Load the Account record
		DBO()->Account->Id = DBO()->Service->Account->Value;
		if (!DBO()->Account->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: The account with id: ". DBO()->Account->Id->Value ." could not be found");
			return FALSE;
		}
	
		// Check that the user has permission to edit this service
		if ((DBO()->Service->Status->Value == SERVICE_ARCHIVED) && (!$bolUserHasAdminPerm))
		{
			// The service is archived and the user doesn't have Admin permissions so they can't edit it
			Ajax()->AddCommand("Alert", "ERROR: Due to the service's status, and your permissions, you cannot edit this service");
			return TRUE;
		}
		
		// load mobile detail if the service is a mobile
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			DBO()->ServiceMobileDetail->Where->Service = DBO()->Service->Id->Value;
			DBO()->ServiceMobileDetail->Load();
		}
		
		// load inbound detail if the service is an inbound 1300/1800
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			DBO()->ServiceInboundDetail->Where->Service = DBO()->Service->Id->Value;
			DBO()->ServiceInboundDetail->Load();
		}
		
		// Set up the ELB checkbox, if service is an indial100
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE && DBO()->Service->Indial100->Value)
		{
			// Check if ELB is currently Enabled or Disabled
			DBL()->ServiceExtension->Service = DBO()->Service->Id->Value;
			DBL()->ServiceExtension->Archived = 0;
			DBL()->ServiceExtension->Load();
			DBO()->Service->ELB = (bool)DBL()->ServiceExtension->RecordCount();
		}
		
		// Store the current FNN to check between states that the FNN textbox has been changed
		DBO()->Service->CurrentFNN							= DBO()->Service->FNN->Value;
		DBO()->Service->CurrentStatus 						= DBO()->Service->Status->Value;
		DBO()->Service->CurrentIndial100 					= DBO()->Service->Indial100->Value;
		DBO()->Service->CurrentELB 							= DBO()->Service->ELB->Value;
		DBO()->Service->CurrentCostCentre 					= DBO()->Service->CostCentre->Value;
		DBO()->Service->CurrentForceInvoiceRender			= DBO()->Service->ForceInvoiceRender->Value; 
		DBO()->ServiceInboundDetail->CurrentAnswerPoint		= DBO()->ServiceInboundDetail->AnswerPoint->Value;
		DBO()->ServiceInboundDetail->CurrentConfiguration 	= DBO()->ServiceInboundDetail->Configuration->Value;
		
		// Declare which page to use
		$this->LoadPage('service_edit');
		return TRUE;
	}	
	
	//------------------------------------------------------------------------//
	// ViewPlan
	//------------------------------------------------------------------------//
	/**
	 * ViewPlan()
	 *
	 * Performs the logic for the View Service RatePlan page
	 * 
	 * Performs the logic for the View Service RatePlan page
	 * It assumes:
	 * 		DBO()->Service->Id		is set to the Id of the service
	 *
	 * @return		void
	 * @method		ViewPlan
	 */
	function ViewPlan()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		$bolUserHasOperatorPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
		$bolUserHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		// The service should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		if (!DBO()->Service->Load())
		{
			DBO()->Error->Message = "The Service id: ". DBO()->Service->Id->Value ." you were attempting to view could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		DBO()->Account->Id = DBO()->Service->Account->Value;
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "Can not find Account: ". DBO()->Service->Account->Value . " associated with this service";
			$this->LoadPage('error');
			return FALSE;
		}

		// context menu
		ContextMenu()->Account->Account_Overview(DBO()->Account->Id->Value);
		ContextMenu()->Account->Invoices_and_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Account->Services->List_Services(DBO()->Account->Id->Value);
		ContextMenu()->Account->Contacts->List_Contacts(DBO()->Account->Id->Value);
		ContextMenu()->Account->View_Cost_Centres(DBO()->Account->Id->Value);
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Account->Services->Add_Services(DBO()->Account->Id->Value);
			ContextMenu()->Account->Contacts->Add_Contact(DBO()->Account->Id->Value);
			ContextMenu()->Account->Payments->Make_Payment(DBO()->Account->Id->Value);
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
			{
				ContextMenu()->Account->Provisioning->Provisioning(NULL, DBO()->Account->Id->Value);
				ContextMenu()->Account->Provisioning->ViewProvisioningHistory(NULL, DBO()->Account->Id->Value);
			}
			ContextMenu()->Account->Payments->Change_Payment_Method(DBO()->Account->Id->Value);
			ContextMenu()->Account->Add_Associated_Account(DBO()->Account->Id->Value);
			ContextMenu()->Account->Notes->Add_Account_Note(DBO()->Account->Id->Value);
		}
		ContextMenu()->Account->Notes->View_Account_Notes(DBO()->Account->Id->Value);
		
		ContextMenu()->Service->View_Service(DBO()->Service->Id->Value);
		ContextMenu()->Service->View_Unbilled_Charges(DBO()->Service->Id->Value);
		if ($bolUserHasOperatorPerm)
		{
			ContextMenu()->Service->Edit_Service(DBO()->Service->Id->Value);
			ContextMenu()->Service->Plan->Change_Plan(DBO()->Service->Id->Value);	
			ContextMenu()->Service->Move_Service(DBO()->Service->Id->Value);	
			ContextMenu()->Service->Adjustments->Add_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			ContextMenu()->Service->Adjustments->Add_Recurring_Adjustment(DBO()->Account->Id->Value, DBO()->Service->Id->Value);
			// Only Landlines can have provisioning
			if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
			{
				ContextMenu()->Service->Provisioning->Provisioning(DBO()->Service->Id->Value);
				ContextMenu()->Service->Provisioning->ViewProvisioningHistory(DBO()->Service->Id->Value);
			}
			ContextMenu()->Service->Notes->Add_Service_Note(DBO()->Service->Id->Value);
		}
		ContextMenu()->Service->Notes->View_Service_Notes(DBO()->Service->Id->Value);
		
		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AccountOverview(DBO()->Account->Id->Value, TRUE);
		BreadCrumb()->ViewService(DBO()->Service->Id->Value, TRUE);
		BreadCrumb()->SetCurrentPage("Plan");
		
		// Retrieve all RecordTypes applicable for this service
		DBL()->RecordType->ServiceType = DBO()->Service->ServiceType->Value;
		DBL()->RecordType->OrderBy("Name, Id");
		DBL()->RecordType->Load();
		
		$this->_LoadPlanDetails();
		
		$this->LoadPage('service_plan_view');
		return TRUE;
	}	
	
	//------------------------------------------------------------------------//
	// RenderServiceRateGroupList
	//------------------------------------------------------------------------//
	/**
	 * RenderServiceRateGroupList()
	 *
	 * Renders the ServiceRateGroupList Html Template
	 * 
	 * Renders the ServiceRateGroupList Html Template for viewing
	 * It expects	DBO()->Service->Id 		service Id 
	 *				DBO()->Container->Id	id of the container div in which to place the 
	 *										Rendered HtmlTemplate
	 *
	 * @return		void
	 * @method
	 */
	function RenderServiceRateGroupList()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);

		DBO()->Service->Load();
		
		// Retrieve all RecordTypes applicable for this service
		DBL()->RecordType->ServiceType = DBO()->Service->ServiceType->Value;
		DBL()->RecordType->OrderBy("Name, Id");
		DBL()->RecordType->Load();
		
		$this->_LoadPlanDetails();
		
		// Render the ServiceRateGroupList HtmlTemplate
		Ajax()->RenderHtmlTemplate("ServiceRateGroupList", HTML_CONTEXT_DEFAULT, DBO()->Container->Id->Value);

		return TRUE;
	}	

	//------------------------------------------------------------------------//
	// _LoadPlanDetails
	//------------------------------------------------------------------------//
	/**
	 * _LoadPlanDetails()
	 *
	 * Loads all details required of the ServiceRateGroupList Html Template
	 * 
	 * Loads all details required of the ServiceRateGroupList Html Template
	 * It expects	DBO()->Service->Id 		service Id
	 * 
	 * POST: Sets up the following objects
	 * DBO()->CurrentRatePlan				RatePlan record for the currently active plan (if there is one)
	 * DBO()->CurrentServiceRatePlan		ServiceRatePlan record for the currently active plan (if there is one)
	 * DBO()->FutureRatePlan				RatePlan record for the future scheduled plan (if there is one)
	 * DBO()->FutureServiceRatePlan			ServiceRatePlan record for the future plan (if there is one)
	 * DBL()->CurrentServiceRateGroup		All ServiceRateGroup records that end after the current (or future)
	 * 										RatePlan started, and were created at or after the current (or future)
	 * 										RatePlan was created
	 *
	 * @return		void
	 * @method
	 */
	private function _LoadPlanDetails()
	{
		$arrPlans = Array();
		
		// Load the current RatePlan
		DBO()->CurrentRatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		$strEarliestAllowableEndDatetime = NULL;
		if (DBO()->CurrentRatePlan->Id->Value)
		{
			// The Service has a current RatePlan
			DBO()->CurrentRatePlan->SetTable("RatePlan");
			DBO()->CurrentRatePlan->Load();

			$arrPlanIds[] = DBO()->CurrentRatePlan->Id->Value;

			// Load the current ServiceRatePlan record
			$strWhere = "Service = <Service> AND NOW() BETWEEN StartDatetime AND EndDatetime ORDER BY CreatedOn DESC";
			DBO()->CurrentServiceRatePlan->SetTable("ServiceRatePlan");
			DBO()->CurrentServiceRatePlan->Where->Set($strWhere, Array("Service" => DBO()->Service->Id->Value));
			DBO()->CurrentServiceRatePlan->Load();
			
			DBO()->CurrentRatePlan->StartDatetime	= DBO()->CurrentServiceRatePlan->StartDatetime->Value;
			DBO()->CurrentRatePlan->EndDatetime		= DBO()->CurrentServiceRatePlan->EndDatetime->Value;
			
			// This will be used to retrieve all ServiceRateGroup records that have an EndDatetime greater than this
			$strEarliestAllowableEndDatetime = DBO()->CurrentServiceRatePlan->StartDatetime->Value;
		}
		
		// If $strEarliestAllowableEndDatetime hasn't yet been defined then define it now
		if (!$strEarliestAllowableEndDatetime)
		{
			$strEarliestAllowableEndDatetime = GetCurrentDateAndTimeForMySQL();
		}
		
		// Load the future RatePlan if there is one scheduled to begin next billing period
		DBO()->FutureRatePlan->Id = GetPlanScheduledForNextBillingPeriod(DBO()->Service->Id->Value);
		if (DBO()->FutureRatePlan->Id->Value)
		{
			// The Service has a plan scheduled to begin at the start of the next billing period
			DBO()->FutureRatePlan->SetTable("RatePlan");
			DBO()->FutureRatePlan->Load();
			
			$arrPlanIds[] = DBO()->FutureRatePlan->Id->Value;
			
			// Load the Future RatePlan record (this is only really needed to get the StartDatetime and EndDatetime)
			$strStartOfNextBillingPeriod = ConvertUnixTimeToMySQLDateTime(GetStartDateTimeForNextBillingPeriod());
			$strWhere = "Service = <Service> AND StartDatetime = <StartOfNextBillingPeriod> AND StartDatetime < EndDatetime ORDER BY CreatedOn DESC";
			DBO()->FutureServiceRatePlan->SetTable("ServiceRatePlan");
			DBO()->FutureServiceRatePlan->Where->Set($strWhere, Array("Service" => DBO()->Service->Id->Value, "StartOfNextBillingPeriod" => $strStartOfNextBillingPeriod));
			DBO()->FutureServiceRatePlan->Load();
			
			DBO()->FutureRatePlan->StartDatetime	= DBO()->FutureServiceRatePlan->StartDatetime->Value;
			DBO()->FutureRatePlan->EndDatetime		= DBO()->FutureServiceRatePlan->EndDatetime->Value;
		}
		
		if (DBO()->CurrentRatePlan->Id->Value)
		{
			// Do not show ServiceRateGroup records that were created before the current ServiceRatePlan record was created
			$strEarliestAllowableCreatedOn = DBO()->CurrentServiceRatePlan->CreatedOn->Value;
		}
		elseif (DBO()->FutureRatePlan->Id->Value)
		{
			// Do not show ServiceRateGroup records that were created before the future ServiceRatePlan record was created
			$strEarliestAllowableCreatedOn = DBO()->FutureServiceRatePlan->CreatedOn->Value;
		}
		else
		{
			// The Service has no plans, so just show all ServiceRateGroup records that are still active
			$strEarliestAllowableCreatedOn = "";
		}
		
		// Retrieve all ServiceRateGroup records (with accompanying RateGroup details) that have an EndDatetime > $strEarliestAllowableEndDatetime
		// and StartDatetime < EndDatetime AND (were created after the current plan was created or are fleet RateGroups)
		$arrColumns	= Array("Id" => "SRG.Id", "RateGroup" => "SRG.RateGroup", "CreatedOn" => "SRG.CreatedOn", "StartDatetime" => "SRG.StartDatetime", 
							"EndDatetime" => "SRG.EndDatetime", "Name" => "RG.Name", "Description" => "RG.Description", "Fleet" => "RG.Fleet",
							"RecordType" => "RG.RecordType", "RateGroupId" => "RG.Id");
		$strTable	= "ServiceRateGroup AS SRG INNER JOIN RateGroup AS RG ON SRG.RateGroup = RG.Id";
		
		// OLD WHERE CLAUSE Includes RateGroups that Start after the current plan finishes as well as all of those that finished after the CurrentPlan Started but before now
		//$strWhere	= "SRG.Service = <Service> AND SRG.EndDatetime > <EarliestAllowableEndDatetime> AND SRG.StartDatetime < SRG.EndDatetime";
		//$arrWhere	= Array("Service" => DBO()->Service->Id->Value, "EarliestAllowableEndDatetime" => $strEarliestAllowableEndDatetime);

		// Retrieve all ServiceRateGroup records that have EndDatetime > CurrentRatePlan.StartDatetime (<EarliestAllowableEndDatetime>)
		// AND StartDatetime < EndDatetime AND (RG.Fleet = 1 OR (CreatedOn + 5 seconds) > CurrentRatePlan.CreatedOn)
		// Which means retrieve all RateGroups that have an EndDatetime greater than the StartDatetime of the Current RatePlan, and (are fleet
		// RateGroups OR were created at or after the CurrentRatePlan was created
		$strWhere	= "SRG.Service = <Service> AND SRG.EndDatetime > <EarliestAllowableEndDatetime> AND SRG.StartDatetime < SRG.EndDatetime".
						" AND (RG.Fleet = 1 OR ADDTIME(SRG.CreatedOn, SEC_TO_TIME(5)) > '$strEarliestAllowableCreatedOn')";
		$arrWhere	= Array("Service" => DBO()->Service->Id->Value, "EarliestAllowableEndDatetime" => $strEarliestAllowableEndDatetime);
		
		$strOrderBy = "SRG.CreatedOn DESC";

		DBL()->CurrentServiceRateGroup->SetColumns($arrColumns);
		DBL()->CurrentServiceRateGroup->SetTable($strTable);
		DBL()->CurrentServiceRateGroup->Where->Set($strWhere, $arrWhere);
		DBL()->CurrentServiceRateGroup->OrderBy($strOrderBy);
		DBL()->CurrentServiceRateGroup->Load();
		
		// Load all the RateGroups belonging to either plans defined for the service (current and/or future)
		// Assuming there are plans associated with this service
		if (count($arrPlanIds) > 0)
		{
			DBL()->PlanRateGroup->SetTable("RateGroup");
			DBL()->PlanRateGroup->SetColumns("Id");
			$strWhere = "Id IN (SELECT RateGroup FROM RatePlanRateGroup WHERE RatePlan IN (". implode(", ", $arrPlanIds) ."))";
			DBL()->PlanRateGroup->Where->SetString($strWhere);
			DBL()->PlanRateGroup->OrderBy("RecordType");
			DBL()->PlanRateGroup->Load();
		}
	}


	//------------------------------------------------------------------------//
	// RemoveServiceRateGroup
	//------------------------------------------------------------------------//
	/**
	 * RemoveServiceRateGroup()
	 *
	 * Removes a ServiceRateGroup record, if it can be safely removed
	 * 
	 * Removes a ServiceRateGroup record, if it can be safely removed
	 * (This function is not currently used as we cannot allow them to remove
	 * a RateGroup that was used to rate a CDR which has already been invoiced.
	 * Eventually this functionality should set the EndDatetime of the RateGroup
	 * to 1 second after the StartDatetime of the most recent CDR it was applied
	 * to, which has been invoiced, or if there are none, then set the EndDatetime
	 * to 1 second before the StartDatetime)
	 * 
	 * A ServiceRateGroup record can only be removed if:
	 * 		It is a fleet RateGroup
	 * OR
	 * 		The entire length of time that the RateGroup is applicable to, is covered
	 * 		by other ServiceRateGroup records (of the same RecordType)
	 * It expects	DBO()->ServiceRateGroup->Id 		Record to be removed 
	 *
	 * @return		void
	 * @method
	 */
	function RemoveServiceRateGroup()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);

		// Currently don't allow the user to use this functionality
		Ajax()->AddCommand("Alert", "ERROR: This functionality has been prohibited, as it currently compromises the integrity and accuracy of the history of the Service's plan details");
		return TRUE;
/*
		// Removing RateGroups can not be done while billing is in progress
		if (IsInvoicing())
		{
			$strErrorMsg =  "Billing is in progress.  Plan details cannot be changed while this is happening.  ".
							"Please try again in a couple of hours.  If this problem persists, please ".
							"notify your system administrator";
			Ajax()->AddCommand("Alert", $strErrorMsg);
			return TRUE;
		}
 
 		$bolCanRemoveRateGroup = FALSE;
 		
		// Load the ServiceRateGroup record to remove, and its RateGroup details 
		DBO()->ServiceRateGroup->Load();
		DBO()->RateGroup->Id = DBO()->ServiceRateGroup->RateGroup->Value;
		DBO()->RateGroup->Load();
		
		if (DBO()->RateGroup->Fleet->Value == TRUE)
		{
			// Fleet RateGroups can always be removed
			$bolCanRemoveRateGroup = TRUE;
		}
		else
		{
			// Retrieve the current RatePlan Record if there is one
			$selCurrentRatePlan	= new StatementSelect("ServiceRatePlan", "*", "Service = <Service> AND StartDatetime < EndDatetime AND NOW() BETWEEN StartDatetime AND EndDatetime", "CreatedOn DESC", "1");
			$intRecordCount		= $selCurrentRatePlan->Execute(Array("Service"=>DBO()->ServiceRateGroup->Service->Value));
			if ($intRecordCount != 1)
			{
				// The Service Does not have a current RatePlan
				// Check if it has a future plan
				$selFutureRatePlan	= new StatementSelect("ServiceRatePlan", "*", "Service = <Service> AND StartDatetime < EndDatetime AND StartDatetime > NOW()", "CreatedOn DESC", "1");
				$intRecordCount		= $selFutureRatePlan->Execute(Array("Service"=>DBO()->ServiceRateGroup->Service->Value));
				if ($intRecordCount != 1)
				{
					// There is no current or future Plan for the service
					// Removing the RateGroup should be safe
					$bolCanRemoveRateGroup = TRUE;
				}
				else
				{
					// Found a future plan
					$arrServiceRatePlan = $selFutureRatePlan->Fetch();
				}
			}
			else
			{
				// Found a current plan
				$arrServiceRatePlan = $selCurrentRatePlan->Fetch();
			}
			
			if (isset($arrServiceRatePlan))
			{
				$strEarliestCreatedOnDate = $arrServiceRatePlan['CreatedOn'];
				
				// Find all ServiceRateGroup records that were created at or after $strEarliestCreatedOnDate
				$arrColumns	= Array("Id"			=> "SRG.Id",
									"CreatedOn"		=> "SRG.CreatedOn",
									"StartDatetime"	=> "SRG.StartDatetime",
									"EndDatetime"	=> "SRG.EndDatetime",
									"RateGroupId"	=> "RG.Id",
									"RecordType"	=> "RG.RecordType",
									"Fleet"			=> "RG.Fleet",
									"Archived"		=> "RG.Archived"	
									);
				$strWhere	= "SRG.Service=<Service> AND RG.RecordType=<RecordType> AND ADDTIME(SRG.CreatedOn, SEC_TO_TIME(5)) > <EarliestCreatedOn> AND SRG.StartDatetime < SRG.EndDatetime AND SRG.Id != <RateGroupToRemove> AND RG.Fleet = 0 AND RG.Archived = 0";
				$strTables	= "ServiceRateGroup AS SRG INNER JOIN RateGroup AS RG ON SRG.RateGroup = RG.Id";
				$strOrderBy	= "SRG.StartDatetime ASC";
				$selServiceRateGroups = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy);
				$intRecCount = $selServiceRateGroups->Execute(Array(	"Service"			=> DBO()->ServiceRateGroup->Service->Value,
																		"RecordType"		=> DBO()->RateGroup->RecordType->Value,
																		"EarliestCreatedOn"	=> $strEarliestCreatedOnDate,
																		"RateGroupToRemove"	=> DBO()->ServiceRateGroup->Id->Value));
				if ($intRecCount)
				{
					// Check that the ServiceRateGroup records retrieved cover the time range of the one being removed.
					// One of them has to start before the one to remove, and then there has to be no gaps
					// until after the one to remove ends
					$arrServiceRateGroups = $selServiceRateGroups->FetchAll();
					
					$bolFoundStart = FALSE;
					$strCoveredUntilEndDatetime = DBO()->ServiceRateGroup->StartDatetime->Value;
					
					// Find the first record that starts before and ends after the one to remove starts
					foreach ($arrServiceRateGroups as $arrServiceRateGroup)
					{
						if (!$bolFoundStart)
						{
							if (($arrServiceRateGroup['StartDatetime'] <= DBO()->ServiceRateGroup->StartDatetime->Value) &&
								($arrServiceRateGroup['EndDatetime'] > DBO()->ServiceRateGroup->StartDatetime->Value))
							{
								// Found the earliest record that encompases the start of the record to remove
								$bolFoundStart = TRUE;
								$strCoveredUntilEndDatetime = $arrServiceRateGroup['EndDatetime'];
							}
							else
							{
								// We still have not found the earliest record which covers the Start of the record to remove
								continue;
							}
						}
						else
						{
							if ((date("Y-m-d H:i:s", strtotime($arrServiceRateGroup['StartDatetime'])-1) <= $strCoveredUntilEndDatetime) &&
								($arrServiceRateGroup['EndDatetime'] > $strCoveredUntilEndDatetime))
							{
								$strCoveredUntilEndDatetime = $arrServiceRateGroup['EndDatetime']; 
							}
						}
						
						// Check if the ServiceRateGroup record to be removed, is already covered
						if ($strCoveredUntilEndDatetime >= DBO()->ServiceRateGroup->EndDatetime->Value)
						{
							// ServiceRateGroup records exist to cover the loss of the one being removed
							// The user is allowed to remove the ServiceRateGroup record
							$bolCanRemoveRateGroup = TRUE;
							break; 
						}
					}
				}
			}
		}
		
		if (!$bolCanRemoveRateGroup)
		{
			// The RateGroup cannot be removed
			Ajax()->AddCommand("Alert", "ERROR: The RateGroup cannot be removed, as it would leave a whole in the Service's Plan");
			return TRUE;
		}
		
		TransactionStart();
		
		// Remove the ServiceRateGroup record by setting its EndDatetime to 1 second before its StartDatetime
		$strNewEndDatetime = date("Y-m-d H:i:s", (strtotime(DBO()->ServiceRateGroup->StartDatetime->Value)-1));
		$strOldEndDatetime = DBO()->ServiceRateGroup->EndDatetime->Value; 
		DBO()->ServiceRateGroup->EndDatetime = $strNewEndDatetime;
		if (!DBO()->ServiceRateGroup->Save())
		{
			// Updating the record failed
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: Removing the RateGroup failed, unexpectedly<br />(Error updating the record to remove)");
			return TRUE;
		}
		
		// Rerate all the Rated CDRs in the CDR table
		$arrUpdate = Array('Status' => CDR_NORMALISED);
		$updCDRs = new StatementUpdate("CDR", "Service = <Service> AND Status = <CDRRated> AND RecordType = <RecordType>", $arrUpdate);
		$arrWhere = Array("Service"=>DBO()->ServiceRateGroup->Service->Value, "CDRRated"=>CDR_RATED, "RecordType"=>DBO()->RateGroup->RecordType->Value);
		if ($updCDRs->Execute($arrUpdate, $arrWhere) === FALSE)
		{
			// Could not update records in CDR table. Exit gracefully
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: Removing the RateGroup failed, unexpectedly<br />(Error updating records in the CDR table)");
			return TRUE;
		}
		
		TransactionCommit();
		
		// Load the RecordType record as we need the name for the SystemNote
		DBO()->RecordType->Id = DBO()->RateGroup->RecordType->Value;
		DBO()->RecordType->Load();
		
		// Add a system note
		$strNote = 	"RateGroup removed from service\n".
					"RecordType: ". DBO()->RecordType->Description->Value ."\n".
					"Name: ". DBO()->RateGroup->Name->Value ."\n".
					"Desc: ". DBO()->RateGroup->Description->Value ."\n".
					"Created: ". date("H:i:s d/m/Y", strtotime(DBO()->ServiceRateGroup->CreatedOn->Value)) ."\n".
					"Start: ". date("H:i:s d/m/Y", strtotime(DBO()->ServiceRateGroup->StartDatetime->Value)) ."\n".
					"Finish: ". (($strOldEndDatetime == END_OF_TIME)? "Indefinite" : date("H:i:s d/m/Y", strtotime($strOldEndDatetime))) ."\n".
					"Fleet: ". DBO()->RateGroup->Fleet->FormattedValue() ."\n".
					"ServiceRateGroupId: ". DBO()->ServiceRateGroup->Id->Value;
		DBO()->Service->Id = DBO()->ServiceRateGroup->Service->Value;
		DBO()->Service->Load();
		SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
		
		// Don't bother firing an OnNewNote event because no notes are displayed on the page where this functionality is accessed
		
		// Fire the EVENT_ON_SERVICE_RATE_GROUPS_UPDATE Event
		$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
		$arrEvent['RecordType']['Id'] = DBO()->RateGroup->RecordType->Value;
		Ajax()->FireEvent(EVENT_ON_SERVICE_RATE_GROUPS_UPDATE, $arrEvent);

		Ajax()->AddCommand("Alert", "The RateGroup has been successfully removed");
		return TRUE;
*/
	}
	
	//------------------------------------------------------------------------//
	// ChangePlan
	//------------------------------------------------------------------------//
	/**
	 * ChangePlan()
	 *
	 * Performs the logic for "Change Plan" popup
	 * 
	 * Performs the logic for "Change Plan" popup
	 * If the service successfully has its plan changed then it will fire an 
	 * EVENT_ON_SERVICE_UPDATE event passing the following Event object data:
	 *		Service.Id		= id of the service which has had its plan changed
	 *
	 *		When the ChangePlan form data is submitted, this function expects the
	 *		following properties to be defined
	 *		DBO()->Service->Id			Id of the service that the change is affecting
	 *		DBO()->NewPlan->Id			Id of the rate plan to change to
	 *		DBO()->NewPlan->StartTime	0 signifies that the new plan should be 
	 *										used for the current billing period
	 *									1 signifies that the new plan should 
	 *										come into affect, starting the next
	 *										billing period
	 *
	 * @return		void
	 * @method		ChangePlan
	 *
	 */
	function ChangePlan()
	{
		// Check user authorization here
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);

		// Check if the billing/invoice process is being run
		if (IsInvoicing())
		{
			// There are currently records in the InvoiceTemp table, which means a bill run is taking place.
			// Plan Changes cannot be made when a bill run is taking place
			$strErrorMsg =  "Billing is in progress.  Plans cannot be changed while this is happening.  ".
							"Please try again in a couple of hours.  If this problem persists, please ".
							"notify your system administrator";
			Ajax()->AddCommand("Alert", $strErrorMsg);
			return TRUE;
		}
		
		if (SubmittedForm("ChangePlan","Change Plan"))
		{
			// Work out the StartDatetime for the new records of the ServiceRatePlan and ServiceRateGroup tables
			$strCurrentDateAndTime						= GetCurrentDateAndTimeForMySQL();
			$intStartDateTimeForCurrentBillingPeriod	= GetStartDateTimeForBillingPeriod($strCurrentDateAndTime);
			$intStartDateTimeForNextBillingPeriod		= GetStartDateTimeForNextBillingPeriod($strCurrentDateAndTime);
			DBO()->Service->Load();
			DBO()->Account->Id = DBO()->Service->Account->Value;
			DBO()->Account->Load();
			
			if (DBO()->NewPlan->StartTime->Value == 1)
			{
				// Get the StartDatetime for the next billing period
				$intStartDatetime = $intStartDateTimeForNextBillingPeriod;
				
				// The records defining the new plan should have their "Active" property set to 0 (Inactive)
				$intActive = 0;
				
				// Declare the note part detailing when the Plan Change will come into effect
				$strNotePlanStart = "This plan change will come into effect as of the start of the next billing period. (". date("d/m/Y", $intStartDatetime) .")";
			}
			else
			{
				// Get the StartDatetime for the current billing period
				$intStartDatetime = $intStartDateTimeForCurrentBillingPeriod;
				
				// The records defining the new plan should have their "Active" property set to 1 (Active)
				$intActive = 1;
				
				// Declare the note part detailing when the Plan Change will come into effect
				$strNotePlanStart = "This plan change has come into effect as of the beginning of the current billing period. (". date("d/m/Y", $intStartDatetime) .")";
			}
			$strStartDatetime = ConvertUnixTimeToMySQLDateTime($intStartDatetime);
			
			// Work out the EndDatetime for the old records of the ServiceRatePlan and ServiceRateGroup tables, which have an EndDatetime
			// greater than $strStartDatetime
			// The EndDatetime will be set to 1 second before the StartDatetime of the records relating to the new plan
			$intOldPlanEndDatetime = $intStartDatetime - 1;
			$strOldPlanEndDatetime = ConvertUnixTimeToMySQLDateTime($intOldPlanEndDatetime);
			
			// Find the current plan (if there is one)
			DBO()->CurrentRatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
			if (DBO()->CurrentRatePlan->Id->Value)
			{
				DBO()->CurrentRatePlan->SetTable("RatePlan");
				DBO()->CurrentRatePlan->Load();
			}
			
			// Find the plan scheduled to start for the next billing run (if there is one)
			DBO()->FutureRatePlan->Id = GetPlanScheduledForNextBillingPeriod(DBO()->Service->Id->Value, $strCurrentDateAndTime);
			if (DBO()->FutureRatePlan->Id->Value)
			{
				DBO()->FutureRatePlan->SetTable("RatePlan");
				DBO()->FutureRatePlan->Load();
			}
			
			// Retrieve the new plan
			DBO()->NewPlan->SetTable("RatePlan");
			DBO()->NewPlan->Load();
			
			// Check that the Plan is active and is of the appropriate ServiceType and CustomerGroup
			if (DBO()->NewPlan->Archived->Value != RATE_STATUS_ACTIVE)
			{
				Ajax()->AddCommand("Alert", "ERROR: This Plan is not currently active");
				return TRUE;
			}
			if (DBO()->NewPlan->ServiceType->Value != DBO()->Service->ServiceType->Value)
			{
				Ajax()->AddCommand("Alert", "ERROR: This Plan is not of the same ServiceType as the Service");
				return TRUE;
			}
			if (DBO()->NewPlan->customer_group->Value != DBO()->Account->CustomerGroup->Value)
			{
				Ajax()->AddCommand("Alert", "ERROR: This Plan does not belong to the CustomerGroup that this account belongs to");
				return TRUE;
			}
			
			// Start the transaction
			TransactionStart();
			
			// Set the EndDatetime to $strOldPlanEndDatetime for all records in the ServiceRatePlan and ServiceRateGroup tables
			// which relate this service.  Do not alter the records' "Active" property regardless of what it is.
			
			// Update existing ServiceRateGroup records
			$arrUpdate = Array('EndDatetime' => $strOldPlanEndDatetime);
			$updServiceRateGroup = new StatementUpdate("ServiceRateGroup", "Service = <Service> AND EndDatetime >= <StartDatetime>", $arrUpdate);
			if ($updServiceRateGroup->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value, "StartDatetime"=>$strStartDatetime)) === FALSE)
			{
				// Could not update records in ServiceRateGroup table. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error updating records in the ServiceRateGroup table)");
				return TRUE;
			}
			
			// Update existing ServiceRatePlan records
			$updServiceRatePlan = new StatementUpdate("ServiceRatePlan", "Service = <Service> AND EndDatetime >= <StartDatetime>", $arrUpdate);
			if ($updServiceRatePlan->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value, "StartDatetime"=>$strStartDatetime)) === FALSE)
			{
				// Could not update records in ServiceRatePlan table. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error updating records in the ServiceRatePlan table)");
				return TRUE;
			}
			
			// Declare the new plan for the service
			// Insert a record into the ServiceRatePlan table
			DBO()->ServiceRatePlan->Service 						= DBO()->Service->Id->Value;
			DBO()->ServiceRatePlan->RatePlan 						= DBO()->NewPlan->Id->Value;
			DBO()->ServiceRatePlan->CreatedBy 						= AuthenticatedUser()->_arrUser['Id'];
			DBO()->ServiceRatePlan->CreatedOn 						= $strCurrentDateAndTime;
			DBO()->ServiceRatePlan->StartDatetime 					= $strStartDatetime;
			DBO()->ServiceRatePlan->EndDatetime 					= END_OF_TIME;
			DBO()->ServiceRatePlan->LastChargedOn					= NULL;
			DBO()->ServiceRatePlan->Active							= $intActive;
			
			$intContractTerm										= DBO()->NewPlan->ContractTerm->Value;
			DBO()->ServiceRatePlan->contract_scheduled_end_datetime	= ($intContractTerm) ? strtotime("-1 second", strtotime("+{$intContractTerm} months", $intStartDatetime)) : NULL;
			DBO()->ServiceRatePlan->contract_effective_end_datetime	= NULL;
			DBO()->ServiceRatePlan->contract_exit_nature_id			= NULL;
			
			if (!DBO()->ServiceRatePlan->Save())
			{
				// Could not save the record. Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error adding record to ServiceRatePlan table)");
				return TRUE;
			}
			
			// Declare the new RateGroups for the service
			$intServiceId	= DBO()->Service->Id->Value;
			$intUserId		= AuthenticatedUser()->_arrUser['Id'];
			$strInsertRateGroupsIntoServiceRateGroup  = "INSERT INTO ServiceRateGroup (Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active) ";
			$strInsertRateGroupsIntoServiceRateGroup .= "SELECT NULL, $intServiceId, RateGroup, $intUserId, '$strCurrentDateAndTime', '$strStartDatetime', '". END_OF_TIME ."', $intActive ";
			$strInsertRateGroupsIntoServiceRateGroup .= "FROM RatePlanRateGroup WHERE RatePlan = ". DBO()->NewPlan->Id->Value ." ORDER BY RateGroup";
			$qryInsertServiceRateGroup = new Query();
			if ($qryInsertServiceRateGroup->Execute($strInsertRateGroupsIntoServiceRateGroup) === FALSE)
			{
				// Inserting the records into the ServiceRateGroup table failed.  Exit gracefully
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error adding records to ServiceRateGroup table)");
				return TRUE;
			}
			
			// If the plan goes into affect at the begining of the current month, then you must rerate all the cdrs which are currently
			// rated but not billed
			if (DBO()->NewPlan->StartTime->Value == 0)
			{
				// The plan change is retroactive to the start of the current month
				// Set the status of all CDRs that are currently "rated" (CDR_RATED) to "ready for rating" (CDR_NORMALISED)
				$arrUpdate	= Array('Status' => CDR_NORMALISED);
				$updCDRs	= new StatementUpdate("CDR", "Service = <Service> AND Status = <CDRRated>", $arrUpdate);
				if ($updCDRs->Execute($arrUpdate, Array("Service"=>DBO()->Service->Id->Value, "CDRRated"=>CDR_RATED)) === FALSE)
				{
					// Could not update records in CDR table. Exit gracefully
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error updating records in the CDR table)");
					return TRUE;
				}
				
				// Only update the Carrier and CarrierPreselect fields of the Service record, 
				// if the new plan comes into affect at the beging of the current billing period
				$arrUpdate = Array(	"Carrier"			=> DBO()->NewPlan->CarrierFullService->Value,
									"CarrierPreselect"	=> DBO()->NewPlan->CarrierPreselection->Value);
				
				$updService = new StatementUpdate("Service", "Id = <Service>", $arrUpdate);
				if ($updService->Execute($arrUpdate, Array("Service" => DBO()->Service->Id->Value)) === FALSE)
				{
					// Could not update the service record. Exit gracefully
					TransactionRollback();
					Ajax()->AddCommand("Alert", "ERROR: Saving the plan change to the database failed, unexpectedly<br />(Error updating carrier details in the service record)");
					return TRUE;
				}
			}
			
			//TODO! Do automatic provisioning here
			
			// Add a system note describing the change of plan
			DBO()->Service->Load();
			if (!DBO()->CurrentRatePlan->Id->Value)
			{
				// The Service has not previously had a RatePlan
				DBO()->CurrentRatePlan->Name = "undefined";
			}
			$strNote  = "This service has had its plan changed from '". DBO()->CurrentRatePlan->Name->Value ."' to '". DBO()->NewPlan->Name->Value ."'.  $strNotePlanStart";
			
			SaveSystemNote($strNote, DBO()->Service->AccountGroup->Value, DBO()->Service->Account->Value, NULL, DBO()->Service->Id->Value);
			
			// All changes to the database, required to define the plan change, have been completed
			// Commit the transaction
			TransactionCommit();
			
			// Close the popup, alert the user
			Ajax()->AddCommand("ClosePopup", $this->_objAjax->strId);
			Ajax()->AddCommand("Alert", "The service's plan has been successfully changed");

			// Build event object
			// The contents of this object should be declared in the doc block of this method
			$arrEvent['Service']['Id'] = DBO()->Service->Id->Value;
			Ajax()->FireEvent(EVENT_ON_SERVICE_UPDATE, $arrEvent);

			// Since a system note has been added, fire the OnNewNote event
			Ajax()->FireOnNewNoteEvent(DBO()->Service->Account->Value, DBO()->Service->Id->Value);

			return TRUE;
		}		
		
		// Retrieve the service details
		if (!DBO()->Service->Load())
		{
			Ajax()->AccCommand("Alert", "The Service id: ". DBO()->Service->Id->value ." you were attempting to view could not be found");
			return TRUE;
		}
		
		// Retrieve the Account Details
		DBO()->Account->Id = DBO()->Service->Account->Value;
		if (!DBO()->Account->Load())
		{
			Ajax()->AccCommand("Alert", "Can not find Account: ". DBO()->Service->Account->Value . " associated with this service");
			return TRUE;
		}
		
		// Retrieve all available plans for this ServiceType/CustomerGroup
		$strWhere	= "ServiceType = <ServiceType> AND customer_group = <CustomerGroup> AND Archived = <ActiveStatus>";
		$arrWhere	= array("ServiceType"	=>DBO()->Service->ServiceType->Value,
							"CustomerGroup"	=>DBO()->Account->CustomerGroup->Value,
							"ActiveStatus"	=> RATE_STATUS_ACTIVE);
		DBL()->RatePlan->Where->Set($strWhere, $arrWhere);
		DBL()->RatePlan->OrderBy("Name");
		DBL()->RatePlan->Load();
		
		// Set the default for the scheduled start time of the new plan
		// Defaults to "Start billing at begining of next billing period"
		DBO()->NewPlan->StartTime = 1;
		
		$this->LoadPage('plan_change');
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _ActivateService
	//------------------------------------------------------------------------//
	/**
	 * _ActivateService()
	 *
	 * Performs the database modifications required of activating the service
	 * 
	 * Performs the database modifications required of activating the service
	 * If a new Service record has to be created, then DBO()->NewService will store the
	 * contents of this new service.
	 *
	 * @precondition	This function should be encapsulated by a database transaction (TransactionStart),
	 *					which should be rolled back if the function returns anything other than TRUE
	 * 		
	 * @param			integer		$intService		Id of the service to activate
	 * @param			string		$strFNN			FNN of the service to activate
	 * @param			bool		$bolIsIndial	TRUE if the service is an Indial100
	 * @param			string		$strCreatedOn	CreatedOn date for the Service record identified by $intService (YYYY-MM-DD)
	 * @param			string		$strClosedOn	date on which the service was closed (YYYY-MM-DD)
	 *	 
	 * @return			mix			returns TRUE if the service can be activated, else it returns an error 
	 *								message (string) detailing why the service could not be activated
	 * @method
	 */
	private function _ActivateService($intService, $strFNN, $bolIsIndial, $strCreatedOn, $strClosedOn)
	{
		if ($strClosedOn < $strCreatedOn)
		{
			$strCreatedOn	= OutputMask()->ShortDate($strCreatedOn);
			$strClosedOn	= OutputMask()->ShortDate($strClosedOn);
			return "ERROR: This service cannot be activated as its CreatedOn date ($strCreatedOn) is greater than its ClosedOn date ($strClosedOn) signifying that it was never actually used by this account";
		}
		
		$strNow = GetCurrentDateForMySQL();
		
		// Check if the FNN is currently in use
		$arrWhere					= Array();
		$arrWhere['FNN']			= ($bolIsIndial) ? substr($strFNN, 0, -2) . "__" : $strFNN; 
		$arrWhere['IndialRange']	= substr($strFNN, 0, -2) . "__";
		$arrWhere['Service']		= $intService;
		$arrWhere['ClosedOn']		= $strClosedOn;
		
		$selFNNInUse = new StatementSelect("Service", "Id", "(FNN LIKE <FNN> OR (FNN LIKE <IndialRange> AND Indial100 = 1)) AND (ClosedOn IS NULL OR (ClosedOn >= CreatedOn AND NOW() <= ClosedOn)) AND Id != <Service>");
		if ($selFNNInUse->Execute($arrWhere))
		{
			// At least one record was returned, which means the FNN is currently in use by an active service
			if ($bolIsIndial)
			{
				return "ERROR: Cannot activate this service as at least one of the FNNs in the Indial Range is currently being used by another service.  The other service must be disconnected or archived before this service can be activated.";
			}
			return 	"ERROR: Cannot activate this service as the FNN: $strFNN is currently being used by another service.  The other service must be disconnected or archived before this service can be activated";
		}
		
		// If the Service hasn't closed yet, then just update the ClosedOn and Status properties
		// You do not need to create a new record, or renormalise CDRs
		if ($strClosedOn >= $strNow)
		{
			// Just update the record
			$arrUpdate = Array	(	"Id"		=> $intService,
									"ClosedOn"	=> NULL,
									"Status"	=> SERVICE_ACTIVE
								);
			$updService = new StatementUpdateById("Service", $arrUpdate);
			if ($updService->Execute($arrUpdate) === FALSE)
			{
				// There was an error while trying to activate the service
				return "ERROR: Activating the service failed, unexpectedly";
			}
			
			// Service was activated successfully
			return TRUE;
		}
		
		
		
		// Check if the FNN has been used by another de-activated service since $intService was de-activated
		/* This check is no longer required because we now always create a new service record when activating a
		 * service irregardless of whether or not the FNN has since been used by a now disconnected service
		$selFNNInUse = new StatementSelect("Service", "Id", "(FNN LIKE <FNN> OR (FNN LIKE <IndialRange> AND Indial100 = 1)) AND (ClosedOn Is NULL OR (ClosedOn >= CreatedOn AND ClosedOn > <ClosedOn>)) AND Id != <Service>");
		if ($selFNNInUse->Execute($arrWhere) == 0)
		{
			// The FNN has not been used since this service was de-activated.  Activate the service
			DBO()->ArchivedService->SetTable("Service");
			DBO()->ArchivedService->SetColumns("Id, ClosedOn, Status");
			DBO()->ArchivedService->Id 			= $intService;
			DBO()->ArchivedService->ClosedOn 	= NULL;
			DBO()->ArchivedService->Status 		= SERVICE_ACTIVE;
			if (!DBO()->ArchivedService->Save())
			{
				// There was an error while trying to activate the service
				return "ERROR: Activating the service failed, unexpectedly";
			}
			
			// Service was activated successfully
			return TRUE;
		}
		*/
		
		// Create the new service record, based on the old service record
		$intOldServiceId = $intService;
		DBO()->NewService->SetTable("Service");
		DBO()->NewService->Id = $intOldServiceId;
		DBO()->NewService->Load();
		
		// By setting the Id to zero, a new record will be inserted when the Save method is executed
		DBO()->NewService->Id						= 0;
		DBO()->NewService->CreatedOn				= $strNow;
		DBO()->NewService->CreatedBy				= AuthenticatedUser()->_arrUser['Id'];
		DBO()->NewService->ClosedOn					= NULL;
		DBO()->NewService->ClosedBy					= NULL;
		DBO()->NewService->Status					= SERVICE_ACTIVE;
		DBO()->NewService->EarliestCDR				= NULL;
		DBO()->NewService->LatestCDR				= NULL;
		DBO()->NewService->LineStatus				= NULL;
		DBO()->NewService->LineStatusDate			= NULL;
		DBO()->NewService->PreselectionStatus		= NULL;
		DBO()->NewService->PreselectionStatusDate	= NULL;
		
		if (!DBO()->NewService->Save())
		{
			return "ERROR: Activating the service failed, unexpectedly";
		}
		
		// Save extra service details like mobile details, and inbound details and address details
		switch (DBO()->NewService->ServiceType->Value)
		{
			case SERVICE_TYPE_MOBILE:
				DBO()->NewServiceMobileDetail->Where->Service = $intOldServiceId;
				DBO()->NewServiceMobileDetail->SetTable("ServiceMobileDetail");
				if (DBO()->NewServiceMobileDetail->Load())
				{
					DBO()->NewServiceMobileDetail->Service	= DBO()->NewService->Id->Value;
					DBO()->NewServiceMobileDetail->Id		= 0;
					DBO()->NewServiceMobileDetail->Save();
				}
				break;
			case SERVICE_TYPE_INBOUND:
				DBO()->NewServiceInboundDetail->Where->Service = $intOldServiceId;
				DBO()->NewServiceInboundDetail->SetTable("ServiceInboundDetail");
				if (DBO()->NewServiceInboundDetail->Load())
				{
					DBO()->NewServiceInboundDetail->Service	= DBO()->NewService->Id->Value;
					DBO()->NewServiceInboundDetail->Id		= 0;
					DBO()->NewServiceInboundDetail->Save();
				}
				break;
			case SERVICE_TYPE_LAND_LINE:
				DBO()->NewServiceAddress->Where->Service = $intOldServiceId;
				DBO()->NewServiceAddress->SetTable("ServiceAddress");
				if (DBO()->NewServiceAddress->Load())
				{
					DBO()->NewServiceAddress->Service	= DBO()->NewService->Id->Value;
					DBO()->NewServiceAddress->Id		= 0;
					DBO()->NewServiceAddress->Save();
				}
				
				// Handle ELB if in use
				if (DBO()->NewService->Indial100->Value)
				{
					// This will perform an insert query for each new record added to the ServiceExtension table.  It could have been done
					// with just one query if StatementInsert could accomodate SELECT querys for the VALUES clause
					// You could use the ExecuteQuery class defined in vixen/framework/db_access to do this in just one query
					DBL()->NewServiceExtension->Service = $intOldServiceId;
					DBL()->NewServiceExtension->SetTable("ServiceExtension");
					DBL()->NewServiceExtension->Load();
					foreach (DBL()->NewServiceExtension as $dboServiceExtension)
					{
						$dboServiceExtension->Service	= DBO()->NewService->Id->Value;
						$dboServiceExtension->Id		= 0;
						$dboServiceExtension->Save();
					}
				}
				break;
			default:
				break;
		}
		
		// Copy all ServiceRatePlan records across from the old service where EndDatetime is in the future and StartDatetime < EndDatetime
		$intNewServiceId = DBO()->NewService->Id->Value;
		$strCopyServiceRatePlanRecordsToNewService =	"INSERT INTO ServiceRatePlan (Id, Service, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active) ".
														"SELECT NULL, $intNewServiceId, RatePlan, CreatedBy, CreatedOn, StartDatetime, EndDatetime, LastChargedOn, Active ".
														"FROM ServiceRatePlan WHERE Service = $intOldServiceId AND EndDatetime > NOW() AND StartDatetime < EndDatetime";
		$qryInsertServicePlanDetails = new Query();
		
		if ($qryInsertServicePlanDetails->Execute($strCopyServiceRatePlanRecordsToNewService) === FALSE)
		{
			// Inserting the records into the ServiceRatePlan table failed
			return "ERROR: Activating the service failed, unexpectedly.  Inserting records into the ServiceRatePlan table failed";
		}
		
		// Copy all ServiceRateGroup records across from the old service where EndDatetime is in the future and StartDatetime < EndDatetime
		$strCopyServiceRateGroupRecordsToNewService =	"INSERT INTO ServiceRateGroup (Id, Service, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active) ".
														"SELECT NULL, $intNewServiceId, RateGroup, CreatedBy, CreatedOn, StartDatetime, EndDatetime, Active ".
														"FROM ServiceRateGroup WHERE Service = $intOldServiceId AND EndDatetime > NOW() AND StartDatetime < EndDatetime";
														
		if ($qryInsertServicePlanDetails->Execute($strCopyServiceRateGroupRecordsToNewService) === FALSE)
		{
			// Inserting the records into the ServiceRateGroup table failed
			return "ERROR: Activating the service failed, unexpectedly.  Inserting records into the ServiceRateGroup table failed";
		}

		// Renormalise all CDRs relating to the old Service record which is in the CDR table
		$strWhere	= "Service = <OldService> AND Status = ". CDR_RATED;
		$arrWhere	= Array("OldService" => $intService);
		$arrUpdate	= Array("Status" => CDR_READY);
		$updCDRs	= new StatementUpdate("CDR", $strWhere, $arrUpdate);
		if ($updCDRs->Execute($arrUpdate, $arrWhere) === FALSE)
		{
			return "ERROR: Activating the service failed, unexpectedly.  Updating CDRs to be re-normalised failed";
		}
		
		// Activating the account was successfull
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _GetAllServiceAddresses
	//------------------------------------------------------------------------//
	/**
	 * _GetAllServiceAddresses()
	 *
	 * Retrieves the address details of each service belonging to the account 
	 * 
	 * Retrieves the address details of each service belonging to the account
	 * 
	 * @param	int		$intAccount		Id of the Account
	 *
	 * @return		array				All ServiceAddress Records associated with the account
	 * @method		_GetAllServiceAddresses
	 */
	private function _GetAllServiceAddresses($intAccount)
	{
		$arrColumns = Array("Id"						=> "SA.Id",
							"Service"					=> "SA.Service",
							"Residential"				=> "SA.Residential",
							"BillName"					=> "SA.BillName",
							"BillAddress1"				=> "SA.BillAddress1",
							"BillAddress2"				=> "SA.BillAddress2",
							"BillLocality"				=> "SA.BillLocality",
							"BillPostcode"				=> "SA.BillPostcode",
							"EndUserTitle"				=> "SA.EndUserTitle",
							"EndUserGivenName"			=> "SA.EndUserGivenName",
							"EndUserFamilyName"			=> "SA.EndUserFamilyName",
							"EndUserCompanyName"		=> "SA.EndUserCompanyName",
							"DateOfBirth"				=> "SA.DateOfBirth",
							"Employer"					=> "SA.Employer",
							"Occupation"				=> "SA.Occupation",
							"ABN"						=> "SA.ABN",
							"TradingName"				=> "SA.TradingName",
							"ServiceAddressType"		=> "SA.ServiceAddressType",
							"ServiceAddressTypeNumber"	=> "SA.ServiceAddressTypeNumber",
							"ServiceAddressTypeSuffix"	=> "SA.ServiceAddressTypeSuffix",
							"ServiceStreetNumberStart"	=> "SA.ServiceStreetNumberStart",
							"ServiceStreetNumberEnd"	=> "SA.ServiceStreetNumberEnd",
							"ServiceStreetNumberSuffix"	=> "SA.ServiceStreetNumberSuffix",
							"ServiceStreetName"			=> "SA.ServiceStreetName",
							"ServiceStreetType"			=> "SA.ServiceStreetType",
							"ServiceStreetTypeSuffix"	=> "SA.ServiceStreetTypeSuffix",
							"ServicePropertyName"		=> "SA.ServicePropertyName",
							"ServiceLocality"			=> "SA.ServiceLocality",
							"ServiceState"				=> "SA.ServiceState",
							"ServicePostcode"			=> "SA.ServicePostcode",
							"FNN"						=> "S.FNN");
		$strTables	= "Service AS S INNER JOIN ServiceAddress AS SA ON S.Id = SA.Service";
		$strWhere	= "S.Account = <AccountId>";
		$selAddresses = new StatementSelect($strTables, $arrColumns, $strWhere, "S.FNN ASC, S.Status ASC");
		$mixResult = $selAddresses->Execute(Array("AccountId" => $intAccount));
		
		$arrAddresses = Array();
		if ($mixResult)
		{
			// The account has addresses associated with it
			$arrRecordSet = $selAddresses->FetchAll();
			
			foreach ($arrRecordSet as $arrRecord)
			{
				// Format the DateOfBirth property if it is not NULL
				if ($arrRecord['DateOfBirth'] != "")
				{
					$arrRecord['DateOfBirth'] = substr($arrRecord['DateOfBirth'], 6, 2) ."/". substr($arrRecord['DateOfBirth'], 4, 2) ."/". substr($arrRecord['DateOfBirth'], 0, 4);
				}
				
				$arrAddresses[$arrRecord["Service"]] = $arrRecord;
				
				// Build the Physical address description
				$arrAddresses[$arrRecord["Service"]]['PhysicalAddressDescription'] = $this->BuildPhysicalAddressDescription($arrRecord, ", ");
			}
		}
		
		return $arrAddresses;
	}
	
	//------------------------------------------------------------------------//
	// _SetDefaultValuesForServiceAddress
	//------------------------------------------------------------------------//
	/**
	 * _SetDefaultValuesForServiceAddress()
	 *
	 * Sets default values for a ServiceAddress DBObject based on an Account DBObject 
	 * 
	 * Sets default values for a ServiceAddress DBObject based on an Account DBObject
	 * Sets the following parameters with values taken from the Account DBObject:
	 * 	ABN, EndUserCompanyName, TradingName, BillName, BillAddress1, BillAddress2,
	 * 	BillLocality, BillPostcode, ServiceState
	 * 
	 * Also sets ServiceAddress->Residential to 0 representing a business landline service
	 * 
	 * @param	DBObject	$dboServiceAddress	The ServiceAddress DBObject to initialise with default
	 * 											values
	 * @param	DBObject	$dboAccount			The Account DBObject from which the initial values come
	 *
	 * @return	void
	 * @method	_SetDefaultValuesForServiceAddress
	 */
	private function _SetDefaultValuesForServiceAddress(&$dboServiceAddress, $dboAccount)
	{
		// Default to a business service
		$dboServiceAddress->Residential = 0;
		$dboServiceAddress->ABN = str_replace(" ", "", $dboAccount->ABN->Value);
		if ($dboAccount->BusinessName->Value != "")
		{
			$strCompanyName = $dboAccount->BusinessName->Value; 
		}
		else
		{
			$strCompanyName = $dboAccount->TradingName->Value;
		}
		$strCompanyName = substr($strCompanyName, 0, 50);
		$dboServiceAddress->EndUserCompanyName = ($strCompanyName !== FALSE) ? $strCompanyName : "";
		
		$strTradingName = substr($dboAccount->TradingName->Value, 0, 50);
		$dboServiceAddress->TradingName = ($strTradingName !== FALSE) ? $strTradingName : "";
		
		// Bill Address should default to the billing address for the account
		$strCompanyName = substr($strCompanyName, 0, 30);
		$dboServiceAddress->BillName = ($strCompanyName !== FALSE) ? $strCompanyName : "";
		
		$strAddress1 = substr($dboAccount->Address1->Value, 0, 30);
		$dboServiceAddress->BillAddress1 = ($strAddress1 !== FALSE) ? $strAddress1 : "";

		$strAddress2 = substr($dboAccount->Address2->Value, 0, 30);
		$dboServiceAddress->BillAddress2 = ($strAddress2 !== FALSE) ? $strAddress2 : "";

		$strLocality = substr($dboAccount->Suburb->Value, 0, 23);
		$dboServiceAddress->BillLocality = ($strLocality !== FALSE) ? $strLocality : "";

		$dboServiceAddress->BillPostcode = $dboAccount->Postcode->Value;
		$dboServiceAddress->ServiceState = $dboAccount->State->Value;
	}
	
	
	//------------------------------------------------------------------------//
	// GetMostRecentServiceRecordId
	//------------------------------------------------------------------------//
	/**
	 * GetMostRecentServiceRecordId()
	 *
	 * Returns the Id of the most recently added Service record which models the same Service as the record referenced by $intService
	 * 
	 * Returns the Id of the most recently added Service record which models the same Service as the record referenced by $intService
	 * If $intService is the most recent, then it will be returned
	 * 
	 * @param	int		$intService		Id of any of the service records modelling the service 
	 * 									for the account that the service record belongs to
	 *
	 * @return	int 					Service record Id
	 * @method
	 */
	function GetMostRecentServiceRecordId($intService)
	{
		$strWhere = "Account = (SELECT Account FROM Service WHERE Id = $intService) AND FNN = (SELECT FNN FROM Service WHERE Id = $intService)";
		$selMostRecentService = new StatementSelect("Service", "Id", $strWhere, "Id DESC", "1");
		if (!$selMostRecentService->Execute())
		{
			return FALSE;
		}
		
		$arrRecord = $selMostRecentService->Fetch();
		return $arrRecord['Id'];
	}
	
	//------------------------------------------------------------------------//
	// GetAllServiceRecordIds
	//------------------------------------------------------------------------//
	/**
	 * GetAllServiceRecordIds()
	 *
	 * Retrieves an array storing all Service Ids which are used to model the same Service that $intService references
	 * 
	 * Retrieves an array storing all Service Ids which are used to model the same Service that $intService references
	 * (for the account the $intService references)
	 * 
	 * @param	int		$intService		Id of any of the service records modelling the service 
	 * 									for the account that the service record belongs to
	 *
	 * @return	array					indexed array of Service Ids
	 * @method
	 */
	static function GetAllServiceRecordIds($intService)
	{
		$strWhere		= "Account = (SELECT Account FROM Service WHERE Id = $intService) AND FNN = (SELECT FNN FROM Service WHERE Id = $intService)";
		$selAllServices	= new StatementSelect("Service", "Id", $strWhere, "Id DESC");
		if (!$selAllServices->Execute())
		{
			return FALSE;
		}
		
		$arrRecordSet	= $selAllServices->FetchAll();
		$arrServiceIds	= Array();
		
		foreach ($arrRecordSet as $arrRecord)
		{
			$arrServiceIds[] = $arrRecord['Id'];
		}
		
		return $arrServiceIds;
	}
	
}
?>