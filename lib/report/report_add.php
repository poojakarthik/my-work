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

$arrDataReport	= Array();
$arrDocReq		= Array();
$arrSQLSelect	= Array();
$arrSQLFields	= Array();

//----------------------------------------------------------------------------//
// Direct Debits by Employee Summary
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Direct Debits by Employee Summary";
$arrDataReport['Summary']		= "Displays a list of Employees, and how many Direct Debit Accounts they've set up in a specified period.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(Employee LEFT JOIN CreditCard ON (Employee.Id = CreditCard.employee_id AND CreditCard.Archived = 0)) LEFT JOIN DirectDebit ON (Employee.Id = DirectDebit.employee_id AND DirectDebit.Archived = 0)";
$arrDataReport['SQLWhere']		= "(DirectDebit.Id IS NULL OR CAST(DirectDebit.created_on AS DATE) BETWEEN <StartDate> AND <EndDate>) AND (CreditCard.Id IS NULL OR CAST(CreditCard.created_on AS DATE) BETWEEN <StartDate> AND <EndDate>)";
$arrDataReport['SQLGroupBy']	= "Employee.Id\n HAVING `Credit Card` > 0 OR `Bank Transfer` > 0\n ORDER BY Employee ASC";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Account";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Employee']				['Value']	= "CONCAT(Employee.LastName, ', ', Employee.FirstName)";

$arrSQLSelect['Credit Card']			['Value']	= "COUNT(DISTINCT CreditCard.Id)";
$arrSQLSelect['Credit Card']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Bank Transfer']			['Value']	= "COUNT(DISTINCT DirectDebit.Id)";
$arrSQLSelect['Bank Transfer']			['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
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