<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_bulk_add.php
//----------------------------------------------------------------------------//
/**
 * service_bulk_add
 *
 * Page Template for the Bulk Add Service webpage
 *
 * Page Template for the Bulk Add Service webpage
 *
 * @file		service_bulk_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
if (DBO()->Account->BusinessName->Value)
{
	$strPageNameSuffix = " - ". DBO()->Account->BusinessName->Value;
}
elseif (DBO()->Account->TradingName->Value)
{
	$strPageNameSuffix = " - ". DBO()->Account->TradingName->Value;
}
else
{
	$strPageNameSuffix = " - ". DBO()->Account->Id->Value;
}

$this->Page->SetName("Add Services". $strPageNameSuffix);

// Set the layout template for the page
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('ServiceBulkAdd',		COLUMN_ONE, HTML_CONTEXT_DEFAULT);
//$this->Page->AddObject('ServiceAddressEdit',	COLUMN_ONE, HTML_CONTEXT_SERVICE_BULK_ADD);

?>