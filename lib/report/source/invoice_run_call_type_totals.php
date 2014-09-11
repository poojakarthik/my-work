<?php
//---------------------------------------------------------------------------//
// Invoice Run: Call Type Totals
//---------------------------------------------------------------------------//

$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();

$arrDataReport['Name']			= "Invoice Run: Call Type Totals";
$arrDataReport['Summary']		= "Shows Call Type Total data for a given Invoice Run.  The Invoice Run must not have been archived.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	CDR cdr
									JOIN Carrier c ON (c.Id = cdr.Carrier)
									JOIN RecordType rt ON (cdr.RecordType = rt.Id)
									JOIN service_type st ON (cdr.ServiceType = st.id)
									JOIN Account a ON (cdr.Account = a.Id)
									LEFT JOIN Service s_destination ON (cdr.FNN != cdr.Destination AND s_destination.FNN = cdr.Destination AND s_destination.CreatedOn < cdr.StartDatetime AND (s_destination.ClosedOn IS NULL OR s_destination.ClosedOn > cdr.EndDatetime))
									LEFT JOIN Account a_destination ON (s_destination.Account = a_destination.Id)";
$arrDataReport['SQLWhere']		= "	cdr.invoice_run_id = <invoice_run_id>
									AND cdr.Status IN (198, 199)";
$arrDataReport['SQLGroupBy']	= "	rt.id,
									cdr.Carrier,
									`Call Nature`,
									cdr.Credit
						
						ORDER BY	st.name ASC,
									rt.Name ASC,
									c.Name ASC,
									`Call Nature` ASC,
									cdr.Credit ASC";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Service Type']						['Value']	= "st.id";

$arrSQLSelect['Service Type Description']			['Value']	= "st.description";

$arrSQLSelect['Record Type']						['Value']	= "rt.Id";

$arrSQLSelect['Record Type Description']			['Value']	= "rt.Name";

$arrSQLSelect['Carrier Id']							['Value']	= "c.Id";

$arrSQLSelect['Carrier Name']						['Value']	= "c.Name";

$arrSQLSelect['Call Nature']						['Value']	= "	CASE
																		WHEN cdr.Account = s_destination.Account THEN 'Intra-Account'
																		WHEN a.CustomerGroup = a_destination.CustomerGroup THEN 'Inter-Account'
																		ELSE 'Off-Network'
																	END";

$arrSQLSelect['Charge Nature']						['Value']	= "IF(cdr.Credit = 0, 'DR', 'CR')";

$arrSQLSelect['Total Units']						['Value']	= "SUM(cdr.Units)";
$arrSQLSelect['Total Units']						['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Units Type']							['Value']	= "	CASE
																		WHEN rt.DisplayType = 3 THEN 'KB(s)'
																		WHEN rt.DisplayType = 1 THEN 'Second(s)'
																		ELSE 'Unit(s)'
																	END";

$arrSQLSelect['Total Cost']							['Value']	= "SUM(cdr.Cost)";
$arrSQLSelect['Total Cost']							['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total Amount Charged to Customers']	['Value']	= "SUM(cdr.Charge)";
$arrSQLSelect['Total Amount Charged to Customers']	['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['CDR Count']							['Value']	= "COUNT(cdr.Id)";
$arrSQLSelect['CDR Count']							['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['FNN Count']							['Value']	= "COUNT(DISTINCT cdr.FNN)";
$arrSQLSelect['FNN Count']							['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Count']						['Value']	= "COUNT(DISTINCT cdr.Account)";
$arrSQLSelect['Account Count']						['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

$arrInvoiceRunQuery =	array
						(
							'Query'			=> "	SELECT		ir.Id																			AS `Value`,
																CONCAT(DATE_FORMAT(ir.BillingDate, '%e %M %Y'), ' (', cg.internal_name, ')')	AS `Label`
													FROM		InvoiceRun ir
																JOIN CustomerGroup cg ON (ir.customer_group_id = cg.Id)
																JOIN invoice_run_type irt ON (irt.id = ir.invoice_run_type_id)
													WHERE		irt.const_name = 'INVOICE_RUN_TYPE_LIVE'
																AND (SELECT Id FROM CDR WHERE invoice_run_id = ir.Id LIMIT 1) IS NOT NULL
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


?>