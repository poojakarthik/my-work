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


$arrDataReports = array();

//---------------------------------------------------------------------------//
// Accounts Consistently Invoicing over $1000 (Last 3 Invoices)
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Accounts Consistently Invoicing over \$1000 (Last 3 Invoices)";
$arrDataReport['Summary']		= "Lists all Accounts who have invoiced over \$1000 over the last 3 Invoices";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Account a
									JOIN account_status a_s ON (a_s.id = a.Archived)
									JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
									JOIN Contact c ON (a.PrimaryContact = c.Id)
									JOIN Invoice i_current ON (a.Id = i_current.Account)
									JOIN Invoice i_previous ON (a.Id = i_previous.Account)
									JOIN Invoice i_threevious ON (a.Id = i_threevious.Account)";
$arrDataReport['SQLWhere']		= "	i_current.Id =	(
														SELECT		Invoice.Id
														FROM		Invoice
																	JOIN InvoiceRun ON (Invoice.invoice_run_id = InvoiceRun.Id)
														WHERE		invoice_run_type_id IN (1)
																	AND Invoice.Account = a.Id
														ORDER BY	Id DESC
														LIMIT		1 OFFSET 0
													)
									AND i_previous.Id =	(
															SELECT		Invoice.Id
															FROM		Invoice
																		JOIN InvoiceRun ON (Invoice.invoice_run_id = InvoiceRun.Id)
															WHERE		invoice_run_type_id IN (1)
																		AND Invoice.Account = a.Id
															ORDER BY	Id DESC
															LIMIT		1 OFFSET 1
														)
									AND i_threevious.Id =	(
																SELECT		Invoice.Id
																FROM		Invoice
																			JOIN InvoiceRun ON (Invoice.invoice_run_id = InvoiceRun.Id)
																WHERE		invoice_run_type_id IN (1)
																			AND Invoice.Account = a.Id
																ORDER BY	Id DESC
																LIMIT		1 OFFSET 2
															)
									AND a_s.const_name NOT IN ('ACCOUNT_CLOSED', 'ACCOUNT_ARCHIVED')
									AND a.vip = 0
									AND (i_current.Total + i_current.Tax) > 1000
									AND (i_previous.Total + i_previous.Tax) > 1000
									AND (i_threevious.Total + i_threevious.Tax) > 1000";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Customer Group']		['Value']	= "cg.internal_name";

$arrSQLSelect['Account']			['Value']	= "a.Id";
$arrSQLSelect['Account']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "a.BusinessName";

$arrSQLSelect['Contact Name']		['Value']	= "CONCAT(c.FirstName, ' ', c.LastName)";

$arrSQLSelect['Contact Phone']		['Value']	= "IF(CAST(c.Phone AS UNSIGNED) > 0, c.Phone, c.Mobile)";
$arrSQLSelect['Contact Phone']		['Type']	= EXCEL_TYPE_FNN;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;



//----------------------------------------------------------------------------//
// Insert the Data Report(s)
//----------------------------------------------------------------------------//

TransactionStart();

$insDataReport = new StatementInsert("DataReport");
foreach ($arrDataReports as $arrDataReport)
{
	if (!$insDataReport->Execute($arrDataReport))
	{
		TransactionRollback();
		Debug($insDataReport->Error());
	}
}

TransactionCommit();
Debug("OK!");


// finished
echo("\n\n-- End of Report Generation --\n");

?>