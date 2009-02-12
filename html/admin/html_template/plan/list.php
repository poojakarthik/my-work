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
		foreach ($GLOBALS['*arrConstant']['service_type'] as $intServiceType=>$arrServiceType)
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

		// Render the header of the Plan Table
		Table()->PlanTable->SetHeader("&nbsp;", "Name", "&nbsp;", "Customer Group", "Carrier Full Service", "Carrier Pre Selection", "Status", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;");
		Table()->PlanTable->SetWidth("4%", "34%", "4%", "18%", "10%", "10%", "12%", "2%", "2%", "2%", "2%");
		Table()->PlanTable->SetAlignment("Left", "Left", "Left", "Left", "Left", "Left", "Left", "Center", "Center", "Center", "Center");
		
		// This array will store the details required for the javascript code that archives a RatePlan
		$arrRatePlanDetails = array();
		
		foreach ($arrRatePlans as $arrRatePlan)
		{
			// Format the Name and Description (The title attribute of the Name will be set to the description)
			$strDescription		= htmlspecialchars($arrRatePlan['Description'], ENT_QUOTES);
			$strName			= htmlspecialchars($arrRatePlan['Name'], ENT_QUOTES);
			$strViewPlanHref	= Href()->ViewPlan($arrRatePlan['Id']);
			$strNameCell		= "<a href='$strViewPlanHref' title='$strDescription'>$strName</a>";
			$strServiceType		= htmlspecialchars(GetConstantDescription($arrRatePlan['ServiceType'], "service_type"), ENT_QUOTES);
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
			
			if ((!$arrRatePlan['IsDefault']) && ($arrRatePlan['Archived'] == RATE_STATUS_ACTIVE || $arrRatePlan['Archived'] == RATE_STATUS_ARCHIVED))
			{
				// The user can toggle the status between Active and Archived
				$strStatusCell = "<span title='Toggle Status' onclick='Vixen.AvailablePlansPage.TogglePlanStatus({$arrRatePlan['Id']})'>$strStatusCell</span>";
			}
			
			$strCustomerGroup	= GetConstantDescription($arrRatePlan['customer_group'], 'CustomerGroup');
			
			// Build the Plan Brochure link
			$strBrochureCell	= '';
			if ($arrRatePlan['brochure_document_id'])
			{
				$objBrochureDocument		= new Document(array('id'=>$arrRatePlan['brochure_document_id']), true);
				$objBrochureDocumentContent	= $objBrochureDocument->getContent();
				
				if ($objBrochureDocumentContent && $objBrochureDocumentContent->content)
				{
					$objBrochureIcon			= new File_Type(array('id'=>$objBrochureDocumentContent->file_type_id), true);
					
					$strImageSrc		= "../admin/reflex.php/File/Image/FileTypeIcon/{$objBrochureIcon->id}/16x16";
					$strBrochureLink	= "../admin/reflex.php/File/Document/{$arrRatePlan['brochure_document_id']}";
					$strBrochureCell	= "<a href='{$strBrochureLink}' title='Download Plan Brochure'><img src='{$strImageSrc}' alt='Download Plan Brochure' /></a>";
					
					$objEmployee		= Employee::getForId(Flex::getUserId());
					$strEmails			= "new Array(\"{$objEmployee->Email}\")";
					$strSubject			= "{$strCustomerGroup} Plan Brochures";
					$strContent			= "Dear <Addressee>\\n\\nPlease find attached the Plan Brochures you requested.\n\nRegards\n<Sender>";
					$strEmailOnClick	= "JsAutoLoader.loadScript(\"javascript/document.js\", function(){Flex.Document.emailDocument({$arrRatePlan['brochure_document_id']}, \"Plan Brochure for {$arrRatePlan['Name']}\", {$strEmails}, \"{$strSubject}\", \"{$strContent}\")});";
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
			
			// Build the Voice Auth Script link
			$strVoiceAuthCell	= '';
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
				$strActionCell	= "{$strEdit}{$strAdd}";
			}
			
			// Add the row
			Table()->PlanTable->AddRow($strServiceTypeCell, $strNameCell, $strDefaultCell, $strCustomerGroup, $strCarrierFullServiceCell, $strCarrierPreselectionCell, $strStatusCell, $strBrochureCell, $strVoiceAuthCell, $strEditCell, $strAddCell);
			
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
		
		//echo "<div class='GroupedContent'>".str_replace("\n", "\n<br />", print_r($arrRatePlans, true))."</div>";
	}
}

?>
