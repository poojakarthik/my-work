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
// Service Line Status Report
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "New Direct Debits in a Period";
$arrDataReport['Summary']		= "Displays a List of all new Direct Debit entries in Flex and the Employee who added them in a specified period.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(Account JOIN CreditCard DD USING (AccountGroup)) LEFT JOIN Employee ON Employee.Id = DD.employee_id";
$arrDataReport['SQLWhere']		= "Account.Archived != 1 AND DD.Archived = 0 
									AND CAST(DD.created_on AS DATE) BETWEEN <StartDate> AND <EndDate>
									GROUP BY DD.employee_id, Account.AccountGroup
									
									UNION
									
									SELECT CONCAT(Employee.LastName, ', ', Employee.FirstName) AS Employee, Account.Id AS `Account #`, Account.BusinessName AS `Business Name`, DATE_FORMAT(DD.created_on, '%Y-%m-%d') AS `Created On`
									FROM (Account JOIN DirectDebit DD USING (AccountGroup)) LEFT JOIN Employee ON Employee.Id = DD.employee_id
									WHERE Account.Archived != 1 AND DD.Archived = 0 
									AND CAST(DD.created_on AS DATE) BETWEEN <StartDate> AND <EndDate>
									GROUP BY DD.employee_id, Account.AccountGroup
									
									ORDER BY ISNULL(Employee) ASC, Employee ASC, `Created On` ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Account";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Employee']				['Value']	= "CONCAT(Employee.LastName, ', ', Employee.FirstName)";

$arrSQLSelect['Account #']				['Value']	= "Account.Id";
$arrSQLSelect['Account #']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Business Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Created On']				['Value']	= "DATE_FORMAT(DD.created_on, '%Y-%m-%d')";

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