<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// plan
//----------------------------------------------------------------------------//
/**
 * plan
 *
 * contains all ApplicationTemplate extended classes relating to Available Plans functionality
 *
 * contains all ApplicationTemplate extended classes relating to Available Plans functionality
 *
 * @file		plan.php
 * @language	PHP
 * @package		framework
 * @author		Ross
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplatePlan
//----------------------------------------------------------------------------//
/**
 * AppTemplatePlan
 *
 * The AppTemplatePlan class
 *
 * The AppTemplatePlan class.  This incorporates all logic for all pages
 * relating to Available Plans
 *
 *
 * @package	ui_app
 * @class	AppTemplatePlan
 * @extends	ApplicationTemplate
 */
class AppTemplatePlan extends ApplicationTemplate
{
	//------------------------------------------------------------------------//
	// _arrRateGroups
	//------------------------------------------------------------------------//
	/**
	 * _arrRateGroups
	 *
	 * List of RateGroups that will be associated with a Rate Plan, when saving a RatePlan
	 *
	 * List of RateGroups that will be associated with a Rate Plan, when saving a RatePlan
	 * This is modified by the _ValidatePlan method and used by the _SavePlan method
	 *
	 * @type		array
	 *
	 * @property
	 */
	private $_arrRateGroups = Array();

	//------------------------------------------------------------------------//
	// AvailablePlans
	//------------------------------------------------------------------------//
	/**
	 * AvailablePlans()
	 *
	 * Performs the logic for the AvailablePlans webpage
	 * 
	 * Performs the logic for the AvailablePlans webpage
	 * Initial DBObjects that can be set through GET or POST variables are:
	 *		DBO()->RatePlan->ServiceType	If you want to restrict the Plans listed, to those of the specified ServiceType only
	 * To View the Plans the user must have Operator privileges.  To Add new Plans and Edit existing ones, the user must have 
	 * Admin Privileges and Rate Management privileges
	 *
	 * @return		void
	 * @method
	 *
	 */
	function AvailablePlans()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		
		// Context menu
		// (Nothing to add)
		
		// Breadcrumb menu
		Breadcrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Available Plans");
		
		if (!(DBO()->RatePlan->GetLast->IsSet && DBO()->RatePlan->GetLast->Value))
		{
			// Don't base the plan retrieval on the constraints stored in the session
			unset($_SESSION['AvailablePlansPage']['Filter']);
		}
		
		// Update the Session details with the current filter, if one has been specified
		if (DBO()->RatePlan->ServiceType->IsSet)
		{
			// A valid ServiceType filter has been specified
			if (array_key_exists(DBO()->RatePlan->ServiceType->Value, $GLOBALS['*arrConstant']['service_type']))
			{
				// A specific ServiceType has been requested
				$_SESSION['AvailablePlansPage']['Filter']['ServiceType'] = DBO()->RatePlan->ServiceType->Value;
			}
			else
			{
				// Show all services types
				$_SESSION['AvailablePlansPage']['Filter']['ServiceType'] = 0;
			}
			
		}
		elseif (!isset($_SESSION['AvailablePlansPage']['Filter']['ServiceType']))
		{
			// A ServiceType filter hasn't been specified, and one isn't currently cached, set it to view all ServiceTypes
			$_SESSION['AvailablePlansPage']['Filter']['ServiceType'] = 0;
		}
		
		if (DBO()->RatePlan->CustomerGroup->IsSet)
		{
			if (array_key_exists(DBO()->RatePlan->CustomerGroup->Value, Customer_Group::getAll()))
			{
				// A specific CustomerGroup filter has been specified
				$_SESSION['AvailablePlansPage']['Filter']['CustomerGroup'] = DBO()->RatePlan->CustomerGroup->Value;
			}
			else
			{
				// Show all CustomerGroups
				$_SESSION['AvailablePlansPage']['Filter']['CustomerGroup'] = 0;
			}
		}
		elseif (!isset($_SESSION['AvailablePlansPage']['Filter']['ServiceType']))
		{
			// A CustomerGroup filter hasn't been specified, and one isn't currently cached, set it to view all CustomerGroups
			$_SESSION['AvailablePlansPage']['Filter']['CustomerGroup'] = 0;
		}
		
		if (DBO()->RatePlan->Status->IsSet)
		{
			// A valid ServiceType filter has been specified
			if (array_key_exists(DBO()->RatePlan->Status->Value, $GLOBALS['*arrConstant']['RateStatus']))
			{
				// A specific RateStatus has been requested
				$_SESSION['AvailablePlansPage']['Filter']['Status'] = DBO()->RatePlan->Status->Value;
			}
			else
			{
				// Show all RatePlans
				$_SESSION['AvailablePlansPage']['Filter']['Status'] = -1;
			}
			
		}
		elseif (!isset($_SESSION['AvailablePlansPage']['Filter']['Status']))
		{
			// A Status filter hasn't been specified, and one isn't currently cached, set it to view all Active RatePlans
			$_SESSION['AvailablePlansPage']['Filter']['Status'] = 0;
		}
		
