<?php

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

/*
//----------------------------------------------------------------------------//
// Credit Totals per Employee
//----------------------------------------------------------------------------//

// General
$arrDataReport = Array();
$arrDataReport['Name']			= "Credit Totals per Employee";
$arrDataReport['Summary']		= "Show the total number of Credits applied, their Total, and the Largest Credit per Employee for the last generated Bill.";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Charge, Employee";
$arrDataReport['SQLWhere']		= "WHERE Charge.Nature = 'CR' AND " .
								"Charge.CreatedBy = Employee.Id AND " .
								"Charge.InvoiceRun = (SELECT InvoiceRun FROM Invoice ORDER BY CreatedOn DESC LIMIT 1)";
$arrDataReport['SQLGroupBy']	= "Charge.CreatedBy";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "Charge";
$arrDocReq[]	= "Employee";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Employee']			= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
$arrSQLSelect['No. of Credits']		= "COUNT(Charge.Id)";
$arrSQLSelect['Total']				= "SUM(Charge.Amount)";
$arrSQLSelect['Largest Credit']		= "MAX(Charge.Amount)";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
*/
/*
//----------------------------------------------------------------------------//
// Debit Totals per Employee
//----------------------------------------------------------------------------//

// General
$arrDataReport = Array();
$arrDataReport['Name']			= "Debit Totals per Employee";
$arrDataReport['Summary']		= "Show the total number of Debits applied, their Total, and the Largest Debit per Employee for the last generated Bill.";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Charge, Employee";
$arrDataReport['SQLWhere']		= "WHERE Charge.Nature = 'DR' AND " .
								"Charge.CreatedBy = Employee.Id AND " .
								"Charge.InvoiceRun = (SELECT InvoiceRun FROM Invoice ORDER BY CreatedOn DESC LIMIT 1)";
$arrDataReport['SQLGroupBy']	= "Charge.CreatedBy";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "Charge";
$arrDocReq[]	= "Employee";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Employee']			= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";
$arrSQLSelect['No. of Debits']		= "COUNT(Charge.Id)";
$arrSQLSelect['Total']				= "SUM(Charge.Amount)";
$arrSQLSelect['Largest Debit']		= "MAX(Charge.Amount)";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
*/
/*
//----------------------------------------------------------------------------//
// Aged Receivables (30/60/90 Days) Report per Account
//----------------------------------------------------------------------------//

// General
$arrDataReport = Array();
$arrDataReport['Name']			= "Aged Receivables (30/60/90 Days) Report per Account";
$arrDataReport['Summary']		= "Shows how much each Account owes, grouped by how old the overdue amount is (1-30 days, 30-60 days, 60-90 days, 90+ days old).";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Invoice JOIN Account ON Invoice.Account = Account.Id JOIN Contact ON Account.PrimaryContact = Contact.Id";
$arrDataReport['SQLWhere']		= "(<ShowArchived> = 1) OR (<ShowArchived> = 0 AND Account.Archived = 0)";
$arrDataReport['SQLGroupBy']	= "Invoice.Account \nHAVING SUM(Balance) > 0";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "Invoice";
$arrDocReq[]	= "Account";
$arrDocReq[]	= "Contact";
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Account No.']			= "Invoice.Account";
$arrSQLSelect['Business Name']			= "Account.BusinessName";
$arrSQLSelect['Customer Group']			=	"CASE " .
											"	WHEN Account.CustomerGroup = 2 THEN 'Voicetalk'" .
											"	WHEN Account.CustomerGroup = 3 THEN 'Imagine'" .
											"	ELSE 'Telco Blue'" .
											"END";
$arrSQLSelect['Customer Name']			= "CONCAT(Contact.FirstName, ' ', Contact.LastName)";
$arrSQLSelect['Address Line 1']			= "Account.Address1";
$arrSQLSelect['Address Line 2']			= "Account.Address2";
$arrSQLSelect['Suburb']					= "Account.Suburb";
$arrSQLSelect['Postcode']				= "Account.Postcode";
$arrSQLSelect['State']					= "Account.State";
$arrSQLSelect['Phone']					= "Contact.Phone";
$arrSQLSelect['Mobile']					= "Contact.Mobile";
$arrSQLSelect['Email']					= "Contact.Email";

$arrSQLSelect['Outstanding Not Overdue']	= "SUM(CASE " .
											"		WHEN CURDATE() <= Invoice.DueOn THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['1-29 Days Overdue']		=	"SUM(CASE " .
											"		WHEN CURDATE() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 1 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 29 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['30-59 Days Overdue']		=	"SUM(CASE " .
											"		WHEN CURDATE() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 30 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 59 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['60-89 Days Overdue']		=	"SUM(CASE " .
											"		WHEN CURDATE() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 60 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 89 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['90+ Days Overdue']		=	"SUM(CASE " .
											"		WHEN CURDATE() >= ADDDATE(Invoice.DueOn, INTERVAL 90 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['Total Overdue']			=	"SUM(CASE " .
											"		WHEN CURDATE() > Invoice.DueOn THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['Total Oustanding']		= "SUM(Balance)";

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrSQLFields['ShowArchived']	= Array(
											'Type'					=> "dataBoolean",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "ShowArchivedAccounts",
										);
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
*/

