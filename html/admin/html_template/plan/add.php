<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// add.php
//----------------------------------------------------------------------------//
/**
 * add
 *
 * HTML Template for the Add Rate Plan HTML object
 *
 * HTML Template for the Add Rate Plan HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a rate plan.
 *
 * @file		add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplatePlanAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanAdd
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanAdd extends HtmlTemplate
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("rate_plan_add");
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
	 *
	 * @method
	 */
	function Render()
	{
		switch ($this->_intContext)
		{
		case HTML_CONTEXT_DETAILS:
			$this->_RenderPlanDetails();
			break;
		case HTML_CONTEXT_RATE_GROUPS:
			$this->_RenderRateGroups();
			break;
		case HTML_CONTEXT_RATE_GROUPS_EMPTY:
			// Don't render anything
			break;
		case HTML_CONTEXT_DEFAULT:
		default:
			// This should only ever be run the first time the Add Rate Plan page is rendered
			// Set Up the form for adding a rate plan
			$this->FormStart("AddPlan", "Plan", "Add");
			
			// Render the value of the page that called this one, so we can return to it, once the plan has been committed
			DBO()->CallingPage->Href->RenderHidden();
			
			// Include the Id of the RatePlan as a hidden input.  This will be zero when adding a new plan
			DBO()->RatePlan->Id->RenderHidden();
			if (DBO()->BaseRatePlan->Id->IsSet)
			{
				// Render the BaseRatePlan.Id if it is set
				DBO()->BaseRatePlan->Id->RenderHidden();
			}
			
			echo "<div id='RatePlanDetailsId'>\n";
			$this->_RenderPlanDetails();
			echo "</div>\n";
	
			// Stick in the div container for the DeclareRateGroups table
			echo "<div id='RateGroupsDiv'></div>\n";
			
			// Create the buttons
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Cancel", "Vixen.Popup.Confirm(\"Are you sure you want to abort adding this rate plan?\", Vixen.RatePlanAdd.ReturnToCallingPage, null, null, \"Yes\", \"No\")");
			$this->Button("Save as Draft", "Vixen.Popup.Confirm(\"Are you sure you want to save this Rate Plan as a Draft?\", Vixen.RatePlanAdd.SaveAsDraft, null, null, \"Yes\", \"No\")");
			$this->Button("Commit", "Vixen.Popup.Confirm(\"Are you sure you want to commit this Rate Plan?<br />The Rate Plan cannot be edited once it is committed\", Vixen.RatePlanAdd.Commit, null, null, \"Yes\", \"No\")");
			//$this->AjaxSubmit("Save as Draft");
			//$this->AjaxSubmit("Commit");
			echo "</div></div>\n";
			
			$this->FormEnd();
			
			// Initialise the Rate Groups assocciated with this form
			$intServiceType = DBO()->RatePlan->ServiceType->Value;
			echo "<script type='text/javascript'>Vixen.RatePlanAdd.ChangeServiceType($intServiceType);</script>\n";
			
			break;
		}
	}
	
	
	//------------------------------------------------------------------------//
	// _RenderPlanDetails
	//------------------------------------------------------------------------//
	/**
	 * _RenderPlanDetails()
	 *
	 * Renders the Plan details section of the form
	 *
	 * Renders the Plan details section of the form
	 *
	 * @method
	 */
	private function _RenderPlanDetails()
	{
		echo "<h2 class='Plan'>Plan Details</h2>\n";
		echo "<div class='GroupedContent'>\n";
		
		// Only apply the output mask if the DBO()->RatePlan is not invalid ( ~ valid)
		$bolApplyOutputMask = !DBO()->RatePlan->IsInvalid();

		DBO()->RatePlan->Name->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask, Array("style:width"=>"480px", "attribute:maxlength"=>255));
		DBO()->RatePlan->Description->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask, Array("style:width"=>"480px", "attribute:maxlength"=>255));
		echo "<div class='SmallSeparator'></div>";
		
		echo "<div id='Container_PlanDetails' style='width:100%;height:auto'>";
		echo "<div id='PlanDetailsColumn1' style='width:50%;float:left'>";		
		DBO()->RatePlan->Shared->RenderInput(CONTEXT_DEFAULT);
		DBO()->RatePlan->MinMonthly->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->ChargeCap->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->UsageCap->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->RecurringCharge->RenderInput(CONTEXT_DEFAULT, FALSE, $bolApplyOutputMask);
		DBO()->RatePlan->discount_cap->RenderInput(CONTEXT_DEFAULT, FALSE, $bolApplyOutputMask);
		
		// Render the "scalable, minimum_services & maximum_services" input controls
		if (DBO()->RatePlan->scalable->Value == TRUE)
		{
			// The plan has been flagged as scalable
			$intMinServices			= DBO()->RatePlan->minimum_services->Value;
			$intMaxServices			= DBO()->RatePlan->maximum_services->Value;
			$strChecked				= "checked='checked'";
			$strContainerStyle		= "display:block;visibility:visible";
		}
		else
		{
			// The plan is not scalable
			$intMinServices		= NULL;
			$intMaxServices		= NULL;
			$strChecked			= "";
			$strContainerStyle	= "display:none;visibility:hidden";
		}
		
		$strMinServicesClass	= (DBO()->RatePlan->minimum_services->IsInvalid())? "DefaultInvalidInputText" : "DefaultInputText";
		$strMaxServicesClass	= (DBO()->RatePlan->maximum_services->IsInvalid())? "DefaultInvalidInputText" : "DefaultInputText";

		echo "
