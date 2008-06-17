<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServiceAccount
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceAccount
 *
 * A specific HTML Template object
 *
 * An service account HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateserviceaccount
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceAccount extends HtmlTemplate
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
		echo "<!-- Actual Service Declared : ". DBO()->ActualRequestedService->Id->Value ." -->\n";
		echo "<h2 class='account'>Account Details</h2>\n";
		echo "<div class='GroupedContent'>\n";
		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		else
		{
			DBO()->Account->BusinessName->RenderArbitrary("[Not Specified]", RENDER_OUTPUT);
		}
		
		echo "</div>\n";
		echo "<div class='SmallSeperator'></div>\n";
	}
}

?>
