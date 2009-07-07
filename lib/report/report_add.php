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
// ADJUSTMENT REQUESTS AWAITING APPROVAL
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Adjustment Requests Awaiting Approval";
$arrDataReport['Summary']		= "Lists all Adjustment Requests Currently Awaiting Approval";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 64;											// Credit Management
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Charge
									INNER JOIN Account ON Charge.Account = Account.Id
									LEFT JOIN Service ON Charge.Service = Service.Id
									INNER JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id
									INNER JOIN Employee AS Creator ON Charge.CreatedBy = Creator.Id";
$arrDataReport['SQLWhere']		= "	(<ChargeType> = 'ANY' OR CONCAT(Charge.Nature, ': ', Charge.ChargeType, ' - ', Charge.Description) = <ChargeType>)
									AND (<CustomerGroupId> = 0 OR Account.CustomerGroup = <CustomerGroupId>)
									AND (Charge.CreatedOn BETWEEN <EarliestDate> AND <LatestDate>)
									AND Charge.Status = 100
									ORDER BY Charge.CreatedOn ASC, Charge.Id ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Adjustment Id']				['Value']	= "Charge.Id";
$arrSQLSelect['Customer Group']				['Value']	= "CustomerGroup.internal_name";
$arrSQLSelect['Account']					['Value']	= "Charge.Account";
$arrSQLSelect['Service FNN']				['Value']	= "Service.FNN";
$arrSQLSelect['Nature']						['Value']	= "Charge.Nature";
$arrSQLSelect['Type']						['Value']	= "Charge.ChargeType";
$arrSQLSelect['Description']				['Value']	= "Charge.Description";
$arrSQLSelect['Amount ($ Ex GST)']			['Value']	= "Charge.Amount";
$arrSQLSelect['Created On']					['Value']	= "DATE_FORMAT(Charge.CreatedOn, '%d/%m/%Y')";
$arrSQLSelect['Created By']					['Value']	= "CONCAT(Creator.FirstName, ' ', Creator.LastName)";
$arrSQLSelect['Charged On']					['Value']	= "DATE_FORMAT(Charge.ChargedOn, '%d/%m/%Y')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrChargeTypeQuery = array('Query'			=> "SELECT DISTINCT Value, Label
												FROM (
													SELECT CONCAT(Nature, ': ', ChargeType, ' - ', Description) AS Value, 
														IF(Archived = 0, CONCAT('[ACTIVE] ', Nature, ': ', ChargeType, ' - ', Description), CONCAT('[ARCHVIED] ', Nature, ': ', ChargeType, ' - ', Description)) AS Label
													FROM ChargeType
													UNION
													SELECT CONCAT(Nature, ': ', ChargeType, ' - ', Description) AS Value, 
														IF(Archived = 0, CONCAT('[ACTIVE] ', Nature, ': ', ChargeType, ' - ', Description), CONCAT('[ARCHVIED] ', Nature, ': ', ChargeType, ' - ', Description)) AS Label
													FROM RecurringChargeType
													WHERE Nature = 'DR'
												) AS AdjustmentType
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
											'Documentation-Field'	=> "Adjustment Type",
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


//---------------------------------------------------------------------------//
// ADJUSTMENT REQUESTS THAT HAVE BEEN APPROVED (by anyone other than the system user)
//---------------------------------------------------------------------------//

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


$arrDataReport['Name']			= "Adjustment Requests That Have Been Approved";
$arrDataReport['Summary']		= "Lists all Adjustment Requests That Have Been Approved";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 64;											// Credit Management
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Charge
									INNER JOIN Account ON Charge.Account = Account.Id
									LEFT JOIN Service ON Charge.Service = Service.Id
									INNER JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id
									INNER JOIN Employee AS Creator ON Charge.CreatedBy = Creator.Id
									INNER JOIN Employee AS Approver ON Charge.ApprovedBy = Approver.Id";
$arrDataReport['SQLWhere']		= "	(<ChargeType> = 'ANY' OR CONCAT(Charge.Nature, ': ', Charge.ChargeType, ' - ', Charge.Description) = <ChargeType>)
									AND (<CustomerGroupId> = 0 OR Account.CustomerGroup = <CustomerGroupId>)
									AND (Charge.CreatedOn BETWEEN <EarliestDate> AND <LatestDate>)
									AND Charge.ApprovedBy > 0
									AND Charge.Status != 104
									AND (LinkType IS NULL OR LinkType != 501)
									ORDER BY Charge.CreatedOn ASC, Charge.Id ASC;";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Adjustment Id']				['Value']	= "Charge.Id";
