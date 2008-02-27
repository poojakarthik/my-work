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
				echo "<span class='Red'><center>This plan is currently saved as a draft.  It must be committed before it can be applied to services.</center></span>\n";
			}
			else
			{
				// The plan must be archived
				echo "<span class='Red'><center>This plan has been archived.  It cannot be applied to services.</center></span>\n";
			}
			echo "<div class='ContentSeparator'></div>\n";
		}
		
		DBO()->RatePlan->Name->RenderOutput();
		DBO()->RatePlan->Description->RenderOutput();
		DBO()->RatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		DBO()->RatePlan->Shared->RenderOutput();
		DBO()->RatePlan->InAdvance->RenderOutput();
		DBO()->RatePlan->MinMonthly->RenderOutput();
		DBO()->RatePlan->ChargeCap->RenderOutput();
		DBO()->RatePlan->UsageCap->RenderOutput();
		
		echo "</div>\n";  // GroupedContent
		
		echo "<div class='SmallSeperator'></div>\n";
	}

	//------------------------------------------------------------------------//
	// _RenderRateDetail (HTML_CONTEXT_RATE_DETAIL) (DEPRICATED)
	//------------------------------------------------------------------------//
	/**
	 * _RenderRateDetail()
	 *
	 * Render this HTML Template with Rate detail
	 *
	 * Render this HTML Template with Rate detail
	 *
	 * @method
	 */
	private function _RenderRateDetail()
	{
		echo "<h2 class='plan'>Plan Details</h2>\n";
		echo "<div class='GroupedContent'>\n";
		
		DBO()->RatePlan->Name->RenderOutput();
		DBO()->RatePlan->Description->RenderOutput();
		DBO()->RatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		DBO()->RatePlan->Archived->RenderOutput();
		DBO()->RatePlan->ChargeCap->RenderOutput();
		DBO()->RatePlan->UsageCap->RenderOutput();
		DBO()->RatePlan->MinMonthly->RenderOutput();
		DBO()->RatePlan->Shared->RenderOutput();
		DBO()->RatePlan->InAdvance->RenderOutput();

		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}	

	//------------------------------------------------------------------------//
	// _RenderFullDetail (HTML_CONTEXT_FULL_DETAIL) (DEPRICATED)
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
		echo "<h2 class='plan'>Plan Details</h2>\n";
		echo "<div class='NarrowContent'>\n";
		
		if (DBO()->RatePlan->Id->Value)
		{
			DBO()->RatePlan->Name->RenderOutput();
			DBO()->RatePlan->Description->RenderOutput();
			DBO()->RatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
			DBO()->RatePlan->Shared->RenderOutput();
			DBO()->RatePlan->InAdvance->RenderOutput();
			DBO()->RatePlan->MinMonthly->RenderOutput();
			DBO()->RatePlan->ChargeCap->RenderOutput();
			DBO()->RatePlan->UsageCap->RenderOutput();
			
			if (DBO()->RatePlan->StartDatetime->IsSet)
			{
				DBO()->RatePlan->StartDatetime->RenderOutput();
			}
			
			if (DBO()->RatePlan->EndDatetime->IsSet)
			{
				DBO()->RatePlan->EndDatetime->RenderOutput();
			}
		}
		else
		{
			echo "This service does not currently have a plan\n";
		}
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}	
}

?>
