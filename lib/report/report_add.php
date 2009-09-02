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
// VIP Accounts List
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "VIP Accounts List";
$arrDataReport['Summary']		= "Lists all Accounts who have the VIP flag selected";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Account a
									JOIN account_status a_s ON (a_s.id = a.Archived)
									JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
									JOIN Contact c ON (a.PrimaryContact = c.Id)";
$arrDataReport['SQLWhere']		= "	a.vip = 1";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Customer Group']		['Value']	= "cg.internal_name";

$arrSQLSelect['Account']			['Value']	= "a.Id";
$arrSQLSelect['Account']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "a.BusinessName";

$arrSQLSelect['Contact Name']		['Value']	= "CONCAT(c.FirstName, ' ', c.LastName)";

$arrSQLSelect['Contact Phone']		['Value']	= "IF(CAST(c.Phone AS UNSIGNED) > 0, c.Phone, c.Mobile)";
$arrSQLSelect['Contact Phone']		['Type']	= EXCEL_TYPE_FNN;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
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