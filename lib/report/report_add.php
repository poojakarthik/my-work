<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Add a new Data Report to viXen
//----------------------------------------------------------------------------//

// load application
require_once('../../flex.require.php');

//----------------------------------------------------------------------------//
// TODO: Specify the DataReport here!  See report_skeleton.php for tut
//----------------------------------------------------------------------------//

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


//---------------------------------------------------------------------------//
// ACCOUNT LIFETIME PROFIT SUMMARY
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Account Lifetime Profit Summary";
$arrDataReport['Summary']		= "Provides a Profit Summary for each Account since they were entered in Flex";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "				Account a
												JOIN Invoice i ON (a.Id = i.Account)
												JOIN InvoiceRun ir ON (i.invoice_run_id = ir.Id)
												JOIN
												(
													SELECT		stt.Account				AS account_id,
																stt.invoice_run_id		AS invoice_run_id,
																SUM(stt.Cost)			AS cdr_cost,
																SUM(stt.Charge)			AS cdr_rated
													FROM		InvoiceRun ir
																JOIN Invoice i ON (i.invoice_run_id = ir.Id)
																LEFT JOIN ServiceTypeTotal stt ON (stt.Account = i.Account AND stt.invoice_run_id = i.invoice_run_id)
													GROUP BY	account_id,
																invoice_run_id
												) ict ON (ict.invoice_run_id = ir.Id AND i.Account = ict.account_id)";
$arrDataReport['SQLWhere']		= "				ir.invoice_run_type_id IN (1, 4, 5)";
$arrDataReport['SQLGroupBy']	= "				a.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']							['Value']	= "a.Id";

$arrSQLSelect['Account Name']						['Value']	= "a.BusinessName";

$arrSQLSelect['Times Invoiced']						['Value']	= "COUNT(i.Id)";

$arrSQLSelect['Last Invoiced On']					['Value']	= "DATE_FORMAT(MAX(ir.BillingDate), '%d/%m/%Y')";

$arrSQLSelect['Total CDR Cost (ex GST)']			['Value']	= "SUM(ict.cdr_cost)";

$arrSQLSelect['Total CDR Rated Charge (ex GST)']	['Value']	= "SUM(ict.cdr_rated)";

$arrSQLSelect['Total Invoiced (ex GST)']			['Value']	= "SUM(i.Total)";

$arrSQLSelect['Total Taxed']						['Value']	= "SUM(i.Tax)";

$arrSQLSelect['Total Invoiced (inc GST)']			['Value']	= "SUM(i.Total + i.Tax)";

$arrSQLSelect['Profit Margin']						['Value']	= "IF(SUM(i.Total), (SUM(i.Total) - SUM(ict.cdr_cost)) / SUM(i.Total), 0)";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// Insert the Data Report
//----------------------------------------------------------------------------//

$insDataReport = new StatementInsert("DataReport");
if (!$insDataReport->Execute($arrDataReport))
{
	Debug($insDataReport->Error());
}
Debug("OK!");

// finished
echo("\n\n-- End of Report Generation --\n");

?>