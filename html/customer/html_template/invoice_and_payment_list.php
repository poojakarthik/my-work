<?php


//------------------------------------------------------------------------//
// CompareInvoiceAndPaymentRecords
//------------------------------------------------------------------------//
/**
 * CompareInvoiceAndPaymentRecords()
 *
 * Compares the CreatedOn dates of 2 Invoice and Payment records to see which came first
 *
 * Compares the CreatedOn dates of 2 Invoice and Payment records to see which came first
 * This is used in conjunction with the usort function to sort the list of Invoices and 
 * Payments from newest to oldest
 *
 * @param		array	$arrRec1	a single Record of the $arrInvoicesAndPayments array
 * @param		array	$arrRec2	a single Record of the $arrInvoicesAndPayments array
 *
 * @return		integer				returns the difference between the DateCreated time stamps which is used to sort the $arrInvoicesAndPayments array
 * @method
 */

function CompareInvoiceAndPaymentRecords($arrRec1, $arrRec2)
{
	return $arrRec2['DateCreatedTimeStamp'] - $arrRec1['DateCreatedTimeStamp'];
}


//----------------------------------------------------------------------------//
// HtmlTemplateInvoiceAndPaymentList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateInvoiceAndPaymentList
 *
 * HTML Template object for the client app, Displays a list of invoices and payments for a given Account
 *
 * HTML Template object for the client app, Displays a list of invoices and payments for a given Account
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateInvoiceAndPaymentList
 * @extends	HtmlTemplate
 */
