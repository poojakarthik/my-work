<?php

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];



// Search for Etech Invoices from January
$arrColumns = Array();
$arrColumns['Balance']	= 0;
$selEtechJan		= new StatementSelect("Invoice", "Account, TotalOwing", "InvoiceRun = '45dfe46ae67cd'");
$updVixenFeb		= new StatementUpdate("Invoice", "Account = <Account> AND InvoiceRun = '45dfe46ae67cd'");
$updEtechBalances	= new StatementUpdate("Invoice", "Account = <Account> AND CreatedOn < '2007-02-01'", $arrColumns);

echo "\n\n[ UPDATING viXen INVOICES ]\n\n";

// for each invoice
$selEtechJan->Execute();
$arrInvoices = $selEtechJan->FetchAll();
$intPassed = 0;
$intTotal = count($arrInvoices);
foreach ($arrInvoices as $arrInvoice)
{
	echo " + Updating TotalOwing for Account #{$arrInvoice['Account']}...\t\t";
	
	if (!$updVixenFeb->Execute(Array('Account' => $arrInvoice['Account'])))
	{
		echo "[ FAILED ]\n";
		continue;
	}
	echo "[   OK   ]\n";
	$intPassed++;
}

echo "\n * $intPassed of $intTotal Invoices updated\n";

// Zero out balances on previous invoices if a negative or zero balance
echo "\n[ ZERO OUT PREVIOUS INVOICES ]\n\n";

// for each invoice
$intToUpdate	= 0;
$intPassed		= 0;

foreach ($arrInvoices as $arrInvoice)
{
	// Check the balance
	if ($arrInvoice['TotalOwing'] > 0)
	{
		continue;
	}
	
	// Zero out the balances of all previous invoices
	$intToUpdate++;
	if (!$intUpdated = $updEtechBalances->Execute($arrColumns, Array('Account' => $arrInvoice['Account'])))
	{
		echo "[ FAILED ]\n";
		continue;
	}
	echo "[   OK   ]\n";
	$intTotalUpdated += $intUpdated;
	$intPassed++;
}

echo "\n * $intPassed of $intTotal Accounts updated.  Total of $intTotalUpdated Invoices updated\n\n";

?>





