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
	
		echo "<h2 class='plan'>Plan Details</h2>\n";
		echo "<div class='Narrow-Form'>\n";
		$this->FormStart("ChangePlan", "Service", "ChangePlan");
								
		$mixServicePlan = GetCurrentPlan(DBO()->Service->Id->Value)	;
		if ($mixServicePlan === FALSE)
		{
			echo "this service does not currently have a plan\n";
		}
		else
		{
			DBO()->RatePlan->Id = $mixServicePlan;
			DBO()->RatePlan->Load();
			
			DBO()->RatePlan->Description->RenderOutput();
		}
		
		DBO()->RatePlan->Id->RenderHidden();
		DBO()->Service->Id->RenderHidden();		
		
		// retrieve all available rate plans for this service type
		DBL()->RatePlan->ServiceType = DBO()->Service->ServiceType->Value;
		DBL()->RatePlan->Archived = 0;
		DBL()->RatePlan->OrderBy("Name");
		DBL()->RatePlan->Load();		
		if (DBL()->RatePlan->RecordCount() > 0)
		{
			echo "<div class='DefaultElement'>\n";
			echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Select Plan:</div>\n";
			echo "   <div class='DefaultOutput'>\n";
			echo "      <select id='SelectPlanCombo' name='NewPlan.Id' style='width:150px'>\n";
	
			foreach (DBL()->RatePlan as $dboRatePlan)
			{
				if (DBO()->RatePlan->Id->Value == $dboRatePlan->Id->Value)
				{
					echo "<option value='".$dboRatePlan->Id->Value."' selected='selected'>".$dboRatePlan->Name->Value."</option>\n";
				}
				else
				{
					echo "<option value='".$dboRatePlan->Id->Value."'>".$dboRatePlan->Name->Value."</option>\n";
				}
			}
	
			echo "      </select>\n";
			echo "   </div>\n";
			echo "</div>";
		}
		echo "<div class='Right'>\n";
		echo "   <input type='button' class='InputSubmit' value='View Plan Details' onClick=\"$strViewPlanButtonJavascript\"></input>\n";
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		echo "<div class='SmallSeperator'></div>\n";
		echo "</div>\n";
		
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Change Plan");
		echo "</div>\n";
		
		$this->FormEnd();
		
	}
}

?>
