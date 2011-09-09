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
			DBO()->Error->Message = "The account with account id: ". DBO()->Account->Id->Value ." could not be found";
			$this->LoadPage('error');
			return FALSE;
		}
		
		// Check that the user can view this account
		$bolUserCanViewAccount = FALSE;
		if (AuthenticatedUser()->_arrUser['account_id'] == DBO()->Account->Id->Value)
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
		
		// Retrieve all unbilled charges for the account
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
		if (AuthenticatedUser()->_arrUser['account_id'] == DBO()->Account->Id->Value)
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
		
		try
		{
			DBO()->Payments 	= $this->_loadPayments();
			DBO()->Adjustments 	= $this->_loadAdjustments();
		}
		catch (Exception $oEx)
		{
			BreadCrumb()->Console();
			BreadCrumb()->SetCurrentPage("Error");
			DBO()->Error->Message = "ERROR: We were unable to display the Invoices and Payments for your Account (# ". DBO()->Account->Id->Value."). ".$oEx->getMessage();
			$this->LoadPage('Error');
			return false;
		}
		
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
		if (AuthenticatedUser()->_arrUser['account_id'] == DBO()->Account->Id->Value)
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
	
	private function _loadPayments()
	{
		// Get all payment records for the account
		$oQuery 	= new Query();
		$mResult	= $oQuery->Execute("SELECT		p.id AS payment_id, 
										        	p.paid_date AS paid_date, 
										       		pt.name AS payment_type_name, 
										        	(p.amount * pn.value_multiplier) AS amount,
													p.transaction_reference AS transaction_reference,
  													prr.payment_reversal_type_id AS payment_reversal_type_id,
					        						prr_reversed.payment_reversal_type_id AS reversed_by_payment_reversal_type_id,
													prr.name AS payment_reversal_reason_name
										FROM		payment p
										JOIN    	payment_nature pn ON (pn.id = p.payment_nature_id)
										LEFT JOIN	payment_type pt ON (pt.id = p.payment_type_id)
										LEFT JOIN   payment_reversal_reason prr ON (prr.id = p.payment_reversal_reason_id)
										LEFT JOIN	payment p_reversed ON (p_reversed.reversed_payment_id = p.id)
										LEFT JOIN   payment_reversal_reason prr_reversed ON (prr_reversed.id = p_reversed.payment_reversal_reason_id)
										WHERE		p.account_id = ".DBO()->Account->Id->Value.";");
		if ($mResult === false)
		{
			throw new Exception("Failed to get payments. ".$oQuery->Error());
		}
		
		$aPayments = array();
		while ($aRow = $mResult->fetch_assoc())
		{
			$aPayments[] = $aRow;
		}
		return $aPayments;
	}
	
	private function _loadAdjustments()
	{
		// Get all adjustment records for the account (with a type that has an invoice visibility of visible)
		$oQuery 	= new Query();
		$mResult	= $oQuery->Execute("SELECT 		adj.id AS adjustment_id,
											        (adj.amount * tn.value_multiplier * adjn.value_multiplier) AS amount,
											        adjt.description AS adjustment_type_name,
										        	adj_reversed.id as reversed_by_adjustment_id,
										        	adj.reversed_adjustment_id as reversed_adjustment_id,
													adj.effective_date AS effective_date,
													IF(adjustment_type_invoice_visibility_id = ".ADJUSTMENT_TYPE_INVOICE_VISIBILITY_VISIBLE.", 1, 0) AS visible_on_invoice
										FROM		adjustment adj
										JOIN		adjustment_type adjt ON (adjt.id = adj.adjustment_type_id)
										JOIN		transaction_nature tn ON (tn.id = adjt.transaction_nature_id)
										JOIN  		adjustment_review_outcome aro ON (aro.id = adj.adjustment_review_outcome_id)
										JOIN  		adjustment_review_outcome_type arot ON (arot.id = aro.adjustment_review_outcome_type_id AND arot.system_name = 'APPROVED')
										JOIN		adjustment_nature adjn ON (adjn.id = adj.adjustment_nature_id)
										JOIN		adjustment_status adjs ON (adjs.id = adj.adjustment_status_id AND adjs.system_name = 'APPROVED')
										LEFT JOIN	adjustment adj_reversed ON (adj_reversed.reversed_adjustment_id = adj.id)
										WHERE		adj.account_id = ".DBO()->Account->Id->Value.";");
		if ($mResult === false)
		{
			throw new Exception("Failed to get adjustments. ".$oQuery->Error());
		}
		
		$aAdjustments = array();
		while ($aRow = $mResult->fetch_assoc())
		{
			$aAdjustments[] = $aRow;
		}
		
		return $aAdjustments;
	}
}
