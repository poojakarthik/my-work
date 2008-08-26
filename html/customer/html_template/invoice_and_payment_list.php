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
		
		// The list of invoices and payments will have to be ordered by the date they were created
		// Make a single array of the data that you want to display and then sort it
		
		// Add all the invoices to the array to sort
		$arrInvoicesAndPayments = Array();
		foreach (DBL()->Invoice as $dboInvoice)
		{
			$arrRecord['InvoiceRun'] 					= $dboInvoice->InvoiceRun->Value;
			$arrRecord['InvoiceId'] 					= $dboInvoice->Id->Value;

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
		
		// Add all the payments to the array to sort
		foreach (DBL()->Payment as $dboPayment)
		{
			$arrRecord['Type'] 					= "Payment";  // This may not be required, but if it is, it should probably be changed to a constant.  Although it's only ever going to be used in this function
			$arrRecord['DateCreatedTimeStamp'] 	= strtotime($dboPayment->PaidOn->Value);
			$arrRecord['Id'] 					= $dboPayment->TXNReference->AsValue();
			$arrRecord['Date'] 					= $dboPayment->PaidOn->AsValue();
			$arrRecord['PaymentType'] 					= $dboPayment->PaymentType->Value;
			
			// Work out what will be displayed in the Debit and Credit columns of the table
			$arrRecord['CreditValue']			= $dboPayment->Amount->Value;
			$arrRecord['Debit']					= "&nbsp;";
			$arrRecord['DebitValue']			= 0;
			
			// If the Payment is applied to an AccountGroup then flag it as such;
			if ($dboPayment->Account->Value === NULL)
			{
				// Payment has been applied to the account group that this account belongs to
				$arrRecord['Credit'] = "<span style='float:left;'>(Group Payment)</span><span style='float:right;'>" . $dboPayment->Amount->AsValue() . "</span>";
			}
			else
			{
				// Payment is applied directly to the account
				$arrRecord['Credit'] = $dboPayment->Amount->AsValue();
			}
			
			// Append the record to the array to sort
			$arrInvoicesAndPayments[] = $arrRecord;
		}
		
		// sort the array in descending order of the date they were created on. (most recent record first)
		usort($arrInvoicesAndPayments, "CompareInvoiceAndPaymentRecords");
		
		// create the Invoices and Payments table
		Table()->InvoicesAndPayments->SetHeader("Type", "Date", "Ref #", "Credit (inc GST)", "Debit (inc GST)", "&nbsp;");
		Table()->InvoicesAndPayments->SetWidth("15%", "15%", "25%", "25%", "15%", "5%");
		Table()->InvoicesAndPayments->SetAlignment("left", "left", "left", "right", "right", "center");
		
		// Declare variables used to calculate the total Credits and Debits
		$fltCreditTotal	= 0;
		$fltDebitTotal	= 0;
		
		// add the rows
		foreach ($arrInvoicesAndPayments as $arrRecord)
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
				$strInvoiceRun = $arrRecord['InvoiceRun'];
				$strInvoiceId = $arrRecord['InvoiceId'];
				// Find out if there is a pdf for this invoice
				$intInvoiceDate 	= strtotime("-1 month", $arrRecord['DateCreatedTimeStamp']);
				$intInvoiceYear 	= (int)date("Y", $intInvoiceDate);
				$intInvoiceMonth 	= (int)date("m", $intInvoiceDate);
				if (InvoicePDFExists(DBO()->Account->Id->Value, $intInvoiceYear, $intInvoiceMonth, $strInvoiceId, $strInvoiceRun))
				{
					// The pdf exists
					// Build "download invoice pdf" link
					$strInvoicePdfHref 	= Href()->DownloadInvoicePDF(DBO()->Account->Id->Value, $intInvoiceYear, $intInvoiceMonth, $strInvoiceId, $strInvoiceRun);
					$strInvoicePdfLabel	= "<span><a href='$strInvoicePdfHref'><img src='img/template/pdf.gif' title='Download PDF Invoice' /></a></span>";
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
			}

			// Display the payment type, e.g. Credit Card, BillExpress, etc, etc
			$mixPaymentType = $GLOBALS['*arrConstant']['PaymentType']["$strPaymentType"]['Description'];
			$mixRefAndType = "$strRefNum - $mixPaymentType";
			Table()->InvoicesAndPayments->AddRow($strRecordType, $strDate, $mixRefAndType, $strCredit, $strDebit, $strInvoicePdfLabel);
			
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
