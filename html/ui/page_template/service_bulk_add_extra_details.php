<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_bulk_add_extra_details.php
//----------------------------------------------------------------------------//
/**
 * service_bulk_add_extra_details.php
 *
 * Page Template for the Extra Details popup window, used on the Service Bulk Add webpage
 *
 * Page Template for the Extra Details popup window, used on the Service Bulk Add webpage
 *
 * @file		service_bulk_add_extra_details.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the layout template for the page.
$this->Page->SetLayout('popup_layout');

// Work out what HtmlTemplate to use
switch (DBO()->Service->ServiceType->Value)
{
	case SERVICE_TYPE_LAND_LINE:
		$strHtmlTemplate = "";
		$this->Page->AddObject("ServiceExtraDetailsLandLine", COLUMN_ONE, HTML_CONTEXT_DEFAULT);
		$this->Page->AddObject("ServiceAddressEdit", COLUMN_ONE, HTML_CONTEXT_SERVICE_BULK_ADD);
		break;
	case SERVICE_TYPE_MOBILE:
		$this->Page->AddObject("ServiceExtraDetailsMobile", COLUMN_ONE, HTML_CONTEXT_DEFAULT);
		break;
	case SERVICE_TYPE_INBOUND:
		$this->Page->AddObject("ServiceExtraDetailsInbound", COLUMN_ONE, HTML_CONTEXT_DEFAULT);
		break;
	default:
		throw new Exception("ERROR: No Extra Details for ServiceType: ". DBO()->Service->ServiceType->Value);
}



?>
