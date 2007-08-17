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
require_once("../framework/require.php");
LoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// execute bill
//$strPath = "/home/vixen_invoices/".date("Y/n/", strtotime("-1 month", time()));
$strPath = "/home/richdavis/Desktop/Invoices/July 07";
$bolResponse = $appBilling->EmailInvoicePDFs($strPath);

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();



?>
