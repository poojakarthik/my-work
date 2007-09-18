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
		echo "<div class='NarrowForm'>\n";
		// Set Up the form for editting an existing user
		$this->FormStart("EditService", "Service", "Edit");
		DBO()->Service->Id->RenderHidden();
		DBO()->Service->ServiceType->RenderHidden();
		DBO()->Service->ClosedOn->RenderHidden();
		DBO()->Service->CreatedOn->RenderHidden();
		DBO()->Service->CurrentFNN->RenderHidden();
		DBO()->Service->Account->RenderHidden();
		DBO()->Service->AccountGroup->RenderHidden();
		DBO()->Service->Indial100->RenderHidden();
		
		DBO()->Service->Id->RenderOutput();
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		DBO()->Service->FNN->RenderInput();
		DBO()->Service->FNNConfirm->RenderInput();
		
		// place holder for service status select HTML element
		// Associate array of service status
		
		$arrServiceStatus = array();
		$arrServiceStatus[SERVICE_ACTIVE] = "Active";
		$arrServiceStatus[SERVICE_DISCONNECTED] = "Service Disconnected";
		
		// Check authentication here, archived is only available to admins only
		AuthenticatedUser()->CheckAuth();
		AuthenticatedUser()->PermissionOrDie($pagePerms);
		if (AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			$arrServiceStatus[SERVICE_ARCHIVED] = "Service Archived";
		}
		
		echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Service Status:</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select name='Service.LineStatus' style='width:162px'>\n";
		
			foreach ($arrServiceStatus as $strKey=>$strServiceStatus)
			{
				if (DBO()->Service->LineStatus->Value == $strKey)
				{
					echo "	<option value='".$strKey."' selected='selected'>$strServiceStatus</option>\n";	
				}
				else
				{
					echo "	<option value='".$strKey."'>$strServiceStatus</option>\n";				
				}
			}
		
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
		
		// ---------------------------------------------------
		
		if (DBO()->Service->Indial100->Value)
		{
			DBO()->Service->ELB->RenderInput();
		}
		
		// load cost centre details
		DBL()->CostCentre->Account = DBO()->Service->Account->Value;
		DBL()->CostCentre->Load();
	
		// Draw a CostCentre combo box, but only if there have been cost centers defined for this Account
		if (DBL()->CostCentre->RecordCount() > 0)
		{
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

		$intClosedOn = strtotime(DBO()->Service->ClosedOn->Value);
		$intCurrentDate = strtotime(GetCurrentDateForMySQL());
		
		// Check if the closedon date has been set i.e. not null
		if (DBO()->Service->ClosedOn->Value == NULL)
		{
			// The service is not scheduled to close it is either active or hasn't been activated yet
			// Check if it is currently active
			$intCreatedOn = strtotime(DBO()->Service->CreatedOn->Value);
			if ($intCurrentDate > $intCreatedOn)
			{
				// The service is currently active
				echo "&nbsp;&nbsp;This service opened on: ".DBO()->Service->CreatedOn->FormattedValue()."<br>";
				// We want the checkbox action to be "archive this service"
				//DBO()->Service->ArchiveService->RenderInput();
			}
			else
			{
				// This service hasn't yet been activated yet
				echo "&nbsp;&nbsp;This service will be activated on: ".DBO()->Service->CreatedOn->FormattedValue()."<br>";
				// We want the checkbox action to be "archive this service"
				//DBO()->Service->ArchiveService->RenderInput();
			}
		}
		else
		{
			// The service has a closedon date check if it is in the future or past
			if ($intClosedOn <= $intCurrentDate)
			{
				// The service has been closed
				echo "&nbsp;&nbsp;This service was closed on: ".DBO()->Service->ClosedOn->FormattedValue()."<br>";
				// We want the checkbox action to be "activate this service"
				//DBO()->Service->ActivateService->RenderInput();
			}
			else
			{
				// The service is scheduled to be closed in the future
				// We dont want the user to cancel the scheduled closure of the service
				echo "&nbsp;&nbsp;This service is scheduled to be closed on: ".DBO()->Service->ClosedOn->FormattedValue()."<br>";
			}
		}
	
		// handle extra inbound phone details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			echo "<div class='Seperator'></div>\n";
			echo "<h2 class='service'>Inbound Details</h2>\n";
			DBO()->ServiceInboundDetail->Id->RenderHidden();
			DBO()->ServiceInboundDetail->AnswerPoint->RenderInput();
			DBO()->ServiceInboundDetail->Configuration->RenderInput();
		}
		
		// handle extra mobile phone details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			echo "<div class='Seperator'></div>\n";
			echo "<h2 class='service'>Mobile Details</h2>\n";
			DBO()->ServiceMobileDetail->Id->RenderHidden();
			DBO()->ServiceMobileDetail->SimPUK->RenderInput();
			DBO()->ServiceMobileDetail->SimESN->RenderInput();
							
			$arrState = array();
			$arrState[SERVICE_STATE_TYPE_ACT] = "Australian Capital Territory";
			$arrState[SERVICE_STATE_TYPE_NSW] = "New South Wales";
			$arrState[SERVICE_STATE_TYPE_VIC] = "Victoria";
			$arrState[SERVICE_STATE_TYPE_SA] = "South Australia";
			$arrState[SERVICE_STATE_TYPE_WA] = "Western Australia";
			$arrState[SERVICE_STATE_TYPE_TAS] = "Tasmania";
			$arrState[SERVICE_STATE_TYPE_NT] = "Northern Territory";
			$arrState[SERVICE_STATE_TYPE_QLD] = "Queensland";
			
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State:</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select name='ServiceMobileDetail.SimState' style='width:152px'>\n";
		
			foreach ($arrState as $strKey=>$strStateSelection)
			{
				if (DBO()->ServiceMobileDetail->SimState->Value == $strKey)
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
			
			DBO()->ServiceMobileDetail->DOB->RenderInput();				
			DBO()->ServiceMobileDetail->Comments->RenderInput();		
		}
		
		echo "</div>";  // NarrowForm
		
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Apply Changes");
		echo "</div>\n";
		
		$this->FormEnd();
	}	
}

?>
