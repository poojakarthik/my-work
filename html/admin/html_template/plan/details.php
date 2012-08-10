<?php
//----------------------------------------------------------------------------//
// HtmlTemplatePlanDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanDetails
 *
 * A specific HTML Template object
 *
 * A Plan details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanDetails extends HtmlTemplate
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
		echo "<h2 class='plan'>Plan Details</h2>\n";
		
		echo "<div class='GroupedContent'>\n";
		
		$bolHasPlanEditPerm = (AuthenticatedUser()->UserHasPerm(PERMISSION_RATE_MANAGEMENT) ||  AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN));
		$arrRatePlan		= DBO()->RatePlan->AsArray();
		
		// Handle the Archived property
		if (DBO()->RatePlan->Archived->Value)
		{
			if (DBO()->RatePlan->Archived->Value == RATE_STATUS_DRAFT)
			{
				// The plan is currently saved as a draft
				echo "<div style='color:#FF0000;text-align:center'>This plan is currently saved as a draft.  It must be committed before it can be applied to services.</div>\n";
			}
			else
			{
				// The plan must be archived
				echo "<div style='color:#FF0000;text-align:center'>This plan has been archived.  It cannot be applied to services.</div>\n";
			}
			echo "<div class='ContentSeparator'></div>\n";
		}
		
		DBO()->RatePlan->Name->RenderOutput();
		DBO()->RatePlan->Description->RenderOutput();
		
		$strCustomerGroup	= Customer_Group::getForId($arrRatePlan['customer_group'])->externalName;
		
		// Build the Plan Brochure link
		$strBrochureCell	= '';
		if (Flex_Module::isActive(FLEX_MODULE_PLAN_BROCHURE))
		{
			if ($arrRatePlan['brochure_document_id'])
			{
				$objBrochureDocument		= new Document(array('id'=>$arrRatePlan['brochure_document_id']), true);
				$objBrochureDocumentContent	= $objBrochureDocument->getContent();
				
				if ($objBrochureDocumentContent && $objBrochureDocumentContent->content)
				{
					$objFileType		= new File_Type(array('id'=>$objBrochureDocumentContent->file_type_id), true);
					
					$strImageSrc		= "../admin/reflex.php/File/Image/FileTypeIcon/{$objFileType->id}/16x16";
					$strBrochureLink	= "../admin/reflex.php/File/Document/{$arrRatePlan['brochure_document_id']}";
					$strBrochureCell	= "<a href='{$strBrochureLink}' title='Download Plan Brochure'><img src='{$strImageSrc}' alt='Download Plan Brochure' /> Download</a>";
					
					$strEmailOnClick	= Rate_Plan::generateEmailButtonOnClick($arrRatePlan['customer_group'], array($arrRatePlan));
					$strBrochureCell	.= "&nbsp;|&nbsp; <a onclick='{$strEmailOnClick}' title='Email Plan Brochure'><img src='../admin/img/template/pdf_email.png' alt='Email Plan Brochure' /> Email this Brochure</a>";
				}
			}
			if ($bolHasPlanEditPerm)
			{
				$strImageSrc	= "../admin/img/template/pdf_add.png";
				$strOnClick		= "JsAutoLoader.loadScript(\"javascript/plan.js\", function(){Flex.Plan.setBrochure({$arrRatePlan['Id']}, \"{$arrRatePlan['Name']}\", \"{$strCustomerGroup}\");});";
				if ($strBrochureCell)
				{
					// Replace Brochure link
					$strBrochureCell	.= " | <a onclick='{$strOnClick}' title='Replace Plan Brochure'><img src='{$strImageSrc}' alt='Replace Plan Brochure' /> Replace this Plan Brochure</a>";
				}
				else
				{
					// Add Brochure link
					$strBrochureCell	= "<a onclick='{$strOnClick}' title='Attach Plan Brochure'><img src='{$strImageSrc}' alt='Attach Plan Brochure' /> Attach a Plan Brochure</a>";
				}
			}
			if (!$strBrochureCell)
			{
				$strBrochureCell	= "No Brochure Attached";
			}
			
			echo "<div class='DefaultElement'>";
			echo "	<div id='RatePlan.brochure_document_id.Output' class='DefaultOutput Default' name='RatePlan.brochure_document_id'>\n";
			echo "		{$strBrochureCell}\n";
			echo "	</div>\n";
			echo "	<div id='RatePlan.brochure_document_id.Label' class='DefaultLabel'>\n";
			echo "		<span>&nbsp;</span>\n";
			echo "		<span id='RatePlan.brochure_document_id.Label.Text'>Brochure : </span>\n";
			echo "	</div>\n";
			echo "</div>";
		}
		
		// Build the Voice Auth Script link
		$strAuthScriptCell	= '';
		if (Flex_Module::isActive(FLEX_MODULE_PLAN_AUTH_SCRIPT))
		{
			if ($arrRatePlan['auth_script_document_id'])
			{
				$objAuthScriptDocument			= new Document(array('id'=>$arrRatePlan['auth_script_document_id']), true);
				$objAuthScriptDocumentContent	= $objAuthScriptDocument->getContentDetails();
				
				if ($objAuthScriptDocumentContent && $objAuthScriptDocumentContent->bolHasContent)
				{
					$objFileType		= new File_Type(array('id'=>$objAuthScriptDocumentContent->file_type_id), true);
					
					$strImageSrc		= "../admin/img/template/script.png";
					$strAuthScriptLink	= "../admin/reflex.php/File/Document/{$arrRatePlan['auth_script_document_id']}";
					$strAuthScriptCell	= "<a href='{$strAuthScriptLink}' title='Download Authorisation Script'><img src='{$strImageSrc}' alt='Download Authorisation Script' /> Download</a>";
				}
			}
			if ($bolHasPlanEditPerm)
			{
				$strImageSrc	= "../admin/img/template/script_add.png";
				$strOnClick		= "JsAutoLoader.loadScript(\"javascript/plan.js\", function(){Flex.Plan.setAuthScript({$arrRatePlan['Id']}, \"{$arrRatePlan['Name']}\", \"{$strCustomerGroup}\");});";
				if ($strAuthScriptCell)
				{
					// Replace Auth Script link
					$strAuthScriptCell	.= " | <a onclick='{$strOnClick}' title='Replace Authorisation Script'><img src='{$strImageSrc}' alt='Replace Authorisation Script' /> Replace this Authorisation Script</a>";
				}
				else
				{
					// Add Auth Script link
					$strAuthScriptCell	= "<a onclick='{$strOnClick}' title='Attach Authorisation Script'><img src='{$strImageSrc}' alt='Attach Authorisation Script' /> Attach an Authorisation Script</a>";
				}
			}
			if (!$strAuthScriptCell)
			{
				$strAuthScriptCell	= "No Authorisation Script Attached";
			}
			
			echo "<div class='DefaultElement'>";
			echo "	<div id='RatePlan.auth_script_document_id.Output' class='DefaultOutput Default' name='RatePlan.auth_script_document_id'>\n";
			echo "		{$strAuthScriptCell}\n";
			echo "	</div>\n";
			echo "	<div id='RatePlan.auth_script_document_id.Label' class='DefaultLabel'>\n";
			echo "		<span>&nbsp;</span>\n";
			echo "		<span id='RatePlan.auth_script_document_id.Label.Text'>Authorisation Script :</span>\n";
			echo "	</div>\n";
			echo "</div>";
		}
		
		// PLAN DETAILS
		echo "<div class='ContentSeparator' ></div>\n";
		echo "<table border='0' cellspacing='0' cellpadding='0' width='100%'><tr>\n";
		echo "<td width='50%'>\n";
		DBO()->RatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT);	
		
		$intFullService = DBO()->RatePlan->CarrierFullService->Value;
		if (!isset($GLOBALS['*arrConstant']['Carrier'][$intFullService]))
		{
			$strFullService = "[Not Specified]";
		}
		else
		{
			$strFullService = $GLOBALS['*arrConstant']['Carrier'][$intFullService]['Description'];
		}
		DBO()->RatePlan->CarrierFullService->RenderArbitrary($strFullService, RENDER_OUTPUT);
		
		$intPreselection = DBO()->RatePlan->CarrierPreselection->Value;
		if (!isset($GLOBALS['*arrConstant']['Carrier'][$intPreselection]))
		{
			$strPreselection = "[Not Specified]";
		}
		else
		{
			$strPreselection = $GLOBALS['*arrConstant']['Carrier'][$intPreselection]['Description'];
		}
		DBO()->RatePlan->CarrierPreselection->RenderArbitrary($strPreselection, RENDER_OUTPUT);
		DBO()->RatePlan->Shared->RenderOutput();
		
		if (DBO()->RatePlan->allow_cdr_hiding->Value)
		{
			DBO()->RatePlan->allow_cdr_hiding->RenderArbitrary('Yes', RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		else
		{
			DBO()->RatePlan->allow_cdr_hiding->RenderArbitrary('No', RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		
		DBO()->RatePlan->InAdvance->RenderOutput();
		if (DBO()->RatePlan->ContractTerm->Value == NULL)
		{
			// There is no contract term
			DBO()->RatePlan->ContractTerm->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		else
		{
			DBO()->RatePlan->ContractTerm->RenderOutput();
			
			// Render Contract Details
			$fltContractExitFee	= (float)DBO()->RatePlan->contract_exit_fee->Value;
			if ($fltContractExitFee > 0)
			{
				DBO()->RatePlan->contract_exit_fee->RenderArbitrary('$'.number_format($fltContractExitFee, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
			else
			{
				DBO()->RatePlan->contract_exit_fee->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
			$fltContractPayout	= (float)DBO()->RatePlan->contract_payout_percentage->Value;
			if ($fltContractPayout > 0)
			{
				// HACKHACKHACK: Shitty way of printing out a nice name
				DBO()->RatePlan->contract_payout	= $fltContractPayout;
				DBO()->RatePlan->contract_payout->RenderArbitrary(number_format($fltContractPayout, 2, '.', '').'%', RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
			else
			{
				DBO()->RatePlan->contract_payout->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
		}
		
		// COMMISSIONABLE VALUE
		DBO()->RatePlan->commissionable_value->RenderArbitrary('$'.number_format(DBO()->RatePlan->commissionable_value->Value, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		
		if (DBO()->RatePlan->scalable->Value == TRUE)
		{
			// Display the "scalable" details
			DBO()->RatePlan->scalable->RenderArbitrary("Yes", RENDER_OUTPUT);
			DBO()->RatePlan->minimum_services->RenderOutput();
			DBO()->RatePlan->maximum_services->RenderOutput();
		}
		else
		{
			// The plan is not scalable
			DBO()->RatePlan->scalable->RenderArbitrary("Not Scalable", RENDER_OUTPUT);
		}
		
		echo "</td><td width='50%'>\n";
		DBO()->RatePlan->CustomerGroup = Customer_Group::getForId(DBO()->RatePlan->customer_group->Value)->externalName;
		DBO()->RatePlan->CustomerGroup->RenderOutput();
		/*DBO()->RatePlan->MinMonthly->RenderOutput();
		DBO()->RatePlan->ChargeCap->RenderOutput();
		DBO()->RatePlan->UsageCap->RenderOutput();
		DBO()->RatePlan->RecurringCharge->RenderOutput();*/
		DBO()->RatePlan->PlanCharge	= DBO()->RatePlan->MinMonthly->Value;
		//DBO()->RatePlan->UsageStart	= DBO()->RatePlan->ChargeCap->Value;
		//DBO()->RatePlan->UsageLimit	= DBO()->RatePlan->UsageCap->Value;
		
		DBO()->RatePlan->PlanCharge->RenderArbitrary('$'.number_format(DBO()->RatePlan->PlanCharge->Value, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		//DBO()->RatePlan->UsageStart->RenderArbitrary('$'.number_format(DBO()->RatePlan->UsageStart->Value, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		//DBO()->RatePlan->UsageLimit->RenderArbitrary('$'.number_format(DBO()->RatePlan->UsageLimit->Value, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		
		if (DBO()->RatePlan->discount_cap->Value == NULL)
		{
			DBO()->RatePlan->discount_cap->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		else
		{
			DBO()->RatePlan->discount_cap->RenderOutput();
		}
		/* DEPRECATED
		$intIncludedData	= DBO()->RatePlan->included_data->Value;
		if ($intIncludedData == 0)
		{
			DBO()->RatePlan->included_data->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		else
		{
			$strUnit				= 'MB';
			$intIncludedDataInMB	= $intIncludedData / 1024;
			DBO()->RatePlan->included_data->RenderArbitrary("{$intIncludedDataInMB} {$strUnit}", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
		}
		*/
		echo '
		<div class="DefaultElement">
		   <div class="DefaultOutput Default" name="RatePlan.locked" id="RatePlan.locked.Output">'.(DBO()->RatePlan->locked->Value ? 'Yes' : 'No').'</div>
		   <div class="DefaultLabel" id="RatePlan.locked.Label">
		      <span> &nbsp;</span>
		      <span id="RatePlan.locked.Label.Text">Restrict Plan Changes : </span>
		   </div>
		</div>';
		
		echo '
		<div class="DefaultElement">
		   <div class="DefaultOutput Default" name="RatePlan.cdr_required" id="RatePlan.cdr_required.Output">'.(DBO()->RatePlan->cdr_required->Value ? 'Yes' : 'No').'</div>
		   <div class="DefaultLabel" id="RatePlan.cdr_required.Label">
		      <span> &nbsp;</span>
		      <span id="RatePlan.cdr_required.Label.Text">Wait for Call Data : </span>
		   </div>
		</div>';

		// Full service Wholesale plan
		$sFullServicePlan = (DBO()->RatePlan->fullservice_wholesale_plan->Value ? (string)DBO()->RatePlan->fullservice_wholesale_plan->Value : 'None');
		echo '
		<div class="DefaultElement">
		   <div class="DefaultOutput Default" name="RatePlan.fullservice_wholesale_plan" id="RatePlan.fullservice_wholesale_plan.Output">'.$sFullServicePlan.'</div>
		   <div class="DefaultLabel" id="RatePlan.fullservice_wholesale_plan.Label">
		      <span> &nbsp;</span>
		      <span id="RatePlan.fullservice_wholesale_plan.Label.Text">Full Service Wholesale Plan : </span>
		   </div>
		</div>';

		// Preselection Wholesale plan
		$sPreselectionPlan = (DBO()->RatePlan->preselection_wholesale_plan->Value ? (string)DBO()->RatePlan->preselection_wholesale_plan->Value : 'None');
		echo '
		<div class="DefaultElement">
		   <div class="DefaultOutput Default" name="RatePlan.preselection_wholesale_plan" id="RatePlan.preselection_wholesale_plan.Output">'.$sPreselectionPlan.'</div>
		   <div class="DefaultLabel" id="RatePlan.preselection_wholesale_plan.Label">
		      <span> &nbsp;</span>
		      <span id="RatePlan.preselection_wholesale_plan.Label.Text">Pre-Selection Wholesale Plan : </span>
		   </div>
		</div>';
		
		echo "</td></tr></table>\n";
		
		echo "</div>\n";  // GroupedContent
		
		echo "<div class='SmallSeperator'></div>\n";
	}

}

?>
