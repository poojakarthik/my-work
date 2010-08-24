<?php
//---------------------------------------------------------------------------//
// PAYMENT DOWNLOAD
//---------------------------------------------------------------------------//
$arrDataReport['Name']					= "Motorpass Payment Method Changes";
$arrDataReport['Summary']				= "A list of all Accounts that should have their motorpass card cancelled because of payment method changes or plan changes";
$arrDataReport['RenderMode']			= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']			= 64;
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLTable']				= "	(

											SELECT			a.Id					AS 'flex_account',
															rm.motorpass_account_id	AS 'red_account',
															'Plan Change'			AS 'cancellation_reason',
															MAX(srp.EndDateTime)	AS 'cancel_date'

											FROM		Account a
														JOIN rebill r ON (a.Id = r.account_id)
														JOIN rebill_motorpass rm ON (r.Id = rm.rebill_id)
														JOIN Service s ON (a.Id = s.Account)
														JOIN ServiceRatePlan srp ON	(
																						s.Id = srp.Service
																						AND CAST(srp.EndDateTime AS DATE) BETWEEN <StartDate> AND <EndDate>
																					)
														JOIN RatePlan rp ON (rp.Id = srp.RatePlan)
														JOIN motorpass_promotioncode_rateplan mprp ON (rp.Id = mprp.rateplan_id)

											WHERE		a.ID NOT IN	(
																		 SELECT		a2.Id	AS accountId
																		 FROM		Account a2
																					JOIN rebill r2 ON (a2.Id = r2.account_id)
																					JOIN rebill_motorpass rm2 ON (r2.Id = rm2.rebill_id)
																					JOIN Service s2 ON (a2.Id = s2.Account)
																					JOIN ServiceRatePlan srp2 ON (
																													s2.Id = srp2.Service
																													AND CAST(srp2.EndDateTime AS DATE) > <EndDate>
																													AND CAST(srp2.StartDatetime AS DATE) <= <EndDate>
																												)
																					JOIN RatePlan rp2 ON (rp2.Id = srp2.RatePlan)
																					JOIN motorpass_promotioncode_rateplan mprp2 ON (rp2.Id = mprp2.rateplan_id)
																		GROUP BY	a2.Id, srp2.EndDateTime
																		HAVING		COUNT(accountId) > 0
																		ORDER BY	a2.Id
																	)

											GROUP BY	a.Id

											UNION


											SELECT			ah.account_id 			AS 'flex_account',
															rm.account_number 		AS 'red_account',
															'Payment Method Change' 	AS 'cancellation_reason',
															ah.change_timestamp 	AS 'cancel_date'

											FROM 			account_history ah JOIN rebill r ON (ah.account_id = r.account_id) /*by joining onto the rebill and motorpass tables we make sure we only select acounts with a motorpass card*/
															JOIN rebill_motorpass rm ON (r.id = rm.rebill_id)

															/*restrict the result to include only accounts that had their payment method changed from 4 to something else during the specified period*/
											WHERE 			ah.change_timestamp = (	SELECT MIN(q.change_timestamp)
																					FROM account_history q
																					WHERE q.billing_type <> 4
																					AND q.account_id = ah.account_id
																					AND q.change_timestamp > /*by doing this we ensure we get the earliest date for the current account where the billing type is not 4*/
																					(
																						SELECT MAX(z.change_timestamp)
																						FROM account_history z
																						WHERE z.billing_type = 4
																						AND CAST(z.change_timestamp AS DATE) BETWEEN <StartDate> AND <EndDate>

																					)
																					 AND CAST(q.change_timestamp AS DATE) BETWEEN <StartDate> AND <EndDate>

																					)
															/* restrict the result to the set of account IDs that on the specified date did not have billing type 4*/
											AND 			ah.account_id in
															(
																SELECT 	u.account_id
																FROM
																		(
																			SELECT a.account_id, a.billing_type
																			FROM account_history a
																			JOIN rebill r2 ON (a.account_id = r2.account_id)
																			JOIN rebill_motorpass rm2 ON (r2.id = rm2.rebill_id)
																			WHERE a.change_timestamp =	(
																								SELECT MAX(x.change_timestamp)
																								FROM account_history x
																								WHERE x.account_id = a.account_id
																								AND CAST(x.change_timestamp AS DATE)<= <EndDate>
																								)
																		) u
																WHERE u.billing_type<>4
															)
										) REQ05
										ORDER BY 'Date of Cancellation'";
$arrDataReport['SQLWhere']				= "";
$arrDataReport['SQLGroupBy']			= "";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);



// SQL Select


$arrSQLSelect['Flex Account Number']	['Value']	= "REQ05.flex_account";
$arrSQLSelect['Flex Account Number']	['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Retail Decisions Account Number']	['Value']	= "REQ05.red_account";
$arrSQLSelect['Retail Decisions Account Number']	['Type']	= EXCEL_TYPE_INTEGER;


$arrSQLSelect['Cancellation Reason']			['Value']	= "REQ05.cancellation_reason";

$arrSQLSelect['Date of Cancellation']			['Value']	= "REQ05.cancel_date";





$arrDataReport['SQLSelect'] 	= serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = array();
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
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);



?>