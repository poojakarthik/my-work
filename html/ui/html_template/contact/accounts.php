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
		$this->_RenderFullDetail();
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
			// build the "View Account Details" link
			$strViewAccountHref = Href()->ViewAccount($dboAccount->Id->Value);
			$strViewAccountLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewAccountHref'>".'View Account'."</a></span>";
			
			// build the "View Invoice And Payments Details" link
			$strViewInvoiceAndPaymentsHref = Href()->InvoicesAndPayments($dboAccount->Id->Value);
			$strViewInvoiceAndPaymentsLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewInvoiceAndPaymentsHref'>".'View Invoices & Payments'."</a></span>";
			
			//build the "View Notes Details" link
			$strViewNotesHref = Href()->ViewNotes($dboAccount->Id->Value);
			$strViewNotesLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewNotesHref'>".'View Notes'."</a></span>";
			
			// Add this row to Invoice table
			Table()->AccountTable->AddRow($dboAccount->Id->AsValue(),
											$dboAccount->BusinessName->AsValue(), 
											$dboAccount->TradingName->AsValue(),
											$dboAccount->Overdue->AsValue(),
											$strViewNotesLabel, $strViewAccountLabel, $strViewInvoiceAndPaymentsLabel
											);
		}
		Table()->AccountTable->RowHighlighting = TRUE;
		Table()->AccountTable->Render();
		
		echo "<div class='Seperator'></div>\n";
	}
}

?>
