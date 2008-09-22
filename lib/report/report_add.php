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


//---------------------------------------------------------------------------//
// CREDIT CARD PAYMENTS SUMMARY BY EMPLOYEE
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Credit Card Payments Summary by Employee";
$arrDataReport['Summary']		= "Shows the number of Credit Card Payments each Employee made in a given time period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "credit_card_payment_history JOIN Employee ON Employee.Id = credit_card_payment_history.employee_id";
$arrDataReport['SQLWhere']		= "CAST(payment_datetime AS DATE) BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "Employee.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Employee']				['Value']	= "CONCAT(Employee.LastName, ', ', Employee.FirstName)";

$arrSQLSelect['Payments Made']			['Value']	= "COUNT(credit_card_payment_history.id)";
$arrSQLSelect['Payments Made']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Payment Total']			['Value']	= "SUM(credit_card_payment_history.amount)";
$arrSQLSelect['Payment Total']			['Type']	= EXCEL_TYPE_CURRENCY;

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