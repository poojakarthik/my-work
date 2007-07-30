<?php
//----------------------------------------------------------------------------//
// HtmlTemplateservicedetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateservicedetails
 *
 * A specific HTML Template object
 *
 * An service details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateservicedetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateservicedetails extends HtmlTemplate
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
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='Narrow-Form'>\n";
		DBO()->Service->Id->RenderOutput();
		DBO()->Service->FNN->RenderOutput();	
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		DBO()->Service->Indial100->RenderOutput();
		if (DBO()->Service->Indial100->Value)
		{
			// only render the Extensive Level Billing boolean, if the service is an Indial100
			DBO()->Service->ELB->RenderOutput();
		}
		DBO()->Service->CreatedOn->RenderOutput();
		DBO()->Service->ClosedOn->RenderOutput();
		DBO()->Service->TotalUnbilledCharges->RenderOutput();
		//Only display the current rate plan if there is one
		if (DBO()->RatePlan->Id->Value !== FALSE)
		{
			DBO()->RatePlan->Name->RenderOutput(1);
		}
		else
		{
			DBO()->RatePlan->Name->RenderArbitrary("No Plan", RENDER_OUTPUT, 1);
		}
		
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
		echo "<h2 class='service'>service details</h2>\n";
		
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
