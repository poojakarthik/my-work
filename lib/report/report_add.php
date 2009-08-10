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


$arrDataReports = array();

//---------------------------------------------------------------------------//
// SERVICES OF A SERVICE TYPE ON A STATUS ON A GIVEN DATE
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Services on a Service Status";
$arrDataReport['Summary']		= "Shows a list of Services for a given Service Type, Service Status, and Date combination";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Service s
									JOIN Account a ON (s.Account = a.Id)
									JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
									JOIN Contact c ON (a.PrimaryContact = c.Id)
									JOIN ServiceRatePlan srp ON (s.Id = srp.Service)";
$arrDataReport['SQLWhere']		= "	<EffectiveDate> BETWEEN CAST(srp.StartDatetime AS DATE) AND CAST(srp.EndDatetime AS DATE)
									AND srp.Id =	(
														SELECT		Id
														FROM		ServiceRatePlan
														WHERE		Service = srp.Service
																	AND <EffectiveDate> BETWEEN CAST(StartDatetime AS DATE) AND CAST(EndDatetime AS DATE)
														ORDER BY	CreatedOn DESC
														LIMIT		1
													)
									AND s.ServiceType = <ServiceType>
									AND s.Status = <ServiceStatus>
									AND IF(s.ClosedOn IS NULL, <EffectiveDate> > s.CreatedOn, <EffectiveDate> BETWEEN s.CreatedOn AND s.ClosedOn)";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['FNN']				['Value']	= "s.FNN";
$arrSQLSelect['FNN']				['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Account #']			['Value']	= "a.Id";
$arrSQLSelect['Account #']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "a.BusinessName";

$arrSQLSelect['Customer Group']		['Value']	= "cg.external_name";

$arrSQLSelect['Contact']			['Value']	= "CONCAT(c.FirstName, ' ', c.LastName)";

$arrSQLSelect['Contact Phone']		['Value']	= "IF(c.Phone != '', c.Phone, c.Mobile)";
$arrSQLSelect['Contact Phone']		['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Rate Plan']			['Value']	= "rp.Name";

$arrSQLSelect['Has Tolled']			['Value']	= "IF(s.EarliestCDR, 'Yes', 'No')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrServiceTypeQuery =	array
						(
							'Query'			=> "	SELECT id AS Value, name AS Label
													FROM service_type
													ORDER BY name ASC;",
							'ValueType'		=> "dataInteger"
						);

$arrServiceStatusQuery =	array
							(
								'Query'			=> "	SELECT id AS Value, name AS Label
														FROM service_status
														ORDER BY name ASC;",
								'ValueType'		=> "dataInteger"
							);



$arrSQLFields['ServiceType']	= Array(
											'Type'					=> "Query",
											'DBQuery'				=> $arrServiceTypeQuery,
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "ServiceType",
										);


$arrSQLFields['ServiceStatus']	= Array(
											'Type'					=> "Query",
											'DBQuery'				=> $arrServiceStatusQuery,
											'Documentation-Entity'	=> "Service",
											'Documentation-Field'	=> "Status",
										);

$arrSQLFields['EffectiveDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;



//----------------------------------------------------------------------------//
// Insert the Data Report(s)
//----------------------------------------------------------------------------//

TransactionStart();

$insDataReport = new StatementInsert("DataReport");
foreach ($arrDataReports as $arrDataReport)
{
	if (!$insDataReport->Execute($arrDataReport))
	{
		TransactionRollback();
		Debug($insDataReport->Error());
	}
}

TransactionCommit();
Debug("OK!");


// finished
echo("\n\n-- End of Report Generation --\n");

?>