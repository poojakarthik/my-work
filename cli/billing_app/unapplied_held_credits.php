<?php

// load framework
LoadFramework();
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