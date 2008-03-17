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
		$dboAddress = DBO()->ServiceAddress;
		// Render the Details
		echo "<div class='GroupedContent'>\n";
		
		echo "<span><strong>User Details</strong></span>\n";
		if ($dboAddress->Residential->Value == 1)
		{
			// The service is "Residential".  Display specific details
			$dboAddress->Name	= GetConstantDescription($dboAddress->EndUserTitle->Value, "EndUserTitleType") ." {$dboAddress->EndUserGivenName->Value} {$dboAddress->EndUserFamilyName->Value}";
			if ($dboAddress->DateOfBirth->Value == NULL || $dboAddress->DateOfBirth->Value = "00000000")
			{
				$dboAddress->DateOfBirth = "[Not Specified]";
			}
			else
			{
				$strDOB = $dboAddress->DateOfBirth->Value;
				$dboAddress->DateOfBirth = substr($strDOB, 6, 2) ." / ". substr($strDOB, 4, 2) ." / ". substr($strDOB, 0, 4);
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
			$dboAddress->ABN->RenderOutput();
			if ($dboAddress->TradingName->Value)
			{
				$dboAddress->TradingName->Value;
			}
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
		
		echo "<div class='ContentSeparator'></div>\n";
		echo "<span><strong>Physical Service Address Details</strong></span>\n";
		
		$strPropertyName	= trim($dboAddress->ServicePropertyName->Value);
		$strLocality		= trim($dboAddress->ServiceLocality->Value);
		$strState			= trim($dboAddress->ServiceState->Value);
		$strPostCode		= $dboAddress->ServicePostcode->Value;
		$strAddressTypeLine = "";
		$strStreetLine		= "";
		
		if ($dboAddress->ServiceAddressType->Value == SERVICE_ADDR_TYPE_LOT)
		{
			// The service address is a "LOT"
			$strAddressTypeLine	= trim("Allotment {$dboAddress->ServiceAddressTypeNumber->Value} {$dboAddress->ServiceAddressTypeSuffix->Value}");
			$strStreetType		= ($dboAddress->ServiceStreetType->Value == SERVICE_STREET_TYPE_NOT_REQUIRED)? "" : GetConstantDescription($dboAddress->ServiceStreetType->Value, "ServiceStreetType"); 
			$strStreetLine		= trim($dboAddress->ServiceStreetName->Value ." $strStreetType ". GetConstantDescription($dboAddress->ServiceStreetSuffixType->Value, "ServiceStreetSuffixType"));
		}
		else if (isset($GLOBALS['*arrConstant']['PostalAddrType'][$dboAddress->ServiceAddressType->Value]))
		{
			// The service address is a postal service address
			$strAddressTypeLine = trim(GetConstantDescription($dboAddress->ServiceAddressType->Value, "ServiceAddrType") ." {$dboAddress->ServiceAddressTypeNumber->Value} {$dboAddress->ServiceAddressTypeSuffix->Value}");
			
		}
		else
		{
			
			// The service address is a standard address, and may or may not have an Address Type
			$strAddressTypeLine = trim(GetConstantDescription($dboAddress->ServiceAddressType->Value, "ServiceAddrType") ." {$dboAddress->ServiceAddressTypeNumber->Value} {$dboAddress->ServiceAddressTypeSuffix->Value}");
			
			$strStreetNumber = "";
			if ($dboAddress->ServiceStreetNumberStart->Value != "")
			{
				$strStreetNumber = $dboAddress->ServiceStreetNumberStart->Value;
			}
			if ($dboAddress->ServiceStreetNumberEnd->Value != "")
			{
				$strStreetNumber .= " - ". $dboAddress->ServiceStreetNumberEnd->Value;
			}
			$strStreetNumber .= " ". $dboAddress->ServiceStreetNumberSuffix->Value;
			
			$strStreetType = ($dboAddress->ServiceStreetType->Value == SERVICE_STREET_TYPE_NOT_REQUIRED)? "" : GetConstantDescription($dboAddress->ServiceStreetType->Value, "ServiceStreetType");
			$strStreetLine = trim("$strStreetNumber {$dboAddress->ServiceStreetName->Value} $strStreetType ". GetConstantDescription($dboAddress->ServiceStreetSuffixType->Value, "ServiceStreetSuffixType"));
			
		}
		
		$strAddress = "";
		if ($strPropertyName != "")
		{
			$strAddress .= $strPropertyName ."<br /> ";
		}
		if ($strAddressTypeLine != "")
		{
			$strAddress .= $strAddressTypeLine ."<br /> ";
		}
		if ($strStreetLine != "")
		{
			$strAddress .= $strStreetLine ."<br /> ";
		}
		$strAddress .= "$strLocality<br /> $strState $strPostCode";
		
		$dboAddress->ServiceAddress = ucwords($strAddress);
		$dboAddress->ServiceAddress->RenderOutput();
		
		
		
		
		echo "</div>"; // GroupedContent
		
		echo "<div class='ButtonContainer'><div class='right'>\n";
		$strEditAddressLink = Href()->EditServiceAddress(DBO()->Service->Id->Value);
		$this->Button("Edit", $strEditAddressLink);
		$this->Button("Close", "Vixen.Popup.Close(this);");
		echo "</div></div>\n";
		
		// If there is only 1 rate then open the Rate Details popup to display the rate
		if (DBO()->RateGroup->TotalRateCount->Value == 1)
		{
			DBL()->Rate->rewind();
			$dboRate = DBL()->Rate->current();
			$strDisplayRatePopup = Href()->ViewRate($dboRate->Id->Value, FALSE);
			echo "<script type='text/javascript'>$strDisplayRatePopup</script>\n";
		}
	}
}

?>
