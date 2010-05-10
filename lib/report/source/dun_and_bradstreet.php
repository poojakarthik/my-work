<?php
//---------------------------------------------------------------------------//
// PAYMENT DOWNLOAD
//---------------------------------------------------------------------------//
$arrDataReport['Name']					= "Dun & Bradstreet Report";
$arrDataReport['Summary']				= "A list of Accounts to be Referred to Dun and Bradstreet";
$arrDataReport['RenderMode']			= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']			= PERMISSION_CREDIT_MANAGEMENT;
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLTable']				= "	Account a
											JOIN (CustomerGroup c, Contact co, Invoice i,credit_control_status cc )
											ON (c.Id = a.CustomerGroup  AND co.Id = a.PrimaryContact AND i.Account = a.Id AND cc.id = a.credit_control_status)";
$arrDataReport['SQLWhere']				= "	i.Balance>0
											AND cc.const_name = 'CREDIT_CONTROL_STATUS_SENDING_TO_DEBT_COLLECTION'
											AND a.tio_reference_number IS NULL";
$arrDataReport['SQLGroupBy']			= " i.Account";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);


// SQL Select
$arrSQLSelect['Account Number']	['Value']	= "a.id";
$arrSQLSelect['Account Number']	['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Business Name']	['Value']	= "a.BusinessName";

$arrSQLSelect['Customer Group']	['Value']	= "c.external_name";

$arrSQLSelect['ABN']			['Value']	= "IF (CHAR_LENGTH(a.ABN)>0,a.ABN, NULL)";


$arrSQLSelect['Contact Name']	['Value']	= "CONCAT_WS(' ',co.FirstName,co.LastName)";

$arrSQLSelect['Address']		['Value']	= "CONCAT_WS(' ',a.Address1,a.Address2)";


$arrSQLSelect['Suburb']			['Value']	= "a.Suburb";


$arrSQLSelect['State']			['Value']	= "a.State";


$arrSQLSelect['Postcode']		['Value']	= "a.Postcode";
$arrSQLSelect['Postcode']		['Type']	= EXCEL_TYPE_INTEGER;


$arrSQLSelect['Phone']			['Value']	= "IF(CONVERT(co.Phone,UNSIGNED), LPAD(co.Phone,10,0), NULL)";
$arrSQLSelect['Phone']			['Type']	= FNN;

$arrSQLSelect['Mobile']			['Value']	= "IF(CONVERT(co.Mobile,UNSIGNED), LPAD(co.Mobile,10,0), NULL)";
$arrSQLSelect['Mobile']			['Type']	= FNN;

$arrSQLSelect['Email']			['Value']	= "co.EMail";

$arrSQLSelect['Date of Debt']	['Value']	= "MIN(i.createdOn)";

$arrSQLSelect['Balance Due']	['Value']	= "SUM(i.Balance)";
$arrSQLSelect['Balance Due']	['Type']	= EXCEL_TYPE_CURRENCY;

$arrDataReport['SQLSelect'] 	= serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = array();
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);

?>