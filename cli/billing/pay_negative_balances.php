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
require_once("../../flex.require.php");
LoadApplication();

// execute bill
$bolResponse = ApplicationBilling::PayNegativeBalances();

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();



?>
