<?php
//----------------------------------------------------------------------------//
// HtmlTemplateContactAccounts
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateContactAccounts
 *
 * A specific HTML Template object
 *
 * An Contact Accounts HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateContactAccounts
 * @extends	HtmlTemplate
 */
class HtmlTemplateContactAccounts extends HtmlTemplate
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
		
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_LEDGER_DETAIL:
				$this->_RenderLedgerDetail();
				break;
			case HTML_CONTEXT_FULL_DETAIL:
				$this->_RenderFullDetail();
				break;
			default:
				$this->_RenderFullDetail();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderFullDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderFullDetail()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderFullDetail()
	{
		echo "<h2 class='Accounts'>Contact Accounts</h2>\n";
		
		// Render each of the account invoices
		
		Table()->AccountTable->SetHeader("Account ID", "Business Name", "Trading Name", "Overdue Charges", "&nbsp;", "&nbsp;", "&nbsp;");
		Table()->AccountTable->SetWidth("10%", "11%", "10%", "11%", "10%", "10%", "15%");
		Table()->AccountTable->SetAlignment("Left", "Left", "Left", "Left", "Center", "Center", "Center");
		
		foreach (DBL()->Account as $dboAccount)
		{
			// build the "View pdf" link
			//$intDate = strtotime("-1 month", strtotime($dboInvoice->CreatedOn->Value));
			//$intYear = (int)date("Y", $intDate);
			//$intMonth = (int)date("m", $intDate);
			//$strPath = "/home/vixen_invoices/$intYear/$intMonth/{$dboInvoice->Account->Value}_*";
			//$arrFiles = glob($strPath);
			//if ($arrFiles[0])
			//{
			//	$strPdfHref = Href()->ViewInvoicePdf($dboInvoice->Account->Value, $intMonth, $intYear);
			//	$strPdfLabel = "<span class='DefaultOutputSpan Default'><a href='$strPdfHref'><img src='img/template/pdf.png' title='View PDF Invoice' /></a></span>";
			//}
			
			// build the "View Account Details" link
			$strViewAccountHref = Href()->ViewAccount($dboAccount->Id->Value);
			$strViewAccountLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewAccountHref'>".'View Account'."</a></span>";
			
			// build the "View Invoice And Payments Details" link
			$strViewInvoiceAndPaymentsHref = Href()->InvoicesAndPayments($dboAccount->Id->Value);
			$strViewInvoiceAndPaymentsLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewInvoiceAndPaymentsHref'>".'View Invoices & Payments'."</a></span>";
			
			//build the "View Notes Details" link
			$strViewNotesHref = Href()->ViewNotes($dboAccount->Id->Value);
			$strViewNotesLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewNotesHref'>".'View Notes'."</a></span>";
			
			//build Email Invoice link
			//$strEmailHref = Href()->EmailPDFInvoice($dboInvoice->Account->Value, $intYear, $intMonth);
			//$strEmailLabel = "<span class='DefaultOutputSpan Default'><a href='$strEmailHref'><img src='img/template/email.png' title='Email PDF Invoice' /></a></span>";
			
			// calculate Invoice Amount
			//$dboInvoice->Amount = $dboInvoice->Total->Value + $dboInvoice->Tax->Value;
			// calculate AppliedAmount
			//$dboInvoice->AppliedAmount = $dboInvoice->Amount->Value - $dboInvoice->Balance->Value;
			
			// Add this row to Invoice table
			Table()->AccountTable->AddRow($dboAccount->Id->AsValue(),
											$dboAccount->BusinessName->AsValue(), 
											$dboAccount->TradingName->AsValue(),
											$dboAccount->Overdue->AsValue(),
											$strViewNotesLabel, $strViewAccountLabel, $strViewInvoiceAndPaymentsLabel
											//$dboInvoice->->AsValue(), 
											//$dboInvoice->Balance->AsValue(), 
											//$dboInvoice->Status->AsCallback("GetConstantDescription", Array("InvoiceStatus")), 
											//$strPdfLabel,
											//$strViewInvoiceLabel,
											//$strEmailLabel);
											);
			// Set the drop down detail
			/*$strDetailHtml = "<div class='VixenTableDetail'>\n";
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
		*/
		}
		//Table()->InvoiceTable->LinkTable("PaymentTable", "InvoiceRun");
		//Table()->InvoiceTable->LinkTable("AdjustmentTable", "InvoiceRun");
		
		Table()->AccountTable->RowHighlighting = TRUE;
		
		Table()->AccountTable->Render();
		
		//DBL()->Account->ShowInfo();
		
		//echo "</div>\n";
		//echo "<div class='Seperator'></div>\n";
		
		echo "<div class='Seperator'></div>\n";
	

	}

	//------------------------------------------------------------------------//
	// _RenderLedgerDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderLedgerDetail()
	 *
	 * Render this HTML Template with ledger detail
	 *
	 * Render this HTML Template with ledger detail
	 *
	 * @method
	 */
	private function _RenderLedgerDetail()
	{
		echo "<h2 class='Contact'>Contact Accounts</h2>\n";
		
		//EXAMPLE:
		/*
		echo "<div class='NarrowContent'>\n";
		
		// Declare the start of the form
		$this->FormStart('AccountDetails', 'Account', 'InvoicesAndPayments');
		
		// Render the Id of the Account as a hidden input
		DBO()->Account->Id->RenderHidden();

		// Render the details of the Account
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		DBO()->Account->Balance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		DBO()->Account->TotalUnbilledAdjustments->RenderOutput();


		// Render the properties that can be changed
		DBO()->Account->DisableDDR->RenderInput();
		DBO()->Account->DisableLatePayment->RenderInput();
		
		// Render the submit button
		echo "<div class='Right'>\n";
		$this->Submit("Apply Changes");
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		echo "<div class='Seperator'></div>\n";
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		
		// Declare the end of the form
		$this->FormEnd();
		*/
	}
}

?>