$arrSQLSelect['Customer Group']				['Value']	= "CustomerGroup.internal_name";
$arrSQLSelect['Account']					['Value']	= "Charge.Account";
$arrSQLSelect['Service FNN']				['Value']	= "Service.FNN";
$arrSQLSelect['Nature']						['Value']	= "Charge.Nature";
$arrSQLSelect['Type']						['Value']	= "Charge.ChargeType";
$arrSQLSelect['Description']				['Value']	= "Charge.Description";
$arrSQLSelect['Amount ($ Ex GST)']			['Value']	= "Charge.Amount";
$arrSQLSelect['Created On']					['Value']	= "DATE_FORMAT(Charge.CreatedOn, '%d/%m/%Y')";
$arrSQLSelect['Created By']					['Value']	= "CONCAT(Creator.FirstName, ' ', Creator.LastName)";
$arrSQLSelect['Charged On']					['Value']	= "DATE_FORMAT(Charge.ChargedOn, '%d/%m/%Y')";
$arrSQLSelect['Approved By']				['Value']	= "CONCAT(Approver.FirstName, ' ', Approver.LastName)";
$arrSQLSelect['Current Status']				['Value']	= "CASE WHEN Charge.Status = 100 THEN 'Awaiting Approval'
															WHEN Charge.Status = 101 THEN 'Approved'
															WHEN Charge.Status = 102 THEN 'Temporarily Invoiced'
															WHEN Charge.Status = 103 THEN 'Invoiced'
															WHEN Charge.Status = 104 THEN 'Declined'
															WHEN Charge.Status = 105 THEN 'Deleted'
															ELSE 'UNKNOWN' END";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrChargeTypeQuery = array('Query'			=> "SELECT DISTINCT Value, Label
												FROM (
													SELECT CONCAT(Nature, ': ', ChargeType, ' - ', Description) AS Value, 
														IF(Archived = 0, CONCAT('[ACTIVE] ', Nature, ': ', ChargeType, ' - ', Description), CONCAT('[ARCHVIED] ', Nature, ': ', ChargeType, ' - ', Description)) AS Label
													FROM ChargeType
													UNION
													SELECT CONCAT(Nature, ': ', ChargeType, ' - ', Description) AS Value, 
														IF(Archived = 0, CONCAT('[ACTIVE] ', Nature, ': ', ChargeType, ' - ', Description), CONCAT('[ARCHVIED] ', Nature, ': ', ChargeType, ' - ', Description)) AS Label
													FROM RecurringChargeType
													WHERE Nature = 'DR'
												) AS AdjustmentType
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
											'Documentation-Field'	=> "Adjustment Type",
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


//---------------------------------------------------------------------------//
// ADJUSTMENT REQUESTS THAT HAVE BEEN REJECTED
//---------------------------------------------------------------------------//

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


$arrDataReport['Name']			= "Adjustment Requests That Have Been Rejected";
$arrDataReport['Summary']		= "Lists all Adjustment Requests That Have Been Rejected";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 64;											// Credit Management
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Charge
									INNER JOIN Account ON Charge.Account = Account.Id
									LEFT JOIN Service ON Charge.Service = Service.Id
									INNER JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id
									INNER JOIN Employee AS Creator ON Charge.CreatedBy = Creator.Id
									LEFT JOIN Employee AS Rejector ON Charge.ApprovedBy = Rejector.Id";
$arrDataReport['SQLWhere']		= "	(<ChargeType> = 'ANY' OR CONCAT(Charge.Nature, ': ', Charge.ChargeType, ' - ', Charge.Description) = <ChargeType>)
									AND (<CustomerGroupId> = 0 OR Account.CustomerGroup = <CustomerGroupId>)
									AND (Charge.CreatedOn BETWEEN <EarliestDate> AND <LatestDate>)
									AND Charge.Status = 104
									ORDER BY Charge.CreatedOn ASC, Charge.Id ASC;";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Adjustment Id']				['Value']	= "Charge.Id";
