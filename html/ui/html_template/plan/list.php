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
	
		$intServiceTypeFilter	= $_SESSION['AvailablePlansPage']['Filter']['ServiceType'];
		$intCustomerGroupFilter	= $_SESSION['AvailablePlansPage']['Filter']['CustomerGroup'];	
	
		// Build the contents for the ServiceType filter combobox
		$strServiceTypeFilterOptions = "<option value='0' ". (($intServiceTypeFilter == 0)? "selected='selected'" : "") .">All</option>\n";
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intServiceType=>$arrServiceType)
		{
			$strSelected					= ($intServiceTypeFilter == $intServiceType) ? "selected='selected'" : "";
			$strServiceTypeFilterOptions	.= "<option value='$intServiceType' $strSelected>{$arrServiceType['Description']}</option>\n";
		}

		// Build the contents for the CustomerGroup filter combobox
		$strCustomerGroupFilterOptions = "<option value='0' ". (($intCustomerGroupFilter == 0)? "selected='selected'" : "") .">All</option>\n";
		foreach ($GLOBALS['*arrConstant']['CustomerGroup'] as $intCustomerGroup=>$arrCustomerGroup)
		{
			$strSelected					= ($intCustomerGroupFilter == $intCustomerGroup) ? "selected='selected'" : "";
			$strCustomerGroupFilterOptions	.= "<option value='$intCustomerGroup' $strSelected>{$arrCustomerGroup['Description']}</option>\n";
		}
		
		$strNewBlankRatePlanLink = Href()->AddRatePlan();
		
		// Build the button to add a new plan
		$strAddNewPlan = "";
		if ($bolHasPlanEditPerm)
		{
			$strAddNewPlan = "<input type='button' value='Add New Plan' style='float:right' onclick='window.location=\"$strNewBlankRatePlanLink\"'></input>";
		}
		
		// Build the filter button onClick script
		$strAvailablePlansLink			= Href()->AvailablePlans();
		$strFilterButtonOnClickJsCode	= "
