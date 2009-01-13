<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// edit.php
//----------------------------------------------------------------------------//
/**
 * edit
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
		$bolUserHasAdminPerm	= AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);
		$intCurrentSatatus		= DBO()->Service->CurrentStatus->Value;
		echo "<!-- Actual Service Declared : ". DBO()->ActualRequestedService->Id->Value ." -->\n";
		
		// Start the form
		$this->FormStart("EditService", "Service", "Edit");

		echo "<div class='GroupedContent'>\n";
		
		$intClosedOn = strtotime(DBO()->Service->ClosedOn->Value);
		$intCurrentDate = strtotime(GetCurrentDateForMySQL());
		
		$strViewHistoryLink	= Href()->ViewServiceHistory(DBO()->Service->Id->Value);
		$strViewHistory		= "<a href='$strViewHistoryLink'>history</a>";
		$objService			= ModuleService::GetServiceById(DBO()->Service->Id->Value, DBO()->Service->RecordType->Value);		
		$arrLastEvent		= HtmlTemplateServiceHistory::GetLastEvent($objService);
		$strLastEvent		= "{$arrLastEvent['Event']}<br />on {$arrLastEvent['TimeStamp']}<br />by {$arrLastEvent['EmployeeName']} ({$strViewHistory})";
		DBO()->Service->MostRecentEvent = $strLastEvent;
		DBO()->Service->MostRecentEvent->RenderOutput();
		
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
		DBO()->Account->Archived->RenderHidden();
		
		DBO()->Service->Account->RenderHidden();
		DBO()->Service->AccountGroup->RenderHidden();
		DBO()->Service->Indial100->RenderHidden();
		DBO()->Service->Status->RenderHidden();
		
		DBO()->Service->ServiceType->RenderCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT);	
		
		// The user can only change the FNN if the service was created today
		// (They should only need to change the FNN if they accidently got it wrong to begin with)
		$objService = ModuleService::GetServiceById(DBO()->Service->Id->Value);
		if ($objService->FNNCanBeChanged())
		{
			// The service was created today, so they can change the FNN
			DBO()->Service->FNN->RenderInput();
			DBO()->Service->FNNConfirm->RenderInput();
		}
		else
		{
			// The service wasn't created today, so they can't change the FNN
			$strFNN = DBO()->Service->FNN->Value . (DBO()->Service->Indial100->Value ? " (Indial 100)":"");
			
			DBO()->Service->FNN->RenderArbitrary($strFNN, RENDER_OUTPUT);
			
			// This shouldn't really be included at all, but if I just render it as a hidden, then I don't have
			// to worry about updating the logic
			DBO()->Service->FNN->RenderHidden();
			DBO()->Service->FNNConfirm->RenderHidden();
		}
		
		// Intialise the value for the Service Status combobox
		if (!DBO()->Service->NewStatus->IsSet)
		{
			DBO()->Service->NewStatus = DBO()->Service->CurrentStatus->Value;
		}
		
		// Work out what options should be available in the Status combobox
		if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			// The status cannot be changed
			$arrStatusOptions[DBO()->Service->CurrentStatus->Value] = $GLOBALS['*arrConstant']['service_status'][DBO()->Service->CurrentStatus->Value];
		}
		else
		{
			$arrStatusOptions = $GLOBALS['*arrConstant']['service_status'];
			if ($objService->GetStatus() != SERVICE_PENDING)
			{
				// Remove the SERVICE_PENDING option
				unset($arrStatusOptions[SERVICE_PENDING]);
				
				if ($objService->GetStatus() != SERVICE_ARCHIVED && !$bolUserHasAdminPerm)
				{
					// Remove the ARCHIVE option as only admin users can archive services
					unset($arrStatusOptions[SERVICE_ARCHIVED]);
				}
			}
			else
			{
				// The service is pending activation
				// The user should not be able to disconnect it or archive it, unless it originated from a SalesPortal sale which is now cancelled
				// I shouldn't need to check for the Sales module, because if it isn't active, then there shouldn't be any records in the flex.sale_item table
				$objFlexSaleItem = FlexSaleItem::getForServiceId(DBO()->Service->Id->Value);
				if ($objFlexSaleItem !== NULL)
				{
					// The service relates to a sale item created in the SalesPortal
					$saleItem = $objFlexSaleItem->getExternalReferenceObject();
					if ($saleItem->saleItemStatusId == DO_Sales_SaleItemStatus::CANCELLED)
					{
						// The sale item has been cancelled, which means the service should be 'cancelled'.
						// Considering it's pending activation, no provisioning should have been done, unless it was manually provisioned
						unset($arrStatusOptions[SERVICE_ARCHIVED]);
						unset($arrStatusOptions[SERVICE_ACTIVE]);
						$arrStatusOptions[SERVICE_DISCONNECTED]['Description'] .= " (Cancelled Sale)";
					}
					else
					{
						// The sale item has not been cancelled
						unset($arrStatusOptions[SERVICE_DISCONNECTED]);
						unset($arrStatusOptions[SERVICE_ARCHIVED]);
					}
				}
				else
				{
					// The service did not originate from a sale in the SalesPortal
					unset($arrStatusOptions[SERVICE_DISCONNECTED]);
					unset($arrStatusOptions[SERVICE_ARCHIVED]);
				}
			}
		}
		
		$strStatusOptions = "";
		foreach ($arrStatusOptions as $intStatus=>$arrStatus)
		{
			$strSelected		= (DBO()->Service->NewStatus->Value == $intStatus) ? "selected='selected'" : "";
			$strStatusOptions	.= "<option value='$intStatus' $strSelected>{$arrStatus['Description']}</option>";
		}
		
		// Render the Service Status Combobox
		echo "
<div class='DefaultElement'>
	<div class='DefaultLabel'>&nbsp;&nbsp;Status :</div>
	<div class='DefaultOutput'>
		<select id='ServiceEditStatusCombo' name='Service.NewStatus' style='width:auto'>$strStatusOptions</select>
	</div>
</div>
";
		
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
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;State :</div>\n";
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

			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Comments :</div>\n";
			echo "   <textarea id='ServiceMobileDetail.Comments' name='ServiceMobileDetail.Comments' class='DefaultInputTextArea' rows='3' style='overflow:auto;width:50%'>". htmlspecialchars(DBO()->ServiceMobileDetail->Comments->Value) ."</textarea>\n";
			echo "</div>\n";

		}
		
		echo "</div>\n";  // GroupedContent
		
		// Render buttons
		echo "
<div class='ButtonContainer'>
	<div style='float:right'>
		<input type='button' style='display:none;' id='ServiceEditSubmitButton' value='Apply Changes' onclick=\"Vixen.Ajax.SendForm('VixenForm_EditService', 'Apply Changes', 'Service', 'Edit', 'Popup', 'EditServicePopupId', 'medium', '{$this->_strContainerDivId}')\"></input>
		<input type='button' value='Cancel' onclick='Vixen.Popup.Close(this)'></input>
		<input type='button' value='Apply Changes' onclick='Vixen.ServiceEdit.ApplyChanges()'></input>
	</div>
</div>
";

		$this->FormEnd();
		
		// Initialise the javascript object
		if ($objService->GetServiceType() == SERVICE_TYPE_LAND_LINE && $objService->GetCurrentPlan() != NULL)
		{
			// The service can be automatically provisioned
			$strCanBeProvisioned = "true";
		}
		else
		{
			$strCanBeProvisioned = "false";
		}
		echo "<script type='text/javascript'>Vixen.ServiceEdit.Initialise($intCurrentSatatus, $strCanBeProvisioned)</script>\n";
	}	
}

?>
