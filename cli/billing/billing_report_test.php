<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// billing_report_test
//----------------------------------------------------------------------------//
/**
 * billing_report_test
 *
 * Runs a single Management Report
 *
 * Runs a single Management Report
 *
 * @file		billing_report_test.php
 * @language	PHP
 * @package		billing
 * @author		Rich 'Waste01' Davis
 * @version		8.07
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
// Load framework
require_once('../../flex.require.php');
$arrConfig = LoadApplication();

$GLOBALS['appBilling']	= new ApplicationBilling($arrConfig);
$selProfitData			= new StatementSelect("InvoiceRun", "*", "BillingDate < <BillingDate>", "BillingDate DESC", 1);
$selInvoiceRun			= new StatementSelect("InvoiceRun", "*", "InvoiceRun = <InvoiceRun>");

// Parse Command Line Arguments
$strReportType	= $argv[1];
$strInvoiceRun	= $argv[2];

CliEcho(" + Calculating Profit Data...");
if ($strInvoiceRun)
{
	// Committed Run
	if ($selInvoiceRun->Execute(Array('InvoiceRun' => $strInvoiceRun)))
	{
		$arrProfitData['ThisMonth']	= $selInvoiceRun->Fetch();
	}
	else
	{
		CliEcho("'{$strInvoiceRun}' is not a valid InvoiceRun!");
		exit(1);
	}
}
else
{
	// Temporary Run
	$arrProfitData['ThisMonth']	= $GLOBALS['appBilling']->CalculateProfitData();
}

$selProfitData->Execute($arrProfitData['ThisMonth']);
$arrProfitData['LastMonth']	= $selProfitData->Fetch();
$selProfitData->Execute($arrProfitData['LastMonth']);
$arrMonthBeforeLast	= $selProfitData->Fetch();	
$arrProfitData['ThisMonth']['LastInvoiceRun']	= $arrProfitData['LastMonth']['InvoiceRun'];
$arrProfitData['ThisMonth']['LastBillingDate']	= $arrProfitData['LastMonth']['BillingDate'];
$arrProfitData['LastMonth']['LastInvoiceRun']	= $arrMonthBeforeLast['InvoiceRun'];
$arrProfitData['LastMonth']['LastBillingDate']	= $arrMonthBeforeLast['BillingDate'];

if ($arrProfitData['ThisMonth'] && $arrProfitData['LastMonth'])
{
	//Generate Management Reports
	$bilManagementReports = new BillingModuleReports($arrProfitData);
	
	$arrReports = Array();
	CliEcho("Generating Report...");
	$bilManagementReports->CreateReport($strReportType);
}

?>