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
		
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("available_plans_page");
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
	
		$arrRatePlans			= DBO()->RatePlans->AsArray->Value;
		$intServiceTypeFilter	= $_SESSION['AvailablePlansPage']['Filter']['ServiceType'];
		$intCustomerGroupFilter	= $_SESSION['AvailablePlansPage']['Filter']['CustomerGroup'];
		$intStatusFilter		= $_SESSION['AvailablePlansPage']['Filter']['Status'];
	
		// Build the contents for the ServiceType filter combobox
		$strServiceTypeFilterOptions = "<option value='0' ". (($intServiceTypeFilter == 0)? "selected='selected'" : "") .">All Service Types</option>\n";
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intServiceType=>$arrServiceType)
		{
			$strSelected					= ($intServiceTypeFilter == $intServiceType) ? "selected='selected'" : "";
			$strServiceTypeFilterOptions	.= "<option value='$intServiceType' $strSelected>{$arrServiceType['Description']}</option>\n";
		}

		// Build the contents for the CustomerGroup filter combobox
		$strCustomerGroupFilterOptions = "<option value='0' ". (($intCustomerGroupFilter == 0)? "selected='selected'" : "") .">All Customer Groups</option>\n";
		foreach ($GLOBALS['*arrConstant']['CustomerGroup'] as $intCustomerGroup=>$arrCustomerGroup)
		{
			$strSelected					= ($intCustomerGroupFilter == $intCustomerGroup) ? "selected='selected'" : "";
			$strCustomerGroupFilterOptions	.= "<option value='$intCustomerGroup' $strSelected>{$arrCustomerGroup['Description']}</option>\n";
		}
		
		// Build the contents for the Status filter combobox
		$strStatusFilterOptions = "<option value='-1' ". (($intStatusFilter == -1)? "selected='selected'" : "") .">All</option>\n";
		foreach ($GLOBALS['*arrConstant']['RateStatus'] as $intStatus=>$arrStatus)
		{
			$strSelected			= ($intStatusFilter == $intStatus) ? "selected='selected'" : "";
			$strStatusFilterOptions	.= "<option value='$intStatus' $strSelected>{$arrStatus['Description']}</option>\n";
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
var elmStatusFilter			= \$ID(\"StatusFilter\");
window.location				= \"$strAvailablePlansLink?RatePlan.ServiceType=\"+ elmServiceTypeFilter.value +\"&RatePlan.CustomerGroup=\"+ elmCustomerGroupFilter.value +\"&RatePlan.Status=\"+ elmStatusFilter.value;
";
		
		echo "
<div class='GroupedContent'>
	<!-- <div style='float:left;margin-top:3px'>Service Type</div> -->
	<select id='ServiceTypeFilter' style='float:left;max-width:150px'>$strServiceTypeFilterOptions</select>
	<!-- <div style='float:left;margin-left:20px;margin-top:3px'>Customer Group</div> -->
	<select id='CustomerGroupFilter' style='float:left;margin-left:10px;max-width:160px'>$strCustomerGroupFilterOptions</select>
	<select id='StatusFilter' style='float:left;margin-left:10px;max-width:160px'>$strStatusFilterOptions</select>
	<input type='button' value='Filter' style='float:left;margin-left:20px'onclick='$strFilterButtonOnClickJsCode'></input>
	$strAddNewPlan
	<div style='float:none;clear:both'></div>
</div>
<div class='SmallSeparator'></div>
";

		// Render the header of the Plan Table.  This depends on the privileges of the user
		if ($bolHasPlanEditPerm)
		{
			Table()->PlanTable->SetHeader("&nbsp;", "Name", "&nbsp;", "Customer Group", "Carrier Full Service", "Carrier Pre Selection", "Status", "&nbsp;");
			Table()->PlanTable->SetWidth("4%", "36%", "4%", "20%", "10%", "10%", "12%", "4%");
			Table()->PlanTable->SetAlignment("Left", "Left", "Left", "Left", "Left", "Left", "Left", "Right");
		}
		else
		{
			Table()->PlanTable->SetHeader("&nbsp;", "Name", "&nbsp;", "Customer Group", "Carrier Full Service", "Carrier Pre Selection", "Status");
			Table()->PlanTable->SetWidth("4%", "40%", "4%", "20%", "10%", "10%", "12%");
			Table()->PlanTable->SetAlignment("Left", "Left", "Left", "Left", "Left", "Left", "Left");
		}

		// This array will store the details required for the javascript code that archives a RatePlan
		$arrRatePlanDetails = array();
		
		foreach ($arrRatePlans as $arrRatePlan)
		{
			// Format the Name and Description (The title attribute of the Name will be set to the description)
			$strDescription		= htmlspecialchars($arrRatePlan['Description'], ENT_QUOTES);
			$strName			= htmlspecialchars($arrRatePlan['Name'], ENT_QUOTES);
			$strViewPlanHref	= Href()->ViewPlan($arrRatePlan['Id']);
			$strNameCell		= "<a href='$strViewPlanHref' title='$strDescription'>$strName</a>";
			$strServiceType		= htmlspecialchars(GetConstantDescription($arrRatePlan['ServiceType'], "ServiceType"), ENT_QUOTES);
			$strCustomerGroup	= htmlspecialchars(GetConstantDescription($arrRatePlan['customer_group'], "CustomerGroup"), ENT_QUOTES);
			$strStatusCell		= GetConstantDescription($arrRatePlan['Archived'], "RateStatus");
			
			$strCarrierFullServiceCell	= GetConstantDescription($arrRatePlan['CarrierFullService'], "Carrier");
			$strCarrierPreselectionCell	= GetConstantDescription($arrRatePlan['CarrierPreselection'], "Carrier");
			
			switch ($arrRatePlan['ServiceType'])
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
			
			$strDefaultCell = "";
			if ($arrRatePlan['IsDefault'] == TRUE)
			{
				$strDefaultCell = "<img src='img/template/flag.png' title='Default plan for $strCustomerGroup, $strServiceType services'></img>";
			}
						
			$strServiceTypeCell	= "<div class='$strServiceTypeClass'></div>";
						
			// Add the Rate Plan to the VixenTable
			if ($bolHasPlanEditPerm)
			{
				// User can add and edit Rate Plans
				// Build the Edit Rate Plan link, if the RatePlan is currently a draft
				$strEdit = "";
				if ($arrRatePlan['Archived'] == RATE_STATUS_DRAFT)
				{
					$strEditPlanLink	= Href()->EditRatePlan($arrRatePlan['Id']);
					$strEdit			= "<a href='$strEditPlanLink' title='Edit'><img src='img/template/edit.png'></img></a>";
				}
				
				if ((!$arrRatePlan['IsDefault']) && ($arrRatePlan['Archived'] == RATE_STATUS_ACTIVE || $arrRatePlan['Archived'] == RATE_STATUS_ARCHIVED))
				{
					// The user can toggle the status between Active and Archived
					$strStatusCell = "<span title='Toggle Status' onclick='Vixen.AvailablePlansPage.TogglePlanStatus({$arrRatePlan['Id']})'>$strStatusCell</span>";
				}
				
				// Build the "Add Rate Plan Based On Existing" link
				$strAddPlanLink	= Href()->AddRatePlan($arrRatePlan['Id']);
				$strAdd			= "<a href='$strAddPlanLink' title='Create a new plan based on this one'><img src='img/template/new.png'></img></a>";
				$strActionCell	= "{$strEdit}{$strAdd}";
				
				// Add the row
				Table()->PlanTable->AddRow($strServiceTypeCell, $strNameCell, $strDefaultCell, $strCustomerGroup, $strCarrierFullServiceCell, $strCarrierPreselectionCell, $strStatusCell, $strActionCell);
			}
			else
			{
				
				// User can not Add or Edit Rate Plans
				// Add the Row
				Table()->PlanTable->AddRow($strServiceTypeCell, $strNameCell, $strDefaultCell, $strCustomerGroup, $strCarrierFullServiceCell, $strCarrierPreselectionCell, $strStatusCell);
			}
			
			$arrRatePlanDetails[$arrRatePlan['Id']] = array(	"Name"			=> $strName,
																"CustomerGroup"	=> $strCustomerGroup,
																"ServiceType"	=> $strServiceType,
																"Status"		=> $arrRatePlan['Archived']
															);
		}
		
		// Check if the table is empty
		if (count($arrRatePlans) == 0)
		{
			// There are no RatePlans to stick in this table
			Table()->PlanTable->AddRow("No Rate Plans to display");
			Table()->PlanTable->SetRowAlignment("left");
			$intNumofColumns = ($bolHasPlanEditPerm) ? 8 : 7;
			Table()->PlanTable->SetRowColumnSpan($intNumofColumns);
		}
		else
		{
			Table()->PlanTable->RowHighlighting = TRUE;
		}
		
		Table()->PlanTable->Render();
		
		$objRatePlans = Json()->Encode($arrRatePlanDetails);
		echo "<script type='text/javascript'>Vixen.AvailablePlansPage.Initialise($objRatePlans);</script>";
		
		echo "<div class='SmallSeparator'></div>";
	}
}

?>
