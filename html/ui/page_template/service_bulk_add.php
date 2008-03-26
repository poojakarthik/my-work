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
$this->Page->SetName("Add Services");

// Set the layout template for the page
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('ServiceBulkAdd',			COLUMN_ONE, HTML_CONTEXT_DEFAULT);
$this->Page->AddObject('ProvisioningServiceList',	COLUMN_ONE, HTML_CONTEXT_SERVICE_BULK_ADD);

?>