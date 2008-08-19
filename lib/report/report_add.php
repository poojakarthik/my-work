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
 // LOST MOBILE SERVICES
 //---------------------------------------------------------------------------//
 
$arrDataReport['Name']			= "Lost Services (Mobiles) in a Date Period";
$arrDataReport['Summary']		= "Lists all of the Mobile Services which were lost in the specified period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= 	"(" . 
										"(" .
											"(" .
												"Service LEFT JOIN Account ON Account.Id = Service.Account" .
											") " .
											"LEFT JOIN Contact ON Account.PrimaryContact = Contact.Id" .
										") " .
										"LEFT JOIN ServiceRatePlan SRP ON Service.Id = SRP.Service" .
									") " .
									"LEFT JOIN RatePlan ON SRP.RatePlan = RatePlan.Id" .

$arrDataReport['SQLWhere']		= "Service.Status = 400 AND ADDDATE(CAST(Service.LatestCDR AS DATE), INTERVAL 30 DAY) BETWEEN <StartDate> AND <EndDate> AND Service.ServiceType = 101";
$arrDataReport['SQLGroupBy']	= "Service.Id ORDER BY Account.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Account.Id";

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Primary Contact']		['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Contact Phone']			['Value']	= "Contact.Phone";
$arrSQLSelect['Contact Phone']			['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service FNN']		['Value']	= "Service.FNN";
$arrSQLSelect['Lost Service FNN']		['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service Plan']		['Value']	= "RatePlan.Name";

$arrSQLSelect['Last Tolled Date']		['Value']	= "Service.LatestCDR";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
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