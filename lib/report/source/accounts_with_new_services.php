<?php
//---------------------------------------------------------------------------//
// CUSTOMERS: ACCOUNTS WITH NEW SERVICES
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Customers: Accounts with new Services";
$arrDataReport['Summary']		= "Lists Accounts which have Active Services created within a specified date period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Account a
									JOIN account_status a_s ON (a.Archived = a_s.id)
									JOIN CustomerGroup cg ON (cg.Id = a.CustomerGroup)
									JOIN Service s ON (s.Account = a.Id)
									JOIN service_status ss ON (s.Status = ss.id)";
$arrDataReport['SQLWhere']		= "	a_s.const_name = 'ACCOUNT_STATUS_ACTIVE'
									AND ss.const_name = 'SERVICE_ACTIVE'
									AND CAST(s.CreatedOn AS DATE) BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "	a.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Customer Group']		['Value']	= "cg.external_name";

$arrSQLSelect['Account']			['Value']	= "a.Id";
$arrSQLSelect['Account']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "a.BusinessName";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Start Date",
									);

$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "End Date",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

?>