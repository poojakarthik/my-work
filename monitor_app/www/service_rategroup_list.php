<?php

// cdr list

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// page title
$objPage->AddPageTitle('viXen Service RateGroup List');

// page link
$objPage->SetPageLink('[ Service RateGroup List ]');

// get values
$intStart 		= (int)$_GET['Start'];
$intLimit 		= (int)$_GET['Limit'];
if (!$intLimit)
{
	$intLimit 	= 30;
}
$intService		= (int)$_GET['Service'];
$intMaxId 		= $intStart;

// menu
$objPage->AddLink("cdr_index.php","[ CDR Menu ]");
$objPage->AddBackLink();

// get Rate Group list
$arrWhere = Array();
if ($intService)
{
	$arrRateGroups = $appMonitor->ListServiceRateGroup($intService);
	if (is_array($arrRateGroups))
	{
		$objPage->AddTitle("RateGroups for Service: $intService");
	
		// table
		$tblRateGroup = $objPage->NewTable('Border');
		$tblRateGroup->AddRow(Array('Id', 'Name', 'Description', 'RecordType', 'Fleet', 'Start Date', 'End Date'));
		foreach($arrRateGroups AS $arrRateGroup)
		{
			$intRecordType = $arrRateGroup['RecordType'];
			$strRecordType = "$intRecordType - ".$appMonitor->arrRecordType[$intRecordType]['Name'];
			$arrRow = Array($arrRateGroup['Id'], $arrRateGroup['Name'], $arrRateGroup['Description'], $strRecordType, $arrRateGroup['Fleet'], $arrRateGroup['StartDateTime'], $arrRateGroup['EndDateTime']);
			$tblRateGroup->AddRow($arrRow);
		}
		$objPage->AddTable($tblRateGroup);
	}
	else
	{
		$objPage->AddError("NO RateGroups FOUND");
	}
}
else
{
	$objPage->AddError("NO Service Selected");
}

// display the page
$objPage->Render();

?>