<div class='DefaultElement'>
	<input type='checkbox' id='ScalableCheckbox' name='RatePlan.scalable' $strChecked onclick='Vixen.RatePlanAdd.ScalableOnChange()' class='DefaultInputCheckBox2 Default' />
	<div class='DefaultLabel'>&nbsp;&nbsp;Scalable :</div>
</div>
<div id='Scalable_ExtraDetailsContainer' style='$strContainerStyle;margin-top:5px'>
	<div class='DefaultElement'>
		<input type='text' id='RatePlan.minimum_services' name='RatePlan.minimum_services' class='$strMinServicesClass' value='$intMinServices' maxlength='4'/>
		<div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Minimum Services :</div>
	</div>
	<div class='DefaultElement'>
		<input type='text' id='RatePlan.maximum_services' name='RatePlan.maximum_services' class='$strMaxServicesClass' value='$intMaxServices' maxlength='4'/>
		<div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Maximum Services :</div>
	</div>
</div>
";
		
		echo "</div>";  // PlanDetailsColumn1
		echo "<div id='PlanDetailsColumn2' style='width:50%;float:left'>";
		DBO()->RatePlan->InAdvance->RenderInput(CONTEXT_DEFAULT);
		
		// Allow CDR Hiding
		echo "
<div class='DefaultElement' style='margin-bottom:4px;'>
	<input type='checkbox' id='RatePlan.allow_cdr_hiding' name='RatePlan.allow_cdr_hiding' $strChecked class='DefaultInputCheckBox2 Default' />
	<div class='DefaultLabel'>&nbsp;&nbsp;Allow CDR Hiding :</div>
</div>";
		
		// Contract Payout Details (done by Rich, sorry if I fuck this up, haha)
		$intContractTerm	= DBO()->RatePlan->ContractTerm->Value;
		if ($intContractTerm > 0)
		{
			// The Plan has a Contract Length
			$fltExitFee				= DBO()->RatePlan->contract_exit_fee->Value;
			$fltPayoutPercentage	= DBO()->RatePlan->contract_payout_percentage->Value;
			$strContainerStyle		= "display:block;visibility:visible";
		}
		else
		{
			// The Plan does not have a Contract Length
			$fltExitFee				= 0.0;
			$fltPayoutPercentage	= 0.0;
			$strContainerStyle		= "display:none;visibility:hidden";
		}
		
		$strContractTermClass		= (DBO()->RatePlan->ContractTerm->IsInvalid())? "DefaultInvalidInputText" : "DefaultInputText";
		$strExitFeeClass			= (DBO()->RatePlan->contract_exit_fee->IsInvalid())? "DefaultInvalidInputText" : "DefaultInputText";
		$strPayoutPercentageClass	= (DBO()->RatePlan->contract_payout_percentage->IsInvalid())? "DefaultInvalidInputText" : "DefaultInputText";

		echo "
