<?php
//---------------------------------------------------------------------------//
// Motorpass Exception Reporting - Payment Method and Plan Misalignment
//---------------------------------------------------------------------------//
$arrDataReport['Name']					= "Exception Reporting – Payment Method and Plan Misalignment";
$arrDataReport['Summary']				= "Shows accounts which do not have Rebill as their payment method but do have a Rebill plans, as well as accounts which are set to Rebill which do not have Rebill plans.";
$arrDataReport['RenderMode']			= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']			= PERMISSION_OPERATOR;
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLWhere'] 				= "	(
												pm.const_name = 'PAYMENT_METHOD_REBILL'
												AND 0 = (
													SELECT	COUNT(DISTINCT IF(rp.Id IN (100000619), srp.Id, NULL))
													FROM 	Service s
													JOIN	ServiceRatePlan srp
																ON (
																	srp.Service = s.Id
																	AND srp.StartDatetime <= NOW() 
																	AND	srp.EndDatetime > NOW()
																)
													JOIN	RatePlan rp
																ON rp.Id = srp.RatePlan
													WHERE	s.Account = a.Id
												)
											) OR (
												pm.const_name <> 'PAYMENT_METHOD_REBILL'
												AND 0 < (
													SELECT	COUNT(DISTINCT IF(rp.Id IN (100000619), srp.Id, NULL))
													FROM 	Service s
													JOIN	ServiceRatePlan srp
																ON (
																	srp.Service = s.Id
																	AND srp.StartDatetime <= NOW() 
																	AND	srp.EndDatetime > NOW()
																)
													JOIN	RatePlan rp
																ON rp.Id = srp.RatePlan
													WHERE	s.Account = a.Id
												)
											);";
$arrDataReport['SQLGroupBy'] 			= "";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL TABLE
$arrDataReport['SQLTable']	= "	FROM 	Account a
								JOIN	billing_type bt
											ON bt.id = a.BillingType
								JOIN	payment_method pm
											ON pm.id = bt.payment_method_id";
// SQL SELECT
$arrSQLSelect['Account Number']['Value']						= "a.Id";
$arrSQLSelect['Business Name']['Value']							= "a.BusinessName";
$arrSQLSelect['Payment Method']['Value']						= "pm.name";
$arrSQLSelect['Total Number Of Services']['Value']				= "	(
																		SELECT	COUNT(DISTINCT srp.Id)
																		FROM 	Service s
																		JOIN	ServiceRatePlan srp
																					ON (
																						srp.Service = s.Id
																						AND srp.StartDatetime <= NOW() 
																						AND	srp.EndDatetime > NOW()
																					)
																		WHERE	s.Account = a.Id
																	)";
$arrSQLSelect['Total Number Of Services']['Type']				= EXCEL_TYPE_INTEGER;
$arrSQLSelect['Number Of Services with Rebill Plans']['Value']	= "	(
																		SELECT	COUNT(DISTINCT IF(rp.Id IN (100000619), srp.Id, NULL))
																		FROM 	Service s
																		JOIN	ServiceRatePlan srp
																					ON (
																						srp.Service = s.Id
																						AND srp.StartDatetime <= NOW() 
																						AND	srp.EndDatetime > NOW()
																					)
																		JOIN	RatePlan rp
																					ON rp.Id = srp.RatePlan
																		WHERE	s.Account = a.Id
																	)";
$arrSQLSelect['Number Of Services with Rebill Plans']['Type']	= EXCEL_TYPE_INTEGER;
$arrDataReport['SQLSelect']										= serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields					= array();
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);

?>