<?php

//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// system_settings_menu.php
//----------------------------------------------------------------------------//
/**
 * system_settings_menu
 *
 * Page Template for the "System Settings Menu" webpage
 *
 * Page Template for the "System Settings Menu" webpage
 *
 * @file		system_settings_menu.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Set the page title
$this->Page->SetName("System Settings");

//Sset the layout template for the page.
$this->Page->SetLayout('1Column');

// Add the Html Objects to their respective columns
$this->Page->AddObject('SystemSettingsMenu', COLUMN_ONE);


?>
