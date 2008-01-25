<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// "Pays off" outstanding Balances with -ve Balances
//----------------------------------------------------------------------------//
 
echo "<pre>";

// load application
require_once("../framework/require.php");
LoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// execute bill
$bolResponse = $appBilling->PayNegativeBalances($strPath);

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();



?>
