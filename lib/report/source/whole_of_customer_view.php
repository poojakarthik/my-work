<?php
/**
 * THIS REPORT IS INCOMPLETE 
 */

//---------------------------------------------------------------------------//
// PAYMENT DOWNLOAD
//---------------------------------------------------------------------------//
$arrDataReport['Name']					= "Whole of Customer View";
$arrDataReport['Summary']				= "A list of all customers.";
$arrDataReport['RenderMode']			= REPORT_RENDER_EMAIL;
$arrDataReport['Priviledges']			= PERMISSION_OPERATOR;
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLWhere'] 				= "a.CreatedOn <= <CreatedOnDate> AND (<CustomerGroup> = 0 OR a.CustomerGroup = <CustomerGroup>)";
$arrDataReport['SQLGroupBy'] 			= "GROUP BY a.Id ORDER BY a.Id";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

//-------------------------------------//
// START SQL TABLE
//-------------------------------------//
$arrDataReport['SQLTable']	= "
	FROM	Account a
JOIN	credit_control_status ccs
			ON a.credit_control_status = ccs.id
JOIN	CustomerGroup cg
			ON a.CustomerGroup = cg.id
JOIN	delivery_method dm
			ON a.BillingMethod = dm.id
JOIN	Contact c
			ON a.PrimaryContact = c.id
JOIN	billing_type bt
			ON a.BillingType = bt.id
JOIN	payment_method pm
			ON bt.payment_method_id = pm.id
LEFT JOIN	Invoice i_last 
				ON (
					i_last.Account = a.Id
					AND i_last.invoice_run_id =	(
						SELECT		ir.Id
						FROM		Invoice i2
									JOIN InvoiceRun ir ON (i2.invoice_run_id = ir.Id)
									JOIN invoice_run_type irt ON (ir.invoice_run_type_id = irt.id)
						WHERE		irt.const_name NOT IN ('INVOICE_RUN_TYPE_SAMPLES', 'INVOICE_RUN_TYPE_FINAL', 'INVOICE_RUN_TYPE_INTERNAL_SAMPLES')
						ORDER BY	ir.BillingDate DESC
						LIMIT		1 OFFSET 0
					)
				)
LEFT JOIN	Invoice i_second_last 
				ON (
					i_second_last.Account = a.Id
					AND i_second_last.invoice_run_id = (
						SELECT		ir.Id
						FROM		Invoice i2
									JOIN InvoiceRun ir ON (i2.invoice_run_id = ir.Id)
									JOIN invoice_run_type irt ON (ir.invoice_run_type_id = irt.id)
						WHERE		irt.const_name NOT IN ('INVOICE_RUN_TYPE_SAMPLES', 'INVOICE_RUN_TYPE_FINAL', 'INVOICE_RUN_TYPE_INTERNAL_SAMPLES')
						ORDER BY	ir.BillingDate DESC
						LIMIT		1 OFFSET 1
					)
				)
LEFT JOIN	(	/* service_cdr */
				SELECT		s.Account																			AS account_id,
							s.FNN																				AS service_fnn,
							s.ServiceType																		AS service_type,
							s.CreatedOn																			AS service_created_on,
							s.ClosedOn																			AS service_closed_on,
							rp.Description																		AS rate_plan_description,
							MAX(cdr.StartDatetime)																AS max_start_datetime,
							MAX(IF(cdr.StartDatetime >= (NOW() - INTERVAL 30 DAY), cdr.StartDatetime, NULL))	AS less_30_days_ago_max_start_datetime,
							MAX(IF(cdr.StartDatetime < (NOW() - INTERVAL 30 DAY), cdr.StartDatetime, NULL))		AS more_30_days_ago_max_start_datetime,
							MIN(cdr.StartDatetime)																AS min_start_datetime,
							sr.contract_scheduled_end_datetime													AS service_scheduled_end_datetime,
							sr.contract_effective_end_datetime													AS service_effective_end_datetime,
							rt.code																				AS cdr_record_type
				FROM		Service s
				LEFT JOIN 	CDR cdr 
								ON (cdr.Service = s.Id)
				JOIN		RecordType rt
								ON cdr.RecordType = rt.id
				LEFT JOIN	ServiceRatePlan sr
								ON sr.Service = s.Id
				LEFT JOIN	RatePlan rp
								ON rp.Id = sr.RatePlan
				JOIN		Account a
								ON a.Id = s.Account
				WHERE		cdr.Status IN (150, 198, 199)	/* Rated, Temporarily Invoiced, Invoiced */
				AND			sr.StartDatetime <= NOW() 
				AND			sr.EndDatetime > NOW() 
				AND 		a.CreatedOn <= <CreatedOnDate>
				AND			(<CustomerGroup> = 0 OR a.CustomerGroup = <CustomerGroup>)
				GROUP BY	s.Account,
							s.FNN
			) sc 
				ON (a.Id = sc.account_id)
