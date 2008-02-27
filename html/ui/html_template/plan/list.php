<?php
//----------------------------------------------------------------------------//
// HtmlTemplatePlanList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanList
 *
 * A specific HTML Template object
 *
 * An Plan HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanList
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanList extends HtmlTemplate
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
		
		$this->LoadJavascript("dhtml");
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
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
		// If the user has Rate Management permissions then they can add and edit Plans
		$bolHasPlanEditPerm = AuthenticatedUser()->UserHasPerm(PERMISSION_RATE_MANAGEMENT | PERMISSION_ADMIN);
	
		// Define what happens when the filter combo is used
		$strOnFilterChange = "window.location=\"flex.php/Plan/AvailablePlans/?RatePlan.ServiceType=\" + this.value;";
		
		// Include a container div for the filter and the "Add New Plan" button.  Required as I am floating divs left and right
		echo "<div style='height:30px'>\n";
		
		// Add the Filter Combobox
		echo "<div class='DefaultOutputSpan Left' style='margin-top:4px'>Filter :\n";
		echo "   <select id='FilterCombo' onchange='$strOnFilterChange'>\n";
		// Add the blank option to the Filter combobox
		$strSelected = (!DBO()->RatePlan->ServiceType->Value) ? "selected='selected'" : "";
		echo "      <option value='0' $strSelected>All Rate Plans</option>";
		
		// Add each ServiceType to the Filter combobox
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intServiceType=>$arrServiceType)
		{
			$strDescription = $arrServiceType['Description'];
			$strSelected = (DBO()->RatePlan->ServiceType->Value == $intServiceType) ? "selected='selected'" : "";
			echo "      <option value='$intServiceType' $strSelected>$strDescription</option>\n";
		}
		echo "   </select>\n";
		echo "</div>\n";

		if ($bolHasPlanEditPerm)
		{
			// Render the "Add New Plan" button
			echo "<div class='Right'>\n";
			$this->Button("Add New Plan", "window.location=\"" . Href()->AddRatePlan(NULL, Href()->AvailablePlans(DBO()->RatePlan->ServiceType->Value)) . "\"");
			echo "</div>\n";
		}
		echo "</div>\n";  // Container div

		// Render the header of the Plan Table.  This depends on the privileges of the user
		if ($bolHasPlanEditPerm)
		{
			Table()->PlanTable->SetHeader("Type", "Name", "Shared", "Min Monthly Spend (\$)", "Cap Charge (\$)", "Cap Limit (\$)", "Carrier Full Service", "Carrier Pre selection", "Status", "&nbsp;", "&nbsp;");
			Table()->PlanTable->SetWidth("8%", "20%", "8%", "12%", "12%", "12%", "8%", "8%", "8%", "2%", "2%");
			Table()->PlanTable->SetAlignment("Left", "Left", "Center", "Right", "Right", "Right", "Center", "Center", "Center", "Center", "Center");
		}
		else
		{
			Table()->PlanTable->SetHeader("Type", "Name", "Shared", "Min Monthly Spend (\$)", "Cap Charge (\$)", "Cap Limit (\$)", "Carrier Full Service", "Carrier Pre selection", "Status");
			Table()->PlanTable->SetWidth("10%", "20%", "10%", "12%", "12%", "12%", "8%", "8%", "8%");
			Table()->PlanTable->SetAlignment("Left", "Left", "Center", "Right", "Right", "Right", "Center", "Center", "Center");
		}

		foreach (DBL()->RatePlan as $dboRatePlan)
		{
			// Workout the status of the Rate Plan
			// Note these constants will eventually be declared in vixen/framework/definitions and you will be able to use the GetConstantDescription() function
			$strStatusCell = "<span class='DefaultOutputSpan'>". GetConstantDescription($dboRatePlan->Archived->Value, "RateStatus") ."</span>";
			
			// Format the RatePlan->Shared boolean
			$strSharedCell = OutputMask()->BooleanYesNo($dboRatePlan->Shared->Value);
			$strSharedCell = "<span class='DefaultOutputSpan'>$strSharedCell</span>";
			
			// Format the Name and Description (The title attribute of the Name will be set to the description)
			$strDescription = htmlspecialchars($dboRatePlan->Description->Value, ENT_QUOTES);
			$strName = $dboRatePlan->Name->FormattedValue();
			$strViewPlanHref = Href()->ViewPlan($dboRatePlan->Id->Value);
			$strNameCell = "<a href='$strViewPlanHref'><span class='DefaultOutputSpan' title='$strDescription'>$strName</span></a>";
			
			// Add the Rate Plan to the VixenTable
			if ($bolHasPlanEditPerm)
			{
				// User can add and edit Rate Plans
				// Build the Edit Rate Plan link, if the RatePlan is currently a draft
				$strEditCell = "&nbsp;";
				if ($dboRatePlan->Archived->Value == RATE_STATUS_DRAFT)
				{
					$strEditPlanLink	= Href()->EditRatePlan($dboRatePlan->Id->Value, Href()->AvailablePlans(DBO()->RatePlan->ServiceType->Value));
					$strEditCell		= "<a href='$strEditPlanLink' title='Edit'><img src='img/template/edit.png'></img></a>";
				}
				
				// Build the "Add Rate Plan Based On Existing" link
				$strAddPlanLink	= Href()->AddRatePlan($dboRatePlan->Id->Value, Href()->AvailablePlans(DBO()->RatePlan->ServiceType->Value));
				$strAddCell = "<a href='$strAddPlanLink' title='Create a new plan based on this one'><img src='img/template/new.png'></img></a>";
				
				// Add the row
				Table()->PlanTable->AddRow(	$dboRatePlan->ServiceType->AsCallBack("GetConstantDescription", Array('ServiceType')),
											$strNameCell,
											$strSharedCell,
											$dboRatePlan->MinMonthly->AsValue(),
											$dboRatePlan->ChargeCap->AsValue(),
											$dboRatePlan->UsageCap->AsValue(),
											$dboRatePlan->CarrierFullService->AsCallBack("GetConstantDescription", Array('Carrier')),
											$dboRatePlan->CarrierPreselection->AsCallBack("GetConstantDescription", Array('Carrier')),
											$strStatusCell,
											$strEditCell,
											$strAddCell);
			}
			else
			{
				// User can not Add or Edit Rate Plans
				// Add the Row
				Table()->PlanTable->AddRow(	$dboRatePlan->ServiceType->AsCallBack("GetConstantDescription", Array('ServiceType')),
											$strNameCell,
											$strSharedCell,
											$dboRatePlan->MinMonthly->AsValue(),
											$dboRatePlan->ChargeCap->AsValue(),
											$dboRatePlan->UsageCap->AsValue(),
											$dboRatePlan->CarrierFullService->AsCallBack("GetConstantDescription", Array('Carrier')),
											$dboRatePlan->CarrierPreselection->AsCallBack("GetConstantDescription", Array('Carrier')),
											$strStatusCell);
			}
		}
		
		// Check if the table is empty
		if (Table()->PlanTable->RowCount() == 0)
		{
			// There are no RatePlans to stick in this table
			Table()->PlanTable->AddRow("<span class='DefaultOutputSpan Default'>No Rate Plans to display</span>");
			Table()->PlanTable->SetRowAlignment("left");
			$intNumofColumns = ($bolHasPlanEditPerm) ? 11 : 9;
			Table()->PlanTable->SetRowColumnSpan($intNumofColumns);
		}
		
		Table()->PlanTable->Render();
		
		// Render another "Add New Plan" button if the user can
		if ($bolHasPlanEditPerm)
		{
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Add New Plan", "window.location=\"" . Href()->AddRatePlan(NULL, Href()->AvailablePlans()) . "\"");
			echo "</div></div>\n";
		}
		
		echo "<div class='Seperator'></div>\n";
	}
}

?>
