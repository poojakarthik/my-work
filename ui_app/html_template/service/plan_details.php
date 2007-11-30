<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServicePlanDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServicePlanDetails
 *
 * A specific HTML Template object
 *
 * A Plan details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServicePlanDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateServicePlanDetails extends HtmlTemplate
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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
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
			case HTML_CONTEXT_CURRENT_PLAN:
				// Render the plan details as the current plan
				if (DBO()->{FutureRatePlan}->Id->Value)
				{
					echo "<h2 class='plan'>Current Plan</h2>\n";
				}
				else
				{
					echo "<h2 class='plan'>Plan Details</h2>\n";
				}
				$this->_RenderDetails("CurrentRatePlan");
				break;
				
			case HTML_CONTEXT_FUTURE_PLAN:
				// Render the plan details as the future plan
				echo "<h2 class='plan'>Future Scheduled Plan</h2>\n";
				$this->_RenderDetails("FutureRatePlan");
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
	private function _RenderDetails($strRatePlan)
	{
		echo "<div class='NarrowContent'>\n";
		
		// Load the details of the $strRatePlan object into the DBO()->RatePlan object
		// Trust me, this is easier than defining a bunch of stuff in the UIAppDocumentation, and then refering to contexts that don't mean anything
		$dboRatePlan = new DBObject("RatePlan");
		foreach (DBO()->{$strRatePlan} as $strProperty=>$objProperty)
		{
			$dboRatePlan->$strProperty = $objProperty->Value;
		}
		
		if ($dboRatePlan->Id->Value)
		{
			// Build a link to the Rate Plan summary (not the one specific to this service)
			$strPlanSummaryHref = Href()->ViewPlan($dboRatePlan->Id->Value);
			$strPlanSummaryLink = "<a href='$strPlanSummaryHref' title='View Plan Details'>{$dboRatePlan->Name->Value}</a>";
		
			$dboRatePlan->Name->RenderArbitrary($strPlanSummaryLink, RENDER_OUTPUT);
			$dboRatePlan->Description->RenderOutput();
			$dboRatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
			$dboRatePlan->Shared->RenderOutput();
			$dboRatePlan->MinMonthly->RenderOutput();
			$dboRatePlan->ChargeCap->RenderOutput();
			$dboRatePlan->UsageCap->RenderOutput();
			
			if ($dboRatePlan->StartDatetime->IsSet)
			{
				$dboRatePlan->StartDatetime->RenderOutput();
			}
			
			if ($dboRatePlan->EndDatetime->IsSet)
			{
				$dboRatePlan->EndDatetime->RenderOutput();
			}
		}
		else
		{
			if ($this->_intContext == HTML_CONTEXT_CURRENT_PLAN)
			{
				echo "<span>This service does not currently have a plan</span>";
			}
		}
		
		echo "</div>\n";  // NarrowContent
		
		echo "<div class='Seperator'></div>\n";
	}	
}

?>
