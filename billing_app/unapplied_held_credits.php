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
ob_start();

echo "[ FIXING UNAPPLIED HELD CREDITS ]\n\n";

$arrColumns = Array();
$arrColumns['Balance'] = new MySQLFunction("GREATEST(Balance + AccountBalance, 0)");
$ubiInvoices = new StatementUpdateById("Invoice", $arrColumns);
$selInvoices = new StatementSelect(	"Invoice",
									"Id",
									"Balance > TotalOwing AND " .
									"TotalOwing >= 0 AND " .
									"AccountBalance < 0 AND " .
									"InvoiceRun = '45dfe46ae67cd'");

$selInvoices->Execute();
$arrInvoices = $selInvoices->FetchAll();
$intPassed = 0;
foreach ($arrInvoices as $arrInvoice)
{
	echo " + Account #{$arrInvoice['Id']}...\t\t\t";
	ob_flush();
	$arrColumns['Id']	= $arrInvoice['Id'];
	if (!$ubiInvoices->Execute($arrColumns))
	{
		echo "[ FAILED ]\n";
	}
	else
	{
		echo "[   OK   ]\n";
		$intPassed++;
	}
}
$intTotal = count($arrInvoices);

echo "\n * Updated $intPassed of $intTotal Invoices!\n\n";


?>