LEFT JOIN	(	/* ticket_account */
				SELECT		a.Id			AS ticket_account,
							tt.id			AS ticket_id,
							tc.const_name	AS ticket_category,
							tst.const_name	AS ticket_status_type
				FROM 		Account a
				LEFT JOIN	ticketing_ticket tt
								ON a.Id = tt.account_id
				LEFT JOIN	ticketing_status ts
								ON tt.status_id = ts.id
				LEFT JOIN	ticketing_status_type tst
								ON ts.status_type_id = tst.id
				LEFT JOIN	ticketing_category tc
								ON tt.category_id = tc.id
				WHERE 		a.CreatedOn <= <CreatedOnDate>
				AND			(<CustomerGroup> = 0 OR a.CustomerGroup = <CustomerGroup>)
			) ta
				ON ta.ticket_account = a.Id
LEFT JOIN	(	/* invoice_account */
				SELECT		i.Account									AS invoice_account,
							i.Id										AS invoice_id,
							COALESCE(i.Balance, 0)						AS invoice_balance,
							i.DueOn										AS invoice_due_on,
							COALESCE(i.Total, 0) + COALESCE(i.Tax, 0)	AS invoice_amount
				FROM		Invoice i
				JOIN 		InvoiceRun ir 
								ON i.invoice_run_id = ir.Id
				JOIN 		invoice_run_type irt 
								ON ir.invoice_run_type_id = irt.id
				JOIN		Account a
								ON i.Account = a.Id
				WHERE		irt.const_name NOT IN ('INVOICE_RUN_TYPE_SAMPLES', 'INVOICE_RUN_TYPE_INTERNAL_SAMPLES')
				AND			i.Status <> 106	/* Not written off */
				AND 		a.CreatedOn <= <CreatedOnDate>
				AND			(<CustomerGroup> = 0 OR a.CustomerGroup = <CustomerGroup>)
				GROUP BY	i.Account, i.Id
			) ia
				ON a.Id = ia.invoice_account
LEFT JOIN	(	/* unbilled_charges */
				SELECT		c.Account					AS charge_account,
							c.Id						AS charge_id,
							COALESCE(c.Amount, 0) * 1.1	AS charge_amount
				FROM		Charge c
				JOIN		Account a
								ON c.Account = a.Id
				WHERE		c.Status in (101, 102)	/* Approved / Temporarily Invoiced */
				AND 		a.CreatedOn <= <CreatedOnDate>
				AND			(<CustomerGroup> = 0 OR a.CustomerGroup = <CustomerGroup>)
				GROUP BY	a.Id
			) uc
				ON a.Id = uc.charge_account";
//-------------------------------------//
// END SQL TABLE
//-------------------------------------//

