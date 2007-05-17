<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Emails Invoices to specified accounts
//----------------------------------------------------------------------------//
 
// load application
require_once('application_loader.php');

// load remote copy
require_once('../framework/remote_copy.php');

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);




//----------------------------------------------------------------------------//
// CONFIG
//----------------------------------------------------------------------------//

// TODO:Sean -> Put the InvoiceRun Here
$strInvoiceRun = "";

// TODO:Sean -> Put the Account numbers here!
$arrAccounts = Array();
$arrAccounts[]	= 1000154811;
$arrAccounts[]	= 1000154842;
$arrAccounts[]	= 1000154843;
$arrAccounts[]	= 1000154937;

//----------------------------------------------------------------------------//
// /CONFIG
//----------------------------------------------------------------------------//


// make sure output buffering is off before we start it
// this will ensure same effect whether or not ob is enabled already
while (ob_get_level()) {
    ob_end_flush();
}
// start output buffering
if (ob_get_length() === false) {
    ob_start();
}

$selInvoice = new StatementSelect("Invoice", "*", "Account = <Account> AND InvoiceRun = '$strInvoiceRun'");
$strFileData = "";
foreach ($arrAccounts as $intAccount)
{
	echo "$intAccount...\n";
	ob_flush();
	// Get Invoice Details
	if (!$selInvoice->Execute(Array('Account' => $intAccount)))
	{
		// No invoice for this account
		continue;
	}
	$arrInvoice = $selInvoice->Fetch();
	$strFileData .= "0010{$arrInvoice['Id']}".$appBilling->_arrBillOutput[BILL_PRINT]->AddInvoice($arrInvoice, TRUE)."\n";
}
// Add footer
$strFileData .= "0019" .
				date("d/m/Y") .
				str_pad(count($arrAccounts), 10, "0", STR_PAD_LEFT) .
				str_pad(0, 10, "0", STR_PAD_LEFT) .
				str_pad(0, 10, "0", STR_PAD_LEFT) .
				str_pad(0, 10, "0", STR_PAD_LEFT) .
				str_pad(0, 10, "0", STR_PAD_LEFT) .
				str_pad(0, 10, "0", STR_PAD_LEFT) .
				str_pad(0, 10, "0", STR_PAD_LEFT);
				

				
// Write to file
$strLocalPath = "/home/vixen_bill_output/";
$strFilename = "reprint".date("Y-m-d_His", time()).".vbf";
echo "\nWriting to '{$strLocalPath}$strFilename'...\n";
ob_flush();
$ptrFile = fopen($strLocalPath.$strFilename, 'w');
fwrite($ptrFile, $strFileData);
fclose($ptrFile);

// Remote Copy
echo "\nCopying to BillPrint...\n";
ob_flush();
$rcpRemoteCopy = new RemoteCopyFTP("203.201.137.55", "vixen", "v1xen");
$rcpRemoteCopy->Connect();
$rcpRemoteCopy->Copy($strLocalPath.$strFilename, "/Incoming/Samples/$strFilename");
$rcpRemoteCopy->Disconnect();

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die;



?>
