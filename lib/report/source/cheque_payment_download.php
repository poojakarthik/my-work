<?php
//---------------------------------------------------------------------------//
// CHEQUE PAYMENT DOWNLOAD
//---------------------------------------------------------------------------//
$arrDataReport['Name']			= "Cheque Payment Download";
$arrDataReport['Summary']		= "A list of cheque payments entered on a certain day.";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= PERMISSION_OPERATOR;
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Payment p 
									JOIN 	Account a ON (a.Id = p.Account) 
									JOIN 	CustomerGroup cg ON cg.Id = a.CustomerGroup ";
$arrDataReport['SQLWhere']		= "	p.PaymentType = ".PAYMENT_TYPE_CHEQUE."
									AND 	CAST(p.PaidOn AS DATE) = CAST(<PaymentDate> AS DATE) 
									AND 	(<CustomerGroup> = 0 OR a.CustomerGroup = <CustomerGroup>)
									ORDER BY cg.internal_name ASC";
$arrDataReport['SQLGroupBy']	= "";

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Customer Group - Name']		['Value']	= "cg.internal_name";
$arrSQLSelect['Customer Group - Account']	['Value']	= "cg.bank_account_name";
$arrSQLSelect['Customer Group - BSB']		['Value']	= "cg.bank_bsb";
$arrSQLSelect['Customer Group - Account #']	['Value']	= "cg.bank_account_number";
$arrSQLSelect['Payment ID']					['Value']	= "p.Id"; 
$arrSQLSelect['Account Group']				['Value']	= "p.AccountGroup";
$arrSQLSelect['Account Group']				['Type']	= EXCEL_TYPE_INTEGER;
$arrSQLSelect['Account']					['Value']	= "p.Account"; 
$arrSQLSelect['Account']					['Type']	= EXCEL_TYPE_INTEGER;
$arrSQLSelect['Business Name']				['Value']	= "a.BusinessName"; 
$arrSQLSelect['Trading Name']				['Value']	= "a.TradingName"; 
$arrSQLSelect['Reference']					['Value']	= "p.TXNReference"; 
$arrSQLSelect['Date']						['Value']	= "p.PaidOn"; 
$arrSQLSelect['Amount']						['Value']	= "p.Amount";
$arrSQLSelect['Amount']						['Type']	= EXCEL_TYPE_CURRENCY;
$arrDataReport['SQLSelect'] 				= serialize($arrSQLSelect);

// SQL Fields
$aCustomerGroupQuery			=	array(
										'Query'			=> "SELECT		Id AS `Value`, external_name AS `Label`
															FROM		CustomerGroup
															ORDER BY	Id ASC;",
										'ValueType'		=> "dataInteger",
										'IgnoreField'	=> array('Value' => 0, 'Label' => 'Any')
									);
$arrSQLFields['CustomerGroup']	= 	Array(
										'Type'					=> "Query",
										'DBQuery'				=> $aCustomerGroupQuery,
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Customer Group",
									);
$arrSQLFields['PaymentDate']	= 	Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Payment Date",
									);
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);

?>