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
 // NON-TOLLING SERVICES
 //---------------------------------------------------------------------------//
 
$arrDataReport['Name']			= "Non-Tolling Services in a Date Period for a Service Type";
$arrDataReport['Summary']		= "Lists all of the Services which have not tolled since the specified Last Tolling Date for the specified Service Type";
$arrDataReport['FileName']		= "<ServiceType::Label> Service that have not tolled since <LatestCDR>";
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
									"LEFT JOIN RatePlan ON SRP.RatePlan = RatePlan.Id";

$arrDataReport['SQLWhere']		= "Service.ServiceType = <ServiceType> AND Service.Status = 400 AND Service.LatestCDR <= CONCAT(<LatestCDR>, ' 23:59:59')";
$arrDataReport['SQLGroupBy']	= "Service.Id ORDER BY Account.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Service";
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
$arrColumns['Label']	= "description";
$arrColumns['Value']	= "id";

$arrSelect = Array();
$arrSelect['Table']		= "service_type";
$arrSelect['Columns']	= $arrColumns;
$arrSelect['Where']		= "const_name NOT IN ('SERVICE_TYPE_ADSL', 'SERVICE_TYPE_DIALUP')";
$arrSelect['OrderBy']	= "description ASC";
$arrSelect['Limit']		= NULL;
$arrSelect['GroupBy']	= NULL;
$arrSelect['ValueType']	= "dataInteger";

$arrSQLFields['ServiceType']	= Array(
										'Type'					=> "StatementSelect",
										'DBSelect'				=> $arrSelect,
										'Documentation-Entity'	=> "Service",
										'Documentation-Field'	=> "ServiceType",
									);
									
$arrSQLFields['LatestCDR']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "Service",
										'Documentation-Field'	=> "LatestCDR",
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