<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// billing_reports
//----------------------------------------------------------------------------//
/**
 * billing_reports
 *
 * Application that handles billing-time reports
 *
 * Application that handles billing-time reports
 *
 * @file		definitions.php
 * @language	PHP
 * @package		framework
 * @author		Rich 'Waste01' Davis
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

// Load framework
require_once('../../flex.require.php');
$arrConfig = LoadApplication();

$GLOBALS['appBilling'] = new ApplicationBilling($arrConfig);

CliEcho(" + Calculating Profit Data...");
$arrProfitData['ThisMonth']	= $GLOBALS['appBilling']->CalculateProfitData();
$selProfitData = new StatementSelect("InvoiceRun", "*", "BillingDate < <BillingDate>", "BillingDate DESC", 1);
//$selProfitData->Execute(Array('BillingDate' => date("Y-m-d")));
//$arrProfitData['ThisMonth']	= $selProfitData->Fetch();
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
	
	// Make sure directory exists
	$strPath = FILES_BASE_PATH."reports/".date("Y/m/", strtotime("-1 month", time()));
	$strProgressivePath = '';
	foreach (explode('/', $strPath) as $strPart)
	{
		if ($strPart)
		{
			$strProgressivePath .= '/' . $strPart;
			//CliEcho("Trying to make '$strProgressivePath'... ", FALSE);
			if (!@mkdir($strProgressivePath))
			{
				//CliEcho("[ FAILED ]\n");
			}
			else
			{
				//CliEcho("[   OK   ]\n");
			}
		}
	}
	$strPath		= FILES_BASE_PATH."reports/".date("Y/m/", strtotime("-1 month", time()));
	@mkdir($strPath, 0777);
	$strFilename	= FILES_BASE_PATH."reports/".date("Y/m/")."Plan_Summary_with_Breakdown_($strServiceType).xls";
	
	$arrReports = Array();
	CliEcho("Invoice Summary...");
	$arrReports	= array_merge($arrReports, $bilManagementReports->CreateReport('InvoiceSummary'));
	CliEcho("Service Summary...");
	$arrReports	= array_merge($arrReports, $bilManagementReports->CreateReport('ServiceSummary'));
	CliEcho("Adjustment Summary...");
	$arrReports	= array_merge($arrReports, $bilManagementReports->CreateReport('AdjustmentSummary'));
	CliEcho("Recurring Adjustment Summary...");
	$arrReports	= array_merge($arrReports, $bilManagementReports->CreateReport('RecurringAdjustmentsSummary'));
	CliEcho("Adjustments By Employee Summary...");
	$arrReports	= array_merge($arrReports, $bilManagementReports->CreateReport('AdjustmentsByEmployeeSummary'));
	CliEcho("Customer Summary...");
	$arrReports	= array_merge($arrReports, $bilManagementReports->CreateReport('CustomerSummary'));
	CliEcho("CustomerGroup Summary...");
	$arrReports	= array_merge($arrReports, $bilManagementReports->CreateReport('CustomerGroup'));
	CliEcho("Plan Summary...");
	$arrReports	= array_merge($arrReports, $bilManagementReports->CreateReport('PlanSummary'));
	
	// Email Management Reports
	$strCustomerName	= $GLOBALS['**arrCustomerConfig']['Customer'];
	$strDir				= date("Y/m/", strtotime("-1 day", time()));
	$strMonth			= date("F", strtotime("-1 day", time()));
	
	$rcpRemoteCopyReports = new RemoteCopyFTP("192.168.2.224", "flame", "zeemu");
	if (is_string($mixResult = $rcpRemoteCopyReports->Connect()))
	{
		echo "$mixResult \n";
	}
	$rcpRemoteCopyReports->Copy($strPath, "/data/www/reports.yellowbilling.com.au/html/$strCustomerName/management/$strDir", RCOPY_BACKUP);
	$rcpRemoteCopyReports->Disconnect();
	
	$strURL = "reports.yellowbilling.com.au/$strCustomerName/$strDir/$strZipname";
	SendEmail('turdminator@hotmail.com, mark.s@yellowbilling.com.au', "$strMonth Management Reports", "$strMonth Management Reports are available at $strURL");
}

?>