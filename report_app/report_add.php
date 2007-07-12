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
require_once('require.php');

//----------------------------------------------------------------------------//
// TODO: Specify the DataReport here!  See report_skeleton.php for tut
//----------------------------------------------------------------------------//

$arrDataReport	= Array();
$arrDocReqs		= Array();
$arrSQLSelect	= Array();
$arrSQLFields	= Array();


// General Data
$arrDataReport['Name']			= "Currently Barred Services";
$arrDataReport['Summary']		= "Lists all of the services which are currently barred";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(Request JOIN Service ON Service.Id = Request.Service) JOIN Account ON Account.Id = Service.Account";
$arrDataReport['SQLWhere']		= "Request.Status = 301 AND RequestType IN (902, 908) AND Request.Id = (SELECT R2.Id FROM Request R2 WHERE R2.RequestType IN (902, 903, 908, 909) AND R2.Service = Request.Service ORDER BY DATE_FORMAT(R2.RequestDatetime, '%Y-%m-%d') DESC, R2.RequestType DESC LIMIT 1) ORDER BY 'Barring Request Date' DESC, 'Customer Name', 'Soft/Hard Barred'";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Service.Account";

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Service FNN']			['Value']	= "Service.FNN";

$arrSQLSelect['Barring Request Date']	['Value']	= "DATE_FORMAT(RequestDatetime, '%Y-%m-%d')";

/*$arrSQLSelect['Carrier']	['Value']				=	"CASE" .
														" WHEN Request.Carrier = 1 THEN 'Unitel'" .
														" WHEN Request.Carrier = 2 THEN 'Optus'" .
														" WHEN Request.Carrier = 3 THEN 'AAPT'" .
														" WHEN Request.Carrier = 4 THEN 'iSeek' " .
														"END";*/

$arrSQLSelect['Soft/Hard Barred']		['Value']	=	"CASE" .
														" WHEN Request.RequestType = 902 THEN 'Soft Bar' " .
														" WHEN Request.RequestType = 908 THEN 'Hard Bar' " .
														"END";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
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