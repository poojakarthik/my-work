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

// load remote copy
require_once('../framework/remote_copy.php');

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

$arrAccounts = Array();
$arrAccounts[]	= 1000155934;
$arrAccounts[]	= 1000162306;

$selInvoice = new StatementSelect("Invoice", "*", "Account = <Account> AND InvoiceRun = '46362bac43428'");
$strFileData = "";
foreach ($arrAccounts as $intAccount)
{
	// Get Invoice Details
	$selInvoice->Execute(Array('Account' => $intAccount));
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
$ptrFile = fopen($strLocalPath.$strFilename, 'w');
fwrite($ptrFile, $strFileData);
fclose($ptrFile);

// Remote Copy
$rcpRemoteCopy = new RemoteCopyFTP("203.201.137.55", "vixen", "v1xen");
$rcpRemoteCopy->Connect();
$rcpRemoteCopy->Copy($strLocalPath.$strFilename, "/Incoming/Samples/$strFilename");

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die;



?>
