<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_address_edit.php
//----------------------------------------------------------------------------//
/**
 * service_address_edit
 *
 * HTML Template for editing the address details of a service
 *
 * HTML Template for editing the address details of a service
 *
 * @file		service_address_edit.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateServiceAddressEdit
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceAddressEdit
 *
 * HTML Template class for the ServiceAddressEdit HTML object
 *
 * HTML Template class for the ServiceAddressEdit HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceAddressEdit
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceAddressEdit extends HtmlTemplate
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
		
		$this->LoadJavascript("service_address");
		$this->LoadJavascript("date_time_picker_xy");
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
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_POPUP:
				$this->RenderAsPopup();
				break;
				
			case HTML_CONTEXT_SERVICE_BULK_ADD:
				$this->RenderForServiceBulkAddPage();
				break;
				
			default:
				echo "ERROR: ServiceAddressEdit Html Template rendered in known context: {$this->_intContext}";
		}
	}

	//------------------------------------------------------------------------//
	// RenderAsPopup
	//------------------------------------------------------------------------//
	/**
	 * RenderAsPopup()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderAsPopup()
	{
		$arrAccountAddresses = DBO()->Account->AllAddresses->Value;
		
		$this->RenderForm($arrAccountAddresses);
		
		echo "<div class='ButtonContainer'><div class='right'>\n";
		$this->Button("Cancel",	"Vixen.Popup.Close(this)");
		$this->Button("Save", "Vixen.ServiceAddress.SaveAddress()");
		echo "</div></div>\n";  //Button Container
		
		// Initialise the Javascript object which facilitates this form
		$arrPostalAddressTypes = Array();
		foreach ($GLOBALS['*arrConstant']['PostalAddrType'] as $strConstant=>$arrConstant)
		{
			$arrPostalAddressTypes[$strConstant] = $arrConstant['Description'];
		}
		
		$jsonPostalAddressTypes	= Json()->encode($arrPostalAddressTypes);
		$jsonAccountAddresses	= Json()->encode($arrAccountAddresses);
		$intServiceId			= DBO()->Service->Id->Value;
		$intAccountId			= DBO()->Account->Id->Value;
		$strContainerDivId		= $this->_strContainerDivId;
		$strPopupId				= $this->_objAjax->strId;
		
		$strJsScript = "Vixen.ServiceAddress.InitialiseEdit($intAccountId, $intServiceId, '$strContainerDivId', $jsonPostalAddressTypes, $jsonAccountAddresses, '$strPopupId');";
		echo "<script type='text/javascript'>$strJsScript</script>\n";
	}
	
	//------------------------------------------------------------------------//
	// RenderForServiceBulkAddPage
	//------------------------------------------------------------------------//
	/**
	 * RenderForServiceBulkAddPage()
	 *
	 * Render this HTML Template for use with the ServiceBulkAdd Page
	 *
	 * Render this HTML Template for use with the ServiceBulkAdd Page
	 *
	 * @method
	 */
	function RenderForServiceBulkAddPage()
	{
		$arrAccountAddresses = DBO()->Account->AllAddresses->Value;
		
		// Render the form
		$this->RenderForm($arrAccountAddresses);
		
		echo "<div class='ButtonContainer' Id='ButtonContainer_LandLine'><div class='Right'>\n";
		$this->Button("Cancel",	"Vixen.Popup.Close(this)");
		$this->Button("Back", "Vixen.ServiceBulkAdd.LandLine.Previous()");
		$this->Button("Save", "Vixen.ServiceBulkAdd.LandLine.Next()");
		echo "</div></div>\n";
		
		// Initialise the Javascript object which facilitates this form
		$arrPostalAddressTypes = Array();
		foreach ($GLOBALS['*arrConstant']['PostalAddrType'] as $strConstant=>$arrConstant)
		{
			$arrPostalAddressTypes[$strConstant] = $arrConstant['Description'];
		}
		
		$jsonPostalAddressTypes	= Json()->encode($arrPostalAddressTypes);
		$jsonAccountAddresses	= Json()->encode($arrAccountAddresses);
		$jsonAddressDetails		= Json()->encode(DBO()->ServiceAddress->_arrProperties);
		$intAccountId			= DBO()->Account->Id->Value;
		
		$strJsScript = "Vixen.ServiceAddress.InitialiseServiceBulkAdd($intAccountId, $jsonPostalAddressTypes, $jsonAccountAddresses, $jsonAddressDetails);";
		echo "<script type='text/javascript'>$strJsScript</script>\n";
	}
	
	//------------------------------------------------------------------------//
	// RenderForm
	//------------------------------------------------------------------------//
	/**
	 * RenderForm
	 *
	 * Renders the ServiceAddress form
	 *
	 * Renders the ServiceAddress form
	 * 
	 * @param	array	$arrAccountAddresses	array of all ServiceAddress records associated with the account
	 * 											as created by the AppTemplateService->_GetAllServiceAddresses() method
	 *
	 * @method
	 */
	function RenderForm($arrAccountAddresses)
	{
		echo "<form id='VixenForm_ServiceAddress' >\n";
		
		// Render the Details
		echo "<div class='GroupedContent' style='height:370px'>\n";

		// Render the AddressRecord Combobox (used to populate the form with 
		// values form another service belonging to the Account)
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Use same as service :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='AddressEdit.AddressCombo' style='width:100%' onChange='Vixen.ServiceAddress.LoadAddress(this.value)'>\n";
		echo "<option value='0'>&nbsp;</option>";
		foreach ($arrAccountAddresses as $intService=>$arrService)
		{
			$strSelected = ($intService == DBO()->ServiceAddress->Service->Value)? "selected='selected'": "";
			
			$strCategory = ($arrService['Residential']) ? "Residential" : "Business";
			
			$strDescription = "{$arrService['FNN']} - $strCategory - {$arrService['PhysicalAddressDescription']}";
			
			echo "<option value='$intService' $strSelected>$strDescription</option>";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		echo "<div class='ContentSeparator'></div>\n";
		
		echo "<div id='AddressEdit_Column1' style='width:50%; float:left'>\n";
		
		// Render the UserDetails div
		echo "<div id='Container.UserDetails' style='height:185px'>";
		echo "<span><strong>User Details</strong></span>\n";
		echo "<div class='SmallSeparator'></div>\n";
		
		// Render the ServiceCategory combobox
		echo "	<div class='DefaultElement'>
					<div class='DefaultLabel'>&nbsp;&nbsp;Category :</div>
					<div class='DefaultOutput'>
						<select id='ServiceAddress.Residential' style='width:155px' onChange='Vixen.ServiceAddress.SetServiceCategory(this.value)'>
							<option value='0'>Business</option>
							<option value='1'>Residential</option>
						</select>
					</div>
				</div>\n";
		
		echo "<div id='Container.ResidentialUserDetails' style='display:none'>\n";
		
		// The Title combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput'>*&nbsp;</span>Title :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='ServiceAddress.EndUserTitle' name='ServiceAddress.EndUserTitle' style='width:155px'>\n";
		foreach ($GLOBALS['*arrConstant']['EndUserTitleType'] as $strKey=>$arrTitle)
		{
			echo "		<option value='$strKey'>{$arrTitle['Description']}</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		DBO()->ServiceAddress->EndUserGivenName->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>30));
		DBO()->ServiceAddress->EndUserFamilyName->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>50));
		
		$strMaxYear = date("Y") - 17;
		$arrDOBArgs = Array("TO_YEAR" => $strMaxYear);
		DBO()->ServiceAddress->DateOfBirth->RenderInput(CONTEXT_DEFAULT, TRUE, TRUE, $arrDOBArgs, Array("attribute:maxlength"=>10));
		DBO()->ServiceAddress->Employer->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>30));
		DBO()->ServiceAddress->Occupation->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>30));
		echo "</div>\n"; //Container.ResidentialUserDetails
		
		echo "<div id='Container.BusinessUserDetails' style='display:none;'>\n";
		DBO()->ServiceAddress->ABN->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>11));
		DBO()->ServiceAddress->EndUserCompanyName->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>50));
		DBO()->ServiceAddress->TradingName->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>50));
		
		echo "</div>\n"; //Container.BusinessUserDetails
		echo "</div>\n"; //Container.UserDetails
		
		// Render the Billing Address Details div
		echo "<div class='SmallSeparator'></div>\n";
		echo "<span><strong>Billing Address Details</strong></span>\n";
		echo "<div class='SmallSeparator'></div>\n";
		echo "<div id='Container.BillingAddressDetails'>";
		DBO()->ServiceAddress->BillName->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>30));
		DBO()->ServiceAddress->BillAddress1->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>30));
		DBO()->ServiceAddress->BillAddress2->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>30));
		DBO()->ServiceAddress->BillLocality->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>23));
		DBO()->ServiceAddress->BillPostcode->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>4));
		echo "</div>"; // Container.BillingAddressDetails
		
		
		echo "</div>"; //AddressEdit_Column1
		echo "<div id='AddressEdit_Column2' style='width:50%; float:left'>\n";
		
		// Render the Service Address details div
		echo "<span><strong>Physical Service Address Details</strong></span>\n";
		echo "<div class='SmallSeparator'></div>\n";
		echo "<div id='Container.ServiceAddressDetails'>";
		
		// ServiceAddressType combo
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput' id='ServiceAddress.ServiceAddressType.Required'>&nbsp&nbsp;</span>Address Type :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='ServiceAddress.ServiceAddressType' name='ServiceAddress.ServiceAddressType' onChange='Vixen.ServiceAddress.UpdateServiceAddressControls(true)'>\n";
		echo "<option value=''>&nbsp;</option>";
		foreach ($GLOBALS['*arrConstant']['ServiceAddrType'] as $strKey=>$arrAddressType)
		{
			echo "<option value='$strKey'>{$arrAddressType['Description']}</option>";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		DBO()->ServiceAddress->ServiceAddressTypeNumber->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>5, "SetRequiredId"=>TRUE));
		DBO()->ServiceAddress->ServiceAddressTypeSuffix->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>2, "SetRequiredId"=>TRUE));
		echo "<div class='SmallSeparator'></div>\n";
		DBO()->ServiceAddress->ServiceStreetNumberStart->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>5, "SetRequiredId"=>TRUE, "attribute:onKeyUp"=>"Vixen.ServiceAddress.UpdateStreetNumberStartControl()"));

		DBO()->ServiceAddress->ServiceStreetNumberEnd->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>5, "SetRequiredId"=>TRUE));
		DBO()->ServiceAddress->ServiceStreetNumberSuffix->RenderInput(CONTEXT_DEFAULT, FALSE, FALSE, Array("attribute:maxlength"=>1, "SetRequiredId"=>TRUE));
		DBO()->ServiceAddress->ServiceStreetName->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>30, "SetRequiredId"=>TRUE, "attribute:onKeyUp"=>"Vixen.ServiceAddress.UpdateStreetNameControl()"));
		
		// ServiceStreetType combo
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput' id='ServiceAddress.ServiceStreetType.Required'>*&nbsp;</span>Street Type :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='ServiceAddress.ServiceStreetType' name='ServiceAddress.ServiceStreetType' style='width:155px'>\n";
		echo "<option value='". SERVICE_STREET_TYPE_NOT_REQUIRED ."'>&nbsp;</option>";
		foreach ($GLOBALS['*arrConstant']['ServiceStreetType'] as $strKey=>$arrStreetType)
		{
			if ($strKey == SERVICE_STREET_TYPE_NOT_REQUIRED)
			{
				// Don't include this one twice
				continue;
			}
			echo "<option value='$strKey'>{$arrStreetType['Description']}</option>";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// ServiceStreetTypeSuffix combo
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput' id='ServiceAddress.ServiceStreetTypeSuffix.Required'>&nbsp;&nbsp;</span>Street Type Suffix :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='ServiceAddress.ServiceStreetTypeSuffix' name='ServiceAddress.ServiceStreetTypeSuffix' style='width:155px'>\n";
		echo "<option value=''>&nbsp;</option>";
		foreach ($GLOBALS['*arrConstant']['ServiceStreetSuffixType'] as $strKey=>$arrSuffix)
		{
			echo "<option value='$strKey'>{$arrSuffix['Description']}</option>";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		echo "<div class='SmallSeperator'></div>\n";
		DBO()->ServiceAddress->ServicePropertyName->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>30, "SetRequiredId"=>TRUE, "attribute:onKeyUp"=>"Vixen.ServiceAddress.UpdatePropertyNameControl()"));
		DBO()->ServiceAddress->ServiceLocality->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>30, "SetRequiredId"=>TRUE));
		
		// ServiceState combo
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'><span class='RequiredInput' id='ServiceAddress.ServiceState.Required'>*&nbsp;</span>State :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='ServiceAddress.ServiceState' name='ServiceAddress.ServiceState'>\n";
		foreach ($GLOBALS['*arrConstant']['ServiceStateType'] as $strKey=>$arrState)
		{
			$strSelected = (DBO()->ServiceAddress->ServiceState->Value == $strKey)? "selected='selected'" : "";
			echo "<option value='$strKey' $strSelected>{$arrState['Description']}</option>";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		DBO()->ServiceAddress->ServicePostcode->RenderInput(CONTEXT_DEFAULT, TRUE, FALSE, Array("attribute:maxlength"=>4, "SetRequiredId"=>TRUE));
		
		echo "</div>\n"; // Container.ServiceAddressDetails
		
		echo "</div>"; //AddressEdit_Column2
		
		echo "</div>"; // GroupedContent
		
		echo "</form>\n";
	}
}

?>
