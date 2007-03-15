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
$arrDataReport['SQLWhere']		= "";
$arrDataReport['SQLGroupBy']	= "Account \nHAVING SUM(Balance) != 0";

// Documentation Reqs
$arrDocReqs = Array();
$arrDocReq[]	= "Invoice";
$arrDocReq[]	= "Account";
$arrDocReq[]	= "Contact";
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
$arrSQLSelect['Total Oustanding']		= "SUM(Balance) AS 'Total Outstanding'";

$arrSQLSelect['Total Overdue']			=	"SUM(CASE " .
											"		WHEN NOW() > Invoice.DueOn THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['1-29 Days Overdue']		=	"SUM(CASE" .
											"		WHEN NOW() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 1 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 29 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['30-59 Days Overdue']		=	"SUM(CASE" .
											"		WHEN NOW() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 30 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 59 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['60-89 Days Overdue']		=	"SUM(CASE" .
											"		WHEN NOW() BETWEEN ADDDATE(Invoice.DueOn, INTERVAL 60 DAY) AND ADDDATE(Invoice.DueOn, INTERVAL 89 DAY) THEN Invoice.Balance" .
											"	END)";
$arrSQLSelect['90+ Days Overdue']		=	"SUM(CASE" .
											"		WHEN NOW() >= ADDDATE(Invoice.DueOn, INTERVAL 90 DAY) THEN Invoice.Balance" .
											"	END)";

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

?>