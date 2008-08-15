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