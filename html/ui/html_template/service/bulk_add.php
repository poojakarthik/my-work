<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
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
		$arrDealers = DBO()->Dealers->AsArray->Value;
		
		// Build the Dealer combobox options
		$strDealerOptions = "<option value='0'></option>";
		foreach ($arrDealers as $arrDealer)
		{
			$strName = htmlspecialchars($arrDealer['Name'], ENT_QUOTES) . " (Id: {$arrDealer['Id']})";
			$strDealerOptions .= "<option value='{$arrDealer['Id']}'>$strName</option>";
		}
		
		// Build the table and add a single row to it, which can then be cloned to add more rows
		Table()->Services->SetHeader("&nbsp;", "FNN", "Confirm FNN", "Active", "Plan", "Cost Centre", "Dealer", "Cost (\$)");
		Table()->Services->SetWidth("4%", "12%", "12%", "5%", "22%", "18%", "19%", "8%");
		Table()->Services->SetAlignment("Center", "Left", "Left", "Center", "Left", "Left", "Left", "Left");
		
		$strFnnCell			= "<input id='FnnTextBox' type='text' maxlength='20' style='width:100%'></input>";
		$strFnnConfirmCell	= "<input id='FnnConfirmTextBox' type='text' maxlength='20' style='width:100%'></input>";
		$strServiceTypeCell	= "<div class='ServiceTypeIconBlank'></div>";
		$strDealerCell		= "<select id='DealerCombo' style='width:100%'>$strDealerOptions</select>";
		$strCostCell		= "<input id='CostTextBox' type='text' maxlength='7' style='width:100%'></input>";
		
		$strDisabled = "";
		if (DBO()->Account->Archived->Value == ACCOUNT_STATUS_PENDING_ACTIVATION)
		{
			// The Account is pending activation.  The user can only add services that are pending activation
			$strDisabled = "disabled='true'";
			echo "<div class='MsgNotice'>The account is pending activation.  New services cannot be activated until the account is activated.</div>";
		}
		$strActivateCell	= "<input id='ActivateCheckBox' type='checkbox' $strDisabled></input>";
		
		$strCostCentreCell	 = "<select id='CostCentreCombo' style='width:100%'>";
		$strCostCentreCell	.= "<option value='0'>&nbsp;</option>";
		$arrCostCenters	= DBO()->Account->AllCostCenters->Value;
		foreach ($arrCostCenters as $intId=>$strName)
		{
			$strCostCentreCell .= "<option value='$intId'>$strName</option>";
		}
		$strCostCentreCell	.= "</select>";
		
		$strPlanCell		= "<select id='PlanCombo' style='width:100%'></select>";
		
		Table()->Services->AddRow($strServiceTypeCell, $strFnnCell, $strFnnConfirmCell, $strActivateCell, $strPlanCell, $strCostCentreCell, $strDealerCell, $strCostCell);
		
		Table()->Services->Render();
		
		// Initialise the Javascript object which facilitates this page
		$arrRatePlans	= DBO()->Account->AllRatePlans->Value;
		$jsonRatePlans	= Json()->encode($arrRatePlans);
		$intAccountId	= DBO()->Account->Id->Value;
		$strJsScript	= "Vixen.ServiceBulkAdd.Initialise($intAccountId, $jsonRatePlans);";
		
		echo "
<div class='ButtonContainer'>
	<input type='button' value='Save' onclick='Vixen.ServiceBulkAdd.ValidateServices()' style='float:right'></input>
</div>
<script type='text/javascript'>$strJsScript</script>
<div class='SmallSeparator'></div>";
	}
}

?>
