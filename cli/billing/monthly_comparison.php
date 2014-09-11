<?php

require_once("../../flex.require.php");




$intMonthsBack = 1;

$selBottom10	= new StatementSelect("Invoice", "ROUND(COUNT(Id) / 10) AS Bottom10", "InvoiceRun = <InvoiceRun> AND Total != 0");
$selTop10		= new StatementSelect("Invoice", "ROUND((COUNT(Id) / 100) * 90) AS Top10", "InvoiceRun = <InvoiceRun> AND Total != 0");
$selInvoiceRuns	= new StatementSelect("InvoiceRun", "*", "1", "BillingDate DESC", $intMonthsBack);
$selAvgAll		= new StatementSelect("Invoice", "AVG(Total + Tax) AS GrandTotal, COUNT(Id) AS Invoices", "InvoiceRun = <InvoiceRun> AND Total != 0");
$selBusLLRental	= new StatementSelect("CDR", "COUNT(Id) as Total", "InvoiceRun = <InvoiceRun> AND Description LIKE '%Bus%Line%Rental%'");
$selLostAccount	= new StatementSelect("Invoice", "Account", "InvoiceRun = <InvoiceRunOld> AND Account NOT IN (SELECT Invoice2.Account FROM Invoice Invoice2 WHERE Invoice2.InvoiceRun = <InvoiceRun>)");

$selInvoiceRuns->Execute();
$arrTotals = Array();
$strLastInvoiceRun = NULL;
$intNext = 1;
$arrInvoiceRuns = $selInvoiceRuns->FetchAll();
foreach ($arrInvoiceRuns as $arrInvoiceRun)
{
	$selBottom10->Execute($arrInvoiceRun);
	$selTop10->Execute($arrInvoiceRun);
	$selAvgAll->Execute($arrInvoiceRun);
	//$selBusLLRental->Execute($arrInvoiceRun);
	$arrBottom10	= $selBottom10->Fetch();
	$arrTop10		= $selTop10->Fetch();
	$arrAvgAll		= $selAvgAll->Fetch();
	//$arrBusLLRental	= $selBusLLRental->Fetch();
	$selProfit		= new StatementSelect("Invoice", "Total + Tax AS GrandTotal", "InvoiceRun = '{$arrInvoiceRun['InvoiceRun']}' AND Total != 0", NULL, "{$arrBottom10['Bottom10']}, {$arrTop10['Top10']}");
	$intCount		= $selProfit->Execute($arrInvoiceRun);
	
	$fltTotal = 0.0;
	while ($arrProfit = $selProfit->Fetch())
	{
		$fltTotal		+= $arrProfit['GrandTotal'];
	}
	
	if ($arrInvoiceRuns[$intNext])
	{
		$arrInvoiceRun['InvoiceRunOld'] = $arrInvoiceRun[$intNext]['InvoiceRun'];
		$arrTotals[$arrInvoiceRun['BillingDate']]['Lost'] = $selLostAccount->Execute($arrInvoiceRun);
	}
	
	$arrTotals[$arrInvoiceRun['BillingDate']]['InvoicesEx']	= $intCount;
	$arrTotals[$arrInvoiceRun['BillingDate']]['InvoicesIn']	= $arrAvgAll['Invoices'];
	//$arrTotals[$arrInvoiceRun['BillingDate']]['BusLLRent']	= $arrBusLLRental['Total'];
	$arrTotals[$arrInvoiceRun['BillingDate']]['Excluding']	= round($fltTotal / $intCount, 2);
	$arrTotals[$arrInvoiceRun['BillingDate']]['Including']	= round($arrAvgAll['GrandTotal'], 2);
	
	$strLastInvoiceRun = $arrInvoiceRun['InvoiceRun'];
	$intNext++;
	Debug($arrTotals[$arrInvoiceRun['BillingDate']]);
}

Debug($arrTotals);



?>