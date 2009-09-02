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
$arrDataReport['SQLTable']		= "Account JOIN CustomerGroup ON CustomerGroup.Id = Account.CustomerGroup JOIN Contact ON Contact.Id = Account.PrimaryContact";
$arrDataReport['SQLWhere']		= "Account.CreatedOn BETWEEN <StartDate> AND <EndDate> AND Account.Archived = 0";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account #']			['Value']	= "Account.Id";
$arrSQLSelect['Account #']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "Account.BusinessName";

$arrSQLSelect['Customer Group']		['Value']	= "CustomerGroup.external_name";

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


//---------------------------------------------------------------------------//
// TICKETING AUDIT REPORT
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Ticketing Audit Report";
$arrDataReport['Summary']		= "Generates a list of Open and Pending Tickets for auditing purposes";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "				ticketing_ticket tt
												JOIN ticketing_category tc ON (tt.category_id = tc.id)
												JOIN ticketing_status ts ON (tt.status_id = ts.id)
												JOIN ticketing_status_type tst ON (ts.status_type_id = tst.id)
												JOIN ticketing_priority tp ON (tt.priority_id = tp.id)
												LEFT JOIN ticketing_user tu ON (tt.owner_id = tu.id)
												LEFT JOIN Employee e ON (tu.employee_id = e.Id)";
$arrDataReport['SQLWhere']		= "				tst.const_name IN ('TICKETING_STATUS_TYPE_PENDING', 'TICKETING_STATUS_TYPE_OPEN')
									ORDER BY	tt.Id ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Ticket ID']						['Value']	= "tt.id";

$arrSQLSelect['Subject']						['Value']	= "tt.subject";

$arrSQLSelect['Last Actioned']					['Value']	= "DATE_FORMAT(tt.modified_datetime, '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Received']						['Value']	= "DATE_FORMAT(tt.creation_datetime, '%d/%m/%Y %H:%i:%s')";

$arrSQLSelect['Owner']							['Value']	= "CONCAT(e.FirstName, ' ', e.LastName)";

$arrSQLSelect['Category']						['Value']	= "tc.name";

$arrSQLSelect['Status']							['Value']	= "ts.name";

$arrSQLSelect['Priority']						['Value']	= "tp.name";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);

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



//---------------------------------------------------------------------------//
// ACCOUNT LIFETIME PROFIT SUMMARY
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Account Lifetime Profit Summary";
$arrDataReport['Summary']		= "Provides a Profit Summary for each Account since they were entered in Flex";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "				Account a
												JOIN Invoice i ON (a.Id = i.Account)
												JOIN InvoiceRun ir ON (i.invoice_run_id = ir.Id)
												JOIN
												(
													SELECT		stt.Account														AS account_id,
																stt.invoice_run_id												AS invoice_run_id,
																SUM(stt.Cost)													AS cdr_cost,
																SUM(stt.Charge)													AS cdr_rated,
																SUM(IF(rt.global_tax_exempt = 0, stt.Cost * 1.1, stt.Cost))		AS cdr_cost_gst,
																SUM(IF(rt.global_tax_exempt = 0, stt.Charge * 1.1, stt.Charge))	AS cdr_rated_gst
													FROM		InvoiceRun ir
																JOIN Invoice i ON (i.invoice_run_id = ir.Id)
																LEFT JOIN ServiceTypeTotal stt ON (stt.Account = i.Account AND stt.invoice_run_id = i.invoice_run_id)
																LEFT JOIN RecordType rt ON (stt.RecordType = rt.Id)
													GROUP BY	account_id,
																invoice_run_id
												) ict ON (ict.invoice_run_id = ir.Id AND i.Account = ict.account_id)";
$arrDataReport['SQLWhere']		= "				ir.invoice_run_type_id IN (1, 4, 5)";
$arrDataReport['SQLGroupBy']	= "				a.Id";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account']							['Value']	= "a.Id";

$arrSQLSelect['Account Name']						['Value']	= "a.BusinessName";

$arrSQLSelect['Times Invoiced']						['Value']	= "COUNT(i.Id)";

$arrSQLSelect['Last Invoiced On']					['Value']	= "DATE_FORMAT(MAX(ir.BillingDate), '%d/%m/%Y')";

