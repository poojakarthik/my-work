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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_SERVICE_ADD:
				$this->_RenderServiceAdd();
				break;
			case HTML_CONTEXT_SERVICE_EDIT:
				$this->_RenderServiceEdit();
				break;
			default:
				echo "ERROR: There is no default render context for HtmlTemplateServiceEdit";
				break;
		}
	}	
	
	//------------------------------------------------------------------------//
	// _RenderServiceAdd
	//------------------------------------------------------------------------//
	/**
	 * _RenderServiceAdd()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */	
	function _RenderServiceAdd()
	{
		// define javascript to be triggered when the ServiceType combo changes value
		$strServiceTypeComboOnChange = 
		"switch (this.value)
		{
			case '". SERVICE_TYPE_MOBILE ."':
				// hide any details not required for a mobile and display the mobile details
				document.getElementById('InboundDetailDiv').style.display='none';
				document.getElementById('MobileDetailDiv').style.display='inline';
				break;
			case '". SERVICE_TYPE_INBOUND ."':
				// hide any details not required for inbound services and show the inbound services details
				document.getElementById('MobileDetailDiv').style.display='none';
				document.getElementById('InboundDetailDiv').style.display='inline';
				break;
			default:
				// hide all extra details
				document.getElementById('MobileDetailDiv').style.display='none';
				document.getElementById('InboundDetailDiv').style.display='none';
				break;
		}";
		
		
		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='Narrow-Form'>\n";
		$this->FormStart("AddService", "Service", "Add");
		
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->AccountGroup->RenderHidden();
		
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Service Type:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='Service.ServiceType' style='width:150px' onchange=\"$strServiceTypeComboOnChange\">\n";
		
		$arrServiceType = array();
		$arrServiceType[SERVICE_TYPE_LAND_LINE] = GetConstantDescription(SERVICE_TYPE_LAND_LINE, "ServiceType");
		$arrServiceType[SERVICE_TYPE_MOBILE] = GetConstantDescription(SERVICE_TYPE_MOBILE, "ServiceType");
		$arrServiceType[SERVICE_TYPE_INBOUND] = GetConstantDescription(SERVICE_TYPE_INBOUND, "ServiceType");
		$arrServiceType[SERVICE_TYPE_ADSL] = GetConstantDescription(SERVICE_TYPE_ADSL, "ServiceType");
		$arrServiceType[SERVICE_TYPE_DIALUP] = GetConstantDescription(SERVICE_TYPE_DIALUP, "ServiceType");
		if (!DBO()->Service->ServiceType->Value)
		{
			// default to landline
			DBO()->Service->ServiceType = SERVICE_TYPE_LAND_LINE;
		}
		
		foreach ($arrServiceType as $strKey=>$strServiceSelection)
		{
			if (DBO()->Service->ServiceType->Value == $strKey)
			{
				echo "		<option value='". $strKey . "' selected='selected'>".$strServiceSelection."</option>\n";
			}
			else
			{
				echo "		<option value='". $strKey . "'>".$strServiceSelection."</option>\n";
			}
		}
		
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
				
		DBO()->Service->FNN->RenderInput();
		DBO()->Service->FNNConfirm->RenderInput();
		
		DBL()->CostCentreCombo->Account = DBO()->Account->Id->Value;
		DBL()->CostCentreCombo->SetTable('CostCentre');
		DBL()->CostCentreCombo->Load();

		if (DBL()->CostCentreCombo->RecordCount() > 0)
		{
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Cost Centre:</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select name='Service.CostCentre' style='width:150px'>\n";
			if (DBO()->Service->CostCentre->Value)
			{
				// the costcentre has been selected
				echo "			<option value='0'>&nbsp;</option>\n";
			}
			else
			{
				// the costcentre hasn't been matched select the default item in the combobox
				echo "			<option value='0' selected='selected'>&nbsp;</option>\n";
			}
			
			foreach (DBL()->CostCentreCombo as $dboCostCentre)
			{
				if (DBO()->Service->CostCentre->Value == $dboCostCentre->Id->Value)
				{
					// this is the currently selected costcentre
					echo "<option value='".$dboCostCentre->Id->Value."' selected='selected'>".$dboCostCentre->Name->Value."</option>";
				}
				else
				{
					// this is not the currently selected costcentre
					echo "<option value='".$dboCostCentre->Id->Value."'>".$dboCostCentre->Name->Value."</option>";
				}
			}
			
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";		
		}
		
		DBO()->Service->Indial100->RenderInput();
		
		// handle extra inbound phone details
		echo "<div id='InboundDetailDiv' style='display:none;'>\n";
		echo "<div class='Seperator'></div>\n";
		echo "<h2 class='service'>Inbound Details</h2>\n";
		DBO()->ServiceInboundDetail->Id->RenderHidden();
		DBO()->ServiceInboundDetail->AnswerPoint->RenderHidden();
		DBO()->ServiceInboundDetail->Configuration->RenderHidden();
		
		DBO()->ServiceInboundDetail->AnswerPoint->RenderInput();
		DBO()->ServiceInboundDetail->Configuration->RenderInput();
		echo "</div>\n";
		
		// handle extra mobile phone details
		//echo "<div id='MobilePhoneDetailsDiv' style='visibility:hidden;'>\n";
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			// show the mobile detail div
			echo "<div id='MobileDetailDiv'>\n";
		}
		else
		{
			// hide the mobile detail div
			echo "<div id='MobileDetailDiv' style='display:none;'>\n";
		}
		echo "<div class='Seperator'></div>\n";
		echo "<h2 class='service'>Mobile Details</h2>\n";
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
		echo "      <select name='ServiceMobileDetail.SimState' style='width:180px'>\n";
	
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
	
		echo "</div>\n";	
		
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Save");
		echo "</div>\n";
		
		$this->FormEnd();
		echo "<div class='Seperator'></div>\n";		
		
		//echo "<script type='text/javascript'>document.getElementById('MobilePhoneDetailsDiv').style.display='inline';</script>\n";
		//document.getElementById('MobilePhoneDetailsDiv').style.visibility='visible';</script>\n";
	}


	//------------------------------------------------------------------------//
	// _RenderServiceEdit
	//------------------------------------------------------------------------//
	/**
	 * _RenderServiceEdit()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function _RenderServiceEdit()
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
		DBO()->Service->Account->RenderHidden();
		
		DBO()->Service->Id->RenderOutput();
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		DBO()->Service->FNN->RenderInput();
		DBO()->Service->FNNConfirm->RenderInput();

		// load cost centre details
		DBL()->CostCentre->Account = DBO()->Service->Account->Value;
		DBL()->CostCentre->Load();
	
		//if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_ADSL)
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

		$intClosedOn = ConvertMySQLDateToUnixTimeStamp(DBO()->Service->ClosedOn->Value);
		$intCurrentDate = ConvertMySQLDateToUnixTimeStamp(GetCurrentDateForMySQL());
		
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
				// We dont want the user to cancel the scheduled closure of the service
			}
		}
	
		// handle extra inbound phone details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			echo "<div class='Seperator'></div>\n";
			echo "<h2 class='service'>Inbound Details</h2>\n";
			DBO()->ServiceInboundDetail->Id->RenderHidden();
			DBO()->ServiceInboundDetail->AnswerPoint->RenderHidden();
			DBO()->ServiceInboundDetail->Configuration->RenderHidden();
			
			DBO()->ServiceInboundDetail->AnswerPoint->RenderInput();
			DBO()->ServiceInboundDetail->Configuration->RenderInput();
		}
		
		// handle extra mobile phone details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			//DBL()->ServiceMobileDetail->Service = DBO()->Service->Id->Value;
			//DBL()->ServiceMobileDetail->Load();
		
			// note it is assumed that we will only retrieve one record
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
			echo "      <select name='ServiceMobileDetail.SimState' style='width:180px'>\n";
		
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
		echo "<div class='Seperator'></div>\n";			
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Apply Changes");
		echo "</div>\n";
		
		$this->FormEnd();
		echo "<div class='Seperator'></div>\n";		
	}
}

?>
