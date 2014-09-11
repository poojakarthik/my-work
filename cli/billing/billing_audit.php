<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Execute a billing run
//----------------------------------------------------------------------------//
 
// echo "<pre>";

// load application
rLoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// execute bill
$bolResponse = $appBilling->GenerateBillAudit();

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
//echo "</pre>";
die();

?>
