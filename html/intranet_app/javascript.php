<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// javascript
//----------------------------------------------------------------------------//
/**
 * javascript
 *
 * Retrieves the desired javascript file
 *
 * Retrieves the desired javascript file
 * It will first look in the Customer's instance of the application,
 * then in the shared directory of the application (web_app)
 * then in the shared directory of the framework (ui_app)
 *
 * @file		javascript.php
 * @language	PHP
 * @package		intranet_app
 * @author		Joel
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

/* 
 * Javascript files can be defined in the application's local javascript directory
 * and in the ui_app directory.  Any js files in the application's local directory
 * will override those in the ui_app directory if they have the same name
 * NOTE: I could have loaded the application's require.php to get these 2 constants
 * but that would be fairly inefficient because we don't really need to load the whole
 * application framework.  We just want to retrieve the requested js files
 */
 
Define ('LOCAL_BASE_DIR', "../intranet_app");
Define ('FRAMEWORK_BASE_DIR', "../ui_app");

// include the framework javascript.php file
require_once(FRAMEWORK_BASE_DIR . "/javascript.php");

// if (!VixenIncludeJavascriptFile())
if (!VixenIncludeJsFiles($_GET['File'], TRUE))
{
	echo "/* Could not find the requested javascript files */\n";
}

?>
