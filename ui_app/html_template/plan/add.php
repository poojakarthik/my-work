<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// add.php
//----------------------------------------------------------------------------//
/**
 * add
 *
 * HTML Template for the Add Rate Plan HTML object
 *
 * HTML Template for the Add Rate Plan HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to add a rate plan.
 *
 * @file		add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplatePlanAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanAdd
 *
 * A specific HTML Template object
 *
 * A specific HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplatePlanAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanAdd extends HtmlTemplate
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
		
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
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
		// Define javascript to execute when a value is selected in the ServiceTypeCombo
		$strServiceTypeOnChange = "javascript: 
							var objObjects = {};
							objObjects.RatePlan = {};
							objObjects.RatePlan.ServiceType = this.value;
							Vixen.Ajax.CallAppTemplate('Plan', 'GetPlanDeclareRateGroupsHtmlTemplate', objObjects);
							";
	
		// Set Up the form for adding a rate plan
		$this->FormStart("AddPlan", "Plan", "Add");

		echo "<h2 class='Plan'>Plan Details</h2>\n";
		echo "<div class='Wide-Form'>\n";

		if (DBO()->RatePlan->IsInvalid())
		{
			$bolApplyOutputMask = FALSE;
		}
		else
		{
			$bolApplyOutputMask = TRUE;
		}

		DBO()->RatePlan->Name->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->Description->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->Shared->RenderInput(2);
		DBO()->RatePlan->MinMonthly->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->ChargeCap->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->UsageCap->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->CarrierFullService->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->RatePlan->CarrierPreselection->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		
		// Build the ServiceType Combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Service Type:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='ServiceTypeCombo' name='RatePlan.ServiceType' style='width:152px' onchange=\"$strServiceTypeOnChange\">\n";
		echo "         <option value='0' selected='selected'>&nbsp;</option>\n";
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intKey=>$arrValue)
		{
					echo "         <option value='". $intKey ."'>". $arrValue['Description'] ."</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		
		
		echo "</div>\n";  // Narrow-Form
		echo "<div class='SmallSeperator'></div>\n";

		// Stick in the div container for the PlanDeclareRateGroups html template
		echo "<div id='RateGroupsDiv'></div>";

		echo "<div class='WideColumn'>\n";

		// create the buttons
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Save as Draft");
		$this->AjaxSubmit("Commit");
		
		echo "<div class='Seperator'></div>\n";
		echo "</div>\n";
		echo "</div>\n"; // Wide-Form
		$this->FormEnd();
	}
	
	//------------------------------------------------------------------------//
	// _RenderFormStart
	//------------------------------------------------------------------------//
	/**
	 * _RenderFormStart()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function _RenderFormStart()
	{
	}
	
	//------------------------------------------------------------------------//
	// _RenderFormEnd
	//------------------------------------------------------------------------//
	/**
	 * _RenderFormEnd()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function _RenderFormEnd()
	{
	}
}

?>
