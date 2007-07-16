<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// add recurring charges
//----------------------------------------------------------------------------//
require_once("../framework/require.php");
LoadApplication();

// Application entry point - create an instance of the application object
$appCharge = new ApplicationCharge($arrConfig);

// Execute the application
$appCharge->Execute();

// finished
echo("\n-- End of Charges::Recurring --\n");
echo "</pre>";
die;

?>
