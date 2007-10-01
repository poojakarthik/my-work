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
			case HTML_CONTEXT_FULL_DETAIL:
				$this->_RenderFullDetail();
				break;
			case HTML_CONTEXT_RATE_DETAIL:
				$this->_RenderRateDetail();
				break;				
		}
	}

	//------------------------------------------------------------------------//
	// _RenderRateDetail
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
		echo "<div class='NarrowForm'>\n";
		
		DBO()->RatePlan->Name->RenderOutput();
		DBO()->RatePlan->Description->RenderOutput();
		DBO()->RatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		DBO()->RatePlan->Archived->RenderOutput();
		DBO()->RatePlan->ChargeCap->RenderOutput();
		DBO()->RatePlan->UsageCap->RenderOutput();
		DBO()->RatePlan->MinMonthly->RenderOutput();
		DBO()->RatePlan->Shared->RenderOutput();

		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
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
		echo "<h2 class='plan'>Plan Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		
		$mixServicePlan = GetCurrentPlan(DBO()->Service->Id->Value)	;
		if ($mixServicePlan === FALSE)
		{
			echo "this service does not currently have a plan\n";
		}
		else
		{
			DBO()->RatePlan->Id = $mixServicePlan;
			DBO()->RatePlan->Load();
			DBO()->RatePlan->Name->RenderOutput();
			DBO()->RatePlan->Description->RenderOutput();
			DBO()->RatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
			DBO()->RatePlan->Shared->RenderOutput();
			DBO()->RatePlan->MinMonthly->RenderOutput();
			DBO()->RatePlan->ChargeCap->RenderOutput();
			DBO()->RatePlan->UsageCap->RenderOutput();
		}
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}	
}

?>
