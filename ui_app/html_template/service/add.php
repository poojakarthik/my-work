<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServiceAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceAdd
 *
 * A specific HTML Template object
 *
 * HTML Template for Adding a service
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceAdd extends HtmlTemplate
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
		// define javascript to be triggered when the ServiceType combo changes value
		$strServiceTypeComboOnChange = 
		"switch (this.value)
		{
			case '". SERVICE_TYPE_MOBILE ."':
				// hide any details not required for a mobile and display the mobile details
				document.getElementById('InboundDetailDiv').style.display='none';
				document.getElementById('LandlineDetailDiv').style.display='none';
				document.getElementById('MobileDetailDiv').style.display='inline';
				break;
			case '". SERVICE_TYPE_INBOUND ."':
				// hide any details not required for inbound services and show the inbound services details
				document.getElementById('MobileDetailDiv').style.display='none';
				document.getElementById('LandlineDetailDiv').style.display='none';
				document.getElementById('InboundDetailDiv').style.display='inline';
				break;
			case '". SERVICE_TYPE_LAND_LINE ."':
				// hide any details not required for inbound services and show the inbound services details
				document.getElementById('MobileDetailDiv').style.display='none';
				document.getElementById('InboundDetailDiv').style.display='none';
				document.getElementById('LandlineDetailDiv').style.display='inline';
				break;
			default:
				// hide all extra details
				document.getElementById('MobileDetailDiv').style.display='none';
				document.getElementById('InboundDetailDiv').style.display='none';
				document.getElementById('LandlineDetailDiv').style.display='none';
				break;
		}";
		
		
		echo "<h2 class='Service'>Service Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		$this->FormStart("AddService", "Service", "Add");
		
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->AccountGroup->RenderHidden();
		
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Service Type:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='Service.ServiceType' style='width:152px' onchange=\"$strServiceTypeComboOnChange\">\n";
		
		$arrServiceType = array();
		$arrServiceType[SERVICE_TYPE_LAND_LINE]	= GetConstantDescription(SERVICE_TYPE_LAND_LINE, "ServiceType");
		$arrServiceType[SERVICE_TYPE_MOBILE]	= GetConstantDescription(SERVICE_TYPE_MOBILE, "ServiceType");
		$arrServiceType[SERVICE_TYPE_INBOUND] 	= GetConstantDescription(SERVICE_TYPE_INBOUND, "ServiceType");
		$arrServiceType[SERVICE_TYPE_ADSL] 		= GetConstantDescription(SERVICE_TYPE_ADSL, "ServiceType");
		$arrServiceType[SERVICE_TYPE_DIALUP] 	= GetConstantDescription(SERVICE_TYPE_DIALUP, "ServiceType");
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
					echo "         <option value='".$dboCostCentre->Id->Value."' selected='selected'>".$dboCostCentre->Name->Value."</option>";
				}
				else
				{
					// this is not the currently selected costcentre
					echo "         <option value='".$dboCostCentre->Id->Value."'>".$dboCostCentre->Name->Value."</option>";
				}
			}
			
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";		
		}
		
		echo "</div>\n"; // NarrowForm
		
		// Land line specific details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE)
		{
			echo "<div id='LandlineDetailDiv'>\n";
		}
		else
		{
			echo "<div id='LandlineDetailDiv' style='display:none'>\n";			
		}
		echo "<h2 class='Service'>Landline Details</h2>\n";
		echo "<div class='NarrowForm'>\n";

		DBO()->Service->Indial100->RenderInput();
		DBO()->Service->ELB->RenderInput();
		
		// Display all the address details required to setup a landline
		//TODO! Joel You were last working on this on Friday 17th, August.  It was the last thing you were working on before you packed up the springhill office
		DBO()->ServiceAddress->Residential->RenderInput();
		echo "</div>\n"; // NarrowForm
		echo "</div>\n"; // LandlineDetailDiv
		
		// Inbound 1300/1800 specific details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			// show extra inbound detail div
			echo "<div id='InboundDetailDiv'>\n";
		}
		else
		{
			// hide extra inbound detail div
			echo "<div id='InboundDetailDiv' style='display:none;'>\n";
		}
		// handle extra inbound phone details
		echo "<h2 class='service'>Inbound Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		DBO()->ServiceInboundDetail->Id->RenderHidden();
		DBO()->ServiceInboundDetail->AnswerPoint->RenderHidden();
		DBO()->ServiceInboundDetail->Configuration->RenderHidden();
		
		DBO()->ServiceInboundDetail->AnswerPoint->RenderInput();
		DBO()->ServiceInboundDetail->Configuration->RenderInput();
		echo "</div>\n"; // NarrowForm
		echo "</div>\n"; // InboundDetailDiv
		
		// handle extra mobile phone details
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
		echo "<h2 class='service'>Mobile Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
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
		echo "</div>\n"; // NarrowForm
		echo "</div>\n"; // MobileDetailDiv
		
		
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Save");
		echo "</div>\n";
		
		$this->FormEnd();
	}
}

?>
