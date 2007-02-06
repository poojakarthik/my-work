<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Reprint invoices for a defined list of accounts
//----------------------------------------------------------------------------//
 
 echo "<pre>";

// load application
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// Add in list of accounts
$arrAccounts[]	= 1000009145;
$arrAccounts[]	= 1000007460;
$arrAccounts[]	= 1000008407;
$arrAccounts[]	= 1000157133;
$arrAccounts[]	= 1000161583;
$arrAccounts[]	= 1000158216;
$arrAccounts[]	= 1000157698;
$arrAccounts[]	= 1000160393;
$arrAccounts[]	= 1000158098;
$arrAccounts[]	= 1000155964;
$arrAccounts[]	= 1000160897;

$arrAccounts[]	= 1000155466;
$arrAccounts[]	= 1000154838;
$arrAccounts[]	= 1000010826;
$arrAccounts[]	= 1000154946;
$arrAccounts[]	= 1000154811;
$arrAccounts[]	= 1000155253;
$arrAccounts[]	= 1000156068;
$arrAccounts[]	= 1000155105;
$arrAccounts[]	= 1000155666;
$arrAccounts[]	= 1000155637;
$arrAccounts[]	= 1000155676;
$arrAccounts[]	= 1000155650;
$arrAccounts[]	= 1000155468;
$arrAccounts[]	= 1000155090;
$arrAccounts[]	= 1000009313;
$arrAccounts[]	= 1000004847;
$arrAccounts[]	= 1000158138;
$arrAccounts[]	= 1000155669;
$arrAccounts[]	= 1000155182;
$arrAccounts[]	= 1000155629;
$arrAccounts[]	= 1000155463;
$arrAccounts[]	= 1000155462;
$arrAccounts[]	= 1000154972;

$arrAccounts[]	= 1000157470;
$arrAccounts[]	= 1000155675;
$arrAccounts[]	= 1000160134;
$arrAccounts[]	= 1000156140;
$arrAccounts[]	= 1000162484;
$arrAccounts[]	= 1000160091;
$arrAccounts[]	= 1000162036;
$arrAccounts[]	= 1000162126;
$arrAccounts[]	= 1000160474;
$arrAccounts[]	= 1000162398;
$arrAccounts[]	= 1000155448;
$arrAccounts[]	= 1000162399;
$arrAccounts[]	= 1000159676;
$arrAccounts[]	= 1000162272;
$arrAccounts[]	= 1000161896;
$arrAccounts[]	= 1000161662;
$arrAccounts[]	= 1000162422;
$arrAccounts[]	= 1000154838;
$arrAccounts[]	= 1000155425;
$arrAccounts[]	= 1000160187;
$arrAccounts[]	= 1000161442;
$arrAccounts[]	= 1000158564;
$arrAccounts[]	= 1000159454;
$arrAccounts[]	= 1000162403;
$arrAccounts[]	= 1000156445;
$arrAccounts[]	= 1000158849;
$arrAccounts[]	= 1000162265;
$arrAccounts[]	= 1000160762;
$arrAccounts[]	= 1000155640;
$arrAccounts[]	= 1000161203;


// Get latest invoice run
$selInvoiceRun = new StatementSelect("Invoice", "InvoiceRun", "1", "CreatedOn DESC", 1);
$selInvoiceRun->Execute();
$arrInvoiceRun = $selInvoiceRun->Fetch();
$strInvoiceRun = $arrInvoiceRun['InvoiceRun'];

$arrInvoices = Array();

// Get list of invoices
$selInvoices = new StatementSelect("Invoice", "Id", "Account = <Account> AND InvoiceRun = '$strInvoiceRun'");
foreach ($arrAccounts as $intAccount)
{
	$selInvoices->Execute(Array('Account' => $intAccount));
	$arrInvoice = $selInvoices->Fetch();
	$arrInvoices[] = $arrInvoice['Id'];
}

// reprint
$bolResponse = $appBilling->Reprint($arrInvoices);

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();

?>

?>
