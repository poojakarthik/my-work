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
 * @package		web_app
 * @author		Joel
 * @version		7.09
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// From the config information, work out where the local application code is and where the framework code is
//TODO! I don't think the appropriate config object has been implemented yet
Define ('LOCAL_BASE_DIR', "../web_app");
Define ('FRAMEWORK_BASE_DIR', "../ui_app");

// include the framework javascript.php file
require_once(FRAMEWORK_BASE_DIR . "/javascript.php");

// if (!VixenIncludeJavascriptFile())
if (!VixenIncludeJsFiles($_GET['File'], TRUE))
{
	echo "/* Could not find the requested javascript files */\n";
}

?>