/*
//----------------------------------------------------------------------------//
// Daily Loss Report
//----------------------------------------------------------------------------//

// General
$arrDataReport = Array();
$arrDataReport['Name']			= "Loss Report for a Date Range";
$arrDataReport['Summary']		= "Shows the Services that have been lost and the date they were lost on for a specified Date range";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Service";
$arrDataReport['SQLWhere']		= "Service.ClosedOn BETWEEN <StartDate> AND <EndDate>";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "Service";
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Account No.']			= "Service.Account";
$arrSQLSelect['Service Id']				= "Service.Id";
$arrSQLSelect['Full National Number']	= "Service.FNN";
$arrSQLSelect['Date Lost']				= "DATE_FORMAT(Service.ClosedOn, '%d/%m/%Y')";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
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
*/

/*
//----------------------------------------------------------------------------//
// Non-Archived Accounts with No/Invalid Email
//----------------------------------------------------------------------------//

$arrDataReport = Array();
$arrDataReport['Name']			= "Non-Archived Accounts with and no valid Email Address";
$arrDataReport['Summary']		= "Shows all Active Accounts who have no valid Email Address, but have their Billing Method set to Email";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Account";
$arrDataReport['SQLWhere']		= "Account.BillingMethod = ".BILLING_METHOD_EMAIL." AND " .
								"Account.Archived = 0 AND " .
								"0 = (SELECT COUNT(Contact.Id) " .
									"FROM Contact " .
									"WHERE Contact.Email LIKE '%@%.%' AND " .
									"Contact.Email NOT LIKE '%delinquents%' AND " .
									"Contact.Account = Account.Id)";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "Account";
$arrDocReq[]	= "Contact";
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Account No.']	= "Account.Id";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
*/
/*
//----------------------------------------------------------------------------//
// Daily Provisioning Report
//----------------------------------------------------------------------------//

$arrDataReport = Array();
$arrDataReport['Name']			= "Daily Provisioning Report";
$arrDataReport['Summary']		= "Shows information on any Provisioning Requests and Responses for a specified date.  Also lists Pending Requests.";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(ProvisioningLog PL LEFT JOIN (Service, Request) ON (PL.Service = Service.Id AND Request.Id = PL.Request)) LEFT JOIN Employee ON Employee.Id = Request.Employee";
$arrDataReport['SQLWhere']		=	"WHERE PL.Date BETWEEN <StartDate> AND <EndDate>\n" .
									"ORDER BY PL.Date DESC\n" .
									"\n" .
									"UNION\n" .
									"\n" .
									"SELECT Request.Service AS Service, \n" .
									"CASE\n" .
									"	WHEN Request.Carrier = 1 THEN 'Unitel'\n" .
									"	WHEN Request.Carrier = 2 THEN 'Optus'\n" .
									"	WHEN Request.Carrier = 3 THEN 'AAPT'\n" .
									"	WHEN Request.Carrier = 4 THEN 'iSeek'\n" .
									"END AS Carrier,\n" .
									"'Outbound AS Direction',\n" .
									"DATE_FORMAT(PL2.Date, '%e/%m/%Y') AS Date,\n" .
									"PL2.Description AS Description,\n" .
									"DATE_FORMAT(Request.RequestDateTime, '%e/%m/%Y') AS RequestDate,\n" .
									"CASE\n" .
									"	WHEN PL2.Type - 600 THEN 'Other Service Operation'\n" .
									"	WHEN PL2.Type - 601 THEN 'Service Gain'\n" .
									"	WHEN PL2.Type - 602 THEN 'Service Loss'\n" .
									"	ELSE NULL\n" .
									"END AS Action,\n" .
									"CONCAT(Employee.FirstName, ' ', Employee.LastName) AS Employee,\n" .
									"CASE\n" . 
									"	WHEN Request.RequestType = 900 THEN 'Full Service'\n" . 
									"	WHEN Request.RequestType = 901 THEN 'Preselection'\n" . 
									"	WHEN Request.RequestType = 902 THEN 'Soft Bar'\n" . 
									"	WHEN Request.RequestType = 903 THEN 'Soft UnBar'\n" . 
									"	WHEN Request.RequestType = 904 THEN 'Activation'\n" . 
									"	WHEN Request.RequestType = 905 THEN 'Deactivation'\n" . 
									"	WHEN Request.RequestType = 906 THEN 'Preselection Reversal'\n" . 
									"	WHEN Request.RequestType = 907 THEN 'Full Service Reversal'\n" . 
									"	WHEN Request.RequestType = 908 THEN 'Hard Bar'\n" . 
									"	WHEN Request.RequestType = 909 THEN 'Hard UnBar'\n" . 
									"	ELSE NULL\n" .
									"END AS RequestType,\n" .
									"Request.Status AS RequestStatus\n\n" .
									"FROM ((Request JOIN Service ON Service.Id = Request.Service) JOIN Employee ON Employee.Id = Request.Employee) LEFT JOIN ProvisioningLog PL2 ON Request.Id = PL2.Request\n" .
									"WHERE DATE_FORMAT(Request.RequestDateTime, '%e/%m/%Y') BETWEEN <StartDate> AND <EndDate>\n" .
									"ORDER BY Request.RequestDateTime DESC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Service Id']		= "PL.Service";
$arrSQLSelect['Service FNN']	= "Service.FNN";
$arrSQLSelect['Carrier']		=	"CASE\n" .
									"WHEN PL.Carrier = 1 THEN 'Unitel'\n" .
									"WHEN PL.Carrier = 2 THEN 'Optus'\n" .
									"WHEN PL.Carrier = 3 THEN 'AAPT'\n" .
									"WHEN PL.Carrier = 4 THEN 'iSeek'\n" .
									"END AS Carrier\n";
$arrSQLSelect['Direction']		=	"CASE\n" .
									"WHEN PL.Direction = ".REQUEST_DIRECTION_OUTGOING." THEN 'Outbound'" .
									"ELSE 'Inbound'";
$arrSQLSelect['Service Id']	= "PL.Service";
$arrSQLSelect['Service Id']	= "PL.Service";
$arrSQLSelect['Service Id']	= "PL.Service";
$arrSQLSelect['Service Id']	= "PL.Service";
$arrSQLSelect['Service Id']	= "PL.Service";
$arrSQLSelect['Service Id']	= "PL.Service";
$arrSQLSelect['Service Id']	= "PL.Service";
$arrSQLSelect['Service Id']	= "PL.Service";
$arrSQLSelect['Service Id']	= "PL.Service";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
*/
/*
//----------------------------------------------------------------------------//
// Payment Import Summary
//----------------------------------------------------------------------------//

$arrDataReport = Array();
$arrDataReport['Name']			= "Payment Import Summary";
$arrDataReport['Summary']		= "Shows all Payment files imported in a specified date range, and their totals";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "FileImport JOIN Payment ON Payment.File = FileImport.Id";
$arrDataReport['SQLWhere']		= "FileImport.ImportedOn BETWEEN <StartDate> AND <EndDate> AND " .
								"Payment.Status BETWEEN 100 AND 199";
$arrDataReport['SQLGroupBy']	= "FileImport.Id";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['File Id']					= "FileImport.Id";
$arrSQLSelect['Date Imported']				= "DATE_FORMAT(FileImport.ImportedOn, '%e/%m/%Y')";
$arrSQLSelect['Origin']						= "\nCASE\n" .
											"WHEN FileType = 1 THEN 'BillExpress'\n" .
											"WHEN FileType = 2 THEN 'BPay'\n" .
											"WHEN FileType = 3 THEN 'Cheque'\n" .
											"WHEN FileType = 4 THEN 'SecurePay'\n" .
											"WHEN FileType = 5 THEN 'Credit Card'\n" .
											"END\n";
$arrSQLSelect['Filename']					= "FileImport.FileName";
$arrSQLSelect['No. of Payments']			= "COUNT(Payment.Id)";
$arrSQLSelect['Total Received']				= "SUM(Payment.Amount)";
$arrSQLSelect['Total Applied']				= "SUM(Payment.Amount) - SUM(Payment.Balance)";
$arrSQLSelect['Unapplied/Overpayments']		= "SUM(Payment.Balance)";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
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
*/

