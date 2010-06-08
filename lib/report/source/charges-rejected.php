<?php

//---------------------------------------------------------------------------//
// CHARGE REQUESTS THAT HAVE BEEN REJECTED
//---------------------------------------------------------------------------//

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


$arrDataReport['Name']					= "Charge Requests That Have Been Rejected";
$arrDataReport['Summary']				= "Lists all Charge Requests That Have Been Rejected";
$arrDataReport['RenderMode']			= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']			= 64;											// Credit Management
//$arrDataReport['Priviledges']			= 1;											// Live
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLTable']				= "	Charge
											INNER JOIN Account ON Charge.Account = Account.Id
											LEFT JOIN Service ON Charge.Service = Service.Id
											INNER JOIN CustomerGroup ON Account.CustomerGroup = CustomerGroup.Id
											INNER JOIN Employee AS Creator ON Charge.CreatedBy = Creator.Id
											LEFT JOIN Employee AS Rejector ON Charge.ApprovedBy = Rejector.Id
											JOIN charge_model cm ON (cm.id = Charge.charge_model_id)";
$arrDataReport['SQLWhere']				= "	(<ChargeType> = 'ANY' OR CONCAT(Charge.Nature, ': ', Charge.ChargeType, ' - ', Charge.Description) = <ChargeType>)
											AND (<CustomerGroupId> = 0 OR Account.CustomerGroup = <CustomerGroupId>)
											AND (Charge.CreatedOn BETWEEN <EarliestDate> AND <LatestDate>)
											AND Charge.Status = 104
											AND (<charge_model_id> = 0 OR cm.id = <charge_model_id>)
											ORDER BY Charge.CreatedOn ASC, Charge.Id ASC;";
$arrDataReport['SQLGroupBy']			= "";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Charge Id']					['Value']	= "Charge.Id";
$arrSQLSelect['Customer Group']				['Value']	= "CustomerGroup.internal_name";
$arrSQLSelect['Account']					['Value']	= "Charge.Account";
$arrSQLSelect['Service FNN']				['Value']	= "Service.FNN";
$arrSQLSelect['Model']						['Value']	= "cm.name";
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
												) AS ChargeType
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


$arrChargeModelQuery = array(	'Query'			=> "SELECT id AS Value, name AS Label
													FROM charge_model
													ORDER BY name ASC;",
								'ValueType'		=> "dataInteger",
								'IgnoreField'	=> array(	'Value'	=> 0,
															'Label'	=> 'Any')
								);



$arrSQLFields['charge_model_id']	= Array('Type'					=> "Query",
											'DBQuery'				=> $arrChargeModelQuery,
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Charge Model",
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