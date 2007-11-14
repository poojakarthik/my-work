#!/usr/bin/php
<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// master application
//----------------------------------------------------------------------------//
require_once('../framework/require.php');
$arrConfig = LoadApplication();


// Parse command line parameters for config file
if (!$argv[1])
{
	CliEcho("\nYou must pass a vaild Master Config File as the first parameter!\n");
	die;
}
$strFilename	= trim($argv[1]);
if (!file_exists($strFilename))
{
	CliEcho("\nFile '$strFilename does not exist!'\n");
	die;
}
require_once(strFilename);

// Application entry point - create an instance of the application object
$appMaster = new ApplicationMaster($arrConfig);

// Run the application
$appMaster->Run();

// finished
die;

?>
