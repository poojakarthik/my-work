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

// The JS file URI contains a unique 'version number' (the md5 of the JS content).
// If the browser is checking to see if has changed, the copy it has MUST be the latest version.
// No point sending it again, so send a 304 (not changed) header instead.
if (array_key_exists('v', $_GET) && array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER))
{
	header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
	exit;
}

/* 
 * Javascript files can be defined in the application's local javascript directory
 * and in the ui_app directory.  Any js files in the application's local directory
 * will override those in the ui_app directory if they have the same name
 * NOTE: I could have loaded the application's require.php to get these 2 constants
 * but that would be fairly inefficient because we don't really need to load the whole
 * application framework.  We just want to retrieve the requested js files
 */
 
Define('LOCAL_BASE_DIR', "../ui");
Define('FRAMEWORK_BASE_DIR', "../ui");

// include the framework javascript_builder.php file
require_once(FRAMEWORK_BASE_DIR . "/javascript_builder.php");

// if (!VixenIncludeJavascriptFile())
if (!VixenIncludeJsFiles($_GET['File'], TRUE))
{
	echo "/* Could not find the requested javascript files */\n";
}

?>
