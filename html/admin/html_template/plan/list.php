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
		$bolHasPlanEditPerm = (AuthenticatedUser()->UserHasPerm(PERMISSION_RATE_MANAGEMENT) ||  AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN));

		$arrRatePlans			= DBO()->RatePlans->AsArray->Value;
		$intServiceTypeFilter	= $_SESSION['AvailablePlansPage']['Filter']['ServiceType'];
		$intCustomerGroupFilter	= $_SESSION['AvailablePlansPage']['Filter']['CustomerGroup'];
		$intStatusFilter		= $_SESSION['AvailablePlansPage']['Filter']['Status'];

		// Build the contents for the ServiceType filter combobox
		$strServiceTypeFilterOptions = "<option value='0' ". (($intServiceTypeFilter == 0)? "selected='selected'" : "") .">All Service Types</option>\n";
		// foreach ($GLOBALS['*arrConstant']['service_type'] as $intServiceType=>$arrServiceType)
		foreach (Service_Type::getAll() as $oServiceType) {
			$strSelected					= ($intServiceTypeFilter == $oServiceType->id) ? "selected='selected'" : "";
			$strServiceTypeFilterOptions	.= "<option value='{$oServiceType->id}' $strSelected>{$oServiceType->description}</option>\n";
		}

		// Build the contents for the CustomerGroup filter combobox
		$strCustomerGroupFilterOptions = "<option value='0' ". (($intCustomerGroupFilter == 0)? "selected='selected'" : "") .">All Customer Groups</option>\n";
		$arrCustomerGroups	= Customer_Group::getAll();
		foreach ($arrCustomerGroups as $intCustomerGroup=>$objCustomerGroup)
		{
			$strSelected					= ($intCustomerGroupFilter == $intCustomerGroup) ? "selected='selected'" : "";
			$strCustomerGroupFilterOptions	.= "<option value='$intCustomerGroup' $strSelected>{$objCustomerGroup->internalName}</option>\n";
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
	<select id='ServiceTypeFilter' style='float:left;max-width:200px'>$strServiceTypeFilterOptions</select>
	<!-- <div style='float:left;margin-left:20px;margin-top:3px'>Customer Group</div> -->
	<select id='CustomerGroupFilter' style='float:left;margin-left:10px;max-width:160px'>$strCustomerGroupFilterOptions</select>
	<select id='StatusFilter' style='float:left;margin-left:10px;max-width:160px'>$strStatusFilterOptions</select>
	<input type='button' value='Filter' style='float:left;margin-left:20px'onclick='$strFilterButtonOnClickJsCode'></input>
	$strAddNewPlan
	<div style='float:none;clear:both'></div>
</div>
<div class='SmallSeparator'></div>
";

		if (Flex_Module::isActive(FLEX_MODULE_PLAN_BROCHURE))
		{
			$strWithSelectedEmailOnClick	= "Vixen.AvailablePlansPage.emailSelectedBrochures();";
			echo "<div class='GroupedContent'><span style='font-weight:bold;'>With Selected : </span><a onclick='{$strWithSelectedEmailOnClick}'><img src='../admin/img/template/pdf_email.png' alt='Email Plan Brochure' /> Email Brochures</a></div>";
		}

		echo "<div class='SmallSeparator'></div>";

		// Render the header of the Plan Table
		Table()->PlanTable->SetHeader("&nbsp;", "&nbsp;", "Name", "Customer Group", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "Status", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;");
		Table()->PlanTable->SetWidth("2%", "2%", "60%", "20%", "2%", "2%", "2%", "2%", "2%", "2%", "2%", "2%", "2%", "2%", "2%", "2%");
		Table()->PlanTable->SetAlignment("Left", "Center", "Left", "Left", "Left", "Left","Left", "Left", "Left", "Left", "Left", "Left", "Center", "Center", "Center", "Center", "Center");

		// This array will store the details required for the javascript code that archives a RatePlan
		$arrRatePlanDetails = array();

		foreach ($arrRatePlans as $arrRatePlan)
		{
			$bolCanEmail		= false;

			// Format the Name and Description (The title attribute of the Name will be set to the description)
			$strDescription		= htmlspecialchars($arrRatePlan['Description'], ENT_QUOTES);
			$strName			= htmlspecialchars($arrRatePlan['Name'], ENT_QUOTES);
			$strViewPlanHref	= Href()->ViewPlan($arrRatePlan['Id']);
			$strNameCell		= "<a href='$strViewPlanHref' title='$strDescription'>$strName</a>";
			$strServiceType		= htmlspecialchars(GetConstantDescription($arrRatePlan['ServiceType'], "service_type"), ENT_QUOTES);
			$strCustomerGroup	= htmlspecialchars(Customer_Group::getForId($arrRatePlan['customer_group'])->internalName, ENT_QUOTES);
			//$strStatusCell		= GetConstantDescription($arrRatePlan['Archived'], "RateStatus");

			$strStatusCell		= "<img ";
			switch ($arrRatePlan['Archived'])
			{
				case RATE_STATUS_ARCHIVED:
					$strStatusCell	.= "src='../admin/img/template/indicator_inactive.png' alt='".GetConstantDescription($arrRatePlan['Archived'], "RateStatus")."'";
					break;
				case RATE_STATUS_DRAFT:
					$strStatusCell	.= "src='../admin/img/template/indicator_draft.png' alt='".GetConstantDescription($arrRatePlan['Archived'], "RateStatus")."'";
					break;
				case RATE_STATUS_ACTIVE:
					$strStatusCell	.= "src='../admin/img/template/indicator_active.png' alt='".GetConstantDescription($arrRatePlan['Archived'], "RateStatus")."'";
					break;
			}

			$strStatusCell	.= " />";

			$strCarrierFullServiceCell	= ($arrRatePlan['CarrierFullService']) ? Carrier::getForId($arrRatePlan['CarrierFullService'])->description : '';
			$strCarrierPreselectionCell	= ($arrRatePlan['CarrierPreselection']) ? Carrier::getForId($arrRatePlan['CarrierPreselection'])->description : '';

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

			// $strServiceTypeCell	= "<div class='$strServiceTypeClass'></div>";
			$oServiceType = Service_Type::getForId($arrRatePlan['ServiceType']);
			$sServiceTypeNameEncoded = htmlspecialchars($oServiceType->name);
			$strServiceTypeCell = "<img src='../admin/img/template/servicetype/" . strtolower($oServiceType->module) . ".png' alt='{$sServiceTypeNameEncoded}' title='{$sServiceTypeNameEncoded}' />";

			// Attributes
			$strDefaultCell		= ($arrRatePlan['IsDefault'])			? "<img src='img/template/flag.png' title='Default plan for $strCustomerGroup, $strServiceType services' />"						: '&nbsp;';
			$strAdvanceCell		= ($arrRatePlan['InAdvance'])			? "<img src='../admin/img/template/charge_in_advance.png' alt='In Advance' title='Plan Charges are in Advance' />"					: '&nbsp;';
			$strContractCell	= ($arrRatePlan['ContractTerm'] >= 1)	? "<img src='../admin/img/template/contract.png' alt='Contracted' title='{$arrRatePlan['ContractTerm']}-month Contract' />"			: '&nbsp;';
			$strLockedCell		= ($arrRatePlan['locked'])				? "<img src='../admin/img/template/btn_locked.png' alt='Locked' title='Plan Changes are Locked' />"									: '&nbsp;';
			$strCDRRequiredCell	= ($arrRatePlan['cdr_required'])		? "<img src='../admin/img/template/cdr_required.png' alt='CDRs Required' title='Only charge Plan Charge if Service has tolled' />"	: '&nbsp;';
			$strSharedCell		= ($arrRatePlan['Shared'])				? "<img src='../admin/img/template/plan_shared.png' alt='Shared' title='Shared Plan' />"											: '&nbsp;';
			$strCDRHiding		= ($arrRatePlan['allow_cdr_hiding'])	? "<img src='../admin/img/template/cdr_hiding.png' alt='CDRs Hiding' title='Zero-Rated CDRs can be hidden on the Invoice' />"		: '&nbsp;';

			if ((!$arrRatePlan['IsDefault']) && ($arrRatePlan['Archived'] == RATE_STATUS_ACTIVE || $arrRatePlan['Archived'] == RATE_STATUS_ARCHIVED))
			{
				// The user can toggle the status between Active and Archived
				$strStatusCell = "<span title='Toggle Status' onclick='Vixen.AvailablePlansPage.TogglePlanStatus({$arrRatePlan['Id']})'>$strStatusCell</span>";
			}

			$objCustomerGroup	= Customer_Group::getForId($arrRatePlan['customer_group']);
			$strCustomerGroup	= $objCustomerGroup->internalName;

			// Build the Plan Brochure link
			$strBrochureCell	= '';
			if (Flex_Module::isActive(FLEX_MODULE_PLAN_BROCHURE))
			{
				if ($arrRatePlan['brochure_document_id'])
				{
					$objBrochureDocument		= new Document(array('id'=>$arrRatePlan['brochure_document_id']), true);
					$objBrochureDocumentContent	= $objBrochureDocument->getContentDetails();

					if ($objBrochureDocumentContent && $objBrochureDocumentContent->bolHasContent)
					{
						$objBrochureIcon			= new File_Type(array('id'=>$objBrochureDocumentContent->file_type_id), true);

						$strImageSrc		= "../admin/reflex.php/File/Image/FileTypeIcon/{$objBrochureIcon->id}/16x16";
						$strBrochureLink	= "../admin/reflex.php/File/Document/{$arrRatePlan['brochure_document_id']}";
						$strBrochureCell	= "<a href='{$strBrochureLink}' title='Download Plan Brochure'><img src='{$strImageSrc}' alt='Download Plan Brochure' /></a>";

						$bolCanEmail		= true;
						$strEmailOnClick	= Rate_Plan::generateEmailButtonOnClick($arrRatePlan['customer_group'], array($arrRatePlan));
						$strBrochureCell	.= "&nbsp;<a onclick='{$strEmailOnClick}' title='Email Plan Brochure'><img src='../admin/img/template/pdf_email.png' alt='Email Plan Brochure' /></a>";
					}
				}
				elseif ($bolHasPlanEditPerm)
				{
					// Add Brochure link
					$strImageSrc		= "../admin/img/template/pdf_add.png";
					$strBrochureOnClick	= "JsAutoLoader.loadScript(\"javascript/plan.js\", function(){Flex.Plan.setBrochure({$arrRatePlan['Id']}, \"{$arrRatePlan['Name']}\", \"{$strCustomerGroup}\");});";
					$strBrochureCell	= "<a onclick='{$strBrochureOnClick}' title='Attach Plan Brochure'><img src='{$strImageSrc}' alt='Attach Plan Brochure' /></a>";
				}
			}

			// Build the Voice Auth Script link
			$strVoiceAuthCell	= '';
			if (Flex_Module::isActive(FLEX_MODULE_PLAN_AUTH_SCRIPT))
			{
				if ($arrRatePlan['auth_script_document_id'])
				{
					$objAuthScriptDocument			= new Document(array('id'=>$arrRatePlan['auth_script_document_id']), true);
					$objAuthScriptDocumentContent	= $objAuthScriptDocument->getContent();

					if ($objAuthScriptDocumentContent && $objAuthScriptDocumentContent->content)
					{
						$objAuthScriptIcon				= new File_Type(array('id'=>$objAuthScriptDocumentContent->file_type_id), true);

						$strImageSrc		= "../admin/img/template/script.png";
						$strVoiceAuthLink	= "../admin/reflex.php/File/Document/{$arrRatePlan['auth_script_document_id']}";
						$strVoiceAuthCell	= "<a href='{$strVoiceAuthLink}' title='Download Authorisation Script'><img src='{$strImageSrc}' alt='Download Authorisation Script' /></a>";
					}
				}
				elseif ($bolHasPlanEditPerm)
				{
					// Add Voice Auth link
					$strImageSrc			= "../admin/img/template/script_add.png";
					$strAuthScriptOnClick	= "JsAutoLoader.loadScript(\"javascript/plan.js\", function(){Flex.Plan.setAuthScript({$arrRatePlan['Id']}, \"{$arrRatePlan['Name']}\", \"{$strCustomerGroup}\");});";
					$strVoiceAuthCell		= "<a onclick='{$strAuthScriptOnClick}' title='Attach Authorisation Script'><img src='{$strImageSrc}' alt='Attach Authorisation Script' /></a>";
				}
			}

			// Build the "Add Rate Plan Based On Existing" link
			if ($bolHasPlanEditPerm)
			{
				$strEditCell = "";
				if ($arrRatePlan['Archived'] == RATE_STATUS_DRAFT)
				{
					$strEditPlanLink	= Href()->EditRatePlan($arrRatePlan['Id']);
					$strEditCell		= "<a href='$strEditPlanLink' title='Edit'><img src='img/template/edit.png' /></a>";
				}
				else
				{
					// Limited Edit Popup
					// TODO
				}
				$strAddPlanLink	= Href()->AddRatePlan($arrRatePlan['Id']);
				$strAddCell		= "<a href='$strAddPlanLink' title='Create a new plan based on this one'><img src='img/template/new.png' /></a>";
				$strTestCell	= '';
				if ($arrRatePlan['Archived'] == RATE_STATUS_DRAFT)
				{
					$strTestPlanLink	= Href()->TestRatePlan($arrRatePlan['Id']);
					$strTestCell		= "<a href=\"$strTestPlanLink\" title='Test the Plan against an existing Invoice'><img src='img/template/rerate.png'/></a>";
				}
			}

			$strCheckboxCell	= "<input id='RatePlan_{$arrRatePlan['Id']}_Checkbox' name='RatePlan_Checkbox' type='checkbox' onclick='this.checked = !this.checked;' value='{$arrRatePlan['Id']}' />";
			$strCheckboxCell	.= "<input id='RatePlan_{$arrRatePlan['Id']}_Name' type='hidden' value='{$arrRatePlan['Name']}' />";

			if ($bolCanEmail)
			{
				$strCheckboxCell	.= "<input id='RatePlan_{$arrRatePlan['Id']}_BrochureId' type='hidden' value='{$arrRatePlan['brochure_document_id']}' />";
			}

			$strCustomerGroupCell	= "<input id='RatePlan_{$arrRatePlan['Id']}_CustomerGroup' type='hidden' value='{$arrRatePlan['customer_group']}' /><span id='RatePlan_{$arrRatePlan['Id']}_CustomerGroup_Name'>".$strCustomerGroup."</span>";

			// Add the row
			Table()->PlanTable->AddRow(
				$strCheckboxCell,
				$strServiceTypeCell,
				$strNameCell,
				$strCustomerGroupCell,
				$strDefaultCell,
				$strAdvanceCell,
				$strContractCell,
				$strLockedCell,
				$strCDRRequiredCell,
				$strSharedCell,
				$strCDRHiding,
				$strStatusCell,
				"<div class='plan-list-brochure-icons'>{$strBrochureCell}</div>",
				$strVoiceAuthCell,
				$strTestCell,
				$strEditCell,
				$strAddCell
			);
			Table()->PlanTable->SetOnClick("\$ID('RatePlan_{$arrRatePlan['Id']}_Checkbox').checked = !\$ID('RatePlan_{$arrRatePlan['Id']}_Checkbox').checked;");

			$arrRatePlanDetails[$arrRatePlan['Id']] = array(	"Name"			=> $strName,
																"CustomerGroup"	=> $strCustomerGroup,
																"ServiceType"	=> $strServiceType,
																"Status"		=> $arrRatePlan['Archived'],
																"DealerCount"	=> $arrRatePlan['DealerCount']
															);
		}

		// Check if the table is empty
		if (count($arrRatePlans) == 0)
		{
			// There are no RatePlans to stick in this table
			Table()->PlanTable->AddRow("No Rate Plans to display");
			Table()->PlanTable->SetRowAlignment("left");
			Table()->PlanTable->SetRowColumnSpan(17);
		}
		else
		{
			Table()->PlanTable->RowHighlighting = TRUE;
		}

		Table()->PlanTable->Render();

		$objRatePlans = Json()->Encode($arrRatePlanDetails);
		echo "<script type='text/javascript'>Vixen.AvailablePlansPage.Initialise($objRatePlans);</script>";

		echo "<div class='SmallSeparator'></div>";

		if (Flex_Module::isActive(FLEX_MODULE_PLAN_BROCHURE))
		{
			echo "<div class='GroupedContent'><span style='font-weight:bold;'>With Selected : </span><a onclick='{$strWithSelectedEmailOnClick}'><img src='../admin/img/template/pdf_email.png' alt='Email Plan Brochure' /> Email Brochures</a></div>";
		}

		echo "<div class='SmallSeparator'></div>";
		//echo "<div class='GroupedContent'>".str_replace("\n", "\n<br />", print_r($arrRatePlans, true))."</div>";
	}
}

?>
