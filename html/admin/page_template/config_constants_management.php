<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// config_constants_management.php
//----------------------------------------------------------------------------//
/**
 * config_constants_management
 *
 * Page Template for the "Config Constants Management" webpage
 *
 * Page Template for the "Config Constants Management" webpage
 *
 * @file		config_constants_management.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
$this->Page->SetName("Constants");

//Sset the layout template for the page.
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('ConfigConstantList', COLUMN_ONE, HTML_CONTEXT_ALL);


?>
