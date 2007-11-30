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
require_once('../framework/require.php');
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
	$strPath = "/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/", strtotime("-1 month", time()));
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
	@mkdir("/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/", strtotime("-1 month", time())), 0777);
	$strFilename	= "/home/vixen/{$GLOBALS['**arrCustomerConfig']['Customer']}/reports/".date("Y/m/")."Plan_Summary_with_Breakdown_($strServiceType).xls";
	
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
	CliEcho("Plan Summary...");
	$arrReports	= array_merge($arrReports, $bilManagementReports->CreateReport('PlanSummary'));
	
	// Email Management Reports	
	$strContent		= "Please find attached the Management Reports for ".date("Y-m-d H:i:s")."\n\nYellow Billing Services";
	$arrHeaders = Array	(
							'From'		=> "billing@telcoblue.com.au",
							'Subject'	=> "Management Reports for ".date("Y-m-d H:i:s")
						);
	$mimMime = new Mail_mime("\n");
	$mimMime->setTXTBody($strContent);
	
	foreach ($arrReports as $strPath)
	{
		//Debug($strPath);
		Debug($mimMime->addAttachment($strPath, 'application/x-msexcel'));
	}
	
	$strBody = $mimMime->get();
	$strHeaders = $mimMime->headers($arrHeaders);
	$emlMail =& Mail::factory('mail');
	
	// Send the email
	$strEmail = 'rich@voiptelsystems.com.au, ' .
				'jared@telcoblue.com.au, ' .
				'turdminator@hotmail.com, ' .
				'aphplix@gmail.com, ' .
				'dan@fhcc.com.au, ' .
				'paula@telcoblue.com.au, ' .
				'kaywan@telcoblue.com.au, ' .
				'julie@telcoblue.com.au, ' .
				'mark@yellowbilling.com.au';
	//$strEmail	= 'rich@voiptelsystems.com.au, turdminator@hotmail.com';

	/*if (!$emlMail->send($strEmail, $strHeaders, $strBody))
	{
		CliEcho("Email Failed!");
	}*/
}

?>