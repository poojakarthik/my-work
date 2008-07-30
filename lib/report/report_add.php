<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Add a new Data Report to viXen
//----------------------------------------------------------------------------//

// load application
require_once('../../flex.require.php');

//----------------------------------------------------------------------------//
// TODO: Specify the DataReport here!  See report_skeleton.php for tut
//----------------------------------------------------------------------------//

$arrDataReport	= Array();
$arrDocReq		= Array();
$arrSQLSelect	= Array();
$arrSQLFields	= Array();

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
// Insert the Data Report
//----------------------------------------------------------------------------//

$insDataReport = new StatementInsert("DataReport");
if (!$insDataReport->Execute($arrDataReport))
{
	Debug($insDataReport->Error());
}
Debug("OK!");

// finished
echo("\n\n-- End of Report Generation --\n");

?>