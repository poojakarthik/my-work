<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_address_view.php
//----------------------------------------------------------------------------//
/**
 * service_address_view
 *
 * HTML Template for displaying the static address details of a service, in a popup
 *
 * HTML Template for displaying the static address details of a service, in a popup
 *
 * @file		service_address_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateServiceAddressView
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateServiceAddressView
 *
 * HTML Template class for the ServiceAddressView HTML object
 *
 * HTML Template class for the ServiceAddressView HTML object
 *
 * @package	ui_app
 * @class	HtmlTemplateServiceAddressView
 * @extends	HtmlTemplate
 */
class HtmlTemplateServiceAddressView extends HtmlTemplate
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
		
		$dboAddress = DBO()->ServiceAddress;
		// Render the Details
		echo "<div class='GroupedContent'>\n";
		
		echo "<span><strong>User Details</strong></span>\n";
		if ($dboAddress->Residential->Value == 1)
		{
			// The service is "Residential".  Display specific details
			$dboAddress->Name	= GetConstantDescription($dboAddress->EndUserTitle->Value, "EndUserTitleType") ." {$dboAddress->EndUserGivenName->Value} {$dboAddress->EndUserFamilyName->Value}";
			if ($dboAddress->DateOfBirth->Value == NULL || $dboAddress->DateOfBirth->Value == "00000000")
			{
				$dboAddress->DateOfBirth = "[Not Specified]";
			}
			else
			{
				$strDOB = $dboAddress->DateOfBirth->Value;
				$dboAddress->DateOfBirth = substr($strDOB, 6, 2) ."/". substr($strDOB, 4, 2) ."/". substr($strDOB, 0, 4);
			}
			$dboAddress->Name->RenderOutput();
			$dboAddress->DateOfBirth->RenderOutput();
			if ($dboAddress->Employer->Value)
			{
				$dboAddress->Employer->RenderOutput();
			}
			if ($dboAddress->Occupation->Value)
			{
				$dboAddress->Occupation->RenderOutput();
			}
		}
		else
		{
			// The service is "Business".  Display specific details
			$dboAddress->EndUserCompanyName->RenderOutput();
			if ($dboAddress->TradingName->Value)
			{
				$dboAddress->TradingName->RenderOutput();
			}
			$dboAddress->ABN->RenderOutput();
		}

		// Render the billing address details
		$strAddressLine1	= htmlspecialchars($dboAddress->BillAddress1->Value);
		$strAddressLine2	= htmlspecialchars($dboAddress->BillAddress2->Value);
		$strLocality		= htmlspecialchars($dboAddress->BillLocality->Value);
		$strPostCode		= $dboAddress->BillPostcode->Value;
		if (trim($strAddressLine2) != "")
		{
			$strAddressLine2 = "<br />$strAddressLine2";
		}
		$dboAddress->BillAddress = "$strAddressLine1 $strAddressLine2<br />$strLocality $strPostCode";
		
		echo "<div class='ContentSeparator'></div>\n";
		echo "<span><strong>Billing Address Details</strong></span>\n";
		$dboAddress->BillName->RenderOutput();
		$dboAddress->BillAddress->RenderOutput();
		
		// Render the physical address
		echo "<div class='ContentSeparator'></div>\n";
		echo "<span><strong>Physical Service Address Details</strong></span>\n";
		
		$dboAddress->ServiceAddress = $dboAddress->PhysicalAddressDescription->Value;
		$dboAddress->ServiceAddress->RenderOutput();
		
		echo "</div>"; // GroupedContent
		
		echo "<div class='ButtonContainer'><div class='right'>\n";
		$strEditAddressLink = Href()->EditServiceAddress(DBO()->Service->Id->Value);
		$this->Button("Edit", $strEditAddressLink);
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";
	}
}

?>
