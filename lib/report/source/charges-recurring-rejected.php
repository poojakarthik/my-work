<?php

//---------------------------------------------------------------------------//
// RECURRING CHARGE REQUESTS THAT HAVE BEEN REJECTED
//---------------------------------------------------------------------------//

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


$arrDataReport['Name']			= "Recurring Charge Requests That Have Been Rejected";
$arrDataReport['Summary']		= "Lists all Recurring Charge Requests That Have Been Rejected";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 64;											// Credit Management
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	RecurringCharge
									INNER JOIN Account ON RecurringCharge.Account = Account.Id
									LEFT JOIN Service ON RecurringCharge.Service = Service.Id
									INNER JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id
									INNER JOIN Employee AS Creator ON RecurringCharge.CreatedBy = Creator.Id
									LEFT JOIN Employee AS Rejector ON RecurringCharge.ApprovedBy = Rejector.Id";
$arrDataReport['SQLWhere']		= "	(<ChargeType> = 'ANY' OR CONCAT(RecurringCharge.Nature, ': ', RecurringCharge.ChargeType, ' - ', RecurringCharge.Description) = <ChargeType>)
									AND (<CustomerGroupId> = 0 OR Account.CustomerGroup = <CustomerGroupId>)
									AND (RecurringCharge.CreatedOn BETWEEN <EarliestDate> AND <LatestDate>)
									AND RecurringCharge.recurring_charge_status_id = (SELECT id FROM recurring_charge_status WHERE system_name = 'DECLINED')
									ORDER BY RecurringCharge.CreatedOn ASC, RecurringCharge.Id ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Recurring Charge Id']			['Value']	= "RecurringCharge.Id";
$arrSQLSelect['Customer Group']					['Value']	= "CustomerGroup.internal_name";
$arrSQLSelect['Account']						['Value']	= "RecurringCharge.Account";
$arrSQLSelect['Service FNN']					['Value']	= "Service.FNN";
$arrSQLSelect['Nature']							['Value']	= "RecurringCharge.Nature";
$arrSQLSelect['Type']							['Value']	= "RecurringCharge.ChargeType";
$arrSQLSelect['Description']					['Value']	= "RecurringCharge.Description";
$arrSQLSelect['Minimum Charge ($ Ex GST)']		['Value']	= "RecurringCharge.MinCharge";
$arrSQLSelect['Recurring Charge ($ Ex GST)']	['Value']	= "RecurringCharge.RecursionCharge";
$arrSQLSelect['Created On']						['Value']	= "DATE_FORMAT(RecurringCharge.CreatedOn, '%d/%m/%Y')";
$arrSQLSelect['Created By']						['Value']	= "CONCAT(Creator.FirstName, ' ', Creator.LastName)";
$arrSQLSelect['Proposed Start Date']			['Value']	= "DATE_FORMAT(RecurringCharge.StartedOn, '%d/%m/%Y')";
$arrSQLSelect['Rejected By']					['Value']	= "IF((Rejector.Id IS NOT NULL), CONCAT(Rejector.FirstName, ' ', Rejector.LastName), 'UNKNOWN')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrChargeTypeQuery = array('Query'			=> "SELECT DISTINCT CONCAT(Nature, ': ', ChargeType, ' - ', Description) AS Value,
													IF(Archived = 0, CONCAT('[ACTIVE] ', Nature, ': ', ChargeType, ' - ', Description), CONCAT('[ARCHVIED] ', Nature, ': ', ChargeType, ' - ', Description)) AS Label
												FROM RecurringChargeType
												ORDER BY Label ASC;",
							'ValueType'		=> "dataString",
							'IgnoreField'	=> array(	'Value'	=> 'ANY',
														'Label'	=> 'Any')
						);


$arrCustomerGroupQuery = array(	'Query'			=> "SELECT Id AS Value, internal_name AS Label
													FROM CustomerGroup
													ORDER BY internal_name ASC;",
								'ValueType'		=> "dataInteger",
								'IgnoreField'	=> array(	'Value'	=> 0,
															'Label'	=> 'Any')
								);



$arrSQLFields['ChargeType']			= Array('Type'					=> "Query",
											'DBQuery'				=> $arrChargeTypeQuery,
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Charge Type",
											);

$arrSQLFields['CustomerGroupId']	= Array('Type'					=> "Query",
											'DBQuery'				=> $arrCustomerGroupQuery,
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Customer Group",
											);

$arrSQLFields['EarliestDate']		= Array(
											'Type'					=> "dataDate",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Earliest Request Date",
											);
$arrSQLFields['LatestDate']			= Array(
											'Type'					=> "dataDate",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Latest Request Date",
											);


$arrDataReport['SQLFields'] = serialize($arrSQLFields);

// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;

?>