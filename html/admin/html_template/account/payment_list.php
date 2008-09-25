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
		$this->LoadJavascript("tooltip");
		$this->LoadJavascript("retractable");
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
		
		// Check if the user has admin privileges
		$bolHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		$bolUserIsGod		= AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD);
		
		if ($bolHasAdminPerm)
		{
			// User has admin permisions and can therefore delete a payment
			Table()->PaymentTable->SetHeader("Date", "&nbsp;", "Amount ($)", "&nbsp;");
			Table()->PaymentTable->SetWidth("30%", "10%", "50%", "10%");
			Table()->PaymentTable->SetAlignment("Left", "Left", "Right", "Center");
		}
		else
		{
			// User cannot delete payments
			Table()->PaymentTable->SetHeader("Date", "&nbsp;", "Amount ($)");
			Table()->PaymentTable->SetWidth("30%", "10%", "60%");
			Table()->PaymentTable->SetAlignment("Left", "Left", "Right");
		}
		
		foreach (DBL()->Payment as $dboPayment)
		{
			// Reversed payments have to be obvious when looking at the table
			$strStatus = "&nbsp;";
			if ($dboPayment->Status->Value == PAYMENT_REVERSED)
			{
				//$strStatus = $dboPayment->Status->AsCallBack("GetConstantDescription", Array("PaymentStatus"));
				$strStatus = GetConstantDescription($dboPayment->Status->Value, "PaymentStatus");
			}
			
			// Build the Payment Amount cell
			$strAmountCell = "<span class='Currency'>{$dboPayment->Amount->FormattedValue()}</span>";
			
			// Build the Payment PaidOn cell
			$strPaidOnCell = $dboPayment->PaidOn->FormattedValue();
			
			if ($bolHasAdminPerm)
			{
				// Check if the payment can be reversed
				if ($dboPayment->Status->Value != PAYMENT_REVERSED)
				{
					$bolOldEtechPaymentWithNoInvoices = FALSE;
					// Check if the payment is an old Etech one which can't be reversed
					if (($dboPayment->Status->Value == PAYMENT_FINISHED) && ($dboPayment->Balance->Value == 0))
					{
						$bolHasInvoice = FALSE;
						
						// Check if there are any invoices related to this payment
						foreach (DBL()->InvoicePayment as $dboInvoicePayment)
            			{
							if ($dboInvoicePayment->Payment->Value == $dboPayment->Id->Value)
							{
								// The current InvoicePayment record relates to the payment.
								// Find the invoice that relates to this invoice_run_id
								foreach (DBL()->Invoice as $dboInvoice)
								{
									if ($dboInvoice->invoice_run_id->Value == $dboInvoicePayment->invoice_run_id->Value)
									{
										// An invoice has been found
										$bolHasInvoice = TRUE;
									}
								}
							}
						}
						
						// If the current payment has no invoices associated with it, then it is an old Etech one which can't be reversed
						if (!$bolHasInvoice)
						{
							$bolOldEtechPaymentWithNoInvoices = TRUE;
						}
            		}
					
					if ($bolOldEtechPaymentWithNoInvoices)
					{
						// Payment cannot be reversed, but should be marked as being a special case
						$strDeletePaymentLabel = "<img src='img/template/etech_payment_notice.png' title=\"Etech payment which can't be reversed\" />";
					}
					else
					{
						// Build the "Reverse Payment" link
						$strDeletePaymentHref  = Href()->DeletePayment($dboPayment->Id->Value);
						$strDeletePaymentLabel = "<img src='img/template/delete.png' title='Reverse Payment' onclick='$strDeletePaymentHref'></img>";
					}
				}
				else
				{
					// Payment can not be reversed
					$strDeletePaymentLabel = "&nbsp;";
				}
				
				// Add this row to Payment table
				Table()->PaymentTable->AddRow($strPaidOnCell, $strStatus, $strAmountCell, $strDeletePaymentLabel);
			}
			else
			{
				// Add this row to Payment table
				Table()->PaymentTable->AddRow($strPaidOnCell, $strStatus, $strAmountCell);
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
					Table()->PaymentTable->AddIndex("invoice_run_id", $dboInvoicePayment->invoice_run_id->Value);
					
					// Find the invoice that relates to this invoice_run_id
					$intInvoiceRunId = $dboInvoicePayment->invoice_run_id->Value;
					foreach (DBL()->Invoice as $dboInvoice)
					{
						if ($dboInvoice->invoice_run_id->Value == $intInvoiceRunId)
						{
							// the current invoice relates to the current payment
							// define data for the row's drop down details
							$arrInvoiceAmount[$dboInvoice->Id->Value] = number_format($dboInvoicePayment->Amount->Value, 2, ".", "");
							$arrInvoiceId[] = $dboInvoice->Id->Value;
						}
					}
                }
            }
			
			// This is used to link credit card surcharge adjustments to the payments they correspond to
			Table()->PaymentTable->AddIndex("PaymentId", $dboPayment->Id->Value);

			// Set the drop down detail, if there is anything to put in it
			if (count($arrInvoiceId))
			{
				$strDetailHtml = "<div class='VixenTableDetail'>\n";
				$strDetailHtml .= "<table border='0' cellpadding='0' cellspacing='0' width='90%'>\n";
				$strDetailHtml .= "<tr><td><b>Invoice</b></td><td align='right'><b>Amount</b></td></tr>\n";
				
				// sort the list of invoices with the most recent being first
				rsort($arrInvoiceId);

				// render the details of each invoice payment
				foreach ($arrInvoiceId as $intInvoiceId)
				{
					$strDetailHtml .= "<tr>\n";
					$strDetailHtml .= "	<td>$intInvoiceId</td>\n";
					$strDetailHtml .= "	<td align='right'>{$arrInvoiceAmount[$intInvoiceId]}</td>\n";
					$strDetailHtml .= "</tr>\n";
				}
				$strDetailHtml .= "</table>\n";
				$strDetailHtml .= "</div>\n";
				
				Table()->PaymentTable->SetDetail($strDetailHtml);
			}
			
			// Set the tooltip
			$strToolTipHtml = "";
			
			if ($bolUserIsGod)
			{
				$strToolTipHtml .= $dboPayment->Id->AsOutput();
			}
			
			// Payment Type
			$strToolTipHtml .= $dboPayment->PaymentType->AsCallBack("GetConstantDescription", Array("PaymentType"), RENDER_OUTPUT);
			
			// If there is a file import date associated with the payment, then include this too
			if ($dboPayment->ImportedOn->Value)
			{
				$strToolTipHtml .= $dboPayment->ImportedOn->AsOutput();
			}
			
			// EnteredBy
			if ($dboPayment->EnteredBy->Value != NULL && $dboPayment->EnteredBy->Value != USER_ID)
			{
				$strToolTipHtml .= $dboPayment->EnteredBy->AsCallBack("GetEmployeeName", NULL, RENDER_OUTPUT);
			}
			
			// Status
			$strToolTipHtml .= $dboPayment->Status->AsCallBack("GetConstantDescription", Array("PaymentStatus"), RENDER_OUTPUT);
			
			// if the payment's status is PAYMENT_REVERSED then AmountApplied = 0 else AmountApplied = Amount - Balance
			if ($dboPayment->Status->Value != PAYMENT_REVERSED)
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
		
		if (DBL()->Payment->RecordCount() == 0)
		{
			// There are no payments to stick in this table
			Table()->PaymentTable->AddRow("<span>No payments to display</span>");
			Table()->PaymentTable->SetRowAlignment("left");
			if ($bolHasAdminPerm)
			{
				Table()->PaymentTable->SetRowColumnSpan(4);
			}
			else
			{
				Table()->PaymentTable->SetRowColumnSpan(3);
			}
		}
		else
		{
			// Link this table to the invoice table, and the adjustments table
			Table()->PaymentTable->LinkTable("InvoiceTable", "invoice_run_id");
			
			// The current implementation of the highlight functionality cannot handle the recursive table links
			//Table()->PaymentTable->LinkTable("AdjustmentTable", "PaymentId");
			Table()->PaymentTable->RowHighlighting = TRUE;
		}
		
		Table()->PaymentTable->Render();
		
		if (AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR))
		{
			// The user can add payments
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$strHref = Href()->MakePayment(DBO()->Account->Id->Value);
			$this->Button("Make Payment", $strHref);
			echo "</div></div>\n";
		}
		else
		{
			// The user can not add payments
			// This separator is added for spacing reasons
			echo "<div class='SmallSeperator'></div>\n";
		}
	}
}

?>
