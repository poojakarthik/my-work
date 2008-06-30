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

define('COLLECTION_DEBUG_MODE',		TRUE);

echo "<pre>\n";

// Application entry point - create an instance of the application object
$appCollection = new ApplicationCollection($arrConfig);

// run the thing
$appCollection->Collect();
$appCollection->_rptCollectionReport->Finish(FILES_BASE_PATH."log/collection/".date("Ymd_His").".log");

// finished
echo("\n-- End of Collection --\n");
echo "</pre>\n";
die();

?>
