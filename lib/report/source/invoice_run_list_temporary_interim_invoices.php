<?php
//---------------------------------------------------------------------------//
// Invoice Run: List Temporary Interim Invoices
//---------------------------------------------------------------------------//

$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();

$arrDataReport['Name']			= "Invoice Run: List Temporary Interim Invoices";
$arrDataReport['Summary']		= "Shows a list of Interim Invoice Runs that are yet to be committed.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Invoice i
									JOIN InvoiceRun ir ON (ir.Id = i.invoice_run_id)
									JOIN invoice_run_type irt ON (ir.invoice_run_type_id = irt.id AND irt.const_name IN ('INVOICE_RUN_TYPE_INTERIM'))
									JOIN invoice_run_status irs ON (ir.invoice_run_status_id = irs.id AND irs.const_name IN ('INVOICE_RUN_STATUS_GENERATING', 'INVOICE_RUN_STATUS_TEMPORARY', 'INVOICE_RUN_STATUS_REVOKING', 'INVOICE_RUN_STATUS_REVOKED', 'INVOICE_RUN_STATUS_COMMITTING'))
									JOIN Account a ON (a.Id = i.Account)";
$arrDataReport['SQLWhere']		= "	1
									
						ORDER BY	ir.BillingDate ASC,
									ir.Id ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']							['Value']	= "a.Id";

$arrSQLSelect['Account Name']						['Value']	= "a.BusinessName";

$arrSQLSelect['Billing Date']						['Value']	= "ir.BillingDate";

$arrSQLSelect['Invoice Run Status']					['Value']	= "irs.name";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

?>