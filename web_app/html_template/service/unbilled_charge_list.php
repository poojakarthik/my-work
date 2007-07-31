<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServiceUnbilledChargeList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceUnbilledChargeList
 *
 * HTML Template object for the client app, List of all Unbilled charges for Service
 *
 * HTML Template object for the client app, List of all Unbilled charges for Service
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateServiceUnbilledChargeList
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceUnbilledChargeList extends HtmlTemplate
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
		echo "<h2 class='Adjustment'>Unbilled Adjustments</h2>\n";
				
		Table()->Adjustments->SetHeader("Date", "Code", "Description", "Nature", "Amount (inc GST)");
		Table()->Adjustments->SetWidth("10%", "15%", "45%", "10%", "20%");
		Table()->Adjustments->SetAlignment("left", "left", "left", "left", "right");
		
		// add the rows
		foreach (DBL()->Charge as $dboCharge)
		{
			Table()->Adjustments->AddRow($dboCharge->CreatedOn->AsValue(),
											$dboCharge->ChargeType->AsValue(),
											$dboCharge->Description->AsValue(),
											$dboCharge->Nature->AsValue(),
											$dboCharge->Amount->AsCallback("AddGST"));
		}
				
		Table()->Adjustments->Render();
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
