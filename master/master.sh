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

// Application entry point - create an instance of the application object
$appMaster = new ApplicationMaster($arrConfig);

// Run the application
$appMaster->Run();

// finished
die;

?>