$arrSQLSelect['Total CDR Cost (ex GST)']			['Value']	= "SUM(ict.cdr_cost)";
$arrSQLSelect['Total CDR Cost (ex GST)']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total CDR Cost (inc GST)']			['Value']	= "SUM(ict.cdr_cost_gst)";
$arrSQLSelect['Total CDR Cost (inc GST)']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total CDR Rated Charge (ex GST)']	['Value']	= "SUM(ict.cdr_rated)";
$arrSQLSelect['Total CDR Rated Charge (ex GST)']	['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total CDR Rated Charge (inc GST)']	['Value']	= "SUM(ict.cdr_rated_gst)";
$arrSQLSelect['Total CDR Rated Charge (inc GST)']	['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['CDR Profit Margin']					['Value']	= "IF(SUM(ict.cdr_rated), (SUM(ict.cdr_rated) - SUM(ict.cdr_cost)) / ABS(SUM(ict.cdr_rated)), 0)";
$arrSQLSelect['CDR Profit Margin']					['Type']	= EXCEL_TYPE_PERCENTAGE;

$arrSQLSelect['Total Invoiced (ex GST)']			['Value']	= "SUM(i.Total)";
$arrSQLSelect['Total Invoiced (ex GST)']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total Taxed']						['Value']	= "SUM(i.Tax)";
$arrSQLSelect['Total Taxed']						['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Total Invoiced (inc GST)']			['Value']	= "SUM(i.Total + i.Tax)";
$arrSQLSelect['Total Invoiced (inc GST)']			['Type']	= EXCEL_TYPE_CURRENCY;

$arrSQLSelect['Invoice Profit Margin']				['Value']	= "IF(SUM(i.Total), (SUM(i.Total) - SUM(ict.cdr_cost)) / ABS(SUM(i.Total)), 0)";
$arrSQLSelect['Invoice Profit Margin']				['Type']	= EXCEL_TYPE_PERCENTAGE;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;




//---------------------------------------------------------------------------//
// ACCOUNTS CREATED IN A DATE PERIOD
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Accounts Created in a Date Period";
$arrDataReport['Summary']		= "Show a list of Accounts which were created in the specified Date Period";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Account JOIN CustomerGroup ON CustomerGroup.Id = Account.CustomerGroup JOIN Contact ON Contact.Id = Account.PrimaryContact";
$arrDataReport['SQLWhere']		= "Account.CreatedOn BETWEEN <StartDate> AND <EndDate> AND Account.Archived = 0";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account #']			['Value']	= "Account.Id";
$arrSQLSelect['Account #']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "Account.BusinessName";

$arrSQLSelect['Customer Group']		['Value']	= "CustomerGroup.external_name";

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


// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;

//---------------------------------------------------------------------------//
// SERVICES OF A SERVICE TYPE ON A STATUS ON A GIVEN DATE
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Services on a Service Status";
$arrDataReport['Summary']		= "Shows a list of Services for a given Service Type, Service Status, and Date combination";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Service s
									JOIN Account a ON (s.Account = a.Id)
									JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
									JOIN Contact c ON (a.PrimaryContact = c.Id)
									JOIN ServiceRatePlan srp ON (s.Id = srp.Service)
									JOIN RatePlan rp ON (srp.RatePlan = rp.Id)";
$arrDataReport['SQLWhere']		= "	<EffectiveDate> BETWEEN CAST(srp.StartDatetime AS DATE) AND CAST(srp.EndDatetime AS DATE)
									AND srp.Id =	(
														SELECT		Id
														FROM		ServiceRatePlan
														WHERE		Service = srp.Service
																	AND <EffectiveDate> BETWEEN CAST(StartDatetime AS DATE) AND CAST(EndDatetime AS DATE)
														ORDER BY	CreatedOn DESC
														LIMIT		1
													)
									AND s.ServiceType = <ServiceType>
									AND s.Status = <ServiceStatus>
									AND IF(s.ClosedOn IS NULL, <EffectiveDate> > s.CreatedOn, <EffectiveDate> BETWEEN s.CreatedOn AND s.ClosedOn)";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['FNN']				['Value']	= "s.FNN";
$arrSQLSelect['FNN']				['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Account #']			['Value']	= "a.Id";
$arrSQLSelect['Account #']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "a.BusinessName";

$arrSQLSelect['Customer Group']		['Value']	= "cg.external_name";

$arrSQLSelect['Contact']			['Value']	= "CONCAT(c.FirstName, ' ', c.LastName)";

$arrSQLSelect['Contact Phone']		['Value']	= "IF(c.Phone != '', c.Phone, c.Mobile)";
$arrSQLSelect['Contact Phone']		['Type']	= EXCEL_TYPE_FNN;

$arrSQLSelect['Rate Plan']			['Value']	= "rp.Name";

$arrSQLSelect['Has Tolled']			['Value']	= "IF(s.EarliestCDR, 'Yes', 'No')";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrServiceTypeQuery =	array
						(
							'Query'			=> "	SELECT id AS Value, name AS Label
													FROM service_type
													ORDER BY name ASC;",
							'ValueType'		=> "dataInteger"
						);

$arrServiceStatusQuery =	array
							(
								'Query'			=> "	SELECT id AS Value, name AS Label
														FROM service_status
														ORDER BY name ASC;",
								'ValueType'		=> "dataInteger"
							);



$arrSQLFields['ServiceType']	= Array(
											'Type'					=> "Query",
											'DBQuery'				=> $arrServiceTypeQuery,
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Service Type",
										);


$arrSQLFields['ServiceStatus']	= Array(
											'Type'					=> "Query",
											'DBQuery'				=> $arrServiceStatusQuery,
											'Documentation-Entity'	=> "Service",
											'Documentation-Field'	=> "Service Status",
										);

$arrSQLFields['EffectiveDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Effective Date",
									);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;


//---------------------------------------------------------------------------//
// MAIL MERGE: WELCOME LETTER
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Mail Merge: Welcome Letter";
$arrDataReport['Summary']		= "Generates a data set for Mail Merging with Welcome Letters";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Account a
									JOIN Contact c ON (a.PrimaryContact = c.Id)
									JOIN CustomerGroup cg ON (cg.Id = a.CustomerGroup)";
$arrDataReport['SQLWhere']		= "	CAST(a.CreatedOn AS DATE) BETWEEN <StartDate> AND <EndDate>
									AND a.Archived IN (0, 5)
									AND a.CustomerGroup = <CustomerGroup>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Customer Group']		['Value']	= "cg.external_name";

$arrSQLSelect['Account']			['Value']	= "a.Id";
$arrSQLSelect['Account']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "a.BusinessName";

$arrSQLSelect['First Name']			['Value']	= "c.FirstName";

$arrSQLSelect['Last Name']			['Value']	= "c.LastName";

$arrSQLSelect['Address Line 1']		['Value']	= "a.Address1";

$arrSQLSelect['Address Line 2']		['Value']	= "a.Address2";

$arrSQLSelect['Suburb']				['Value']	= "a.Suburb";

$arrSQLSelect['Postcode']			['Value']	= "a.Postcode";

$arrSQLSelect['State']				['Value']	= "a.State";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrCustomerGroupQuery =	array
							(
								'Query'			=> "	SELECT		Id				AS `Value`,
																	external_name	AS `Label`
														FROM		CustomerGroup cg
														WHERE		1
														ORDER BY	Id ASC;",
								'ValueType'		=> "dataInteger"
							);



$arrSQLFields['CustomerGroup']	= Array(
											'Type'					=> "Query",
											'DBQuery'				=> $arrCustomerGroupQuery,
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Customer Group",
										);

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
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;

//---------------------------------------------------------------------------//
// Accounts Consistently Invoicing over $1000 (Last 3 Invoices)
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Accounts Consistently Invoicing over \$1000 (Last 3 Invoices)";
$arrDataReport['Summary']		= "Lists all Accounts who have invoiced over \$1000 over the last 3 Invoices";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Account a
									JOIN account_status a_s ON (a_s.id = a.Archived)
									JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
									JOIN Contact c ON (a.PrimaryContact = c.Id)
									JOIN Invoice i_current ON (a.Id = i_current.Account)
									JOIN Invoice i_previous ON (a.Id = i_previous.Account)
									JOIN Invoice i_threevious ON (a.Id = i_threevious.Account)";
$arrDataReport['SQLWhere']		= "	i_current.Id =	(
														SELECT		Invoice.Id
														FROM		Invoice
																	JOIN InvoiceRun ON (Invoice.invoice_run_id = InvoiceRun.Id)
														WHERE		invoice_run_type_id IN (1)
																	AND Invoice.Account = a.Id
														ORDER BY	Id DESC
														LIMIT		1 OFFSET 0
													)
									AND i_previous.Id =	(
															SELECT		Invoice.Id
															FROM		Invoice
																		JOIN InvoiceRun ON (Invoice.invoice_run_id = InvoiceRun.Id)
															WHERE		invoice_run_type_id IN (1)
																		AND Invoice.Account = a.Id
															ORDER BY	Id DESC
															LIMIT		1 OFFSET 1
														)
									AND i_threevious.Id =	(
																SELECT		Invoice.Id
																FROM		Invoice
																			JOIN InvoiceRun ON (Invoice.invoice_run_id = InvoiceRun.Id)
																WHERE		invoice_run_type_id IN (1)
																			AND Invoice.Account = a.Id
																ORDER BY	Id DESC
																LIMIT		1 OFFSET 2
															)
									AND a_s.const_name NOT IN ('ACCOUNT_CLOSED', 'ACCOUNT_ARCHIVED')
									AND a.vip = 0
									AND (i_current.Total + i_current.Tax) > 1000
									AND (i_previous.Total + i_previous.Tax) > 1000
									AND (i_threevious.Total + i_threevious.Tax) > 1000";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Customer Group']		['Value']	= "cg.internal_name";

$arrSQLSelect['Account']			['Value']	= "a.Id";
$arrSQLSelect['Account']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Account Name']		['Value']	= "a.BusinessName";

$arrSQLSelect['Contact Name']		['Value']	= "CONCAT(c.FirstName, ' ', c.LastName)";

$arrSQLSelect['Contact Phone']		['Value']	= "IF(CAST(c.Phone AS UNSIGNED) > 0, c.Phone, c.Mobile)";
$arrSQLSelect['Contact Phone']		['Type']	= EXCEL_TYPE_FNN;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


// Add the report to the array of reports to add to the database
$arrDataReports[] = $arrDataReport;



?>