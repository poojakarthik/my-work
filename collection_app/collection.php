<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rating application
//----------------------------------------------------------------------------//
require_once("../framework/require.php");
$arrConfig = LoadApplication();

echo "<pre>\n";

// Application entry point - create an instance of the application object
$appCollection = new ApplicationCollection($arrConfig);

// run the thing
$appCollection->Collect();
$appCollection->_rptCollectionReport->Finish("/home/vixen_log/collection_app/".date("Ymd_His").".log");

// finished
echo("\n-- End of Collection --\n");
echo "</pre>\n";
die();

?>
