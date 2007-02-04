<?php

// cdr index

// load application
require_once('application_loader.php');

// load page class
require_once('page.php');

// page title
$objPage->AddPageTitle('viXen CDRs by Status');

// page link
$objPage->SetPageLink('[ CDRs by Status ]');

// menu
$objPage->AddLink("cdr_index.php","[ CDR Menu ]");

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

// display the page
$objPage->Render();

?>
