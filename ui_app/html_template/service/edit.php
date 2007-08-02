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


		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_ADSL)
		{
			
			DBL()->CostCentre->Account = DBO()->Service->Account->Value;
			DBL()->CostCentre->Load();
			
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Cost Centre:</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select name='Service.CostCentre' style='width:150px'>\n";
			
			if (DBO()->Service->CostCentre->Value == NULL)
			{
				echo "	<option value='0' selected='selected'>&nbsp;</option>";
			}
			else
			{
				echo "	<option value='0'>&nbsp;</option>";				
			}
			
			foreach (DBL()->CostCentre as $dboCostCentre)
			{
				if (DBO()->Service->CostCentre->Value == $dboCostCentre->Id->Value)
				{
					echo "<option value='".$dboCostCentre->Id->Value."' selected='selected'>".$dboCostCentre->Name->Value."</option>";
				}
				else
				{
					echo "<option value='".$dboCostCentre->Id->Value."'>".$dboCostCentre->Name->Value."</option>";
				}
			}
			
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
		}

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
				echo "&nbsp;&nbsp;This service opened on: ".DBO()->Service->CreatedOn->FormattedValue()."<br>";
				DBO()->Service->ArchiveService->RenderInput();
				// We want the checkbox action to be "archive this service"
			}
			else
			{
				// This service hasn't yet been activated
				echo "&nbsp;&nbsp;This service will be activated on: ".DBO()->Service->CreatedOn->FormattedValue()."<br>";
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
				echo "&nbsp;&nbsp;This service was closed on: ".DBO()->Service->ClosedOn->FormattedValue()."<br>";
				DBO()->Service->ActivateService->RenderInput();
				// We want the checkbox action to be "activate this service"
			}
			else
			{
				// The service is scheduled to be closed in the future
				echo "&nbsp;&nbsp;This service is scheduled to be closed on: ".DBO()->Service->ClosedOn->FormattedValue()."<br>";
				DBO()->Service->CancelScheduledClosure->RenderInput();
				// We want the checkbox action to be "cancel scheduled closure"
			}
		}
	
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			echo "<div class='Seperator'></div>\n";
			echo "<h2 class='service'>Inbound Details</h2>\n";
			
			DBL()->ServiceInboundDetail->Service = DBO()->Service->Id->Value;
			DBL()->ServiceInboundDetail->Load();
			
			foreach (DBL()->ServiceInboundDetail as $dboServiceInboundDetail)
			{
				$dboServiceInboundDetail->AnswerPoint->RenderInput();
				$dboServiceInboundDetail->Configuration->RenderInput();
			}
		}
		
		
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			DBL()->ServiceMobileDetail->Service = DBO()->Service->Id->Value;
			DBL()->ServiceMobileDetail->Load();
		
			// note it is assumed that we will only retrieve one record
			foreach (DBL()->ServiceMobileDetail as $dboServiceMobileDetail)
			{
				echo "<div class='Seperator'></div>\n";
				echo "<h2 class='service'>Mobile Details</h2>\n";
				$dboServiceMobileDetail->SimPUK->RenderInput();
				$dboServiceMobileDetail->SimESN->RenderInput();
								
				$arrState = array();
				$arrState[SERVICE_STATE_TYPE_ACT] = "Australian Capital Territory";
				$arrState['NSW'] = "New South Wales";
				$arrState['VIC'] = "Victoria";
				$arrState['SA'] = "South Australia";
				$arrState['WA'] = "Western Australia";
				$arrState['TAS'] = "Tasmania";
				$arrState['NT'] = "Northern Territory";
				$arrState['QLD'] = "Queensland";
				
				echo "<div class='DefaultElement'>\n";
				echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State:</div>\n";
				echo "   <div class='DefaultOutput'>\n";
				echo "      <select name='ServiceMobileDetail.SimState' style='width:180px'>\n";
			
				foreach ($arrState as $strKey=>$strStateSelection)
				{
					if ($dboServiceMobileDetail->SimState->Value == $strKey)
					{
						// this is the currently selected combobox option
						echo "		<option value='". $strKey . "' selected='selected'>$strStateSelection</option>\n";
					}
					else
					{
						// this is currently not the selected combobox option
						echo "		<option value='". $strKey . "'>$strStateSelection</option>\n";
					}
				}
				
				echo "      </select>\n";
				echo "   </div>\n";
				echo "</div>\n";
				
				$dboServiceMobileDetail->DOB->RenderInput();				
				$dboServiceMobileDetail->Comments->RenderInput();		
			}
		}
		echo "<div class='Seperator'></div>\n";			
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Apply Changes");
		echo "</div>\n";
		
		$this->FormEnd();
		echo "<div class='Seperator'></div>\n";		
	}
}

?>
