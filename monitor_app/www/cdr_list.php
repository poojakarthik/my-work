<?php

// cdr list

// load application
require_once('application_loader.php');

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

// show title
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

// show list
$objPage->ShowCDRList($arrWhere, $intStart, $intLimit);

// display the page
$objPage->Render();

?>
