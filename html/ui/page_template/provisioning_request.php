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
$this->Page->SetName("Provisioning");

// Set the layout template for the page
$this->Page->SetLayout('3Column_65_35');

// Add the Html Objects to their respective columns
$this->Page->AddObject('ProvisioningServiceList',	COLUMN_ONE, HTML_CONTEXT_DEFAULT, "ProvisioningServiceListDiv");
$this->Page->AddObject('ProvisioningRequest',		COLUMN_TWO, HTML_CONTEXT_LEDGER_DETAIL);


?>