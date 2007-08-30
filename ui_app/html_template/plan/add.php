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
			
			echo "<div id='RatePlanDetailsId'>\n";
			$this->_RenderPlanDetails();
			echo "</div>\n";
	
			// Stick in the div container for the DeclareRateGroups table
			echo "<div id='RateGroupsDiv'></div>\n";
			
			// Create the buttons
			// Workout where to go if the user clicks on the Cancel button
			$strOnCancelRelocation = (DBO()->CallingPage->Href->IsSet) ? DBO()->CallingPage->Href->Value : Href()->AdminConsole();
			echo "<div class='SmallSeperator'></div>\n";
			echo "<div class='Right'>\n";
			$this->Button("Cancel", "location.href=\"$strOnCancelRelocation\"");
			$this->AjaxSubmit("Save as Draft");
			$this->AjaxSubmit("Commit");
			echo "</div>\n";
			$this->FormEnd();
			
			// Initialise the Rate Groups Assocciated with this form
			$intServiceType = DBO()->RatePlan->ServiceType->Value;
			echo "<script type='text/javascript'>Vixen.RatePlanAdd.ChangeServiceType(". $intServiceType .");</script>\n";
			
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
		echo "<div class='Wide-Form'>\n";

		// Only apply the output mask if the DBO()->RatePlan is not invalid
		$bolApplyOutputMask = !DBO()->RatePlan->IsInvalid();

		DBO()->RatePlan->Name->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->Description->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->Shared->RenderInput(2, TRUE);  //BUG:If this is checked then the conditional contexts make it render using context 1 instead of this one
		DBO()->RatePlan->MinMonthly->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->ChargeCap->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->UsageCap->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		
		// Retrieve a list of carriers
		DBL()->Carrier->OrderBy(Name);
		DBL()->Carrier->Load();
		
		// Build the CarrierFullService combo box
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Carrier Full Service :</div>\n";
		echo "      <select id='CarrierFullServiceCombo' name='RatePlan.CarrierFullService' class='DefaultInputComboBox' style='width:152px;'>\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>\n";
		foreach (DBL()->Carrier as $dboCarrier)
		{
			// Flag the option as being selected if it is the currently selected CarrierFullService
			$strSelected = (DBO()->RatePlan->CarrierFullService->Value == $dboCarrier->Id->Value) ? "selected='selected'" : "";
			echo "         <option value='". $dboCarrier->Id->Value ."' $strSelected>". $dboCarrier->Name->Value ."</option>\n";
		}
		echo "      </select>\n";
		echo "</div>\n"; // DefaultElement
		
		// Build the CarrierPreselection combo box
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Carrier Preselection :</div>\n";
		echo "      <select id='CarrierPreselectionCombo' name='RatePlan.CarrierPreselection' class='DefaultInputComboBox' style='width:152px;'>\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>\n";
		foreach (DBL()->Carrier as $dboCarrier)
		{
			// Flag the option as being selected if it is the currently selected CarrierFullService
			$strSelected = (DBO()->RatePlan->CarrierPreselection->Value == $dboCarrier->Id->Value) ? "selected='selected'" : "";
			echo "         <option value='". $dboCarrier->Id->Value ."' $strSelected>". $dboCarrier->Name->Value ."</option>\n";
		}
		echo "      </select>\n";
		echo "</div>\n"; // DefaultElement
		
		// Build the ServiceType Combobox
		if (DBO()->RatePlan->Id->Value > 0)
		{
			// Disable the ServiceType Combobox
			$strServiceTypeDisabled = "disabled='disabled'";
		}
		
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Service Type :</div>\n";
		echo "      <select id='ServiceTypeCombo' name='RatePlan.ServiceType' class='DefaultInputComboBox' style='width:152px;' onchange='javascript: Vixen.RatePlanAdd.ChangeServiceType(this.value);' $strServiceTypeDisabled>\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>\n";
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intKey=>$arrValue)
		{
			// Flag the option as being selected if it is the currently selected ServiceType
			$strSelected = (DBO()->RatePlan->ServiceType->Value == $intKey) ? "selected='selected'" : "";
			echo "         <option value='". $intKey ."' $strSelected>". $arrValue['Description'] ."</option>\n";
		}
		echo "      </select>\n";
		echo "</div>\n"; // DefaultElement
		
		echo "</div>\n"; // Narrow-Form
		echo "<div class='SmallSeperator'></div>\n";
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
		Table()->RateGroups->SetHeader("&nbsp;", "Record Type", "Rate Group", "&nbsp;", "Fleet Rate Group", "&nbsp;", "&nbsp;");
		Table()->RateGroups->SetWidth("1%", "24%", "30%", "5%", "30%", "5%", "5%");
		Table()->RateGroups->SetAlignment("Center", "Left", "Left", "Center", "Left", "Center", "Center");
		
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
					$strRateGroupCell .= "<option value='". $dboRateGroup->Id->Value ."' $strSelected $strDraft>". $strName ."</option>";
				}
			}
			$strRateGroupCell .= "      </select>\n";
			$strRateGroupCell .= "   </div>\n";
			$strRateGroupCell .= "</div>\n";
			
			// Build the Edit Rate Group Button
			$strEditRateGroupHref = "javascript: Vixen.RatePlanAdd.EditRateGroup(". $dboRecordType->Id->Value .", false)";
			$strEditRateGroupCell = "<span class='DefaultOutputSpan'><a href='$strEditRateGroupHref' style='color:blue; text-decoration: none;'>Edit</a></span>";
			
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

			// Build the Edit Fleet Rate Group Button
			$strEditRateGroupHref = "javascript: Vixen.RatePlanAdd.EditRateGroup(". $dboRecordType->Id->Value .", true)";
			$strEditFleetRateGroupCell = "<span class='DefaultOutputSpan'><a href='$strEditRateGroupHref' style='color:blue; text-decoration: none;'>Edit</a></span>";

			// Build the Add Rate Group Button
			$strAddRateGroupHref = Href()->AddRateGroupToRatePlan($dboRecordType->Id->Value);
			$strActionsCell = "<span class='DefaultOutputSpan'><a href='$strAddRateGroupHref' style='color:blue; text-decoration: none;'>New</a></span>";
			
			// Add this row to the table
			Table()->RateGroups->AddRow($strRequiredCell, $strRecordTypeCell, $strRateGroupCell, $strEditRateGroupCell, $strFleetRateGroupCell, $strEditFleetRateGroupCell, $strActionsCell);
		}
		
		if (DBL()->RecordType->RecordCount() == 0)
		{
			$strServiceType = DBO()->RatePlan->ServiceType->AsCallback("GetConstantDescription", Array("ServiceType"));
			// There are no RecordTypes required for the ServiceType chosen
			Table()->RateGroups->AddRow("<span class='DefaultOutputSpan Default'>No Record Types required for Service Type: $strServiceType</span>");
			Table()->RateGroups->SetRowAlignment("left");
			Table()->RateGroups->SetRowColumnSpan(7);
		}
		
		Table()->RateGroups->Render();
	}
	
	
}

?>