/*
//----------------------------------------------------------------------------//
// Payment Totals Summary
//----------------------------------------------------------------------------//

$arrDataReport = Array();
$arrDataReport['Name']			= "Payment Totals Summary";
$arrDataReport['Summary']		= "Shows the totals all Payments made in the specified Date Range, grouped by their Payment Type (eg. BillExpress, BPay)";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Payment";
$arrDataReport['SQLWhere']		= "PaidOn BETWEEN <StartDate> AND <EndDate> AND " .
								"Status BETWEEN 100 AND 199";
$arrDataReport['SQLGroupBy']	= "PaymentType";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Payment Type']				= "\nCASE\n" .
											"WHEN PaymentType = 1 THEN 'BillExpress'\n" .
											"WHEN PaymentType = 2 THEN 'BPay'\n" .
											"WHEN PaymentType = 3 THEN 'Cheque'\n" .
											"WHEN PaymentType = 4 THEN 'SecurePay'\n" .
											"WHEN PaymentType = 5 THEN 'Credit Card'\n" .
											"WHEN Payment = 'Scraped From Etech' THEN Payment\n" .
											"ELSE 'Manually Entered'\n" .
											"END";
$arrSQLSelect['No. of Payments']			= "COUNT(Id)";
$arrSQLSelect['Total Received']				= "SUM(Amount)";
$arrSQLSelect['Total Applied']				= "SUM(Amount) - SUM(Balance)";
$arrSQLSelect['Unapplied/Overpayments']		= "SUM(Balance)";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
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
*/
/*
//----------------------------------------------------------------------------//
// Payment in a Date Range - FIXME
//----------------------------------------------------------------------------//

$arrDataReport = Array();
$arrDataReport['Name']			= "Payments List in a Date Range";
$arrDataReport['Summary']		= "Shows all Payments made in the specified Date Range";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(Payment LEFT JOIN Employee ON Payment.EnteredBy = Employee.Id) LEFT JOIN FileImport ON FileImport.Id = Payment.File";
$arrDataReport['SQLWhere']		= "Payment.PaidOn BETWEEN <StartDate> AND <EndDate> AND " .
								"Payment.Status BETWEEN 100 AND 199";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Payment Id']				= "Payment.Id";
$arrSQLSelect['AccountGroup']			= "Payment.AccountGroup";
$arrSQLSelect['Account']				= "Payment.Account";
$arrSQLSelect['Payment Type']			= "\nCASE\n" .
										"WHEN Payment.PaymentType = 1 THEN 'BillExpress'\n" .
										"WHEN Payment.PaymentType = 2 THEN 'BPay'\n" .
										"WHEN Payment.PaymentType = 3 THEN 'Cheque'\n" .
										"WHEN Payment.PaymentType = 4 THEN 'SecurePay'\n" .
										"WHEN Payment.PaymentType = 5 THEN 'Credit Card'\n" .
										"WHEN Payment.Payment = 'Scraped From Etech' THEN Payment.Payment\n" .
										"ELSE 'Manually Entered'\n" .
										"END";
$arrSQLSelect['Reference No.']			= "Payment.TXNReference";
$arrSQLSelect['File/Employee']			= "CASE\n" .
										"WHEN File IS NOT NULL THEN CONCAT(FileImport.FileName, ' Line(', Payment.SequenceNo, ')')\n" .
										"WHEN Payment.EnteredBy != 999999999 THEN CONCAT(Employee.FirstName, ' ', Employee.LastName)\n" .
										"WHEN Payment.EnteredBy = 999999999 THEN 'Automated Entry'" .
										"END";
$arrSQLSelect['Total Paid']				= "Payment.Amount";
$arrSQLSelect['Applied to Invoices']	= "Payment.Amount - Payment.Balance";
$arrSQLSelect['Total Unapplied']		= "Payment.Balance";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
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
*/
/*
//----------------------------------------------------------------------------//
// Direct Debit Report
//----------------------------------------------------------------------------//

$strStartDate	= date("Y-m-01", time());

$arrDataReport = Array();
$arrDataReport['Name']			= "Direct Debit Payments Report";
//$arrDataReport['Name']			= "Direct Debit Payments Report (Hack)";
$arrDataReport['Summary']		= "Details Direct Debit information for use in automated payments";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "DirectDebit JOIN Account ON (Account.DirectDebit = DirectDebit.Id) JOIN Invoice ON (Account.Id = Invoice.Account)";
//$arrDataReport['SQLWhere']		= "DirectDebit.Archived = 0 AND Invoice.Balance > 0 AND Invoice.AccountBalance >= 0 AND Invoice.DueOn BETWEEN '$strStartDate' AND SUBDATE(ADDDATE('$strStartDate', INTERVAL 1 MONTH), INTERVAL 1 DAY)";
$arrDataReport['SQLWhere']		= "Account.Archived = 0 AND DirectDebit.Archived = 0 AND Account.BillingType = 1 AND Invoice.DueOn <= CURDATE()";
$arrDataReport['SQLGroupBy']	= "Invoice.Account\n HAVING SUM(Invoice.Balance) > 5";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['BSB']					= "LPAD(DirectDebit.BSB, 6, '0')";
$arrSQLSelect['Bank Account Number']	= "DirectDebit.AccountNumber";
$arrSQLSelect['Account Name']			= "DirectDebit.AccountName";
//$arrSQLSelect['Amount Charged']			= "Invoice.Balance";
$arrSQLSelect['Amount Charged']			= "CAST(ROUND(SUM(Invoice.Balance * 100)) AS SIGNED)";
$arrSQLSelect['Account Number']			= "Account.Id";
$arrSQLSelect['Customer Name']			= "Account.BusinessName";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
*/
/*
//----------------------------------------------------------------------------//
// Credit Card Report 
//----------------------------------------------------------------------------//

$strStartDate	= date("Y-m-01", time());

$arrDataReport = Array();
$arrDataReport['Name']			= "Credit Card Payments Report";
//$arrDataReport['Name']			= "Credit Card Payments Report (Hack)";
$arrDataReport['Summary']		= "Details Credit Card information for use in automated payments";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "CreditCard JOIN Account ON (Account.CreditCard = CreditCard.Id) JOIN Invoice ON (Account.Id = Invoice.Account)";
//$arrDataReport['SQLWhere']		= "CreditCard.Archived = 0 AND Invoice.Balance > 0 AND Invoice.AccountBalance >= 0 AND Invoice.DueOn BETWEEN '$strStartDate' AND SUBDATE(ADDDATE('$strStartDate', INTERVAL 1 MONTH), INTERVAL 1 DAY)";
$arrDataReport['SQLWhere']		= "Account.Archived = 0 AND CreditCard.Archived = 0 AND Account.BillingType = 2 AND Invoice.DueOn <= CURDATE()";
$arrDataReport['SQLGroupBy']	= "Invoice.Account\n HAVING SUM(Invoice.Balance) > 5";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['CC Number']				= "REPLACE(CreditCard.CardNumber, ' ', '')";
$arrSQLSelect['Expiry Date']			= "CONCAT(LPAD(CreditCard.ExpMonth, 2, '00'), '/', LPAD(SUBSTR(LPAD(CreditCard.ExpYear, 4, '2000'), -2), 2, '0'))";
//$arrSQLSelect['Amount Charged']			= "Invoice.Balance";
$arrSQLSelect['Amount Charged']			= "CAST(ROUND(SUM(Invoice.Balance * 100)) AS SIGNED)";
$arrSQLSelect['Account Number']			= "Account.Id";
$arrSQLSelect['Customer Name']			= "Account.BusinessName";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
*/
/*
//----------------------------------------------------------------------------//
// Unbilled Adjustments Report 
//----------------------------------------------------------------------------//

$arrDataReport = Array();
$arrDataReport['Name']			= "Unbilled Adjustments Report";
$arrDataReport['Summary']		= "Lists all Accounts with Unbilled Adjustments, and the total of the two.";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "Charge JOIN Account ON Account.Id = Charge.Account";
$arrDataReport['SQLWhere']		= "Charge.Status = 101 AND Account.Archived = 0";
$arrDataReport['SQLGroupBy']	= "Charge.Account";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Account Number']			= "Charge.Account";
$arrSQLSelect['Customer Name']			= "Account.BusinessName";
$arrSQLSelect['Total Debits']			= "SUM(CASE WHEN Charge.Nature = 'DR' THEN Charge.Amount END)";
$arrSQLSelect['Total Credits']			= "SUM(CASE WHEN Charge.Nature = 'CR' THEN Charge.Amount END)";
$arrSQLSelect['Adjustment Total']		= "SUM(CASE WHEN Charge.Nature = 'DR' THEN Charge.Amount ELSE 0.0 END) - SUM(CASE WHEN Charge.Nature = 'CR' THEN Charge.Amount ELSE 0.0 END)";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
*/
/*
//----------------------------------------------------------------------------//
// All Delinquents in a Date Period
//----------------------------------------------------------------------------//

$arrDataReport = Array();
$arrDataReport['Name']			= "All Delinquents in a Date Period";
$arrDataReport['Summary']		= "Lists all Delinquent FNNs and their associated totals and Carriers in a specified Date Range";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "CDR USE INDEX (Status)";
$arrDataReport['SQLWhere']		= "Carrier BETWEEN 1 AND 4 AND Status = ".CDR_BAD_OWNER." AND (StartDatetime BETWEEN CONCAT(<StartDate>, ' 00:00:00') AND CONCAT(<EndDate>, ' 23:59:59') OR NormalisedOn BETWEEN CONCAT(<StartDate>, ' 00:00:00') AND CONCAT(<EndDate>, ' 23:59:59'))";
$arrDataReport['SQLGroupBy']	= "FNN, Carrier";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['FNN']				= "FNN";
$arrSQLSelect['Total Cost $']		= "SUM(Cost)";
$arrSQLSelect['Total Occurrences']	= "COUNT(Id)";
$arrSQLSelect['Unitel']				= "CASE WHEN Carrier = 1 THEN 'X' END";
$arrSQLSelect['Optus']				= "CASE WHEN Carrier = 2 THEN 'X' END";
$arrSQLSelect['AAPT']				= "CASE WHEN Carrier = 3 THEN 'X' END";
$arrSQLSelect['iSeek']				= "CASE WHEN Carrier = 4 THEN 'X' END";
$arrSQLSelect['Earliest CDR']		= "MIN(StartDatetime)";
$arrSQLSelect['Latest CDR']			= "MAX(StartDatetime)";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
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
*/
/*
//----------------------------------------------------------------------------//
// Delinquent CDR Details in a Date Period
//----------------------------------------------------------------------------//

$arrDataReport = Array();
$arrDataReport['Name']			= "Delinquent CDR Details in a Date Period";
$arrDataReport['Summary']		= "Lists all CDRs for a specified Delinquent FNN in a specified Date Range";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "(CDR USE INDEX (Status) JOIN RecordType ON CDR.RecordType = RecordType.Id) JOIN FileImport ON CDR.File = FileImport.Id";
$arrDataReport['SQLWhere']		= "CDR.FNN LIKE <FNN> AND CDR.Status = ".CDR_BAD_OWNER." AND (CAST(CDR.StartDatetime AS DATE) BETWEEN <StartDate> AND <EndDate> OR CAST(CDR.NormalisedOn AS DATE) BETWEEN <StartDate> AND <EndDate>) ORDER BY CDR.StartDatetime";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Service";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Call Started On']	= "DATE_FORMAT(CDR.StartDatetime, '%e %b %Y, %r')";
$arrSQLSelect['Call Type']			= "RecordType.Description";
$arrSQLSelect['Source #']			= "CDR.Source";
$arrSQLSelect['Destination #']		= "CDR.Destination";
$arrSQLSelect['Duration']			= "SEC_TO_TIME(CDR.Units)";
$arrSQLSelect['Cost $']				= "CDR.Cost";
$arrSQLSelect['Carrier']			= "CASE\n" .
									"WHEN CDR.Carrier = 1 THEN 'Unitel'\n" .
									"WHEN CDR.Carrier = 2 THEN 'Optus'\n" .
									"WHEN CDR.Carrier = 3 THEN 'AAPT'\n" .
									"WHEN CDR.Carrier = 4 THEN 'iSeek'\n" .
									"END";
$arrSQLSelect['Originating File']	= "FileImport.FileName";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
$arrSQLFields['FNN']		= Array(
										'Type'					=> "dataString",
										'Documentation-Entity'	=> "Service",
										'Documentation-Field'	=> "FNN",
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
*//*
//----------------------------------------------------------------------------//
// Unrated CDR Summary in a Date Period
//----------------------------------------------------------------------------//

$arrDataReport = Array();
$arrDataReport['Name']			= "Unrated CDR Summary in a Date Period";
$arrDataReport['Summary']		= "Lists a summary of Unrated CDRs for a specified Account and FNN in a specified Date Range";
$arrDataReport['Priviledges']	= 0;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "CDR USE INDEX (Status) JOIN RecordType ON CDR.RecordType = RecordType.Id";
$arrDataReport['SQLWhere']		= "CDR.FNN LIKE <FNN> AND <Account> LIKE CAST(CDR.Account AS CHAR) AND CDR.Status = ".CDR_RATE_NOT_FOUND." AND (CAST(CDR.StartDatetime AS DATE) BETWEEN <StartDate> AND <EndDate> OR CAST(CDR.NormalisedOn AS DATE) BETWEEN <StartDate> AND <EndDate>)";
$arrDataReport['SQLGroupBy']	= "CDR.Service, RecordType.Id";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "DataReport";
$arrDocReq[]	= "Service";
$arrDocReq[]	= "Account";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect = Array();
$arrSQLSelect['Account']			= "CDR.Account";
$arrSQLSelect['Service']			= "CDR.Service";
$arrSQLSelect['FNN']				= "CDR.FNN";
$arrSQLSelect['Record Type']		= "RecordType.Description";
$arrSQLSelect['Record Type Id']		= "RecordType.Id";
$arrSQLSelect['Total Cost $']		= "SUM(CDR.Cost)";
$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = Array();
$arrDataReport['SQLFields'] = serialize($arrSQLFields);
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
*/