//-------------------------------------//
// START SQL SELECT
//-------------------------------------//
$arrSQLSelect['Account Number']['Value']		= "a.Id";
$arrSQLSelect['Business Number']['Value']		= "a.BusinessName";
$arrSQLSelect['Created On']['Value']			= "a.CreatedOn";
$arrSQLSelect['Address']['Value']				= "CONCAT(a.Address1, ' ', a.Address2)";
$arrSQLSelect['Suburb']['Value']				= "a.Suburb";
$arrSQLSelect['State']['Value']					= "a.State";
$arrSQLSelect['Postcode']['Value']				= "a.Postcode";
$arrSQLSelect['Credit Control Status']['Value']	= "ccs.description";
$arrSQLSelect['Payment Method']['Value']		= "
													CONCAT(pm.name, ' ', (
														CASE
															WHEN	pm.const_name = 'PAYMENT_METHOD_DIRECT_DEBIT' 
															THEN	CASE
																		WHEN	bt.const_name = 'BILLING_TYPE_DIRECT_DEBIT'
																		THEN	CONCAT('via ', (
																					SELECT	ddt.name
																					FROM	direct_debit_type ddt
																					WHERE	ddt.const_name = 'DIRECT_DEBIT_TYPE_BANK_ACCOUNT'
																				))
																		WHEN	bt.const_name = 'BILLING_TYPE_CREDIT_CARD'
																		THEN	CONCAT('via ', (
																					SELECT	ddt.name
																					FROM	direct_debit_type ddt
																					WHERE	ddt.const_name = 'DIRECT_DEBIT_TYPE_CREDIT_CARD'
																				))
																	END
															WHEN	pm.const_name = 'PAYMENT_METHOD_REBILL' 
															THEN	CONCAT('via ', (
																		SELECT	rt.name
																		FROM	rebill_type rt
																		JOIN	rebill r
																					ON rt.id = r.rebill_type_id
																		WHERE	a.Id = r.account_id
																		AND		r.created_timestamp in (
																					SELECT	MAX(r2.created_timestamp)
																					FROM	rebill r2
																					WHERE	r2.account_id = a.Id
																				)
																	))
															ELSE	''
														END
													))";
$arrSQLSelect['Expiry Date']['Value']	= "
											(
												CASE
													WHEN	bt.const_name = 'BILLING_TYPE_CREDIT_CARD'
													THEN	CONCAT((
																SELECT	CONCAT(cc.ExpMonth, '/', cc.ExpYear)
																FROM	CreditCard cc
																WHERE	cc.id = a.CreditCard
															), '')
													WHEN	pm.const_name = 'PAYMENT_METHOD_REBILL' 
													THEN	CASE
																WHEN	'REBILL_TYPE_MOTORPASS' = (
																			SELECT	rt.const_name
																			FROM	rebill_type rt
																			JOIN	rebill r
																						ON rt.id = r.rebill_type_id
																			WHERE	r.account_id = a.Id
																			AND		r.created_timestamp in (
																						SELECT	MAX(r2.created_timestamp)
																						FROM	rebill r2
																						WHERE	r2.account_id = r.account_id
																					)
																		)
																THEN	CONCAT((
																			SELECT	rm.card_expiry_date
																			FROM	rebill_motorpass rm
																			JOIN	rebill r
																						ON rm.rebill_id = r.id
																			WHERE	r.account_id = a.Id
																			AND		r.created_timestamp in (
																						SELECT	MAX(r2.created_timestamp)
																						FROM	rebill r2
																						WHERE	r2.account_id = r.account_id
																					)
																		), '')
																ELSE	''
															END
													ELSE	''
												END
											)";
$arrSQLSelect['Bill Delivery Method']['Value']		= "dm.description";
$arrSQLSelect['Contact Name']['Value']				= "CONCAT(c.FirstName, ' ', c.LastName)";
$arrSQLSelect['Contact Number Landline']['Value']	= "c.Phone";
$arrSQLSelect['Contact Number Mobile']['Value']		= "c.Mobile"; 
$arrSQLSelect['Contact E-Mail']['Value']			= "c.Email";
$arrSQLSelect['Customer Group']['Value']			= "cg.internal_name";
$arrSQLSelect['Late Notices Flag']['Value']			= "
														(
															CASE
																WHEN	a.LatePaymentAmnesty = '9999-12-31'
																THEN 	'Never send late notices'
																WHEN	(a.LatePaymentAmnesty < now()) OR (a.LatePaymentAmnesty IS NULL)
																THEN	'Send late notices'
																ELSE	CONCAT('Exempt until after ', a.LatePaymentAmnesty)
															END
														)";
