<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// REPORT_SOURCE
//----------------------------------------------------------------------------//
/**
 * REPORT_SOURCE
 *
 * Data Report Source
 *
 * Data Report Source
 *
 * @file		report_source.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */



//----------------------------------------------------------------------------//
// PROFIT REPORT
//----------------------------------------------------------------------------//

$arrDataReport	= Array();
$arrDocReqs		= Array();
$arrSQLSelect	= Array();
$arrSQLFields	= Array();


// General Data
$arrDataReport['Name']			= "Current Services and Accounts";
$arrDataReport['Summary']		= "Lists all Services and the Accounts they belong to.  Only displays the most recent version of an FNN (ie. Change of Lessees only appear once)";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Service";
$arrDataReport['SQLWhere']		= "Id = (SELECT Id FROM Service S2 WHERE FNN = Service.FNN ORDER BY ISNULL(ClosedOn))";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']	['Value']	= "Invoice.Account";

$arrSQLSelect['Customer Group']	['Value']	=	"CASE " .
												"WHEN Account.CustomerGroup = 2 THEN 'VoiceTalk' " .
												"ELSE 'Telco Blue' " .
												"END";

$arrSQLSelect['Customer Name']	['Value']	= "Account.BusinessName";

$strNLDTypes = "2, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 18, 19, 20, 27, 28, 33, 35, 36, 38";
$arrSQLSelect['Cost NLD']		['Value']	=	"SUM(CASE " .
												"WHEN ServiceTypeTotal.RecordType IN ($strNLDTypes) THEN ServiceTypeTotal.Cost " .
												"ELSE 0 " .
												"END)";
$arrSQLSelect['Cost NLD']		['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Cost NLD']		['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Charge NLD']		['Value']	=	"SUM(CASE " .
												"WHEN ServiceTypeTotal.RecordType IN ($strNLDTypes) THEN ServiceTypeTotal.Charge " .
												"ELSE 0 " .
												"END)";
$arrSQLSelect['Charge NLD']		['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Charge NLD']		['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Bill Cost']		['Value']	= "SUM(ServiceTypeTotal.Cost)";
$arrSQLSelect['Bill Cost']		['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Bill Cost']		['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Bill Charge']	['Value']	= "Invoice.Total";
$arrSQLSelect['Bill Charge']	['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Bill Charge']	['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Margin']			['Value']		= "NULL";
$arrSQLSelect['Margin']			['Function']	= "=IF(<Bill Charge>=0; 0; (<Bill Charge> - <Bill Cost>) / ABS(<Bill Charge>))";
$arrSQLSelect['Margin']			['Type']		= EXCEL_TYPE_PERCENTAGE;
$arrSQLSelect['Margin']			['Total']		= "=IF(<Bill Charge>=0; 0; (<Bill Charge> - <Bill Cost>) / ABS(<Bill Charge>))";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = array();
$arrColumns['Label']	= "CONCAT(DATE_FORMAT(BillingDate, '%d %M %Y'), ': ', cg.internal_name)";
$arrColumns['Value']	= "ir.InvoiceRun";

$arrSelect = array();
$arrSelect['Table']		= "InvoiceRun ir JOIN invoice_run_type irt ON (ir.invoice_run_type_id = itr.id) JOIN CustomerGroup cg ON (cg.Id = ir.customer_group_id)";
$arrSelect['Columns']	= $arrColumns;
$arrSelect['Where']		= "ir.BillingDate > '2007-03-01' AND irt.const_name = 'INVOICE_RUN_TYPE_LIVE'";
$arrSelect['OrderBy']	= "ir.BillingDate DESC";
$arrSelect['Limit']		= NULL;
$arrSelect['GroupBy']	= NULL;
$arrSelect['ValueType']	= "dataString";

$arrSQLFields['InvoiceRun']	= Array(
										'Type'					=> "StatementSelect",
										'DBSelect'				=> $arrSelect,
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "BillingDate",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);



 //---------------------------------------------------------------------------//
 // CURRENTLY BARRED SERVICES
 //---------------------------------------------------------------------------//

 $arrDataReport['Name']			= "Currently Barred Services";
$arrDataReport['Summary']		= "Lists all of the services which are currently barred";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(Request JOIN Service ON Service.Id = Request.Service) JOIN Account ON Account.Id = Service.Account";
$arrDataReport['SQLWhere']		= "Request.Status = 301 AND RequestType IN (902, 908) AND Request.Id = (SELECT R2.Id FROM Request R2 WHERE R2.RequestType IN (902, 903, 908, 909) AND R2.Service = Request.Service ORDER BY DATE_FORMAT(R2.RequestDatetime, '%Y-%m-%d') DESC, R2.RequestType DESC LIMIT 1) ORDER BY 'Barring Request Date' DESC, 'Customer Name', 'Soft/Hard Barred'";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Service.Account";

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Service FNN']			['Value']	= "Service.FNN";

$arrSQLSelect['Barring Request Date']	['Value']	= "DATE_FORMAT(RequestDatetime, '%Y-%m-%d')";

/*$arrSQLSelect['Carrier']	['Value']				=	"CASE" .
														" WHEN Request.Carrier = 1 THEN 'Unitel'" .
														" WHEN Request.Carrier = 2 THEN 'Optus'" .
														" WHEN Request.Carrier = 3 THEN 'AAPT'" .
														" WHEN Request.Carrier = 4 THEN 'iSeek' " .
														"END";*/

$arrSQLSelect['Soft/Hard Barred']		['Value']	=	"CASE" .
														" WHEN Request.RequestType = 902 THEN 'Soft Bar' " .
														" WHEN Request.RequestType = 908 THEN 'Hard Bar' " .
														"END";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


 //---------------------------------------------------------------------------//
 // LOST SERVICES
 //---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Lost Services in a Date Period";
