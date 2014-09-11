<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// customer_groups_list.php
//----------------------------------------------------------------------------//
/**
 * payment_terms_display
 *
 * Page Template for the "Invoice Run Event" webpage
 *
 * Page Template for the "Invoice Run Event" webpage
 *
 * @file		customer_groups_list.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
$this->Page->SetName("Invoice Run Events");

//Sset the layout template for the page.
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('InvoicerunEvent', COLUMN_ONE, $intContext);


?>