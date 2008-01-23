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
$strApplicationDir = "ui_app/";
// If absolute paths don't work then use: 
// $strApplicationDir = "../ui_app/"; and use require_once instead of VixenRequire

// Application Requirements
VixenRequire($strApplicationDir.'definitions.php');
VixenRequire($strApplicationDir.'functions.php');
VixenRequire($strApplicationDir.'framework.php');
VixenRequire($strApplicationDir.'menu_items.php');
VixenRequire($strApplicationDir.'application.php');

VixenRequire($strApplicationDir.'db/db_access_ui.php');
VixenRequire($strApplicationDir.'db/db_object_base.php');
VixenRequire($strApplicationDir.'db/db_object.php');
VixenRequire($strApplicationDir.'db/db_list.php');
VixenRequire($strApplicationDir.'db/token.php');
VixenRequire($strApplicationDir.'vixen_table.php');

VixenRequire($strApplicationDir.'json.php');

VixenRequire($strApplicationDir . 'style_template/html_elements.php');

?>
