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
// New Accounts Created after a Date
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "New Accounts Created after a Date";
$arrDataReport['Summary']		= "Displays a list of Accounts which were created on or after a specified date.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Account JOIN Contact ON Account.PrimaryContact = Contact.Id";
$arrDataReport['SQLWhere']		= "Account.CreatedOn >= <CreatedOn> AND Account.Archived = 0";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Account";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account #']				['Value']	= "Account.Id";
$arrSQLSelect['Account #']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Business Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Primary Contact']		['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Contact Phone']			['Value']	= "CASE WHEN Contact.Phone = '' THEN Contact.Mobile ELSE Contact.Phone END";
$arrSQLSelect['Contact Phone']			['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Created On']				['Value']	= "DATE_FORMAT(Account.CreatedOn, '%d/%m/%Y')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrSQLFields['CreatedOn']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "Account",
										'Documentation-Field'	=> "CreatedOn",
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