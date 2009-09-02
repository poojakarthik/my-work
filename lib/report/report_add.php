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
// VIP Revenue Report
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "VIP Revenue Report";
$arrDataReport['Summary']		= "Lists all Accounts who have the VIP flag selected and their Invoice value for a given Invoice Run";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Account a
									JOIN account_status a_s ON (a_s.id = a.Archived)
									JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
									JOIN Contact c ON (a.PrimaryContact = c.Id)
									JOIN Invoice i ON (a.Id = i.Account)";
$arrDataReport['SQLWhere']		= "	a.vip = 1
									AND a_s.const_name IN ('ACCOUNT_STATUS_ACTIVE', 'ACCOUNT_STATUS_SUSPENDED', 'ACCOUNT_STATUS_DEBT_COLLECTION')
									AND i.invoice_run_id = <invoice_run_id>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Customer Group']				['Value']	= "cg.internal_name";

$arrSQLSelect['Account']					['Value']	= "a.Id";
$arrSQLSelect['Account']					['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']				['Value']	= "a.BusinessName";

$arrSQLSelect['Contact Name']				['Value']	= "CONCAT(c.FirstName, ' ', c.LastName)";

$arrSQLSelect['Contact Phone']				['Value']	= "IF(CAST(c.Phone AS UNSIGNED) > 0, c.Phone, c.Mobile)";
$arrSQLSelect['Contact Phone']				['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Invoice Total (inc GST)']	['Value']	= "a.Id";
$arrSQLSelect['Invoice Total (inc GST)']	['Type']	= EXCEL_TYPE_CURRENCY;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

$arrInvoiceRunQuery =	array
						(
							'Query'			=> "	SELECT		ir.Id																			AS `Value`,
																CONCAT(DATE_FORMAT(ir.BillingDate, '%e %M %Y'), ' (', cg.internal_name, ')')	AS `Label`
													FROM		InvoiceRun ir
																JOIN CustomerGroup cg ON (ir.customer_group_id = cg.Id)
																JOIN invoice_run_type irt ON (irt.id = ir.invoice_run_type_id)
													WHERE		irt.const_name = 'INVOICE_RUN_TYPE_LIVE'
													ORDER BY	ir.BillingDate DESC, cg.Id DESC;",
							'ValueType'		=> "dataInteger"
						);



$arrSQLFields['invoice_run_id']	= Array(
											'Type'					=> "Query",
											'DBQuery'				=> $arrInvoiceRunQuery,
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Invoice Run",
										);

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