//Debug($arrDataReport);
//die;

$insDataReport = new StatementInsert("DataReport");
if (!$insDataReport->Execute($arrDataReport))
{
	Debug($insDataReport->Error());
}
Debug("OK!");

die;




















/*
 * SELECT 
CONCAT(Employee.FirstName, ' ', Employee.LastName) AS Employee, 
COUNT(Charge.Id) AS Count,
SUM(Charge.Amount) AS Total,
MAX(Charge.Amount) AS Max

FROM Charge, Employee
WHERE Charge.Nature = 'CR'
AND Charge.InvoiceRun = <InvoiceRun>
AND Charge.CreatedBy = Employee.Id
GROUP BY Charge.CreatedBy
 */



$arrDoc		= unserialize('a:2:{i:0;s:7:"Account";i:1;s:4:"Rate";}');
$arrSelect	= unserialize('a:5:{s:9:"Rate Name";s:6:"r.Name";s:10:"Cost Price";s:9:"sum(Cost)";s:12:"Charge Price";s:11:"sum(Charge)";s:10:"Profit ($)";s:23:"sum(Charge) - sum(Cost)";s:10:"Profit (%)";s:35:"sum(Charge) / sum(Cost) * 100 - 100";}');
$strWhere	= "StartDatetime BETWEEN <StartDatetime> AND <EndDatetime> AND (Status =150 OR Status = 198 OR Status = 199) AND Credit = 0";
$arrFields	= unserialize('a:2:{s:13:"StartDatetime";a:3:{s:4:"Type";s:8:"dataDate";s:20:"Documentation-Entity";s:3:"CDR";s:19:"Documentation-Field";s:13:"StartDatetime";}s:11:"EndDatetime";a:3:{s:4:"Type";s:8:"dataDate";s:20:"Documentation-Entity";s:3:"CDR";s:19:"Documentation-Field";s:11:"EndDatetime";}}');

