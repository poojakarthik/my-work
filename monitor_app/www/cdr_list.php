<?php

// cdr list

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// page title
$objPage->AddPageTitle('viXen CDR List');

// page link
$objPage->SetPageLink('[ CDR List ]');

// get values
$intStart 		= (int)$_GET['Start'];
$intLimit 		= (int)$_GET['Limit'];
if (!$intLimit)
{
	$intLimit 	= 30;
}
$intStatus 		= (int)$_GET['Status'];
$intRecordType 	= (int)$_GET['RecordType'];
$strStatus 		= GetConstantDescription($intStatus, 'CDR');
$strRecordType 	= $appMonitor->arrRecordType[$intRecordType]['Name'];
$intMaxId 		= $intStart;

// menu
$objPage->AddLink("cdr_index.php","[ CDR Menu ]");
$objPage->AddBackLink();

// get CDR Status list
$arrWhere = Array();
if ($intStatus)
{
	$arrWhere['Status'] = $intStatus;
}
if ($intRecordType)
{
	$arrWhere['RecordType'] = $intRecordType;
}
$arrCDRs = $appMonitor->ListCDR($arrWhere, $intStart, $intLimit);
if (is_array($arrCDRs))
{
	$strTitle = "CDRs ";
	$strJoin = "with";
	// title
	if ($intStatus)
	{
		$strTitle .= $strJoin." Status: $intStatus - $strStatus ";
		$strJoin = "and";
	}
	if ($intRecordType)
	{
		$strTitle .= $strJoin." RecordType: $intRecordType - $strRecordType ";
	}
	$objPage->AddTitle($strTitle);
	
	// table
	$tblCDR = $objPage->NewTable('Border');
	$tblCDR->AddRow(Array('Id', 'Account', 'Service', 'FNN', 'Source', 'Destination', 'Description', 'Units', 'Cost', 'Charge', 'Credit', 'Rate', 'Dest.', 'ServiceType', 'RecordType', 'Status', 'Carrier', 'Start', 'End'));
	foreach($arrCDRs AS $arrCDR)
	{
		$intMaxId = max($intMaxId, $arrCDR['Id']);
		$arrRow = Array($arrCDR['Id'], $arrCDR['Account'], $arrCDR['Service'], $arrCDR['FNN'], $arrCDR['Source'], $arrCDR['Destination'], $arrCDR['Description'], $arrCDR['Units'], $arrCDR['Cost'], $arrCDR['Charge'], $arrCDR['Credit'], $arrCDR['Rate'], $arrCDR['DestinationCode'], $arrCDR['ServiceType'], $arrCDR['RecordType'], $arrCDR['Status'], $arrCDR['Carrier'], $arrCDR['StartDatetime'], $arrCDR['EndDatetime']);
		$tblCDR->AddRow($arrRow, "cdr_view.php?Id={$arrCDR['Id']}");
	}
	$objPage->AddTable($tblCDR);
	
	// pagination ('previous' button won't work properly)
	$intPaginateStart = $intMaxId - $intLimit;
	$objPage->AddPagination("cdr_list.php", "Status=$intStatus", $intPaginateStart, $intLimit);
}
else
{
	$objPage->AddError("NO CDRs FOUND");
}

// display the page
$objPage->Render();

?>
