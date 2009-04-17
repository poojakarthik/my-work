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

// The JS file URI contains a unique 'version number' (the md5 of the JS content).
// If the browser is checking to see if has changed, the copy it has MUST be the latest version.
// No point sending it again, so send a 304 (not changed) header instead.
if (array_key_exists('v', $_GET) && array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER))
{
	header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
	exit;
}

// From the config information, work out where the local application code is and where the framework code is
//TODO! I don't think the appropriate config object has been implemented yet
Define ('LOCAL_BASE_DIR', "../customer");
Define ('FRAMEWORK_BASE_DIR', "../ui");

// include the framework javascript_builder.php file
require_once(FRAMEWORK_BASE_DIR . "/javascript_builder.php");

if (array_key_exists('File', $_GET))
{
	if (is_array($_GET['File']))
	{
		$arrFiles = $_GET['File'];
	}
	else
	{
		$arrFiles = array($_GET['File']);
	}

	if (!VixenIncludeJsFiles($arrFiles, TRUE))
	{
		echo "/* Could not find the requested javascript files */\n";
	}
}
else
{
	echo "/* No javascript files requested */\n";
}

?>
