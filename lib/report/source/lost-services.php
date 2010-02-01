<?php
//---------------------------------------------------------------------------//
// Lost Services in a Date Period (Loss Report)
//---------------------------------------------------------------------------//

$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();

$arrDataReport['Name']			= "Lost Services in a Date Period";
$arrDataReport['Summary']		= "Lists all of the services which were lost in the specified period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Service s
									JOIN ProvisioningResponse pr ON (s.FNN = pr.FNN AND s.Account = pr.Account AND pr.Type IN (910, 916) AND CAST(pr.ImportedOn AS DATE) BETWEEN <StartDate> AND <EndDate>)
									JOIN Account a ON (a.Id = s.Account)
									JOIN CustomerGroup cg ON (cg.Id = a.CustomerGroup)
									JOIN Contact c ON (a.PrimaryContact = c.Id)
									
									LEFT JOIN ServiceRatePlan srp ON (s.Id = srp.Service AND <EndDate> BETWEEN srp.StartDatetime AND srp.EndDatetime)
									LEFT JOIN RatePlan rp ON (rp.Id = srp.RatePlan)
									
									LEFT JOIN Service s_active ON (a.Id = s_active.Account AND (s_active.CreatedOn < s_active.ClosedOn OR s_active.ClosedOn IS NULL) AND CAST(s.CreatedOn AS DATE) <= <EndDate> AND (s_active.ClosedOn IS NULL OR CAST(s_active.ClosedOn AS DATE) > <EndDate>))
";
$arrDataReport['SQLWhere']		= "	pr.Id =	(
													SELECT		Id
													FROM		ProvisioningResponse
													WHERE		FNN = s.FNN
																AND Account = s.Account
																AND Type IN (910, 916)
																AND CAST(ImportedOn AS DATE) BETWEEN <StartDate> AND <EndDate>
													ORDER BY	EffectiveDate DESC,
																Id DESC
													LIMIT 1
												)
									AND srp.Id =	(
														SELECT		Id
														FROM		ServiceRatePlan
														WHERE		Service = s.Id
																	AND <EndDate> BETWEEN StartDatetime AND EndDatetime
														ORDER BY	CreatedOn DESC
														LIMIT		1
													)
									AND
									(
										SELECT		Type
										FROM		ProvisioningResponse
										WHERE		FNN = s.FNN
													AND Account = s.Account
													AND
													(
														(
															Type IN (900)
														)
														OR
														(
															Type IN (910, 916)
															AND CAST(ImportedOn AS DATE) BETWEEN <StartDate> AND <EndDate>
														)
													)
										ORDER BY	EffectiveDate DESC,
													(Type = 900) DESC
										LIMIT		1
									) IN (910, 916)";
$arrDataReport['SQLGroupBy']	= "	a.Id,
									s.FNN

ORDER BY							a.Id,
									s.FNN";


// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']								['Value']	= "a.Id";
$arrSQLSelect['Account']								['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']							['Value']	= "a.BusinessName";

$arrSQLSelect['Customer Group']							['Value']	= "cg.internal_name";

$arrSQLSelect['Primary Contact']						['Value']	= "CONCAT(c.FirstName, ' ', c.LastName)";

$arrSQLSelect['Contact Phone']							['Value']	= "IF(CAST(c.Phone AS UNSIGNED) > 0, c.Phone, c.Mobile)";
$arrSQLSelect['Contact Phone']							['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Service FNN']							['Value']	= "s.FNN";
$arrSQLSelect['Service FNN']							['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Rate Plan']								['Value']	= "rp.Name";

$arrSQLSelect['Loss Date']								['Value']	= "pr.EffectiveDate";

$arrSQLSelect['Loss Details']							['Value']	= "pr.Description";

$arrSQLSelect['Account Active Service Count']			['Value']	= "COUNT(s_active.Id)";
$arrSQLSelect['Account Active Service Count']			['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Start Date",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "End Date",
									);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


?>