<?php
//---------------------------------------------------------------------------//
// PAYMENT DOWNLOAD
//---------------------------------------------------------------------------//
$arrDataReport['Name']					= "Motorpass Plan & Promotion Code Report";
$arrDataReport['Summary']				= "A list of all Accounts where the active payment method is Motorpass";
$arrDataReport['RenderMode']			= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']			= 2147483648;
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLTable']				= "	Account a
												JOIN billing_type b                		   	ON  (a.BillingType = b.id AND b.system_name = 'REBILL')
												JOIN rebill r                      		    ON  (a.Id = r.account_id)
												JOIN rebill_type rt                		    ON  (r.rebill_type_id = rt.id AND rt.system_name = 'MOTORPASS')
												JOIN rebill_motorpass rm           		    ON  (r.Id = rm.rebill_id)
												JOIN motorpass_account ma          		    ON  (rm.motorpass_account_id = ma.id)
												JOIN motorpass_promotion_code mpc  		    ON  (mpc.id = ma.motorpass_promotion_code_id)
												JOIN Contact c                    		    ON  (a.PrimaryContact = c.id)
												LEFT JOIN account_history ah                ON  (a.Id = ah.account_id
																		                      	 AND a.BillingType <> ah.billing_type
																			                     AND ah.change_timestamp =
																			                       (
																				                      select max(change_timestamp)
																				                      FROM account_history z
																				                      where z.account_id = a.Id
																				                      and a.BillingType <> z.billing_type
																			                       )
																								 )
												JOIN billing_type b2               		  	 ON  (ah.billing_type = b2.id)
												JOIN Service s                            	 ON  (a.Id = s.Account
																			                      AND s.CreatedOn <=now()
																			                      AND (ISNULL(s.ClosedOn) OR s.ClosedOn>=Now())
																		                      	 )
												JOIN ServiceRatePlan srp           			 ON (s.Id = srp.Service
																							     AND NOW() BETWEEN srp.StartDatetime AND srp.EndDatetime
																								 AND srp.Id =
																								 			(
																					                            SELECT Id
																					                            FROM ServiceRatePlan
																					                            WHERE Service = s.Id
																					                            AND NOW() BETWEEN StartDatetime AND EndDatetime
																					                            ORDER BY CreatedOn DESC
																					                            LIMIT 1
																					                         )
																								)
												JOIN RatePlan rp               				ON  (rp.Id = srp.RatePlan)
												JOIN motorpass_promotioncode_rateplan mprp 	ON (rp.Id = mprp.rateplan_id)
												LEFT JOIN account_history ah2            	ON  (a.Id = ah2.account_id
																								 AND ah2.change_timestamp =
																									     (
																										    SELECT min(change_timestamp) AS 'mindate'
																										    FROM account_history x
							                                          										JOIN rebill q ON (x.account_id = q.account_id and DATE(q.created_timestamp) = DATE(x.change_timestamp) and q.rebill_type_id = 1)
																										    where x.account_id = a.Id
																										    and a.BillingType = x.billing_type
																										  )
																								)

												order by a.Id, s.FNN";
/*$arrDataReport['SQLWhere']				= "	i.Balance > 0
											AND cc.const_name = 'CREDIT_CONTROL_STATUS_SENDING_TO_DEBT_COLLECTION'
											AND a.tio_reference_number IS NULL";*/
$arrDataReport['SQLGroupBy']			= " i.Account HAVING `Balance Due` > 0";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);



// SQL Select
$arrSQLSelect['Account Number']	['Value']	= "a.id";
$arrSQLSelect['Account Number']	['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Service Number']	['Value']	= "s.FNN";

$arrSQLSelect['Rate Plan']	['Value']	= "rp.Name";

$arrSQLSelect['Date Motorpass Processed']			['Value']	= "COALESCE(ah2.change_timestamp, 'UNKNOWN')";


$arrSQLSelect['Promotion Code']	['Value']	= "mpc.name";

$arrSQLSelect['Old Payment Method']		['Value']	= "COALESCE(b2.name, 'UNKNOWN')";


$arrSQLSelect['Business Name']			['Value']	= "a.BusinessName";


$arrSQLSelect['Contact Name']			['Value']	= "CONCAT_WS(' ',c.FirstName, c.LastName)";



$arrSQLSelect['Phone']			['Value']	= "IF(CONVERT(c.Phone,UNSIGNED), LPAD(c.Phone,10,0), NULL)";
$arrSQLSelect['Phone']			['Type']	= FNN;



$arrDataReport['SQLSelect'] 	= serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = array();
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);

?>