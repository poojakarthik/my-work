<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// payment_list.php
//----------------------------------------------------------------------------//
/**
 * payment_list
 *
 * HTML Template for the Payment List HTML object
 *
 * HTML Template for the Payment List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all payments relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		payment_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAccountPaymentList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountPaymentList
 *
 * HTML Template class for the Payment List HTML object
 *
 * HTML Template class for the Payment List HTML object
 * Lists all payments related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountPaymentList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountPaymentList extends HtmlTemplate
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
		echo "<h2 class='Payment'>Payments</h2>\n";
		//echo "<div class='NarrowContent'>\n";
		
		Table()->PaymentTable->SetHeader("Date", "Amount");
		Table()->PaymentTable->SetWidth("30%", "70%");
		Table()->PaymentTable->SetAlignment("Left", "Right");
		
		foreach (DBL()->Payment as $dboPayment)
		{
			// Add this row to Payment table
			Table()->PaymentTable->AddRow($dboPayment->PaidOn->AsValue(), $dboPayment->Amount->AsValue());
			
			// Add tooltip
			$strPaymentType 	= GetConstantDescription($dboPayment->PaymentType->Value, "PaymentType");
			$strToolTipHtml 	= $dboPayment->PaymentType->AsArbitrary($strPaymentType, RENDER_OUTPUT);
			$strEnteredByName 	= GetEmployeeName($dboPayment->EnteredBy->Value);
			$strToolTipHtml 	.= $dboPayment->EnteredBy->AsArbitrary($strEnteredByName, RENDER_OUTPUT);
			$strStatus 			= GetConstantDescription($dboPayment->Status->Value, "PaymentStatus");
			$strToolTipHtml 	.= $dboPayment->Status->AsArbitrary($strStatus, RENDER_OUTPUT);
			$strToolTipHtml 	.= $dboPayment->AmountApplied->AsOutput();
			$strToolTipHtml 	.= $dboPayment->Balance->AsOutput();
			Table()->PaymentTable->SetToolTip($strToolTipHtml);
			
			// Add drop down detail
			//TODO! work out what should go here
			Table()->PaymentTable->SetDetail($strToolTipHtml);
			
			// Add indexes
			foreach (DBL()->InvoicePayment as $dboInvoicePayment)
            {
                if ($dboInvoicePayment->Payment->Value == $dboPayment->Id->Value)
                {
                    // The current InvoicePayment record relates to the payment so add it as an index
                    Table()->PaymentTable->AddIndex("InvoiceRun", $dboInvoicePayment->InvoiceRun->Value);
                }
            }
		}
		
		Table()->PaymentTable->LinkTable("InvoiceTable", "InvoiceRun");
		Table()->PaymentTable->RowHighlighting = TRUE;
		
		Table()->PaymentTable->Render();
		
		//echo "</div>\n";
		//echo "<div class='Seperator'></div>\n";
	
	}
}

?>
