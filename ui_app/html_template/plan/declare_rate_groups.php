<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// declare_rate_groups.php
//----------------------------------------------------------------------------//
/**
 * declare_rate_groups
 *
 * HTML Template for the Declare Rate Groups table HTML object
 *
 * HTML Template for the Declare Rate Groups table HTML object 
 * This is used in the "Add Rate Plan" page to declare which rate groups will be used by the plan
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays all invoices relating to an account and can be embedded in
 * various Page Templates
 *
 * @file		declare_rate_groups.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplatePlanDeclareRateGroups
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanDeclareRateGroups
 *
 * HTML Template class for the Declare Rate Groups table HTML object
 *
 * HTML Template class for the Declare Rate Groups table HTML object
 * For each RecordType used by the ServiceType of the plan, this will display 
 * a row in the table with comboboxes to select which rate groups to use
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanDeclareRateGroups
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanDeclareRateGroups extends HtmlTemplate
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
		
		// Load all java script specific to the page here
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
			case HTML_CONTEXT_NO_DETAIL:
				// Don't do anything
				break;
			default:
				$this->_RenderNormal();
				break;
		}
	}
	
	private function _RenderNormal()
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
			
			// Retrieve all non-fleet rate groups applicable to this recordType
			DBL()->RateGroup->Where->Clean();
			DBL()->RateGroup->RecordType = $dboRecordType->Id->Value;
			DBL()->RateGroup->Archived = 0;
			DBL()->RateGroup->Load();
			
			// Build the RateGroup Combobox
			$strId		= "RateGroup_" . $dboRecordType->Id->Value;
			$strName	= "Rate.RateGroup_" . $dboRecordType->Id->Value;
			$strRateGroupCell  = "<div class='DefaultElement'>\n";
			$strRateGroupCell .= "   <div class='DefaultInputSpan'>\n";
			$strRateGroupCell .= "      <select id='$strId' name='$strName' style='width:100%'>\n";
			if (DBO()->RatePlan->{$strName}->Value == 0)
			{
				// this option is currently selected
				$strRateGroupCell .= "         <option value='0' selected='selected'>&nbsp;</option>\n";
			}
			else
			{
				// this option is no selected
				$strRateGroupCell .= "         <option value='0'>&nbsp;</option>\n";
			}
			foreach (DBL()->RateGroup as $dboRateGroup)
			{
				if ($dboRateGroup->Fleet->Value == FALSE)
				{
					if (DBO()->RatePlan->{$strName}->Value == $dboRateGroup->Id->Value)
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
			$strId		= "FleetRateGroup_" . $dboRecordType->Id->Value;
			$strName	= "Rate.FleetRateGroup_" . $dboRecordType->Id->Value;
			$strFleetRateGroupCell  = "<div class='DefaultElement'>\n";
			$strFleetRateGroupCell .= "   <div class='DefaultInputSpan'>\n";
			$strFleetRateGroupCell .= "      <select id='$strId' name='$strName' style='width:100%'>\n";
			if (DBO()->RatePlan->{$strName}->Value == 0)
			{
				// this option is currently selected
				$strFleetRateGroupCell .= "         <option value='0' selected='selected'>&nbsp;</option>\n";
			}
			else
			{
				// this option is no selected
				$strFleetRateGroupCell .= "         <option value='0'>&nbsp;</option>\n";
			}
			foreach (DBL()->RateGroup as $dboRateGroup)
			{
				if ($dboRateGroup->Fleet->Value == TRUE)
				{
					if (DBO()->RatePlan->{$strName}->Value == $dboRateGroup->Id->Value)
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
			
			$strActionsCell = "Add New";
			
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
		//echo "<div class='Seperator'></div>\n";
		
		
	}
}

?>
