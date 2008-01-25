<?php
// call application
require_once("../../flex.require.php");

// Get InvoiceRuns
$selInvoiceRuns		= new StatementSelect("Invoice", "InvoiceRun, CreatedOn AS BillingDate, SUM(Total) AS BillInvoiced, SUM(Tax) AS BillTax, COUNT(Id) AS InvoiceCount", "1", "CreatedOn", NULL, "InvoiceRun");
$selCDRTotals		= new StatementSelect("CDR", "SUM(Cost) AS BillCost, SUM(Charge) AS BillRated", "InvoiceRun = <InvoiceRun>");
$selChargeTotals	= new StatementSelect("Charge", "SUM(CASE WHEN Nature = 'CR' THEN (0 - Amount) ELSE Amount END) AS Total", "InvoiceRun = <InvoiceRun>");
$insInvoiceRun		= new StatementInsert("InvoiceRun");
$selInvoiceRuns->Execute();
while ($arrInvoiceRun = $selInvoiceRuns->Fetch())
{
	// Get additional Details
	$selCDRTotals->Execute($arrInvoiceRun);
	$arrInvoiceRun = array_merge($arrInvoiceRun, $selCDRTotals->Fetch());
	$selChargeTotals->Execute($arrInvoiceRun);
	$arrChargeTotals = $selChargeTotals->Fetch();
	$arrInvoiceRun['BillRated'] += $arrChargeTotals['Total'];
	
	// Handle Etech Invoices
	if (!$arrInvoiceRun['BillCost'])
	{
		$arrInvoiceRun['BillCost']		= 0;
		$arrInvoiceRun['BillRated']		= 0;
	}
	
	// Date hack for invoice run on the 30th
	$arrDate = explode('-', $arrInvoiceRun['BillDate']);
	if ((int)$arrDate[2] >= 28)
	{
		Debug($arrDate);
		$arrInvoiceRun['BillDate'] = date("Y-m-d", strtotime("+1 month", strtotime("{$arrDate[0]}-$arrDate[1]-01")));
	}
	
	$arrInvoiceRun['Id'] = $insInvoiceRun->Execute($arrInvoiceRun);
	Debug($arrInvoiceRun);
}


?>