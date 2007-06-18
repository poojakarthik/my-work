<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_payments.php
//----------------------------------------------------------------------------//
/**
 * account_payments
 *
 * HTML Template for the Account Payments HTML object
 *
 * HTML Template for the Account Payments HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all payments relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		account_payments.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAccountPayments
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountPayments
 *
 * HTML Template class for the Account Payments HTML object
 *
 * HTML Template class for the Account Payments HTML object
 * Lists all payments related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountPayments
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountPayments extends HtmlTemplate
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
		//$this->LoadJavascript("dhtml");
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("debug");  // Tools for debugging, only use when js-ing
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
		//TODO!
		
		Table()->PaymentTable->SetHeader("Payment Id", "Payment Amount", "Payment Date", "Account Balance");
		//Table()->PaymentTable->SetWidth("20%", "30%", "50%");
		//Table()->PaymentTable->SetAlignment("Left", FALSE, "Right");
		
		foreach (DBL()->Payment as $dboPayment)
		{
			$arrInvoices = Array();
			
			// Add this row to Payment table
			//$dboPayment->ShowInfo();
			Table()->PaymentTable->AddRow($dboPayment->Id->Value, $dboPayment->Amount->Value, $dboPayment->PaidOn->Value, $dboPayment->Balance->Value);
			Table()->PaymentTable->SetDetail("INSERT HTML CODE HERE");
			//Table()->PaymentTable->SetToolTip("[INSERT HTML CODE HERE FOR THE TOOL TIP FOR ROW]");
			
			foreach (DBL()->InvoicePayment as $dboInvoicePayment)
            {
                if ($dboInvoicePayment->Payment->Value == $dboPayment->Id->Value)
                {
                    // The current InvoicePayment record relates to the payment so add it as an index
                    Table()->PaymentTable->AddIndex("InvoiceRun", $dboInvoicePayment->InvoiceRun->Value);
                }
            }
			
			// find each InvoicePayment record that relates to the current Payment record
			/*foreach (DBL()->InvoicePayment as $dboInvoicePayment)
			{
				if ($dboInvoicePayment->Payment->Value == $dboPayment->Id->Value)
				{
					// the current InvoicePayment record relates to the current Payment record
					
					// find each Invoice that relates to the current InvoicePayment record
					foreach (DBL()->Invoice as $dboInvoice)
					{
						if ($dboInvoice->InvoiceRun->Value == $dboInvoicePayment->InvoiceRun->Value)
						{
						
						
							Table()->PaymentTable->AddIndex("Invoice", $intInvoiceNumber);
		
							// the current Invoice record relates to the current InvoicePayment record
							// this means that the current Invoice record relates to the current Payment Record
							// so store this information in both of them so that it can be used as an index when VixenTables are being built
							// also rememeber that a payment can be linked to multiple invoices
							// and an invoice can be linked to multiple payments
							
							$arrInvoices[] = $dboInvoice->Id->Value;
							$arrPayments[$dboInvoice->Id->Value][] = $dboPayment->Id->Value;
						}
					}
				}
			}*/
			//$dboPayment->Invoices = $arrInvoices;
		}		
		
		Table()->PaymentTable->LinkTable("InvoiceTable", "InvoiceRun");
		Table()->PaymentTable->RowHighlighting = TRUE;
		
		Table()->PaymentTable->Render();
		
	
	}
}

?>