		// Retrieve all RatePlans that satisfy the filter conditions
		$strServiceTypeFilter	= "TRUE";
		$strCustomerGroupFilter	= "TRUE";
		$strStatusFilter		= "TRUE";
		if ($_SESSION['AvailablePlansPage']['Filter']['ServiceType'])
		{
			$strServiceTypeFilter = "RP.ServiceType = <ServiceType>";
		}
		if ($_SESSION['AvailablePlansPage']['Filter']['CustomerGroup'])
		{
			$strCustomerGroupFilter = "RP.customer_group = <CustomerGroup>";
		}
		if (array_key_exists($_SESSION['AvailablePlansPage']['Filter']['Status'], $GLOBALS['*arrConstant']['RateStatus']))
		{
			$strStatusFilter = "RP.Archived = <Status>";
		}
		
		$strWhere = "$strServiceTypeFilter AND $strCustomerGroupFilter AND $strStatusFilter";
		$arrWhere = array(
							"ServiceType"	=> $_SESSION['AvailablePlansPage']['Filter']['ServiceType'],
							"CustomerGroup"	=> $_SESSION['AvailablePlansPage']['Filter']['CustomerGroup'],
							"Status"		=> $_SESSION['AvailablePlansPage']['Filter']['Status']
						);
		
		/*$arrColumns = array(
							"Id"					=> "RP.Id",
							"ServiceType"			=> "RP.ServiceType",
							"Name"					=> "RP.Name",
							"Description"			=> "RP.Description",
							"CarrierFullService"	=> "RP.CarrierFullService",
							"CarrierPreselection"	=> "RP.CarrierPreselection",
							"customer_group"		=> "RP.customer_group",
							"Archived"				=> "RP.Archived",
							"IsDefault"				=> "CASE WHEN drp.Id IS NOT NULL THEN TRUE ELSE FALSE END",
							"DealerCount"			=> "COALESCE(DRP.DealerCount, 0)"
							);*/
		$strColumns		= "RP.*, CASE WHEN drp.Id IS NOT NULL THEN TRUE ELSE FALSE END AS IsDefault, COALESCE(DRP.DealerCount, 0) AS DealerCount";
		$strTables		= "RatePlan AS RP LEFT JOIN default_rate_plan AS drp ON RP.Id = drp.rate_plan AND RP.customer_group = drp.customer_group AND RP.ServiceType = drp.service_type LEFT OUTER JOIN (SELECT rate_plan_id AS RatePlanId, COUNT(id) AS DealerCount FROM dealer_rate_plan GROUP BY rate_plan_id) AS DRP ON RP.Id = DRP.RatePlanId";
		$strOrderBy		= "ServiceType, Name, customer_group";
		$selRatePlans	= new StatementSelect($strTables, $strColumns, $strWhere, $strOrderBy);
		if ($selRatePlans->Execute($arrWhere) === FALSE)
		{
			DBO()->Error->Message = "Unexpected database error occurred when trying to retrieve RatePlans.  Please notify your system administrator";
			$this->LoadPage('error');
			return FALSE;
		}
		
		DBO()->RatePlans->AsArray = $selRatePlans->FetchAll();
		
	
		$this->LoadPage('plans_list');

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// TogglePlanStatus
	//------------------------------------------------------------------------//
	/**
	 * TogglePlanStatus()
	 *
	 * Changes the status of a plan between Active and Archived
	 * 
	 * Changes the status of a plan between Active and Archived
	 * It expects the following objects to be defined
	 * 	DBO()->RatePlan->Id		Id of the RatePlan to toggle the status of
	 *
	 * @return		void
	 * @method
	 */
	function TogglePlanStatus()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
		
		if (!DBO()->RatePlan->Load())
		{
			Ajax()->AddCommand("Alert", "ERROR: Could not find RatePlan with Id: ". DBO()->RatePlan->Id->Value);
			return TRUE;
		}
		
		// The status of a RatePlan is stored in the Archived property of the RatePlan table
		switch (DBO()->RatePlan->Archived->Value)
		{
			case RATE_STATUS_ACTIVE:
				DBO()->RatePlan->Archived = RATE_STATUS_ARCHIVED;
				break;
				
			case RATE_STATUS_ARCHIVED:
				DBO()->RatePlan->Archived = RATE_STATUS_ACTIVE;
				break;
				
			default:
				// Cannot toggle from whatever the status currently is
				Ajax()->AddCommand("Alert", "ERROR: The RatePlan's status cannot be changed");
				return TRUE;
		}
		
