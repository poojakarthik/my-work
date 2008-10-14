<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// list.php
//----------------------------------------------------------------------------//
/**
 * list
 *
 * HTML Template for the Invoice List HTML object
 *
 * HTML Template for the Invoice List HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all invoices relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateInvoiceList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateInvoiceList
 *
 * HTML Template class for the Invoice List HTML object
 *
 * HTML Template class for the Invoice List HTML object
 * Lists all invoices related to an account
 *
 * @package	ui_app
 * @class	HtmlTemplateInvoiceList
 * @extends	HtmlTemplate
 */
class HtmlTemplateInvoiceList extends HtmlTemplate
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
		$bolUserHasOperatorPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
	
		// Render each of the account invoices
		echo "<h2 class='Invoice'>Invoices</h2>\n";
		
		Table()->InvoiceTable->SetHeader("Date", "Invoice #", "Invoice Amount", "Applied Amount", "Amount Owing", "Status", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;");
		Table()->InvoiceTable->SetWidth("10%", "12%", "17%", "17%", "17%", "11%", "4%", "4%", "4%", "4%");
		Table()->InvoiceTable->SetAlignment("Left", "Left", "Right", "Right", "Right", "Left", "Center", "Center", "Center", "Center");
		
		// Invoices that are older than 1 year will not have CDR records stored in the database
		$strCDRCutoffDate = date("Y-m-01", strtotime("-1 year"));

/*
		$arrSampleInvoices = ListPDFSamples(DBO()->Account->Id->Value);
		foreach ($arrSampleInvoices as $strInvoiceRun => $strSampleType)
		{
			// If the strInvoiceRun value is shorter than a 8, it must be an Id. We should load the InvoiceRun for that so that we can 
			// display the link for it.
			$strPdfHref		= Href()->ViewInvoicePdf(DBO()->Account->Id->Value, 0, 0, 0, $strInvoiceRun);
			$strPdfLabel 	= "<a href='$strPdfHref'><img src='img/template/pdf_small.png' title='View $strSampleType Sample PDF Invoice' /></a>";
			$strInvoiceRunDate = is_numeric(substr($strInvoiceRun, 0, 8)) 
									? substr($strInvoiceRun, 6, 2) . '/' . substr($strInvoiceRun, 4, 2) . '/' .substr($strInvoiceRun, 0, 4) 
									: "Unknown";

			// Add this row to Invoice table
			Table()->InvoiceTable->AddRow(  $strInvoiceRunDate,
											$strSampleType, 
											"N/A", 
											"N/A",
											"N/A",
											'Sample', 
											$strPdfLabel, 
											"&nbsp;",
											"&nbsp;",
											"&nbsp;");
		}
*/
		foreach (DBL()->Invoice as $dboInvoice)
		{
			$bolIsSample = $dboInvoice->Status->Value == "SAMPLE";
			
			// Build the links 
			$intDate = strtotime("-1 month", strtotime($dboInvoice->CreatedOn->Value));
			$intYear = (int)date("Y", $intDate);
			$intMonth = (int)date("m", $intDate);

			// Check if a pdf exists for the invoice
			$strPdfLabel	= "&nbsp;";
			$strEmailLabel	= "&nbsp;";

			if (InvoicePDFExists($dboInvoice->Account->Value, $intYear, $intMonth, $dboInvoice->Id->Value, intval($dboInvoice->invoice_run_id->Value)))
			{
				// The pdf exists
				// Build "view invoice pdf" link
				$strPdfHref 	= Href()->ViewInvoicePdf($dboInvoice->Account->Value, $intYear, $intMonth, $dboInvoice->Id->Value, $dboInvoice->invoice_run_id->Value);
				$strPdfLabel 	= "<a href='$strPdfHref'><img src='img/template/pdf_small.png' title='View PDF Invoice' /></a>";
				
				// Build "Email invoice pdf" link, if the user has OPERATOR privileges
				if (!$bolIsSample && $bolUserHasOperatorPerm)
				{
					$strEmailHref 	= Href()->EmailPDFInvoice($dboInvoice->Account->Value, $intYear, $intMonth, $dboInvoice->Id->Value, $dboInvoice->invoice_run_id->Value);
					$strEmailLabel = "<img src='img/template/email.png' title='Email PDF Invoice' onclick='$strEmailHref'></img>";
				}
			}

			$strViewInvoiceLabel	= "&nbsp;";
			$strExportCSV			= "&nbsp;";
			if ($dboInvoice->CreatedOn->Value > $strCDRCutoffDate)
			{
				// Build the "View Invoice Details" link
				$strViewInvoiceHref		= Href()->ViewInvoice($dboInvoice->Id->Value);
				$strViewInvoiceLabel	= "<a href='$strViewInvoiceHref'><img src='img/template/invoice.png' title='View Invoice Details' /></a>";
				
				// Build the "Export Invoice as CSV" link
				$strExportCSV = Href()->ExportInvoiceAsCSV($dboInvoice->Id->Value);
				$strExportCSV = "<a href='$strExportCSV'><img src='img/template/export.png' title='Export as CSV' /></a>";
			}

			// Calculate Invoice Amount
			$dboInvoice->Amount = $dboInvoice->Total->Value + $dboInvoice->Tax->Value;

			// Calculate AppliedAmount
			$dboInvoice->AppliedAmount = $dboInvoice->Amount->Value - $dboInvoice->Balance->Value;

			// Add this row to Invoice table
			Table()->InvoiceTable->AddRow(  $dboInvoice->CreatedOn->FormattedValue(),
											$dboInvoice->Id->Value, 
											"<span class='Currency'>". $dboInvoice->Amount->FormattedValue() ."</span>", 
											"<span class='Currency'>". $dboInvoice->AppliedAmount->FormattedValue() ."</span>",
											"<span class='Currency'>". $dboInvoice->Balance->FormattedValue() ."</span>",
											($bolIsSample ? "SAMPLE" : GetConstantDescription($dboInvoice->Status->Value, "InvoiceStatus")), 
											$strPdfLabel, 
											$strEmailLabel,
											$strViewInvoiceLabel,
											$strExportCSV);
											
			// Set the drop down detail
			$strDetailHtml = "<div class='VixenTableDetail'>\n";
			$strDetailHtml .= $dboInvoice->DueOn->AsOutput();
			if ($dboInvoice->SettledOn->Value)
			{
				$strDetailHtml .= $dboInvoice->SettledOn->AsOutput();
			}
			//$strDetailHtml .= $dboInvoice->Credits->AsOutput();
			//$strDetailHtml .= $dboInvoice->Debits->AsOutput();
			//$strDetailHtml .= $dboInvoice->Total->AsOutput();
			//$strDetailHtml .= $dboInvoice->Tax->AsOutput();
			$strDetailHtml .= $dboInvoice->TotalOwing->AsOutput();
			//$strDetailHtml .= $dboInvoice->Balance->AsOutput();
			if ($dboInvoice->Disputed->Value > 0)//does this include GST??????
			{
				$strDetailHtml .= $dboInvoice->Disputed->AsOutput();
			}
			//$strDetailHtml .= $dboInvoice->AccountBalance->AsOutput();
			$strDetailHtml .= "</div>\n";

			Table()->InvoiceTable->SetDetail($strDetailHtml);

			// Add the row index
			Table()->InvoiceTable->AddIndex("invoice_run_id", $dboInvoice->invoice_run_id->Value);
		}
		
		if (Table()->InvoiceTable->RowCount() == 0)
		{
			// There are no invoices to stick in this table
			Table()->InvoiceTable->AddRow("No invoices to display");
			Table()->InvoiceTable->SetRowAlignment("left");
			Table()->InvoiceTable->SetRowColumnSpan(10);
		}
		else
		{
			// Link this table to the Payments table and the Adjustments table
			Table()->InvoiceTable->LinkTable("PaymentTable", "invoice_run_id");
			Table()->InvoiceTable->LinkTable("AdjustmentTable", "invoice_run_id");
			Table()->InvoiceTable->RowHighlighting = TRUE;
		}
		
		Table()->InvoiceTable->Render();

		echo "<div class='SmallSeperator'></div>\n";
	}
}

?>
