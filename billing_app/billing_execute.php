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
$arrResponse = $appBilling->CalculateProfitData();

CliEcho(" + Calculating Debug Data...");
$selBillingDebug = new StatementSelect("InvoiceTemp", "DueOn, COUNT(Id) AS InvoiceCount, SUM(Total) + SUM(Tax) AS TotalInvoiced, SUM(TotalOwing) AS TotalOwing", "1", "DueOn", NULL, "DueOn");
$selBillingDebug->Execute();
$arrBillingDebug = $selBillingDebug->FetchAll();

$arrChargeCols = Array();
$arrChargeCols['LPCount']		= "COUNT(CASE WHEN ChargeType LIKE 'LP????' THEN Id ELSE NULL END)";
$arrChargeCols['LPTotal']		= "SUM(CASE WHEN ChargeType LIKE 'LP????' THEN (Amount + (Amount / 10)) ELSE NULL END)";
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
				"\t+ InvoiceRun\t\t: {$arrResponse['InvoiceRun']}\n" .
				"\t+ Invoice Count\t\t: {$arrResponse['InvoiceCount']}\n" .
				"\t+ Total Cost\t\t: \${$arrResponse['BillCost']}\n" .
				"\t+ Total Rated\t\t: \${$arrResponse['BillRated']}\n" .
				"\t+ Total Invoiced (ex Tax)\t: \${$arrResponse['BillInvoiced']}\n" .
				"\t+ Total Taxed\t\t: \${$arrResponse['BillTax']}\n\n" .
				str_repeat("=", 80) .
				"\n\nBilling Debug by Due Date\n\n" .
				"\tDue Date\tInvoice Count\tInvoice Total (inc Tax)\tTotal Owing\n";
				
foreach ($arrBillingDebug as $arrDebug)
{
	$strContent .= "\t{$arrDebug['DueOn']}\t{$arrDebug['InvoiceCount']}\t\t\${$arrDebug['TotaInvoiced']}\t\${$arrDebug['TotalOwing']}\n";	
}

$strContent	.=	"\n\n" .
				str_repeat("=", 80) .
				"\n\nCharge Debug Data (Totals are inc Tax)\n\n" .
				"\t+ Late Payment (LPmmyy) Count\t: {$arrChargeDebug['LPCount']};\tTotal: \${$arrChargeDebug['LPTotal']}\n" .
				"\t+ Non-DDR (AP250) Count\t\t: {$arrChargeDebug['APCount']};\tTotal: \${$arrChargeDebug['APTotal']}\n" .
				"\t+ LL S&E Creditts (SEC) Count\t: {$arrChargeDebug['SECCount']};\tTotal: \${$arrChargeDebug['SECTotal']}\n" .
				"\t+ Inbound Fee (INB15) Count\t: {$arrChargeDebug['INBCount']};\tTotal: \${$arrChargeDebug['INBTotal']}\n\n" .
				"\t+ Misc Credit Count\t\t: {$arrChargeDebug['OtherCRCount']};\tTotal: \${$arrChargeDebug['OtherCRTotal']}\n" .
				"\t+ Misc Debit Count\t\t: {$arrChargeDebug['OtherDRCount']};\tTotal: \${$arrChargeDebug['OtherDRTotal']}\n\n" .
				"\t+ Credit Count\t\t\t: {$arrChargeDebug['CRCount']};\tTotal: \${$arrChargeDebug['CRTotal']}\n" .
				"\t+ Debit Count\t\t\t: {$arrChargeDebug['DRCount']};\tTotal: \${$arrChargeDebug['DRTotal']}\n";

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
$strEmail = 'rich@voiptelsystems.com.au';
if (!$emlMail->send($strEmail, $strHeaders, $strBody))

$appBilling->FinaliseReport();

// finished
echo("\n\n-- End of Billing --\n");
echo "</pre>";
die();

?>
