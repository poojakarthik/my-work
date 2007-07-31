<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServiceCDRList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceCDRList
 *
 * HTML Template object for the client app, Displays a paginated list of CDRs for a given service
 *
 * HTML Template object for the client app, Displays a paginated list of CDRs for a given service
 *
 *
 * @prefix	<prefix>
 *
 * @package	web_app
 * @class	HtmlTemplateServiceCDRList
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceCDRList extends HtmlTemplate
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
		echo "<h2 class='CDR'>Call Information</h2>\n";
		//TODO!
		
		Table()->Services->SetHeader("FNN", "Service Type", "Current Plan", "Current Unbilled Charges (inc GST)", "&nbsp;");
		Table()->Services->SetWidth("10%", "15%", "35%", "15%", "25%");
		Table()->Services->SetAlignment("left", "left", "left", "right", "left");
		
		// add the rows
		foreach (DBL()->Service as $dboService)
		{
			// Find the current plan for the service
			$mixCurrentPlan = GetCurrentPlan($dboService->Id->Value);
			if ($mixCurrentPlan === FALSE)
			{
				// There is no current plan for this service
				DBO()->RatePlan->Name = "No Current Plan";
			}
			else
			{
				// a plan was found
				DBO()->RatePlan->Id = $mixCurrentPlan;
				DBO()->RatePlan->Load();
			}
			
			// Calculate the total unbilled charges for this service (inc GST)
			$dboService->TotalUnbilled = AddGST(UnbilledServiceCDRTotal($dboService->Id->Value) + UnbilledServiceChargeTotal($dboService->Id->Value));

			// build the "View Unbilled Charges for Service" link
			$strViewUnbilledCharges = Href()->ViewUnbilledChargesForService($dboService->Id->Value);
			$strViewUnbilledChargesLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewUnbilledCharges'>View Unbilled Charges</a></span>";


			Table()->Services->AddRow($dboService->FNN->AsValue(),
										$dboService->ServiceType->AsCallback("GetConstantDescription", Array("ServiceType")),
										DBO()->RatePlan->Name->AsValue(),
										$dboService->TotalUnbilled->AsValue(),
										$strViewUnbilledChargesLabel);
		}
		
		Table()->Services->Render();
		
		echo "<div class='Seperator'></div>\n";
		
		echo "</div>\n";
	}
}

?>
