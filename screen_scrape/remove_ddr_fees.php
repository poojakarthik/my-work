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



// Search for Feb Invoices with only NDDR fees
$arrColumns = Array();
$arrColumns['Status']	= INVOICE_SETTLED;
$arrColumns['Total']	= 0.0;
$arrColumns['Tax']		= 0.0;
$arrColumns['Balance']	= 0.0;
$selFebNDDRInvoices = new StatementSelect("Invoice", "Id, Account", "Total = 2.5 AND Balance = 2.75 AND InvoiceRun = '45f4cb0c0a135'");
$ubiFebNDDRInvoice	= new StatementUpdateById("Invoice", $arrColumns);
$qryRemoveCharge	= new Query();

// foreach invoice
$intCount = $selFebNDDRInvoices->Execute();
while ($arrInvoice = $selFebNDDRInvoices->Fetch())
{
	echo " + Fixing Invoice #{$arrInvoice['Id']} for Account {$arrInvoice['Account']}...\t\t";
	
	// Remove the charge
	if (!$qryRemoveCharge->Execute("DELETE FROM Charge WHERE InvoiceRun = '45f4cb0c0a135' AND ChargeType = 'NDDR' AND Account = {$arrInvoice['Account']}"))
	{
		echo "[ FAILED ]\n\t- Reason: Unable to remove NDDR Charge\n\n";
		die;
	}
	
	// Update the Invoice
	$arrColumns['Id'] = $arrInvoice['Id'];
	if (!$ubiFebNDDRInvoice->Execute($arrColumns))
	{
		echo "[ FAILED ]\n\t- Reason: Unable to update Invoice\n\n";
		die;
	}
	
	echo "[   OK   ]\n";
}

echo "\nCompleted! $intCount accounts updated.\n\n";
?>