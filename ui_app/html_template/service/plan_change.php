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
		// define the javascript which is executed when the "View Plan Details" button is clicked
		$strViewPlanButtonJavascript = "
			var intPlanId = getElementById('SelectPlanCombo').value;
			window.location = 'rates_plan_summary.php?Id=' + intPlanId;
		";
	
		$this->FormStart("ChangePlan", "Service", "ChangePlan");
		echo "<h2 class='plan'>Plan Details</h2>\n";
		echo "<div class='NarrowForm'>\n";
		
		$mixServicePlan = GetCurrentPlan(DBO()->Service->Id->Value)	;
		if ($mixServicePlan === FALSE)
		{
			echo "<span class='DefaultOutputSpan'>&nbsp;&nbsp;This service does not currently have a plan</span>\n";
		}
		else
		{
			// The service currently has a plan
			DBO()->RatePlan->Id = $mixServicePlan;
			DBO()->RatePlan->Load();
			
			DBO()->RatePlan->Description->RenderOutput();
		}
		
		DBO()->RatePlan->Id->RenderHidden();
		DBO()->Service->Id->RenderHidden();		
		DBO()->Page->ViewService = TRUE;
		DBO()->Page->ViewService->RenderHidden();
		
		// retrieve all available rate plans for this service type
		DBL()->RatePlan->ServiceType = DBO()->Service->ServiceType->Value;
		DBL()->RatePlan->Archived = 0;
		DBL()->RatePlan->OrderBy("Name");
		DBL()->RatePlan->Load();		
		if (DBL()->RatePlan->RecordCount() > 0)
		{
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Select New Plan :</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='SelectPlanCombo' name='NewPlan.Id' style='width:100%'>\n";
	
			foreach (DBL()->RatePlan as $dboRatePlan)
			{
				$strSelected = (DBO()->RatePlan->Id->Value == $dboRatePlan->Id->Value) ? "selected='selected'" : "";
				echo "<option value='".$dboRatePlan->Id->Value."' $strSelected>". $dboRatePlan->Name->Value ."</option>\n";
			}
	
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>";
		}
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		echo "   <input type='button' class='InputSubmit' value='View Plan Details' onClick=\"$strViewPlanButtonJavascript\"></input>\n";
		echo "</div></div>\n";
		
		echo "</div>\n";  // NarrowForm

 		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this);");
		$this->AjaxSubmit("Change Plan");
		echo "</div></div>\n";
		
		$this->FormEnd();
		
	}
}

?>
