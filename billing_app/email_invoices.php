<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Emails Invoices to specified accounts
//----------------------------------------------------------------------------//
 
 echo "<pre>";

// load application
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// execute bill
//$strPath = "/home/vixen_invoices/2007/2";
$strPath = "/home/richdavis/invoice_temp/2007/02/";
$bolResponse = $appBilling->EmailInvoicePDFs($strPath);

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();



?>
