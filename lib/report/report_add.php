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
// ACCOUNTS CREATED IN A DATE PERIOD
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Accounts Created in a Date Period";
$arrDataReport['Summary']		= "Show a list of Accounts which were created in the specified Date Period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Account JOIN CustomerGroup ON CustomerGroup.Id = Account.CustomerGroup JOIN Contact ON Contact.Id = Account.PrimaryContact";
$arrDataReport['SQLWhere']		= "Account.CreatedOn BETWEEN <StartDate> AND <EndDate> AND Account.Archived = 0";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account #']			['Value']	= "Account.Id";
$arrSQLSelect['Account #']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "Account.BusinessName";

$arrSQLSelect['Customer Group']		['Value']	= "CustomerGroup.external_name";

$arrSQLSelect['Contact']			['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Contact Phone']		['Value']	= "CASE WHEN Contact.Phone != '' THEN Contact.Phone ELSE Contact.Mobile END";
$arrSQLSelect['Contact Phone']		['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Date Created']		['Value']	= "Account.CreatedOn";

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