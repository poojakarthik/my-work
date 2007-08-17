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
require_once('../framework/require.php');

//----------------------------------------------------------------------------//
// TODO: Specify the DataReport here!  See report_skeleton.php for tut
//----------------------------------------------------------------------------//

$arrDataReport	= Array();
$arrDocReqs		= Array();
$arrSQLSelect	= Array();
$arrSQLFields	= Array();

// General Data
$arrDataReport['Name']			= "Lost Services in a Date Period";
$arrDataReport['Summary']		= "Lists all of the services which were lost in the specified period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= 	"(" .
										"(" .
											"(" .
												"(" .
													"Service LEFT JOIN ProvisioningResponse PR ON Service.Account = PR.Account" .
												") " .
												"LEFT JOIN Account ON Account.Id = Service.Account" .
											") " .
											"LEFT JOIN Contact ON Account.PrimaryContact = Contact.Id" .
										") " .
										"LEFT JOIN ServiceRatePlan SRP ON PR.Service = SRP.Service" .
									") " .
									"LEFT JOIN RatePlan ON SRP.RatePlan = RatePlan.Id";

$arrDataReport['SQLWhere']		= "PR.ImportedOn BETWEEN <StartDate> AND ADDDATE(<EndDate>, INTERVAL 1 DAY) \n" .
								"AND PR.Status = ".RESPONSE_STATUS_IMPORTED." AND PR.Request IS NULL \n" .
								"AND PR.Type IN (".REQUEST_LOSS_PRESELECT.", ".REQUEST_LOSS_FULL.") \n" .
								"AND SRP.Id = (SELECT ServiceRatePlan.Id FROM ServiceRatePlan WHERE ServiceRatePlan.Service = PR.Service AND PR.ImportedOn BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime ORDER BY ServiceRatePlan.CreatedOn DESC LIMIT 1) \n" . 
								"AND ((PR.Type = ".REQUEST_LOSS_PRESELECT." AND PR.Id = (SELECT Id FROM ProvisioningResponse PR2 WHERE PR2.Service = PR.Service AND PR2.Type IN (".REQUEST_LOSS_PRESELECT.", ".REQUEST_PRESELECTION.") ORDER BY EffectiveDate DESC LIMIT 1)) " .
								"OR (PR.Type = ".REQUEST_LOSS_FULL." AND PR.Id = (SELECT Id FROM ProvisioningResponse PR2 WHERE PR2.Service = PR.Service AND PR2.Type IN (".REQUEST_LOSS_FULL.", ".REQUEST_FULL_SERVICE.") ORDER BY EffectiveDate DESC LIMIT 1)))";
$arrDataReport['SQLGroupBy']	= "PR.Service ORDER BY Account.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Account.Id";

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Primary Contact']		['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Contact Phone']			['Value']	= "Contact.Phone";
$arrSQLSelect['Contact Phone']			['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service FNN']		['Value']	= "PR.FNN";
$arrSQLSelect['Lost Service FNN']		['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service Plan']		['Value']	= "RatePlan.Name";

$arrSQLSelect['Full Service Loss Date']	['Value']	= "MAX(CASE WHEN PR.Type = ".REQUEST_LOSS_FULL." THEN DATE_FORMAT(PR.EffectiveDate, '%d/%m/%Y') ELSE NULL END)";

$arrSQLSelect['Preselection Loss Date']	['Value']	= "MAX(CASE WHEN PR.Type = ".REQUEST_LOSS_PRESELECT." THEN DATE_FORMAT(PR.EffectiveDate, '%d/%m/%Y') ELSE NULL END)";

$arrSQLSelect['Loss Details']			['Value']	= "PR.Description";

$arrSQLSelect['Active Services']		['Value']	= "COUNT(DISTINCT CASE WHEN Service.ClosedOn IS NULL THEN Service.Id ELSE NULL END)";
$arrSQLSelect['Active Services']		['Type']	= EXCEL_TYPE_INTEGER;

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