		// Check that the plan isn't one of the default plans for the Customer Group
		DBL()->default_rate_plan->rate_plan = DBO()->RatePlan->Id->Value;
		DBL()->default_rate_plan->Load();
		if (DBL()->default_rate_plan->RecordCount() > 0)
		{
			Ajax()->AddCommand("Alert", "ERROR: This Plan is being used as a default rate plan and cannot have its status changed");
			return TRUE;
		}
		
		TransactionStart();
		
		// Save the changes
		if (!DBO()->RatePlan->Save())
		{
			TransactionRollback();
			Ajax()->AddCommand("Alert", "ERROR: Saving the status change failed, unexpectedly.  Please notify your system administrator");
			return TRUE;
		}
		
		$strSuccessMsg = "Status change was successful";
		
		if (DBO()->AlternateRatePlan->Id->Value && DBO()->RatePlan->Archived == RATE_STATUS_ARCHIVED)
		{
			// Associate the Alternate RatePlan with all the dealers that are associated with the rate plan which was just archived
			$intArchivedPlanId	= DBO()->RatePlan->Id->Value;
			$intAlternatePlanId	= DBO()->AlternateRatePlan->Id->Value;
			
			$objQuery = new Query();
			
			$strQuery = "	INSERT INTO dealer_rate_plan (dealer_id, rate_plan_id)
							SELECT DISTINCT drp.dealer_id, $intAlternatePlanId
							FROM (	SELECT dealer_id
									FROM dealer_rate_plan
									WHERE dealer_id IN (SELECT dealer_id FROM dealer_rate_plan WHERE rate_plan_id = $intArchivedPlanId)
									AND dealer_id NOT IN (SELECT dealer_id FROM dealer_rate_plan WHERE rate_plan_id = $intAlternatePlanId)
								) AS drp;
							";
			if ($objQuery->Execute($strQuery) === FALSE)
			{
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Could not add records to the dealer_rate_plan table to associate the alternate plan");
				return TRUE;
			}
		}
		
		TransactionCommit();
		
		// Update the status of the RatePlan in the Sales database, if there is one
		if (Flex_Module::isActive(FLEX_MODULE_SALES_PORTAL))
		{
			try
			{
				Cli_App_Sales::pushAll();
			}
			catch (Exception $e)
			{
				// Pushing the data failed
				$strSuccessMsg .= "<br /><span class='warning'>WARNING: Pushing the data from Flex to the Sales database, failed. Contact your system administrators to have them manually trigger the data push.<br />Error message: ". htmlspecialchars($e->getMessage()) ."</span>";
			}
		}
		
		// Everything worked
		Ajax()->AddCommand("AlertReload", $strSuccessMsg);
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// Add
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Performs the logic for the Add Rate Plan webpage
	 * 
	 * Performs the logic for the Add Rate Plan webpage
	 * Initial DBObjects that can be set through GET or POST variables are:
	 *		DBO()->RatePlan->Id			If you want to edit an existing draft Rate Plan
	 *		DBO()->BaseRatePlan->Id		If you want to add a new Rate Plan, based on an existing one defined by this value
	 *		DBO()->CallingPage->Href	If you want to specify the href of the page that called this one
	 *		The user needs PERMISSION_RATE_MANAGEMENT and PERMISSION_ADMIN permissions to view this page
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Add()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		// The User needs both Rate Management and Admin Permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
		
		// Context menu
		// Nothing to add
		
		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AvailablePlans(TRUE);
		BreadCrumb()->SetCurrentPage("Add Rate Plan");
		
