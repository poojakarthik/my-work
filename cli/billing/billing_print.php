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
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing::Print Started @ $strDateTime", "viXen Billing Started @ $strDateTime");

// execute bill
$bolResponse = $appBilling->GenerateInvoiceOutput();

// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing::Print Ended @ $strDateTime", "viXen Billing Ended @ $strDateTime");

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();

?>
