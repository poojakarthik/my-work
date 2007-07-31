<?php
//----------------------------------------------------------------------------//
// HtmlTemplateAccountServiceList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountServiceList
 *
 * HTML Template object for the client app, List of all Services for account
 *
 * HTML Template object for the client app, List of all Services for account
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateAccountServiceList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountServiceList extends HtmlTemplate
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
		echo "<h2 class='Services'>Services</h2>\n";
		
		Table()->Services->SetHeader("FNN", "Service Type", "Current Plan", "Current Unbilled Charges (inc GST)");
		Table()->Services->SetWidth("15%", "20%", "35%", "30%");
		Table()->Services->SetAlignment("left", "left", "left", "right");
		
		// add the rows
		foreach (DBL()->Service as $dboService)
		{
			// Find the current plan for the service
			$mixCurrentPlan = GetCurrentPlan($dboService->Id->Value);
			if ($mixCurrentPlan === FALSE)
			{
				// There is no current plan for this service
				//TODO! do something to error trap this scenario
			}
			else
			{
				// a plan was found
				DBO()->RatePlan->Id = $mixCurrentPlan;
				DBO()->RatePlan->Load();
			}
			
			// Calculate the total unbilled charges for this service (inc GST)
			$dboService->TotalUnbilled = AddGST(UnbilledServiceCDRTotal($dboService->Id->Value) + UnbilledServiceChargeTotal($dboService->Id->Value));
			
			Table()->Services->AddRow($dboService->FNN->AsValue(),
										$dboService->ServiceType->AsCallback("GetConstantDescription", Array("ServiceType")),
										DBO()->RatePlan->Name->AsValue(),
										$dboService->TotalUnbilled->AsValue());
		}
		
		Table()->Services->Render();
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
