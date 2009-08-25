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
// MAIL MERGE: WELCOME LETTER
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Mail Merge: Welcome Letter";
$arrDataReport['Summary']		= "Generates a data set for Mail Merging with Welcome Letters";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Account a
									JOIN Contact c ON (a.PrimaryContact = c.Id)
									JOIN CustomerGroup cg ON (cg.Id = a.CustomerGroup)";
$arrDataReport['SQLWhere']		= "	CAST(a.CreatedOn AS DATE) BETWEEN <StartDate> AND <EndDate>
									AND a.Archived IN (0, 5)
									AND a.CustomerGroup = <CustomerGroup>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Customer Group']		['Value']	= "cg.external_name";

$arrSQLSelect['Account']			['Value']	= "a.Id";
$arrSQLSelect['Account']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "a.BusinessName";

$arrSQLSelect['First Name']			['Value']	= "c.FirstName";

$arrSQLSelect['Last Name']			['Value']	= "c.LastName";

$arrSQLSelect['Address Line 1']		['Value']	= "a.Address1";

$arrSQLSelect['Address Line 2']		['Value']	= "a.Address2";

$arrSQLSelect['Suburb']				['Value']	= "a.Suburb";

$arrSQLSelect['Postcode']			['Value']	= "a.Postcode";

$arrSQLSelect['State']				['Value']	= "a.State";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrCustomerGroupQuery =	array
							(
								'Query'			=> "	SELECT		Id				AS `Value`,
																	external_name	AS `Label`
														FROM		CustomerGroup cg
														WHERE		1
														ORDER BY	Id ASC;",
								'ValueType'		=> "dataInteger"
							);



$arrSQLFields['CustomerGroup']	= Array(
											'Type'					=> "Query",
											'DBQuery'				=> $arrCustomerGroupQuery,
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Customer Group",
										);

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