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
$arrDataReport['Name']			= "Percentage Collected for a Given Invoice Run";
$arrDataReport['Summary']		= "Shows the Percentage collected to date for a specified Invoice Run";
$arrDataReport['FileName']		= "Percentage Collected from <InvoiceRun::Label> Invoice Run as of <DATETIME()>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Invoice";
$arrDataReport['SQLWhere']		= "InvoiceRun = <InvoiceRun>";
$arrDataReport['SQLGroupBy']	= "InvoiceRun";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Invoice Grand Total']	['Value']	= "SUM(CASE WHEN Total+Tax > 0 THEN Total+Tax ELSE 0 END)";
$arrSQLSelect['Invoice Grand Total']	['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total Collected']		['Value']	= "SUM(CASE WHEN Total+Tax > 0 THEN Total+Tax ELSE 0 END) - SUM(CASE WHEN Total+Tax > 0 THEN Balance ELSE 0 END)";
$arrSQLSelect['Total Collected']		['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total Outstanding']		['Value']	= "SUM(CASE WHEN Total+Tax >= 0 THEN Balance ELSE 0 END)";
$arrSQLSelect['Total Outstanding']		['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Percent Collected']		['Value']	= "1 - (SUM(CASE WHEN Total+Tax > 0 THEN Balance ELSE 0 END) / SUM(CASE WHEN Total+Tax > 0 THEN Total+Tax ELSE 0 END))";
$arrSQLSelect['Percent Collected']		['Type']	= EXCEL_TYPE_PERCENTAGE;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrColumns = Array();
$arrColumns['Label']	= "DATE_FORMAT(BillingDate, '%d %M %Y')";
$arrColumns['Value']	= "InvoiceRun";

$arrSelect = Array();
$arrSelect['Table']		= "InvoiceRun";
$arrSelect['Columns']	= $arrColumns;
$arrSelect['Where']		= "BillingDate > '2007-03-01'";
$arrSelect['OrderBy']	= "BillingDate DESC";
$arrSelect['Limit']		= NULL;
$arrSelect['GroupBy']	= NULL;
$arrSelect['ValueType']	= "dataString";

$arrSQLFields['InvoiceRun']	= Array(
										'Type'					=> "StatementSelect",
										'DBSelect'				=> $arrSelect,
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "BillingDate",
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