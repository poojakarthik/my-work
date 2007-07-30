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

// Set Application name
//$strApplicationName = "ui";
$strApplicationName = "web";

// Set Framework Dir
$strFrameworkDir = "../framework/";

// Set Application Framework Dir
$strApplicationFrameworkDir	= "../ui_app/";

// Set Application Dir
$strApplicationDir = "";

// Framework Requirements
require_once($strFrameworkDir."require.php");

// Application Requirements
require_once($strApplicationDir.'definitions.php');
require_once($strApplicationFrameworkDir.'functions.php');
require_once($strApplicationFrameworkDir.'framework.php');
require_once($strApplicationDir.'menu_items.php');
require_once($strApplicationFrameworkDir.'application.php');

require_once($strApplicationFrameworkDir.'db/db_access_ui.php');
require_once($strApplicationFrameworkDir.'db/db_object_base.php');
require_once($strApplicationFrameworkDir.'db/db_object.php');
require_once($strApplicationFrameworkDir.'db/db_list.php');
require_once($strApplicationFrameworkDir.'db/token.php');
require_once($strApplicationFrameworkDir.'vixen_table.php');

require_once($strApplicationFrameworkDir.'json.php');

require_once(TEMPLATE_STYLE_DIR.'html_elements.php');
 
 ?>
