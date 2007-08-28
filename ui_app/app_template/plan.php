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
					if (DBO()->Plan->Archived->Value == 0)
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
		
		// Check if we are to display an existing RatePlan or if we are adding a new one
		if (DBO()->RatePlan->Id->Value)
		{
			// We want to display an existing RatePlan
			if (!DBO()->RatePlan->Load())
			{
				// Could not load the RatePlan
				DBO()->Error->Message = "The RatePlan with id:". DBO()->RatePlan->Id->value ." could not be found";
				$this->LoadPage('error');
				return FALSE;
			}
		}
		else
		{
			// We want to add a new RatePlan
			DBO()->RatePlan->Id = 0;
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
	 * @param		bool	$bolSaveAsDraft		TRUE if the plan is to be saved as a draft, FALSE if the plan is to be committed to the database
	 *
	 * @return		mix							returns TRUE if the new RatePlan saved successfully, else it returns
	 *											a specific error message detailing why the RatePlan could not be saved
	 * @method
	 *
	 */
	private function _ValidatePlan($bolSaveAsDraft=FALSE)
	{
		// Validate the fields
		if (DBO()->RatePlan->IsInvalid())
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: Invalid fields are highlighted";
		}
		if (!DBO()->RatePlan->CarrierFullService->Value)
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A Full Service Carrier must be selected";
		}
		if (!DBO()->RatePlan->CarrierPreselection->Value)
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A Carrier Preselection must be selected";
		}
		if (!DBO()->RatePlan->ServiceType->Value)
		{
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A service type must be selected";
		}
		
		
		// Make sure the name of the rate plan isn't currently in use
		/* I don't think the name of the rate plan has to be unique
		DBO()->ExistingRatePlan->Where->Name = DBO()->RatePlan->Name->Value;
		DBO()->ExistingRatePlan->SetTable("RatePlan");
		if (DBO()->ExistingRatePlan->Load())
		{
			// A rate plan with the same name already exists
			DBO()->RatePlan->Name->SetToInvalid();
			Ajax()->RenderHtmlTemplate('PlanAdd', HTML_CONTEXT_DETAILS, "RatePlanDetailsId");
			return "ERROR: A Rate Plan named '". DBO()->RatePlan->Name->Value ."' already exists.<br>Please choose a unique name";
		}
		*/
		
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
		
		// Save the list of Rate Groups to associate with the Rate Plan
		// Retrieve a list of all the Rate Groups associated with the Rate Plan
		$strWhere = "Id IN (";
		foreach ($arrRateGroups as $intRateGroup)
		{
			$strWhere .= "$intRateGroup, ";
		}
		$strWhere = substr($strWhere, 0, -2);
		$strWhere .= ")";
		DBL()->RateGroup->Where->SetString($strWhere);
		DBL()->RateGroup->Load();
		
		
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
	 * @param		bool	$bolSaveAsDraft		TRUE if the plan is to be saved as a draft, FALSE if the plan is to be committed to the database
	 *
	 * @return		mix							returns TRUE if the new RatePlan saved successfully, else it returns
	 *											a specific error message detailing why the RatePlan could not be saved
	 * @method
	 *
	 */
	private function _SavePlan()
	{
		// Setup the remaing fields required of a RatePlan record
		DBO()->RatePlan->MinMonthly	= ltrim(DBO()->RatePlan->MinMonthly->Value, "$");
		DBO()->RatePlan->ChargeCap	= ltrim(DBO()->RatePlan->ChargeCap->Value, "$");
		DBO()->RatePlan->UsageCap	= ltrim(DBO()->RatePlan->UsageCap->Value, "$");
		
		if (SubmittedForm('AddPlan', 'Save as Draft'))
		{
			// Flag the plan as being a draft
			DBO()->RatePlan->Archived = 2;
		}
		else
		{
			// The plan is not being saved as a draft
			DBO()->RatePlan->Archived = 0;
		}
		
		// Save the plan to the database
		if (!DBO()->RatePlan->Save())
		{
			// Saving failed
			return "ERROR: Saving the RatePlan to the RatePlan database table failed, unexpectedly";
		}
		
		// Remove all records from the RatePlanRateGroup table where RatePlan == DBO()->RatePlan->Id->Value
		$delRatePlanRateGroup = new Query();
		$delRatePlanRateGroup->Execute("DELETE FROM RatePlanRateGroup WHERE RatePlan = " . DBO()->RatePlan->Id->Value);

		// Save each of the RateGroups associated with the RatePlan to the RatePlanRateGroup table
		$arrRateGroups = Array();
		DBO()->RatePlanRateGroup->RatePlan = DBO()->RatePlan->Id->Value;
		foreach (DBL()->RateGroup as $dboRateGroup)
		{
			DBO()->RatePlanRateGroup->Id = 0;
			DBO()->RatePlanRateGroup->RateGroup = $dboRateGroup->Id->Value;
			
			if (!DBO()->RatePlanRateGroup->Save())
			{
				// Saving failed
				return "ERROR: Saving one of the RateGroup - RatePlan associations failed, unexpectedly.<br />The RatePlan has not been saved";
			}
			
			// This array is used to commit draft RateGroups and draft Rates used by this RatePlan, but only if the RatePlan is being committed
			$arrRateGroups[] = $dboRateGroup->Id->Value;
		}
		
		// If the RatePlan is being committed then all draft RateGroups used by it must be commited and all draft Rates
		// used by the draft RateGroups must be committed
		if (SubmittedForm('AddPlan', 'Commit'))
		{
			$strRateGroups 	= implode(',', $arrRateGroups);
			$arrUpdate		= Array("Archived" => 0);
			$updRateGroups 	= new StatementUpdate("RateGroup", "Archived = 2 AND Id IN ($strRateGroups)", $arrUpdate);
			$updRates 		= new StatementUpdate("Rate", "Archived = 2 AND Id IN (SELECT Rate FROM RateGroupRate WHERE RateGroup IN ($strRateGroups))", $arrUpdate);
			
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
		$strWhere = "ServiceType = <ServiceType> AND Archived != 1";
		DBL()->RateGroup->Where->Set($strWhere, Array('ServiceType' => DBO()->RatePlan->ServiceType->Value));
		DBL()->RateGroup->OrderBy("Description");
		DBL()->RateGroup->Load();
		
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
