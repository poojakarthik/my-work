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
		if (SubmittedForm('RatePlan', 'Commit'))
		{
			TransactionStart();
			
			$mixResult = $this->_AddPlan();
			if ($mixResult !== TRUE && $mixResult !== FALSE)
			{
				// Adding the plan failed, and an error message has been returned
				TransactionRollback();
				Ajax()->AddCommand("Alert", $mixResult);  //THIS ISN'T BEING DISPLAYED AND IT LOOKS LIKE THE PAGE IS RELOADING
				return TRUE;
			}
			elseif ($mixResult === TRUE)
			{
				// Adding the plan was successfull
				TransactionCommit();
				Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "The plan has been successfully added", "Location" => Href()->AdminConsole()));
				return TRUE;
			}
			else
			{
				// Adding the plan failed, and no error message was specified
				// I would hope this condition never takes place
				TransactionRollback();
				Ajax()->AddCommand("Alert", "ERROR: Adding the plan failed, unexpectedly");
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
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DEFAULT, "PlanDiv");
			return "ERROR: Invalid fields are highlighted";
		}
		
		// Check that a rate group has been defined for each RecordType that has been marked as required
		
		//Save the plan to the database
		return TRUE;
	}
	
	// This retrieves all data required of the HtmlTemplate, PlanDeclareRateGroups, and renders the template using an ajax command
	function GetPlanDeclareRateGroupsHtmlTemplate()
	{
		if (!DBO()->RatePlan->ServiceType->Value)
		{
			// A service type was not actually chosen 
			Ajax()->RenderHtmlTemplate("PlanDeclareRateGroups", HTML_CONTEXT_NO_DETAIL, "RateGroupsDiv");
			return TRUE;
		}
	
		// Find all RecordTypes required of this ServiceType
		DBL()->RecordType->ServiceType = DBO()->RatePlan->ServiceType->Value;
		DBL()->RecordType->OrderBy("Name");
		DBL()->RecordType->Load();
		
		$intNumTypes = DBL()->RecordType->RecordCount();
		Ajax()->RenderHtmlTemplate("PlanDeclareRateGroups", HTML_CONTEXT_DEFAULT, "RateGroupsDiv");
		return TRUE;
	}
	
	
}
