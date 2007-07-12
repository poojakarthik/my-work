<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Execute a billing run
//----------------------------------------------------------------------------//
 
 echo "<pre>";

// load application
require_once('application_loader.php');

// Application entry point - create an instance of the application object
$appBilling = new ApplicationBilling($arrConfig);

// execute bill
//$bolResponse = $appBilling->Execute();

CliEcho("\n[ Generating Debug Data ]\n");

// Email Invoice Total Data
CliEcho(" + Calculating Profit Data...");
//if ($arrResponse = $appBilling->CalculateProfitData())
if ($arrResponse = $appBilling->CalculateProfitData("468865b79ffa3", TRUE))
{
	
	CliEcho(" + Calculating Debug Data...");
	//$selBillingDebug = new StatementSelect("InvoiceTemp", "DueOn, COUNT(Id) AS InvoiceCount, SUM(Total) + SUM(Tax) AS TotalInvoiced, SUM(TotalOwing) AS TotalOwing", "InvoiceRun = <InvoiceRun> AND (Total != 0 OR Invoice.TotalOwing != 0)", "DueOn", NULL, "DueOn");
	$selBillingDebug = new StatementSelect("Invoice", "DueOn, COUNT(Id) AS InvoiceCount, (SUM(Total) + SUM(Tax)) AS TotalInvoiced, SUM(TotalOwing) AS TotalOwing", "InvoiceRun = <InvoiceRun> AND (Total != 0 OR Invoice.TotalOwing != 0)", "DueOn", NULL, "DueOn");
	$selBillingDebug->Execute($arrResponse);
	$arrBillingDebug = $selBillingDebug->FetchAll();
	
	$arrChargeCols = Array();
	$arrChargeCols['LPCount']		= "COUNT(CASE WHEN ChargeType LIKE 'LP____' THEN Id ELSE NULL END)";
	$arrChargeCols['LPTotal']		= "SUM(CASE WHEN ChargeType LIKE 'LP____' THEN (Amount + (Amount / 10)) ELSE NULL END)";
	$arrChargeCols['APCount']		= "COUNT(CASE WHEN ChargeType LIKE 'AP250' THEN Id ELSE NULL END)";
	$arrChargeCols['APTotal']		= "SUM(CASE WHEN ChargeType LIKE 'AP250' THEN (Amount + (Amount / 10)) ELSE NULL END)";
	$arrChargeCols['SECCount']		= "COUNT(CASE WHEN ChargeType LIKE 'SEC' THEN Id ELSE NULL END)";
	$arrChargeCols['SECTotal']		= "SUM(CASE WHEN ChargeType LIKE 'SEC' THEN (Amount + (Amount / 10)) ELSE NULL END)";
	$arrChargeCols['INBCount']		= "COUNT(CASE WHEN ChargeType LIKE 'INB15' THEN Id ELSE NULL END)";
	$arrChargeCols['INBTotal']		= "SUM(CASE WHEN ChargeType LIKE 'INB15' THEN (Amount + (Amount / 10)) ELSE NULL END)";
	$arrChargeCols['OtherCRCount']	= "COUNT(CASE WHEN ChargeType NOT IN ('AP250', 'SEC', 'INB15') AND ChargeType NOT LIKE 'LP????' AND Nature = 'CR' THEN Id ELSE NULL END)";
	$arrChargeCols['OtherCRTotal']	= "SUM(CASE WHEN ChargeType NOT IN ('AP250', 'SEC', 'INB15') AND ChargeType NOT LIKE 'LP????' AND Nature = 'CR' THEN (Amount + (Amount / 10)) ELSE NULL END)";
	$arrChargeCols['OtherDRCount']	= "COUNT(CASE WHEN ChargeType NOT IN ('AP250', 'SEC', 'INB15') AND ChargeType NOT LIKE 'LP????' AND Nature = 'DR' THEN Id ELSE NULL END)";
	$arrChargeCols['OtherDRTotal']	= "SUM(CASE WHEN ChargeType NOT IN ('AP250', 'SEC', 'INB15') AND ChargeType NOT LIKE 'LP????' AND Nature = 'DR' THEN (Amount + (Amount / 10)) ELSE NULL END)";
	$arrChargeCols['CRCount']		= "COUNT(CASE WHEN Nature = 'CR' THEN Id ELSE NULL END)";
	$arrChargeCols['CRTotal']		= "SUM(CASE WHEN Nature = 'CR' THEN (Amount + (Amount / 10)) ELSE NULL END)";
	$arrChargeCols['DRCount']		= "COUNT(CASE WHEN Nature = 'DR' THEN Id ELSE NULL END)";
	$arrChargeCols['DRTotal']		= "SUM(CASE WHEN Nature = 'DR' THEN (Amount + (Amount / 10)) ELSE NULL END)";
	$selChargeDebug = new StatementSelect("Charge", $arrChargeCols, "InvoiceRun = <InvoiceRun>");
	$selChargeDebug->Execute($arrResponse);
	$arrChargeDebug = $selChargeDebug->Fetch();
	
	CliEcho(" + Emailing Debug Data...");
	
	$strContent	=	"Invoice Total Data for {$arrResponse['BillingDate']} Invoice Run\n\n" .
					"\t+ InvoiceRun\t\t\t: {$arrResponse['InvoiceRun']}\n" .
					"\t+ Invoice Count\t\t\t: {$arrResponse['InvoiceCount']}\n" .
					"\t+ Total Cost\t\t\t: \$".sprintf("%01.2f", $arrResponse['BillCost'])."\n" .
					"\t+ Total Rated\t\t\t: \$".sprintf("%01.2f", $arrResponse['BillRated'])."\n" .
					"\t+ Total Invoiced (ex Tax)\t: \$".sprintf("%01.2f", $arrResponse['BillInvoiced'])."\n" .
					"\t+ Total Taxed\t\t\t: \$".sprintf("%01.2f", $arrResponse['BillTax'])."\n" .
					"\t+ Gross Profit (ex Tax)\t\t: \$".sprintf("%01.2f", $arrResponse['GrossProfit'])."\n" .
					"\t+ Profit Margin\t\t\t: {$arrResponse['ProfitMargin']}\n\n" .
					str_repeat("=", 80) .
					"\n\nBilling Data by Due Date\n\n" .
					"\t  Due Date\tInvoice Count\tInvoice Total (inc Tax)\t\tTotal Owing\n";
					
	foreach ($arrBillingDebug as $arrDebug)
	{
		$strContent .= "\t+ {$arrDebug['DueOn']}\t{$arrDebug['InvoiceCount']}\t\t\$".sprintf("%01.2f", $arrDebug['TotalInvoiced'])."\t\t\t\$".sprintf("%01.2f", $arrDebug['TotalOwing'])."\n";	
	}
	
	$strContent	.=	"\n\n" .
					str_repeat("=", 80) .
					"\n\nCharge Data (Totals are inc Tax)\n\n" .
					"\t+ Late Payment (LPmmyy)\tCount: {$arrChargeDebug['LPCount']};\tTotal: \$".sprintf("%01.2f", $arrChargeDebug['LPTotal'])."\n" .
					"\t+ Non-DDR (AP250)\tCount: {$arrChargeDebug['APCount']};\tTotal: \$".sprintf("%01.2f", $arrChargeDebug['APTotal'])."\n" .
					"\t+ LL S&E Creditts (SEC)\tCount: {$arrChargeDebug['SECCount']};\tTotal: \$".sprintf("%01.2f", $arrChargeDebug['SECTotal'])."\n" .
					"\t+ Inbound Fee (INB15)\tCount: {$arrChargeDebug['INBCount']};\tTotal: \$".sprintf("%01.2f", $arrChargeDebug['INBTotal'])."\n\n" .
					"\t+ Misc Credit\t\tCount: {$arrChargeDebug['OtherCRCount']};\tTotal: \$".sprintf("%01.2f", $arrChargeDebug['OtherCRTotal'])."\n" .
					"\t+ Misc Debit\t\tCount: {$arrChargeDebug['OtherDRCount']};\tTotal: \$".sprintf("%01.2f", $arrChargeDebug['OtherDRTotal'])."\n\n" .
					"\t+ Total Credit\t\tCount: {$arrChargeDebug['CRCount']};\tTotal: \$".sprintf("%01.2f", $arrChargeDebug['CRTotal'])."\n" .
					"\t+ Total Debit\t\tCount: {$arrChargeDebug['DRCount']};\tTotal: \$".sprintf("%01.2f", $arrChargeDebug['DRTotal'])."\n";
	
	$arrHeaders = Array	(
							'From'		=> "billing@telcoblue.com.au",
							'Subject'	=> "Billing::Execute Debugging Data for ".date("Y-m-d")
						);
	
	$mimMime = new Mail_mime("\n");
	$mimMime->setTXTBody($strContent);
	$strBody = $mimMime->get();
	$strHeaders = $mimMime->headers($arrHeaders);
	$emlMail =& Mail::factory('mail');
	
	// Send the email
	$strEmail = 'rich@voiptelsystems.com.au, jared@telcoblue.com.au';
	if (!$emlMail->send($strEmail, $strHeaders, $strBody))
	{
		CliEcho("Email Failed!");
	}
}
else
{
	CliEcho("No data in InvoiceTemp table!!");
}

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();

?>
