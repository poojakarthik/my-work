<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// provisioning_request.php
//----------------------------------------------------------------------------//
/**
 * provisioning_request
 *
 * Page Template for the Bulk Provisioning Request webpage
 *
 * Page Template for the Bulk Provisioning Request webpage
 *
 * @file		provisioning_request.php
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

$this->Page->SetName("Provisioning". $strPageNameSuffix);

// Set the layout template for the page
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('ProvisioningRequest',		COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('ProvisioningServiceList',	COLUMN_ONE, HTML_CONTEXT_DEFAULT, "ProvisioningServiceListDiv");
$this->Page->AddObject('ProvisioningHistoryList',	COLUMN_ONE, HTML_CONTEXT_PAGE);

?>