<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// generate InvoiceOutput for a billing run and print samples
//----------------------------------------------------------------------------//
 
 echo "<pre>";

// load application
LoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// execute bill
$bolResponse = $appBilling->GenerateInvoiceOutput();

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();

?>
