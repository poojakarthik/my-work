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
		echo "<div class='NarrowColumn'>\n";
		
		// Check if the user has admin privileges
		$bolHasAdminPerm = AuthenticatedUser()->UserHasPerm(PRIVILEGE_ADMIN);
		
		//HACK HACK HACK!!!! remove this line when we have properly implemented users loging in
		$bolHasAdminPerm = TRUE;
		//HACK HACK HACK!!!!
		
		if ($bolHasAdminPerm)
		{
			// User has admin permisions and can therefore delete a payment
			Table()->PaymentTable->SetHeader("Date", "Amount", "&nbsp;");
			Table()->PaymentTable->SetWidth("40%", "50%", "10%");
			Table()->PaymentTable->SetAlignment("Left", "Right", "Center");
		}
		else
		{
			// User cannot delete payments
			Table()->PaymentTable->SetHeader("Date", "Amount");
			Table()->PaymentTable->SetWidth("30%", "70%");
			Table()->PaymentTable->SetAlignment("Left", "Right");
		}
		
		foreach (DBL()->Payment as $dboPayment)
		{
			if ($bolHasAdminPerm)
			{
				// Check if the payment can be reversed
				if ($dboPayment->Status->Value != PAYMENT_REVERSED)
				{
					// build the "Delete Payment" link
					$strDeletePaymentHref  = Href()->DeletePayment($dboPayment->Id->Value);
					$strDeletePaymentLabel = "<span class='DefaultOutputSpan Default'><a href='$strDeletePaymentHref'><img src='img/template/delete.png' title='Delete Adjustment' /></a></span>";
				}
				else
				{
					// Payment can not be reversed
					$strDeletePaymentLabel = "";
				}
				
				// Add this row to Payment table
				Table()->PaymentTable->AddRow($dboPayment->PaidOn->AsValue(), $dboPayment->Amount->AsValue(), $strDeletePaymentLabel);
			}
			else
			{
				// Add this row to Payment table
				Table()->PaymentTable->AddRow($dboPayment->PaidOn->AsValue(), $dboPayment->Amount->AsValue());
			}
			
			// initialise variables
			$arrInvoiceId = Array();
			$arrInvoiceAmount = Array();
			
			// Add indexes, and calculate applied payments (for the drop down details)
			foreach (DBL()->InvoicePayment as $dboInvoicePayment)
            {
                if ($dboInvoicePayment->Payment->Value == $dboPayment->Id->Value)
                {
                    // The current InvoicePayment record relates to the payment so add it as an index
                    Table()->PaymentTable->AddIndex("InvoiceRun", $dboInvoicePayment->InvoiceRun->Value);
					
					// find the invoice that relates to this InvoiceRun
					$strInvoiceRun = $dboInvoicePayment->InvoiceRun->Value;
					foreach (DBL()->Invoice as $dboInvoice)
					{
						if ($dboInvoice->InvoiceRun->Value == $strInvoiceRun)
						{
							// the current invoice relates to the current payment
							// define data for the row's drop down details
							$arrInvoiceAmount[$dboInvoice->Id->AsValue()] = $dboInvoicePayment->Amount->AsValue();
							$arrInvoiceId[] = $dboInvoice->Id->AsValue();
						}
					}
                }
            }

			// Set the drop down detail, if there is anything to put in it
			if (count($arrInvoiceId))
			{
				$strDetailHtml = "<div class='VixenTableDetail'>\n";
				$strDetailHtml .= "<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
				$strDetailHtml .= "<tr><th>Invoice#</th><th>Amount</th></tr>\n";
				
				// sort the list of invoices
				sort($arrInvoiceId);

				// render the details of each invoice payment
				foreach ($arrInvoiceId as $intInvoiceId)
				{
					$strDetailHtml .= "<tr>\n";
					$strDetailHtml .= "	<td>$intInvoiceId</td>\n";
					$strDetailHtml .= "	<td>{$arrInvoiceAmount[$intInvoiceId]}</td>\n";
					$strDetailHtml .= "</tr>\n";
				}
				$strDetailHtml .= "</table>\n";
				$strDetailHtml .= "</div>\n";
				
				Table()->PaymentTable->SetDetail($strDetailHtml);
			}
			
			// Set the tooltip
			// Payment Type
			$strPaymentType = GetConstantDescription($dboPayment->PaymentType->Value, "PaymentType");
			$strToolTipHtml = $dboPayment->PaymentType->AsArbitrary($strPaymentType, RENDER_OUTPUT);
			
			// EnteredBy
			$strEnteredByName = GetEmployeeName($dboPayment->EnteredBy->Value);
			$strToolTipHtml .= $dboPayment->EnteredBy->AsArbitrary($strEnteredByName, RENDER_OUTPUT);
			
			// Status
			$strStatus = GetConstantDescription($dboPayment->Status->Value, "PaymentStatus");
			$strToolTipHtml .= $dboPayment->Status->AsArbitrary($strStatus, RENDER_OUTPUT);
			
			// if the payment's status is PAYMENT_REVERSED then AmountApplied = 0 else AmountApplied = Amount - Balance
			if ($dboStatus != PAYMENT_REVERSED)
			{
				$dboPayment->AmountApplied = $dboPayment->Amount->Value - $dboPayment->Balance->Value;
			}
			else
			{
				$dboPayment->AmountApplied = 0;
			}
			
			$strToolTipHtml .= $dboPayment->AmountApplied->AsOutput();
			
			// Balance
			$strToolTipHtml .= $dboPayment->Balance->AsOutput();
			Table()->PaymentTable->SetToolTip($strToolTipHtml);
		}
		Table()->PaymentTable->LinkTable("InvoiceTable", "InvoiceRun");
		Table()->PaymentTable->RowHighlighting = TRUE;
		
		Table()->PaymentTable->Render();
		
		echo "<div class='Right'>\n";
		$strHref = Href()->MakePayment(DBO()->Account->Id->Value);
		$this->Button("Make Payment", $strHref);
		echo "</div>\n";
		
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		echo "<div class='Seperator'></div>\n";
	
	}
}

?>
