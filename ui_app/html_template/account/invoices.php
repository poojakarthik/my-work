<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_invoices.php
//----------------------------------------------------------------------------//
/**
 * account_invoices
 *
 * HTML Template for the Account Invoices HTML object
 *
 * HTML Template for the Account Invoices HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all invoices relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		account_invoices.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAccountInvoices
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountInvoices
 *
 * HTML Template class for the Account Invoices HTML object
 *
 * HTML Template class for the Account Invoices HTML object
 * Lists all invoices related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountInvoices
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountInvoices extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
		$this->LoadJavascript("tooltip");
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{	
		// Render each of the account invoices

		
		Table()->InvoiceTable->SetHeader("Date", "Invoice No", "Amount(total)", "Applied Amount(balance)", "Amount Owing(totalowing)", "Invoice Sent", "View PDF", "View Invoice Details");
		//Table()->PaymentTable->SetWidth("20%", "30%", "50%");
		//Table()->PaymentTable->SetAlignment("Left", FALSE, "Right");
		
		foreach (DBL()->Invoice as $dboInvoice)
		{
			$arrInvoices = Array();
			
			// Add this row to Payment table
			Table()->InvoiceTable->AddRow(  $dboInvoice->DueOn->Value,
											$dboInvoice->Id->Value, 
											$dboInvoice->Total->Value, 
											$dboInvoice->Balance->Value, 
											$dboInvoice->TotalOwing->Value, 
											"Yes", 
											"PDF LINK",
											"DETAIL LINK");
			Table()->InvoiceTable->SetDetail("INSERT HTML CODE HERE");
			//Table()->InvoiceTable->SetToolTip("[INSERT HTML CODE HERE FOR THE TOOL TIP FOR ROW]");
			Table()->InvoiceTable->AddIndex("InvoiceRun", $dboInvoice->InvoiceRun->Value);
		}		
		
		Table()->InvoiceTable->LinkTable("PaymentTable", "InvoiceRun");
		Table()->InvoiceTable->RowHighlighting = TRUE;
		
		Table()->InvoiceTable->Render();
		
	}
}

?>
