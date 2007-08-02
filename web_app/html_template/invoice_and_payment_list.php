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
		echo "<h2 class='Invoice'>Invoices and Payments</h2>\n";
		
		// The list of invoices and payments will have to be ordered by the date they were created
		// Make a single array of the data that you want to display and then sort it
		
		// Add all the invoices to the array to sort
		$arrInvoicesAndPayments = Array();
		foreach (DBL()->Invoice as $dboInvoice)
		{
			$arrRecord['Type'] 					= "Invoice";  // This may not be required, but if it is, it should probably be changed to a constant.  Although it's only ever going to be used in this function
			$arrRecord['DateCreatedTimeStamp'] 	= strtotime($dboInvoice->CreatedOn->Value);
			$arrRecord['Id'] 					= $dboInvoice->Id->AsValue();
			
			// Store data properties specifically required of invoices
			$arrRecord['DateCreated'] 			= $dboInvoice->CreatedOn->AsValue();
			$arrRecord['Debit'] 				= $dboInvoice->Total->AsValue();
			$arrRecord['Tax'] 					= $dboInvoice->Tax->AsValue();
			//TODO! there are probably more of these to add
			
			// Append the record to the array to sort
			$arrInvoicesAndPayments[] 			= $arrRecord;
		}
		
		// Add all the payments to the array to sort
		foreach (DBL()->Payment as $dboPayment)
		{
			$arrRecord['Type'] 					= "Payment";  // This may not be required, but if it is, it should probably be changed to a constant.  Although it's only ever going to be used in this function
			$arrRecord['DateCreatedTimeStamp'] 	= strtotime($dboPayment->PaidOn->Value);
			$arrRecord['Id'] 					= $dboPayment->Id->AsValue();
			
			// Store data properties specifically required of payments
			$arrRecord['DatePaidOn'] 			= $dboPayment->PaidOn->AsValue();
			$arrRecord['Amount'] 				= $dboPayment->Amount->AsValue();
			
			// Append the record to the array to sort
			$arrInvoicesAndPayments[] 			= $arrRecord;
		}
		
		// sort the array in descending order of the date they were created on. (most recent record first)
		usort($arrInvoicesAndPayments, "CompareInvoiceAndPaymentRecords");
		
		
		
		Table()->InvoicesAndPayments->SetHeader("Type", "Date", "Ref #", "Amount (inc GST)", "Balance (inc GST)", "&nbsp;");
		Table()->InvoicesAndPayments->SetWidth("15%", "15%", "15%", "25%", "25%", "5%");
		Table()->InvoicesAndPayments->SetAlignment("left", "left", "left", "right", "right", "center");
		
		// add the rows
		foreach ($arrInvoicesAndPayments as $arrRecord)
		{
			// build values for the columns of the table
			$strRecordType 	= "<span class='DefaultOutputSpan'>{$arrRecord['Type']}</span>";
			$strRefNum		= $arrRecord['Id'];
			$strBalance 	= $arrRecord['Balance'];
			
			if ($arrRecord['Type'] == 'Invoice')
			{	
				// Values specific to Invoices
				$strDate 	= $arrRecord['DateCreated'];
				$strAmount 	= $arrRecord['TotalOwing'];
				
				// Find out if there is a pdf for this invoice
				$intInvoiceDate 	= strtotime("-1 month", $arrRecord['DateCreatedTimeStamp']);
				$intInvoiceYear 	= (int)date("Y", $intInvoiceDate);
				$intInvoiceMonth 	= (int)date("m", $intInvoiceDate);
				
				if (InvoicePDFExists(DBO()->Account->Id->Value, $intInvoiceMonth, $intInvoiceYear))
				{
					// The pdf exists
					// Build "download invoice pdf" link
					$strInvoicePdfHref 	= Href()->DownloadInvoicePDF(DBO()->Account->Value, $intInvoiceYear, $intInvoiceMonth);
					$strInvoicePdfLabel	= "<span class='DefaultOutputSpan'><a href='$strInvoicePdfHref'><img src='img/template/pdf.png' title='Download PDF Invoice' /></a></span>";
				}
				else
				{
					// don't allow the user to view the pdf for this invoice (or email it) because it doesn't exist
					$strInvoicePdfLabel	= "&nbsp;";
				}
			}
			else
			{
				// Values specific to Payments
				$strDate = $arrRecord['DatePaidOn'];
				$strAmount = $arrRecord['Amount'];
				$strInvoicePdfLabel = "&nbsp;";
			}
			
			Table()->InvoicesAndPayments->AddRow($strRecordType, $strDate, $strRefNum, $strAmount, $strBalance, $strInvoicePdfLabel);
		}
		
		if (Table()->InvoicesAndPayments->RowCount() == 0)
		{
			// There aren't any CDRs to display in the CDR table
			Table()->InvoicesAndPayments->AddRow("<span class='DefaultOutputSpan'>No records to display</span>", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;");
		}
		
		// Render the Call Information table
		Table()->InvoicesAndPayments->Render();
		
		echo "<div class='Seperator'></div>\n";
		echo "</div>\n";
	}
}

?>
