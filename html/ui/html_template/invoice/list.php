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
		$bolUserHasOperatorPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_OPERATOR);
	
		// Render each of the account invoices
		echo "<h2 class='Invoice'>Invoices</h2>\n";
		
		Table()->InvoiceTable->SetHeader("Date", "Invoice #", "Invoice Amount", "Applied Amount", "Amount Owing", "Status", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;");
		Table()->InvoiceTable->SetWidth("10%", "12%", "17%", "17%", "17%", "11%", "4%", "4%", "4%", "4%");
		Table()->InvoiceTable->SetAlignment("Left", "Left", "Right", "Right", "Right", "Left", "Center", "Center", "Center", "Center");
		
		// Invoices that are older than 1 year will not have CDR records stored in the database
		$strCDRCutoffDate = date("Y-m-01", strtotime("-1 year"));
		
		foreach (DBL()->Invoice as $dboInvoice)
		{
			// Build the links 
			$intDate = strtotime("-1 month", strtotime($dboInvoice->CreatedOn->Value));
			$intYear = (int)date("Y", $intDate);
			$intMonth = (int)date("m", $intDate);
			
			// Check if a pdf exists for the invoice
			$strPdfLabel	= "&nbsp;";
			$strEmailLabel	= "&nbsp;";
			if (InvoicePdfExists($dboInvoice->Account->Value, $intYear, $intMonth))
			{
				// The pdf exists
				// Build "view invoice pdf" link
				$strPdfHref 	= Href()->ViewInvoicePdf($dboInvoice->Account->Value, $intYear, $intMonth);
				$strPdfLabel 	= "<a href='$strPdfHref'><img src='img/template/pdf_small.png' title='View PDF Invoice' /></a>";
				
				// Build "Email invoice pdf" link, if the user has OPERATOR privileges
				if ($bolUserHasOperatorPerm)
				{
					$strEmailHref 	= Href()->EmailPDFInvoice($dboInvoice->Account->Value, $intYear, $intMonth);
					$strEmailLabel 	= "<a href='$strEmailHref'><img src='img/template/email.png' title='Email PDF Invoice' /></a>";
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
											GetConstantDescription($dboInvoice->Status->Value, "InvoiceStatus"), 
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
			Table()->InvoiceTable->AddIndex("InvoiceRun", $dboInvoice->InvoiceRun->Value);
		}
		
		if (DBL()->Invoice->RecordCount() == 0)
		{
			// There are no invoices to stick in this table
			Table()->InvoiceTable->AddRow("<span>No invoices to display</span>");
			Table()->InvoiceTable->SetRowAlignment("left");
			Table()->InvoiceTable->SetRowColumnSpan(10);
		}
		else
		{
			// Link this table to the Payments table and the Adjustments table
			Table()->InvoiceTable->LinkTable("PaymentTable", "InvoiceRun");
			Table()->InvoiceTable->LinkTable("AdjustmentTable", "InvoiceRun");
			Table()->InvoiceTable->RowHighlighting = TRUE;
		}
		
		Table()->InvoiceTable->Render();

		echo "<div class='SmallSeperator'></div>\n";
	}
}

?>
