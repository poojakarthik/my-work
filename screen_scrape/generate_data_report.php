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
											"		WHEN NOW() <= Invoice.DueOn THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['1-29 Days Overdue']		=	"SUM(CASE " .
											"		WHEN NOW() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 1 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 29 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['30-59 Days Overdue']		=	"SUM(CASE " .
											"		WHEN NOW() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 30 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 59 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['60-89 Days Overdue']		=	"SUM(CASE " .
											"		WHEN NOW() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 60 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 89 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['90+ Days Overdue']		=	"SUM(CASE " .
											"		WHEN NOW() >= ADDDATE(Invoice.DueOn, INTERVAL 90 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['Total Overdue']			=	"SUM(CASE " .
											"		WHEN NOW() > Invoice.DueOn THEN Invoice.Balance" .
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
$arrDataReport['Name']			= "Non-Archived Accounts with No Invalid Email";
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