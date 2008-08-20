<?php
//----------------------------------------------------------------------------//
// HtmlTemplateServicePlanChange
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServicePlanChange
 *
 * A specific HTML Template object
 *
 * An Plan HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanChange
 * @extends	HtmlTemplate
 */
class HtmlTemplateServicePlanChange extends HtmlTemplate
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
		
		$this->LoadJavascript("plan_change");
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
		
		$this->FormStart("ChangePlan", "Service", "ChangePlan");
		
		echo "<div class='NarrowForm'>\n";
		
		// Render the Service Details
		DBO()->Account->Id->RenderOutput();
		if (DBO()->Account->BusinessName->Value)
		{
			DBO()->Account->BusinessName->RenderOutput();
		}
		elseif (DBO()->Account->TradingName->Value)
		{
			DBO()->Account->TradingName->RenderOutput();
		}
		
		DBO()->Service->FNN->RenderOutput();
		DBO()->Service->Status->RenderCallback("GetConstantDescription", Array("service_status"), RENDER_OUTPUT);		
		
		
		DBO()->CurrentRatePlan->Id = GetCurrentPlan(DBO()->Service->Id->Value);
		if (DBO()->CurrentRatePlan->Id->Value)
		{
			// The service currently has a plan
			DBO()->CurrentRatePlan->SetTable("RatePlan");
			DBO()->CurrentRatePlan->Load();
		}
		else
		{
			DBO()->CurrentRatePlan->Name = "[No Current Plan]";
		}
		DBO()->CurrentRatePlan->Name->RenderOutput();
		
		//TODO! You should probably make the name of the current plan a link to the Service Plan Details page,
		// so the user can view the details of the current plan
		
		// Check if there is a plan scheduled to begin in the next billing period and if so, display its name
		DBO()->FutureRatePlan->Id = GetPlanScheduledForNextBillingPeriod(DBO()->Service->Id->Value);
		if (DBO()->FutureRatePlan->Id->Value)
		{
			// The service has a plan scheduled to start in the next billing period
			DBO()->FutureRatePlan->SetTable("RatePlan");
			DBO()->FutureRatePlan->Load();
			DBO()->FutureRatePlan->Name->RenderOutput();
		}
		
		DBO()->Service->Id->RenderHidden();
		
		$strNewPlanOnChangeJsCode = 'Vixen.PlanChange.elmViewPlanLink.href = "'. Href()->ViewPlan("") .'" + this.value;';
		
		// Build the RatePlan combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;New Plan :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Combo_NewPlan.Id' name='NewPlan.Id' style='width:90%' onchange='$strNewPlanOnChangeJsCode'>\n";
		$intFirstRatePlan = NULL;
		foreach (DBL()->RatePlan as $dboRatePlan)
		{
			$strSelected = "";
			if ($intFirstRatePlan === NULL)
			{
				// We need to initialise the View Rate Plan link
				$intFirstRatePlan	= $dboRatePlan->Id->Value;
				$strSelected		= "selected='selected'";
			}
			echo "<option value='{$dboRatePlan->Id->Value}' $strSelected>{$dboRatePlan->Name->Value}</option>\n";
		}
		
		$strViewFirstRatePlanLink = Href()->ViewPlan($intFirstRatePlan);
		echo "      </select>\n";
		echo "      <a id='ChangePlan.ViewPlanDetails' href='$strViewFirstRatePlanLink' title='View Plan Details'><img src='img/template/view.png'></img></a>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// Render the Start time options
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Schedule to start :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Combo_NewPlan.StartTime' name='NewPlan.StartTime' style='width:100%'>\n";

		if (DBO()->NewPlan->StartTime->Value == 0)
		{
			$strSelectCurrentBillingPeriod	= "selected='selected'";
			$strSelectNextBillingPeriod		= "";
		}
		else
		{
			$strSelectNextBillingPeriod		= "selected='selected'";
			$strSelectCurrentBillingPeriod	= "";
		}

		echo "<option value='0' $strSelectCurrentBillingPeriod>Begining of current billing period</option>\n";
		echo "<option value='1' $strSelectNextBillingPeriod>Begining of next billing period</option>\n";

		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>";
		
		echo "</div>\n";  // NarrowForm

 		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this);");
		
		// Make this utilise a confirm box which details what happens when they change the plan.  If one is scheduled for a future date
		// And they declare a new plan for the current month, then the scheduled plan will be removed.  Also notify them that all Rate overrides
		// will be removed.
		$this->Button("Change Plan", "Vixen.PlanChange.ChangePlan();");
		
		//$this->AjaxSubmit("Change Plan");
		echo "</div></div>\n";
		
		$this->FormEnd();
		
		// Initialise the js object which facilitates this popup
		echo "<script type='text/javascript'>Vixen.PlanChange.Initialise(". DBO()->Service->Id->Value .", '{$this->_objAjax->strId}');</script>\n";
	}
}

?>
