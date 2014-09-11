<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// add recurring charges DEPRECATED
//----------------------------------------------------------------------------//

// The new Recurring Charge app is located at cli/recurring_charges.php, and it uses /lib/cli/app/Cli_App_Recurring_Charges.php

require_once("../../flex.require.php");
$arrConfig = LoadApplication();

// Application entry point - create an instance of the application object
$appCharge = new ApplicationCharge($arrConfig);

// Execute the application
$appCharge->Execute();

// finished
echo("\n-- End of Charges::Recurring --\n");
echo "</pre>";
die;

?>
