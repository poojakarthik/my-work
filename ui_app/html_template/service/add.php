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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("service_edit");
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
		// Start the form
		$this->FormStart("AddService", "Service", "Add");
		
		echo "<h2 class='Service'>Service Details</h2>\n";
		// Start the Generic Details div
		echo "<div class='NarrowForm'>\n";
		
		DBO()->Account->Id->RenderHidden();
		DBO()->Account->AccountGroup->RenderHidden();
		
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		
		// If the service type is not set, then set it to being a landline
		if (!DBO()->Service->ServiceType->IsSet)
		{
			DBO()->Service->ServiceType = SERVICE_TYPE_LAND_LINE;
		}
		
		// Render the ServiceType combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Service Type :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='Service.ServiceType' style='width:158px' onchange='Vixen.ServiceEdit.ServiceTypeOnChange(this.value)'>\n";
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intKey=>$arrServiceSelection)
		{
			$strSelected = (DBO()->Service->ServiceType->Value == $intKey) ? "selected='selected'" : "";
			echo "		   <option value='$intKey' $strSelected>". $arrServiceSelection['Description'] ."</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
				
		DBO()->Service->FNN->RenderInput();
		DBO()->Service->FNNConfirm->RenderInput();
		
		DBL()->CostCentreCombo->Account = DBO()->Account->Id->Value;
		DBL()->CostCentreCombo->SetTable('CostCentre');
		DBL()->CostCentreCombo->OrderBy("Name");
		DBL()->CostCentreCombo->Load();

		if (DBL()->CostCentreCombo->RecordCount() > 0)
		{
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Cost Centre :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select name='Service.CostCentre' style='width:158px'>\n";
			// If a CostCentre has not been selected then DBO()->Service->CostCentre->Value == NULL or 0
			$strSelected = (!DBO()->Service->CostCentre->Value) ? "selected='selected'" : "";
			echo "			<option value='0' $strSelected>&nbsp;</option>\n";

			foreach (DBL()->CostCentreCombo as $dboCostCentre)
			{
				$strSelected = (DBO()->Service->CostCentre->Value == $dboCostCentre->Id->Value) ? "selected='selected'" : "";
				echo "         <option value='". $dboCostCentre->Id->Value ."' $strSelected>". $dboCostCentre->Name->Value ."</option>\n";
			}

			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";		
		}
		
		echo "</div>\n"; // NarrowForm - GenericDetailsDiv
		
		// Land line specific details
		$strStyleDisplay = (DBO()->Service->ServiceType->Value == SERVICE_TYPE_LAND_LINE) ? "style='display:inline'" : "style='display:none'";
		echo "<div id='LandlineDetailDiv' $strStyleDisplay>\n";
		$this->_RenderLandlineDetails();
		echo "</div>\n";
		
		// Mobile phone specific details
		$strStyleDisplay = (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE) ? "style='display:inline'" : "style='display:none'";
		echo "<div id='MobileDetailDiv' $strStyleDisplay>\n";
		$this->_RenderMobileDetails();
		echo "</div>\n";
		
		// Inbound specific details
		$strStyleDisplay = (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND) ? "style='display:inline'" : "style='display:none'";
		echo "<div id='InboundDetailDiv' $strStyleDisplay>\n";
		$this->_RenderInboundDetails();
		echo "</div>\n";
		
		// Render the buttons
		echo "<div class='Right'>\n";
		// workout where to go if cancel is triggered
		if (DBO()->Account->Id->IsSet)
		{
			$strCancelAction = Href()->InvoicesAndPayments(DBO()->Account->Id->Value);
		}
		elseif (DBO()->Service->Id->IsSet)
		{
			$strCancelAction = Href()->ViewService(DBO()->Service->Id-Value);
		}
		else
		{
			$strCancelAction = "javascript: window.history.go(-1)";
		}
		
		$this->Button("Cancel", $strCancelAction);
		$this->AjaxSubmit("Save");
		echo "</div>\n";
		
		$this->FormEnd();
	}
	
	function _RenderLandlineDetails()
	{
		echo "<div class='SmallSeperator'></div>\n";
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
	}
	
	function _RenderMobileDetails()
	{
		echo "<div class='SmallSeperator'></div>\n";
		echo "<h2 class='service'>Mobile Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		DBO()->ServiceMobileDetail->SimPUK->RenderInput();
		DBO()->ServiceMobileDetail->SimESN->RenderInput();
						
		$arrState = array();
		$arrState[SERVICE_STATE_TYPE_ACT]	= "Australian Capital Territory";
		$arrState[SERVICE_STATE_TYPE_NSW]	= "New South Wales";
		$arrState[SERVICE_STATE_TYPE_VIC]	= "Victoria";
		$arrState[SERVICE_STATE_TYPE_SA]	= "South Australia";
		$arrState[SERVICE_STATE_TYPE_WA]	= "Western Australia";
		$arrState[SERVICE_STATE_TYPE_TAS]	= "Tasmania";
		$arrState[SERVICE_STATE_TYPE_NT]	= "Northern Territory";
		$arrState[SERVICE_STATE_TYPE_QLD]	= "Queensland";
		
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='ServiceMobileDetail.SimState' style='width:180px'>\n";
		foreach ($arrState as $strKey=>$strStateSelection)
		{
			$strSelected = (DBO()->ServiceMobileDetail->SimState->Value == $strKey) ? "selected='selected'" : "";
			echo "         <option value='$strKey' $strSelected>$strStateSelection</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		DBO()->ServiceMobileDetail->DOB->RenderInput();				
		DBO()->ServiceMobileDetail->Comments->RenderInput();
		echo "</div>\n"; // NarrowForm
	}
	
	function _RenderInboundDetails()
	{
		//TODO! Check out the Service Edit page
	}
}

?>
