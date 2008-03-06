<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// request.php
//----------------------------------------------------------------------------//
/**
 * request
 *
 * HTML Template for the Provisioning Request HTML object
 *
 * HTML Template for the Provisioning Request HTML object
 *
 * @file		request.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateProvisioningRequest
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateProvisioningRequest
 *
 * HTML Template class for the Provisioning Request HTML object
 *
 * HTML Template class for the Provisioning Request HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateProvisioningRequest
 * @extends	HtmlTemplate
 */
class HtmlTemplateProvisioningRequest extends HtmlTemplate
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
		
		//$this->LoadJavascript("provisioning_page");
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
		echo "<h2 class='Provisioning'>Provision Request</h2>\n";
		
		// Build the checkbox used to select/unselect all the carriers
		$strSelectAll = "<input type='checkbox' class='DefaultInputCheckBox' onchange='Vixen.ProvisioningPage.SelectAllCarriers(this.checked);' />";
		
		Table()->Carriers->SetHeader($strSelectAll, "Carrier");
		Table()->Carriers->SetWidth("10%", "90%");
		Table()->Carriers->SetAlignment("Left", "Left");
		
		foreach ($GLOBALS['*arrConstant']['Carrier'] as $intCarrier=>$arrCarrier)
		{
			if ($intCarrier == CARRIER_PAYMENT)
			{
				// Skip this special case
				continue;
			}
			
			$strSelectCell = "<input type='checkbox' class='DefaultInputCheckBox' name='Carrier_{$intCarrier}'/>";

			$strCarrierCell = $arrCarrier['Description'];
				
			Table()->Carriers->AddRow($strSelectCell, $strCarrierCell);
		}
		
		// If the account has no carriers then output an appropriate message in the table
		if (Table()->Carriers->RowCount() == 0)
		{
			// There are no services to stick in this table
			Table()->Carriers->AddRow("No carriers to display");
			Table()->Carriers->SetRowAlignment("left");
			Table()->Carriers->SetRowColumnSpan(2);
		}
		
		Table()->Carriers->Render();
		
		//TODO! Probably should initialise the javascript object that manages this list, or this page
		// If it hasn't already been initialised
		//echo "<script type='text/javascript'>Vixen.ProvisioningPage.Initialise($intAccountId, null, '$strTableContainerDivId')</script>\n";
		
		echo "<div class='SmallSeperator'></div>";
		echo "<div class='GroupedContent'>";

		// Draw Provisioning Combobox
		echo "<div style='height:25px'>\n";
		echo "   <div class='Left'>\n";
		echo "      <span>&nbsp;&nbsp;Request</span>\n";
		echo "      <span>\n";
		echo "         <select id='RequestCombo' name='Request.RequestType' style='width:100%'>\n";
		echo "            <option id='RequestType.0' value='0'>&nbsp;</option>";
		// Add each Request Type
		foreach ($GLOBALS['*arrConstant']['Request'] as $intRequest=>$arrRequest)
		{
			echo "<option id='RequestType.{$intRequest}' value='$intRequest'>{$arrRequest['Description']}</option>";
		}
		echo "         </select>\n";
		echo "      </span>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		echo "</div>\n";  // GroupedContent
		
		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Submit Request", "Vixen.ProvisioningPage.InitialiseRequestForm();");
		echo "</div></div>\n";
		
		$this->FormEnd();
		
		//TODO! Initialise javascript objects here
		//echo "<script type='text/javascript'>VixenCreateNoteAddObject(); Vixen.NoteAdd.Initialise();</script>\n";
	}
}

?>