Debug($arrDoc);
Debug($arrSelect);
Debug($strWhere);
Debug($arrFields);


/*
SELECT PL.Service, 

Service.FNN, 

CASE
	WHEN PL.Carrier = 1 THEN 'Unitel'
	WHEN PL.Carrier = 2 THEN 'Optus'
	WHEN PL.Carrier = 3 THEN 'AAPT'
	WHEN PL.Carrier = 4 THEN 'iSeek'
END AS Carrier,

CASE
	WHEN PL.Direction = 1 THEN 'Inbound' ELSE 'Outbound'
END AS Direction,

DATE_FORMAT(PL.Date, '%e/%m/%Y') AS Date,

PL.Description AS Description,

NULL AS RequestDate,

CASE
	WHEN PL.Type = 600 THEN 'Other Service Operation'
	WHEN PL.Type = 601 THEN 'Service Gain'
	WHEN PL.Type = 602 THEN 'Service Loss'
	ELSE NULL
END AS Action,

CONCAT(Employee.FirstName, ' ', Employee.LastName) AS Employee,

CASE
	WHEN Request.RequestType = 900 THEN 'Full Service'
	WHEN Request.RequestType = 901 THEN 'Preselection'
	WHEN Request.RequestType = 902 THEN 'Soft Bar'
	WHEN Request.RequestType = 903 THEN 'Soft UnBar'
	WHEN Request.RequestType = 904 THEN 'Activation'
	WHEN Request.RequestType = 905 THEN 'Deactivation'
	WHEN Request.RequestType = 906 THEN 'Preselection Reversal'
	WHEN Request.RequestType = 907 THEN 'Full Service Reversal'
	WHEN Request.RequestType = 908 THEN 'Hard Bar'
	WHEN Request.RequestType = 909 THEN 'Hard UnBar'
	ELSE NULL
END AS RequestType,

Request.Status AS RequestStatus


FROM (ProvisioningLog PL LEFT JOIN (Service, Request) ON (PL.Service = Service.Id AND Request.Id = PL.Request)) LEFT JOIN Employee ON Employee.Id = Request.Employee


UNION

SELECT Request.Service, 

Service.FNN, 

CASE
	WHEN Request.Carrier = 1 THEN 'Unitel'
	WHEN Request.Carrier = 2 THEN 'Optus'
	WHEN Request.Carrier = 3 THEN 'AAPT'
	WHEN Request.Carrier = 4 THEN 'iSeek'
END AS Carrier,

'Outbound' AS Direction,

DATE_FORMAT(PL2.Date, '%e/%m/%Y') AS Date,

PL2.Description AS Description,

DATE_FORMAT(Request.RequestDateTime, '%e/%m/%Y') AS RequestDate,

CASE
	WHEN PL2.Type = 600 THEN 'Other Service Operation'
	WHEN PL2.Type = 601 THEN 'Service Gain'
	WHEN PL2.Type = 602 THEN 'Service Loss'
	ELSE NULL
END AS Action,

CONCAT(Employee.FirstName, ' ', Employee.LastName) AS Employee,

CASE
	WHEN Request.RequestType = 900 THEN 'Full Service'
	WHEN Request.RequestType = 901 THEN 'Preselection'
	WHEN Request.RequestType = 902 THEN 'Soft Bar'
	WHEN Request.RequestType = 903 THEN 'Soft UnBar'
	WHEN Request.RequestType = 904 THEN 'Activation'
	WHEN Request.RequestType = 905 THEN 'Deactivation'
	WHEN Request.RequestType = 906 THEN 'Preselection Reversal'
	WHEN Request.RequestType = 907 THEN 'Full Service Reversal'
	WHEN Request.RequestType = 908 THEN 'Hard Bar'
	WHEN Request.RequestType = 909 THEN 'Hard UnBar'
	ELSE NULL
END AS RequestType,

Request.Status AS RequestStatus


FROM ((Request JOIN Service ON Service.Id = Request.Service) JOIN Employee ON Employee.Id = Request.Employee) LEFT JOIN ProvisioningLog PL2 ON Request.Id = PL2.Request
 */

?>