<div class='DefaultElement'>
	<input type='text' id='RatePlan.ContractTerm' name='RatePlan.ContractTerm' onchange='Vixen.RatePlanAdd.ContractTermOnChange()' class='{$strContractTermClass}' value='{$intContractTerm}'/>
	<div class='DefaultLabel'>&nbsp;&nbsp;Contract Term (months) :</div>
</div>
<div id='Contract_ExtraDetailsContainer' style='$strContainerStyle;margin-top:0px'>
	<div class='DefaultElement'>
		<input type='text' id='RatePlan.contract_exit_fee' name='RatePlan.contract_exit_fee' class='$strExitFeeClass' value='$fltExitFee'/>
		<div class='DefaultLabel'>&nbsp;&nbsp;Contract Exit Fee (\$):</div>
	</div>
	<div class='DefaultElement'>
		<input type='text' id='RatePlan.contract_payout_percentage' name='RatePlan.contract_payout_percentage' class='$strPayoutPercentageClass' value='$fltPayoutPercentage'/>
		<div class='DefaultLabel'>&nbsp;&nbsp;Contract Payout (%):</div>
	</div>
</div>
";
		
		// Build the list of carriers
		$arrCarriers = Array();
		DBL()->Carrier->SetColumns("Id, Name, carrier_type");
		DBL()->Carrier->carrier_type = CARRIER_TYPE_TELECOM;
		DBL()->Carrier->OrderBy("Name");
		DBL()->Carrier->Load();
		
		foreach (DBL()->Carrier as $dboCarrier)
		{
			$arrCarriers[$dboCarrier->Id->Value] = $dboCarrier->Name->Value;
		}
		
		// Build the list of default Carrier values for each ServiceType that has defaults
		$arrServiceTypeDefaults = Array();
		$arrServiceTypeDefaults[SERVICE_TYPE_LAND_LINE]['CarrierFullService']	= CARRIER_UNITEL;
		$arrServiceTypeDefaults[SERVICE_TYPE_LAND_LINE]['CarrierPreselection']	= CARRIER_OPTUS;
		
		// Build the ServiceType Combobox
		if (DBO()->RatePlan->Id->Value > 0)
		{
			// Disable the ServiceType Combobox
			$strServiceTypeDisabled = "disabled='disabled'";
		}
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Service Type :</div>\n";
		echo "      <select id='ServiceTypeCombo' name='RatePlan.ServiceType' class='DefaultInputComboBox' style='width:155px;' onchange='javascript: Vixen.RatePlanAdd.ChangeServiceType(this.value, true);' $strServiceTypeDisabled>\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>\n";
		foreach ($GLOBALS['*arrConstant']['service_type'] as $intKey=>$arrValue)
		{
			// If the ServiceType has default values for the Carrier fields, then include them in the <option> tag as attributes
			$strCarrierDefaults = "";
			if (IsSet($arrServiceTypeDefaults[$intKey]))
			{
				$strCarrierDefaults	 = "CarrierFullService='{$arrServiceTypeDefaults[$intKey]['CarrierFullService']}'";
				$strCarrierDefaults .= " CarrierPreselection='{$arrServiceTypeDefaults[$intKey]['CarrierPreselection']}'";
			}
			
			// Flag the option as being selected if it is the currently selected ServiceType
			$strSelected = (DBO()->RatePlan->ServiceType->Value == $intKey) ? "selected='selected'" : "";
			echo "         <option value='$intKey' $strSelected $strCarrierDefaults>{$arrValue['Description']}</option>\n";
		}
		echo "      </select>\n";
		echo "</div>\n"; // DefaultElement

		// Build the CarrierFullService combo box
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Carrier Full Service :</div>\n";
		echo "      <select id='CarrierFullServiceCombo' name='RatePlan.CarrierFullService' class='DefaultInputComboBox' style='width:155px;'>\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>\n";
		foreach ($arrCarriers as $intCarrier=>$strCarrier)
		{
			// Flag the option as being selected if it is the currently selected CarrierFullService
			$strSelected = (DBO()->RatePlan->CarrierFullService->Value == $intCarrier) ? "selected='selected'" : "";
			echo "         <option value='$intCarrier' $strSelected>$strCarrier</option>\n";
		}
		echo "      </select>\n";
		echo "</div>\n"; // DefaultElement
		
		// Build the CarrierPreselection combo box
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Carrier Preselection :</div>\n";
		echo "      <select id='CarrierPreselectionCombo' name='RatePlan.CarrierPreselection' class='DefaultInputComboBox' style='width:155px;'>\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>\n";
		foreach ($arrCarriers as $intCarrier=>$strCarrier)
		{
			// Flag the option as being selected if it is the currently selected CarrierPreselection
			$strSelected = (DBO()->RatePlan->CarrierPreselection->Value == $intCarrier) ? "selected='selected'" : "";
			echo "         <option value='$intCarrier' $strSelected>$strCarrier</option>\n";
		}
		echo "      </select>\n";
		echo "</div>\n"; // DefaultElement
		
		// Build the CustomerGroup combo box
		$selCustomerGroups = new StatementSelect("CustomerGroup", "Id, InternalName", "TRUE", "InternalName");
		$selCustomerGroups->Execute();
		$arrCustomerGroups = $selCustomerGroups->FetchAll();
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Customer Group :</div>\n";
		echo "      <select id='CustomerGroupCombo' name='RatePlan.customer_group' class='DefaultInputComboBox' style='width:155px;'>\n";
		foreach ($arrCustomerGroups as $arrCustomerGroup)
		{
			// Flag the option as being selected if it is the currently selected CustomerGroup
			$strSelected = (DBO()->RatePlan->customer_group->Value == $arrCustomerGroup['Id']) ? "selected='selected'" : "";
			echo "         <option value='{$arrCustomerGroup['Id']}' $strSelected>{$arrCustomerGroup['InternalName']}</option>\n";
		}
		echo "      </select>\n";
		echo "</div>\n"; // DefaultElement
		

		echo "</div>";  // PlanDetailsColumn2
		echo "<div style='float:none; clear:both;'></div>";
		echo "</div>";  // Container_PlanDetails
		echo "</div>\n"; // GroupedContent
		echo "<div class='SmallSeperator'></div>\n";
		echo "<script type='text/javascript'>Vixen.RatePlanAdd.Initialise()</script>";
	}
	
	//------------------------------------------------------------------------//
	// _RenderRateGroups
	//------------------------------------------------------------------------//
	/**
	 * _RenderRateGroups()
	 *
	 * Renders the Rate Groups section of the form
	 *
	 * Renders the Rate Groups section of the form
	 *
	 * @method
	 */
	private function _RenderRateGroups()
	{
		// Render a table for the user to specify a Rate Group for each Record Type required of the Service Type
		echo "<h2 class='Plan'>Rate Groups</h2>\n";
		Table()->RateGroups->SetHeader("&nbsp;", "Record Type", "Rate Group", "&nbsp;", "Fleet Rate Group", "&nbsp;");
		Table()->RateGroups->SetWidth("1%", "25%", "32%", "5%", "32%", "5%");
		Table()->RateGroups->SetAlignment("Center", "Left", "Left", "Center", "Left", "Center");
		
		foreach (DBL()->RecordType as $dboRecordType)
		{
			// build the Record Type cell
			$strRequiredCell = "&nbsp;";
			if ($dboRecordType->Required->Value)
			{
				$strRequiredCell = "<span class='RequiredInput'>*</span>";
			}
			
			$strRecordTypeCell = $dboRecordType->Description->AsValue();
			
			// Build the RateGroup Combobox
			$strObject		= "RateGroup" . $dboRecordType->Id->Value;
			$strProperty	= "RateGroupId";
			$strRateGroupCell  = "<div class='DefaultElement'>\n";
			$strRateGroupCell .= "   <div class='DefaultInputSpan'>\n";
			$strRateGroupCell .= "      <select id='$strObject.$strProperty' name='$strObject.$strProperty' style='width:100%'>\n";
			$strRateGroupCell .= "         <option value='0' selected='selected'>&nbsp;</option>\n";
			foreach (DBL()->RateGroup as $dboRateGroup)
			{
				if (($dboRateGroup->RecordType->Value == $dboRecordType->Id->Value) && ($dboRateGroup->Fleet->Value == FALSE))
				{
					// Flag the RateGroup option as being a draft, if it is one
					if ($dboRateGroup->Archived->Value == 2)
					{
						// The Rate Group is currently saved as a draft.  Flag it as such
						$strDraft = "draft='draft'";
						$strName = "DRAFT: ". htmlspecialchars($dboRateGroup->Name->Value, ENT_QUOTES);
						$strName = "<span class='DefaultOutputSpan'>$strName</span>";
					}
					else
					{
						// The Rate Group is not a draft
						$strDraft = "";
						$strName = htmlspecialchars($dboRateGroup->Name->Value, ENT_QUOTES);
						$strName = "<span class='DefaultOutputSpan'>$strName</span>";
					}
					
					// Flag this option as being selected if it is the currently selected RateGroup for this RecordType
					$strSelected = (!DBO()->{$strObject}->{$strProperty}->IsSet && $dboRateGroup->Selected->IsSet) ? "selected='selected'" : "";
					$strRateGroupCell .= "<option value='{$dboRateGroup->Id->Value}' $strSelected $strDraft>$strName</option>";
				}
			}
			$strRateGroupCell .= "      </select>\n";
			$strRateGroupCell .= "   </div>\n";
			$strRateGroupCell .= "</div>\n";
			
			// Build the FleetRateGroup Combobox
			$strProperty	= "FleetRateGroupId";
			$strFleetRateGroupCell  = "<div class='DefaultElement'>\n";
			$strFleetRateGroupCell .= "   <div class='DefaultInputSpan'>\n";
			$strFleetRateGroupCell .= "      <select id='$strObject.$strProperty' name='$strObject.$strProperty' style='width:100%'>\n";
			$strFleetRateGroupCell .= "         <option value='0' selected='selected'>&nbsp;</option>\n";
			foreach (DBL()->RateGroup as $dboRateGroup)
			{
				if (($dboRateGroup->RecordType->Value == $dboRecordType->Id->Value) && ($dboRateGroup->Fleet->Value == TRUE))
				{
					// Flag the RateGroup option as being a draft, if it is one
					if ($dboRateGroup->Archived->Value == 2)
					{
						// The Rate Group is currently saved as a draft.  Flag it as such
						$strDraft = "draft='draft'";
						$strName = "DRAFT: ". htmlspecialchars($dboRateGroup->Name->Value, ENT_QUOTES);
						$strName = "<span class='DefaultOutputSpan'>$strName</span>";
					}
					else
					{
						// The Rate Group is not a draft
						$strDraft = "";
						$strName = htmlspecialchars($dboRateGroup->Name->Value, ENT_QUOTES);
						$strName = "<span class='DefaultOutputSpan'>$strName</span>";
					}
					
					// Flag this option as being selected if it is the currently selected Fleet RateGroup for this RecordType
					$strSelected = (!DBO()->{$strObject}->{$strProperty}->IsSet && $dboRateGroup->Selected->IsSet) ? "selected='selected'" : "";
					$strFleetRateGroupCell .= "<option value='". $dboRateGroup->Id->Value ."' $strSelected $strDraft>". $strName ."</option>";
				}
			}
			$strFleetRateGroupCell .= "      </select>\n";
			$strFleetRateGroupCell .= "   </div>\n";
			$strFleetRateGroupCell .= "</div>\n";

			// Build the buttons
			if ($dboRecordType->Context->Value == 0)
			{
				// The RecordType does not make use of multiple destinations.  Don't allow exporting or importing
				
				// Build the Edit Rate Group Button
				$strEditRateGroupHref		= "javascript: Vixen.RatePlanAdd.EditRateGroup({$dboRecordType->Id->Value}, false)";
				$strRateGroupActionsCell	= "<span><a href='$strEditRateGroupHref' title='Edit'><img src='img/template/edit.png'></img></a></span>";
				
				// Build the Add Rate Group Button
				$strAddRateGroupHref		= "javascript: Vixen.RatePlanAdd.AddRateGroup({$dboRecordType->Id->Value}, false)";
				$strRateGroupActionsCell	.= "&nbsp;<span><a href='$strAddRateGroupHref' title='New'><img src='img/template/new.png'></img></a></span>";
				
				// Build the Edit Fleet Rate Group Button
				$strEditRateGroupHref			= "javascript: Vixen.RatePlanAdd.EditRateGroup({$dboRecordType->Id->Value}, true)";
				$strFleetRateGroupActionsCell	= "<span><a href='$strEditRateGroupHref' title='Edit'><img src='img/template/edit.png'></img></a></span>";
				// Build the Add Fleet Rate Group Button
				$strAddRateGroupHref			= "javascript: Vixen.RatePlanAdd.AddRateGroup({$dboRecordType->Id->Value}, true)";
				$strFleetRateGroupActionsCell	.= "&nbsp;<span><a href='$strAddRateGroupHref' title='New'><img src='img/template/new.png'></img></a></span>";
			}
			else
			{
				// The RecordType uses multiple destinations.  Use RateGroup Import/Export functionality instead of the standard Edit/New RateGroup functionality
				
				// Build the Import Rate Group Button (This is used for both Fleet and Normal RateGroups)
				$strImportRateGroupHref			= Href()->ImportRateGroup($dboRecordType->Id->Value, FALSE);
				$strImportFleetRateGroupHref	= Href()->ImportRateGroup($dboRecordType->Id->Value, TRUE);
				$strRateGroupActionsCell		= "<span><a href='$strImportRateGroupHref' title='Import'><img src='img/template/import.png'></img></a></span>";
				$strFleetRateGroupActionsCell	= "<span><a href='$strImportFleetRateGroupHref' title='Import'><img src='img/template/import.png'></img></a></span>";
				
				// Build the Export Rate Group Buttons
				$strExportRateGroup			= "javascript: Vixen.RatePlanAdd.ExportRateGroup({$dboRecordType->Id->Value}, false)";
				$strExportFleetRateGroup	= "javascript: Vixen.RatePlanAdd.ExportRateGroup({$dboRecordType->Id->Value}, true)";

				$strRateGroupActionsCell		.= "&nbsp<span><a href='$strExportRateGroup' title='Export'><img src='img/template/export.png'></img></a></span>";
				$strFleetRateGroupActionsCell	.= "&nbsp;<span><a href='$strExportFleetRateGroup' title='Export'><img src='img/template/export.png'></img></a></span>";
			}

			// Add this row to the table
			Table()->RateGroups->AddRow($strRequiredCell, $strRecordTypeCell, $strRateGroupCell, $strRateGroupActionsCell, $strFleetRateGroupCell, $strFleetRateGroupActionsCell);
		}
		
		if (DBL()->RecordType->RecordCount() == 0)
		{
			$strServiceType = DBO()->RatePlan->ServiceType->AsCallback("GetConstantDescription", Array("service_type"));
			// There are no RecordTypes required for the ServiceType chosen
			Table()->RateGroups->AddRow("<span class='DefaultOutputSpan Default'>No Record Types required for Service Type: $strServiceType</span>");
			Table()->RateGroups->SetRowAlignment("left");
			Table()->RateGroups->SetRowColumnSpan(6);
		}
		
		Table()->RateGroups->Render();
	}
	
	
}

?>
