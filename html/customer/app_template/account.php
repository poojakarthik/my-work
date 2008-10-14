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
				
		// Load the account
		if (!DBO()->Account->Load())
		{
			// Could not load the account
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
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
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value ." as it is not part of their Account Group";
			$this->LoadPage('Error');
			return FALSE;
		}
		
		// Retrieve all unbilled adjustments for the account
		$strWhere  = "(Account = ". DBO()->Account->Id->Value .")";
		$strWhere .= " AND (Status = ". CHARGE_APPROVED .")";
		DBL()->Charge->Where->SetString($strWhere);
		DBL()->Charge->OrderBy("CreatedOn DESC, Id DESC");
		DBL()->Charge->Load();
		
		// Retrieve all Services for the account
		//TODO! Should we only retrieve the Active, Closed and Suspended services?
		DBL()->Service->Account = DBO()->Account->Id->Value;
		DBL()->Service->OrderBy("ServiceType, FNN");
		DBL()->Service->Load();

		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
		if (DBO()->Account->BusinessName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Account Charges - " . substr(DBO()->Account->BusinessName->Value, 0, 60));
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Account Charges - " . substr(DBO()->Account->TradingName->Value, 0, 60));
		}
		else
		{
			// Don't display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Account Charges");
		}

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

		// Load the account
		if (!DBO()->Account->Load())
		{
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
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
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value;
			$this->LoadPage('Error');
			return FALSE;
		}
		
		$intAccountId = DBO()->Account->Id->Value;
		
		// Retrieve all Invoices and all Payments for the account
		$arrInvoiceColumns = array(	"Id"				=> "I.Id",
									"AccountGroup"		=> "I.AccountGroup",
									"Account"			=> "I.Account",
									"CreatedOn"			=> "I.CreatedOn",
									"DueOn"				=> "I.DueOn",
									"SettledOn"			=> "I.SettledOn",
									"Credits"			=> "I.Credits",
									"Debits"			=> "I.Debits",
									"Total"				=> "I.Total",
									"Tax"				=> "I.Tax",
									"TotalOwing"		=> "I.TotalOwing",
									"Balance"			=> "I.Balance",
									"Disputed"			=> "I.Disputed",
									"AccountBalance"	=> "I.AccountBalance",
									"DeliveryMethod"	=> "I.DeliveryMethod",
									"Status"			=> "I.Status",
									"invoice_run_id"	=> "I.invoice_run_id"
									);
		
		$strInvoiceWhere = "I.Account = $intAccountId AND I.Status != ". INVOICE_TEMP ." AND ir.invoice_run_status_id = ". INVOICE_RUN_STATUS_COMMITTED ." AND ir.invoice_run_type_id = ". INVOICE_RUN_TYPE_LIVE;
		$strInvoiceTables = "Invoice AS I INNER JOIN InvoiceRun AS ir ON I.invoice_run_id = ir.Id";
		DBL()->Invoice->SetTable($strInvoiceTables);
		DBL()->Invoice->SetColumns($arrInvoiceColumns);
		DBL()->Invoice->Where->SetString($strInvoiceWhere);
		DBL()->Invoice->OrderBy("I.CreatedOn DESC");
		DBL()->Invoice->Load();
		
		$strWhere = "(Account = <Account> OR (AccountGroup = <AccountGroup> AND Account IS NULL)) AND (Status = <PaymentPaying> OR Status = <PaymentFinished> OR Status = <PaymentWaiting>)";
		DBL()->Payment->Account				= DBO()->Account->Id->Value;
		DBL()->Payment->AccountGroup		= DBO()->Account->AccountGroup->Value;
		DBL()->Payment->PaymentPaying		= PAYMENT_PAYING;
		DBL()->Payment->PaymentFinished		= PAYMENT_FINISHED;
		DBL()->Payment->PaymentWaiting		= PAYMENT_WAITING;
		DBL()->Payment->Where->SetString($strWhere);
		DBL()->Payment->OrderBy("PaidOn DESC");
		DBL()->Payment->Load();
		
		// Breadcrumb menu
		BreadCrumb()->LoadAccountInConsole(DBO()->Account->Id->Value);
		if (DBO()->Account->BusinessName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Invoices and Payments - " . substr(DBO()->Account->BusinessName->Value, 0, 60));
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			// Display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Invoices and Payments - " . substr(DBO()->Account->TradingName->Value, 0, 60));
		}
		else
		{
			// Don't display the business name in the bread crumb menu
			BreadCrumb()->SetCurrentPage("Invoices and Payments");
		}

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
		AuthenticatedUser()->CheckClientAuth(TRUE);

		// Load the account
		if (!DBO()->Account->Load())
		{
			// The account could not be loaded
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->value ." could not be found";
			$this->LoadPage('error');
			return TRUE;
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
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The user does not have permission to view account# ". DBO()->Account->Id->Value;
			$this->LoadPage('Error');
			return TRUE;
		}
		
		// check if a pdf exists for the invoice
		if (InvoicePDFExists(DBO()->Account->Id->Value, DBO()->Invoice->Year->Value, DBO()->Invoice->Month->Value, DBO()->Invoice->Id->Value, intval(DBO()->Invoice->invoice_run_id->Value)))
		{
			// Try to pull the Invoice PDF
			$strInvoice = GetPDFContent(DBO()->Account->Id->Value, DBO()->Invoice->Year->Value, DBO()->Invoice->Month->Value, DBO()->Invoice->Id->Value, DBO()->Invoice->invoice_run_id->Value);
			$strInvoiceFilename = GetPdfFilename(DBO()->Account->Id->Value, DBO()->Invoice->Year->Value, DBO()->Invoice->Month->Value, DBO()->Invoice->Id->Value, DBO()->Invoice->invoice_run_id->Value);
			header("Content-Type: application/pdf");
			header("Content-Disposition: attachment; filename=\"$strInvoiceFilename\"");
			echo $strInvoice;
			die;
		}
		else
		{
			// The invoice could not be found
			$intUnixTime = mktime(0, 0, 0, DBO()->Invoice->Month->Value, 0, DBO()->Invoice->Year->Value);
			$strDate = date("F, Y", $intUnixTime);

			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: The pdf of the $strDate invoice could not be found";
			$this->LoadPage('Error');
			return TRUE;
		}
		
		return TRUE;
	}
	
	//----- DO NOT REMOVE -----//
	
}