$arrDataReport['Summary']		= "Lists all of the services which were lost in the specified period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= 	"(" .
										"(" .
											"(" .
												"(" .
													"Service LEFT JOIN ProvisioningResponse PR ON Service.Account = PR.Account" .
												") " .
												"LEFT JOIN Account ON Account.Id = Service.Account" .
											") " .
											"LEFT JOIN Contact ON Account.PrimaryContact = Contact.Id" .
										") " .
										"LEFT JOIN ServiceRatePlan SRP ON PR.Service = SRP.Service" .
									") " .
									"LEFT JOIN RatePlan ON SRP.RatePlan = RatePlan.Id";

$arrDataReport['SQLWhere']		= "PR.ImportedOn BETWEEN <StartDate> AND ADDDATE(<EndDate>, INTERVAL 1 DAY) \n" .
								"AND PR.Status = ".RESPONSE_STATUS_IMPORTED." AND PR.Request IS NULL \n" .
								"AND PR.Type IN (".REQUEST_LOSS_PRESELECT.", ".REQUEST_LOSS_FULL.") \n" .
								"AND SRP.Id = (SELECT ServiceRatePlan.Id FROM ServiceRatePlan WHERE ServiceRatePlan.Service = PR.Service AND PR.ImportedOn BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime ORDER BY ServiceRatePlan.CreatedOn DESC LIMIT 1) \n" .
								"AND ((PR.Type = ".REQUEST_LOSS_PRESELECT." AND PR.Id = (SELECT Id FROM ProvisioningResponse PR2 WHERE PR2.Service = PR.Service AND PR2.Type IN (".REQUEST_LOSS_PRESELECT.", ".REQUEST_PRESELECTION.") ORDER BY EffectiveDate DESC LIMIT 1)) " .
								"OR (PR.Type = ".REQUEST_LOSS_FULL." AND PR.Id = (SELECT Id FROM ProvisioningResponse PR2 WHERE PR2.Service = PR.Service AND PR2.Type IN (".REQUEST_LOSS_FULL.", ".REQUEST_FULL_SERVICE.") ORDER BY EffectiveDate DESC LIMIT 1)))";
$arrDataReport['SQLGroupBy']	= "PR.Service ORDER BY Account.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Account.Id";

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Primary Contact']		['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Contact Phone']			['Value']	= "Contact.Phone";
$arrSQLSelect['Contact Phone']			['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service FNN']		['Value']	= "PR.FNN";
$arrSQLSelect['Lost Service FNN']		['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service Plan']		['Value']	= "RatePlan.Name";

$arrSQLSelect['Full Service Loss Date']	['Value']	= "MAX(CASE WHEN PR.Type = ".REQUEST_LOSS_FULL." THEN DATE_FORMAT(PR.EffectiveDate, '%d/%m/%Y') ELSE NULL END)";

$arrSQLSelect['Preselection Loss Date']	['Value']	= "MAX(CASE WHEN PR.Type = ".REQUEST_LOSS_PRESELECT." THEN DATE_FORMAT(PR.EffectiveDate, '%d/%m/%Y') ELSE NULL END)";

$arrSQLSelect['Loss Details']			['Value']	= "PR.Description";

$arrSQLSelect['Active Services']		['Value']	= "COUNT(DISTINCT CASE WHEN Service.ClosedOn IS NULL THEN Service.Id ELSE NULL END)";
$arrSQLSelect['Active Services']		['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


 //---------------------------------------------------------------------------//
 // CDRS APPLIED TO ARCHIVED ACCOUNTS
 //---------------------------------------------------------------------------//

$arrDataReport['Name']			= "CDR Totals Applied to Archived Accounts";
$arrDataReport['Summary']		= "Lists all Accounts which have CDRs debited against them, despite being Archived, for a specified period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "CDR JOIN Account ON Account.Id = CDR.Account";
$arrDataReport['SQLWhere']		= "Account.Archived = 1 AND CDR.Status = 150 AND CDR.Credit != 1 " .
									"AND CDR.StartDatetime BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "CDR.Account";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Account.Id";

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Total CDRs']				['Value']	= "COUNT(CDR.Id)";
$arrSQLSelect['Total CDRs']				['Type']	= EXCEL_TYPE_INTEGER;
$arrSQLSelect['Total CDRs']				['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Total Cost']				['Value']	= "SUM(CDR.Cost)";
$arrSQLSelect['Total Cost']				['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Total Cost']				['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Total Charge']			['Value']	= "SUM(CDR.Charge)";
$arrSQLSelect['Total Charge']			['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Total Charge']			['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Earliest CDR']			['Value']	= "MIN(CDR.StartDatetime)";

$arrSQLSelect['Latest CDR']				['Value']	= "MAX(CDR.EndDatetime)";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


 //---------------------------------------------------------------------------//
 //  LOST SERVICES BY ACCOUNT
 //---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Lost Services (By Account) in a Date Period";
$arrDataReport['Summary']		= "Lists all of the Services which were lost in the specified period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
//$arrDataReport['SQLTable']		= "(ProvisioningResponse PR JOIN Account ON PR.Account = Account.Id) JOIN Service ON Service.Account = PR.Account";
$arrDataReport['SQLTable']		= "((Service LEFT JOIN ProvisioningResponse PR ON Service.Account = PR.Account) JOIN Account ON Account.Id = Service.Account) LEFT JOIN Contact ON Account.PrimaryContact = Contact.Id";
$arrDataReport['SQLWhere']		= "PR.Id IS NULL OR (PR.ImportedOn BETWEEN <StartDate> AND ADDDATE(<EndDate>, INTERVAL 1 DAY) " .
								"AND PR.Status = ".RESPONSE_STATUS_IMPORTED." AND PR.Request IS NULL " .
								"AND PR.Type IN (".REQUEST_LOSS_PRESELECT.", ".REQUEST_LOSS_FULL."))";
$arrDataReport['SQLGroupBy']	= "Service.Account HAVING (`Full Services Lost` > 0 OR `Preselections Lost` > 0)";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Account.Id";

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Primary Contact']		['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Phone']					['Value']	= "Contact.Phone";
$arrSQLSelect['Phone']					['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Full Services Lost']		['Value']	= "COUNT(DISTINCT CASE WHEN PR.Type = ".REQUEST_LOSS_FULL." THEN PR.Service ELSE NULL END)";
$arrSQLSelect['Full Services Lost']		['Type']	= EXCEL_TYPE_INTEGER;
$arrSQLSelect['Full Services Lost']		['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Preselections Lost']		['Value']	= "COUNT(DISTINCT CASE WHEN PR.Type = ".REQUEST_LOSS_PRESELECT." THEN PR.Service ELSE NULL END)";
$arrSQLSelect['Preselections Lost']		['Type']	= EXCEL_TYPE_INTEGER;
$arrSQLSelect['Preselections Lost']		['Total']	= EXCEL_TOTAL_SUM;

$arrSQLSelect['Active Services']		['Value']	= "COUNT(DISTINCT CASE WHEN Service.ClosedOn IS NULL THEN Service.Id ELSE NULL END)";
$arrSQLSelect['Active Services']		['Type']	= EXCEL_TYPE_INTEGER;
$arrSQLSelect['Active Services']		['Total']	= EXCEL_TOTAL_SUM;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


 //---------------------------------------------------------------------------//
 //  LOST SERVICES 2
 //---------------------------------------------------------------------------//


$arrDataReport['Name']			= "Lost Services in a Date Period";
$arrDataReport['Summary']		= "Lists all of the services which were lost in the specified period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= 	"(" .
										"(" .
											"(" .
												"(" .
													"Service LEFT JOIN ProvisioningResponse PR ON Service.Account = PR.Account" .
												") " .
												"LEFT JOIN Account ON Account.Id = Service.Account" .
											") " .
											"LEFT JOIN Contact ON Account.PrimaryContact = Contact.Id" .
										") " .
										"LEFT JOIN ServiceRatePlan SRP ON PR.Service = SRP.Service" .
									") " .
									"LEFT JOIN RatePlan ON SRP.RatePlan = RatePlan.Id";

$arrDataReport['SQLWhere']		= "PR.ImportedOn BETWEEN <StartDate> AND ADDDATE(<EndDate>, INTERVAL 1 DAY) \n" .
								"AND PR.Status = ".RESPONSE_STATUS_IMPORTED." AND PR.Request IS NULL \n" .
								"AND PR.Type IN (".REQUEST_LOSS_PRESELECT.", ".REQUEST_LOSS_FULL.") \n" .
								"AND SRP.Id = (SELECT ServiceRatePlan.Id FROM ServiceRatePlan WHERE ServiceRatePlan.Service = PR.Service AND PR.ImportedOn BETWEEN ServiceRatePlan.StartDatetime AND ServiceRatePlan.EndDatetime ORDER BY ServiceRatePlan.CreatedOn DESC LIMIT 1) \n" .
								"AND ((PR.Type = ".REQUEST_LOSS_PRESELECT." AND PR.Id = (SELECT Id FROM ProvisioningResponse PR2 WHERE PR2.Service = PR.Service AND PR2.Type IN (".REQUEST_LOSS_PRESELECT.", ".REQUEST_PRESELECTION.") ORDER BY EffectiveDate DESC LIMIT 1)) " .
								"OR (PR.Type = ".REQUEST_LOSS_FULL." AND PR.Id = (SELECT Id FROM ProvisioningResponse PR2 WHERE PR2.Service = PR.Service AND PR2.Type IN (".REQUEST_LOSS_FULL.", ".REQUEST_FULL_SERVICE.") ORDER BY EffectiveDate DESC LIMIT 1)))";
$arrDataReport['SQLGroupBy']	= "PR.Service ORDER BY Account.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Account.Id";

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Primary Contact']		['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Contact Phone']			['Value']	= "Contact.Phone";
$arrSQLSelect['Contact Phone']			['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service FNN']		['Value']	= "PR.FNN";
$arrSQLSelect['Lost Service FNN']		['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service Plan']		['Value']	= "RatePlan.Name";

$arrSQLSelect['Full Service Loss Date']	['Value']	= "MAX(CASE WHEN PR.Type = ".REQUEST_LOSS_FULL." THEN DATE_FORMAT(PR.EffectiveDate, '%d/%m/%Y') ELSE NULL END)";

$arrSQLSelect['Preselection Loss Date']	['Value']	= "MAX(CASE WHEN PR.Type = ".REQUEST_LOSS_PRESELECT." THEN DATE_FORMAT(PR.EffectiveDate, '%d/%m/%Y') ELSE NULL END)";

$arrSQLSelect['Loss Details']			['Value']	= "PR.Description";

$arrSQLSelect['Active Services']		['Value']	= "COUNT(DISTINCT CASE WHEN Service.ClosedOn IS NULL THEN Service.Id ELSE NULL END)";
$arrSQLSelect['Active Services']		['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

 //---------------------------------------------------------------------------//
 //  WRITTEN OFF INVOICES
 //---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Written-off Invoices in a Date Period";
$arrDataReport['Summary']		= "Lists all of the Invoices which were written off in the specified period, ordered by Account";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Invoice JOIN Account ON Invoice.Account = Account.Id";

$arrDataReport['SQLWhere']		= "SettledOn BETWEEN <StartDate> AND <EndDate> AND Invoice.Status = ".INVOICE_WRITTEN_OFF." \n ORDER BY Account, InvoiceRun";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Account.Id";
$arrSQLSelect['Account No.']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Invoice No.']			['Value']	= "Invoice.Id";
$arrSQLSelect['Invoice No.']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Billing Date']			['Value']	= "Invoice.CreatedOn";

$arrSQLSelect['Write-Off Date']			['Value']	= "Invoice.SettledOn";

$arrSQLSelect['Value Written Off']		['Value']	= "Invoice.Balance";
$arrSQLSelect['Value Written Off']		['Type']	= EXCEL_TYPE_CURRENCY;
$arrSQLSelect['Value Written Off']		['Total']	= EXCEL_TOTAL_SUM;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// Credit Card Report
//----------------------------------------------------------------------------//

$strStartDate	= date("Y-m-01", time());

$arrDataReport = Array();
$arrDataReport['Name']			= "Credit Card Payments Report";
//$arrDataReport['Name']			= "Credit Card Payments Report (Hack)";
$arrDataReport['FileName']		= "SAE0009";
$arrDataReport['Summary']		= "Details Credit Card information for use in automated payments";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['RenderTarget']	= REPORT_TARGET_CSV;
$arrDataReport['Priviledges']	= 1;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "CreditCard JOIN Account ON (Account.CreditCard = CreditCard.Id) JOIN Invoice ON (Account.Id = Invoice.Account)";
//$arrDataReport['SQLWhere']		= "CreditCard.Archived = 0 AND Invoice.Balance > 0 AND Invoice.AccountBalance >= 0 AND Invoice.DueOn BETWEEN '$strStartDate' AND SUBDATE(ADDDATE('$strStartDate', INTERVAL 1 MONTH), INTERVAL 1 DAY)";
$arrDataReport['SQLWhere']		= "Account.Archived IN (0, 2) AND CreditCard.Archived = 0 AND Account.BillingType = 2 AND Invoice.DueOn <= CURDATE()";
$arrDataReport['SQLGroupBy']	= "Invoice.Account\n HAVING SUM(Invoice.Balance) > 5";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['CC Number']			['Value']	= "CreditCard.CardNumber";
$arrSQLSelect['Expiry Date']		['Value']	= "CONCAT(LPAD(CreditCard.ExpMonth, 2, '00'), '/', LPAD(SUBSTR(LPAD(CreditCard.ExpYear, 4, '2000'), -2), 2, '0'))";
//$arrSQLSelect['Amount Charged']	['Value']		= "Invoice.Balance";
$arrSQLSelect['Amount Charged']		['Value']	= "CAST(ROUND(SUM(Invoice.Balance * 100)) AS SIGNED)";
$arrSQLSelect['Account Number']		['Value']	= "Account.Id";
$arrSQLSelect['Customer Name']		['Value']	=	"LEFT(" .
													"	REPLACE(" .
													"		REPLACE(" .
													"			REPLACE(" .
													"				REPLACE(" .
													"					REPLACE(" .
													"						REPLACE(" .
													//"							REPLACE(" .
													"								REPLACE(Account.BusinessName, ',', '')" .
													//"							, ' ', '')" .
													"						, ')', '')" .
													"					, '(', '')" .
													"				, '\/', '')" .
													"			, '\\\', '')" .
													"		, '\"', '')" .
													"	, '\\'', '')" .
													", 32)";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

$arrPostSelectProcess = array();
$arrPostSelectProcess['CC Number'] = "DecryptAndStripSpaces";
$arrDateReport['PostSelectProcess'] = serialize($arrPostSelectProcess);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

// Overrides
$arrOverrides = Array();
$arrOverrides['Enclose']	= "";
$arrOverrides['Delimiter']	= ",";
$arrOverrides['NoTitles']	= TRUE;
$arrOverrides['Extension']	= "txt";
$arrDataReport['Overrides'] = serialize($arrOverrides);

//----------------------------------------------------------------------------//
// Direct Debit Report
//----------------------------------------------------------------------------//

$strStartDate	= date("Y-m-01", time());
$arrDataReport = Array();
$arrDataReport['Name']			= "Direct Debit Payments Report";
//$arrDataReport['Name']			= "Direct Debit Payments Report (Hack)";
$arrDataReport['FileName']		= "SAE00";
$arrDataReport['Summary']		= "Details Direct Debit information for use in automated payments";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['RenderTarget']	= REPORT_TARGET_CSV;
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['Priviledges']	= 2147483648;									// Debug
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "DirectDebit JOIN Account ON (Account.DirectDebit = DirectDebit.Id) JOIN Invoice ON (Account.Id = Invoice.Account)";
//$arrDataReport['SQLWhere']		= "DirectDebit.Archived = 0 AND Invoice.Balance > 0 AND Invoice.AccountBalance >= 0 AND Invoice.DueOn BETWEEN '$strStartDate' AND SUBDATE(ADDDATE('$strStartDate', INTERVAL 1 MONTH), INTERVAL 1 DAY)";
$arrDataReport['SQLWhere']		= "Account.Archived IN (0, 2) AND DirectDebit.Archived = 0 AND Account.BillingType = 1 AND Invoice.DueOn <= CURDATE()";
$arrDataReport['SQLGroupBy']	= "Invoice.Account\n HAVING SUM(Invoice.Balance) > 5";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['BSB']					['Value']	= "LPAD(DirectDebit.BSB, 6, '0')";
$arrSQLSelect['Bank Account Number']	['Value']	= "DirectDebit.AccountNumber";
$arrSQLSelect['Account Name']			['Value']	= "LEFT(" .
														"	REPLACE(" .
														"		REPLACE(" .
														"			REPLACE(" .
														"				REPLACE(" .
														"					REPLACE(" .
														"						REPLACE(" .
														//"							REPLACE(" .
														"								REPLACE(DirectDebit.AccountName, ',', '')" .
														//"							, ' ', '')" .
														"						, ')', '')" .
														"					, '(', '')" .
														"				, '\/', '')" .
														"			, '\\\', '')" .
														"		, '\"', '')" .
														"	, '\\'', '')" .
														", 32)";
$arrSQLSelect['Amount Charged']			['Value']	= "CAST(ROUND(SUM(Invoice.Balance * 100)) AS SIGNED)";
$arrSQLSelect['Account Number']			['Value']	= "Account.Id";
$arrSQLSelect['Customer Name']			['Value']	=	"LEFT(" .
														"	REPLACE(" .
														"		REPLACE(" .
														"			REPLACE(" .
														"				REPLACE(" .
														"					REPLACE(" .
														"						REPLACE(" .
														//"							REPLACE(" .
														"								REPLACE(Account.BusinessName, ',', '')" .
														//"							, ' ', '')" .
														"						, ')', '')" .
														"					, '(', '')" .
														"				, '\/', '')" .
														"			, '\\\', '')" .
														"		, '\"', '')" .
														"	, '\\'', '')" .
														", 32)";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

// Overrides
$arrOverrides = Array();
$arrOverrides['Enclose']	= "";
$arrOverrides['Delimiter']	= ",";
$arrOverrides['NoTitles']	= TRUE;
$arrOverrides['Extension']	= "txt";
$arrDataReport['Overrides'] = serialize($arrOverrides);


//----------------------------------------------------------------------------//
// Itemised CDRs in a Date Period for a given Service & Account Report
//----------------------------------------------------------------------------//

$arrDataReport['Name']			= "CDRs for an Account and Call Type in a Date Range";
$arrDataReport['Summary']		= "Lists all CDRs for an Account and Call Type in a specified Date Range";
$arrDataReport['FileName']		= "CDRs for <FNN> (<Account>) for <RecordType::Label> between <StartDate> AND <EndDate>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(CDR JOIN RecordType ON RecordType.Id = CDR.RecordType) LEFT JOIN InvoiceRun ON InvoiceRun.InvoiceRun = CDR.InvoiceRun";
$arrDataReport['SQLWhere']		= "FNN LIKE <FNN> AND StartDatetime BETWEEN <StartDate> AND <EndDate> AND CAST(Account AS CHAR) LIKE <Account> AND RecordType.GroupId = <RecordType> \n ORDER BY CDR.FNN, CDR.StartDatetime";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Account";
$arrDocReq[]	= "Service";
$arrDocReq[]	= "Record Type";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
/*$arrSQLSelect['Service']		['Value']	= "CDR.FNN";
$arrSQLSelect['Service']		['Type']	= EXCEL_TYPE_FNN;*/

$arrSQLSelect['Date']			['Value']	= "DATE_FORMAT(StartDatetime, '%d/%m/%Y')";

$arrSQLSelect['Time']			['Value']	= "DATE_FORMAT(StartDatetime, '%H:%i:%s')";

$arrSQLSelect['Called Party']	['Value']	= "Destination";
$arrSQLSelect['Called Party']	['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Description']	['Value']	= "CDR.Description";

$arrSQLSelect['Duration']		['Value']	= 	"CASE" .
												"	WHEN DisplayType = 1 THEN SEC_TO_TIME(CDR.Units)" .
												"	WHEN DisplayType = 3 THEN CONCAT(CAST(CDR.Units AS CHAR), 'KB')" .
												"	ELSE CAST(CDR.Units AS CHAR)" .
												"END";

$arrSQLSelect['Charge']			['Value']	= "CDR.Charge";
$arrSQLSelect['Charge']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Invoice Date']	['Value']	=	"CASE" .
												"	WHEN CDR.InvoiceRun IS NULL THEN 'N/A'" .
												"	ELSE DATE_FORMAT(InvoiceRun.BillingDate, '%d/%m/%Y')" .
												"END";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
$arrColumns['Value']	= "Id";
$arrColumns['Label']	=	"CONCAT(CASE" .
							"			WHEN ServiceType = 100 THEN 'ADSL : '" .
							"			WHEN ServiceType = 101 THEN 'Mobile : '" .
							"			WHEN ServiceType = 102 THEN 'Landline : '" .
							"			WHEN ServiceType = 103 THEN 'Inbound : '" .
							"			ELSE NULL" .
							"		END, Description)";

$arrSelect = Array();
$arrSelect['Table']		= "RecordType";
$arrSelect['Columns']	= $arrColumns;
$arrSelect['Where']		= "Id = GroupId AND Code != 'DELETED'";
$arrSelect['OrderBy']	= "ServiceType, Description";
$arrSelect['Limit']		= NULL;
$arrSelect['GroupBy']	= "GroupId";
$arrSelect['ValueType']	= "dataInteger";

$arrSQLFields['Account']	= Array(
										'Type'					=> "dataString",
										'Documentation-Entity'	=> "Account",
										'Documentation-Field'	=> "Id",
									);

$arrSQLFields['FNN']		= Array(
										'Type'					=> "dataString",
										'Documentation-Entity'	=> "Service",
										'Documentation-Field'	=> "FNN",
									);
$arrSQLFields['RecordType']	= Array(
										'Type'					=> "StatementSelect",
										'DBSelect'				=> $arrSelect,
										'Documentation-Entity'	=> "Record Type",
										'Documentation-Field'	=> "RecordType",
									);
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


//----------------------------------------------------------------------------//
// Active Inbound Numbers for a Billing Month
//----------------------------------------------------------------------------//
// General Data
$arrDataReport['Name']			= "Active Inbound Numbers for a Billing Month";
$arrDataReport['Summary']		= "Lists all active Inbound (13, 1800...) numbers for a Billing Month";
$arrDataReport['FileName']		= "Active Inbound Numbers for Bill from <InvoiceRun::Label>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Charge JOIN Service ON (Charge.Service = Service.Id)";
$arrDataReport['SQLWhere']		= "InvoiceRun = <InvoiceRun> AND ChargeType = 'INB15' \n ORDER BY Service.Account, FNN";

$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No']		['Value']	= "Service.Account";
$arrSQLSelect['Account No']		['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['FNN']			['Value']	= "FNN";
$arrSQLSelect['FNN']			['Type']	= EXCEL_TYPE_FNN;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
$arrColumns['Label']	= "DATE_FORMAT(BillingDate, '%d %M %Y')";
$arrColumns['Value']	= "InvoiceRun";

$arrSelect = Array();
$arrSelect['Table']		= "InvoiceRun";
$arrSelect['Columns']	= $arrColumns;
$arrSelect['Where']		= "BillingDate > '2007-03-01'";
$arrSelect['OrderBy']	= "BillingDate DESC";
$arrSelect['Limit']		= NULL;
$arrSelect['GroupBy']	= NULL;
$arrSelect['ValueType']	= "dataString";

$arrSQLFields['InvoiceRun']	= Array(
										'Type'					=> "StatementSelect",
										'DBSelect'				=> $arrSelect,
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "BillingDate",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


//----------------------------------------------------------------------------//
// Active Services
//----------------------------------------------------------------------------//
// General Data
$arrDataReport['Name']			= "Show Services";
$arrDataReport['Summary']		= "Lists all Services and the Accounts they belong to.  Only displays the most recent version of an FNN (ie. Change of Lessees only appear once)";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 1;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Service";
$arrDataReport['SQLWhere']		= "Id = (SELECT Id FROM Service S2 WHERE FNN = Service.FNN ORDER BY ISNULL(ClosedOn), CreatedOn DESC LIMIT 1) ORDER BY Account";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']	['Value']	= "Account";
$arrSQLSelect['Account No.']	['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['FNN']			['Value']	= "FNN";
$arrSQLSelect['FNN']			['Type']	= EXCEL_TYPE_FNN;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);



//----------------------------------------------------------------------------//
// Percentage Collected for a Given Invoice Run
//----------------------------------------------------------------------------//
// General Data
$arrDataReport['Name']			= "Percentage Collected for a Given Invoice Run";
$arrDataReport['Summary']		= "Shows the Percentage collected to date for a specified Invoice Run";
$arrDataReport['FileName']		= "Percentage Collected from <InvoiceRun::Label> Invoice Run as of <DATETIME()>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Invoice";
$arrDataReport['SQLWhere']		= "InvoiceRun = <InvoiceRun>";
$arrDataReport['SQLGroupBy']	= "InvoiceRun";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Invoice Grand Total']	['Value']	= "SUM(CASE WHEN Total+Tax > 0 THEN Total+Tax ELSE 0 END)";
$arrSQLSelect['Invoice Grand Total']	['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total Collected']		['Value']	= "SUM(CASE WHEN Total+Tax > 0 THEN Total+Tax ELSE 0 END) - SUM(CASE WHEN Total+Tax > 0 THEN Balance ELSE 0 END)";
$arrSQLSelect['Total Collected']		['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total Outstanding']		['Value']	= "SUM(CASE WHEN Total+Tax >= 0 THEN Balance ELSE 0 END)";
$arrSQLSelect['Total Outstanding']		['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Percent Collected']		['Value']	= "1 - (SUM(CASE WHEN Total+Tax > 0 THEN Balance ELSE 0 END) / SUM(CASE WHEN Total+Tax > 0 THEN Total+Tax ELSE 0 END))";
$arrSQLSelect['Percent Collected']		['Type']	= EXCEL_TYPE_PERCENTAGE;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrColumns = Array();
$arrColumns['Label']	= "DATE_FORMAT(BillingDate, '%d %M %Y')";
$arrColumns['Value']	= "InvoiceRun";

$arrSelect = Array();
$arrSelect['Table']		= "InvoiceRun";
$arrSelect['Columns']	= $arrColumns;
$arrSelect['Where']		= "BillingDate > '2007-03-01'";
$arrSelect['OrderBy']	= "BillingDate DESC";
$arrSelect['Limit']		= NULL;
$arrSelect['GroupBy']	= NULL;
$arrSelect['ValueType']	= "dataString";

$arrSQLFields['InvoiceRun']	= Array(
										'Type'					=> "StatementSelect",
										'DBSelect'				=> $arrSelect,
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "BillingDate",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


//----------------------------------------------------------------------------//
// Duplicate Unbilled CDR Files in a given Period
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Duplicate Unbilled CDR Files in a given Period";
$arrDataReport['Summary']		= "Displays a list of CDR Files which have duplicate unbilled CDRs in them.";
$arrDataReport['FileName']		= "Duplicate Unbilled CDR Files between <StartDate> AND <EndDate>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(CDR JOIN FileImport ON CDR.File = FileImport.Id) JOIN Carrier ON Carrier.Id = FileImport.Carrier";
$arrDataReport['SQLWhere']		= "CDR.StartDatetime BETWEEN <StartDate> AND <EndDate> AND CDR.Status = ".CDR_DUPLICATE;
$arrDataReport['SQLGroupBy']	= "CDR.File";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['File Name']				['Value']	= "FileImport.FileName";

$arrSQLSelect['Carrier']				['Value']	= "Carrier.Name";

$arrSQLSelect['Duplicate CDRs']			['Value']	= "COUNT(CDR.Id)";
$arrSQLSelect['Duplicate CDRs']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Total CDR Cost']			['Value']	= "SUM(CDR.Cost)";
$arrSQLSelect['Total CDR Cost']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Earliest CDR Date']		['Value']	= "DATE_FORMAT(MIN(CDR.StartDatetime), '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Latest CDR Date']		['Value']	= "DATE_FORMAT(MAX(CDR.StartDatetime), '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Import Date']			['Value']	= "DATE_FORMAT(FileImport.ImportedOn, '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['YBS File Reference']		['Value']	= "FileImport.Id";
$arrSQLSelect['YBS File Reference']		['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


//----------------------------------------------------------------------------//
// Contract Cancellation Fees
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Contract Cancellation Fees in a Time Period";
$arrDataReport['Summary']		= "Displays a list of Contract Cancellation Fees for a specified period.";
$arrDataReport['FileName']		= "Contract Cancellation Fees between <StartDate> AND <EndDate>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Charge";
$arrDataReport['SQLWhere']		= "ChargeType IN ('DSLCAN', 'CONT', 'EARL') AND CreatedOn BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']				['Value']	= "Account";
$arrSQLSelect['Account']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Date Created']			['Value']	= "DATE_FORMAT(CreatedOn, '%d/%m/%Y')";

$arrSQLSelect['Description']			['Value']	= "Description";

$arrSQLSelect['Date Charged']			['Value']	= "DATE_FORMAT(ChargedOn, '%d/%m/%Y')";

$arrSQLSelect['Amount']					['Value']	= "CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END";
$arrSQLSelect['Amount']					['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Notes']					['Value']	= "Notes";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// Administration Fees
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Administration Fees in a Time Period";
$arrDataReport['Summary']		= "Displays a list of Administration Fees for a specified period.";
$arrDataReport['FileName']		= "Administration Fees between <StartDate> AND <EndDate>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Charge";
$arrDataReport['SQLWhere']		= "ChargeType IN ('ADMF') AND CreatedOn BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']				['Value']	= "Account";
$arrSQLSelect['Account']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Date Created']			['Value']	= "DATE_FORMAT(CreatedOn, '%d/%m/%Y')";

$arrSQLSelect['Description']			['Value']	= "Description";

$arrSQLSelect['Date Charged']			['Value']	= "DATE_FORMAT(ChargedOn, '%d/%m/%Y')";

$arrSQLSelect['Amount']					['Value']	= "CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END";
$arrSQLSelect['Amount']					['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Notes']					['Value']	= "Notes";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// Contract Cancellation Fees
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Contract Cancellation Fees in a Time Period";
$arrDataReport['Summary']		= "Displays a list of Contract Cancellation Fees for a specified period.";
$arrDataReport['FileName']		= "Contract Cancellation Fees between <StartDate> AND <EndDate>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Charge";
$arrDataReport['SQLWhere']		= "ChargeType IN ('DSLCAN', 'CONT', 'EARL') AND CreatedOn BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']				['Value']	= "Account";
$arrSQLSelect['Account']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Date Created']			['Value']	= "DATE_FORMAT(CreatedOn, '%d/%m/%Y')";

$arrSQLSelect['Description']			['Value']	= "Description";

$arrSQLSelect['Date Charged']			['Value']	= "DATE_FORMAT(ChargedOn, '%d/%m/%Y')";

$arrSQLSelect['Amount']					['Value']	= "CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END";
$arrSQLSelect['Amount']					['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Notes']					['Value']	= "Notes";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


//----------------------------------------------------------------------------//
// Bar Requests Sent in a Time Period
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Bar Requests Sent in a Time Period";
$arrDataReport['Summary']		= "Displays a list of Bar Requests sent in a specified time period.";
$arrDataReport['FileName']		= "Bar Requests Sent between <StartDate> AND <EndDate>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "ProvisioningRequest PR JOIN Carrier ON PR.Carrier = Carrier.Id";
$arrDataReport['SQLWhere']		= "Type = 902 AND CAST(SentOn AS DATE) BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']				['Value']	= "Account";
$arrSQLSelect['Account']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['FNN']					['Value']	= "FNN";
$arrSQLSelect['FNN']					['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Carrier']				['Value']	= "Carrier.Name";

$arrSQLSelect['Date Requested']			['Value']	= "DATE_FORMAT(RequestedOn, '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Date Sent']				['Value']	= "DATE_FORMAT(SentOn, '%d/%m/%Y %H:%i:%s')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// Payments to Debt Collection Accounts in a Time Period
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Payments to Debt Collection Accounts in a Time Period";
$arrDataReport['Summary']		= "Shows any Payments that have been applied to Debt Collection Accounts in the specified period.";
$arrDataReport['FileName']		= "Payments to Debt Collection Accounts between <StartDate> AND <EndDate>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(Payment JOIN Account ON Payment.Account = Account.Id) JOIN ConfigConstant ON (ConstantGroup = 18 AND Payment.PaymentType = ConfigConstant.Value)";
$arrDataReport['SQLWhere']		= "Payment.Status IN (101, 103, 150) AND Account.Archived = 3 AND PaidOn BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']				['Value']	= "Account";
$arrSQLSelect['Account']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Business Name']			['Value']	= "BusinessName";

$arrSQLSelect['Payment Type']			['Value']	= "ConfigConstant.Description";

$arrSQLSelect['Paid On']				['Value']	= "DATE_FORMAT(PaidOn, '%d/%m/%Y')";

$arrSQLSelect['Amount']					['Value']	= "Payment.Amount";
$arrSQLSelect['Amount']					['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Applied']				['Value']	= "Payment.Amount - Payment.Balance";
$arrSQLSelect['Applied']				['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Remaining']				['Value']	= "Payment.Balance";
$arrSQLSelect['Remaining']				['Type']	= EXCEL_TYPE_CURRENCY;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// Duplicate Unbilled CDR Files in a given Period
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Duplicate Unbilled CDR Files in a given Period";
$arrDataReport['Summary']		= "Displays a list of CDR Files which have duplicate unbilled CDRs in them.";
$arrDataReport['FileName']		= "Duplicate Unbilled CDR Files between <StartDate> AND <EndDate>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(CDR JOIN FileImport ON CDR.File = FileImport.Id) JOIN Carrier ON Carrier.Id = FileImport.Carrier";
$arrDataReport['SQLWhere']		= "CDR.StartDatetime BETWEEN <StartDate> AND <EndDate> AND CDR.Status = ".CDR_DUPLICATE;
$arrDataReport['SQLGroupBy']	= "CDR.File";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['File Name']				['Value']	= "FileImport.FileName";

$arrSQLSelect['Carrier']				['Value']	= "Carrier.Name";

$arrSQLSelect['Duplicate CDRs']			['Value']	= "COUNT(CDR.Id)";
$arrSQLSelect['Duplicate CDRs']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Total CDR Cost']			['Value']	= "SUM(CDR.Cost)";
$arrSQLSelect['Total CDR Cost']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Earliest CDR Date']		['Value']	= "DATE_FORMAT(MIN(CDR.StartDatetime), '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Latest CDR Date']		['Value']	= "DATE_FORMAT(MAX(CDR.StartDatetime), '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Import Date']			['Value']	= "DATE_FORMAT(FileImport.ImportedOn, '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['YBS File Reference']		['Value']	= "FileImport.Id";
$arrSQLSelect['YBS File Reference']		['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// Credit Cards Expiring Next Month
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Credit Cards Expiring Next Month";
$arrDataReport['Summary']		= "Displays a list of Accounts whose Credit Cards will Expire Next Month.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "CreditCard LEFT JOIN Account USING (AccountGroup)";
$arrDataReport['SQLWhere']		= "((<Active> = 1 AND Account.CreditCard = CreditCard.Id AND Account.BillingType = ".BILLING_TYPE_CREDIT_CARD.") OR (<Active> = 0)) AND CONCAT(LPAD(CAST(CAST(ExpYear AS UNSIGNED) AS CHAR), 4, '2000'), '-', LPAD(CAST(CAST(ExpMonth AS UNSIGNED) AS CHAR), 2, '0'), '-01') = ADDDATE(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "CreditCard";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account Group']			['Value']	= "DISTINCT Account.AccountGroup";

$arrSQLSelect['Account #']				['Value']	= "Account.Id";

$arrSQLSelect['Business Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Expiry']					['Value']	= "CONCAT(LPAD(CAST(CAST(ExpMonth AS UNSIGNED) AS CHAR), 2, '0'), '/', LPAD(CAST(CAST(ExpYear AS UNSIGNED) AS CHAR), 4, '2000'))";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrSQLFields['Active']	= Array(
										'Type'					=> "dataBoolean",
										'Documentation-Entity'	=> "CreditCard",
										'Documentation-Field'	=> "Active",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


//----------------------------------------------------------------------------//
// New Accounts Created after a Date
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "New Accounts Created after a Date";
$arrDataReport['Summary']		= "Displays a list of Accounts which were created on or after a specified date.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Account JOIN Contact ON Account.PrimaryContact = Contact.Id";
$arrDataReport['SQLWhere']		= "Account.CreatedOn >= <CreatedOn> AND Account.Archived = 0";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Account";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account #']				['Value']	= "Account.Id";
$arrSQLSelect['Account #']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Business Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Primary Contact']		['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Contact Phone']			['Value']	= "CASE WHEN Contact.Phone = '' THEN Contact.Mobile ELSE Contact.Phone END";
$arrSQLSelect['Contact Phone']			['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Created On']				['Value']	= "DATE_FORMAT(Account.CreatedOn, '%d/%m/%Y')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrSQLFields['CreatedOn']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "Account",
										'Documentation-Field'	=> "CreatedOn",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// Service Line Status Report
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Service Line Status Report";
$arrDataReport['Summary']		= "Displays a List of all ";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Service JOIN Account ON Service.Account = Account.Id";
$arrDataReport['SQLWhere']		= "(LineStatus = <LineStatus> OR <LineStatus> IS NULL) AND (PreselectionStatus = <PreselectionStatus> OR <PreselectionStatus> IS NULL) AND (Service.Status = <ServiceStatus> OR <ServiceStatus> IS NULL) AND Account.Archived != 1 AND Service.Status != 403 AND ServiceType = 102";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Account";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account #']				['Value']	= "Account.Id";
$arrSQLSelect['Account #']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Business Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Service FNN']			['Value']	= "Service.FNN";
$arrSQLSelect['Service FNN']			['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Full Line Status']		['Value']	= "CASE WHEN Contact.Phone = '' THEN Contact.Mobile ELSE Contact.Phone END";

$arrSQLSelect['Created On']				['Value']	= "DATE_FORMAT(Account.CreatedOn, '%d/%m/%Y')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrColumns = Array();
$arrColumns['Label']	= "description";
$arrColumns['Value']	= "id";

$arrSelect = Array();
$arrSelect['Table']			= "service_line_status";
$arrSelect['Columns']		= $arrColumns;
$arrSelect['Where']			= "1";
$arrSelect['OrderBy']		= "";
$arrSelect['Limit']			= NULL;
$arrSelect['GroupBy']		= NULL;
$arrSelect['ValueType']		= "dataInteger";

$arrSelect['IgnoreField']	= Array('Allow' => TRUE, 'Label' => "* Show All *", 'Value' => NULL, 'Position' => 'First');
$arrSQLFields['LineStatus']			= Array(
												'Type'					=> "StatementSelect",
												'DBSelect'				=> $arrSelect,
												'Documentation-Entity'	=> "Service",
												'Documentation-Field'	=> "LineStatus",
											);

$arrColumns = Array();
$arrColumns['Label']	= "description";
$arrColumns['Value']	= "id";

$arrSelect = Array();
$arrSelect['Table']			= "service_line_status";
$arrSelect['Columns']		= $arrColumns;
$arrSelect['Where']			= "1";
$arrSelect['OrderBy']		= "";
$arrSelect['Limit']			= NULL;
$arrSelect['GroupBy']		= NULL;
$arrSelect['ValueType']		= "dataInteger";

$arrSelect['IgnoreField']	= Array('Label' => "* Show All *", 'Value' => NULL, 'Position' => 'First');
$arrSQLFields['PreselectionStatus']	= Array(
												'Type'					=> "StatementSelect",
												'DBSelect'				=> $arrSelect,
												'Documentation-Entity'	=> "Service",
												'Documentation-Field'	=> "PreselectionStatus",
											);

$arrColumns = Array();
$arrColumns['Label']	= "description";
$arrColumns['Value']	= "id";

$arrSelect = Array();
$arrSelect['Table']			= "service_status";
$arrSelect['Columns']		= $arrColumns;
$arrSelect['Where']			= "1";
$arrSelect['OrderBy']		= "";
$arrSelect['Limit']			= NULL;
$arrSelect['GroupBy']		= NULL;
$arrSelect['ValueType']		= "dataInteger";

$arrSelect['IgnoreField']	= Array('Label' => "* Show All *", 'Value' => NULL);
$arrSQLFields['ServiceStatus']		= Array(
												'Type'					=> "StatementSelect",
												'DBSelect'				=> $arrSelect,
												'Documentation-Entity'	=> "Service",
												'Documentation-Field'	=> "Status",
											);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// New Direct Debits in a Period
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "New Direct Debits in a Period";
$arrDataReport['Summary']		= "Displays a List of all new Direct Debit entries in Flex and the Employee who added them in a specified period.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(Account JOIN CreditCard DD USING (AccountGroup)) LEFT JOIN Employee ON Employee.Id = DD.employee_id";
$arrDataReport['SQLWhere']		= "Account.Archived != 1 AND DD.Archived = 0
									AND CAST(DD.created_on AS DATE) BETWEEN <StartDate> AND <EndDate>
									GROUP BY DD.employee_id, Account.AccountGroup

									UNION

									SELECT CONCAT(Employee.LastName, ', ', Employee.FirstName) AS Employee, Account.Id AS `Account #`, Account.BusinessName AS `Business Name`, DATE_FORMAT(DD.created_on, '%Y-%m-%d') AS `Created On`
									FROM (Account JOIN DirectDebit DD USING (AccountGroup)) LEFT JOIN Employee ON Employee.Id = DD.employee_id
									WHERE Account.Archived != 1 AND DD.Archived = 0
									AND CAST(DD.created_on AS DATE) BETWEEN <StartDate> AND <EndDate>
									GROUP BY DD.employee_id, Account.AccountGroup

									ORDER BY ISNULL(Employee) ASC, Employee ASC, `Created On` ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Account";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Employee']				['Value']	= "CONCAT(Employee.LastName, ', ', Employee.FirstName)";

$arrSQLSelect['Account #']				['Value']	= "Account.Id";
$arrSQLSelect['Account #']				['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Business Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Created On']				['Value']	= "DATE_FORMAT(DD.created_on, '%Y-%m-%d')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//----------------------------------------------------------------------------//
// Direct Debits by Employee Summary
//----------------------------------------------------------------------------//

// General Data
$arrDataReport['Name']			= "Direct Debits by Employee Summary";
$arrDataReport['Summary']		= "Displays a list of Employees, and how many Direct Debit Accounts they've set up in a specified period.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	(
										SELECT CONCAT(Employee.LastName, ', ', Employee.FirstName) AS Employee, CreditCard.Id AS CreditCard, NULL AS BankTransfer
										FROM Employee JOIN CreditCard ON (Employee.Id = CreditCard.employee_id AND CreditCard.Archived = 0)";
$arrDataReport['SQLWhere']		= "		CAST(DirectDebit.created_on AS DATE) BETWEEN <StartDate> AND <EndDate>

										UNION

										SELECT CONCAT(Employee.LastName, ', ', Employee.FirstName) AS Employee, NULL AS CreditCard, DirectDebit.Id AS BankTransfer
										FROM Employee JOIN DirectDebit ON (Employee.Id = DirectDebit.employee_id AND DirectDebit.Archived = 0)
										WHERE CAST(DirectDebit.created_on AS DATE) BETWEEN <StartDate> AND <EndDate>
									)";
$arrDataReport['SQLGroupBy']	= "Employee.Id\n HAVING `Credit Card` > 0 OR `Bank Transfer` > 0\n ORDER BY Employee ASC";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Account";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Employee']				['Value']	= "Employee";

$arrSQLSelect['Credit Card']			['Value']	= "COUNT(DISTINCT CreditCard.Id)";
$arrSQLSelect['Credit Card']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Bank Transfer']			['Value']	= "COUNT(DISTINCT BankTransfer.Id)";
$arrSQLSelect['Bank Transfer']			['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);


// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


 //---------------------------------------------------------------------------//
 // NON-TOLLING SERVICES
 //---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Non-Tolling Services in a Date Period for a Service Type";
$arrDataReport['Summary']		= "Lists all of the Services which have not tolled since the specified Last Tolling Date for the specified Service Type";
$arrDataReport['FileName']		= "<ServiceType::Label> Services that have not tolled since <LatestCDR>";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= 	"(" .
										"(" .
											"(" .
												"Service LEFT JOIN Account ON Account.Id = Service.Account" .
											") " .
											"LEFT JOIN Contact ON Account.PrimaryContact = Contact.Id" .
										") " .
										"LEFT JOIN ServiceRatePlan SRP ON Service.Id = SRP.Service" .
									") " .
									"LEFT JOIN RatePlan ON SRP.RatePlan = RatePlan.Id";

$arrDataReport['SQLWhere']		= "Service.ServiceType = <ServiceType> AND Service.Status = 400 AND Service.LatestCDR <= CONCAT(<LatestCDR>, ' 23:59:59')";
$arrDataReport['SQLGroupBy']	= "Service.Id ORDER BY Account.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Service";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account No.']			['Value']	= "Account.Id";

$arrSQLSelect['Customer Name']			['Value']	= "Account.BusinessName";

$arrSQLSelect['Primary Contact']		['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Contact Phone']			['Value']	= "Contact.Phone";
$arrSQLSelect['Contact Phone']			['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service FNN']		['Value']	= "Service.FNN";
$arrSQLSelect['Lost Service FNN']		['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Lost Service Plan']		['Value']	= "RatePlan.Name";

$arrSQLSelect['Last Tolled Date']		['Value']	= "Service.LatestCDR";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrColumns = Array();
$arrColumns['Label']	= "description";
$arrColumns['Value']	= "id";

$arrSelect = Array();
$arrSelect['Table']		= "service_type";
$arrSelect['Columns']	= $arrColumns;
$arrSelect['Where']		= "const_name NOT IN ('SERVICE_TYPE_ADSL', 'SERVICE_TYPE_DIALUP')";
$arrSelect['OrderBy']	= "description ASC";
$arrSelect['Limit']		= NULL;
$arrSelect['GroupBy']	= NULL;
$arrSelect['ValueType']	= "dataInteger";

$arrSQLFields['ServiceType']	= Array(
										'Type'					=> "StatementSelect",
										'DBSelect'				=> $arrSelect,
										'Documentation-Entity'	=> "Service",
										'Documentation-Field'	=> "ServiceType",
									);

$arrSQLFields['LatestCDR']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "Service",
										'Documentation-Field'	=> "LatestCDR",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

//---------------------------------------------------------------------------//
// CALL TYPE STATISTICS FOR A CARRIER
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Call Type Statistics for a Carrier";
$arrDataReport['Summary']		= "Lists Call Type summaries for each Carrier";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "((CDR JOIN RecordType ON CDR.RecordType = RecordType.Id) JOIN Carrier ON Carrier.Id = CDR.Carrier) JOIN service_type ON RecordType.ServiceType = service_type.id";
$arrDataReport['SQLWhere']		= "CDR.Status IN (150, 198) AND Credit = 0";
$arrDataReport['SQLGroupBy']	= "Carrier.Id, CDR.RecordType \n ORDER BY Carrier.Id ASC, service_type.id, RecordType.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Carrier']				['Value']	= "Carrier.Name";

$arrSQLSelect['Service Type']			['Value']	= "service_type.description";

$arrSQLSelect['Call Type']				['Value']	= "RecordType.Description";

$arrSQLSelect['Unique FNNs']			['Value']	= "COUNT(DISTINCT CDR.FNN)";
$arrSQLSelect['Unique FNNs']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Total Calls']			['Value']	= "COUNT(CDR.Id)";
$arrSQLSelect['Total Calls']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Total Units']			['Value']	= "SUM(CDR.Units)";
$arrSQLSelect['Total Units']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Total Cost']				['Value']	= "SUM(CDR.Cost)";
$arrSQLSelect['Total Cost']				['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total Rated']			['Value']	= "SUM(CDR.Charge)";
$arrSQLSelect['Total Rated']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


//---------------------------------------------------------------------------//
// CREDIT CARD PAYMENTS SUMMARY BY EMPLOYEE
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Credit Card Payments Summary by Employee";
$arrDataReport['Summary']		= "Shows the number of Credit Card Payments each Employee made in a given time period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "credit_card_payment_history JOIN Employee ON Employee.Id = credit_card_payment_history.employee_id";
$arrDataReport['SQLWhere']		= "CAST(payment_datetime AS DATE) BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "Employee.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Employee']				['Value']	= "CONCAT(Employee.LastName, ', ', Employee.FirstName)";

$arrSQLSelect['Payments Made']			['Value']	= "COUNT(credit_card_payment_history.id)";
$arrSQLSelect['Payments Made']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Payment Total']			['Value']	= "SUM(credit_card_payment_history.amount)";
$arrSQLSelect['Payment Total']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);




//---------------------------------------------------------------------------//
// CREDIT CARD PAYMENT DETAILS
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Credit Card Payment Details";
$arrDataReport['Summary']		= "Show a list of Credit Card Payments made through Flex for a given date period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(credit_card_payment_history JOIN Employee ON Employee.Id = credit_card_payment_history.employee_id) JOIN Account ON Account.Id = credit_card_payment_history.account_id";
$arrDataReport['SQLWhere']		= "CAST(payment_datetime AS DATE) BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Employee']			['Value']	= "CONCAT(Employee.LastName, ', ', Employee.FirstName)";

$arrSQLSelect['Account #']			['Value']	= "Account.Id";
$arrSQLSelect['Account #']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "Account.BusinessName";

$arrSQLSelect['Payment Date']		['Value']	= "DATE_FORMAT(payment_datetime, '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Amount']				['Value']	= "credit_card_payment_history.amount";
$arrSQLSelect['Amount']				['Type']	= EXCEL_TYPE_CURRENCY;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


//---------------------------------------------------------------------------//
// ACCOUNTS CREATED IN A DATE PERIOD
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Accounts Created in a Date Period";
$arrDataReport['Summary']		= "Show a list of Accounts which were created in the specified Date Period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Account JOIN Contact ON Account.Id = Contact.Account";
$arrDataReport['SQLWhere']		= "Account.CreatedOn BETWEEN <StartDate> AND <EndDate> AND Account.Archived = 0";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account #']			['Value']	= "Account.Id";
$arrSQLSelect['Account #']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "Account.BusinessName";

$arrSQLSelect['Contact']			['Value']	= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";

$arrSQLSelect['Contact Phone']		['Value']	= "CASE WHEN Contact.Phone != '' THEN Contact.Phone ELSE Contact.Mobile END";
$arrSQLSelect['Contact Phone']		['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Date Created']		['Value']	= "Account.CreatedOn";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "StartDateRange",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "EndDateRange",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


//---------------------------------------------------------------------------//
// OPEN NETWORKS DAILY ORDER FILE
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Open Networks Daily Order File";
$arrDataReport['Summary']		= "Generates the Open Networks Daily Order File for a specified date";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['RenderTarget']	= REPORT_TARGET_CSV;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Service s
									JOIN Account a ON (s.Account = a.Id)
									JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
									JOIN ServiceRatePlan srp ON (s.Id = srp.Service)
									JOIN RatePlan rp ON (rp.id = srp.RatePlan)";
$arrDataReport['SQLWhere']		= "	s.ServiceType = 100
												AND s.Status = 400
												AND a.Archived = 0
												AND CAST(s.CreatedOn AS DATE) = <order_date>
												AND <order_date> BETWEEN CAST(srp.StartDatetime AS DATE) AND CAST(srp.EndDatetime AS DATE)
												AND srp.Id	=	(
																	SELECT		Id
																	FROM		ServiceRatePlan
																	WHERE		Service = s.Id
																				AND <order_date> BETWEEN CAST(StartDatetime AS DATE) AND CAST(EndDatetime AS DATE)
																	ORDER BY	CreatedOn DESC
																	LIMIT		1
																)
									ORDER BY	s.Id ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Order Number']					['Value']	= "s.Id";

$arrSQLSelect['ISP Identification']				['Value']	= "cg.internal_name";

$arrSQLSelect['Required Ship Date']				['Value']	= "DATE_FORMAT(CURDATE(), '%d/%m/%y')";

$arrSQLSelect['Product Required']				['Value']	= "'Netgear DM111PUSP'";

$arrSQLSelect['Additional Product Required']	['Value']	= "''";

$arrSQLSelect['Subscriber Name']				['Value']	= "a.BusinessName";

$arrSQLSelect['Unique User Name']				['Value']	= "CONCAT(LEFT(s.FNN, 10), '@blue1000.com.au')";

$arrSQLSelect['Unique Password']				['Value']	= "LEFT(s.FNN, 10)";

$arrSQLSelect['Subscriber Ph. No.']				['Value']	= "LEFT(s.FNN, 10)";

$arrSQLSelect['Street Number & Name']			['Value']	= "a.Address1";

$arrSQLSelect['Extra Address Details']			['Value']	= "a.Address2";

$arrSQLSelect['Suburb/City']					['Value']	= "a.Suburb";

$arrSQLSelect['State']							['Value']	= "a.State";

$arrSQLSelect['Postcode']						['Value']	= "a.Postcode";

$arrSQLSelect['Notes/comments']					['Value']	= "rp.Name";

$arrSQLSelect['Serial Number']					['Value']	= "''";

$arrSQLSelect['Consignment Number']				['Value']	= "''";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['order_date']	= array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "OrderDate",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

// Overrides
$arrOverrides				= array();
$arrOverrides['Delimiter']	= ",";
/*
$arrOverrides['Delimiter']	= "\t";
$arrOverrides['Enclose']		= "";
$arrOverrides['Extension']	= "txt";
*/
$arrDataReport['Overrides'] = serialize($arrOverrides);


//---------------------------------------------------------------------------//
// ACCOUNT ADDRESS CHANGES SUMMARY
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Account Address Changes Summary";
$arrDataReport['Summary']		= "Generates the Open Networks Daily Order File for a specified date";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['RenderTarget']	= REPORT_TARGET_XLS;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Service s
									JOIN Account a ON (s.Account = a.Id)
									JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
									JOIN ServiceRatePlan srp ON (s.Id = srp.Service)
									JOIN RatePlan rp ON (rp.id = srp.RatePlan)";
$arrDataReport['SQLWhere']		= "	s.ServiceType = 100
												AND s.Status = 400
												AND a.Archived = 0
												AND CAST(s.CreatedOn AS DATE) = <order_date>
												AND <order_date> BETWEEN CAST(srp.StartDatetime AS DATE) AND CAST(srp.EndDatetime AS DATE)
												AND srp.Id	=	(
																	SELECT		Id
																	FROM		ServiceRatePlan
																	WHERE		Service = s.Id
																				AND <order_date> BETWEEN CAST(StartDatetime AS DATE) AND CAST(EndDatetime AS DATE)
																	ORDER BY	CreatedOn DESC
																	LIMIT		1
																)
									ORDER BY	s.Id ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Order Number']					['Value']	= "s.Id";

$arrSQLSelect['ISP Identification']				['Value']	= "cg.internal_name";

$arrSQLSelect['Required Ship Date']				['Value']	= "DATE_FORMAT(CURDATE(), '%d/%m/%y')";

$arrSQLSelect['Product Required']				['Value']	= "'Netgear DM111PUSP'";

$arrSQLSelect['Additional Product Required']	['Value']	= "''";

$arrSQLSelect['Subscriber Name']				['Value']	= "a.BusinessName";

$arrSQLSelect['Unique User Name']				['Value']	= "CONCAT(LEFT(s.FNN, 10), '@blue1000.com.au')";

$arrSQLSelect['Unique Password']				['Value']	= "LEFT(s.FNN, 10)";

$arrSQLSelect['Subscriber Ph. No.']				['Value']	= "LEFT(s.FNN, 10)";

$arrSQLSelect['Street Number & Name']			['Value']	= "a.Address1";

$arrSQLSelect['Extra Address Details']			['Value']	= "a.Address2";

$arrSQLSelect['Suburb/City']					['Value']	= "a.Suburb";

$arrSQLSelect['State']							['Value']	= "a.State";

$arrSQLSelect['Postcode']						['Value']	= "a.Postcode";

$arrSQLSelect['Notes/comments']					['Value']	= "rp.Name";

$arrSQLSelect['Serial Number']					['Value']	= "''";

$arrSQLSelect['Consignment Number']				['Value']	= "''";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['order_date']	= array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "OrderDate",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

// Overrides
$arrOverrides				= array();
$arrOverrides['Delimiter']	= ",";
/*
$arrOverrides['Delimiter']	= "\t";
$arrOverrides['Enclose']		= "";
$arrOverrides['Extension']	= "txt";
*/
$arrDataReport['Overrides'] = serialize($arrOverrides);

?>