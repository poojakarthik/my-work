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
		echo "<!-- Actual Service Declared : ". DBO()->ActualRequestedService->Id->Value ." -->\n";
		
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_CURRENT_PLAN:
				// Render the plan details as the current plan
				if (DBO()->FutureRatePlan->Id->Value)
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
		echo "<div class='GroupedContent'>\n";
		
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
			$dboRatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT);	
			$dboRatePlan->CustomerGroup = $dboRatePlan->customer_group->Value;
			$dboRatePlan->CustomerGroup->RenderCallback("GetConstantDescription", Array("CustomerGroup"), RENDER_OUTPUT);
			
			$intFullService = $dboRatePlan->CarrierFullService->Value;
			if (!isset($GLOBALS['*arrConstant']['Carrier'][$intFullService]))
			{
				$strFullService = "[Not Specified]";
			}
			else
			{
				$strFullService = $GLOBALS['*arrConstant']['Carrier'][$intFullService]['Description'];
			}
			$dboRatePlan->CarrierFullService->RenderArbitrary($strFullService, RENDER_OUTPUT);
			
			$intPreselection = $dboRatePlan->CarrierPreselection->Value;
			if (!isset($GLOBALS['*arrConstant']['Carrier'][$intPreselection]))
			{
				$strPreselection = "[Not Specified]";
			}
			else
			{
				$strPreselection = $GLOBALS['*arrConstant']['Carrier'][$intPreselection]['Description'];
			}
			$dboRatePlan->CarrierPreselection->RenderArbitrary($strPreselection, RENDER_OUTPUT);
			
			$dboRatePlan->Shared->RenderOutput();
			$dboRatePlan->InAdvance->RenderOutput();
			if ($dboRatePlan->ContractTerm->Value == NULL)
			{
				// There is no contract term
				$dboRatePlan->ContractTerm->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
			else
			{
				$dboRatePlan->ContractTerm->RenderOutput();
			
				// Render Contract Details
				if ((float)$dboRatePlan->contract_exit_fee->Value)
				{
					DBO()->RatePlan->contract_exit_fee->RenderArbitrary('$'.number_format(DBO()->RatePlan->contract_exit_fee->Value, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
				}
				else
				{
					$dboRatePlan->contract_exit_fee->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
				}
				if ((float)$dboRatePlan->contract_payout_percentage->Value)
				{
					// HACKHACKHACK: Shitty way of printing out a nice name
					DBO()->RatePlan->contract_payout	= DBO()->RatePlan->contract_payout_percentage->Value;
					DBO()->RatePlan->contract_payout->RenderArbitrary(number_format(DBO()->RatePlan->contract_payout->Value, 2, '.', '').'%', RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
				}
				else
				{
					$dboRatePlan->contract_payout->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
				}
			}
			
			if ($dboRatePlan->scalable->Value == TRUE)
			{
				// Display the "scalable" details
				$dboRatePlan->scalable->RenderArbitrary("Yes", RENDER_OUTPUT);
				$dboRatePlan->minimum_services->RenderOutput();
				$dboRatePlan->maximum_services->RenderOutput();
			}
			else
			{
				// The plan is not scalable
				$dboRatePlan->scalable->RenderArbitrary("Not Scalable", RENDER_OUTPUT);
			}
			
			$dboRatePlan->MinMonthly->RenderOutput();
			$dboRatePlan->ChargeCap->RenderOutput();
			$dboRatePlan->UsageCap->RenderOutput();
			$dboRatePlan->RecurringCharge->RenderOutput();
			
			if ($dboRatePlan->discount_cap->Value == NULL)
			{
				$dboRatePlan->discount_cap->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
			else
			{
				$dboRatePlan->discount_cap->RenderOutput();
			}
			
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
		
		echo "</div>\n";  // GroupedContent
		
		echo "<div class='SmallSeperator'></div>\n";
	}	
}

?>
