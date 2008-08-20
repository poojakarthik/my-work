<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// provisioning_history_view.php
//----------------------------------------------------------------------------//
/**
 * provisioning_history_view
 *
 * Page Template for the View Provisioning History popup window
 *
 * Page Template for the View Provisioning History popup window
 *
 * @file		provisioning_history_view.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// set the page title
if (DBO()->Service->Id->IsSet)
{
	$strServiceType	= GetConstantDescription(DBO()->Service->ServiceType->Value, "service_type");
	$strStatus		= (DBO()->Service->Status->Value != SERVICE_ACTIVE)? "(". GetConstantDescription(DBO()->Service->Status->Value, "service_status") .")" : "";
	$strFNN			= DBO()->Service->FNN->Value;
	$strPageName = "Provisioning History - $strServiceType - $strFNN";
}
else
{
	$strPageName = "Provisioning History";
}
$this->Page->SetName($strPageName);

// Set the layout template for the page
$this->Page->SetLayout('popup_layout');

// Add the Html Objects to their respective columns
$this->Page->AddObject('ProvisioningHistoryList', COLUMN_ONE, HTML_CONTEXT_POPUP);

?>
