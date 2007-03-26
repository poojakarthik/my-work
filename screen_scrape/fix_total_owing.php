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

$rptReport = new Report("Fix Total Owing for ".date("Y-m-d", time()), "rich@voiptelsystems.com.au");



// Search for Etech Invoices from January
$arrBalanceColumns	= Array();
$arrVixenColumns	= Array();
$arrBalanceColumns['Balance']		= 0;
$arrVixenColumns['AccountBalance']	= NULL;
$arrVixenColumns['TotalOwing']		= MySQLFunction("<AccountBalance> + Total + Tax");
$selEtechJan		= new StatementSelect("Invoice", "Account, TotalOwing", "InvoiceRun = '45dfe46ae67cd'");
$updVixenFeb		= new StatementUpdate("Invoice", "Account = <Account> AND InvoiceRun = '45dfe46ae67cd'", $arrVixenColumns);
$updEtechBalances	= new StatementUpdate("Invoice", "Account = <Account> AND CreatedOn < '2007-03-01'", $arrBalanceColumns);

$rptReport->AddMessage("\n\n[ UPDATING viXen INVOICES ]\n\n");

// for each invoice
$selEtechJan->Execute();
$arrInvoices = $selEtechJan->FetchAll();
$intPassed = 0;
$intTotal = count($arrInvoices);
foreach ($arrInvoices as $arrInvoice)
{
	if ($arrInvoice['TotalOwing'] >= 0)
	{
		continue;
	}
	
	$rptReport->AddMessage(" + Updating AccountBalance and TotalOwing for Account #{$arrInvoice['Account']}...\t\t");
	
	$arrVixenColumns['AccountBalance']	= $arrInvoice['TotalOwing'];
	$arrVixenColumns['TotalOwing']		= MySQLFunction("<AccountBalance> + Total + Tax", Array('AccountBalance' => $arrInvoice['TotalOwing']));
	if (!$updVixenFeb->Execute($arrVixenColumns, Array('Account' => $arrInvoice['Account'])))
	{
		$rptReport->AddMessage("[ FAILED ]\n");
		continue;
	}
	$rptReport->AddMessage("[   OK   ]\n");
	$intPassed++;
}

$rptReport->AddMessage("\n * $intPassed of $intTotal Invoices updated\n");

// Zero out balances on previous invoices if a negative or zero balance
$rptReport->AddMessage("\n[ ZERO OUT PREVIOUS INVOICES ]\n\n");

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
	if (!$intUpdated = $updEtechBalances->Execute($arrBalanceColumns, Array('Account' => $arrInvoice['Account'])))
	{
		$rptReport->AddMessage("[ FAILED ]\n");
		continue;
	}
	$rptReport->AddMessage("[   OK   ]\n");
	$intTotalUpdated += $intUpdated;
	$intPassed++;
}

$rptReport->AddMessage("\n * $intPassed of $intTotal Accounts updated.  Total of $intTotalUpdated Invoices updated\n\n");
$rptReport->Finish("/home/vixen_reports/fix_total_owing_".date("Y-m-d", time()).".log");

?>





