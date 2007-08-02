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
	 * Performs the logic for the account_view_unbilled_charges.php webpage
	 * 
	 * Performs the logic for the account_view_unbilled_charges.php webpage
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
		
				
		// Load the account
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
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
		
		// Calculate the Account's total unbilled adjustments
		$fltTotalUnbilledAdjustments = $this->Framework->GetUnbilledCharges(DBO()->Account->Id->Value);
		
		// Calculate the total unbilled CDRs for the account
		$fltTotalUnbilledCDRs = AddGST(UnbilledAccountCDRTotal(DBO()->Account->Id->Value));
		
		// Calculate the current unbilled total for the account
		DBO()->Account->CurrentUnbilledTotal = $fltTotalUnbilledAdjustments + $fltTotalUnbilledCDRs;
		
		

		// Retrieve all unbilled adjustments for the account
		$strWhere  = "(Account = ". DBO()->Account->Id->Value .")";
		$strWhere .= " AND (Status = ". CHARGE_APPROVED .")";
		DBL()->Charge->Where->SetString($strWhere);
		DBL()->Charge->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Charge->Load();
		
		// Retrieve all Services for the account
		DBL()->Service->Account = DBO()->Account->Id->Value;
		DBL()->Service->Load();
		
		// Find the current Plan for each service and the current unbilled charges and CDRs for each service
		// This is currently handled in HtmlTemplateAccountServiceList->Render() 
		// I wanted to use the following block of code, but you can't add anything to a DBList within a foreach loop because
		// with the current implementation of the iterator interface for DBListBase, everything is returned as copies instead of references.
		/*
		$selCurrentPlan = new StatementSelect('ServiceRatePlan AS srpT INNER JOIN RatePlan AS rpT ON srpT.RatePlan = rpT.Id', 
										'srpT.Service, srpT.RatePlan, rpT.Name, rpT.Description', 
										'(srpT.Service = <Service>) AND (Now() BETWEEN srpT.StartDatetime AND srpT.EndDatetime)', 
										'srpT.StartDatetime DESC',	1);
		
		// For each service, find the current rate plan and the name of the current rate plan AND the total unbilled charges and CDRs
		foreach (DBL()->Service as &$dboService)
		{
			// Find the rateplan for the service
			$selCurrentPlan->Execute(Array("Service" => $dboService->Id->Value));
			
			// this can return 0 or 1 records
			if ($selCurrentPlan->Count() == 1)
			{
				//There is a current plan for this record
				$arrCurrentPlan = $selCurrentPlan->Fetch();
				$dboService->CurrentPlan 		= $arrCurrentPlan['RatePlan'];
				$dboService->CurrentPlanName 	= $arrCurrentPlan['Name'];
				$dboService->TotalUnbilled 		= AddGST(UnbilledServiceCDRTotal($dboService->Id->Value) + UnbilledServiceChargeTotal($dboService->Id->Value));
			}
			else
			{
				//There is no rateplan for this service
				$dboService->CurrentPlan 		= NULL;
				$dboService->CurrentPlanName 	= NULL;
				$dboService->TotalUnbilled 		= NULL;
			}
		}
		*/

		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);

		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('account_view_unbilled_charges');
		
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// ListInvoicesAndPayments
	//------------------------------------------------------------------------//
	/**
	 * ListInvoicesAndPayments()
	 *
	 * Performs the logic for the list_invoices_and_payments.php webpage
	 * 
	 * Performs the logic for the list_invoices_and_payments.php webpage
	 *
	 * @return		void
	 * @method		ListInvoicesAndPayments
	 *
	 */
	function ListInvoicesAndPayments()
	{
		// Check user authorization
		AuthenticatedUser()->CheckClientAuth();

		// Context menu
		//ContextMenu()->Admin_Console();
		//ContextMenu()->Logout();
		
				
		// Load the account
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
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
		
		// Retrieve all Invoices and all Payments for the account
		DBL()->Invoice->Account = DBO()->Account->Id->Value;
		DBL()->Invoice->OrderBy("CreatedOn DESC");
		DBL()->Invoice->Load();
		DBL()->Payment->Account = DBO()->Account->Id->Value;
		DBL()->Payment->OrderBy("PaidOn DESC");
		DBL()->Payment->Load();
		
		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
		BreadCrumb()->ViewUnbilledChargesForAccount(DBO()->Account->Id->Value);

		// All required data has been retrieved from the database so now load the page template
		$this->LoadPage('list_invoices_and_payments');
		
		return TRUE;
	
	}
	
	//------------------------------------------------------------------------//
	// DownloadInvoicePDF
	//------------------------------------------------------------------------//
	/**
	 * DownloadInvoicePDF()
	 *
	 * Performs the logic for when a client wants to download a pdf
	 * 
	 * Performs the logic for when a client wants to download a pdf
	 *
	 * @return		void
	 * @method		DownloadInvoicePDF
	 *
	 */
	function DownloadInvoicePDF()
	{
		// Check user authorization
		AuthenticatedUser()->CheckClientAuth();

		// Context menu
		//ContextMenu()->Admin_Console();
		//ContextMenu()->Logout();
		
				
		// Load the account
		if (!DBO()->Account->Load())
		{
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
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
			// The user does not have permission to view any information about the requested account
			Ajax()->AddCommand("AlertAndRelocate", Array("Alert" => "ERROR: You do not have permission to view the details of account# ". DBO()->Account->Id->Value, "Location" => "vixen.php/Console/Console/"));
			return TRUE;
		}
		
		// check if a pdf exists for the invoice
		if (InvoicePdfExists(DBO()->Account->Id->Value, DBO()->Invoice->Year->Value, DBO()->Invoice->Month->Value))
		{
			// Try to pull the Invoice PDF
			$strInvoice = GetPDF(DBO()->Account->Id->Value, DBO()->Invoice->Year->Value, DBO()->Invoice->Month->Value);
			header ("Content-Type: application/pdf");
			echo $strInvoice;
			exit;
		}
		else
		{
			// The invoice could not be found
			$intUnixTime = mktime(0, 0, 0, DBO()->Invoice->Month->Value, 0, DBO()->Invoice->Year->Value);
			$strDate = date("F, Y", $intUnixTime);

			DBO()->Error->Message = "ERROR: Could not find the pdf relating to the $strDate invoice for Account# ". DBO()->Account->Id->Value;
			$this->LoadPage('Error');
			return FALSE;
		}

		// Breadcrumb menu
		// I don't know if we are actually displaying a page here

		// We shouldn't need to load a page
		//$this->LoadPage('list_invoices_and_payments');
		
		return TRUE;
	}
	
	
	//----- DO NOT REMOVE -----//
	
}
