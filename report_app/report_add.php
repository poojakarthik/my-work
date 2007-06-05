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
$arrDataReport['Name']			= "Profit Report for a Billing Period";
$arrDataReport['Summary']		= "Lists Profit Data for every Invoice generated in a specified Billing Period";
$arrDataReport['RenderMode']	= REPORT_RENDER_EMAIL;
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Invoice";
$arrDataReport['SQLWhere']		= "DueOn BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']	['Value']	= "Invoice.Account";
$arrSQLSelect['Account No.']	['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Customer Group']	['Value']	= "Account.CustomerGroup";

$arrSQLSelect['Customer Name']	['Value']	= "Account.BusinessName";

$strNLDTypes = "2, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 18, 19, 20, 27, 28, 33, 35, 36, 38";
$arrSQLSelect['Cost NLD']		['Value']	=	"SUM(CASE " .
												"WHEN ServiceTypeTotal.RecordType IN ($strNLDTypes) THEN ServiceTypeTotal.Cost " .
												"ELSE 0 " .
												"END)";
$arrSQLSelect['Cost NLD']		['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Cost NLD']		['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Charge NLD']		['Value']	=	"SUM(CASE " .
												"WHEN ServiceTypeTotal.RecordType IN ($strNLDTypes) THEN ServiceTypeTotal.Charge " .
												"ELSE 0 " .
												"END)";
$arrSQLSelect['Charge NLD']		['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Charge NLD']		['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Bill Cost']		['Value']	= "SUM(ServiceTypeTotal.Cost)";
$arrSQLSelect['Bill Cost']		['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Bill Cost']		['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Bill Charge']	['Value']	= "Invoice.Total + Invoice.Tax";
$arrSQLSelect['Bill Charge']	['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Bill Charge']	['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Margin']			['Value']	= "((Invoice.Total + Invoice.Tax) - SUM(ServiceTypeTotal.Cost)) / ABS(Invoice.Total + Invoice.Tax)";
$arrSQLSelect['Margin']			['Type']	= EXCEL_TYPE_PERCENTAGE;
$arrSQLSelect['Margin']			['Total']	= "(<Bill Charge> - <Bill Cost>) / ABS(<Bill Charge>)";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['InvoiceRun']	= Array(
										'Type'					=> "dataInvoiceRun",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "BillingPeriod",
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