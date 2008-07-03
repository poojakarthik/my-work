<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rating application
//----------------------------------------------------------------------------//
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

define('COLLECTION_DEBUG_MODE',		FALSE);

// Application entry point - create an instance of the application object
$appCollection = new ApplicationCollection($arrConfig);

// run the thing
$appCollection->Collect();

// finished
CliEcho("\n-- End of Collection --\n");
exit(0);

?>
