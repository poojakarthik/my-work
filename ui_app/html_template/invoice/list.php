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
		
		Table()->InvoiceTable->SetHeader("Date", "Invoice #", "Invoice Amount", "Applied Amount", "Amount Owing", "Status", "&nbsp;", "&nbsp;", "&nbsp;");
		Table()->InvoiceTable->SetWidth("10%", "11%", "18%", "18%", "18%", "10%", "5%", "5%", "5%");
		Table()->InvoiceTable->SetAlignment("Left", "Left", "Right", "Right", "Right", "Left", "Center", "Center", "Center");
		
		foreach (DBL()->Invoice as $dboInvoice)
		{
			// Build the links 
			$intDate = strtotime("-1 month", strtotime($dboInvoice->CreatedOn->Value));
			$intYear = (int)date("Y", $intDate);
			$intMonth = (int)date("m", $intDate);
			
			// Check if a pdf exists for the invoice
			$strPdfLabel	= "<span>&nbsp;</span>";
			$strEmailLabel	= "<span>&nbsp;</span>";
			if (InvoicePdfExists($dboInvoice->Account->Value, $intYear, $intMonth))
			{
				// The pdf exists
				// Build "view invoice pdf" link
				$strPdfHref 	= Href()->ViewInvoicePdf($dboInvoice->Account->Value, $intYear, $intMonth);
				$strPdfLabel 	= "<span><a href='$strPdfHref'><img src='img/template/pdf_small.png' title='View PDF Invoice' /></a></span>";
				
				// Build "Email invoice pdf" link, if the user has OPERATOR privileges
				if ($bolUserHasOperatorPerm)
				{
					$strEmailHref 	= Href()->EmailPDFInvoice($dboInvoice->Account->Value, $intYear, $intMonth);
					$strEmailLabel 	= "<span><a href='$strEmailHref'><img src='img/template/email.png' title='Email PDF Invoice' /></a></span>";
				}
			}
			
			// Build the "View Invoice Details" link
			$strViewInvoiceHref = Href()->ViewInvoice($dboInvoice->Id->Value);
			$strViewInvoiceLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewInvoiceHref'><img src='img/template/invoice.png' title='View Invoice Details' /></a></span>";
			
			// Calculate Invoice Amount
			$dboInvoice->Amount = $dboInvoice->Total->Value + $dboInvoice->Tax->Value;
			// Calculate AppliedAmount
			$dboInvoice->AppliedAmount = $dboInvoice->Amount->Value - $dboInvoice->Balance->Value;
			
			// Add this row to Invoice table
			Table()->InvoiceTable->AddRow(  $dboInvoice->CreatedOn->AsValue(),
											$dboInvoice->Id->AsValue(), 
											$dboInvoice->Amount->AsValue(), 
											$dboInvoice->AppliedAmount->AsValue(), 
											$dboInvoice->Balance->AsValue(), 
											$dboInvoice->Status->AsCallback("GetConstantDescription", Array("InvoiceStatus")), 
											$strPdfLabel,
											$strViewInvoiceLabel,
											$strEmailLabel);
											
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
			Table()->InvoiceTable->AddRow("<span class='DefaultOutputSpan Default'>No invoices to display</span>");
			Table()->InvoiceTable->SetRowAlignment("left");
			Table()->InvoiceTable->SetRowColumnSpan(9);
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
