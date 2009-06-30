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


//---------------------------------------------------------------------------//
// ACCOUNT ADDRESS CHANGES SUMMARY
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Ticketing Audit Report";
$arrDataReport['Summary']		= "Generates a list of Open and Pending Tickets for auditing purposes";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	FROM		ticketing_ticket tt
												JOIN ticketing_category tc ON (tt.category_id = tc.id)
												JOIN ticketing_status ts ON (tt.status_id = ts.id)
												JOIN ticketing_status_type tst ON (ts.status_type_id = tst.id)
												JOIN ticketing_priority tp ON (tt.priority_id = tp.id)
												LEFT JOIN ticketing_user tu ON (tt.owner_id = tu.id)
												LEFT JOIN Employee e ON (tu.employee_id = e.Id)";
$arrDataReport['SQLWhere']		= "	WHERE		tst.const_name IN ('TICKETING_STATUS_TYPE_PENDING', 'TICKETING_STATUS_TYPE_OPEN')
									ORDER BY	tt.Id ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Ticket ID']						['Value']	= "tt.id";

$arrSQLSelect['Subject']						['Value']	= "tt.subject";

$arrSQLSelect['Last Actioned']					['Value']	= "DATE_FORMAT(tt.modified_datetime, '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Received']						['Value']	= "DATE_FORMAT(tt.creation_datetime, '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Owner']							['Value']	= "CONCAT(e.FirstName, ' ', e.LastName)";

$arrSQLSelect['Category']						['Value']	= "tc.name";

$arrSQLSelect['Status']							['Value']	= "ts.name";

$arrSQLSelect['Priority']						['Value']	= "tp.name";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
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