$arrSQLSelect['Customer Group']				['Value']	= "CustomerGroup.internal_name";
$arrSQLSelect['Account']					['Value']	= "Charge.Account";
$arrSQLSelect['Service FNN']				['Value']	= "Service.FNN";
$arrSQLSelect['Nature']						['Value']	= "Charge.Nature";
$arrSQLSelect['Type']						['Value']	= "Charge.ChargeType";
$arrSQLSelect['Description']				['Value']	= "Charge.Description";
$arrSQLSelect['Amount ($ Ex GST)']			['Value']	= "Charge.Amount";
$arrSQLSelect['Created On']					['Value']	= "DATE_FORMAT(Charge.CreatedOn, '%d/%m/%Y')";
$arrSQLSelect['Created By']					['Value']	= "CONCAT(Creator.FirstName, ' ', Creator.LastName)";
$arrSQLSelect['Charged On']					['Value']	= "DATE_FORMAT(Charge.ChargedOn, '%d/%m/%Y')";
$arrSQLSelect['Rejected By']				['Value']	= "IF((Rejector.Id IS NOT NULL), CONCAT(Rejector.FirstName, ' ', Rejector.LastName), 'UNKNOWN')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrChargeTypeQuery = array('Query'			=> "SELECT DISTINCT Value, Label
												FROM (
													SELECT CONCAT(Nature, ': ', ChargeType, ' - ', Description) AS Value, 
														IF(Archived = 0, CONCAT('[ACTIVE] ', Nature, ': ', ChargeType, ' - ', Description), CONCAT('[ARCHVIED] ', Nature, ': ', ChargeType, ' - ', Description)) AS Label
													FROM ChargeType
													UNION
													SELECT CONCAT(Nature, ': ', ChargeType, ' - ', Description) AS Value, 
														IF(Archived = 0, CONCAT('[ACTIVE] ', Nature, ': ', ChargeType, ' - ', Description), CONCAT('[ARCHVIED] ', Nature, ': ', ChargeType, ' - ', Description)) AS Label
													FROM RecurringChargeType
													WHERE Nature = 'DR'
												) AS AdjustmentType
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
											'Documentation-Field'	=> "Adjustment Type",
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



//---------------------------------------------------------------------------//
// RECURRING ADJUSTMENT REQUESTS AWAITING APPROVAL
//---------------------------------------------------------------------------//

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


$arrDataReport['Name']			= "Recurring Adjustment Requests Awaiting Approval";
$arrDataReport['Summary']		= "Lists all Recurring Adjustment Requests Currently Awaiting Approval";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 64;											// Credit Management
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	RecurringCharge
									INNER JOIN Account ON RecurringCharge.Account = Account.Id
									LEFT JOIN Service ON RecurringCharge.Service = Service.Id
									INNER JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id
									INNER JOIN Employee AS Creator ON RecurringCharge.CreatedBy = Creator.Id";
$arrDataReport['SQLWhere']		= "	(<ChargeType> = 'ANY' OR CONCAT(RecurringCharge.Nature, ': ', RecurringCharge.ChargeType, ' - ', RecurringCharge.Description) = <ChargeType>)
									AND (<CustomerGroupId> = 0 OR Account.CustomerGroup = <CustomerGroupId>)
									AND (RecurringCharge.CreatedOn BETWEEN <EarliestDate> AND <LatestDate>)
									AND RecurringCharge.recurring_charge_status_id = (SELECT id FROM recurring_charge_status WHERE system_name = 'AWAITING_APPROVAL')
									ORDER BY RecurringCharge.CreatedOn ASC, RecurringCharge.Id ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Recurring Adjustment Id']		['Value']	= "RecurringCharge.Id";
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
											'Documentation-Field'	=> "Adjustment Type",
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


//---------------------------------------------------------------------------//
// RECURRING ADJUSTMENT REQUESTS THAT HAVE BEEN APPROVED
//---------------------------------------------------------------------------//

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


$arrDataReport['Name']			= "Recurring Adjustment Requests That Have Been Approved";
$arrDataReport['Summary']		= "Lists all Recurring Adjustment Requests That Have Been Approved";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 64;											// Credit Management
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	RecurringCharge
									INNER JOIN Account ON RecurringCharge.Account = Account.Id
									LEFT JOIN Service ON RecurringCharge.Service = Service.Id
									INNER JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id
									INNER JOIN Employee AS Creator ON RecurringCharge.CreatedBy = Creator.Id
									INNER JOIN Employee AS Approver ON RecurringCharge.ApprovedBy = Approver.Id
									INNER JOIN recurring_charge_status ON RecurringCharge.recurring_charge_status_id = recurring_charge_status.id";
