<?php
//---------------------------------------------------------------------------//
// PAYMENT DOWNLOAD
//---------------------------------------------------------------------------//
$arrDataReport['Name']					= "Motorpass: Finance Report";
$arrDataReport['Summary']				= "Motorpass Finance Report, listing Motorpass Invoice information for a given Bill Date";
$arrDataReport['RenderMode']			= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']			= PERMISSION_OPERATOR;
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLTable']				= "	InvoiceRun ir
											JOIN Invoice i ON (ir.Id = i.invoice_run_id)
											JOIN Account a ON (a.Id = i.Account)
											JOIN CustomerGroup cg ON (cg.Id = a.CustomerGroup)
											JOIN account_history ah ON	(
																			i.Account = ah.account_id
																			AND change_timestamp < ir.billing_period_end_datetime
																			AND ah.id =	(
																							SELECT		id
																							FROM		account_history
																							WHERE		account_id = i.Account
																										AND change_timestamp < ir.billing_period_end_datetime
																							ORDER BY	change_timestamp DESC
																							LIMIT		1
																						)
																		)
											JOIN rebill r ON	(
																	i.Account = r.account_id
																	AND r.created_timestamp < ir.billing_period_end_datetime
																	AND r.rebill_type_id = (SELECT id FROM rebill_type WHERE system_name = 'MOTORPASS' LIMIT 1)
																	AND r.id =	(
																					SELECT		id
																					FROM		rebill
																					WHERE		account_id = i.Account
																								AND created_timestamp < ir.billing_period_end_datetime
																					ORDER BY	created_timestamp DESC
																					LIMIT		1
																				)
																)
											JOIN rebill_motorpass rm ON (rm.rebill_id = r.id)
											LEFT JOIN carrier_payment_type cpt ON	(
																						cpt.carrier_id = (SELECT Id FROM Carrier WHERE const_name = 'CARRIER_RETAIL_DECISIONS')
																						AND cpt.payment_type_id = (SELECT id FROM payment_type WHERE const_name = 'PAYMENT_TYPE_REBILL_PAYOUT')
																					)";
$arrDataReport['SQLWhere']				= "	ir.BillingDate = <billing_date>
											AND invoice_run_type_id = (SELECT id FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_LIVE')
											AND invoice_run_status_id = (SELECT id FROM invoice_run_status WHERE const_name = 'INVOICE_RUN_STATUS_COMMITTED')";
$arrDataReport['SQLGroupBy']			= "";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account #']					['Value']	= "a.Id";
$arrSQLSelect['Account #']					['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Invoice #']					['Value']	= "i.Id";
$arrSQLSelect['Invoice #']					['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Billed Amount']				['Value']	= "(i.Total + i.Tax)";
$arrSQLSelect['Billed Amount']				['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Customer Group']				['Value']	= "cg.external_name";

$arrSQLSelect['Motorpass Account #']		['Value']	= "rm.account_number";
$arrSQLSelect['Motorpass Account #']		['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['ReD Program Admin Fee']		['Value']	= "19.99";
$arrSQLSelect['ReD Program Admin Fee']		['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Payment Surcharge Amount']	['Value']	= "ROUND((i.Total + i.Tax) * cpt.surcharge_percent, 2)";
$arrSQLSelect['Payment Surcharge Amount']	['Type']	= EXCEL_TYPE_CURRENCY;

$arrDataReport['SQLSelect'] 				= serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['billing_date']	= 	array
									(
										'Type'					=> "Query",
										'DBQuery'				=>	array
																	(
																		'Query'			=> "SELECT		DISTINCT
																										BillingDate								AS `Value`,
																										DATE_FORMAT(BillingDate, '%e %M %Y')	AS `Label`
																							FROM		InvoiceRun
																							WHERE		invoice_run_type_id = (SELECT id FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_LIVE')
																										AND invoice_run_status_id = (SELECT id FROM invoice_run_status WHERE const_name = 'INVOICE_RUN_STATUS_COMMITTED')
																							ORDER BY	BillingDate DESC;",
																		'ValueType'		=> "dataString"
																	),
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Billing Date",
									);
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);

?>