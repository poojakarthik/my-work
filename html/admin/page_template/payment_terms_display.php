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
 * Page Template for the "Payment Terms Display" webpage
 *
 * Page Template for the "Payment Terms Display" webpage
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
$this->Page->SetName("Payment Process");

//Sset the layout template for the page.
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('PaymentTermsDisplay', COLUMN_ONE, $intContext);


?>