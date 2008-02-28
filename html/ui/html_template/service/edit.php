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

		//echo "<h2 class='service'>Service Details</h2>\n";
		echo "<div class='GroupedContent'>\n";
		
		$intClosedOn = strtotime(DBO()->Service->ClosedOn->Value);
		$intCurrentDate = strtotime(GetCurrentDateForMySQL());
		
		// Check if the ClosedOn date has been set
		if (DBO()->Service->ClosedOn->Value == NULL)
		{
			// The service is not scheduled to close.  It is either active or hasn't been activated yet
			// Check if it is currently active
			$intCreatedOn = strtotime(DBO()->Service->CreatedOn->Value);
			if ($intCurrentDate >= $intCreatedOn)
			{
				// The service is currently active
				echo "&nbsp;&nbsp;Service opened on ". DBO()->Service->CreatedOn->FormattedValue() ."<br>";
			}
			else
			{
				// This service hasn't been activated yet (change of lessee has been scheduled at a future date)
				echo "&nbsp;&nbsp;Scheduled to be acquired by this lessee on ". DBO()->Service->CreatedOn->FormattedValue() ."<br>";
			}
		}
		else
		{
			// The service has a closedon date check if it is in the future or past
			if ($intClosedOn < $intCurrentDate)
			{
				// The service has been closed
				echo "&nbsp;&nbsp;Service was closed on ".DBO()->Service->ClosedOn->FormattedValue()."<br>";
			}
			elseif ($intClosedOn == $intCurrentDate)
			{
				// The service closes today
				echo "&nbsp;&nbsp;Service closes at the end of today";
			}
			else
			{
				// The service is scheduled to be closed in the future (change of lessee has been scheduled at a future date)
				echo "&nbsp;&nbsp;Scheduled to close on ". DBO()->Service->ClosedOn->FormattedValue() ."<br>";
			}
		}
		
		echo "<div class='ContentSeparator'></div>\n";
		
		// Render hidden properties
		DBO()->Service->Id->RenderHidden();
		
		// Maintaining State (This shouldn't be done here)
		DBO()->Service->CurrentFNN->RenderHidden();
		DBO()->Service->CurrentStatus->RenderHidden();
		DBO()->Service->CurrentIndial100->RenderHidden();
		DBO()->Service->CurrentELB->RenderHidden();
		DBO()->Service->CurrentCostCentre->RenderHidden();
		DBO()->Service->CurrentForceInvoiceRender->RenderHidden();
		DBO()->ServiceInboundDetail->CurrentAnswerPoint->RenderHidden();
		DBO()->ServiceInboundDetail->CurrentConfiguration->RenderHidden();
		
		// I'm pretty sure these are no longer required		
		//DBO()->ServiceMobileDetail->CurrentSimPUK->RenderHidden();
		//DBO()->ServiceMobileDetail->CurrentSimESN->RenderHidden();
		//DBO()->ServiceMobileDetail->CurrentSimState->RenderHidden();
		//DBO()->ServiceMobileDetail->CurrentDOB->RenderHidden();
		//DBO()->ServiceMobileDetail->CurrentComments->RenderHidden();

		DBO()->Service->Account->RenderHidden();
		DBO()->Service->AccountGroup->RenderHidden();
		DBO()->Service->Indial100->RenderHidden();
		DBO()->Service->Status->RenderHidden();
		
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("ServiceType"), RENDER_OUTPUT);	
		
		// The user can only change the FNN if the service was created today
		// (They should only need to change the FNN if they accidently got it wrong to begin with)
		if (DBO()->Service->CreatedOn->Value == GetCurrentDateForMySQL())
		{
			// The service was created today, so they can change the FNN
			DBO()->Service->FNN->RenderInput();
			DBO()->Service->FNNConfirm->RenderInput();
		}
		else
		{
			// The service wasn't created today, so they can't change the FNN
			DBO()->Service->FNN->RenderOutput();
			
			// This shouldn't really be included at all, but if I just render it as a hidden, then I don't have
			// to worry about updating the logic
			DBO()->Service->FNN->RenderHidden();
			DBO()->Service->FNNConfirm->RenderHidden();
		}
		
		// Intialise the value for the Service Status combobox
		if (!DBO()->Service->NewStatus->IsSet)
		{
			DBO()->Service->NewStatus = DBO()->Service->Status->Value;
		}
		
		// Render the Service Status Combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Service Status :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select name='Service.NewStatus' style='width:155px'>\n";
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
		
		// load cost centre details
		$strWhere = "Account IN (0, ". DBO()->Service->Account->Value .")";
		DBL()->CostCentre->Where->SetString($strWhere);
		DBL()->CostCentre->OrderBy("Name");
		DBL()->CostCentre->Load();
	
		// Draw a CostCentre combo box, but only if there have been cost centers defined for this Account
		if (DBL()->CostCentre->RecordCount() > 0)
		{
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Cost Centre :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select name='Service.CostCentre' style='width:155px'>\n";
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

		DBO()->Service->ForceInvoiceRender->RenderInput();
		
		if (DBO()->Service->Indial100->Value)
		{
			DBO()->Service->ELB->RenderInput();
		}
		
		// handle extra inbound phone details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_INBOUND)
		{
			echo "<div class='ContentSeparator'></div>\n";
			DBO()->ServiceInboundDetail->Id->RenderHidden();
			DBO()->ServiceInboundDetail->AnswerPoint->RenderInput();
			DBO()->ServiceInboundDetail->Configuration->RenderInput();
		}
		
		// handle extra mobile phone details
		if (DBO()->Service->ServiceType->Value == SERVICE_TYPE_MOBILE)
		{
			echo "<div class='ContentSeparator'></div>\n";
			DBO()->ServiceMobileDetail->SimPUK->RenderInput();
			DBO()->ServiceMobileDetail->SimESN->RenderInput();
			
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State:</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select name='ServiceMobileDetail.SimState' >\n";
			echo "<option value=''><span>&nbsp;</span></option>\n";
			foreach ($GLOBALS['*arrConstant']['ServiceStateType'] as $strKey=>$arrState)
			{
				$strSelected = (DBO()->ServiceMobileDetail->SimState->Value == $strKey) ? "selected='selected'" : "";
				echo "         <option value='$strKey' $strSelected><span>". $arrState['Description'] ."</span></option>\n";
			}
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>\n";
			
			DBO()->ServiceMobileDetail->DOB->RenderInput();				
			DBO()->ServiceMobileDetail->Comments->RenderInput();
		}
		
		echo "</div>\n";  // GroupedContent
		
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this)");
		$this->AjaxSubmit("Apply Changes");
		echo "</div></div>\n";
		
		$this->FormEnd();
	}	
}

?>