		// Handle form submittion
		if (SubmittedForm('AddPlan', 'Commit') || SubmittedForm('AddPlan', 'Save as Draft'))
		{
			// Validate the plan
			$mixResult = $this->_ValidatePlan();
			if ($mixResult !== TRUE && $mixResult !== FALSE)
			{
				// The plan is invalid and an error message has been returned
				Ajax()->AddCommand("Alert", $mixResult);
				return TRUE;
			}
			elseif ($mixResult === FALSE)
			{
				// The plan is invalid and no error message was specified, so it is assumed appropraite actions have already taken place
				return TRUE;
			}
			else
			{
				// The plan is valid.  Save it to the database
				TransactionStart();
				$mixResult = $this->_SavePlan();
				if ($mixResult !== TRUE && $mixResult !== FALSE)
				{
					// Saving the plan failed, and an error message has been returned
					TransactionRollback();
					Ajax()->AddCommand("Alert", $mixResult);
					return TRUE;
				}
				elseif ($mixResult === FALSE)
				{
					// Saving the plan failed, and no error message was specified, so it is assumed appropraite actions have already taken place
					TransactionRollback();
					return TRUE;
				}
				else
				{
					// The plan was successfully saved to the database
					TransactionCommit();
					
					// Set the message appropriate to the action
					$strSuccessMsg = (DBO()->Plan->Archived->Value == RATE_STATUS_ACTIVE)? "The plan has been successfully saved" : "The plan has been successfully saved as a draft";
					Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => $strSuccessMsg, "Location" => Href()->AvailablePlans()));
					return TRUE;
				}
			}
		}
		
		// Check if there has been a BaseRatePlan.Id specified, to base the new RatePlan on
		if (DBO()->BaseRatePlan->Id->Value)
		{
			// There is, so load it
			DBO()->RatePlan->Id = DBO()->BaseRatePlan->Id->Value;
			if (!DBO()->RatePlan->Load())
			{
				// Could not load the RatePlan
				DBO()->Error->Message = "The RatePlan with id: ". DBO()->RatePlan->Id->value ." could not be found";
				$this->LoadPage('error');
				return FALSE;
			}
			
			// Reset the Id of the RatePlan, because we are creating a new one, not editing an existing one
			DBO()->RatePlan->Id = 0;
		}
		elseif (DBO()->RatePlan->Id->Value)
		{
			// We are opening an existing RatePlan for editing
			// Update the Breadcrumb menu
			BreadCrumb()->SetCurrentPage("Edit Draft Rate Plan");
			
			// We want to display an existing RatePlan
			if (!DBO()->RatePlan->Load())
			{
				// Could not load the RatePlan
				DBO()->Error->Message = "The RatePlan with id: ". DBO()->RatePlan->Id->value ." could not be found";
				$this->LoadPage('error');
				return FALSE;
			}
			
			// Make sure the Rate Plan is a draft
			if (DBO()->RatePlan->Archived->Value != RATE_STATUS_DRAFT)
			{
				// Can't edit the Rate Plan
				DBO()->Error->Message = "The RatePlan with id: ". DBO()->RatePlan->Id->value ." and Name \"". DBO()->RatePlan->Name->Value ."\" is not a draft and therefore cannot be edited";
				$this->LoadPage('error');
				return FALSE;
			}
		}
		else
		{
			// We want to add a new RatePlan
			DBO()->RatePlan->Id = 0;
		}
		
		$this->LoadPage('rate_plan_add');

		return TRUE;
	
	}
	
	//------------------------------------------------------------------------//
	// View
	//------------------------------------------------------------------------//
	/**
	 * View()
	 *
	 * Performs the logic for the View Rate Plan webpage
	 * 
	 * Performs the logic for the View Rate Plan webpage
	 * Initial DBObjects that can be set through GET or POST variables are:
	 *		DBO()->RatePlan->Id			Id of the RatePlan you want to view
	 *
	 * @return		void
	 * @method
	 */
	function View()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR_VIEW);
		
		// Context menu
		// Nothing to add
		
		// Breadcrumb menu
		BreadCrumb()->Employee_Console();
		BreadCrumb()->AvailablePlans(TRUE);
		BreadCrumb()->SetCurrentPage("Rate Plan");
		
		if (!DBO()->RatePlan->Load())
		{
			// Could not load the RatePlan
			DBO()->Error->Message = "The RatePlan with id: ". DBO()->RatePlan->Id->value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Load all the RateGroups belonging to the RatePlan
		$strWhere = "Id IN (SELECT RateGroup FROM RatePlanRateGroup WHERE RatePlan = ". DBO()->RatePlan->Id->Value .")";
		DBL()->RateGroup->Where->SetString($strWhere);
		DBL()->RateGroup->OrderBy("Name");
		DBL()->RateGroup->Load();
		
		
		$this->LoadPage('rate_plan_view');

		return TRUE;
	
	}


	//------------------------------------------------------------------------//
	// _ValidatePlan
	//------------------------------------------------------------------------//
	/**
	 * _ValidatePlan()
	 *
	 * Validates the Rate Plan
	 * 
	 * Validates the Rate Plan
	 * This will only work with the "Add Rate Plan" webpage as it assumes specific DBObjects have been defined within DBO()
	 *
	 * @return		mix							returns TRUE if the new RatePlan saved successfully, else it returns
	 *											a specific error message detailing why the RatePlan could not be saved
	 * @method
	 *
	 */
	private function _ValidatePlan($bolSaveAsDraft=FALSE)
	{
		/* 
		 * Validation process:
		 *		V1: Check that a Name and Description have been declared
		 *		V1: Check that the MinCharge, ChargeCap and UsageCap are valid monetary values
		 *		V1: Check that the "scalable" details are valid, if the plan has been flagged as scalable 
		 *		V2: Check that a service type has been declared
		 *		V3: If ServiceType == LandLine, Check that CarrierFullService and CarrierPreselection have been declared
		 *		V4: Check that the Name is unique when compared with all other Rate Plans, for a given CustomerGroup/ServiceType combination (including all archived and draft plans)
		 *		V5: Check that a non-fleet Rate Group has been declared for each RecordType which is Required
		 */
	
		// V1: Validate the fields
		if (DBO()->RatePlan->IsInvalid())
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: Invalid fields are highlighted";
		}
		
		// Nullify fields that can be null
		if ((float)DBO()->RatePlan->discount_cap->Value == 0)
		{
			DBO()->RatePlan->discount_cap = NULL;			
		}
		if ((float)DBO()->RatePlan->RecurringCharge->Value == 0)
		{
			DBO()->RatePlan->RecurringCharge = NULL;
		}
		
		if (DBO()->RatePlan->scalable->Value == TRUE)
		{
			$arrErrors = array();
			// The plan is scalable.  Validate the min and max services
			if ((!is_numeric(DBO()->RatePlan->minimum_services->Value)) || ((integer)DBO()->RatePlan->minimum_services->Value < 0))
			{
				DBO()->RatePlan->minimum_services->SetToInvalid();
				$arrErrors[] = "Minimum Services must be a positive whole number";
			}
			if ((!is_numeric(DBO()->RatePlan->maximum_services->Value)) || ((integer)DBO()->RatePlan->maximum_services->Value < 1))
			{
				DBO()->RatePlan->maximum_services->SetToInvalid();
				$arrErrors[] = "Maximum Services must be a positive whole number, greater than 0";
			}
			if (count($arrErrors))
			{
				// Errors have been encountered
				Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
				return "ERROR: " . implode(".  ", $arrErrors) . ".";
			}
			
			$intMinServices = (integer)DBO()->RatePlan->minimum_services->Value;
			$intMaxServices = (integer)DBO()->RatePlan->maximum_services->Value;
			if ($intMinServices > $intMaxServices)
			{
				DBO()->RatePlan->minimum_services->SetToInvalid();
				DBO()->RatePlan->maximum_services->SetToInvalid();
				Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
				return "ERROR: Minimum Services must be smaller than or equal to Maximum Services";
			}
		}
		else
		{
			DBO()->RatePlan->minimum_services = NULL;
			DBO()->RatePlan->maximum_services = NULL;
		}
	
		if ((integer)DBO()->RatePlan->ContractTerm->Value > 0)
		{
			$arrErrors = array();
			// The Contract Term has been specified, validate the Details
			if ((float)DBO()->RatePlan->contract_exit_fee->Value < 0)
			{
				DBO()->RatePlan->contract_exit_fee->SetToInvalid();
				$arrErrors[] = "Contract Exit Fee must be greater than or equal to \$0";
			}
			if ((float)DBO()->RatePlan->contract_payout_percentage->Value < 0)
			{
				DBO()->RatePlan->contract_payout_percentage->SetToInvalid();
				$arrErrors[] = "Contract Payout must be greater than or equal to 0%";
			}
			if (count($arrErrors))
			{
				// Errors have been encountered
				Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
				return "ERROR: " . implode(".  ", $arrErrors) . ".";
			}
		}
		else
		{
			DBO()->RatePlan->ContractTerm				= NULL;
			DBO()->RatePlan->contract_exit_fee			= 0.0;
			DBO()->RatePlan->contract_payout_percentage	= 0.0;
		}
		
		// Included Data
		DBO()->RatePlan->included_data	= max(0, (int)DBO()->RatePlan->included_data->Value);
		DBO()->RatePlan->included_data	= (DBO()->RatePlan->included_data->Value > 0) ? DBO()->RatePlan->included_data->Value * 1024 : 0;
		
		// Commissionable Value
		$fltCommissionableValue	= (float)DBO()->RatePlan->commissionable_value->Value;
		if ($fltCommissionableValue < 0)
		{
			DBO()->RatePlan->commissionable_value->SetToInvalid();
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: The Commissionable Value must be greater than or equal to \$0.00";
		}
		DBO()->RatePlan->commissionable_value	= $fltCommissionableValue;
		
		// V2: ServiceType
		if (!DBO()->RatePlan->ServiceType->Value)
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A service type must be selected";
		}
		
		// V3: CarrierFullService and CarrierPreselection are manditory for landlines only
		if (DBO()->RatePlan->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
		{
			// CarrierFullService
			if (!DBO()->RatePlan->CarrierFullService->Value)
			{
				Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
				$strServiceType = GetConstantDescription(DBO()->RatePlan->ServiceType->Value, "service_type");
				return "ERROR: $strServiceType requires Carrier Full Service to be declared";
			}
			// CarrierPreselection
			if (!DBO()->RatePlan->CarrierPreselection->Value)
			{
				Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
				$strServiceType = GetConstantDescription(DBO()->RatePlan->ServiceType->Value, "service_type");
				return "ERROR: $strServiceType requires Carrier Preselection to be declared";
			}
		}
		
		// V4: Make sure the name of the rate plan isn't currently in use for this CustomerGroup/ServiceType
		if (DBO()->RatePlan->Id->Value == 0)
		{
			// The RatePlan name should not be in the database
			$strWhere = "Name=<Name> AND ServiceType = <ServiceType> AND customer_group = <CustomerGroup>";
		}
		else
		{
			// We are working with an already saved draft.  Check that the New name is not used by any other RatePlan
			$strWhere = "Name=<Name> AND ServiceType = <ServiceType> AND customer_group = <CustomerGroup> AND Id != ". DBO()->RatePlan->Id->Value;
		}
		$arrWhere = array(
							"Name"			=> DBO()->RatePlan->Name->Value,
							"ServiceType"	=> DBO()->RatePlan->ServiceType->Value,
							"CustomerGroup"	=> DBO()->RatePlan->customer_group->Value
						);
		$selRatePlanName = new StatementSelect("RatePlan", "Id", $strWhere);
		if ($selRatePlanName->Execute($arrWhere) > 0)
		{
			// The Name is already being used by another rate plan
			$strServiceType = GetConstantDescription(DBO()->RatePlan->ServiceType->Value, "service_type");
			$strCustomerGroup = Customer_Group::getForId(DBO()->RatePlan->customer_group->Value)->externalName;
			DBO()->RatePlan->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: The $strCustomerGroup customer group already has a plan with this name, for $strServiceType services<br />Please choose a unique name";
		}
		
		
		// V5: Check that a rate group has been defined for each RecordType that has been marked as required
		DBL()->RecordType->ServiceType = DBO()->RatePlan->ServiceType->Value;
		DBL()->RecordType->Load();
		
		// Find the declared rate group for each RecordType required of the RatePlan
		foreach (DBL()->RecordType as $dboRecordType)
		{
			$intRateGroup = NULL;
			$intFleetRateGroup = NULL;
			
			// Build the name of the object storing the Rate Group details for this particular record type
			$strObject = "RateGroup" . $dboRecordType->Id->Value;
			if (DBO()->{$strObject}->RateGroupId->IsSet)
			{
				// A RateGroup has been specified for this ServiceType
				$intRateGroup = DBO()->{$strObject}->RateGroupId->Value;
				$intFleetRateGroup = DBO()->{$strObject}->FleetRateGroupId->Value;
				
				// Check if a rate group has not been chosen for this record type, but is required
				if (($intRateGroup == 0) && ($dboRecordType->Required->Value == TRUE))
				{
					// A rate group is required but hasn't been specified
					return "ERROR: Not all required rate groups have been specified";
				}
				elseif ($intRateGroup > 0)
				{
					// Add the rategroup to the list of rate groups
					$this->_arrRateGroups[] = $intRateGroup;
				}
				
				if ($intFleetRateGroup > 0)
				{
					// Add the fleet rate group to the list of rate groups
					$this->_arrRateGroups[] = $intFleetRateGroup;
				}
			}
			elseif ($dboRecordType->Required->Value == TRUE)
			{
				// The RatePlan requires a RateGroup of this RecordType, but one has not been declared
				//NOTE! This is only run if the RecordType was not associated with the ServiceType before loading the RateGroupDiv contents
				$this->GetRateGroupsForm();
				return "ERROR: A new record type has been associated with this service type, since you chose the service type of the plan";
			}
			else
			{
				// A RateGroup associated with the RecordType, was not specified and not required
				continue;
			}
		}
		
		// Make sure all the RateGroups are valid (pretty much just checking that there are no over or under allocations)
		$strRateGroups = implode(", ", $this->_arrRateGroups);
		$selRateGroups = new StatementSelect("RateGroup", "*", "Id IN ($strRateGroups)");
		$selRateGroups->Execute();
		$arrRateGroups = $selRateGroups->FetchAll();
		
		// Validate each RateGroup that is currently saved as a draft
		$appRateGroup = new AppTemplateRateGroup();
		foreach ($arrRateGroups as $arrRateGroup)
		{
			if ($arrRateGroup['Archived'] == RATE_STATUS_DRAFT && !$appRateGroup->IsValidRateGroup($arrRateGroup['Id']))
			{
				// The RateGroup is invalid
				return "ERROR: The Draft RateGroup, '{$arrRateGroup['Name']}', is currently invalid.  Saving of the Plan has been aborted.";
			}
		}
		
		// All validation has been completed successfully
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// _SavePlan
	//------------------------------------------------------------------------//
	/**
	 * _SavePlan()
	 *
	 * Saves the records to the database, required for defining a RatePlan
	 * 
	 * Saves the records to the database, required for defining a RatePlan
	 * This will only work with the "Add Rate Plan" webpage as it assumes specific DBObjects have been defined within DBO()
	 *
	 * @return		mix							returns TRUE if the new RatePlan saved successfully, else it returns
	 *											a specific error message detailing why the RatePlan could not be saved
	 * @method
	 *
	 */
	private function _SavePlan()
	{
		/* 
		 * Saving process:
		 *		S1: Set up values for properties of the RatePlan object that are not already defined, or not in their database correct format
		 *		S2: Save the record to the RatePlan table
		 *		S3: Remove any records in the RatePlanRateGroup table
		 *		S4: For each RateGroup belonging to this RatePlan:
		 *				add a record to the RatePlanRateGroup table
		 *		S5: If the RatePlan is being Committed: (not being saved as a draft)
		 *				For each draft RateGroup belonging to this RatePlan:
		 *					update the Archived property of the RateGroup in the RateGroup table so that it is now a committed RateGroup, not a draft
		 *					For each draft Rate belonging to the draft RateGroup:
		 *						update the Archived property of the Rate in the Rate table so that it is now a committed Rate, not a draft
		 */
	
		// S1: Set up the remaing fields required of a RatePlan record
		DBO()->RatePlan->MinMonthly	= ltrim(DBO()->RatePlan->MinMonthly->Value, "$");
		DBO()->RatePlan->ChargeCap	= ltrim(DBO()->RatePlan->ChargeCap->Value, "$");
		DBO()->RatePlan->UsageCap	= ltrim(DBO()->RatePlan->UsageCap->Value, "$");
		
		if (SubmittedForm('AddPlan', 'Save as Draft'))
		{
			// Flag the plan as being a draft
			DBO()->RatePlan->Archived = RATE_STATUS_DRAFT;
		}
		else
		{
			// The plan is not being saved as a draft
			DBO()->RatePlan->Archived = RATE_STATUS_ACTIVE;
		}
		
		DBO()->RatePlan->modified_employee_id	= Flex::getUserId();
		if (!DBO()->RatePlan->Id->Value)
		{
			// Plan has not been saved before
			DBO()->RatePlan->created_employee_id	= Flex::getUserId();
		}

		// If the RatePlan has already been saved as a draft then load in the details that don't get edited here, so they don't get erased
		if (DBO()->RatePlan->Id->Value)
		{
			DBO()->RatePlan->LoadMerge();
		}

		// S2: Save the plan to the database
		if (!DBO()->RatePlan->Save())
		{
			// Saving failed
			return "ERROR: Saving the RatePlan to the RatePlan database table failed, unexpectedly";
		}
		
		// S3: Remove all records from the RatePlanRateGroup table where RatePlan == DBO()->RatePlan->Id->Value
		$delRatePlanRateGroup = new Query();
		$delRatePlanRateGroup->Execute("DELETE FROM RatePlanRateGroup WHERE RatePlan = " . DBO()->RatePlan->Id->Value);

		// S4: Save each of the RateGroups associated with the RatePlan to the RatePlanRateGroup table
		DBO()->RatePlanRateGroup->RatePlan = DBO()->RatePlan->Id->Value;
		foreach ($this->_arrRateGroups as $intRateGroup)
		{
			DBO()->RatePlanRateGroup->Id = 0;
			DBO()->RatePlanRateGroup->RateGroup = $intRateGroup;
			
			if (!DBO()->RatePlanRateGroup->Save())
			{
				// Saving failed
				return "ERROR: Saving one of the RateGroup - RatePlan associations failed, unexpectedly.<br />The RatePlan has not been saved";
			}
		}
		
		// S5: If the RatePlan is being committed then all draft RateGroups used by it must be commited and all draft Rates
		// used by the draft RateGroups must be committed
		if ((SubmittedForm('AddPlan', 'Commit')) && (count($this->_arrRateGroups) > 0))
		{
			$strRateGroups 	= implode(',', $this->_arrRateGroups);
			$arrUpdate		= Array("Archived" => RATE_STATUS_ACTIVE);
			$updRateGroups 	= new StatementUpdate("RateGroup", "Archived = ". RATE_STATUS_DRAFT ." AND Id IN ($strRateGroups)", $arrUpdate);
			$updRates 		= new StatementUpdate("Rate", "Archived = ". RATE_STATUS_DRAFT ." AND Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup IN ($strRateGroups))", $arrUpdate);
			
			if ($updRateGroups->Execute($arrUpdate, NULL) === FALSE)
			{
				return "ERROR: Commiting one of the Draft Rate Groups, used by this Rate Plan, failed.<br />The RatePlan has not been saved";
			}
			
			if ($updRates->Execute($arrUpdate, NULL) === FALSE)
			{
				return "ERROR: Commiting one of the Draft Rates, used by this Rate Plan, failed.<br />The RatePlan has not been saved";
			}
		}
		
		// Everything has been saved
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// GetRateGroupsForm
	//------------------------------------------------------------------------//
	/**
	 * GetRateGroupsForm()
	 *
	 * Renders the Rate Groups delaration section of the "Add Rate Plan" form, via an ajax command
	 * 
	 * Renders the Rate Groups delaration section of the "Add Rate Plan" form, via an ajax command
	 * This will only work with the "Add Rate Plan" webpage as it assumes specific DBObjects have been defined within DBO()
	 * This function expects DBO()->ServiceType->Id to be set, as it only displays the RateGroups for the RecordTypes belonging to the ServiceType
	 * If (DBO()->RatePlan->Id is set XOR DBO()->BaseRatePlan->Id is set) then it will flag which RateGroups are currently used by the RatePlan
	 *
	 * @return		bool			TRUE if successfull
	 * @method
	 *
	 */
	function GetRateGroupsForm()
	{
		// We do not need to authorise the user as this only draws a subsection of one of the forms
	
		if (!DBO()->RatePlan->ServiceType->Value)
		{
			// A service type was not actually chosen 
			Ajax()->RenderHtmlTemplate("PlanAdd", HTML_CONTEXT_RATE_GROUPS_EMPTY, "RateGroupsDiv");
			return TRUE;
		}
	
		// Find all RecordTypes belonging to this ServiceType
		DBL()->RecordType->ServiceType = DBO()->RatePlan->ServiceType->Value;
		DBL()->RecordType->OrderBy("Name");
		DBL()->RecordType->Load();
		
		// Find all Rate Groups for this ServiceType that aren't archived (archived can equal, 0 (not archived), 1 (archived) or 2 (not yet committed/draft))
		$strWhere = "ServiceType = <ServiceType> AND Archived != " . RATE_STATUS_ARCHIVED;
		DBL()->RateGroup->Where->Set($strWhere, Array('ServiceType' => DBO()->RatePlan->ServiceType->Value));
		DBL()->RateGroup->OrderBy("Description");
		DBL()->RateGroup->Load();
		
		// If a RatePlan.Id xor BaseRatePlan.Id has been specified then we want to mark which of these rate groups belong to it
		if (DBO()->RatePlan->Id->Value)
		{
			$intRatePlanId = DBO()->RatePlan->Id->Value;
		}
		elseif (DBO()->BaseRatePlan->Id->Value)
		{
			$intRatePlanId = DBO()->BaseRatePlan->Id->Value;
		}
		if (IsSet($intRatePlanId))
		{
			// Find all the RateGroups currently used by this RatePlan
			DBL()->RatePlanRateGroup->RatePlan = $intRatePlanId;
			DBL()->RatePlanRateGroup->Load();
			
			// Mark the RateGroups that are currently used
			foreach (DBL()->RatePlanRateGroup as $dboRatePlanRateGroup)
			{
				foreach (DBL()->RateGroup as $dboRateGroup)
				{
					if ($dboRatePlanRateGroup->RateGroup->Value == $dboRateGroup->Id->Value)
					{
						// Mark this rate group as being selected
						$dboRateGroup->Selected = TRUE;
						break;
					}
				}
			}
		}
		
		// Render the html template
		Ajax()->RenderHtmlTemplate("PlanAdd", HTML_CONTEXT_RATE_GROUPS, "RateGroupsDiv");
		
		// Set the focus to the Rate Group combobox of the first RecordType to display
		/*  This is not done anymore and can be removed
		if (DBL()->RecordType->RecordCount() > 0)
		{
			DBL()->RecordType->rewind();
			$dboFirstRecordType = DBL()->RecordType->current();
			$strElement = "RateGroup" . $dboFirstRecordType->Id->Value . ".RateGroupId";
			Ajax()->AddCommand("SetFocus", $strElement);
		}
		*/
		return TRUE;
	}
	
	
}
?>