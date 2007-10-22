<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_edit.php
//----------------------------------------------------------------------------//
/**
 * service_edit
 *
 * HTML Template for the Edit Service popup
 *
 * HTML Template for the Edit Service popup
 * This file defines the class responsible for defining and rendering the layout
 * of the HTML Template used by the Edit Services popup
 *
 * @file		edit.php
 * @language	PHP
 * @package		ui_app
 * @author		Ross
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// HtmlTemplateServiceEdit
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceEdit
 *
 * A specific HTML Template object
 *
 * An service details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceEdit
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
		$this->FormStart("EditService", "Service", "Edit");

		echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		
		// Render hidden properties
		DBO()->Service->Id->RenderHidden();
		DBO()->Service->ServiceType->RenderHidden();
		DBO()->Service->ClosedOn->RenderHidden();
		DBO()->Service->CreatedOn->RenderHidden();
		DBO()->Service->CurrentFNN->RenderHidden();
		DBO()->Service->Account->RenderHidden();
		DBO()->Service->AccountGroup->RenderHidden();
		DBO()->Service->Indial100->RenderHidden();
		DBO()->Service->Status->RenderHidden();
		
		DBO()->Service->Id->RenderOutput();
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		DBO()->Service->FNN->RenderInput();
		DBO()->Service->FNNConfirm->RenderInput();
		
		// Intialise the value for the Service Status combobox
		if (!DBO()->Service->NewStatus->IsSet)
		{
			DBO()->Service->NewStatus = DBO()->Service->Status->Value;
		}
		
		// Render the Service Status Combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Service Status :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='Service.NewStatus' style='width:158px'>\n";
		foreach ($GLOBALS['*arrConstant']['Service'] as $intConstant=>$arrServiceStatus)
		{
			// Only users with admin privileges can archive an account
			if (($intConstant == SERVICE_ARCHIVED) && (!AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN)))
			{
				// The user does not have admin privileges
				continue;
			}

			$strSelected = (DBO()->Service->NewStatus->Value == $intConstant) ? "selected='selected'" : "";
			echo "         <option value='$intConstant' $strSelected>{$arrServiceStatus['Description']}</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
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
			echo "      <select name='Service.CostCentre' >\n";
			$strSelected = (DBO()->Service->CostCentre->Value == NULL) ? "selected='selected'" : "";
			echo "<option value='0' $strSelected>&nbsp;</option>";
			
			foreach (DBL()->CostCentre as $dboCostCentre)
			{
				$strSelected = (DBO()->Service->CostCentre->Value == $dboCostCentre->Id->Value) ? "selected='selected'" : "";
				echo "<option value='".$dboCostCentre->Id->Value."' $strSelected>". $dboCostCentre->Name->Value ."</option>";
			}
			
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
		}

		$intClosedOn = strtotime(DBO()->Service->ClosedOn->Value);
		$intCurrentDate = strtotime(GetCurrentDateForMySQL());
		
		// Check if the ClosedOn date has been set
		if (DBO()->Service->ClosedOn->Value == NULL)
		{
			// The service is not scheduled to close.  It is either active or hasn't been activated yet
			// Check if it is currently active
			$intCreatedOn = strtotime(DBO()->Service->CreatedOn->Value);
			if ($intCurrentDate > $intCreatedOn)
			{
				// The service is currently active
				echo "&nbsp;&nbsp;This service opened on: ". DBO()->Service->CreatedOn->FormattedValue() ."<br>";
			}
			else
			{
				// This service hasn't been activated yet (change of lessee has been scheduled at a future date)
				echo "&nbsp;&nbsp;This service is scheduled to be acquired by this lessee on: ". DBO()->Service->CreatedOn->FormattedValue() ."<br>";
				//echo "&nbsp;&nbsp;This service will be activated on: ". DBO()->Service->CreatedOn->FormattedValue() ."<br>";
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
				// The service is scheduled to be closed in the future (change of lessee has been scheduled at a future date)
				// We dont want the user to cancel the scheduled closure of the service
				echo "&nbsp;&nbsp;This service is scheduled to change to a different lessee on: ".DBO()->Service->ClosedOn->FormattedValue()."<br>";
				//echo "&nbsp;&nbsp;This service is scheduled to be closed on: ".DBO()->Service->ClosedOn->FormattedValue()."<br>";
			}
		}
	
		echo "</div>\n";  // NarrowForm - Generic ServiceDetails
		
		// handle extra inbound phone details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			echo "<div class='SmallSeperator'></div>\n";
			echo "<h2 class='service'>Inbound Specific Details</h2>\n";
			echo "<div class='NarrowForm'>\n";
			DBO()->ServiceInboundDetail->Id->RenderHidden();
			DBO()->ServiceInboundDetail->AnswerPoint->RenderInput();
			DBO()->ServiceInboundDetail->Configuration->RenderInput();
			echo "</div>\n";
		}
		
		// handle extra mobile phone details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			echo "<div class='SmallSeperator'></div>\n";
			echo "<h2 class='service'>Mobile Specific Details</h2>\n";
			echo "<div class='NarrowForm'>\n";
			DBO()->ServiceMobileDetail->Id->RenderHidden();
			DBO()->ServiceMobileDetail->SimPUK->RenderInput();
			DBO()->ServiceMobileDetail->SimESN->RenderInput();
			
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State:</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select name='ServiceMobileDetail.SimState' >\n";
			echo "<option value=''><span class='DefaultOutputSpan'>&nbsp;</span></option>\n";
			foreach ($GLOBALS['*arrConstant']['ServiceStateType'] as $strKey=>$arrState)
			{
				$strSelected = (DBO()->ServiceMobileDetail->SimState->Value == $strKey) ? "selected='selected'" : "";
				echo "         <option value='$strKey' $strSelected><span class='DefaultOutputSpan'>". $arrState['Description'] ."</span></option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
			
			DBO()->ServiceMobileDetail->DOB->RenderInput();				
			DBO()->ServiceMobileDetail->Comments->RenderInput();
			echo "</div>\n";  // NarrowForm - MobileDetails
		}
		
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this)");
		$this->AjaxSubmit("Apply Changes");
		echo "</div></div>\n";
		
		$this->FormEnd();
	}	
}

?>
