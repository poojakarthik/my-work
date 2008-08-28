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
 // CALL TYPE STATISTICS FOR A CARRIER
 //---------------------------------------------------------------------------//
 
$arrDataReport['Name']			= "Call Type Statistics for a Carrier";
$arrDataReport['Summary']		= "Lists Call Type summaries for each Carrier";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "((CDR JOIN RecordType ON CDR.RecordType = RecordType.Id) JOIN Carrier ON Carrier.Id = CDR.Carrier) JOIN service_type ON RecordType.ServiceType = service_type.id";
$arrDataReport['SQLWhere']		= "CDR.Status = IN (150, 198) AND Credit = 0";
$arrDataReport['SQLGroupBy']	= "Carrier.Id, CDR.RecordType \n ORDER BY Carrier.Name ASC, service_type.description, RecordType.Description";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Carrier']				['Value']	= "Carrier.Name";

$arrSQLSelect['Service Type']			['Value']	= "service_type.description";

$arrSQLSelect['Call Type']				['Value']	= "RecordType.Description";

$arrSQLSelect['Unique FNNs']			['Value']	= "COUNT(DISTINCT CDR.FNN)";
$arrSQLSelect['Unique FNNs']			['Value']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Total Calls']			['Value']	= "COUNT(CDR.Id)";
$arrSQLSelect['Total Calls']			['Value']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Total Units']			['Value']	= "SUM(CDR.Units)";
$arrSQLSelect['Total Units']			['Value']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Total Cost']				['Value']	= "SUM(CDR.Cost)";
$arrSQLSelect['Total Cost']				['Value']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total Rated']			['Value']	= "SUM(CDR.Charge)";
$arrSQLSelect['Total Rated']			['Value']	= EXCEL_TYPE_CURRENCY;

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