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
			// Render the start of the form
			// Set Up the form for adding a rate plan
			$this->FormStart("AddPlan", "Plan", "Add");
			
			echo "<div id='RatePlanDetailsId'>\n";
			$this->_RenderPlanDetails();
			echo "</div>\n";
	
			// Stick in the div container for the DeclareRateGroups table
			echo "<div id='RateGroupsDiv'></div>\n";
			
			// create the buttons
			echo "<div class='SmallSeperator'></div>\n";
			echo "<div class='Right'>\n";
			$this->AjaxSubmit("Save as Draft");
			$this->AjaxSubmit("Commit");
			echo "</div>\n";
			$this->FormEnd();			
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
		// Define javascript to execute when a value is selected in the ServiceTypeCombo
		$strServiceTypeOnChange = "javascript: 
							var objObjects = {};
							objObjects.RatePlan = {};
							objObjects.RatePlan.ServiceType = this.value;
							Vixen.Ajax.CallAppTemplate('Plan', 'GetRateGroupsForm', objObjects);
							";
	
		echo "<h2 class='Plan'>Plan Details</h2>\n";
		echo "<div class='Wide-Form'>\n";

		// Only apply the output mask if the DBO()->RatePlan is not invalid
		$bolApplyOutputMask = !DBO()->RatePlan->IsInvalid();

		DBO()->RatePlan->Name->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->Description->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->Shared->RenderInput(2);
		DBO()->RatePlan->MinMonthly->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->ChargeCap->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->UsageCap->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->CarrierFullService->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->CarrierPreselection->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		
		// Build the ServiceType Combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Service Type:</div>\n";
		echo "      <select id='ServiceTypeCombo' name='RatePlan.ServiceType' class='DefaultInputComboBox' style='width:152px;' onchange=\"$strServiceTypeOnChange\">\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>\n";
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intKey=>$arrValue)
		{
			if (DBO()->RatePlan->ServiceType->Value == $intKey)
			{
				echo "         <option value='". $intKey ."' selected='selected'>". $arrValue['Description'] ."</option>\n";
			}
			else
			{
				echo "         <option value='". $intKey ."'>". $arrValue['Description'] ."</option>\n";
			}
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
		Table()->RateGroups->SetHeader("&nbsp;", "Record Type", "Rate Group", "Fleet Rate Group", "&nbsp;");
		Table()->RateGroups->SetWidth("1%", "29%", "30%", "30%", "10%");
		Table()->RateGroups->SetAlignment("Center", "Left", "Left", "Left", "Center");
		
		foreach (DBL()->RecordType as $dboRecordType)
		{
			// build the Record Type cell
			$strRequiredCell = "&nbsp;";
			if ($dboRecordType->Required->Value)
			{
				$strRequiredCell = "<span class='RequiredInput'>*</span>";
			}
			
			$strRecordTypeCell = $dboRecordType->Name->AsValue();
			
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
					if (DBO()->{$strObject}->{$strProperty}->Value == $dboRateGroup->Id->Value)
					{
						// This option is currently selected
						$strRateGroupCell .= "         <option value='". $dboRateGroup->Id->Value ."' selected='selected'>". $dboRateGroup->Name->AsValue() ."</option>\n";
					}
					else
					{
						// This option is not selected
						$strRateGroupCell .= "         <option value='". $dboRateGroup->Id->Value ."'>". $dboRateGroup->Name->AsValue() ."</option>\n";
					}
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
					if (DBO()->{$strObject}->{$strProperty}->Value == $dboRateGroup->Id->Value)
					{
						// This option is currently selected
						$strFleetRateGroupCell .= "         <option value='". $dboRateGroup->Id->Value ."' selected='selected'>". $dboRateGroup->Name->AsValue() ."</option>\n";
					}
					else
					{
						// This option is not selected
						$strFleetRateGroupCell .= "         <option value='". $dboRateGroup->Id->Value ."'>". $dboRateGroup->Name->AsValue() ."</option>\n";
					}
				}
			}
			$strFleetRateGroupCell .= "      </select>\n";
			$strFleetRateGroupCell .= "   </div>\n";
			$strFleetRateGroupCell .= "</div>\n";
			
			$strAddRateGroupHref = Href()->AddRateGroupToRatePlan($dboRecordType->Id->Value);
			$strActionsCell = "<span class='DefaultOutputSpan'><a href='$strAddRateGroupHref' style='color:blue; text-decoration: none;'>Add New</a></span>";
			
			// Add this row to the table
			Table()->RateGroups->AddRow($strRequiredCell, $strRecordTypeCell, $strRateGroupCell, $strFleetRateGroupCell, $strActionsCell);
		}
		
		if (DBL()->RecordType->RecordCount() == 0)
		{
			$strServiceType = DBO()->RatePlan->ServiceType->AsCallback("GetConstantDescription", Array("ServiceType"));
			// There are no RecordTypes required for the ServiceType chosen
			Table()->RateGroups->AddRow("<span class='DefaultOutputSpan Default'>No Record Types required for Service Type: $strServiceType</span>");
			Table()->RateGroups->SetRowAlignment("left");
			Table()->RateGroups->SetRowColumnSpan(5);
		}
		
		Table()->RateGroups->Render();
	}
	
	
}

?>