class HtmlTemplateInvoiceAndPaymentList extends HtmlTemplate
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
		echo "<div class='WideContent'>\n";
		
		echo "
		<TABLE width=\"100%\">
		<TR>
			<TD style=\"width: 300px; text-indent: 5; font-size: 8pt;\"><a href=\"#\" onclick=\"javascript:window.print();return false;\"><IMG SRC=\"./img/generic/Button_Printer.jpg\" WIDTH=\"18\" HEIGHT=\"20\" BORDER=\"0\" ALT=\"\"></a> <a href=\"#\" onclick=\"javascript:window.print();return false;\">Print This Page</a></TD>
			<TD style=\"text-indent: 175; font-weight: bold;\"><!-- anything can go here --></TD>
		</TR>
		<TR>
			<TD COLSPAN=\"2\" style=\"text-indent: 5; font-size: 8pt;\"><a href=\"http://www.adobe.com/\"><img src=\"./img/template/acrobat_15x15.jpg\" width=\"15\" height=\"15\" border=\"0\" alt=\"\"></a> Please ensure you have the latest copy of acrobat reader and provider link to download. <a href=\"http://www.adobe.com/\">Download Acrobat Reader</a></TD>
		</TR>
		</TABLE>\n";

		// The list of invoices and payments will have to be ordered by the date they were created
		// Make a single array of the data that you want to display and then sort it
		
		// Add all the invoices to the array to sort
		$arrInvoicesAndPayments = Array();
		foreach (DBL()->Invoice as $dboInvoice)
		{
			$arrRecord = array();
			$arrRecord['Visible']			= true;
			$arrRecord['invoice_run_id']	= $dboInvoice->invoice_run_id->Value;
			$arrRecord['InvoiceId'] 		= $dboInvoice->Id->Value;

			$arrRecord['Type'] 					= "Invoice";  // This may not be required, but if it is, it should probably be changed to a constant.  Although it's only ever going to be used in this function
			$arrRecord['DateCreatedTimeStamp'] 	= strtotime($dboInvoice->CreatedOn->Value);
			$arrRecord['Id'] 					= $dboInvoice->Id->AsValue();
			$arrRecord['Date'] 					= $dboInvoice->CreatedOn->AsValue();

			// Work out what will be displayed in the Debit and Credit columns of the table
			$fltDebit = $dboInvoice->Total->Value + $dboInvoice->Tax->Value;
			if ($fltDebit < 0)
			{
				// The invoice value (Total + Tax) should be listed in the Credit column
				// Convert $fltDebit to Credit
				$fltCredit					= $fltDebit * (-1.0);
				$arrRecord['Credit']		= $dboInvoice->Total->AsArbitrary($fltCredit);
				$arrRecord['CreditValue'] 	= $fltCredit;
				$arrRecord['Debit']			= "&nbsp;";
				$arrRecord['DebitValue'] 	= 0;
			}
			else
			{
				// The invoice value should be listed in the Debit column
				$arrRecord['Debit']			= $dboInvoice->Total->AsArbitrary($fltDebit);
				$arrRecord['DebitValue']	= $fltDebit;
				$arrRecord['Credit']		= "&nbsp;";
				$arrRecord['CreditValue'] 	= 0;
			}
			
			// Append the record to the array to sort
			$arrInvoicesAndPayments[] 		= $arrRecord;
		}
		
		// Add all the payments to the array to sort (Only show non-reversed and those that have been reversed due to dishonour)
		foreach (DBO()->Payments as $aPayment)
		{
			$arrRecord = array();
			if ($aPayment['payment_reversal_type_id'] == PAYMENT_REVERSAL_TYPE_AGENT || $aPayment['reversed_by_payment_reversal_type_id'] == PAYMENT_REVERSAL_TYPE_AGENT)
			{
				// Ignore reversed payments or reversals that are agent reversals
				$arrRecord['Visible'] = false;
			}
			else
			{
				$arrRecord['Visible'] = true;
			}
			
			$arrRecord['Type'] 					= "Payment";
			$arrRecord['DateCreatedTimeStamp'] 	= strtotime($aPayment['paid_date']);
			$arrRecord['Id'] 					= "<span>{$aPayment['transaction_reference']}</span>";
			$arrRecord['Date'] 					= "<span>".date('d/m/Y', $arrRecord['DateCreatedTimeStamp'])."</span>";
			$arrRecord['PaymentType'] 			= $aPayment['payment_type_name'];
			
			// Determine if a reversal reason needs to be shown
			if ($aPayment['payment_reversal_type_id'] !== null)
			{
				$arrRecord['ReversalReason'] = "<span>({$aPayment['payment_reversal_reason_name']})</span>";
			}
			else
			{
				$arrRecord['ReversalReason'] = null;
			}
			
			// Work out what will be displayed in the Debit and Credit columns of the table
			$fAmount 	= Rate::roundToRatingStandard($aPayment['amount'], 2);
			$fAbsAmount	= abs($fAmount);
			$sAbsAmount	= number_format($fAbsAmount, 2);
			if ($fAmount < 0)
			{
				// Credit
				$arrRecord['Credit'] 		= "<span class='Currency'>{$sAbsAmount}</span>";
				$arrRecord['CreditValue']	= $fAbsAmount;
				$arrRecord['Debit'] 		= "&nbsp;";
				$arrRecord['DebitValue']	= 0;
			}
			else
			{
				// Debit
				$arrRecord['Debit'] 		= "<span class='Currency'>{$sAbsAmount}</span>";
				$arrRecord['DebitValue']	= $fAbsAmount;
				$arrRecord['Credit'] 		= "&nbsp;";
				$arrRecord['CreditValue']	= 0;
			}
			
			// Append the record to the array to sort
			$arrInvoicesAndPayments[] = $arrRecord;
		}
		
		// Add all the adjustments to the array to sort (Only show non-reversed and those that have been reversed due to dishonour)
		foreach (DBO()->Adjustments as $aAdjustment)
		{
			$arrRecord = array();
			if ($aAdjustment['reversed_adjustment_id'] !== null || $aAdjustment['reversed_by_adjustment_id'] !== null || $aAdjustment['visible_on_invoice'] == 0)
			{
				// Ignore reversed adjustments, or ones not visible on invoice
				$arrRecord['Visible'] = false;
			}
			else
			{
				$arrRecord['Visible'] = true;
			}
			
			$arrRecord['Type'] 					= "Adjustment";
			$arrRecord['DateCreatedTimeStamp'] 	= strtotime($aAdjustment['effective_date']);
			$arrRecord['Id'] 					= "&nbsp;";
			$arrRecord['Date'] 					= "<span>".date('d/m/Y', $arrRecord['DateCreatedTimeStamp'])."</span>";
			$arrRecord['PaymentType'] 			= $aAdjustment['adjustment_type_name'];
			
			// Work out what will be displayed in the Debit and Credit columns of the table
			$fAmount 	= Rate::roundToRatingStandard($aAdjustment['amount'], 2);
			$fAbsAmount	= abs($fAmount);
			$sAbsAmount	= number_format($fAbsAmount, 2);
			if ($fAmount < 0)
			{
				// Credit
				$arrRecord['Credit'] 		= "<span class='Currency'>{$sAbsAmount}</span>";
				$arrRecord['CreditValue']	= $fAbsAmount;
				$arrRecord['Debit'] 		= "&nbsp;";
				$arrRecord['DebitValue']	= 0;
			}
			else
			{
				// Debit
				$arrRecord['Debit'] 		= "<span class='Currency'>{$sAbsAmount}</span>";
				$arrRecord['DebitValue']	= $fAbsAmount;
				$arrRecord['Credit'] 		= "&nbsp;";
				$arrRecord['CreditValue']	= 0;
			}
			
			// Append the record to the array to sort
			$arrInvoicesAndPayments[] = $arrRecord;
		}
		
		// sort the array in descending order of the date they were created on. (most recent record first)
		usort($arrInvoicesAndPayments, "CompareInvoiceAndPaymentRecords");
		
		// create the Invoices and Payments table
		Table()->InvoicesAndPayments->SetHeader("Type", "Date", "Ref #", "Credit (inc GST)", "Debit (inc GST)", "&nbsp;");
		Table()->InvoicesAndPayments->SetWidth("8%", "15%", "25%", "25%", "15%", "12%");
		Table()->InvoicesAndPayments->SetAlignment("left", "left", "left", "right", "right", "center");
		
		// Declare variables used to calculate the total Credits and Debits
		$fltCreditTotal	= 0;
		$fltDebitTotal	= 0;
		
		// add the rows
		foreach ($arrInvoicesAndPayments as $arrRecord)
		{
			if ($arrRecord['Visible'])
			{
				// build values for the columns of the table
				$strRecordType 	= "<span class='DefaultOutputSpan'>{$arrRecord['Type']}</span>";
				$strRefNum		= $arrRecord['Id'];
				$strPaymentType	= $arrRecord['PaymentType'];
				$strDate		= $arrRecord['Date'];
				$strCredit		= $arrRecord['Credit'];
				$strDebit		= $arrRecord['Debit'];
				
				if ($arrRecord['Type'] == 'Invoice')
				{	
					$intInvoiceRunId = $arrRecord['invoice_run_id'];
					$strInvoiceId = $arrRecord['InvoiceId'];
					// Find out if there is a pdf for this invoice
					$intInvoiceDate 	= strtotime("-1 month", $arrRecord['DateCreatedTimeStamp']);
					$intInvoiceYear 	= (int)date("Y", $intInvoiceDate);
					$intInvoiceMonth 	= (int)date("m", $intInvoiceDate);
					if (InvoicePDFExists(DBO()->Account->Id->Value, $intInvoiceYear, $intInvoiceMonth, $strInvoiceId, intval($intInvoiceRunId)))
					{
						// The pdf exists
						// Build "download invoice pdf" link
						$strInvoicePdfHref 	= Href()->DownloadInvoicePDF(DBO()->Account->Id->Value, $intInvoiceYear, $intInvoiceMonth, $strInvoiceId, $intInvoiceRunId);
						$strInvoicePdfLabel	= "<span><a href='$strInvoicePdfHref'><img src='img/template/pdf.gif' title='Download PDF Invoice' /></a> <a href='$strInvoicePdfHref'>View my bill</a></span>";
					}
					else
					{
						// don't allow the user to view the pdf for this invoice because it doesn't exist
						$strInvoicePdfLabel	= "&nbsp;";
					}
				}
				else
				{
					// Values specific to Payments
					$strInvoicePdfLabel = "&nbsp;";
					if ($arrRecord['ReversalReason'] !== null)
					{
						$strInvoicePdfLabel = $arrRecord['ReversalReason'];
					}
				}
				
				$mixRefAndType = "<span>".($strRefNum !== '&nbsp;' ? "$strRefNum  - " : '')."{$strPaymentType}</span>";
				Table()->InvoicesAndPayments->AddRow($strRecordType, $strDate, $mixRefAndType, $strCredit, $strDebit, $strInvoicePdfLabel);
			}
			
			$fltCreditTotal	+= $arrRecord['CreditValue'];
			$fltDebitTotal	+= $arrRecord['DebitValue'];
		}
		
		if (Table()->InvoicesAndPayments->RowCount() == 0)
		{
			// There aren't any Invoices or Payments to display in the table
			Table()->InvoicesAndPayments->AddRow("<span class='DefaultOutputSpan Default'>No records to display</span>\n");
			Table()->InvoicesAndPayments->SetRowAlignment("left");
			Table()->InvoicesAndPayments->SetRowColumnSpan(6);
		}
		else
		{
			// Append the sub-totals and total rows
			$strTotals			= "<span class='DefaultOutputSpan Default' style='font-weight:bold;'>Total:</span>\n";
			$strTotalCredits	= "<span class='DefaultOutputSpan Currency' style='font-weight:bold;'>". OutputMask()->MoneyValue($fltCreditTotal, 2, TRUE) ."</span>\n";
			$strTotalDebits		= "<span class='DefaultOutputSpan Currency' style='font-weight:bold;'>". OutputMask()->MoneyValue($fltDebitTotal, 2, TRUE) ."</span>\n";
			
			Table()->InvoicesAndPayments->AddRow($strTotals, $strTotalCredits, $strTotalDebits, "&nbsp;");
			Table()->InvoicesAndPayments->SetRowAlignment("left", "right", "right", "center");
			Table()->InvoicesAndPayments->SetRowColumnSpan(3, 1, 1, 1);
		}
		
		// Render the table
		//Table()->InvoicesAndPayments->RowHighlighting = TRUE;
		Table()->InvoicesAndPayments->Render();
		
		echo "</div>\n";
	}
}

?>