var elmServiceTypeFilter	= \$ID(\"ServiceTypeFilter\");
var elmCustomerGroupFilter	= \$ID(\"CustomerGroupFilter\");
window.location				= \"$strAvailablePlansLink?RatePlan.ServiceType=\"+ elmServiceTypeFilter.value +\"&RatePlan.CustomerGroup=\"+ elmCustomerGroupFilter.value;
";
		
		echo "
<div class='GroupedContent'>
	<div style='float:left'>Service Type</div>
	<select id='ServiceTypeFilter' style='float:left;margin-left:10px;max-width:150px'>$strServiceTypeFilterOptions</select>
	<div style='float:left;margin-left:20px'>Customer Group</div>
	<select id='CustomerGroupFilter' style='float:left;margin-left:10px;max-width:150px'>$strCustomerGroupFilterOptions</select>
	<input type='button' value='Filter' style='float:left;margin-left:20px'onclick='$strFilterButtonOnClickJsCode'></input>
	$strAddNewPlan
	<div style='float:none;clear:both'></div>
</div>
<div class='SmallSeparator'></div>
";

		// Render the header of the Plan Table.  This depends on the privileges of the user
		if ($bolHasPlanEditPerm)
		{
			Table()->PlanTable->SetHeader("&nbsp;", "Name", "Customer Group", "Status", "&nbsp;");
			Table()->PlanTable->SetWidth("4%", "50%", "30%", "12%", "4%");
			Table()->PlanTable->SetAlignment("Left", "Left", "Left", "Center", "Right");
		}
		else
		{
			Table()->PlanTable->SetHeader("&nbsp;", "Name", "Customer Group", "Status");
			Table()->PlanTable->SetWidth("4%", "54%", "30%", "12%");
			Table()->PlanTable->SetAlignment("Left", "Left", "Left", "Center");
		}

		foreach (DBL()->RatePlan as $dboRatePlan)
		{
			// Workout the status of the Rate Plan
			$strStatusCell = GetConstantDescription($dboRatePlan->Archived->Value, "RateStatus");
			
			// Format the Name and Description (The title attribute of the Name will be set to the description)
			$strDescription		= htmlspecialchars($dboRatePlan->Description->Value, ENT_QUOTES);
			$strName			= $dboRatePlan->Name->FormattedValue();
			$strViewPlanHref	= Href()->ViewPlan($dboRatePlan->Id->Value);
			$strNameCell		= "<a href='$strViewPlanHref' title='$strDescription'>$strName</a>";
			
			switch ($dboRatePlan->ServiceType->Value)
			{
				case SERVICE_TYPE_MOBILE:
					$strServiceTypeClass = "ServiceTypeIconMobile";
					break;
				case SERVICE_TYPE_LAND_LINE:
					$strServiceTypeClass = "ServiceTypeIconLandLine";
					break;
				case SERVICE_TYPE_ADSL:
					$strServiceTypeClass = "ServiceTypeIconADSL";
					break;
				case SERVICE_TYPE_INBOUND:
					$strServiceTypeClass = "ServiceTypeIconInbound";
					break;
				default:
					$strServiceTypeClass = "ServiceTypeIconBlank";
					break;
			}
			
			$strServiceTypeCell	= "<div class='$strServiceTypeClass'></div>";
			$strCustomerGroup	= GetConstantDescription($dboRatePlan->customer_group->Value, "CustomerGroup");
			
			// Add the Rate Plan to the VixenTable
			if ($bolHasPlanEditPerm)
			{
				// User can add and edit Rate Plans
				// Build the Edit Rate Plan link, if the RatePlan is currently a draft
				$strEdit = "";
				if ($dboRatePlan->Archived->Value == RATE_STATUS_DRAFT)
				{
					$strEditPlanLink	= Href()->EditRatePlan($dboRatePlan->Id->Value);
					$strEdit			= "<a href='$strEditPlanLink' title='Edit'><img src='img/template/edit.png'></img></a>";
				}
				
				// Build the "Add Rate Plan Based On Existing" link
				$strAddPlanLink	= Href()->AddRatePlan($dboRatePlan->Id->Value);
				$strAdd			= "<a href='$strAddPlanLink' title='Create a new plan based on this one'><img src='img/template/new.png'></img></a>";
				$strActionCell	= "{$strEdit}{$strAdd}";
				
				// Add the row
				Table()->PlanTable->AddRow($strServiceTypeCell, $strNameCell, $strCustomerGroup, $strStatusCell, $strActionCell);
			}
			else
			{
				// User can not Add or Edit Rate Plans
				// Add the Row
				Table()->PlanTable->AddRow($strServiceTypeCell, $strNameCell, $strCustomerGroup, $strStatusCell);
			}
			

/* Don't include drop down details
			$strDetail = "<table border='0' cellspacing='0' cellpadding='0' width='100%' style='background-color:#D1D1D1'><tr>\n";
			$strDetail .= "<td width='50%'>\n";
			
			$intFullService = $dboRatePlan->CarrierFullService->Value;
			$strFullService = (!isset($GLOBALS['*arrConstant']['Carrier'][$intFullService]))? "[Not Specified]" : $GLOBALS['*arrConstant']['Carrier'][$intFullService]['Description'];
			$strDetail .= $dboRatePlan->CarrierFullService->AsArbitrary($strFullService, RENDER_OUTPUT);
			
			$intPreselection = $dboRatePlan->CarrierPreselection->Value;
			$strPreselection = (!isset($GLOBALS['*arrConstant']['Carrier'][$intPreselection]))? "[Not Specified]" : $strPreselection = $GLOBALS['*arrConstant']['Carrier'][$intPreselection]['Description'];
	
			$strDetail .= $dboRatePlan->CarrierPreselection->AsArbitrary($strPreselection, RENDER_OUTPUT);
			$strDetail .= $dboRatePlan->Shared->AsOutput();
			$strDetail .= $dboRatePlan->InAdvance->AsOutput();
			$strContractTerm = (DBO()->RatePlan->ContractTerm->Value == NULL)? "[Not Specified]" : DBO()->RatePlan->ContractTerm->Value;
			$strDetail .= $dboRatePlan->ContractTerm->AsArbitrary($strContractTerm, RENDER_OUTPUT);
			$strDetail .= "</td><td width='50%'>\n";
					
			$strDetail .= $dboRatePlan->MinMonthly->AsOutput();
			$strDetail .= $dboRatePlan->ChargeCap->AsOutput();
			$strDetail .= $dboRatePlan->UsageCap->AsOutput();
			$strDetail .= $dboRatePlan->RecurringCharge->AsOutput();
			
			if ($dboRatePlan->discount_cap->Value == NULL)
			{
				$strDetail .= $dboRatePlan->discount_cap->AsArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
			else
			{
				$strDetail .= $dboRatePlan->discount_cap->AsOutput();
			}
			
			$strDetail .= "</td></tr></table>\n";
			Table()->PlanTable->SetDetail($strDetail);
*/

			
			
		}
		
		// Check if the table is empty
		if (Table()->PlanTable->RowCount() == 0)
		{
			// There are no RatePlans to stick in this table
			Table()->PlanTable->AddRow("No Rate Plans to display");
			Table()->PlanTable->SetRowAlignment("left");
			$intNumofColumns = ($bolHasPlanEditPerm) ? 5 : 4;
			Table()->PlanTable->SetRowColumnSpan($intNumofColumns);
		}
		else
		{
			Table()->PlanTable->RowHighlighting = TRUE;
		}
		
		Table()->PlanTable->Render();
		echo "<div class='SmallSeparator'></div>";
	}
}

?>