$arrSQLSelect['Late Payment Fee Flag']['Value']	= "
													(
														CASE
															WHEN	(a.DisableLatePayment = 0) OR (a.DisableLatePayment IS NULL)
															THEN	'Charge a late payment fee'
															WHEN	a.DisableLatePayment = -1
															THEN	'Do not charge a late payment fee on the next invoice'
															WHEN	a.DisableLatePayment = 1
															THEN	'Never charge a late payment fee'
														END
													)";
$arrSQLSelect['Count Tolling Services']['Value']	= "
														COUNT(
															DISTINCT IF(
																sc.max_start_datetime > (NOW() - INTERVAL 14 DAY), 
																sc.service_fnn, 
																NULL
															)
														)";
 $arrSQLSelect['Count Mobiles']['Value']	= "
												COUNT(
													DISTINCT IF(
														(sc.max_start_datetime > (NOW() - INTERVAL 14 DAY)) 
														AND (sc.service_type = 101), 
														sc.service_fnn, 
														NULL
													)
												)";
$arrSQLSelect['Count Inbound']['Value']	= "
											COUNT(
												DISTINCT IF(
													(sc.max_start_datetime > (NOW() - INTERVAL 14 DAY)) 
													AND	(sc.service_type = 103), 
													sc.service_fnn, 
													NULL
												)
											)";
$arrSQLSelect['Count ADSL']['Value']	= "
											COUNT(
												DISTINCT IF(
													(sc.max_start_datetime > (NOW() - INTERVAL 14 DAY)) 
													AND	(sc.service_type = 100) 
													AND (sc.service_created_on <= NOW()) 
													AND ((sc.service_closed_on IS NULL) OR (sc.service_closed_on > NOW())),
													sc.service_fnn, 
													NULL
												)
											)";
$arrSQLSelect['Count Land Line']['Value']	= "
												COUNT(
													DISTINCT IF(
														(sc.max_start_datetime > (NOW() - INTERVAL 14 DAY))
														AND (sc.service_type = 102), 
														sc.service_fnn, 
														NULL
													)
												)";
$arrSQLSelect['Count Wireless']['Value']	= "
												COUNT(
													DISTINCT IF(
														(sc.max_start_datetime > (NOW() - INTERVAL 14 DAY))
														AND (sc.service_type = 101)
														AND (sc.rate_plan_description like '#wireless%'), 
														sc.service_fnn, 
														NULL
													)
												)";
$arrSQLSelect['Average Last 2 Bills']['Value']	= "
													COALESCE(
														(((COALESCE(i_last.Total,0) + COALESCE(i_last.Tax,0)) + (COALESCE(i_second_last.Total,0) + COALESCE(i_second_last.Tax,0))) / 2), 
														0
													)";
$arrSQLSelect['Last Billed Amount']['Value']			= "COALESCE(i_last.Total,0) + COALESCE(i_last.Tax,0)";
$arrSQLSelect['Count Closed Tickets (Other)']['Value']	= "
															COUNT(
																DISTINCT IF(
																	ta.ticket_status_type = 'TICKETING_STATUS_TYPE_CLOSED'
																	AND ta.ticket_category <> 'TICKETING_CATEGORY_FAULTS',
																	ta.ticket_id,
																	NULL
																)
															)";
$arrSQLSelect['Count Open Tickets (Other)']['Value']	= "
															COUNT(
																DISTINCT IF(
																	ta.ticket_status_type = 'TICKETING_STATUS_TYPE_OPEN'
																	AND ta.ticket_category <> 'TICKETING_CATEGORY_FAULTS',
																	ta.ticket_id,
																	NULL
																)
															)";
$arrSQLSelect['Count Closed Tickets (Faults)']['Value']	= "
															COUNT(
																DISTINCT IF(
																	ta.ticket_status_type = 'TICKETING_STATUS_TYPE_CLOSED'
																	AND ta.ticket_category = 'TICKETING_CATEGORY_FAULTS',
																	ta.ticket_id,
																	NULL
																)
															)";
