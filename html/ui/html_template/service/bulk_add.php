<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// bulk_add.php
//----------------------------------------------------------------------------//
/**
 * bulk_add
 *
 * HTML Template for the ServiceBulkAdd HTML object
 *
 * HTML Template for the ServiceBulkAdd HTML object
 *
 * @file		bulk_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateServiceBulkAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceBulkAdd
 *
 * HTML Template class for the ServiceBulkAdd HTML object
 *
 * HTML Template class for the ServiceBulkAdd HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceBulkAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceBulkAdd extends HtmlTemplate
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
		
		$this->LoadJavascript("service_bulk_add");
		$this->LoadJavascript("service_extra_details_inbound");
		$this->LoadJavascript("service_extra_details_mobile");
		$this->LoadJavascript("service_extra_details_land_line");
		$this->LoadJavascript("validation");
		
		
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
		// Build the table and add a single row to it, which can then be cloned to add more rows
		Table()->Services->SetHeader("&nbsp;", "FNN", "Confirm FNN", "Plan", "Cost Centre");
		Table()->Services->SetWidth("4%", "12%", "12%", "36%", "36%");
		Table()->Services->SetAlignment("Center", "Left", "Left", "Left", "Left");
		
		$strFnnCell			= "<input id='FnnTextBox' type='text' maxlength='20' style='width:100%'></input>";
		$strFnnConfirmCell	= "<input id='FnnConfirmTextBox' type='text' maxlength='20' style='width:100%'></input>";
		$strServiceTypeCell	= "<div class='ServiceTypeIconBlank'></div>";
		
		$strCostCentreCell	 = "<select id='CostCentreCombo' style='width:100%'>";
		$strCostCentreCell	.= "<option value='0'>&nbsp;</option>";
		$arrCostCenters	= DBO()->Account->AllCostCenters->Value;
		foreach ($arrCostCenters as $intId=>$strName)
		{
			$strCostCentreCell .= "<option value='$intId'>$strName</option>";
		}
		$strCostCentreCell	.= "</select>";
		
		$strPlanCell		= "<select id='PlanCombo' style='width:100%'></select>";
		
		Table()->Services->AddRow($strServiceTypeCell, $strFnnCell, $strFnnConfirmCell, $strPlanCell, $strCostCentreCell);
		
		Table()->Services->Render();
		
		echo "<div class='ButtonContainer'><div class='right'>\n";
		
		// The following commented out functionality isn't really needed anymore
		/*echo "<span>Add </span><input type='text' id='ServiceBulkAdd.NumServicesTextBox' value='1' maxlength='2' style='width:25px'></input><span> More Services </span>";
		$this->Button("Go", "var elmTextbox = document.getElementById('ServiceBulkAdd.NumServicesTextBox'); Vixen.ServiceBulkAdd.AddMoreServices(elmTextbox.value)");
		*/
		$this->Button("Save", "Vixen.ServiceBulkAdd.ConfirmSave()");
		
		echo "</div></div>\n";  //Button Container
		
		// Initialise the Javascript object which facilitates this page
		$arrRatePlans	= DBO()->Account->AllRatePlans->Value;
		$jsonRatePlans	= Json()->encode($arrRatePlans);
		$intAccountId	= DBO()->Account->Id->Value;
		
		$strJsScript = "Vixen.ServiceBulkAdd.Initialise($intAccountId, $jsonRatePlans);";
		echo "<script type='text/javascript'>$strJsScript</script>\n";
		
		
		echo "<div class='SmallSeparator'></div>\n";
	}
}

?>
