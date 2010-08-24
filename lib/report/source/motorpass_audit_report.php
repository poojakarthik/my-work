<?php
//---------------------------------------------------------------------------//
// PAYMENT DOWNLOAD
//---------------------------------------------------------------------------//
$arrDataReport['Name']					= "Motorpass Audit Report";
$arrDataReport['Summary']				= "To check processing outcomes at each point in the process.";
$arrDataReport['RenderMode']			= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']			= 64;
$arrDataReport['CreatedOn']				= date("Y-m-d");
$arrDataReport['SQLTable']				= "Account a
								JOIN rebill r ON (a.Id = r.account_id)
								JOIN rebill_motorpass rm ON (r.Id = rm.rebill_id)
								JOIN motorpass_account mp ON (mp.id = rm.motorpass_account_id)
								LEFT JOIN FileExport fe ON (fe.Id = mp.file_export_id)
								LEFT JOIN FileImport fi ON (fi.Id = mp.file_import_id)
								LEFT JOIN motorpass_account_status st ON (st.id = mp.motorpass_account_status_id)";
$arrDataReport['SQLWhere']				= "";
$arrDataReport['SQLGroupBy']			= "";
$arrDataReport['data_report_status_id']	= DATA_REPORT_STATUS_DRAFT;

// Documentation Reqs
$arrDocReq[]					= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);



// SQL Select

$arrSQLSelect['Account Number']['Value']						= "a.Id";
$arrSQLSelect['Date of Sale']['Value']							= "mp.external_sale_datetime";
$arrSQLSelect['Date to ReD']['Value']							= "fe.exportedOn";
$arrSQLSelect['Date to ReD']['Type']							= EXCEL_TYPE_DATE;
$arrSQLSelect['Filename to ReD']['Value']						= "fe.FileName";
$arrSQLSelect['Date Application Response Received']['Value']	= "fi.importedOn";
$arrSQLSelect['Date Application Response Received']['Type']		= EXCEL_TYPE_DATE;
$arrSQLSelect['Response Filename']['Value']						= "fi.FileName";
$arrSQLSelect['Motorpass Accound Status']['Value']				= 'st.name';





$arrDataReport['SQLSelect'] 	= serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields = array();
/*$arrSQLFields['StartDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "Start Date",
									);
$arrSQLFields['EndDate']	= Array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "End Date",
									);*/
$arrDataReport['SQLFields'] 	= serialize($arrSQLFields);



?>