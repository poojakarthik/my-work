<?php
//----------------------------------------------------------------------------//
// HtmlTemplateservicedetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateservicedetails
 *
 * A specific HTML Template object
 *
 * An service details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateservicedetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceDetails extends HtmlTemplate
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
			case HTML_CONTEXT_MINIMUM_DETAIL:
				$this->_RenderMinimumDetail();
				break;
			case HTML_CONTEXT_FULL_DETAIL:
				$this->_RenderFullDetail();
				break;
			case HTML_CONTEXT_BARE_DETAIL:
				$this->_RenderBareDetail();
				break;				
			default:
				$this->_RenderFullDetail();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderBareDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderBareDetail()
	 *
	 * Render this HTML Template with bare service detail
	 *
	 * Render this HTML Template with bare service detail
	 *
	 * @method
	 */
	private function _RenderBareDetail()
	{
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		DBO()->Account->Id->RenderOutput();
		DBO()->Service->FNN->RenderOutput();
		DBO()->Service->LineStatus->RenderCallback("GetConstantDescription", Array("Service"), RENDER_OUTPUT);
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";	
	}

	//------------------------------------------------------------------------//
	// _RenderMinimumDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderMinimumDetail()
	 *
	 * Render this HTML Template with minimum service detail
	 *
	 * Render this HTML Template with minimum service detail
	 *
	 * @method
	 */
	private function _RenderMinimumDetail()
	{
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		DBO()->Service->Id->RenderOutput();
		DBO()->Service->FNN->RenderOutput();
		DBO()->Service->LineStatus->RenderCallback("GetConstantDescription", Array("Service"), RENDER_OUTPUT);		
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
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		DBO()->Service->Id->RenderOutput();
		DBO()->Service->FNN->RenderOutput();	
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		
	
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
		{
			DBO()->Service->Indial100->RenderOutput();
			DBO()->Service->ELB->RenderOutput();
		}
		
		DBO()->Service->CreatedOn->RenderOutput();
		DBO()->Service->ClosedOn->RenderOutput();
		DBO()->Service->TotalUnbilledCharges->RenderOutput();
		//Only display the current rate plan if there is one
		if (DBO()->RatePlan->Id->Value !== FALSE)
		{
			DBO()->RatePlan->Name->RenderOutput(1);
		}
		else
		{
			DBO()->RatePlan->Name->RenderArbitrary("No Plan", RENDER_OUTPUT, 1);
		}
		
		DBO()->Service->LineStatus->RenderCallback("GetConstantDescription", Array("Service"), RENDER_OUTPUT);

		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
}

?>
