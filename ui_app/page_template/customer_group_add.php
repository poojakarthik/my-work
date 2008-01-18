<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// customer_group_add.php
//----------------------------------------------------------------------------//
/**
 * customer_group_add
 *
 * Page Template for the "Add Customer Group" webpage
 *
 * Page Template for the "Add Customer Group" webpage
 *
 * @file		customer_group_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
$this->Page->SetName("New Customer Group");

//Sset the layout template for the page.
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('CustomerGroupNew', COLUMN_ONE, HTML_CONTEXT_DEFAULT, "CustomerGroupNewContainerDiv");

?>
