<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// require
//----------------------------------------------------------------------------//
/**
 * require
 *
 * Handles all file requirements for an application
 *
 * This file should load all files required by an application.
 * This file should not set up any objects or produce any output
 *
 * @file		require.php
 * @language	PHP
 * @package		web_app
 * @author		Rich 'Waste' Davis modified by Joel 'MagnumSwordFortress' Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Framework Requirements
require_once("../../flex.require.php");

// Set Application Framework Dir
$strApplicationFrameworkDir	= "ui_app/";

// Set Application Dir
$strApplicationDir			= "web_app/";

// If absolute paths don't work then use: 
// $strApplicationDir			= "../web_app/";
// $strApplicationFrameworkDir	= "../ui_app/";

// Application Requirements
VixenRequire($strApplicationDir.'definitions.php');
VixenRequire($strApplicationFrameworkDir.'functions.php');
VixenRequire($strApplicationFrameworkDir.'framework.php');
VixenRequire($strApplicationDir.'menu_items.php');
VixenRequire($strApplicationFrameworkDir.'application.php');

VixenRequire($strApplicationFrameworkDir.'db/db_access_ui.php');
VixenRequire($strApplicationFrameworkDir.'db/db_object_base.php');
VixenRequire($strApplicationFrameworkDir.'db/db_object.php');
VixenRequire($strApplicationFrameworkDir.'db/db_list.php');
VixenRequire($strApplicationFrameworkDir.'db/token.php');
VixenRequire($strApplicationFrameworkDir.'vixen_table.php');

VixenRequire($strApplicationFrameworkDir.'json.php');

VixenRequire($strApplicationFrameworkDir . 'style_template/html_elements.php');
 
 ?>
