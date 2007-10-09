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
	 *
	 * @return		void
	 * @method
	 *
	 */
	function AvailablePlans()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_OPERATOR);
		
		// context menu
		ContextMenu()->Employee_Console();
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		BreadCrumb()->AdminConsole();
		BreadCrumb()->SetCurrentPage("Available Plans");
		
		// Retrieve all RatePlans that aren't currently archived
		
		// Check if a filter has been specified
		if (DBO()->RatePlan->ServiceType->Value)
		{
			// A filter has been specified.  Only retrieve records of the desired ServiceType
			$strWhere = "Archived != <Archived> AND ServiceType = <ServiceType>";
		}
		else
		{
			// A filter has not been specified
			$strWhere = "Archived != <Archived>";
		}
		
		DBL()->RatePlan->Where->Set($strWhere, Array("Archived" => ARCHIVE_STATUS_ARCHIVED, "ServiceType"=>DBO()->RatePlan->ServiceType->Value));
		DBL()->RatePlan->OrderBy("ServiceType, Name");
		DBL()->RatePlan->Load();
	
		$this->LoadPage('plans_list');

		return TRUE;
	}
	
	function RateList()
	{
		$intRatePlan = DBO()->RatePlan->Id->Value;		
		//$arrColumns = Array('Rate.Id', 'Rate.Description', 'RateGroup.Name', 'RecordType.Description as rategroup');
		$arrColumns = Array('Id' => 'Rate.Id', 'Description' => 'Rate.Description', 'Name' => 'RateGroup.Name', 'RateGroup' => 'RecordType.Description');
		DBL()->RateList->SetColumns($arrColumns);
		DBL()->RateList->SetTable("Rate, RatePlanRateGroup, RateGroupRate, RateGroup, RecordType");
		$strWhere = "Rate.Id = RateGroupRate.Rate ".
					"AND RateGroup.Id = RateGroupRate.RateGroup ".
					"AND RateGroupRate.RateGroup = RatePlanRateGroup.RateGroup ".
					"AND RecordType.Id = Rate.RecordType ".
					"AND RatePlanRateGroup.RatePlan = $intRatePlan";
		DBL()->RateList->Where->SetString($strWhere);
		DBL()->RateList->OrderBy("Rate.Description ");
		DBL()->RateList->SetLimit(30);
		DBL()->RateList->Load();
		
		//DBL()->RateList->ShowInfo();

		// Should probably check user authorization here
		//TODO!include user authorisation
		AuthenticatedUser()->CheckAuth();
		// context menu
		//TODO! define what goes in the context menu
		/*ContextMenu()->Contact_Retrieve->Account->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Account->View_Account(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Service->Invoices_And_Payments(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Service->View_Account(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->Add_Adjustment(DBO()->Account->Id->Value);
		ContextMenu()->Contact_Retrieve->View_Notes(DBO()->Account->Id->Value);*/
		
		// Console and logout should appear by default, no?
		ContextMenu()->Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		//TODO! define what goes in the breadcrumb menu (assuming this page uses one)
		//BreadCrumb()->Invoices_And_Payments(DBO()->Account->Id->Value);
		
		
		// Setup all DBO and DBL objects required for the page
		//TODO!
		// The account should already be set up as a DBObject because it will be specified as a GET variable or a POST variable
		/*if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}*/
		
		// the DBList storing the invoices should be ordered so that the most recent is first
		// same with the payments list
		//DBL()->RatePlan->Load();
	
		$this->LoadPage('plan_rates');

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
	 *
	 * @return		void
	 * @method
	 *
	 */
	function Add()
	{
		// Check user authorization and permissions
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie(PERMISSION_ADMIN);
		
		// context menu
		ContextMenu()->Employee_Console();
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		BreadCrumb()->Admin_Console();
		BReadCrumb()->AvailablePlans();
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
					
					// Work out which page called this one
					if (DBO()->CallingPage->Href->Value)
					{
						$strCallingPage = DBO()->CallingPage->Href->Value;
					}
					else
					{
						$strCallingPage = Href()->AdminConsole();
					}
					
					// Set the message appropriate to the action
					if (DBO()->Plan->Archived->Value == ARCHIVE_STATUS_ACTIVE)
					{
						$strSuccessMsg = "The plan has been successfully saved";
					}
					else
					{
						$strSuccessMsg = "The plan has been successfully saved as a draft";
					}
					Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => $strSuccessMsg, "Location" => $strCallingPage));
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
			if (DBO()->RatePlan->Archived->Value != ARCHIVE_STATUS_DRAFT)
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
		 *		V1: Check that a Name and Description have been declared						(implemented via UiAppDocumentation table of database)
		 *		V1: Check that the MinCharge, ChargeCap and UsageCap are valid monetary values	(implemented via UiAppDocumentation table of database)
		 *		V2: Check that CarrierFullService and CarrierPreselection have been declared	(implemented)
		 *		V3: Check that a service type has been declared									(implemented)
		 *		V4: Check that the Name is unique when compared with all other Rate Plans (including all archived and draft plans)	(implemented)
		 *		V5: Check that a non-fleet Rate Group has been declared for each RecordType which is Required						(implemented)
		 */
	
		// V1: Validate the fields
		if (DBO()->RatePlan->IsInvalid())
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: Invalid fields are highlighted";
		}
		
		// V2: CarrierFullService
		if (!DBO()->RatePlan->CarrierFullService->Value)
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A Full Service Carrier must be selected";
		}
		// V2: CarrierPreselection
		if (!DBO()->RatePlan->CarrierPreselection->Value)
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A Carrier Preselection must be selected";
		}
		
		// V3: ServiceType
		if (!DBO()->RatePlan->ServiceType->Value)
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A service type must be selected";
		}
		
		// V4: Make sure the name of the rate plan isn't currently in use
		if (DBO()->RatePlan->Id->Value == 0)
		{
			// The RatePlan name should not be in the database
			$strWhere = "Name=<Name>";
		}
		else
		{
			// We are working with an already saved draft.  Check that the New name is not used by any other RatePlan
			$strWhere = "Name=<Name> AND Id != ". DBO()->RatePlan->Id->Value;
		}
		$selRatePlanName = new StatementSelect("RatePlan", "Id", $strWhere);
		if ($selRatePlanName->Execute(Array("Name" => DBO()->RatePlan->Name->Value)) > 0)
		{
			// The Name is already being used by another rate plan
			DBO()->RatePlan->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: This name is already used by another Plan<br />Please choose a unique name";
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
					// add the rategroup to the list of rate groups
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
				return "ERROR: strObject = '$strObject' intRecCount=$intRecCount objectsChecked=$strObjectsChecked A new record type has been associated with this service type, since you chose the service type of the plan";
			}
			else
			{
				// A RateGroup associated with the RecordType, was not specified and not required
				continue;
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
	
		// S1: Setup the remaing fields required of a RatePlan record
		DBO()->RatePlan->MinMonthly	= ltrim(DBO()->RatePlan->MinMonthly->Value, "$");
		DBO()->RatePlan->ChargeCap	= ltrim(DBO()->RatePlan->ChargeCap->Value, "$");
		DBO()->RatePlan->UsageCap	= ltrim(DBO()->RatePlan->UsageCap->Value, "$");
		
		if (SubmittedForm('AddPlan', 'Save as Draft'))
		{
			// Flag the plan as being a draft
			DBO()->RatePlan->Archived = ARCHIVE_STATUS_DRAFT;
		}
		else
		{
			// The plan is not being saved as a draft
			DBO()->RatePlan->Archived = ARCHIVE_STATUS_ACTIVE;
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
			$arrUpdate		= Array("Archived" => ARCHIVE_STATUS_ACTIVE);
			$updRateGroups 	= new StatementUpdate("RateGroup", "Archived = ". ARCHIVE_STATUS_DRAFT ." AND Id IN ($strRateGroups)", $arrUpdate);
			$updRates 		= new StatementUpdate("Rate", "Archived = ". ARCHIVE_STATUS_DRAFT ." AND Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup IN ($strRateGroups))", $arrUpdate);
			
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
		$strWhere = "ServiceType = <ServiceType> AND Archived != " + ARCHIVE_STATUS_ARCHIVED;
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
		if (DBL()->RecordType->RecordCount() > 0)
		{
			DBL()->RecordType->rewind();
			$dboFirstRecordType = DBL()->RecordType->current();
			$strElement = "RateGroup" . $dboFirstRecordType->Id->Value . ".RateGroupId";
			Ajax()->AddCommand("SetFocus", $strElement);
		}
		return TRUE;
	}
	
	
}
