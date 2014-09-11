<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// generate InvoiceOutput for a billing run and print samples
//----------------------------------------------------------------------------//
 
// load application
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

// Parse command-line parameters
$strBillingModule	= trim($argv[1]);
if (!$strBillingModule)
{
	CliEcho("No Billing Module Provided!  Options: BILL_FLEX_XML, BILL_PRINT, BILL_PRINT_ETECH.");
	die;
}
elseif (!defined($strBillingModule))
{
	CliEcho("Billing Module '$strBillingModule' does not exist!  Options: BILL_FLEX_XML, BILL_PRINT, BILL_PRINT_ETECH.");
	die;
}


// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing::Print Started @ $strDateTime", "viXen Billing Started @ $strDateTime");

// execute bill
$bolResponse = $appBilling->GenerateInvoiceOutput(constant($strBillingModule));

// Email Status
$strDateTime = date("Y-m-d H:i:s");
SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "viXen Billing::Print Ended @ $strDateTime", "viXen Billing Ended @ $strDateTime");

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
die;

?>
