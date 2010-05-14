<?php
//---------------------------------------------------------------------------//
// Motorpass Exception Reporting - Invalid Payment Type Applied
//---------------------------------------------------------------------------//
$arrDataReport['Name']					= "Exception Reporting: Invalid Payment Type Applied";
$arrDataReport['Summary']				= "Shows account & payment details where the payment method is Rebill where a non-Rebill payment has been applied.";
$arrDataReport['RenderMode']			= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']			= PERMISSION_OPERATOR;
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLWhere'] 				= "	pm.const_name = 'PAYMENT_METHOD_REBILL'
											AND pt.const_name <> 'PAYMENT_TYPE_REBILL_PAYOUT'
											AND	((p.File IS NULL AND (p.PaidOn BETWEEN <StartDate> AND <EndDate>)) OR (p.File IS NOT NULL AND (fi.ImportedOn BETWEEN <StartDate> AND <EndDate>)))
											ORDER BY a.Id";
$arrDataReport['SQLGroupBy'] 			= "";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

//-------------------------------------//
// START SQL TABLE
//-------------------------------------//
$arrDataReport['SQLTable']	= "	Account a
								JOIN		billing_type bt
												ON bt.id = a.BillingType
								JOIN		payment_method pm
												ON pm.id = bt.payment_method_id
								JOIN		Payment p
												ON p.Account = a.Id
								JOIN		payment_type pt
												ON pt.id = p.PaymentType
								LEFT JOIN	FileImport fi
												ON fi.Id = p.File";
//-------------------------------------//
// END SQL TABLE
//-------------------------------------//

//-------------------------------------//
// START SQL SELECT
//-------------------------------------//
$arrSQLSelect['Account Number']['Value']		= "a.Id";
$arrSQLSelect['Business Name']['Value']			= "a.BusinessName";
$arrSQLSelect['Payment Method']['Value']		= "pm.name";
$arrSQLSelect['Payment Type Applied']['Value']	= "pt.Name";
$arrSQLSelect['Payment Date Applied']['Value']	= "p.PaidOn";
$arrSQLSelect['Payment Date Applied']['Type']	= EXCEL_TYPE_DATE;
$arrSQLSelect['Payment Amount']['Value']		= "p.Amount";
$arrSQLSelect['Payment Amount']['Type']			= EXCEL_TYPE_CURRENCY;
//-------------------------------------//
// END SQL SELECT
//-------------------------------------//

$arrDataReport['SQLSelect']	= serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['StartDate']	= 	array(
									'Type'					=> "dataDate",
									'Documentation-Entity'	=> "DataReport",
									'Documentation-Field'	=> "Start Date",
								);
$arrSQLFields['EndDate']	= 	array(
									'Type'					=> "dataDate",
									'Documentation-Entity'	=> "DataReport",
									'Documentation-Field'	=> "End Date",
								);
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);

?>