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
		$strHtmlTemplate = "ServiceExtraDetailsLandLine";
		break;
	case SERVICE_TYPE_MOBILE:
		$strHtmlTemplate = "ServiceExtraDetailsMobile";
		break;
	case SERVICE_TYPE_INBOUND:
		$strHtmlTemplate = "ServiceExtraDetailsInbound";
		break;
	default:
		throw new Exception("ERROR: No Extra Details for ServiceType: ". DBO()->Service->ServiceType->Value);
}

$this->Page->AddObject($strHtmlTemplate, COLUMN_ONE, HTML_CONTEXT_DEFAULT);

?>
