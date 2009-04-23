<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServicePlanDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServicePlanDetails
 *
 * A specific HTML Template object
 *
 * A Plan details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServicePlanDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateServicePlanDetails extends HtmlTemplate
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
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
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
		echo "<!-- Actual Service Declared : ". DBO()->ActualRequestedService->Id->Value ." -->\n";
		
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_CURRENT_PLAN:
				// Render the plan details as the current plan
				if (DBO()->FutureRatePlan->Id->Value)
				{
					echo "<h2 class='plan'>Current Plan</h2>\n";
				}
				else
				{
					echo "<h2 class='plan'>Plan Details</h2>\n";
				}
				$this->_RenderDetails("CurrentRatePlan");
				break;
				
			case HTML_CONTEXT_FUTURE_PLAN:
				// Render the plan details as the future plan
				echo "<h2 class='plan'>Future Scheduled Plan</h2>\n";
				$this->_RenderDetails("FutureRatePlan");
				break;				
		}
	}

	//------------------------------------------------------------------------//
	// _RenderFullDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderFullDetail()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderDetails($strRatePlan)
	{
		echo "<div class='GroupedContent'>\n";
		
		// Load the details of the $strRatePlan object into the DBO()->RatePlan object
		// Trust me, this is easier than defining a bunch of stuff in the UIAppDocumentation, and then refering to contexts that don't mean anything
		$dboRatePlan = new DBObject("RatePlan");
		foreach (DBO()->{$strRatePlan} as $strProperty=>$objProperty)
		{
			$dboRatePlan->$strProperty = $objProperty->Value;
		}
		
		if ($dboRatePlan->Id->Value)
		{
			// Build a link to the Rate Plan summary (not the one specific to this service)
			$strPlanSummaryHref = Href()->ViewPlan($dboRatePlan->Id->Value);
			$strPlanSummaryLink = "<a href='$strPlanSummaryHref' title='View Plan Details'>{$dboRatePlan->Name->Value}</a>";
		
			$dboRatePlan->Name->RenderArbitrary($strPlanSummaryLink, RENDER_OUTPUT);
			$dboRatePlan->Description->RenderOutput();
			
			$arrRatePlan		= $dboRatePlan->AsArray();
			$strCustomerGroup	= Customer_Group::getForId($arrRatePlan['customer_group'])->externalName;
			
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
						$objFileType		= new File_Type(array('id'=>$objBrochureDocumentContent->file_type_id), true);
						
						$strImageSrc		= "../admin/reflex.php/File/Image/FileTypeIcon/{$objFileType->id}/16x16";
						$strBrochureLink	= "../admin/reflex.php/File/Document/{$arrRatePlan['brochure_document_id']}";
						$strBrochureCell	= "<a href='{$strBrochureLink}' title='Download Plan Brochure'><img src='{$strImageSrc}' alt='Download Plan Brochure' /> Download</a>";
						
						$strEmailOnClick	= Rate_Plan::generateEmailButtonOnClick($arrRatePlan['customer_group'], array($arrRatePlan), DBO()->Account->Id->Value);
						$strBrochureCell	.= "&nbsp;|&nbsp; <a onclick='{$strEmailOnClick}' title='Email Plan Brochure'><img src='../admin/img/template/pdf_email.png' alt='Email Plan Brochure' /> Email this Brochure</a>";
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
			$dboRatePlan->ServiceType->RenderCallback("GetConstantDescription", Array("service_type"), RENDER_OUTPUT);	
			$dboRatePlan->CustomerGroup = Customer_Group::getForId($dboRatePlan->customer_group->Value)->externalName;
			$dboRatePlan->CustomerGroup->RenderOutput();
			
			$intFullService = $dboRatePlan->CarrierFullService->Value;
			if (!isset($GLOBALS['*arrConstant']['Carrier'][$intFullService]))
			{
				$strFullService = "[Not Specified]";
			}
			else
			{
				$strFullService = $GLOBALS['*arrConstant']['Carrier'][$intFullService]['Description'];
			}
			$dboRatePlan->CarrierFullService->RenderArbitrary($strFullService, RENDER_OUTPUT);
			
			$intPreselection = $dboRatePlan->CarrierPreselection->Value;
			if (!isset($GLOBALS['*arrConstant']['Carrier'][$intPreselection]))
			{
				$strPreselection = "[Not Specified]";
			}
			else
			{
				$strPreselection = $GLOBALS['*arrConstant']['Carrier'][$intPreselection]['Description'];
			}
			$dboRatePlan->CarrierPreselection->RenderArbitrary($strPreselection, RENDER_OUTPUT);
			
			$dboRatePlan->Shared->RenderOutput();
			$dboRatePlan->InAdvance->RenderOutput();
			if ($dboRatePlan->ContractTerm->Value == NULL)
			{
				// There is no contract term
				$dboRatePlan->ContractTerm->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
			else
			{
				$dboRatePlan->ContractTerm->RenderOutput();
			
				// Render Contract Details
				$fltContractExitFee	= (float)$dboRatePlan->contract_exit_fee->Value;
				if ($fltContractExitFee > 0)
				{
					$dboRatePlan->contract_exit_fee->RenderArbitrary('$'.number_format($fltContractExitFee, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
				}
				else
				{
					$dboRatePlan->contract_exit_fee->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
				}
				$fltContractPayout	= (float)$dboRatePlan->contract_payout_percentage->Value;
				if ($fltContractPayout > 0)
				{
					// HACKHACKHACK: Shitty way of printing out a nice name
					$dboRatePlan->contract_payout	= $fltContractPayout;
					$dboRatePlan->contract_payout->RenderArbitrary(number_format($fltContractPayout, 2, '.', '').'%', RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
				}
				else
				{
					$dboRatePlan->contract_payout->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
				}
			}
		
			// COMMISSIONABLE VALUE
			$dboRatePlan->commissionable_value->RenderArbitrary('$'.number_format($dboRatePlan->commissionable_value->Value, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			
			if ($dboRatePlan->scalable->Value == TRUE)
			{
				// Display the "scalable" details
				$dboRatePlan->scalable->RenderArbitrary("Yes", RENDER_OUTPUT);
				$dboRatePlan->minimum_services->RenderOutput();
				$dboRatePlan->maximum_services->RenderOutput();
			}
			else
			{
				// The plan is not scalable
				$dboRatePlan->scalable->RenderArbitrary("Not Scalable", RENDER_OUTPUT);
			}
			
			/*$dboRatePlan->MinMonthly->RenderOutput();
			$dboRatePlan->ChargeCap->RenderOutput();
			$dboRatePlan->UsageCap->RenderOutput();
			$dboRatePlan->RecurringCharge->RenderOutput();*/
			$dboRatePlan->PlanCharge	= $dboRatePlan->MinMonthly->Value;
			$dboRatePlan->UsageStart	= $dboRatePlan->ChargeCap->Value;
			$dboRatePlan->UsageLimit	= $dboRatePlan->UsageCap->Value;
			
			$dboRatePlan->PlanCharge->RenderArbitrary('$'.number_format($dboRatePlan->PlanCharge->Value, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			$dboRatePlan->UsageStart->RenderArbitrary('$'.number_format($dboRatePlan->UsageStart->Value, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			$dboRatePlan->UsageLimit->RenderArbitrary('$'.number_format($dboRatePlan->UsageLimit->Value, 2, '.', ''), RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			
			if ($dboRatePlan->discount_cap->Value == NULL)
			{
				$dboRatePlan->discount_cap->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
			else
			{
				$dboRatePlan->discount_cap->RenderOutput();
			}
			
			$intIncludedData	= $dboRatePlan->included_data->Value;
			if ($intIncludedData == 0)
			{
				$dboRatePlan->included_data->RenderArbitrary("[Not Specified]", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
			else
			{
				$strUnit				= 'MB';
				$intIncludedDataInMB	= $intIncludedData / 1024;
				$dboRatePlan->included_data->RenderArbitrary("{$intIncludedDataInMB} {$strUnit}", RENDER_OUTPUT, CONTEXT_DEFAULT, FALSE, FALSE);
			}
		
		echo '<div class="DefaultElement">
   <div class="DefaultOutput Default" name="RatePlan.locked" id="RatePlan.locked.Output">'.($dboRatePlan->locked->Value ? 'Yes' : 'No').'</div>
   <div class="DefaultLabel" id="RatePlan.locked.Label">
      <span> &nbsp;</span>
      <span id="RatePlan.locked.Label.Text">Restrict Plan Changes : </span>
   </div>
</div>';
		
		echo '<div class="DefaultElement">
   <div class="DefaultOutput Default" name="RatePlan.cdr_required" id="RatePlan.cdr_required.Output">'.($dboRatePlan->cdr_required->Value ? 'Yes' : 'No').'</div>
   <div class="DefaultLabel" id="RatePlan.cdr_required.Label">
      <span> &nbsp;</span>
      <span id="RatePlan.cdr_required.Label.Text">Wait for Call Data : </span>
   </div>
</div>';
			
			if ($dboRatePlan->StartDatetime->IsSet)
			{
				$dboRatePlan->StartDatetime->RenderOutput();
			}
			
			if ($dboRatePlan->EndDatetime->IsSet)
			{
				$dboRatePlan->EndDatetime->RenderOutput();
			}
		}
		else
		{
			if ($this->_intContext == HTML_CONTEXT_CURRENT_PLAN)
			{
				echo "<span>This service does not currently have a plan</span>";
			}
		}
		
		echo "</div>\n";  // GroupedContent
		
		echo "<div class='SmallSeperator'></div>\n";
	}	
}

?>
