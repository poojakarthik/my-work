<?php
//----------------------------------------------------------------------------//
// HtmlTemplateContactDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateContactDetails
 *
 * A specific HTML Template object
 *
 * An Contact Details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateContactDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateContactDetails extends HtmlTemplate
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
		echo "<h2 class='Contact'>Contact Details</h2>\n";
		
		$strFullName = DBO()->Contact->Title->Value ." ". DBO()->Contact->FirstName->Value ." ". DBO()->Contact->LastName->Value;
		DBO()->Contact->FullName = $strFullName;
		
		echo "<div class='Narrow-Form'>\n";
			DBO()->Contact->FullName->RenderOutput();
			DBO()->Contact->JobTitle->RenderOutput();
			DBO()->Contact->DOB->RenderOutput();
			DBO()->Contact->Email->RenderOutput();
			DBO()->Contact->Phone->RenderOutput();
			DBO()->Contact->Mobile->RenderOutput();
			DBO()->Contact->UserName->RenderOutput();
			DBO()->Contact->CustomerContact->RenderOutput();
			DBO()->Contact->Archived->RenderOutput();
echo "</div>\n";
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
		echo "<h2 class='Contact'>Contact Details</h2>\n";
		
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
