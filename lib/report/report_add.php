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
// Contract Cancellation Fees
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Contract Cancellation Fees in a Time Period";
$arrDataReport['Summary']		= "Displays a list of Contract Cancellation Fees for a specified period.";
$arrDataReport['FileName']		= "Contract Cancellation Fees between <StartDate> AND <EndDate>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Charge";
$arrDataReport['SQLWhere']		= "ChargeType IN ('DSLCAN', 'CONT', 'EARL') AND CreatedOn BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "InvoiceRun";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']				['Value']	= "Account";
$arrSQLSelect['Account']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Date Created']			['Value']	= "DATE_FORMAT(CreatedOn, '%d/%m/%Y')";

$arrSQLSelect['Description']			['Value']	= "Description";

$arrSQLSelect['Date Charged']			['Value']	= "DATE_FORMAT(ChargedOn, '%d/%m/%Y')";

$arrSQLSelect['Amount']					['Value']	= "CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END";
$arrSQLSelect['Amount']					['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Notes']					['Value']	= "Notes";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
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