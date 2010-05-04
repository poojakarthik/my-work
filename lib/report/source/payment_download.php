<?php
//---------------------------------------------------------------------------//
// PAYMENT DOWNLOAD
//---------------------------------------------------------------------------//
$arrDataReport['Name']					= "Payment Download";
$arrDataReport['Summary']				= "A list of payments entered on a certain day.";
$arrDataReport['RenderMode']			= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']			= PERMISSION_OPERATOR;
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLTable']				= "	Payment p
											JOIN 	Account a ON p.Account = a.Id
											JOIN 	CustomerGroup cg ON cg.Id = a.CustomerGroup";
$arrDataReport['SQLWhere']				= "	p.PaymentType = <PaymentType>
											AND 	p.PaidOn = <PaymentDate>
											AND 	(<CustomerGroup> = 0 OR a.CustomerGroup = <CustomerGroup>)";
$arrDataReport['SQLGroupBy']			= "";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Account Group']	['Value']	= "p.AccountGroup";
$arrSQLSelect['Account Group']	['Type']	= EXCEL_TYPE_INTEGER;
$arrSQLSelect['Account Id']		['Value']	= "p.Account";
$arrSQLSelect['Account Id']		['Type']	= EXCEL_TYPE_INTEGER;
$arrSQLSelect['Business Name']	['Value']	= "a.BusinessName";
$arrSQLSelect['Trading Name']	['Value']	= "a.TradingName";
$arrSQLSelect['Customer Group']	['Value']	= "cg.external_name";
$arrSQLSelect['Reference']		['Value']	= "p.TXNReference";
$arrSQLSelect['Date']			['Value']	= "p.PaidOn";
$arrSQLSelect['Amount']			['Value']	= "p.Amount";
$arrSQLSelect['Amount']			['Type']	= EXCEL_TYPE_CURRENCY;
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
$aPaymentTypeQuery				=	array(
										'Query'		=> 	"SELECT		Id AS `Value`, name AS `Label`
														FROM		payment_type
														WHERE		Id <> ".PAYMENT_TYPE_CHEQUE."
														ORDER BY	Id ASC;",
										'ValueType'	=> "dataInteger"
									);
$arrSQLFields['PaymentType']	= 	Array(
										'Type'					=> "Query",
										'DBQuery'				=> $aPaymentTypeQuery,
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Payment Type",
									);
$arrSQLFields['PaymentDate']	= 	Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Payment Date",
									);
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);

?>