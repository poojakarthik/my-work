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
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'flex.require.php');

// Set Application Framework Dir
$strApplicationFrameworkDir	= "html/ui/";

// Set Application Dir
$strApplicationDir			= "html/admin/";

// Application Requirements
VixenRequire($strApplicationDir.			'definitions.php');
VixenRequire($strApplicationFrameworkDir.	'functions.php');
VixenRequire($strApplicationFrameworkDir.	'framework.php');
VixenRequire($strApplicationDir.			'menu_items.php');
VixenRequire($strApplicationFrameworkDir.	'application.php');

VixenRequire($strApplicationFrameworkDir.	'db/db_access_ui.php');
VixenRequire($strApplicationFrameworkDir.	'db/db_object_base.php');
VixenRequire($strApplicationFrameworkDir.	'db/db_object.php');
VixenRequire($strApplicationFrameworkDir.	'db/db_list.php');
VixenRequire($strApplicationFrameworkDir.	'db/token.php');
VixenRequire($strApplicationFrameworkDir.	'vixen_table.php');

VixenRequire($strApplicationFrameworkDir.	'json.php');

VixenRequire($strApplicationFrameworkDir . 'style_template/html_elements.php');

?>
