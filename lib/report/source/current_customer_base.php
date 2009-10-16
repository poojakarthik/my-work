<?php
//---------------------------------------------------------------------------//
// Sales: Current Customer Base
//---------------------------------------------------------------------------//

$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();

$arrDataReport['Name']			= "Sales: Current Customer Base";
$arrDataReport['Summary']		= "Shows all active & tolling Customers in Flex for the given Customer Group";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Account a
									JOIN Contact c ON (c.Id = a.PrimaryContact)
									JOIN CustomerGroup cg ON (cg.Id = a.CustomerGroup)
									JOIN Service s ON (a.Id = s.Account AND s.Status = 400)
									JOIN ServiceRatePlan srp ON (srp.Service = s.Id AND NOW() BETWEEN srp.StartDatetime AND srp.EndDatetime)
									JOIN RatePlan rp ON (srp.RatePlan = rp.Id)
									LEFT JOIN
									(
										SELECT		Service.FNN																										AS fnn,
													MIN(ir.BillingDate)																								AS earliest_last_billing_date,
													MAX(Service.LatestCDR)																							AS latest_cdr,
													SUM(stt.Records)																								AS invoiced_cdr_count,
													IF(MAX(Service.LatestCDR) IS NOT NULL AND MIN(ir.BillingDate) <= CAST(MAX(Service.LatestCDR) AS DATE), 1, 0)	AS has_unbilled_cdrs
										FROM		Service
													JOIN ServiceTypeTotal stt ON (Service.Id = stt.Service)
													JOIN
													(
														SELECT		customer_group_id,
																	MAX(Id)				AS last_invoice_run_id								FROM		InvoiceRun
														WHERE		invoice_run_type_id = 1
																	AND customer_group_id IS NOT NULL
														GROUP BY	customer_group_id
													) /* customer_group_last_invoice_run */ cglir ON (stt.invoice_run_id = cglir.last_invoice_run_id)
													JOIN InvoiceRun ir ON (ir.Id = stt.invoice_run_id)
										WHERE		1
										GROUP BY	fnn
									) /* fnn_cdr_summary */ fcs ON (s.FNN = fcs.fnn)";
$arrDataReport['SQLWhere']		= "	a.Archived = 0
									AND a.vip = 0
									AND a.credit_control_status NOT IN (3, 4)	/* Sending to Debt Collection/Debt Collection */
									AND (a.tio_reference_number IS NULL OR CHAR_LENGTH(a.tio_reference_number) = 0)
									AND	srp.Id =	(
														SELECT		Id
														FROM		ServiceRatePlan
														WHERE		Service = s.id
																	AND NOW() BETWEEN srp.StartDatetime AND srp.EndDatetime
														ORDER BY	CreatedOn DESC
														LIMIT		1
													)
									AND (<customer_group_id> = -1 OR cg.Id = <customer_group_id>)
									AND a.CreatedOn < <date_cutoff>";
$arrDataReport['SQLGroupBy']	= "	a.Id
									
						HAVING		COUNT(
										CASE
											WHEN s.ServiceType = 100 THEN s.Id
											WHEN fcs.invoiced_cdr_count > 0 THEN s.Id
											WHEN fcs.has_unbilled_cdrs = 1 THEN s.Id
											ELSE NULL
										END
									) > 0 /* This is the same as `Total Tolling Services`, but there are issues with using that alias in Data Reports */
						
						ORDER BY	cg.Id, a.Id ASC";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']							['Value']	= "a.Id";

$arrSQLSelect['Account Name']						['Value']	= "a.BusinessName";

$arrSQLSelect['Contact Name']						['Value']	= "CONCAT(c.FirstName, ' ', c.LastName)";

$arrSQLSelect['Contact Number']						['Value']	= "IF(CAST(c.Phone AS UNSIGNED) > 0, c.Phone, c.Mobile)";
$arrSQLSelect['Contact Number']						['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Contact Email']						['Value']	= "c.Email";

$arrSQLSelect['Customer Group']						['Value']	= "cg.external_name";

$arrSQLSelect['Total Tolling Services']				['Value']	= "	COUNT(
																		CASE
																			WHEN s.ServiceType = 100 THEN s.Id
																			WHEN fcs.invoiced_cdr_count > 0 THEN s.Id
																			WHEN fcs.has_unbilled_cdrs = 1 THEN s.Id
																			ELSE NULL
																		END
																	)";
$arrSQLSelect['Total Tolling Services']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Mobile']								['Value']	= "COUNT(IF(s.ServiceType = 101 AND rp.Description NOT LIKE '%wireless%' AND (fcs.invoiced_cdr_count > 0 OR fcs.has_unbilled_cdrs = 1), s.Id, NULL))";
$arrSQLSelect['Mobile']								['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Inbound']							['Value']	= "COUNT(IF(s.ServiceType = 103 AND (fcs.invoiced_cdr_count > 0 OR fcs.has_unbilled_cdrs = 1), s.Id, NULL))";
$arrSQLSelect['Inbound']							['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['ADSL']								['Value']	= "COUNT(IF(s.ServiceType = 100, s.Id, NULL))";
$arrSQLSelect['ADSL']								['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Residential Land Line']				['Value']	= "COUNT(IF(s.ServiceType = 102 AND rp.Name LIKE '%home%free%19%' AND (fcs.invoiced_cdr_count > 0 OR fcs.has_unbilled_cdrs = 1), s.Id, NULL))";
$arrSQLSelect['Residential Land Line']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Wireless']							['Value']	= "COUNT(IF(s.ServiceType = 101 AND rp.Description LIKE '%wireless%' AND (fcs.invoiced_cdr_count > 0 OR fcs.has_unbilled_cdrs = 1), s.Id, NULL))";
$arrSQLSelect['Wireless']							['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

$arrInvoiceRunQuery =	array
						(
							'Query'			=> "		SELECT		cg.Id						AS `Value`,
																	cg.external_name			AS `Label`
														FROM		CustomerGroup cg
														WHERE		1
													
													UNION
														SELECT		-1							AS `Value`,
																	'[ All Customer Groups ]'	AS `Label`
													
													ORDER BY	(Value IS NULL) DESC, Value ASC",
							'ValueType'		=> "dataInteger"
						);

$arrSQLFields['customer_group_id']	= Array(
											'Type'					=> "Query",
											'DBQuery'				=> $arrInvoiceRunQuery,
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Customer Group",
										);

$arrSQLFields['date_cutoff']	= Array(
											'Type'					=> "dataDate",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Account Creation Cutoff Date",
											);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


?>