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
class HtmlTemplateServiceEdit extends HtmlTemplate
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
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='Narrow-Form'>\n";
		// Set Up the form for editting an existing user
		$this->FormStart("EditService", "Service", "Edit");
		DBO()->Service->Id->RenderHidden();
		DBO()->Service->ServiceType->RenderHidden();
		DBO()->Service->ClosedOn->RenderHidden();
		DBO()->Service->CreatedOn->RenderHidden();
		DBO()->Service->CurrentFNN->RenderHidden();
		
		DBO()->Service->Id->RenderOutput();
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		DBO()->Service->FNN->RenderInput();
		DBO()->Service->FNNConfirm->RenderInput();
		
		if((DBO()->Service->ClosedOn->Value == NULL) || (DBO()->Service->ClosedOn->Value > GetCurrentDateForMySQL()))
		{
			echo "&nbsp;This service opens on: ".DBO()->Service->CreatedOn->FormattedValue()."\n";
		}
		else
		{
			echo "&nbsp;&nbsp;This service closed on: ".DBO()->Service->ClosedOn->FormattedValue()."\n";
		}
		
		DBO()->Service->Archive->RenderInput();
	
		// Render the status message, if there is one
		DBO()->Status->Message->RenderOutput();

		/*DBO()->Service->Indial100->RenderOutput();
		if (DBO()->Service->Indial100->Value)
		{
			// only render the Extensive Level Billing boolean, if the service is an Indial100
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
		}*/
		
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Apply Changes");
		echo "</div>\n";
		$this->FormEnd();
		echo "<div class='Seperator'></div>\n";		
	}
}

?>
