<?php

//---------------------------------------------------------------------------//
// Aged Receivables Report
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Aged Receivables (30/60/90 Days) Report per Account";
$arrDataReport['Summary']		= "Shows how much each Account owes, grouped by how old the overdue amount is (1-30 days, 30-60 days, 60-90 days, 90+ days old).";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Invoice i
									JOIN Account a ON (i.Account = a.Id)
									JOIN CustomerGroup cg ON (cg.Id = a.CustomerGroup)
									JOIN Contact c ON a.PrimaryContact = c.Id
									LEFT JOIN credit_control_status ccs ON (ccs.id = a.credit_control_status)";
$arrDataReport['SQLWhere']		= "	(
										(<ShowArchived> = 1)
										OR (<ShowArchived> = 0 AND a.Archived != 1)
									)";
$arrDataReport['SQLGroupBy']	= "	i.Account
						
						HAVING		SUM(i.Balance) > 0";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']					['Value']	= "a.Id";
$arrSQLSelect['Account']					['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']				['Value']	= "a.BusinessName";

$arrSQLSelect['Customer Group']				['Value']	= "cg.internal_name";

$arrSQLSelect['Customer Name']				['Value']	= "CONCAT(c.FirstName, ' ', c.LastName)";

$arrSQLSelect['Address Line 1']				['Value']	= "a.Address1";

$arrSQLSelect['Address Line 2']				['Value']	= "a.Address2";

$arrSQLSelect['Suburb']						['Value']	= "a.Suburb";

$arrSQLSelect['Postcode']					['Value']	= "a.Postcode";

$arrSQLSelect['State']						['Value']	= "a.State";

$arrSQLSelect['Phone']						['Value']	= "c.Phone";
$arrSQLSelect['Phone']						['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Mobile']						['Value']	= "c.Mobile";
$arrSQLSelect['Mobile']						['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Email']						['Value']	= "c.Email";

$arrSQLSelect['Credit Control Status']		['Value']	= "ccs.name";

$arrSQLSelect['TIO Reference Number']		['Value']	= "a.tio_reference_number";

$arrSQLSelect['Outstanding Not Overdue']	['Value']	= "SUM(IF(CURDATE() <= i.DueOn AND i.Status NOT IN (106, 105, 100), i.Balance, 0))";
$arrSQLSelect['Outstanding Not Overdue']	['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Outstanding Not Overdue']	['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['1-29 Days Overdue']			['Value']	= "SUM(IF(CURDATE() BETWEEN ADDDATE(i.DueOn, INTERVAL 1 DAY) AND ADDDATE(i.DueOn, INTERVAL 29 DAY) AND i.Status NOT IN (106, 105, 100), i.Balance, 0))";
$arrSQLSelect['1-29 Days Overdue']			['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['1-29 Days Overdue']			['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['30-59 Days Overdue']			['Value']	= "SUM(IF(CURDATE() BETWEEN ADDDATE(i.DueOn, INTERVAL 30 DAY) AND ADDDATE(i.DueOn, INTERVAL 59 DAY) AND i.Status NOT IN (106, 105, 100), i.Balance, 0))";
$arrSQLSelect['30-59 Days Overdue']			['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['30-59 Days Overdue']			['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['60-89 Days Overdue']			['Value']	= "SUM(IF(CURDATE() BETWEEN ADDDATE(i.DueOn, INTERVAL 60 DAY) AND ADDDATE(i.DueOn, INTERVAL 89 DAY) AND i.Status NOT IN (106, 105, 100), i.Balance, 0))";
$arrSQLSelect['60-89 Days Overdue']			['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['60-89 Days Overdue']			['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['90+ Days Overdue']			['Value']	= "SUM(IF(CURDATE() >= ADDDATE(i.DueOn, INTERVAL 90 DAY) AND i.Status NOT IN (106, 105, 100), i.Balance, 0))";
$arrSQLSelect['90+ Days Overdue']			['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['90+ Days Overdue']			['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Total Overdue']				['Value']	= "SUM(IF(CURDATE() > i.DueOn AND i.Status NOT IN (106, 105, 100), i.Balance, 0))";
$arrSQLSelect['Total Overdue']				['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Total Overdue']				['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Total Outstanding']			['Value']	= "SUM(IF(i.Status NOT IN (106, 105, 100), i.Balance, 0))";
$arrSQLSelect['Total Outstanding']			['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Total Outstanding']			['Total']	= EXCEL_TOTAL_SUM;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

$arrSQLFields['ShowArchived']	= Array(
											'Type'					=> "dataBoolean",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "ShowArchivedAccounts",
										);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;

?>