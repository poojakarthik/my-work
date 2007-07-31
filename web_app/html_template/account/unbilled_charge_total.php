<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountUnbilledChargeTotal
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountUnbilledChargeTotal
 *
 * HTML Template object for the client app, Total Unbilled charges for account
 *
 * HTML Template object for the client app, Total Unbilled charges for account
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateAccountUnbilledChargeTotal
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountUnbilledChargeTotal extends HtmlTemplate
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
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
		//$this->LoadJavascript("tooltip");
		
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
		echo "<h2 class='Adjustment'>Unbilled Charges for Account# ". DBO()->Account->Id->Value ."</h2>\n";
		
		// Display the details of the nominated account
		//echo "<h2 class='Account'>Account Details</h2>\n";
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		if (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		
		// Display the current unbilled Total for this account (unbilled charges + unbilled CDRs)
		DBO()->Account->CurrentUnbilledTotal->RenderOutput();
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
