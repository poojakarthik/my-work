<?php
//----------------------------------------------------------------------------//
// HtmlTemplatePlanDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanDetails
 *
 * A specific HTML Template object
 *
 * A Plan details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanDetails extends HtmlTemplate
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
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
		echo "<h2 class='plan'>Plan Details</h2>\n";
		
		echo "<div class='GroupedContent'>\n";
		
		// Handle the Archived property
		if (DBO()->RatePlan->Archived->Value)
		{
			if (DBO()->RatePlan->Archived->Value == RATE_STATUS_DRAFT)
			{
				// The plan is currently saved as a draft
				echo "<div style='color:#FF0000;text-align:center'>This plan is currently saved as a draft.  It must be committed before it can be applied to services.</div>\n";
			}
			else
			{
				// The plan must be archived
				echo "<div style='color:#FF0000;text-align:center'>This plan has been archived.  It cannot be applied to services.</div>\n";
			}
			echo "<div class='ContentSeparator'></div>\n";
		}
		
		DBO()->RatePlan->Name->RenderOutput();
		DBO()->RatePlan->Description->RenderOutput();
		
		echo "<div class='ContentSeparator' ></div>\n";
		echo "<table border='0' cellspacing='0' cellpadding='0' width='100%'><tr>\n";
		echo "<td width='50%'>\n";
		DBO()->RatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT);	
		
		$intFullService = DBO()->RatePlan->CarrierFullService->Value;
		if (!isset($GLOBALS['*arrConstant']['Carrier'][$intFullService]))
		{
			$strFullService = "[Not Specified]";
		}
		else
		{
			$strFullService = $GLOBALS['*arrConstant']['Carrier'][$intFullService]['Description'];
		}
		DBO()->RatePlan->CarrierFullService->RenderArbitrary($strFullService, RENDER_OUTPUT);
		
		$intPreselection = DBO()->RatePlan->CarrierPreselection->Value;
		if (!isset($GLOBALS['*arrConstant']['Carrier'][$intPreselection]))
		{
			$strPreselection = "[Not Specified]";
		}
		else
		{
			$strPreselection = $GLOBALS['*arrConstant']['Carrier'][$intPreselection]['Description'];
		}
		DBO()->RatePlan->CarrierPreselection->RenderArbitrary($strPreselection, RENDER_OUTPUT);
		DBO()->RatePlan->Shared->RenderOutput();
		DBO()->RatePlan->InAdvance->RenderOutput();
		if (DBO()->RatePlan->ContractTerm->Value == NULL)
		{
			// There is no contract term
			DBO()->RatePlan->ContractTerm->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		else
		{
			DBO()->RatePlan->ContractTerm->RenderOutput();
		}
		echo "</td><td width='50%'>\n";
		DBO()->RatePlan->CustomerGroup = DBO()->RatePlan->customer_group->Value;
		DBO()->RatePlan->CustomerGroup->RenderCallback("GetConstantDescription", Array("CustomerGroup"), RENDER_OUTPUT);
		DBO()->RatePlan->MinMonthly->RenderOutput();
		DBO()->RatePlan->ChargeCap->RenderOutput();
		DBO()->RatePlan->UsageCap->RenderOutput();
		DBO()->RatePlan->RecurringCharge->RenderOutput();
		
		if (DBO()->RatePlan->discount_cap->Value == NULL)
		{
			DBO()->RatePlan->discount_cap->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		else
		{
			DBO()->RatePlan->discount_cap->RenderOutput();
		}
		
		echo "</td></tr></table>\n";
		
		echo "</div>\n";  // GroupedContent
		
		echo "<div class='SmallSeperator'></div>\n";
	}

}

?>
