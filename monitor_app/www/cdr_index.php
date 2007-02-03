<?php

// cdr index

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// page title
$objPage->AddPageTitle('viXen CDR Menu');

// page link
$objPage->SetPageLink('[ CDR Menu ]');

// get CDR Status list
$arrStatus = $appMonitor->CountCDRStatus();
if (is_array($arrStatus))
{
	// title
	$objPage->AddTitle('CDRs by Status');
	
	// table
	$tblMenu = $objPage->NewTable('Border');
	$tblMenu->AddRow(Array('Code', 'Status', 'Count'));
	foreach($arrStatus AS $intStatus=>$intCount)
	{
		$strStatus 	= GetConstantDescription($intStatus, 'CDR');
		$arrRow = Array($intStatus, $strStatus, $intCount);
		$tblMenu->AddRow($arrRow, "cdr_list.php?Status=$intStatus");
	}
	$objPage->AddTable($tblMenu);
}
else
{
	$objPage->AddError("NO CDRs FOUND");
}

// get CDR Status, RecordType List
$arrStatusRecordType = $appMonitor->CountCDRStatusRecordType();
if (is_array($arrStatusRecordType))
{
	// title
	$objPage->AddTitle('CDRs by Status, RecordType');
	
	// table
	$tblMenu = $objPage->NewTable('Border');
	$tblMenu->AddRow(Array('Code', 'Status', 'RecordType', 'Count'));
	foreach($arrStatusRecordType AS $intStatus=>$arrRecordType)
	{
		$strStatus 	= GetConstantDescription($intStatus, 'CDR');
		foreach($arrRecordType AS $intRecordType=>$intCount)
		{
			$intRecordType = (int)$intRecordType;
			$strRecordType = $appMonitor->arrRecordType[$intRecordType]['Name'];
			$arrRow = Array($intStatus, $strStatus, "$intRecordType - $strRecordType", $intCount);
			$tblMenu->AddRow($arrRow, "cdr_list.php?Status=$intStatus&RecordType=$intRecordType");
		}
	}
	$objPage->AddTable($tblMenu);
}
else
{
	$objPage->AddError("NO CDRs FOUND");
}

// display the page
$objPage->Render();

?>
