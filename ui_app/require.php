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
 * @package		framework
 * @author		Rich 'Waste' Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Framework Requirements
require_once("../framework/require.php");

// Set Application Dir
$strApplicationDir = GetVixenBase() . "ui_app/";
// If absolute paths don't work then use: 
// $strApplicationDir = "../ui_app/";

// Application Requirements
require_once($strApplicationDir.'definitions.php');
require_once($strApplicationDir.'functions.php');
require_once($strApplicationDir.'framework.php');
require_once($strApplicationDir.'menu_items.php');
require_once($strApplicationDir.'application.php');

require_once($strApplicationDir.'db/db_access_ui.php');
require_once($strApplicationDir.'db/db_object_base.php');
require_once($strApplicationDir.'db/db_object.php');
require_once($strApplicationDir.'db/db_list.php');
require_once($strApplicationDir.'db/token.php');
require_once($strApplicationDir.'vixen_table.php');

require_once($strApplicationDir.'json.php');

require_once($strApplicationDir . 'style_template/html_elements.php');

?>