$arrSQLSelect['Count Open Tickets (Faults)']['Value']	= "
															COUNT(
																DISTINCT IF(
																	ta.ticket_status_type = 'TICKETING_STATUS_TYPE_OPEN'
																	AND ta.ticket_category = 'TICKETING_CATEGORY_FAULTS',
																	ta.ticket_id,
																	NULL
																)
															)";
$arrSQLSelect['Earliest CDR']['Value']					= "MIN(sc.min_start_datetime)";
$arrSQLSelect['Latest CDR']['Value']					= "MAX(sc.max_start_datetime)";
$arrSQLSelect['Total Number of Bills Sent']['Value']	= "
															COUNT(
																DISTINCT IF(
																	ia.invoice_amount <> 0,
																	ia.invoice_id,
																	NULL
																)
															)";
$arrSQLSelect['Total Value of Bills Sent']['Value']	= "SUM(ia.invoice_amount)";
$arrSQLSelect['ABN']['Value']						= "a.ABN";
$arrSQLSelect['Current Balance Due']['Value']		= "SUM(ia.invoice_balance)";
$arrSQLSelect['Overdue Balance']['Value']			= "
														SUM(
															IF(
																ia.invoice_due_on >= NOW(),
																ia.invoice_balance,
																NULL
															)
														)";
$arrSQLSelect['Unbilled Adjustments']['Value']		= "SUM(uc.charge_amount)";
$arrSQLSelect['Count Contracted Services']['Value']	= "
														COUNT(
															DISTINCT IF(
																sc.service_scheduled_end_datetime NOT IS NULL
																AND sc.service_effective_end_datetime IS NULL,
																sc.service_fnn,
																NULL
															)
														)";
$arrSQLSelect['Earliest Expiry Date']['Value']	= "
													MIN(
														IF(
															sc.service_effective_end_datetime IS NULL,
															sc.service_scheduled_end_datetime,
															NULL
														)
													)";
$arrSQLSelect['Bundled Products Count']['Value']	= "
														COUNT(
															DISTINCT IF(
																sc.rate_plan_description like '#bundled%', 
																sc.service_fnn, 
																NULL
															)
														)";
$arrSQLSelect['Count Services Lost']['Value']	= "
													COUNT(
														DISTINCT IF(
															(sc.max_start_datetime <= (NOW() - INTERVAL 14 DAY))
															AND (sc.less_30_days_ago_max_start_datetime NOT IS NULL)
															AND (sc.cdr_record_type <> 'S&E'), 
															sc.service_fnn, 
															NULL
														)
													)";
$arrSQLSelect['Count Services Gained']['Value']	= "
													COUNT(
														DISTINCT IF(
															(sc.less_30_days_ago_max_start_datetime NOT IS NULL)
															AND ((more_30_days_ago_max_start_datetime IS NULL) OR (sc.max_start_datetime <= (NOW() - INTERVAL 2 MONTH)))
															AND (sc.cdr_record_type <> 'S&E'), 
															sc.service_fnn, 
															NULL
														)
													)";
//-------------------------------------//
// END SQL SELECT
//-------------------------------------//
$arrDataReport['SQLSelect']	= serialize($arrSQLSelect);

// SQL Fields
$aCustomerGroupQuery			=	array(
										'Query'			=> "SELECT		Id AS `Value`, external_name AS `Label`
															FROM		CustomerGroup
															ORDER BY	Id ASC;",
										'ValueType'		=> "dataInteger",
										'IgnoreField'	=> array('Value' => 0, 'Label' => 'Any')
									);
/*$arrSQLFields['CustomerGroup']	= 	array(
										'Type'					=> "Query",
										'DBQuery'				=> $aCustomerGroupQuery,
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Customer Group",
									);
$arrSQLFields['CreatedOnDate']	= 	array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Account Created On or Before",
									);*/
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);

?>