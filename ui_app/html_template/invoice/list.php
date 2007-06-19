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
		// Render each of the account invoices
		echo "<h2 class='Invoice'>Invoices</h2>\n";
		//echo "<div class='WideContent'>\n";

		
		//Table()->InvoiceTable->SetHeader("Date", "Invoice No", "Amount(total)", "Applied Amount(balance)", "Amount Owing(totalowing)", "Invoice Sent", "View PDF", "View Invoice Details");
		Table()->InvoiceTable->SetHeader("Date", "Invoice No", "Amount(total)", "Applied Amount(balance)", "Amount Owing(totalowing)", "Status", "View PDF", "View Invoice Details");
		//Table()->PaymentTable->SetWidth("20%", "30%", "50%");
		//Table()->PaymentTable->SetAlignment("Left", FALSE, "Right");
		
		foreach (DBL()->Invoice as $dboInvoice)
		{
			// build the "View pdf" link
			$strPdfHref = Href()->ViewInvoicePdf($dboInvoice->Id->Value);
			$strPdfLabel = "<span class='DefaultOutputSpan Default'><a href='$strPdfHref'><h2 class='PDF'></h2></a></span>";
			
			// build the "View Invoice Details" link
			$strViewInvoiceHref = Href()->ViewInvoice($dboInvoice->Id->Value);
			$strViewInvoiceLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewInvoiceHref'><h2 class='Invoice'></h2></a></span>";
			
			
			// Add this row to Invoice table
			Table()->InvoiceTable->AddRow(  $dboInvoice->DueOn->AsValue(),
											$dboInvoice->Id->AsValue(), 
											$dboInvoice->Total->AsValue(), 
											$dboInvoice->Balance->AsValue(), 
											$dboInvoice->TotalOwing->AsValue(), 
											$dboInvoice->Status->AsCallback("GetConstantDescription", Array("InvoiceStatus")), 
											$strPdfLabel,
											$strViewInvoiceLabel);
			Table()->InvoiceTable->SetDetail("INSERT HTML CODE HERE");
			//Table()->InvoiceTable->SetToolTip("[INSERT HTML CODE HERE FOR THE TOOL TIP FOR ROW]");
			Table()->InvoiceTable->AddIndex("InvoiceRun", $dboInvoice->InvoiceRun->Value);
		}
		
		Table()->InvoiceTable->LinkTable("PaymentTable", "InvoiceRun");
		Table()->InvoiceTable->LinkTable("AdjustmentTable", "InvoiceRun");
		
		Table()->InvoiceTable->RowHighlighting = TRUE;
		
		Table()->InvoiceTable->Render();
		//echo "</div>\n";
		//echo "<div class='Seperator'></div>\n";
		
	}
}

?>
