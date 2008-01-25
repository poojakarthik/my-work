<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// HtmlTemplateServicesWithoutPlans
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServicesWithoutPlans
 *
 * HTML Template object for the ServicesWithoutPlans List
 *
 * HTML Template object for the ServicesWithoutPlans List
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateServicesWithoutPlans
 * @extends	HtmlTemplate
 */
class HtmlTemplateServicesWithoutPlans extends HtmlTemplate
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
		
		// Load all java script specific to the page here
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
		//Build the combo box html for each combo box
		$strComboboxes = Array();
		
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intKey=>$arrValue)
		{
			$strHtml  = "<span class='DefaultOutputSpan Default'>\n";
			$strHtml .= "   <select name='Service.NewPlan<ServiceId>' style='width:500px'>\n";
			$strHtml .= "      <option value='0' selected='selected'>&nbsp;</option>\n";
			foreach (DBL()->RatePlan as $dboRatePlan)
			{
				if ($dboRatePlan->ServiceType->Value == $intKey)
				{
					$strHtml .= "      <option value='".$dboRatePlan->Id->Value."'>".$dboRatePlan->Name->Value."</option>\n";
				}
			}
			$strHtml .= "   </select>\n";
			$strHtml .= "</span>\n";

			$strComboboxes[$intKey] = $strHtml;
		}
	
		echo "<div class='WideColumn'>\n";
		
		// Start the form
		$this->FormStart("SetPlans", "Service", "BulkSetPlanForUnplanned");

		// Stick in the submit button 
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Submit Changes");
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
		echo "<div class='Seperator'></div>\n";

		// display all Services in a table
		Table()->Services->SetHeader("Account", "FNN", "Service Type", "Plan");
		Table()->Services->SetAlignment("Left", "Left", "Left", "Left");
		Table()->Services->SetWidth("10%", "15%", "20%", "55%");
		
		foreach (DBL()->Service as $dboService)
		{
			// Build the Account cell
			$strAccountHref = Href()->ViewAccount($dboService->Account->Value);
			$strAccountCell = "<a href='$strAccountHref'>". $dboService->Account->AsValue() ."</a>";
			
			// Build the FNN cell (link it to View Service)
			$strFnnHref = Href()->ViewService($dboService->Id->Value);
			$strFnnCell = "<a href='$strFnnHref'>". $dboService->FNN->AsValue() ."</a>";
			
			$strServiceTypeCell = $dboService->ServiceType->AsCallback("GetConstantDescription", Array("ServiceType"));
			
			// Build the Plan Select combobox
			$strPlanCell = str_replace("<ServiceId>", $dboService->Id->Value, $strComboboxes[$dboService->ServiceType->Value]);
			
			Table()->Services->AddRow($strAccountCell, $strFnnCell, $strServiceTypeCell, $strPlanCell);
		}
		
		Table()->Services->Render();
		
		// Stick in the submit button 
		echo "<div class='Right'>\n";
		$this->AjaxSubmit("Submit Changes");
		echo "</div>\n";
		
		// End the form
		$this->FormEnd();
		
		echo "</div>";
	}
}

?>
