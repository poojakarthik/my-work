<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// add recurring charges
//----------------------------------------------------------------------------//
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appCharge = new ApplicationCharge($arrConfig);

// Execute the application
$intCount = $appCharge->AddPinnacleMobileFees();

// finished
echo ("$intCount Pinnacle Mobile Services Charged.");
echo("\n-- End of Charges --\n");
die();

?>
