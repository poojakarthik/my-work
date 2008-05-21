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

// General Data
$arrDataReport['Name']			= "Duplicate Unbilled CDR Files in a given Period";
$arrDataReport['Summary']		= "Displays a list of CDR Files which have duplicate unbilled CDRs in them.";
$arrDataReport['FileName']		= "Duplicate Unbilled CDR Files as of <DATETIME()>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "CDR JOIN FileImport ON CDR.File = FileImport.Id";
$arrDataReport['SQLWhere']		= "Status = ".CDR_DUPLICATE;
$arrDataReport['SQLGroupBy']	= "InvoiceRun";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['File Name']				['Value']	= "FileExport.FileName";

$arrSQLSelect['Duplicate CDRs']			['Value']	= "COUNT(CDR.Id)";
$arrSQLSelect['Duplicate CDRs']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Total CDR Cost']			['Value']	= "SUM(CDR.Cost)";
$arrSQLSelect['Total CDR Cost']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Import Date']			['Value']	= "DATE_FORMAT(FileExport.ImportedOn, '%d/%m/%Y %H:%i:%s')";

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