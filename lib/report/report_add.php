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

$arrDataReport	= array();
$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();


//---------------------------------------------------------------------------//
// OPEN NETWORKS DAILY ORDER FILE
//---------------------------------------------------------------------------//

$arrDataReport['Name']			= "Open Networks Daily Order File";
$arrDataReport['Summary']		= "Generates the Open Networks Daily Order File for a specified date";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 2147483648;									// Debug
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "	Service s
									JOIN Account a ON (s.Account = a.Id)
									JOIN CustomerGroup cg ON (a.CustomerGroup = cg.Id)
									JOIN ServiceRatePlan srp ON (s.Id = srp.Service)
									JOIN RatePlan rp ON (rp.id = srp.RatePlan)";
$arrDataReport['SQLWhere']		= "Account.CreatedOn BETWEEN <StartDate> AND <EndDate> AND Account.Archived = 0";
$arrDataReport['SQLGroupBy']	= "	s.ServiceType = 100
												AND s.Status = 400
												AND a.Archived = 0
												AND CAST(s.CreatedOn AS DATE) = <order_date>
												AND <order_date> BETWEEN CAST(srp.StartDatetime AS DATE) AND CAST(srp.EndDatetime AS DATE)
												AND srp.Id	=	(
																	SELECT		Id
																	FROM		ServiceRatePlan
																	WHERE		Service = s.Id
																				AND <order_date> BETWEEN CAST(StartDatetime AS DATE) AND CAST(EndDatetime AS DATE)
																	ORDER BY	CreatedOn DESC
																	LIMIT		1
																)
									ORDER BY	s.Id ASC";

// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['Order Number']					['Value']	= "s.Id";

$arrSQLSelect['ISP Identification']				['Value']	= "cg.internal_name";

$arrSQLSelect['Required Ship Date']				['Value']	= "DATE_FORMAT(CURDATE(), '%d/%m/%y')";

$arrSQLSelect['Product Required']				['Value']	= 'Netgear DM111PUSP';

$arrSQLSelect['Additional Product Required']	['Value']	= '';

$arrSQLSelect['Subscriber Name']				['Value']	= "a.BusinessName";

$arrSQLSelect['Unique User Name']				['Value']	= "CONCAT(LEFT(s.FNN, 10), '@blue1000.com.au')";

$arrSQLSelect['Unique Password']				['Value']	= "LEFT(s.FNN, 10)";

$arrSQLSelect['Subscriber Ph. No.']				['Value']	= "LEFT(s.FNN, 10)";

$arrSQLSelect['Street Number & Name']			['Value']	= "a.Address1";

$arrSQLSelect['Extra Address Details']			['Value']	= "a.Address2";

$arrSQLSelect['Suburb/City']					['Value']	= "a.Suburb";

$arrSQLSelect['State']							['Value']	= "a.State";

$arrSQLSelect['Postcode']						['Value']	= "a.Postcode";

$arrSQLSelect['Notes/comments']					['Value']	= "rp.Name";

$arrSQLSelect['Serial Number']					['Value']	= '';

$arrSQLSelect['Consignment Number']				['Value']	= '';

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

// SQL Fields
$arrSQLFields['order_date']	= array(
										'Type'					=> "dataDate",
										'Documentation-Entity'	=> "DataReport",
										'Documentation-Field'	=> "OrderDate",
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