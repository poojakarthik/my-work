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
		
		$intClosedOn = ConvertMySQLDateToUnixTimeStamp(DBO()->Service->ClosedOn->Value);
		$intCurrentDate = ConvertMySQLDateToUnixTimeStamp(GetCurrentDateForMySQL());
		
		$intClosedOn = ConvertMySQLDateToUnixTimeStamp(DBO()->Service->ClosedOn->Value);
		$intTodaysDate = time();
		
		// Check if the closedon date has been set i.e. not null
		if (DBO()->Service->ClosedOn->Value == NULL)
		{
			// The service is not scheduled to close it is either active or hasn't been activated yet
			// Check if it is currently active
			$intCreatedOn = ConvertMySQLDateToUnixTimeStamp(DBO()->Service->CreatedOn->Value);
			if ($intTodaysDate > $intCreatedOn)
			{
				// The service is currently active
				echo "This service opened on: ".DBO()->Service->CreatedOn->FormattedValue()."<br>";
				DBO()->Service->ArchiveService->RenderInput();
				// We want the checkbox action to be "archive this service"
			}
			else
			{
				// This service hasn't yet been activated
				echo "This service will be actived on: ".DBO()->Service->CreatedOn->FormattedValue()."<br>";
				DBO()->Service->ArchiveService->RenderInput();
				// We want the checkbox action to be "archive this service"
			}
		}
		else
		{
			// The service has a closedon date check if it is in the future or past
			if ($intClosedOn <= $intTodaysDate)
			{
				// The service has been closed
				echo "This service was closed on: ".DBO()->Service->ClosedOn->FormattedValue()."<br>";
				DBO()->Service->ActivateService->RenderInput();
				// We want the checkbox action to be "activate this service"
			}
			else
			{
				// The service is scheduled to be closed in the future
				echo "This service is scheduled to be closed on: ".DBO()->Service->ClosedOn->FormattedValue()."<br>";
				DBO()->Service->CancelScheduledClosure->RenderInput();
				// We want the checkbox action to be "cancel scheduled closure"
			}
		}
	
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
