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
$arrDataReport['Name']			= "Profit Report for a Billing Period";
$arrDataReport['Summary']		= "Lists Profit Data for every Invoice generated in a specified Billing Period";
$arrDataReport['RenderMode']	= REPORT_RENDER_EMAIL;
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(Invoice JOIN Account ON Account.Id = Invoice.Account) LEFT JOIN ServiceTypeTotal USING (Account, InvoiceRun)";
$arrDataReport['SQLWhere']		= "Invoice.InvoiceRun = <InvoiceRun>";
$arrDataReport['SQLGroupBy']	= "Account.Id";

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

 ?>