$arrDataReport['SQLWhere']		= "	(<ChargeType> = 'ANY' OR CONCAT(RecurringCharge.Nature, ': ', RecurringCharge.ChargeType, ' - ', RecurringCharge.Description) = <ChargeType>)
									AND (<CustomerGroupId> = 0 OR Account.CustomerGroup = <CustomerGroupId>)
									AND (RecurringCharge.CreatedOn BETWEEN <EarliestDate> AND <LatestDate>)
									AND RecurringCharge.ApprovedBy IS NOT NULL
									AND RecurringCharge.recurring_charge_status_id != (SELECT id FROM recurring_charge_status WHERE system_name = 'DECLINED')
									AND (<IncludeAutoApproved> = 1 OR (<IncludeAutoApproved> = 0 AND RecurringCharge.ApprovedBy != 0))
									ORDER BY RecurringCharge.CreatedOn ASC, RecurringCharge.Id ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Recurring Adjustment Id']		['Value']	= "RecurringCharge.Id";
$arrSQLSelect['Customer Group']					['Value']	= "CustomerGroup.internal_name";
$arrSQLSelect['Account']						['Value']	= "RecurringCharge.Account";
$arrSQLSelect['Service FNN']					['Value']	= "Service.FNN";
$arrSQLSelect['Nature']							['Value']	= "RecurringCharge.Nature";
$arrSQLSelect['Type']							['Value']	= "RecurringCharge.ChargeType";
$arrSQLSelect['Description']					['Value']	= "RecurringCharge.Description";
$arrSQLSelect['Minimum Charge ($ Ex GST)']		['Value']	= "RecurringCharge.MinCharge";
$arrSQLSelect['Recurring Charge ($ Ex GST)']	['Value']	= "RecurringCharge.RecursionCharge";
$arrSQLSelect['Total Charged ($ Ex GST)']		['Value']	= "RecurringCharge.TotalCharged";
$arrSQLSelect['Created On']						['Value']	= "DATE_FORMAT(RecurringCharge.CreatedOn, '%d/%m/%Y')";
$arrSQLSelect['Created By']						['Value']	= "CONCAT(Creator.FirstName, ' ', Creator.LastName)";
$arrSQLSelect['Start Date']						['Value']	= "DATE_FORMAT(RecurringCharge.StartedOn, '%d/%m/%Y')";
$arrSQLSelect['Approved By']					['Value']	= "CONCAT(Approver.FirstName, ' ', Approver.LastName)";
$arrSQLSelect['Current Status']					['Value']	= "recurring_charge_status.name";

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
											'Documentation-Field'	=> "Adjustment Type",
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

$arrSQLFields['IncludeAutoApproved']	= Array(
											'Type'					=> "dataBoolean",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Include Automatically Approved",
											);


$arrDataReport['SQLFields'] = serialize($arrSQLFields);

// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;

//---------------------------------------------------------------------------//
// RECURRING ADJUSTMENT REQUESTS THAT HAVE BEEN REJECTED
//---------------------------------------------------------------------------//

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


$arrDataReport['Name']			= "Recurring Adjustment Requests That Have Been Rejected";
$arrDataReport['Summary']		= "Lists all Recurring Adjustment Requests That Have Been Rejected";
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
$arrSQLSelect['Recurring Adjustment Id']		['Value']	= "RecurringCharge.Id";
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
											'Documentation-Field'	=> "Adjustment Type",
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


//---------------------------------------------------------------------------//
// Tallied Plan Changes by Employee
//---------------------------------------------------------------------------//

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


$arrDataReport['Name']			= "Tallied Plan Changes by Employee";
$arrDataReport['Summary']		= "Lists the total number of plan changes (and service creations) made by each employee during the timeframe defined.  If an employee isn't listed in the report, then they did not perform any plan changes, or create any new services during the timeframe in question.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 8192;											// Proper Admin
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Employee INNER JOIN
									(
										SELECT DISTINCT Service.FNN AS fnn, ServiceRatePlan.CreatedBy AS employee_id, ServiceRatePlan.CreatedOn AS change_timestamp
										FROM Service INNER JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service
										WHERE ServiceRatePlan.EndDatetime > ServiceRatePlan.StartDatetime
									) AS ServicePlanChange ON Employee.Id = ServicePlanChange.employee_id";
$arrDataReport['SQLWhere']		= "	DATE(ServicePlanChange.change_timestamp) BETWEEN <EarliestDate> AND <LatestDate>
									GROUP BY Employee.Id
									ORDER BY COUNT(*) DESC, MAX(CONCAT(Employee.FirstName, ' ', Employee.LastName)) ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Employee']				['Value']	= "MAX(CONCAT(Employee.FirstName, ' ', Employee.LastName))";
$arrSQLSelect['Total Plan Changes']		['Value']	= "COUNT(*)";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['EarliestDate']		= Array(
											'Type'					=> "dataDate",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Earliest Date",
											);
$arrSQLFields['LatestDate']			= Array(
											'Type'					=> "dataDate",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Latest Date",
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