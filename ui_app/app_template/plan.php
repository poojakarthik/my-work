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
 * @version		
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
	function View()
	{
		if (SubmittedForm("RatePlanFilter"))
		{
			if (DBO()->RatePlan->Name->Value != NULL)
			{
				DBL()->RatePlan->Name = DBO()->RatePlan->Name->Value;
			}
			if (DBO()->RatePlan->ServiceType->Value != "All")
			{
				DBL()->RatePlan->ServiceType = DBO()->RatePlan->ServiceType->Value;
			}
		}
		
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
		
		// Handle form submittion
		if (SubmittedForm('AddPlan', 'Commit'))
		{
			TransactionStart();
			
			$mixResult = $this->_AddPlan();
			if ($mixResult !== TRUE && $mixResult !== FALSE)
			{
				// Adding the plan failed, and an error message has been returned
				TransactionRollback();
				Ajax()->AddCommand("Alert", $mixResult);
				return TRUE;
			}
			elseif ($mixResult === FALSE)
			{
				// Adding the plan failed, and no error message was specified, so it is assumed approraite actions have already taken place
				TransactionRollback();
				return TRUE;
			}
			else
			{
				// Adding the plan was successfull
				TransactionCommit();
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "The plan has been successfully added", "Location" => Href()->AdminConsole()));
				return TRUE;
			}
		}
		
		
		// context menu
		ContextMenu()->Admin_Console();
		ContextMenu()->Logout();
		
		// breadcrumb menu
		BreadCrumb()->Admin_Console();
		BreadCrumb()->SetCurrentPage("Add Rate Plan");
	
		$this->LoadPage('rate_plan_add');

		return TRUE;
	
	}
	
	// This will handle form validation and commiting the plan to the database
	private function _AddPlan()
	{
		// Validate the fields
		if (DBO()->RatePlan->IsInvalid())
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: Invalid fields are highlighted";
		}
		if (!DBO()->RatePlan->ServiceType->Value)
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A service type must be selected";
		}
		
		// Make sure the name of the rate plan isn't currently in use
		DBO()->ExistingRatePlan->Where->Name = DBO()->RatePlan->Name->Value;
		DBO()->ExistingRatePlan->SetTable("RatePlan");
		if (DBO()->ExistingRatePlan->Load())
		{
			// A rate plan with the same name already exists
			DBO()->RatePlan->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A Rate Plan named '". DBO()->RatePlan->Name->Value ."' already exists.<br>Please choose a unique name";
		}
		
		// Check that a rate group has been defined for each RecordType that has been marked as required
		DBL()->RecordType->ServiceType = DBO()->RatePlan->ServiceType->Value;
		DBL()->RecordType->Load();
		
		$arrRateGroups = Array();

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
					$arrRateGroups[] = $intRateGroup;
				}
				
				if ($intFleetRateGroup > 0)
				{
					// Add the fleet rate group to the list of rate groups
					$arrRateGroups[] = $intFleetRateGroup;
				}
			}
			elseif ($dboRecordType->Required->Value == TRUE)
			{
				// The RatePlan requires a RateGroup of this RecordType, but one has not been declared
				//NOTE! This is only run if the RecordType was not associated with the ServiceType before loading the RateGroupDiv contents
				$this->GetPlanDeclareRateGroupsHtmlTemplate();
				return "ERROR: strObject = '$strObject' intRecCount=$intRecCount objectsChecked=$strObjectsChecked A new record type has been associated with this service type, since you chose the service type of the plan";
			}
			else
			{
				// A RateGroup associated with the RecordType, was not specified and not required
				continue;
			}
		}
		
		// All validation has completed and the fields are valid
		// Setup the remaing fields required of a RatePlan record
		DBO()->RatePlan->MinMonthly	= ltrim(DBO()->RatePlan->MinMonthly->Value, "$");
		DBO()->RatePlan->ChargeCap	= ltrim(DBO()->RatePlan->ChargeCap->Value, "$");
		DBO()->RatePlan->UsageCap	= ltrim(DBO()->RatePlan->UsageCap->Value, "$");
		DBO()->RatePlan->Archived	= 0;
		
		// Save the plan to the database
		if (!DBO()->RatePlan->Save())
		{
			// Saving failed
			return "ERROR: Saving the RatePlan to the RatePlan database table failed, unexpectedly";
		}
		
		// Save each of the RateGroups associated with the RatePlan to the RatePlanRateGroup table
		foreach ($arrRateGroups as $intRateGroup)
		{
			DBO()->RatePlanRateGroup->Id = 0;
			DBO()->RatePlanRateGroup->RatePlan = DBO()->RatePlan->Id->Value;
			DBO()->RatePlanRateGroup->RateGroup = $intRateGroup;
			
			if (!DBO()->RatePlanRateGroup->Save())
			{
				// Saving failed
				return "ERROR: Saving one of the RateGroup - RatePlan associations failed, unexpectedly<br>The RatePlan has not been saved";
			}
		}
		
		// Everything has been saved
		return TRUE;
	}
	
	// This retrieves all data required of the HtmlTemplate, PlanDeclareRateGroups, and renders the template using an ajax command
	function GetPlanDeclareRateGroupsHtmlTemplate()
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
		$strWhere = "ServiceType = <ServiceType> AND Archived != 1";
		DBL()->RateGroup->Where->Set($strWhere, Array('ServiceType' => DBO()->RatePlan->ServiceType->Value));
		DBL()->RateGroup->OrderBy("Name");
		DBL()->RateGroup->Load();
		
		Ajax()->RenderHtmlTemplate("PlanAdd", HTML_CONTEXT_RATE_GROUPS, "RateGroupsDiv");
		return TRUE;
	}
	
	
}
