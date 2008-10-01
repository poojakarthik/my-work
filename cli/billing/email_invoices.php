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
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

// Check Parameters
$strInvoiceRun	= $argv[1];
$selInvoiceRun	= new StatementSelect("InvoiceRun", "*", "InvoiceRun = <InvoiceRun>");
if (!$selInvoiceRun->Execute(Array('InvoiceRun' => $strInvoiceRun)))
{
	CliEcho("\n'$strInvoiceRun' is not a valid InvoiceRun!\n");
	exit(1);
}
$arrInvoiceRun	= $selInvoiceRun->Fetch();

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// execute bill
$bolResponse = $appBilling->EmailInvoices($arrInvoiceRun);

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die;
?>
