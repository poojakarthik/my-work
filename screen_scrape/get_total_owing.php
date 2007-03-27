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

// Init Statements
$arrData = Array();
$arrData['TotalOwing']	= NULL;
$selInvoiceOutput	= new StatementSelect("InvoiceOutput", "InvoiceRun, Account, Data");
$updTotalOwing		= new StatementUpdate("Invoice", "InvoiceRun = <InvoiceRun> AND Account = <Account>", $arrData);

echo "\n\n[ GRABBING TOTAL OWING AND INSERTING INTO INVOICE TABLE ]\n\n";

// Grab the InvoiceOutput
$intTotal = $selInvoiceOutput->Execute();
$intPassed = 0;
while ($arrInvoiceOutput = $selInvoiceOutput->Fetch())
{
	// Find the Total Owing
	$strTotalOwing = substr($arrInvoiceOutput['Data'], 106, 11);
	$fltTotalOwing = (float)substr($arrInvoiceOutput['Data'], 106, 11);
	
	echo " + Updating Account #{$arrInvoiceOutput['Account']} setting TotalOwing to '$strTotalOwing'...\t\t";
	
	// Update the Invoice
	$arrData['TotalOwing']	= $fltTotalOwing;
	$arrWhere = Array();
	$arrWhere['InvoiceRun']	= $arrInvoiceOutput['InvoiceRun'];
	$arrWhere['Account']	= $arrInvoiceOutput['Account'];
	if ($updTotalOwing->Execute($arrData, $arrWhere))
	{
		echo "[   OK   ]\n";
		$intPassed++;
	}
	else
	{
		echo "[ FAILED ]\n";
	}
}

$intFailed = $intTotal - $intPassed;
echo "\nCompleted! $intPassed passed, $intFailed failed.\n\n";

?>