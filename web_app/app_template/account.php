<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// account
//----------------------------------------------------------------------------//
/**
 * account
 *
 * contains all ApplicationTemplate extended classes relating to account functionality
 *
 * contains all ApplicationTemplate extended classes relating to account functionality
 *
 * @file		account.php
 * @language	PHP
 * @package		framework
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// AppTemplateAccount
//----------------------------------------------------------------------------//
/**
 * AppTemplateAccount
 *
 * The AppTemplateAccount class
 *
 * The AppTemplateAccount class.  This incorporates all logic for all pages
 * relating to accounts
 *
 *
 * @package	web_app
 * @class	AppTemplateAccount
 * @extends	ApplicationTemplate
 */
class AppTemplateAccount extends ApplicationTemplate
{

	//------------------------------------------------------------------------//
	// ViewUnbilledCharges
	//------------------------------------------------------------------------//
	/**
	 * ViewUnbilledCharges()
	 *
	 * Performs the logic for the Account_ViewUnbilledCharges.php webpage
	 * 
	 * Performs the logic for the Account_ViewUnbilledCharges.php webpage
	 *
	 * @return		void
	 * @method		ViewUnbilledCharges
	 *
	 */
	function ViewUnbilledCharges()
	{
		// Check user authorization
		AuthenticatedUser()->CheckClientAuth();

		// Context menu
		//ContextMenu()->Admin_Console();
		//ContextMenu()->Logout();
		
		// Breadcrumb menu
				
		// Load the account
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id:". DBO()->Account->Id->value ."could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Check that the user can view this account
		$bolUserCanViewAccount = FALSE;
		if (AuthenticatedUser()->_arrUser['CustomerContact'])
		{
			// The user can only view the account, if it belongs to the account group that they belong to
			if (AuthenticatedUser()->_arrUser['AccountGroup'] == DBO()->Account->AccountGroup->Value)
			{
				$bolUserCanViewAccount = TRUE;
			}
		}
		elseif (AuthenticatedUser()->_arrUser['Account'] == DBO()->Account->Id->Value)
		{
			// The user can only view the account, if it is their primary account
			$bolUserCanViewAccount = TRUE;
		}
		
		if (!$bolUserCanViewAccount)
		{
			// The user does not have permission to view the requested account
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value ." as it is not part of their Account Group";
			$this->LoadPage('Error');
			return FALSE;
		}
		
		// Calculate the unbilled total for the account
		// The unbilled total = TotalUnbilledAdjustments + sum of unbilled charges for each service
		
		// Calculate the Account's total unbilled adjustments (inc GST)
		DBO()->Account->TotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Calculate the Account's total unbilled CDRs (For every service) (inc GST)
		//DBO()->Account->TotalUnbilledCDRs = $this->Framework->GetUnbilledCDRs(DBO()->Account->Id->Value);
		// HACK!
		DBO()->Account->TotalUnbilledCDRs = 1000.00;
		// HACK!
		
		/*
		// Retrieve the list of services for the account
		DBL()->Service->Account = DBO()->Account->Id->Value;
		DBL()->Service->Load();
		
		// prepare query for finding the current plan for a given service
		$selCurrentPlan = new StatementSelect('ServiceRatePlan', 'RatePlan', 
												'Service = <Service> AND (Now() BETWEEN StartDatetime AND EndDatetime)', 
												'CreatedOn DESC',	1);
		
		// For each service of the Account, calculate the unbilled charges
		foreach (DBL()->Service as $dboService)
		{
			// Find the rateplan for the service
			$selCurrentPlan->Execute(Array("Service" => $dboService->Id->Value));
			
			// this can return 0 or 1 records
			if ($selCurrentPlan->Count() == 1)
			{
				//There is a current plan for this record
				$arrCurrentPlan = $selCurrentPlan->Fetch();
				$dboService->CurrentPlan = $arrCurrentPlan['RatePlan'];
			}
			else
			{
				//There is no rateplan for this service
				
			}
			
			
		}
		*/
		

		// Retrieve all unbilled adjustments for the account
		$strWhere  = "(Account = ". DBO()->Account->Id->Value .")";
		$strWhere .= " AND ((Status = ". CHARGE_WAITING .")";
		$strWhere .= " OR (Status = ". CHARGE_APPROVED ."))";
		DBL()->Charge->Where->SetString($strWhere);
		DBL()->Charge->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Charge->Load();
		
		// Retrieve all Services for the account
		DBL()->Service->Account = DBO()->Account->Id->Value;
		DBL()->Service->Load();
		
		// prepare query for finding the current plan and plan name for a given service
		// FIX IT! I've tested this query and it works, but I don't think the StatementSelect is working
		/*
		SELECT srpT.Service, srpT.RatePlan, rpT.Name, rpT.Description
		FROM ServiceRatePlan AS srpT inner join RatePlan AS rpT ON srpT.RatePlan = rpT.Id
		WHERE (srpT.Service = <Service>) AND (Now() BETWEEN srpT.StartDatetime AND srpT.EndDatetime)
		ORDER BY srpT.CreatedOn DESC
		LIMIT 0, 1
		*/
		$selCurrentPlan = new StatementSelect('ServiceRatePlan AS srpT INNER JOIN RatePlan AS rpT ON srpT.RatePlan = rpT.Id', 
												'srpT.Service, srpT.RatePlan, rpT.Name, rpT.Description', 
												'(srpT.Service = <Service>) AND (Now() BETWEEN srpT.StartDatetime AND srpT.EndDatetime)', 
												'srpT.CreatedOn DESC',	1);
		
		// For each service, find the current RatePlan
		foreach (DBL()->Service as $dboService)
		{
			// Find the rateplan for the service
			$selCurrentPlan->Execute(Array("Service" => $dboService->Id->Value));
			
			// this can return 0 or 1 records
			if ($selCurrentPlan->Count() == 1)
			{
				//There is a current plan for this record
				$arrCurrentPlan = $selCurrentPlan->Fetch();
				$dboService->CurrentPlan = $arrCurrentPlan['RatePlan'];
			}
			else
			{
				//There is no rateplan for this service
			}
		}
		


		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('account_view_unbilled_charges');
		
		return TRUE;
	}
	
	//----- DO NOT REMOVE -----//
	
}
