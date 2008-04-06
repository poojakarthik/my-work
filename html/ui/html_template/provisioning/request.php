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
		// Build the list of values that can go into the Carrier combobox
		$arrCarrierOptions = Array();
		//TODO! use this "Plan Select" option, when plan Carrier select functionality has been implemented
		//$arrCarrierOptions[] = Array("Name" => "(Plan Select)", "Value" => "0");
		$arrCarrierOptions[] = Array("Name" => "&nbsp;", "Value" => "0");
		foreach ($GLOBALS['*arrConstant']['Carrier'] as $intCarrier=>$arrCarrier)
		{
			if ($intCarrier == CARRIER_PAYMENT)
			{
				// Skip this special case
				continue;
			}
			$arrCarrierOptions[] = Array("Name" => $arrCarrier['Description'], "Value" => $intCarrier);
		}
		
		// Build the list of values that can go into the Request combobox
		$arrRequestOptions = Array();
		$arrRequestOptions[] = Array("Name" => "&nbsp;", 				"Value" => "0");
		foreach ($GLOBALS['*arrConstant']['Request'] as $intConstant=>$arrConstant)
		{
			switch ($intConstant)
			{
				case REQUEST_CHANGE_ADDRESS:
				case REQUEST_LOSS_VIRTUAL_PRESELECTION:
				case REQUEST_LOSS_PRESELECT:
				case REQUEST_LOSS_FULL:
					continue;
					break;
					
				default:
					$arrRequestOptions[] = Array("Name" => $arrConstant['Description'], "Value"	=> $intConstant);
					break;
			}
		}
		
		echo "<div class='GroupedContent'>";
		echo "<div style='height:22px'>\n";
		echo "   <div class='Left'>\n";
		
		// Draw the Carrier combobox
		echo "   <span>Carrier</span>\n";
		echo "   <span>\n";
		echo "      <select id='CarrierCombo'>\n";
		foreach ($arrCarrierOptions as $arrCarrier)
		{
			echo "<option value='{$arrCarrier['Value']}'>{$arrCarrier['Name']}</option>";
		}
		echo "      </select>\n";
		echo "   </span>\n";
		
		// Draw Provisioning Combobox
		echo "      <span style='margin-left:10px;'>Request</span>\n";
		echo "      <span>\n";
		echo "         <select id='RequestCombo'>\n";
		// Add each Request Type
		foreach ($arrRequestOptions as $arrRequest)
		{
			echo "<option value='{$arrRequest['Value']}'>{$arrRequest['Name']}</option>";
		}
		echo "         </select>\n";
		echo "      </span>\n";
		
		// Draw AuthorisationDate textbox
		$strAuthorisationDate = date("d/m/Y");
		echo "      <span style='margin-left:10px;'>Authorisation Date</span>\n";
		echo "      <span>\n";
		echo "         <input type='text' id='AuthorisationDateTextBox' name='ProvisioningRequest->AuthorisationDate' value='$strAuthorisationDate' style='width:85px'/>\n";
		echo "      </span>\n";
		
		echo "   </div>\n"; // Left
		
		// Render the buttons
		echo "<div class='Right'>\n";
		$this->Button("Submit Request", "Vixen.ProvisioningPage.SubmitRequest();");
		echo "</div>\n";


		echo "</div>\n"; // height=22px
		
		echo "</div>\n";  // GroupedContent
		
		echo "<div class='SmallSeperator'></div>\n";
		
		$this->FormEnd();
		
		//Initialise the javascript object that manages this list
		$intAccount = DBO()->Account->Id->Value;
		echo "<script type='text/javascript'>Vixen.ProvisioningPage.InitialiseRequestForm($intAccount)</script>\n";
	}
}

?>
