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

// Get Invoice Details
$selInvoice = new StatementSelect("Invoice", "*", "Account = 1000158008 AND InvoiceRun = '46362bac43428'");
$selInvoice->Execute();
$arrInvoice = $selInvoice->Fetch();

// Debug bill output
Debug($appBilling->_arrBillOutput[BILL_PRINT]->AddInvoice($arrInvoice, TRUE));

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();



?>
